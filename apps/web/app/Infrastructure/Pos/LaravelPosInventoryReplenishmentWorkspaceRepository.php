<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosInventoryReplenishmentWorkspaceRepository;
use App\Application\Pos\PosInventoryReplenishmentWorkspaceSnapshot;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosInventoryReplenishmentWorkspaceRepository implements PosInventoryReplenishmentWorkspaceRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function read(PosExecutionContext $context): PosInventoryReplenishmentWorkspaceSnapshot
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
                ->where('catalog.active', true)
                ->orderBy('catalog.display_name')
                ->orderBy('catalog.product_id')
                ->limit(250)
                ->get([
                    'catalog.product_id',
                    'catalog.display_name',
                    'catalog.available_quantity',
                ]);

            $items = [];
            foreach ($catalogRows as $row) {
                if (! is_string($row->product_id) || trim($row->product_id) === ''
                    || ! is_string($row->display_name) || trim($row->display_name) === '') {
                    throw new PosTransactionViolation();
                }

                $items[] = [
                    'product_id' => $row->product_id,
                    'display_name' => $row->display_name,
                    'available_quantity' => (string) $this->safeUnsignedBigIntToInt($row->available_quantity),
                ];
            }

            $recentRows = $this->connection->table('oneqay_pos_inventory_replenishments')
                ->where('tenant_id', $context->tenantId())
                ->where('organization_id', $context->organizationId())
                ->where('outlet_id', $context->outletId())
                ->orderByDesc('occurred_at_unix')
                ->orderByDesc('replenishment_id')
                ->limit(50)
                ->get();

            $recent = [];
            foreach ($recentRows as $row) {
                if (! is_string($row->replenishment_id)
                    || preg_match('/\A[a-f0-9]{32}\z/', $row->replenishment_id) !== 1
                    || ! is_string($row->product_id)
                    || trim($row->product_id) === '') {
                    throw new PosTransactionViolation();
                }

                $before = $this->safeUnsignedBigIntToInt($row->before_available_quantity);
                $quantity = $this->safeUnsignedBigIntToInt($row->replenished_quantity);
                $after = $this->safeUnsignedBigIntToInt($row->after_available_quantity);
                $occurredAt = $this->safeUnsignedBigIntToInt($row->occurred_at_unix);
                if ($quantity <= 0 || $occurredAt <= 0 || $quantity > PHP_INT_MAX - $before || $before + $quantity !== $after) {
                    throw new PosTransactionViolation();
                }

                $recent[] = [
                    'replenishment_id' => $row->replenishment_id,
                    'product_id' => $row->product_id,
                    'before_available_quantity' => (string) $before,
                    'replenished_quantity' => (string) $quantity,
                    'after_available_quantity' => (string) $after,
                    'occurred_at_unix' => $occurredAt,
                ];
            }

            return new PosInventoryReplenishmentWorkspaceSnapshot(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $context->deviceId(),
                $items,
                $recent,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
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
