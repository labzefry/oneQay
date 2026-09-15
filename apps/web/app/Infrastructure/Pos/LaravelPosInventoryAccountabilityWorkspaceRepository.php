<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosInventoryAccountabilityWorkspaceRepository;
use App\Application\Pos\PosInventoryAccountabilityWorkspaceSnapshot;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosInventoryAccountabilityWorkspaceRepository implements PosInventoryAccountabilityWorkspaceRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function read(PosExecutionContext $context): PosInventoryAccountabilityWorkspaceSnapshot
    {
        $this->assertOperational();

        try {
            $catalogRows = $this->connection->table('oneqay_pos_sale_catalog_items as catalog')
                ->join('oneqay_pos_inventory_baselines as baseline', function ($join): void {
                    $join->on('baseline.tenant_id', '=', 'catalog.tenant_id')
                        ->on('baseline.outlet_id', '=', 'catalog.outlet_id')
                        ->on('baseline.product_id', '=', 'catalog.product_id');
                })
                ->where('catalog.tenant_id', $context->tenantId())
                ->where('catalog.outlet_id', $context->outletId())
                ->where('baseline.organization_id', $context->organizationId())
                ->orderBy('catalog.display_name')
                ->orderBy('catalog.product_id')
                ->limit(500)
                ->get([
                    'catalog.product_id',
                    'catalog.display_name',
                    'catalog.available_quantity',
                    'catalog.active',
                    'baseline.baseline_id',
                    'baseline.opening_quantity',
                    'baseline.occurred_at_unix',
                ]);

            $products = [];
            $movements = [];

            foreach ($catalogRows as $row) {
                $productId = $this->requiredString($row->product_id);
                $displayName = $this->requiredString($row->display_name);
                if (isset($products[$productId])) {
                    throw new PosTransactionViolation();
                }

                $opening = $this->safeUnsignedBigIntToInt($row->opening_quantity);
                $current = $this->safeUnsignedBigIntToInt($row->available_quantity);
                $occurredAt = $this->positiveUnix($row->occurred_at_unix);
                $baselineId = $this->requiredString($row->baseline_id);

                $products[$productId] = [
                    'product_id' => $productId,
                    'display_name' => $displayName,
                    'active' => (bool) $row->active,
                    'current' => $current,
                    'opening' => $opening,
                    'replenished' => 0,
                    'sold' => 0,
                    'restored' => 0,
                ];

                $movements[] = $this->movement(
                    'OPENING_BASELINE',
                    $baselineId,
                    $productId,
                    $displayName,
                    'IN',
                    $opening,
                    $occurredAt,
                    $baselineId,
                );
            }

            $replenishmentRows = $this->connection->table('oneqay_pos_inventory_replenishments')
                ->where('tenant_id', $context->tenantId())
                ->where('organization_id', $context->organizationId())
                ->where('outlet_id', $context->outletId())
                ->get();

            foreach ($replenishmentRows as $row) {
                $productId = $this->requiredKnownProduct($products, $row->product_id);
                $before = $this->safeUnsignedBigIntToInt($row->before_available_quantity);
                $quantity = $this->safeUnsignedBigIntToInt($row->replenished_quantity);
                $after = $this->safeUnsignedBigIntToInt($row->after_available_quantity);
                if ($quantity <= 0 || $quantity > PHP_INT_MAX - $before || $before + $quantity !== $after) {
                    throw new PosTransactionViolation();
                }

                $products[$productId]['replenished'] = $this->safeAdd(
                    $products[$productId]['replenished'],
                    $quantity,
                );

                $replenishmentId = $this->requiredString($row->replenishment_id);
                $movements[] = $this->movement(
                    'REPLENISHMENT',
                    $replenishmentId,
                    $productId,
                    $products[$productId]['display_name'],
                    'IN',
                    $quantity,
                    $this->positiveUnix($row->occurred_at_unix),
                    $replenishmentId,
                );
            }

            $saleRows = $this->connection->table('oneqay_pos_sale_lines as lines')
                ->join('oneqay_pos_sales as sales', function ($join): void {
                    $join->on('sales.tenant_id', '=', 'lines.tenant_id')
                        ->on('sales.sale_id', '=', 'lines.sale_id');
                })
                ->where('lines.tenant_id', $context->tenantId())
                ->where('sales.organization_id', $context->organizationId())
                ->where('sales.outlet_id', $context->outletId())
                ->get([
                    'sales.sale_id',
                    'sales.completed_at_unix',
                    'lines.product_id',
                    'lines.quantity',
                ]);

            $sales = [];
            foreach ($saleRows as $row) {
                $productId = $this->requiredKnownProduct($products, $row->product_id);
                $saleId = $this->requiredString($row->sale_id);
                $quantity = $this->safeUnsignedBigIntToInt($row->quantity);
                if ($quantity <= 0) {
                    throw new PosTransactionViolation();
                }

                $key = $saleId."\0".$productId;
                if (! isset($sales[$key])) {
                    $sales[$key] = [
                        'sale_id' => $saleId,
                        'product_id' => $productId,
                        'quantity' => 0,
                        'occurred_at_unix' => $this->positiveUnix($row->completed_at_unix),
                    ];
                } elseif ($sales[$key]['occurred_at_unix'] !== $this->positiveUnix($row->completed_at_unix)) {
                    throw new PosTransactionViolation();
                }

                $sales[$key]['quantity'] = $this->safeAdd($sales[$key]['quantity'], $quantity);
            }

            foreach ($sales as $sale) {
                $productId = $sale['product_id'];
                $products[$productId]['sold'] = $this->safeAdd(
                    $products[$productId]['sold'],
                    $sale['quantity'],
                );
                $movements[] = $this->movement(
                    'SALE_DECREMENT',
                    $sale['sale_id'].'|'.$productId,
                    $productId,
                    $products[$productId]['display_name'],
                    'OUT',
                    $sale['quantity'],
                    $sale['occurred_at_unix'],
                    $sale['sale_id'],
                );
            }

            $voidRows = $this->connection->table('oneqay_pos_sale_voids as voids')
                ->join('oneqay_pos_sale_lines as lines', function ($join): void {
                    $join->on('lines.tenant_id', '=', 'voids.tenant_id')
                        ->on('lines.sale_id', '=', 'voids.sale_id');
                })
                ->join('oneqay_pos_sales as sales', function ($join): void {
                    $join->on('sales.tenant_id', '=', 'voids.tenant_id')
                        ->on('sales.sale_id', '=', 'voids.sale_id');
                })
                ->where('voids.tenant_id', $context->tenantId())
                ->where('voids.organization_id', $context->organizationId())
                ->where('voids.outlet_id', $context->outletId())
                ->where('sales.organization_id', $context->organizationId())
                ->where('sales.outlet_id', $context->outletId())
                ->get([
                    'voids.void_id',
                    'voids.sale_id',
                    'voids.voided_at_unix',
                    'lines.product_id',
                    'lines.quantity',
                ]);

            $voids = [];
            foreach ($voidRows as $row) {
                $productId = $this->requiredKnownProduct($products, $row->product_id);
                $voidId = $this->requiredString($row->void_id);
                $saleId = $this->requiredString($row->sale_id);
                $quantity = $this->safeUnsignedBigIntToInt($row->quantity);
                if ($quantity <= 0) {
                    throw new PosTransactionViolation();
                }

                $key = $voidId."\0".$productId;
                if (! isset($voids[$key])) {
                    $voids[$key] = [
                        'void_id' => $voidId,
                        'sale_id' => $saleId,
                        'product_id' => $productId,
                        'quantity' => 0,
                        'occurred_at_unix' => $this->positiveUnix($row->voided_at_unix),
                    ];
                } elseif ($voids[$key]['sale_id'] !== $saleId
                    || $voids[$key]['occurred_at_unix'] !== $this->positiveUnix($row->voided_at_unix)) {
                    throw new PosTransactionViolation();
                }

                $voids[$key]['quantity'] = $this->safeAdd($voids[$key]['quantity'], $quantity);
            }

            foreach ($voids as $void) {
                $productId = $void['product_id'];
                $products[$productId]['restored'] = $this->safeAdd(
                    $products[$productId]['restored'],
                    $void['quantity'],
                );
                $movements[] = $this->movement(
                    'FULL_SALE_VOID_RESTORATION',
                    $void['void_id'].'|'.$productId,
                    $productId,
                    $products[$productId]['display_name'],
                    'IN',
                    $void['quantity'],
                    $void['occurred_at_unix'],
                    $void['void_id'],
                );
            }

            $items = [];
            foreach ($products as $product) {
                $grossAvailable = $this->safeAdd(
                    $this->safeAdd($product['opening'], $product['replenished']),
                    $product['restored'],
                );
                if ($product['sold'] > $grossAvailable) {
                    throw new PosTransactionViolation();
                }
                $expected = $grossAvailable - $product['sold'];
                if ($expected !== $product['current']) {
                    throw new PosTransactionViolation();
                }

                $items[] = [
                    'product_id' => $product['product_id'],
                    'display_name' => $product['display_name'],
                    'active' => $product['active'],
                    'current_available_quantity' => (string) $product['current'],
                    'expected_available_quantity' => (string) $expected,
                    'opening_quantity' => (string) $product['opening'],
                    'replenished_quantity' => (string) $product['replenished'],
                    'sold_quantity' => (string) $product['sold'],
                    'restored_quantity' => (string) $product['restored'],
                ];
            }

            usort($movements, static function (array $left, array $right): int {
                $time = $right['occurred_at_unix'] <=> $left['occurred_at_unix'];
                if ($time !== 0) {
                    return $time;
                }

                return strcmp($right['evidence_id'], $left['evidence_id']);
            });

            return new PosInventoryAccountabilityWorkspaceSnapshot(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $context->deviceId(),
                $items,
                array_slice($movements, 0, 200),
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    /** @param array<string, array<string, mixed>> $products */
    private function requiredKnownProduct(array $products, mixed $value): string
    {
        $productId = $this->requiredString($value);
        if (! isset($products[$productId])) {
            throw new PosTransactionViolation();
        }

        return $productId;
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new PosTransactionViolation();
        }

        return $value;
    }

    private function positiveUnix(mixed $value): int
    {
        $unix = $this->safeUnsignedBigIntToInt($value);
        if ($unix <= 0) {
            throw new PosTransactionViolation();
        }

        return $unix;
    }

    private function safeAdd(int $left, int $right): int
    {
        if ($left < 0 || $right < 0 || $right > PHP_INT_MAX - $left) {
            throw new PosTransactionViolation();
        }

        return $left + $right;
    }

    /**
     * @return array{
     *   movement_type:string,
     *   evidence_id:string,
     *   product_id:string,
     *   display_name:string,
     *   direction:string,
     *   quantity:string,
     *   occurred_at_unix:int,
     *   reference_id:string
     * }
     */
    private function movement(
        string $movementType,
        string $evidenceId,
        string $productId,
        string $displayName,
        string $direction,
        int $quantity,
        int $occurredAtUnix,
        string $referenceId,
    ): array {
        return [
            'movement_type' => $movementType,
            'evidence_id' => $evidenceId,
            'product_id' => $productId,
            'display_name' => $displayName,
            'direction' => $direction,
            'quantity' => (string) $quantity,
            'occurred_at_unix' => $occurredAtUnix,
            'reference_id' => $referenceId,
        ];
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled || ! $this->featureEnabled) {
            throw new PosTransactionViolation();
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
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
