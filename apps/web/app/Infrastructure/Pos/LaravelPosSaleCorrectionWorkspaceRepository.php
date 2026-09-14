<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosSaleCorrectionWorkspaceRepository;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosSaleCorrectionWorkspaceRepository implements PosSaleCorrectionWorkspaceRepository
{
    private const LIMIT = 50;

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $workspaceEnabled,
        private bool $saleCompletionEnabled,
        private bool $voidEnabled,
        private bool $cashRefundEnabled,
    ) {}

    public function recent(PosExecutionContext $context): array
    {
        $this->assertOperational();

        try {
            $sales = $this->connection->table('oneqay_pos_sales')
                ->where('tenant_id', $context->tenantId())
                ->where('organization_id', $context->organizationId())
                ->where('outlet_id', $context->outletId())
                ->orderByDesc('completed_at_unix')
                ->limit(self::LIMIT)
                ->get();

            if ($sales->count() === 0) {
                return [];
            }

            $saleIds = [];
            $shiftIds = [];
            foreach ($sales as $sale) {
                $saleId = $this->requiredString($sale->sale_id ?? null);
                $shiftId = $this->requiredString($sale->shift_id ?? null);
                if (isset($saleIds[$saleId])) {
                    throw new PosTransactionViolation();
                }
                $saleIds[$saleId] = true;
                $shiftIds[$shiftId] = true;
            }

            $voids = $this->connection->table('oneqay_pos_sale_voids')
                ->where('tenant_id', $context->tenantId())
                ->whereIn('sale_id', array_keys($saleIds))
                ->get();
            $refunds = $this->connection->table('oneqay_pos_sale_cash_refunds')
                ->where('tenant_id', $context->tenantId())
                ->whereIn('sale_id', array_keys($saleIds))
                ->get();
            $activeShifts = $this->connection->table('oneqay_pos_shifts')
                ->where('tenant_id', $context->tenantId())
                ->whereIn('shift_id', array_keys($shiftIds))
                ->where('active_slot', 1)
                ->get();

            $voidBySale = $this->uniqueBySale($voids->all());
            $refundBySale = $this->uniqueBySale($refunds->all());
            $activeShiftById = [];
            foreach ($activeShifts as $shift) {
                $shiftId = $this->requiredString($shift->shift_id ?? null);
                if (isset($activeShiftById[$shiftId])) {
                    throw new PosTransactionViolation();
                }
                $activeShiftById[$shiftId] = $shift;
            }

            $rows = [];
            foreach ($sales as $sale) {
                $saleId = $this->requiredString($sale->sale_id ?? null);
                $organizationId = $this->requiredString($sale->organization_id ?? null);
                $outletId = $this->requiredString($sale->outlet_id ?? null);
                if (! hash_equals($context->organizationId(), $organizationId)
                    || ! hash_equals($context->outletId(), $outletId)) {
                    throw new PosTransactionViolation();
                }

                $shiftId = $this->requiredString($sale->shift_id ?? null);
                $originalDeviceId = $this->requiredString($sale->device_id ?? null);
                $amountAtomic = $this->safeUnsignedBigIntToInt($sale->applied_atomic ?? null);
                $currency = $this->requiredString($sale->currency ?? null);
                $scale = $this->safeScale($sale->currency_scale ?? null);
                $tender = $this->requiredString($sale->tender_category ?? null);
                if (! in_array($tender, ['CASH', 'MANUAL_EXTERNAL'], true)) {
                    throw new PosTransactionViolation();
                }
                $completedAtUnix = $this->safePositiveBigIntToInt($sale->completed_at_unix ?? null);

                $shift = $activeShiftById[$shiftId] ?? null;
                $shiftActive = $shift !== null
                    && hash_equals($organizationId, $this->requiredString($shift->organization_id ?? null))
                    && hash_equals($outletId, $this->requiredString($shift->outlet_id ?? null));

                $void = $voidBySale[$saleId] ?? null;
                $refund = $refundBySale[$saleId] ?? null;

                $voidId = null;
                $voidedAtUnix = null;
                if ($void !== null) {
                    $this->assertEvidenceScope($void, $organizationId, $outletId);
                    $voidId = $this->requiredString($void->void_id ?? null);
                    if (preg_match('/\Avoid-[a-f0-9]{24}\z/', $voidId) !== 1
                        || $this->safeUnsignedBigIntToInt($void->reversed_atomic ?? null) !== $amountAtomic
                        || ! hash_equals($currency, $this->requiredString($void->currency ?? null))
                        || $this->safeScale($void->currency_scale ?? null) !== $scale
                        || ! hash_equals($tender, $this->requiredString($void->tender_category ?? null))
                        || ! hash_equals('FULL_SALE_VOID', $this->requiredString($void->evidence_mode ?? null))) {
                        throw new PosTransactionViolation();
                    }
                    $voidedAtUnix = $this->safePositiveBigIntToInt($void->voided_at_unix ?? null);
                }

                $refundId = null;
                $refundedAtUnix = null;
                if ($refund !== null) {
                    if ($void === null || $tender !== 'CASH') {
                        throw new PosTransactionViolation();
                    }
                    $this->assertEvidenceScope($refund, $organizationId, $outletId);
                    $refundId = $this->requiredString($refund->refund_id ?? null);
                    if (preg_match('/\Arefund-[a-f0-9]{24}\z/', $refundId) !== 1
                        || ! hash_equals($voidId ?? '', $this->requiredString($refund->void_id ?? null))
                        || $this->safeUnsignedBigIntToInt($refund->refunded_atomic ?? null) !== $amountAtomic
                        || ! hash_equals($currency, $this->requiredString($refund->currency ?? null))
                        || $this->safeScale($refund->currency_scale ?? null) !== $scale
                        || ! hash_equals('CASH', $this->requiredString($refund->tender_category ?? null))
                        || ! hash_equals('FULL_CASH_REFUND', $this->requiredString($refund->evidence_mode ?? null))) {
                        throw new PosTransactionViolation();
                    }
                    $refundedAtUnix = $this->safePositiveBigIntToInt($refund->refunded_at_unix ?? null);
                }

                $rows[] = [
                    'sale_id' => $saleId,
                    'completed_at_unix' => $completedAtUnix,
                    'amount_atomic' => $amountAtomic,
                    'currency' => $currency,
                    'scale' => $scale,
                    'tender_category' => $tender,
                    'original_device_id' => $originalDeviceId,
                    'state' => $refund !== null ? 'REFUNDED' : ($void !== null ? 'VOIDED' : 'COMPLETED'),
                    'shift_active' => $shiftActive,
                    'void_id' => $voidId,
                    'voided_at_unix' => $voidedAtUnix,
                    'refund_id' => $refundId,
                    'refunded_at_unix' => $refundedAtUnix,
                ];
            }

            return $rows;
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    /** @param list<object> $rows @return array<string, object> */
    private function uniqueBySale(array $rows): array
    {
        $mapped = [];
        foreach ($rows as $row) {
            $saleId = $this->requiredString($row->sale_id ?? null);
            if (isset($mapped[$saleId])) {
                throw new PosTransactionViolation();
            }
            $mapped[$saleId] = $row;
        }
        return $mapped;
    }

    private function assertEvidenceScope(object $row, string $organizationId, string $outletId): void
    {
        if (! hash_equals($organizationId, $this->requiredString($row->organization_id ?? null))
            || ! hash_equals($outletId, $this->requiredString($row->outlet_id ?? null))) {
            throw new PosTransactionViolation();
        }
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled
            || ! $this->workspaceEnabled
            || ! $this->saleCompletionEnabled
            || ! $this->voidEnabled
            || ! $this->cashRefundEnabled
            || ! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function safeScale(mixed $value): int
    {
        $scale = $this->safeUnsignedBigIntToInt($value);
        if ($scale > 6) {
            throw new PosTransactionViolation();
        }
        return $scale;
    }

    private function safePositiveBigIntToInt(mixed $value): int
    {
        $number = $this->safeUnsignedBigIntToInt($value);
        if ($number <= 0) {
            throw new PosTransactionViolation();
        }
        return $number;
    }

    private function safeUnsignedBigIntToInt(mixed $value): int
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

        $normalized = ltrim($value, '0');
        $normalized = $normalized === '' ? '0' : $normalized;
        $maximum = (string) PHP_INT_MAX;
        if (strlen($normalized) > strlen($maximum)
            || (strlen($normalized) === strlen($maximum) && strcmp($normalized, $maximum) > 0)) {
            throw new PosTransactionViolation();
        }

        return (int) $normalized;
    }
}
