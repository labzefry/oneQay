<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosCatalogInventorySetupWorkspaceRepository;
use App\Application\Pos\PosCatalogInventorySetupWorkspaceSnapshot;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosCatalogInventorySetupWorkspaceRepository implements PosCatalogInventorySetupWorkspaceRepository
{
    private const ITEM_LIMIT = 250;

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $workspaceEnabled,
        private bool $catalogPreparationEnabled,
        private bool $inventoryBaselineEnabled,
    ) {}

    public function read(PosExecutionContext $context): PosCatalogInventorySetupWorkspaceSnapshot
    {
        $this->assertOperational();

        try {
            $catalogRows = $this->connection->table('oneqay_pos_sale_catalog_items')
                ->where('tenant_id', $context->tenantId())
                ->where('outlet_id', $context->outletId())
                ->orderBy('display_name')
                ->orderBy('product_id')
                ->limit(self::ITEM_LIMIT)
                ->get([
                    'product_id',
                    'display_name',
                    'unit_price_atomic',
                    'currency',
                    'currency_scale',
                    'available_quantity',
                    'active',
                ]);

            $baselineProducts = $this->stringSet(
                $this->connection->table('oneqay_pos_inventory_baselines')
                    ->where('tenant_id', $context->tenantId())
                    ->where('outlet_id', $context->outletId())
                    ->distinct()
                    ->pluck('product_id')
                    ->all(),
            );

            // Match the canonical baseline mutation rule: any sale history in this
            // tenant/outlet makes opening inventory ineligible, regardless of which
            // authorized organization previously completed the sale.
            $saleHistoryProducts = $this->stringSet(
                $this->connection->table('oneqay_pos_sale_lines as lines')
                    ->join('oneqay_pos_sales as sales', function ($join): void {
                        $join->on('sales.tenant_id', '=', 'lines.tenant_id')
                            ->on('sales.sale_id', '=', 'lines.sale_id');
                    })
                    ->where('lines.tenant_id', $context->tenantId())
                    ->where('sales.outlet_id', $context->outletId())
                    ->distinct()
                    ->pluck('lines.product_id')
                    ->all(),
            );

            $items = [];
            foreach ($catalogRows as $row) {
                $productId = $this->requiredString($row->product_id ?? null);
                $displayName = $this->requiredString($row->display_name ?? null);
                $unitPriceAtomic = $this->unsignedString($row->unit_price_atomic ?? null);
                $currency = strtoupper($this->requiredString($row->currency ?? null));
                $scale = $this->smallInteger($row->currency_scale ?? null, 0, 6);
                $availableQuantity = $this->unsignedString($row->available_quantity ?? null);
                $sellable = $this->boolean($row->active ?? null);

                if (preg_match('/\A[A-Z]{3}\z/', $currency) !== 1) {
                    throw new PosTransactionViolation();
                }

                $baselineEstablished = isset($baselineProducts[$productId]);
                $saleHistoryExists = isset($saleHistoryProducts[$productId]);
                $baselineEligible = $availableQuantity === '0'
                    && ! $baselineEstablished
                    && ! $saleHistoryExists;

                $items[] = [
                    'product_id' => $productId,
                    'display_name' => $displayName,
                    'unit_price_atomic' => $unitPriceAtomic,
                    'currency' => $currency,
                    'scale' => $scale,
                    'available_quantity' => $availableQuantity,
                    'sellable' => $sellable,
                    'baseline_established' => $baselineEstablished,
                    'sale_history_exists' => $saleHistoryExists,
                    'baseline_eligible' => $baselineEligible,
                ];
            }

            return new PosCatalogInventorySetupWorkspaceSnapshot(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $context->deviceId(),
                $items,
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
            || ! $this->workspaceEnabled
            || ! $this->catalogPreparationEnabled
            || ! $this->inventoryBaselineEnabled
            || ! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }

    /** @param list<mixed> $values @return array<string, true> */
    private function stringSet(array $values): array
    {
        $set = [];
        foreach ($values as $value) {
            $set[$this->requiredString($value)] = true;
        }
        return $set;
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new PosTransactionViolation();
        }
        return trim($value);
    }

    private function unsignedString(mixed $value): string
    {
        if (is_int($value)) {
            if ($value < 0) {
                throw new PosTransactionViolation();
            }
            return (string) $value;
        }
        if (! is_string($value) || preg_match('/\A[0-9]+\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }
        $normalized = ltrim($value, '0');
        return $normalized === '' ? '0' : $normalized;
    }

    private function smallInteger(mixed $value, int $minimum, int $maximum): int
    {
        if (is_string($value) && preg_match('/\A[0-9]+\z/', $value) === 1) {
            $value = (int) $value;
        }
        if (! is_int($value) || $value < $minimum || $value > $maximum) {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function boolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === 0 || $value === '0') {
            return false;
        }
        if ($value === 1 || $value === '1') {
            return true;
        }
        throw new PosTransactionViolation();
    }
}
