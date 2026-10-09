<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosProductSalesPerformanceWorkspaceRepository;
use App\Application\Pos\PosProductSalesPerformanceWorkspaceSnapshot;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosProductSalesPerformanceWorkspaceRepository implements PosProductSalesPerformanceWorkspaceRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $reportingEnabled,
        private bool $featureEnabled,
    ) {}

    public function read(PosExecutionContext $context): PosProductSalesPerformanceWorkspaceSnapshot
    {
        $this->assertOperational();

        try {
            $rows = $this->connection->table('oneqay_pos_sale_lines as lines')
                ->join('oneqay_pos_sales as sales', function ($join): void {
                    $join->on('sales.tenant_id', '=', 'lines.tenant_id')
                        ->on('sales.sale_id', '=', 'lines.sale_id');
                })
                ->leftJoin('oneqay_pos_sale_voids as voids', function ($join): void {
                    $join->on('voids.tenant_id', '=', 'sales.tenant_id')
                        ->on('voids.sale_id', '=', 'sales.sale_id');
                })
                ->leftJoin('oneqay_pos_sale_catalog_items as catalog', function ($join): void {
                    $join->on('catalog.tenant_id', '=', 'sales.tenant_id')
                        ->on('catalog.outlet_id', '=', 'sales.outlet_id')
                        ->on('catalog.product_id', '=', 'lines.product_id');
                })
                ->where('sales.tenant_id', $context->tenantId())
                ->where('sales.organization_id', $context->organizationId())
                ->where('sales.outlet_id', $context->outletId())
                ->selectRaw(
                    'lines.product_id, catalog.display_name, lines.currency, lines.currency_scale, '.
                    'SUM(lines.quantity) AS gross_quantity, '.
                    'SUM(CASE WHEN voids.void_id IS NULL THEN 0 ELSE lines.quantity END) AS voided_quantity, '.
                    'SUM(lines.line_total_atomic) AS gross_atomic, '.
                    'SUM(CASE WHEN voids.void_id IS NULL THEN 0 ELSE lines.line_total_atomic END) AS voided_atomic, '.
                    'MIN(lines.quantity) AS min_quantity, '.
                    'MIN(lines.unit_price_atomic) AS min_unit_price_atomic, '.
                    'MIN(lines.line_total_atomic) AS min_line_total_atomic'
                )
                ->groupBy('lines.product_id', 'catalog.display_name', 'lines.currency', 'lines.currency_scale')
                ->orderByDesc('gross_quantity')
                ->orderBy('lines.product_id')
                ->orderBy('lines.currency')
                ->orderBy('lines.currency_scale')
                ->limit(251)
                ->get();

            $truncated = $rows->count() > 250;
            if ($truncated) {
                $rows = $rows->take(250);
            }

            $performance = [];
            foreach ($rows as $row) {
                $productId = is_string($row->product_id) ? trim($row->product_id) : '';
                $displayName = is_string($row->display_name) ? trim($row->display_name) : '';
                $currency = is_string($row->currency) ? strtoupper(trim($row->currency)) : '';
                $scale = $this->safeNonNegativeInteger($row->currency_scale);
                $grossQuantity = $this->safeNonNegativeInteger($row->gross_quantity);
                $voidedQuantity = $this->safeNonNegativeInteger($row->voided_quantity);
                $grossAtomic = $this->safeNonNegativeInteger($row->gross_atomic);
                $voidedAtomic = $this->safeNonNegativeInteger($row->voided_atomic);
                $minQuantity = $this->safeNonNegativeInteger($row->min_quantity);
                $minUnitPrice = $this->safeNonNegativeInteger($row->min_unit_price_atomic);
                $minLineTotal = $this->safeNonNegativeInteger($row->min_line_total_atomic);

                if ($productId === ''
                    || $displayName === ''
                    || preg_match('/\A[A-Z]{3}\z/', $currency) !== 1
                    || $scale > 6
                    || $grossQuantity <= 0
                    || $minQuantity <= 0
                    || $minUnitPrice < 0
                    || $minLineTotal < 0
                    || $voidedQuantity > $grossQuantity
                    || $voidedAtomic > $grossAtomic) {
                    throw new PosTransactionViolation();
                }

                $performance[] = [
                    'product_id' => $productId,
                    'display_name' => $displayName,
                    'currency' => $currency,
                    'scale' => $scale,
                    'gross_quantity' => (string) $grossQuantity,
                    'voided_quantity' => (string) $voidedQuantity,
                    'net_quantity' => (string) ($grossQuantity - $voidedQuantity),
                    'gross_atomic' => (string) $grossAtomic,
                    'voided_atomic' => (string) $voidedAtomic,
                    'net_atomic' => (string) ($grossAtomic - $voidedAtomic),
                ];
            }

            return new PosProductSalesPerformanceWorkspaceSnapshot(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $performance,
                $truncated,
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
            || ! $this->reportingEnabled
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
        $maximum = (string) PHP_INT_MAX;
        if (strlen($normalized) > strlen($maximum)
            || (strlen($normalized) === strlen($maximum) && strcmp($normalized, $maximum) > 0)) {
            throw new PosTransactionViolation();
        }

        return (int) $normalized;
    }
}
