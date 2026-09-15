<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosActiveShiftPerformanceWorkspaceRepository;
use App\Application\Pos\PosActiveShiftPerformanceWorkspaceSnapshot;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Pos\TenderCategory;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosActiveShiftPerformanceWorkspaceRepository implements PosActiveShiftPerformanceWorkspaceRepository
{
    private const MAX_BUCKETS = 64;
    private const VOID_MODE = 'FULL_SALE_VOID';
    private const REFUND_MODE = 'FULL_CASH_REFUND';

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $reportingEnabled,
        private bool $featureEnabled,
    ) {}

    public function read(PosExecutionContext $context): PosActiveShiftPerformanceWorkspaceSnapshot
    {
        $this->assertOperational();

        try {
            return $this->connection->transaction(
                fn (): PosActiveShiftPerformanceWorkspaceSnapshot => $this->readStableSnapshot($context),
                1,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function readStableSnapshot(PosExecutionContext $context): PosActiveShiftPerformanceWorkspaceSnapshot
    {
        $shiftRows = $this->connection->table('oneqay_pos_shifts')
            ->where('tenant_id', $context->tenantId())
            ->where('organization_id', $context->organizationId())
            ->where('outlet_id', $context->outletId())
            ->where('device_id', $context->deviceId())
            ->where('active_slot', 1)
            ->get(['shift_id', 'actor_identity_id', 'opened_at_unix']);

        if ($shiftRows->count() > 1) {
            throw new PosTransactionViolation();
        }

        $shift = $shiftRows->first();
        if ($shift === null) {
            return new PosActiveShiftPerformanceWorkspaceSnapshot(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $context->deviceId(),
                null,
                [],
            );
        }
        if (! is_object($shift)) {
            throw new PosTransactionViolation();
        }

        $shiftId = $this->requiredString($shift->shift_id ?? null);
        $openedAt = $this->safePositiveInteger($shift->opened_at_unix ?? null);
        $openerActorId = $this->requiredString($shift->actor_identity_id ?? null);

        $legacyUnbound = (int) $this->connection->table('oneqay_pos_sales')
            ->where('tenant_id', $context->tenantId())
            ->where('organization_id', $context->organizationId())
            ->where('outlet_id', $context->outletId())
            ->where('device_id', $context->deviceId())
            ->whereNull('shift_id')
            ->where('completed_at_unix', '>=', $openedAt)
            ->count();
        if ($legacyUnbound !== 0) {
            throw new PosTransactionViolation();
        }

        $allBoundCount = (int) $this->connection->table('oneqay_pos_sales')
            ->where('tenant_id', $context->tenantId())
            ->where('shift_id', $shiftId)
            ->count();
        $scopedBoundCount = (int) $this->scopedSales($context, $shiftId)->count();
        if ($allBoundCount !== $scopedBoundCount) {
            throw new PosTransactionViolation();
        }

        $invalidSaleCount = (int) $this->scopedSales($context, $shiftId)
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

        $this->assertVoidIntegrity($context, $shiftId);
        $this->assertRefundIntegrity($context, $shiftId);

        $buckets = [];
        $grossRows = $this->scopedSales($context, $shiftId)
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
            ->where('sales.device_id', $context->deviceId())
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
            ->where('sales.device_id', $context->deviceId())
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

        return new PosActiveShiftPerformanceWorkspaceSnapshot(
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            [
                'shift_id' => $shiftId,
                'opener_actor_identity_id' => $openerActorId,
                'opened_at_unix' => $openedAt,
            ],
            $rows,
        );
    }

    private function scopedSales(PosExecutionContext $context, string $shiftId)
    {
        return $this->connection->table('oneqay_pos_sales')
            ->where('tenant_id', $context->tenantId())
            ->where('organization_id', $context->organizationId())
            ->where('outlet_id', $context->outletId())
            ->where('device_id', $context->deviceId())
            ->where('shift_id', $shiftId);
    }

    private function assertVoidIntegrity(PosExecutionContext $context, string $shiftId): void
    {
        $invalid = (int) $this->connection->table('oneqay_pos_sales as sales')
            ->join('oneqay_pos_sale_voids as voids', function ($join): void {
                $join->on('voids.tenant_id', '=', 'sales.tenant_id')
                    ->on('voids.sale_id', '=', 'sales.sale_id');
            })
            ->where('sales.tenant_id', $context->tenantId())
            ->where('sales.organization_id', $context->organizationId())
            ->where('sales.outlet_id', $context->outletId())
            ->where('sales.device_id', $context->deviceId())
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

    private function assertRefundIntegrity(PosExecutionContext $context, string $shiftId): void
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
            ->where('sales.device_id', $context->deviceId())
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
        $tender = TenderCategory::tryFrom($this->requiredString($value));
        if (! $tender instanceof TenderCategory) {
            throw new PosTransactionViolation();
        }

        return $tender->value;
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

        return trim($value);
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

        if (! is_string($value) || $value === '' || ! ctype_digit($value)) {
            throw new PosTransactionViolation();
        }

        $normalized = ltrim($value, '0');
        $normalized = $normalized === '' ? '0' : $normalized;
        $maximum = (string) PHP_INT_MAX;
        if (strlen($normalized) > strlen($maximum)
            || (strlen($normalized) === strlen($maximum) && strcmp($normalized, $maximum) > 0)) {
            throw new PosTransactionViolation();
        }

        return (int) $normalized;
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled
            || ! $this->reportingEnabled
            || ! $this->featureEnabled
            || ! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }
}
