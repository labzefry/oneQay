<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosOperationalSalesSummary;
use App\Application\Pos\PosOperationalSalesSummaryRepository;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosOperationalSalesSummaryRepository implements PosOperationalSalesSummaryRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function summarize(PosExecutionContext $context): PosOperationalSalesSummary
    {
        $this->assertOperational();

        try {
            $scope = static fn ($query) => $query
                ->where('tenant_id', $context->tenantId())
                ->where('organization_id', $context->organizationId())
                ->where('outlet_id', $context->outletId());

            $completedSales = (int) $scope($this->connection->table('oneqay_pos_sales'))->count();
            $voidedSales = (int) $scope($this->connection->table('oneqay_pos_sale_voids'))->count();
            $cashRefundedSales = (int) $scope($this->connection->table('oneqay_pos_sale_cash_refunds'))->count();

            $grossTotals = [];
            $grossRows = $scope($this->connection->table('oneqay_pos_sales'))
                ->selectRaw('currency, currency_scale, SUM(total_atomic) AS gross_atomic')
                ->groupBy('currency', 'currency_scale')
                ->orderBy('currency')
                ->orderBy('currency_scale')
                ->get();

            foreach ($grossRows as $row) {
                $currency = is_string($row->currency) ? trim($row->currency) : '';
                $scale = (int) $row->currency_scale;
                $grossAtomic = $this->safeNonNegativeInteger($row->gross_atomic);
                if ($currency === '' || $scale < 0) {
                    throw new PosTransactionViolation();
                }
                $grossTotals[] = [
                    'currency' => $currency,
                    'scale' => $scale,
                    'gross_atomic' => $grossAtomic,
                ];
            }

            $recentSales = [];
            $recentRows = $this->connection->table('oneqay_pos_sales as sales')
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
                ->where('sales.outlet_id', $context->outletId())
                ->orderByDesc('sales.completed_at_unix')
                ->orderByDesc('sales.sale_id')
                ->limit(10)
                ->get([
                    'sales.sale_id',
                    'sales.completed_at_unix',
                    'sales.total_atomic',
                    'sales.currency',
                    'sales.currency_scale',
                    'sales.tender_category',
                    'voids.void_id',
                    'refunds.refund_id',
                ]);

            foreach ($recentRows as $row) {
                $state = $row->refund_id !== null ? 'REFUNDED' : ($row->void_id !== null ? 'VOIDED' : 'COMPLETED');
                $saleId = is_string($row->sale_id) ? trim($row->sale_id) : '';
                $currency = is_string($row->currency) ? trim($row->currency) : '';
                $tenderCategory = is_string($row->tender_category) ? trim($row->tender_category) : '';
                if ($saleId === '' || $currency === '' || $tenderCategory === '') {
                    throw new PosTransactionViolation();
                }
                $recentSales[] = [
                    'sale_id' => $saleId,
                    'completed_at_unix' => $this->safeNonNegativeInteger($row->completed_at_unix),
                    'total_atomic' => $this->safeNonNegativeInteger($row->total_atomic),
                    'currency' => $currency,
                    'scale' => (int) $row->currency_scale,
                    'tender_category' => $tenderCategory,
                    'state' => $state,
                ];
            }

            return new PosOperationalSalesSummary(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $completedSales,
                $voidedSales,
                $cashRefundedSales,
                $grossTotals,
                $recentSales,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled
            || ! $this->featureEnabled
            || ! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
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
        $max = (string) PHP_INT_MAX;
        if (strlen($normalized) > strlen($max)
            || (strlen($normalized) === strlen($max) && strcmp($normalized, $max) > 0)) {
            throw new PosTransactionViolation();
        }

        return (int) $normalized;
    }
}