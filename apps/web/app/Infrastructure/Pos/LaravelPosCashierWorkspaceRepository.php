<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\PosCashierCatalogItem;
use App\Application\Pos\PosCashierWorkspaceRepository;
use App\Application\Pos\PosCashierWorkspaceSnapshot;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosCashierWorkspaceRepository implements PosCashierWorkspaceRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $workspaceEnabled,
        private bool $saleCompletionEnabled,
    ) {}

    public function snapshot(PosExecutionContext $context): PosCashierWorkspaceSnapshot
    {
        $this->assertOperational();

        try {
            $activeShift = $this->connection->table('oneqay_pos_shifts')
                ->where('tenant_id', $context->tenantId())
                ->where('organization_id', $context->organizationId())
                ->where('outlet_id', $context->outletId())
                ->where('device_id', $context->deviceId())
                ->where('active_slot', 1)
                ->value('shift_id');

            if ($activeShift !== null && (! is_string($activeShift) || trim($activeShift) === '')) {
                throw new PosTransactionViolation();
            }

            $rows = $this->connection->table('oneqay_pos_sale_catalog_items')
                ->where('tenant_id', $context->tenantId())
                ->where('outlet_id', $context->outletId())
                ->where('active', true)
                ->where('available_quantity', '>', 0)
                ->orderBy('display_name')
                ->orderBy('product_id')
                ->limit(250)
                ->get([
                    'product_id',
                    'display_name',
                    'available_quantity',
                    'unit_price_atomic',
                    'currency',
                    'currency_scale',
                ]);

            $items = [];
            foreach ($rows as $row) {
                if (! is_string($row->product_id)
                    || ! is_string($row->display_name)
                    || ! is_string($row->currency)) {
                    throw new PosTransactionViolation();
                }

                $items[] = new PosCashierCatalogItem(
                    ProductId::fromString($row->product_id),
                    $row->display_name,
                    $this->positiveInteger($row->available_quantity),
                    Money::fromAtomicUnits(
                        $this->nonNegativeInteger($row->unit_price_atomic),
                        $row->currency,
                        $this->nonNegativeInteger($row->currency_scale),
                    ),
                );
            }

            return new PosCashierWorkspaceSnapshot(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $context->deviceId(),
                $activeShift === null ? null : $activeShift,
                $items,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (InvalidArgumentException) {
            throw new PosTransactionViolation();
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled || ! $this->workspaceEnabled || ! $this->saleCompletionEnabled) {
            throw new PosTransactionViolation();
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }

    private function positiveInteger(mixed $value): int
    {
        $integer = $this->nonNegativeInteger($value);
        if ($integer <= 0) {
            throw new PosTransactionViolation();
        }

        return $integer;
    }

    private function nonNegativeInteger(mixed $value): int
    {
        $normalized = is_int($value) ? (string) $value : (is_string($value) ? $value : '');
        if (preg_match('/\A(?:0|[1-9][0-9]*)\z/', $normalized) !== 1) {
            throw new PosTransactionViolation();
        }

        $maximum = (string) PHP_INT_MAX;
        if (strlen($normalized) > strlen($maximum)
            || (strlen($normalized) === strlen($maximum) && strcmp($normalized, $maximum) > 0)) {
            throw new PosTransactionViolation();
        }

        return (int) $normalized;
    }
}
