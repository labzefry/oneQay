<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\InventoryBaselineCommand;
use App\Application\Pos\InventoryBaselineRepository;
use App\Application\Pos\InventoryBaselineResult;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelInventoryBaselineRepository implements InventoryBaselineRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function establish(
        PosExecutionContext $context,
        InventoryBaselineCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): InventoryBaselineResult {
        $this->assertOperational();

        $fingerprint = hash('sha256', implode('|', [
            $context->actorId(),
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            $command->semanticFingerprintPart(),
        ]));

        try {
            $existingOperation = $this->connection->table('oneqay_pos_inventory_baselines')
                ->where('tenant_id', $context->tenantId())
                ->where('operation_id', $command->operationId())
                ->lockForUpdate()
                ->first();

            if ($existingOperation !== null) {
                if (! is_string($existingOperation->payload_fingerprint)
                    || ! hash_equals($existingOperation->payload_fingerprint, $fingerprint)) {
                    throw new PosTransactionViolation();
                }

                return $this->hydrateResult($existingOperation);
            }

            $catalog = $this->connection->table('oneqay_pos_sale_catalog_items')
                ->where('tenant_id', $context->tenantId())
                ->where('outlet_id', $context->outletId())
                ->where('product_id', $command->productId()->value())
                ->lockForUpdate()
                ->first();

            if ($catalog === null) {
                throw new PosTransactionViolation();
            }

            $existingProductBaseline = $this->connection->table('oneqay_pos_inventory_baselines')
                ->where('tenant_id', $context->tenantId())
                ->where('outlet_id', $context->outletId())
                ->where('product_id', $command->productId()->value())
                ->lockForUpdate()
                ->first();

            if ($existingProductBaseline !== null) {
                throw new PosTransactionViolation();
            }

            $available = $this->safeUnsignedBigIntToInt($catalog->available_quantity);
            if ($available !== 0) {
                throw new PosTransactionViolation();
            }

            $hasSaleHistory = $this->connection->table('oneqay_pos_sale_lines as lines')
                ->join('oneqay_pos_sales as sales', function ($join): void {
                    $join->on('sales.tenant_id', '=', 'lines.tenant_id')
                        ->on('sales.sale_id', '=', 'lines.sale_id');
                })
                ->where('lines.tenant_id', $context->tenantId())
                ->where('sales.outlet_id', $context->outletId())
                ->where('lines.product_id', $command->productId()->value())
                ->exists();

            if ($hasSaleHistory) {
                throw new PosTransactionViolation();
            }

            if ($command->openingQuantity() > 0) {
                $updated = $this->connection->table('oneqay_pos_sale_catalog_items')
                    ->where('tenant_id', $context->tenantId())
                    ->where('outlet_id', $context->outletId())
                    ->where('product_id', $command->productId()->value())
                    ->where('available_quantity', 0)
                    ->update(['available_quantity' => $command->openingQuantity()]);

                if ($updated !== 1) {
                    throw new PosTransactionViolation();
                }
            }

            $baselineId = substr(hash('sha256', $context->tenantId().'|'.$command->operationId()), 0, 32);

            $this->connection->table('oneqay_pos_inventory_baselines')->insert([
                'tenant_id' => $context->tenantId(),
                'baseline_id' => $baselineId,
                'operation_id' => $command->operationId(),
                'payload_fingerprint' => $fingerprint,
                'actor_identity_id' => $context->actorId(),
                'organization_id' => $context->organizationId(),
                'outlet_id' => $context->outletId(),
                'device_id' => $context->deviceId(),
                'product_id' => $command->productId()->value(),
                'before_available_quantity' => 0,
                'opening_quantity' => $command->openingQuantity(),
                'correlation_id' => $correlationId,
                'occurred_at_unix' => $occurredAtUnix,
            ]);

            return new InventoryBaselineResult(
                $baselineId,
                $command->operationId(),
                $context->tenantId(),
                $context->outletId(),
                $command->productId()->value(),
                $command->openingQuantity(),
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function hydrateResult(object $baseline): InventoryBaselineResult
    {
        return new InventoryBaselineResult(
            (string) $baseline->baseline_id,
            (string) $baseline->operation_id,
            (string) $baseline->tenant_id,
            (string) $baseline->outlet_id,
            (string) $baseline->product_id,
            $this->safeUnsignedBigIntToInt($baseline->opening_quantity),
        );
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
