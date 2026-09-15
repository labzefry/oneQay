<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosShiftHistoryPerformanceWorkspaceRepository;
use App\Application\Pos\PosShiftHistoryPerformanceWorkspaceSnapshot;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Pos\TenderCategory;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosShiftHistoryPerformanceWorkspaceRepository implements PosShiftHistoryPerformanceWorkspaceRepository
{
    private const MAX_SHIFTS = 50;
    private const MAX_BUCKETS = 64;
    private const VOID_MODE = 'FULL_SALE_VOID';
    private const REFUND_MODE = 'FULL_CASH_REFUND';
    private const REVIEW_ACCEPTED = 'REVIEW_ACCEPTED';
    private const SHIFT_ID_PATTERN = '/\A[a-f0-9]{32}\z/';

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $reportingEnabled,
        private bool $featureEnabled,
        private bool $shiftCloseEnabled,
    ) {}

    public function read(
        PosExecutionContext $context,
        ?string $selectedShiftId,
    ): PosShiftHistoryPerformanceWorkspaceSnapshot {
        $this->assertOperational();
        if ($selectedShiftId !== null && preg_match(self::SHIFT_ID_PATTERN, $selectedShiftId) !== 1) {
            throw new PosTransactionViolation();
        }

        try {
            return $this->connection->transaction(
                fn (): PosShiftHistoryPerformanceWorkspaceSnapshot => $this->readStableSnapshot($context, $selectedShiftId),
                1,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function readStableSnapshot(
        PosExecutionContext $context,
        ?string $selectedShiftId,
    ): PosShiftHistoryPerformanceWorkspaceSnapshot {
        $closedShiftCount = (int) $this->connection->table('oneqay_pos_shifts')
            ->where('tenant_id', $context->tenantId())
            ->where('organization_id', $context->organizationId())
            ->where('outlet_id', $context->outletId())
            ->whereNull('active_slot')
            ->count();

        $joinedCloseCount = (int) $this->connection->table('oneqay_pos_shifts as shifts')
            ->join('oneqay_pos_shift_close_evidence as close', function ($join): void {
                $join->on('close.tenant_id', '=', 'shifts.tenant_id')
                    ->on('close.shift_id', '=', 'shifts.shift_id');
            })
            ->where('shifts.tenant_id', $context->tenantId())
            ->where('shifts.organization_id', $context->organizationId())
            ->where('shifts.outlet_id', $context->outletId())
            ->whereNull('shifts.active_slot')
            ->count();
        if ($closedShiftCount !== $joinedCloseCount) {
            throw new PosTransactionViolation();
        }

        $scopedCloseCount = (int) $this->closedShiftQuery($context)->count();
        if ($scopedCloseCount !== $joinedCloseCount) {
            throw new PosTransactionViolation();
        }

        $rows = $this->closedShiftQuery($context)
            ->orderByDesc('close.closed_at_unix')
            ->orderByDesc('shifts.shift_id')
            ->limit(self::MAX_SHIFTS)
            ->get([
                'shifts.shift_id',
                'shifts.device_id as shift_device_id',
                'shifts.actor_identity_id as opener_actor_identity_id',
                'shifts.opened_at_unix',
                'close.device_id as close_device_id',
                'close.closer_actor_identity_id',
                'close.cutoff_at_unix',
                'close.expected_cash_atomic',
                'close.observed_closing_cash_atomic',
                'close.variance_atomic',
                'close.variance_direction',
                'close.currency',
                'close.currency_scale',
                'close.review_outcome',
                'close.closed_at_unix',
            ]);

        $closedShifts = [];
        $byId = [];
        foreach ($rows as $row) {
            if (! is_object($row)) {
                throw new PosTransactionViolation();
            }
            $shift = $this->mapClosedShift($row);
            $shiftId = (string) $shift['shift_id'];
            if (isset($byId[$shiftId])) {
                throw new PosTransactionViolation();
            }
            $byId[$shiftId] = $shift;
            $closedShifts[] = $shift;
        }

        $selectedId = $selectedShiftId;
        if ($selectedId === null && $closedShifts !== []) {
            $selectedId = (string) $closedShifts[0]['shift_id'];
        }
        if ($selectedId !== null && ! isset($byId[$selectedId])) {
            throw new PosTransactionViolation();
        }

        $selected = $selectedId === null ? null : $byId[$selectedId];
        $buckets = $selected === null ? [] : $this->performanceBuckets($context, $selected);

        return new PosShiftHistoryPerformanceWorkspaceSnapshot(
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            $closedShifts,
            $selected,
            $buckets,
        );
    }

    private function closedShiftQuery(PosExecutionContext $context)
    {
        return $this->connection->table('oneqay_pos_shifts as shifts')
            ->join('oneqay_pos_shift_close_evidence as close', function ($join): void {
                $join->on('close.tenant_id', '=', 'shifts.tenant_id')
                    ->on('close.shift_id', '=', 'shifts.shift_id');
            })
            ->where('shifts.tenant_id', $context->tenantId())
            ->where('shifts.organization_id', $context->organizationId())
            ->where('shifts.outlet_id', $context->outletId())
            ->whereNull('shifts.active_slot')
            ->where('close.organization_id', $context->organizationId())
            ->where('close.outlet_id', $context->outletId());
    }

    /** @return array<string,mixed> */
    private function mapClosedShift(object $row): array
    {
        $shiftId = $this->requiredString($row->shift_id ?? null);
        if (preg_match(self::SHIFT_ID_PATTERN, $shiftId) !== 1) {
            throw new PosTransactionViolation();
        }
        $deviceId = $this->requiredString($row->shift_device_id ?? null);
        $this->assertEquals($row->close_device_id ?? null, $deviceId);

        $openedAt = $this->safePositiveInteger($row->opened_at_unix ?? null);
        $closedAt = $this->safePositiveInteger($row->closed_at_unix ?? null);
        $cutoffAt = $this->safePositiveInteger($row->cutoff_at_unix ?? null);
        if ($closedAt < $openedAt || $cutoffAt < $openedAt || $cutoffAt > $closedAt) {
            throw new PosTransactionViolation();
        }

        $expected = $this->safeNonNegativeInteger($row->expected_cash_atomic ?? null);
        $observed = $this->safeNonNegativeInteger($row->observed_closing_cash_atomic ?? null);
        $variance = $this->safeSignedInteger($row->variance_atomic ?? null);
        if ($variance !== $observed - $expected) {
            throw new PosTransactionViolation();
        }

        $direction = $this->requiredString($row->variance_direction ?? null);
        $reviewOutcome = $this->nullableString($row->review_outcome ?? null);
        $validVariance = ($direction === 'MATCH' && $variance === 0 && $reviewOutcome === null)
            || ($direction === 'OVER' && $variance > 0 && $reviewOutcome === self::REVIEW_ACCEPTED)
            || ($direction === 'SHORT' && $variance < 0 && $reviewOutcome === self::REVIEW_ACCEPTED);
        if (! $validVariance) {
            throw new PosTransactionViolation();
        }

        return [
            'shift_id' => $shiftId,
            'device_id' => $deviceId,
            'opener_actor_identity_id' => $this->requiredString($row->opener_actor_identity_id ?? null),
            'closer_actor_identity_id' => $this->requiredString($row->closer_actor_identity_id ?? null),
            'opened_at_unix' => $openedAt,
            'closed_at_unix' => $closedAt,
            'duration_seconds' => $closedAt - $openedAt,
            'cutoff_at_unix' => $cutoffAt,
            'expected_cash_atomic' => (string) $expected,
            'observed_closing_cash_atomic' => (string) $observed,
            'variance_atomic' => (string) $variance,
            'variance_direction' => $direction,
            'currency' => $this->currency($row->currency ?? null),
            'scale' => $this->scale($row->currency_scale ?? null),
            'review_outcome' => $reviewOutcome,
        ];
    }

    /** @param array<string,mixed> $shift @return list<array<string,mixed>> */
    private function performanceBuckets(PosExecutionContext $context, array $shift): array
    {
        $shiftId = (string) $shift['shift_id'];
        $deviceId = (string) $shift['device_id'];
        $openedAt = (int) $shift['opened_at_unix'];
        $closedAt = (int) $shift['closed_at_unix'];

        $legacyUnbound = (int) $this->connection->table('oneqay_pos_sales')
            ->where('tenant_id', $context->tenantId())
            ->where('organization_id', $context->organizationId())
            ->where('outlet_id', $context->outletId())
            ->where('device_id', $deviceId)
            ->whereNull('shift_id')
            ->where('completed_at_unix', '>=', $openedAt)
            ->where('completed_at_unix', '<=', $closedAt)
            ->count();
        if ($legacyUnbound !== 0) {
            throw new PosTransactionViolation();
        }

        $allBoundCount = (int) $this->connection->table('oneqay_pos_sales')
            ->where('tenant_id', $context->tenantId())
            ->where('shift_id', $shiftId)
            ->count();
        $scopedBoundCount = (int) $this->scopedSales($context, $shiftId, $deviceId)->count();
        if ($allBoundCount !== $scopedBoundCount) {
            throw new PosTransactionViolation();
        }

        $outsideWindow = (int) $this->scopedSales($context, $shiftId, $deviceId)
            ->where(function ($query) use ($openedAt, $closedAt): void {
                $query->where('completed_at_unix', '<', $openedAt)
                    ->orWhere('completed_at_unix', '>', $closedAt);
            })
            ->count();
        if ($outsideWindow !== 0) {
            throw new PosTransactionViolation();
        }

        $invalidSaleCount = (int) $this->scopedSales($context, $shiftId, $deviceId)
            ->where(function ($query): void {
                $query->whereColumn('total_atomic', '!=', 'applied_atomic')
                    ->orWhere('currency_scale', '>', 6)
                    ->orWhere(function ($tender): void {
                        $tender->where('tender_category', TenderCategory::CASH->value)
                            ->where('evidence_mode', '!=', TenderCategory::CASH->evidenceMode());
                    })
                    ->orWhere(function ($tender): void {
                        $tender->where('tender_category', TenderCategory::MANUAL_EXTERNAL->value)
                            ->where('evidence_mode', '!=', TenderCategory::MANUAL_EXTERNAL->evidenceMode());
                    })
                    ->orWhereNotIn('tender_category', [
                        TenderCategory::CASH->value,
                        TenderCategory::MANUAL_EXTERNAL->value,
                    ]);
            })
            ->count();
        if ($invalidSaleCount !== 0) {
            throw new PosTransactionViolation();
        }

        $this->assertVoidIntegrity($context, $shiftId, $deviceId);
        $this->assertRefundIntegrity($context, $shiftId, $deviceId);

        $buckets = [];
        $grossRows = $this->scopedSales($context, $shiftId, $deviceId)
            ->selectRaw('tender_category, currency, currency_scale, COUNT(*) AS completed_sales, SUM(total_atomic) AS gross_atomic')
            ->groupBy('tender_category', 'currency', 'currency_scale')
            ->orderBy('currency')
            ->orderBy('currency_scale')
            ->orderBy('tender_category')
            ->get();
        if ($grossRows->count() > self::MAX_BUCKETS) {
            throw new PosTransactionViolation();
        }

        foreach ($grossRows as $row) {
            if (! is_object($row)) {
                throw new PosTransactionViolation();
            }
            $tender = $this->tender($row->tender_category ?? null);
            $currency = $this->currency($row->currency ?? null);
            $scale = $this->scale($row->currency_scale ?? null);
            $completed = $this->safePositiveInteger($row->completed_sales ?? null);
            $gross = $this->safeNonNegativeInteger($row->gross_atomic ?? null);
            $key = $this->bucketKey($tender, $currency, $scale);
            if (isset($buckets[$key])) {
                throw new PosTransactionViolation();
            }
            $buckets[$key] = [
                'tender_category' => $tender,
                'currency' => $currency,
                'scale' => $scale,
                'completed_sales' => $completed,
                'voided_sales' => 0,
                'refunded_sales' => 0,
                'gross_atomic' => $gross,
                'voided_atomic' => 0,
                'refunded_cash_atomic' => 0,
            ];
        }

        $voidRows = $this->connection->table('oneqay_pos_sales as sales')
            ->join('oneqay_pos_sale_voids as voids', function ($join): void {
                $join->on('voids.tenant_id', '=', 'sales.tenant_id')
                    ->on('voids.sale_id', '=', 'sales.sale_id');
            })
            ->where('sales.tenant_id', $context->tenantId())
            ->where('sales.organization_id', $context->organizationId())
            ->where('sales.outlet_id', $context->outletId())
            ->where('sales.device_id', $deviceId)
            ->where('sales.shift_id', $shiftId)
            ->selectRaw('sales.tender_category, sales.currency, sales.currency_scale, COUNT(*) AS voided_sales, SUM(voids.reversed_atomic) AS voided_atomic')
            ->groupBy('sales.tender_category', 'sales.currency', 'sales.currency_scale')
            ->get();
        foreach ($voidRows as $row) {
            if (! is_object($row)) {
                throw new PosTransactionViolation();
            }
            $key = $this->bucketKey(
                $this->tender($row->tender_category ?? null),
                $this->currency($row->currency ?? null),
                $this->scale($row->currency_scale ?? null),
            );
            if (! isset($buckets[$key])) {
                throw new PosTransactionViolation();
            }
            $buckets[$key]['voided_sales'] = $this->safeNonNegativeInteger($row->voided_sales ?? null);
            $buckets[$key]['voided_atomic'] = $this->safeNonNegativeInteger($row->voided_atomic ?? null);
        }

        $refundRows = $this->connection->table('oneqay_pos_sales as sales')
            ->join('oneqay_pos_sale_cash_refunds as refunds', function ($join): void {
                $join->on('refunds.tenant_id', '=', 'sales.tenant_id')
                    ->on('refunds.sale_id', '=', 'sales.sale_id');
            })
            ->where('sales.tenant_id', $context->tenantId())
            ->where('sales.organization_id', $context->organizationId())
            ->where('sales.outlet_id', $context->outletId())
            ->where('sales.device_id', $deviceId)
            ->where('sales.shift_id', $shiftId)
            ->selectRaw('sales.tender_category, sales.currency, sales.currency_scale, COUNT(*) AS refunded_sales, SUM(refunds.refunded_atomic) AS refunded_cash_atomic')
            ->groupBy('sales.tender_category', 'sales.currency', 'sales.currency_scale')
            ->get();
        foreach ($refundRows as $row) {
            if (! is_object($row)) {
                throw new PosTransactionViolation();
            }
            $key = $this->bucketKey(
                $this->tender($row->tender_category ?? null),
                $this->currency($row->currency ?? null),
                $this->scale($row->currency_scale ?? null),
            );
            if (! isset($buckets[$key])) {
                throw new PosTransactionViolation();
            }
            $buckets[$key]['refunded_sales'] = $this->safeNonNegativeInteger($row->refunded_sales ?? null);
            $buckets[$key]['refunded_cash_atomic'] = $this->safeNonNegativeInteger($row->refunded_cash_atomic ?? null);
        }

        $rows = [];
        foreach ($buckets as $bucket) {
            $completed = (int) $bucket['completed_sales'];
            $voided = (int) $bucket['voided_sales'];
            $refunded = (int) $bucket['refunded_sales'];
            $gross = (int) $bucket['gross_atomic'];
            $voidedAtomic = (int) $bucket['voided_atomic'];
            $refundedCash = (int) $bucket['refunded_cash_atomic'];
            if ($voided > $completed
                || $refunded > $voided
                || $voidedAtomic > $gross
                || $refundedCash > $voidedAtomic
                || ($bucket['tender_category'] === TenderCategory::MANUAL_EXTERNAL->value
                    && ($refunded !== 0 || $refundedCash !== 0))) {
                throw new PosTransactionViolation();
            }
            $rows[] = [
                'tender_category' => (string) $bucket['tender_category'],
                'currency' => (string) $bucket['currency'],
                'scale' => (int) $bucket['scale'],
                'completed_sales' => (string) $completed,
                'voided_sales' => (string) $voided,
                'active_sales' => (string) ($completed - $voided),
                'refunded_sales' => (string) $refunded,
                'gross_atomic' => (string) $gross,
                'voided_atomic' => (string) $voidedAtomic,
                'active_net_atomic' => (string) ($gross - $voidedAtomic),
                'refunded_cash_atomic' => (string) $refundedCash,
            ];
        }
        return $rows;
    }

    private function scopedSales(PosExecutionContext $context, string $shiftId, string $deviceId)
    {
        return $this->connection->table('oneqay_pos_sales')
            ->where('tenant_id', $context->tenantId())
            ->where('organization_id', $context->organizationId())
            ->where('outlet_id', $context->outletId())
            ->where('device_id', $deviceId)
            ->where('shift_id', $shiftId);
    }

    private function assertVoidIntegrity(PosExecutionContext $context, string $shiftId, string $deviceId): void
    {
        $invalid = (int) $this->connection->table('oneqay_pos_sales as sales')
            ->join('oneqay_pos_sale_voids as voids', function ($join): void {
                $join->on('voids.tenant_id', '=', 'sales.tenant_id')
                    ->on('voids.sale_id', '=', 'sales.sale_id');
            })
            ->where('sales.tenant_id', $context->tenantId())
            ->where('sales.organization_id', $context->organizationId())
            ->where('sales.outlet_id', $context->outletId())
            ->where('sales.device_id', $deviceId)
            ->where('sales.shift_id', $shiftId)
            ->where(function ($query): void {
                $query->whereColumn('voids.organization_id', '!=', 'sales.organization_id')
                    ->orWhereColumn('voids.outlet_id', '!=', 'sales.outlet_id')
                    ->orWhereColumn('voids.reversed_atomic', '!=', 'sales.applied_atomic')
                    ->orWhereColumn('voids.currency', '!=', 'sales.currency')
                    ->orWhereColumn('voids.currency_scale', '!=', 'sales.currency_scale')
                    ->orWhereColumn('voids.tender_category', '!=', 'sales.tender_category')
                    ->orWhere('voids.evidence_mode', '!=', self::VOID_MODE);
            })
            ->count();
        if ($invalid !== 0) {
            throw new PosTransactionViolation();
        }
    }

    private function assertRefundIntegrity(PosExecutionContext $context, string $shiftId, string $deviceId): void
    {
        $invalid = (int) $this->connection->table('oneqay_pos_sales as sales')
            ->join('oneqay_pos_sale_cash_refunds as refunds', function ($join): void {
                $join->on('refunds.tenant_id', '=', 'sales.tenant_id')
                    ->on('refunds.sale_id', '=', 'sales.sale_id');
            })
            ->leftJoin('oneqay_pos_sale_voids as voids', function ($join): void {
                $join->on('voids.tenant_id', '=', 'sales.tenant_id')
                    ->on('voids.sale_id', '=', 'sales.sale_id');
            })
            ->where('sales.tenant_id', $context->tenantId())
            ->where('sales.organization_id', $context->organizationId())
            ->where('sales.outlet_id', $context->outletId())
            ->where('sales.device_id', $deviceId)
            ->where('sales.shift_id', $shiftId)
            ->where(function ($query): void {
                $query->whereNull('voids.void_id')
                    ->orWhereColumn('refunds.void_id', '!=', 'voids.void_id')
                    ->orWhereColumn('refunds.organization_id', '!=', 'sales.organization_id')
                    ->orWhereColumn('refunds.outlet_id', '!=', 'sales.outlet_id')
                    ->orWhereColumn('refunds.refunded_atomic', '!=', 'sales.applied_atomic')
                    ->orWhereColumn('refunds.currency', '!=', 'sales.currency')
                    ->orWhereColumn('refunds.currency_scale', '!=', 'sales.currency_scale')
                    ->orWhere('refunds.tender_category', '!=', TenderCategory::CASH->value)
                    ->orWhere('sales.tender_category', '!=', TenderCategory::CASH->value)
                    ->orWhere('refunds.evidence_mode', '!=', self::REFUND_MODE);
            })
            ->count();
        if ($invalid !== 0) {
            throw new PosTransactionViolation();
        }
    }

    private function bucketKey(string $tender, string $currency, int $scale): string
    {
        return $tender.'|'.$currency.'|'.$scale;
    }

    private function tender(mixed $value): string
    {
        $tender = $this->requiredString($value);
        if (! in_array($tender, [TenderCategory::CASH->value, TenderCategory::MANUAL_EXTERNAL->value], true)) {
            throw new PosTransactionViolation();
        }
        return $tender;
    }

    private function currency(mixed $value): string
    {
        $currency = $this->requiredString($value);
        if (preg_match('/\A[A-Z]{3}\z/', $currency) !== 1) {
            throw new PosTransactionViolation();
        }
        return $currency;
    }

    private function scale(mixed $value): int
    {
        $scale = $this->safeNonNegativeInteger($value);
        if ($scale > 6) {
            throw new PosTransactionViolation();
        }
        return $scale;
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return $this->requiredString($value);
    }

    private function assertEquals(mixed $actual, string $expected): void
    {
        if (! is_string($actual) || ! hash_equals($expected, $actual)) {
            throw new PosTransactionViolation();
        }
    }

    private function safePositiveInteger(mixed $value): int
    {
        $integer = $this->safeNonNegativeInteger($value);
        if ($integer <= 0) {
            throw new PosTransactionViolation();
        }
        return $integer;
    }

    private function safeNonNegativeInteger(mixed $value): int
    {
        if (is_int($value)) {
            if ($value < 0) {
                throw new PosTransactionViolation();
            }
            return $value;
        }
        if (! is_string($value) || preg_match('/\A[0-9]+\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }
        $canonical = ltrim($value, '0');
        $canonical = $canonical === '' ? '0' : $canonical;
        $maximum = (string) PHP_INT_MAX;
        if (strlen($canonical) > strlen($maximum)
            || (strlen($canonical) === strlen($maximum) && strcmp($canonical, $maximum) > 0)) {
            throw new PosTransactionViolation();
        }
        return (int) $canonical;
    }

    private function safeSignedInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (! is_string($value) || preg_match('/\A-?[0-9]+\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }
        if ($value[0] !== '-') {
            return $this->safeNonNegativeInteger($value);
        }
        $magnitude = ltrim(substr($value, 1), '0');
        $magnitude = $magnitude === '' ? '0' : $magnitude;
        $minimumMagnitude = ltrim((string) PHP_INT_MIN, '-');
        if (strlen($magnitude) > strlen($minimumMagnitude)
            || (strlen($magnitude) === strlen($minimumMagnitude) && strcmp($magnitude, $minimumMagnitude) > 0)) {
            throw new PosTransactionViolation();
        }
        return (int) ('-'.$magnitude);
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled || ! $this->reportingEnabled || ! $this->featureEnabled || ! $this->shiftCloseEnabled) {
            throw new PosTransactionViolation();
        }
        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }
}
