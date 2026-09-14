<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosSaleHistoryWorkspaceRepository;
use App\Application\Pos\PosSaleHistoryWorkspaceSnapshot;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Pos\TenderCategory;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosSaleHistoryWorkspaceRepository implements PosSaleHistoryWorkspaceRepository
{
    private const SALE_ID_PATTERN = '/\Asale-[a-f0-9]{24}\z/';
    private const HISTORY_LIMIT = 50;

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $reportingEnabled,
        private bool $historyEnabled,
    ) {}

    public function read(
        PosExecutionContext $context,
        ?string $selectedSaleId,
    ): PosSaleHistoryWorkspaceSnapshot {
        $this->assertOperational();

        if ($selectedSaleId !== null && preg_match(self::SALE_ID_PATTERN, $selectedSaleId) !== 1) {
            throw new PosTransactionViolation();
        }

        try {
            $recentRows = $this->scopedSalesQuery($context)
                ->orderByDesc('sales.completed_at_unix')
                ->orderByDesc('sales.sale_id')
                ->limit(self::HISTORY_LIMIT)
                ->get($this->saleColumns());

            $recentSales = [];
            foreach ($recentRows as $row) {
                $sale = $this->mapSale($row, $context);
                $recentSales[] = $this->historySummary($sale);
            }

            $selectedReceipt = null;
            if ($selectedSaleId !== null) {
                $selectedRow = $this->scopedSalesQuery($context)
                    ->where('sales.sale_id', $selectedSaleId)
                    ->first($this->saleColumns());

                if ($selectedRow !== null) {
                    $sale = $this->mapSale($selectedRow, $context);
                    $selectedReceipt = $this->receipt($context, $sale);
                }
            }

            return new PosSaleHistoryWorkspaceSnapshot(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $selectedSaleId,
                $recentSales,
                $selectedReceipt,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function scopedSalesQuery(PosExecutionContext $context)
    {
        return $this->connection->table('oneqay_pos_sales as sales')
            ->leftJoin('oneqay_pos_sale_voids as voids', function ($join): void {
                $join->on('voids.tenant_id', '=', 'sales.tenant_id')
                    ->on('voids.sale_id', '=', 'sales.sale_id');
            })
            ->leftJoin('oneqay_pos_sale_cash_refunds as refunds', function ($join): void {
                $join->on('refunds.tenant_id', '=', 'sales.tenant_id')
                    ->on('refunds.sale_id', '=', 'sales.sale_id');
            })
            ->where('sales.tenant_id', $context->tenantId())
            ->where('sales.organization_id', $context->organizationId())
            ->where('sales.outlet_id', $context->outletId());
    }

    /** @return list<string> */
    private function saleColumns(): array
    {
        return [
            'sales.sale_id',
            'sales.device_id',
            'sales.shift_id',
            'sales.total_atomic',
            'sales.currency',
            'sales.currency_scale',
            'sales.tender_category',
            'sales.evidence_mode',
            'sales.applied_atomic',
            'sales.change_atomic',
            'sales.completed_at_unix',
            'voids.void_id',
            'voids.organization_id as void_organization_id',
            'voids.outlet_id as void_outlet_id',
            'voids.reversed_atomic',
            'voids.currency as void_currency',
            'voids.currency_scale as void_currency_scale',
            'voids.tender_category as void_tender_category',
            'voids.evidence_mode as void_evidence_mode',
            'voids.voided_at_unix',
            'refunds.refund_id',
            'refunds.void_id as refund_void_id',
            'refunds.organization_id as refund_organization_id',
            'refunds.outlet_id as refund_outlet_id',
            'refunds.refunded_atomic',
            'refunds.currency as refund_currency',
            'refunds.currency_scale as refund_currency_scale',
            'refunds.tender_category as refund_tender_category',
            'refunds.evidence_mode as refund_evidence_mode',
            'refunds.refunded_at_unix',
        ];
    }

    /** @return array<string, mixed> */
    private function mapSale(object $row, PosExecutionContext $context): array
    {
        $saleId = $this->requiredString($row->sale_id ?? null);
        if (preg_match(self::SALE_ID_PATTERN, $saleId) !== 1) {
            throw new PosTransactionViolation();
        }

        $deviceId = $this->requiredString($row->device_id ?? null);
        $shiftId = $this->nullableString($row->shift_id ?? null);
        $currency = $this->currency($row->currency ?? null);
        $scale = $this->scale($row->currency_scale ?? null);
        $tender = TenderCategory::from($this->requiredString($row->tender_category ?? null));
        $evidenceMode = $this->requiredString($row->evidence_mode ?? null);
        $total = $this->safeNonNegativeInteger($row->total_atomic ?? null);
        $applied = $this->safeNonNegativeInteger($row->applied_atomic ?? null);
        $change = $this->safeNonNegativeInteger($row->change_atomic ?? null);
        $completedAt = $this->safePositiveInteger($row->completed_at_unix ?? null);

        if ($applied !== $total) {
            throw new PosTransactionViolation();
        }
        if ($tender === TenderCategory::MANUAL_EXTERNAL && $change !== 0) {
            throw new PosTransactionViolation();
        }

        $voidId = $this->nullableString($row->void_id ?? null);
        $refundId = $this->nullableString($row->refund_id ?? null);
        $voidedAt = null;
        $refundedAt = null;

        if ($voidId !== null) {
            $voidedAt = $this->safePositiveInteger($row->voided_at_unix ?? null);
            if (! hash_equals($context->organizationId(), $this->requiredString($row->void_organization_id ?? null))
                || ! hash_equals($context->outletId(), $this->requiredString($row->void_outlet_id ?? null))
                || $voidedAt < $completedAt
                || $this->safeNonNegativeInteger($row->reversed_atomic ?? null) !== $applied
                || ! hash_equals($currency, $this->currency($row->void_currency ?? null))
                || $scale !== $this->scale($row->void_currency_scale ?? null)
                || ! hash_equals($tender->value, $this->requiredString($row->void_tender_category ?? null))
                || $this->requiredString($row->void_evidence_mode ?? null) !== 'FULL_SALE_VOID') {
                throw new PosTransactionViolation();
            }
        }

        if ($refundId !== null) {
            if ($voidId === null || $tender !== TenderCategory::CASH) {
                throw new PosTransactionViolation();
            }
            $refundVoidId = $this->requiredString($row->refund_void_id ?? null);
            $refundedAt = $this->safePositiveInteger($row->refunded_at_unix ?? null);
            if (! hash_equals($context->organizationId(), $this->requiredString($row->refund_organization_id ?? null))
                || ! hash_equals($context->outletId(), $this->requiredString($row->refund_outlet_id ?? null))
                || ! hash_equals($voidId, $refundVoidId)
                || $voidedAt === null
                || $refundedAt < $voidedAt
                || $this->safeNonNegativeInteger($row->refunded_atomic ?? null) !== $applied
                || ! hash_equals($currency, $this->currency($row->refund_currency ?? null))
                || $scale !== $this->scale($row->refund_currency_scale ?? null)
                || $this->requiredString($row->refund_tender_category ?? null) !== TenderCategory::CASH->value
                || $this->requiredString($row->refund_evidence_mode ?? null) !== 'FULL_CASH_REFUND') {
                throw new PosTransactionViolation();
            }
        }

        $state = $refundId !== null ? 'REFUNDED' : ($voidId !== null ? 'VOIDED' : 'COMPLETED');

        return [
            'sale_id' => $saleId,
            'device_id' => $deviceId,
            'shift_id' => $shiftId,
            'total' => $total,
            'currency' => $currency,
            'scale' => $scale,
            'tender_category' => $tender->value,
            'evidence_mode' => $evidenceMode,
            'applied' => $applied,
            'change' => $change,
            'completed_at_unix' => $completedAt,
            'state' => $state,
            'void_id' => $voidId,
            'voided_at_unix' => $voidedAt,
            'refund_id' => $refundId,
            'refunded_at_unix' => $refundedAt,
        ];
    }

    /** @param array<string, mixed> $sale @return array<string, mixed> */
    private function historySummary(array $sale): array
    {
        return [
            'sale_id' => $sale['sale_id'],
            'completed_at_unix' => $sale['completed_at_unix'],
            'total_atomic' => (string) $sale['total'],
            'currency' => $sale['currency'],
            'scale' => $sale['scale'],
            'tender_category' => $sale['tender_category'],
            'state' => $sale['state'],
            'shift_id' => $sale['shift_id'],
            'device_id' => $sale['device_id'],
            'voided_at_unix' => $sale['voided_at_unix'],
            'refunded_at_unix' => $sale['refunded_at_unix'],
        ];
    }

    /** @param array<string, mixed> $sale @return array<string, mixed> */
    private function receipt(PosExecutionContext $context, array $sale): array
    {
        $lineRows = $this->connection->table('oneqay_pos_sale_lines')
            ->where('tenant_id', $context->tenantId())
            ->where('sale_id', $sale['sale_id'])
            ->orderBy('line_no')
            ->get([
                'line_no',
                'product_id',
                'quantity',
                'unit_price_atomic',
                'line_total_atomic',
                'currency',
                'currency_scale',
            ]);

        if ($lineRows->count() === 0) {
            throw new PosTransactionViolation();
        }

        $lines = [];
        $expectedLineNo = 1;
        $sum = 0;
        foreach ($lineRows as $line) {
            $lineNo = $this->safePositiveInteger($line->line_no ?? null);
            $quantity = $this->safePositiveInteger($line->quantity ?? null);
            $unitPrice = $this->safeNonNegativeInteger($line->unit_price_atomic ?? null);
            $lineTotal = $this->safeNonNegativeInteger($line->line_total_atomic ?? null);
            $lineCurrency = $this->currency($line->currency ?? null);
            $lineScale = $this->scale($line->currency_scale ?? null);

            if ($lineNo !== $expectedLineNo
                || ! hash_equals((string) $sale['currency'], $lineCurrency)
                || (int) $sale['scale'] !== $lineScale
                || $this->safeMultiply($unitPrice, $quantity) !== $lineTotal) {
                throw new PosTransactionViolation();
            }

            $sum = $this->safeAdd($sum, $lineTotal);
            $lines[] = [
                'line_no' => $lineNo,
                'product_id' => $this->requiredString($line->product_id ?? null),
                'quantity' => $quantity,
                'unit_price_atomic' => (string) $unitPrice,
                'line_total_atomic' => (string) $lineTotal,
                'currency' => $lineCurrency,
                'scale' => $lineScale,
            ];
            $expectedLineNo++;
        }

        if ($sum !== (int) $sale['total']) {
            throw new PosTransactionViolation();
        }

        return [
            'sale_id' => $sale['sale_id'],
            'completed_at_unix' => $sale['completed_at_unix'],
            'total_atomic' => (string) $sale['total'],
            'currency' => $sale['currency'],
            'scale' => $sale['scale'],
            'tender_category' => $sale['tender_category'],
            'evidence_mode' => $sale['evidence_mode'],
            'applied_atomic' => (string) $sale['applied'],
            'change_atomic' => (string) $sale['change'],
            'state' => $sale['state'],
            'shift_id' => $sale['shift_id'],
            'device_id' => $sale['device_id'],
            'void_id' => $sale['void_id'],
            'voided_at_unix' => $sale['voided_at_unix'],
            'refund_id' => $sale['refund_id'],
            'refunded_at_unix' => $sale['refunded_at_unix'],
            'lines' => $lines,
        ];
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled
            || ! $this->reportingEnabled
            || ! $this->historyEnabled
            || ! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new PosTransactionViolation();
        }
        return trim($value);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return $this->requiredString($value);
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
        if ($scale > 255) {
            throw new PosTransactionViolation();
        }
        return $scale;
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

    private function safeAdd(int $left, int $right): int
    {
        if ($right > PHP_INT_MAX - $left) {
            throw new PosTransactionViolation();
        }
        return $left + $right;
    }

    private function safeMultiply(int $left, int $right): int
    {
        if ($left !== 0 && $right > intdiv(PHP_INT_MAX, $left)) {
            throw new PosTransactionViolation();
        }
        return $left * $right;
    }
}
