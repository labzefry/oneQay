<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\InventoryReplenishmentCommand;
use App\Application\Pos\InventoryReplenishmentRepository;
use App\Application\Pos\InventoryReplenishmentResult;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelInventoryReplenishmentRepository implements InventoryReplenishmentRepository
{
    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function replenish(
        PosExecutionContext $context,
        InventoryReplenishmentCommand $command,
        string $correlationId,
        int $occurredAtUnix,
    ): InventoryReplenishmentResult {
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
            $existing = $this->connection->table('oneqay_pos_inventory_replenishments')
                ->where('tenant_id', $context->tenantId())
                ->where('operation_id', $command->operationId())
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if (! is_string($existing->payload_fingerprint)
                    || ! hash_equals($existing->payload_fingerprint, $fingerprint)) {
                    throw new PosTransactionViolation();
                }

                return $this->hydrateResult($existing);
            }

            $baseline = $this->connection->table('oneqay_pos_inventory_baselines')
                ->where('tenant_id', $context->tenantId())
                ->where('organization_id', $context->organizationId())
                ->where('outlet_id', $context->outletId())
                ->where('product_id', $command->productId()->value())
                ->lockForUpdate()
                ->first();

            if ($baseline === null) {
                throw new PosTransactionViolation();
            }

            $catalog = $this->connection->table('oneqay_pos_sale_catalog_items')
                ->where('tenant_id', $context->tenantId())
                ->where('outlet_id', $context->outletId())
                ->where('product_id', $command->productId()->value())
                ->where('active', true)
                ->lockForUpdate()
                ->first();

            if ($catalog === null) {
                throw new PosTransactionViolation();
            }

            $before = $this->safeUnsignedBigIntToInt($catalog->available_quantity);
            $quantity = $command->replenishedQuantity();
            if ($quantity > PHP_INT_MAX - $before) {
                throw new PosTransactionViolation();
            }
            $after = $before + $quantity;

            $updated = $this->connection->table('oneqay_pos_sale_catalog_items')
                ->where('tenant_id', $context->tenantId())
                ->where('outlet_id', $context->outletId())
                ->where('product_id', $command->productId()->value())
                ->where('active', true)
                ->where('available_quantity', $before)
                ->update(['available_quantity' => $after]);

            if ($updated !== 1) {
                throw new PosTransactionViolation();
            }

            $replenishmentId = substr(
                hash('sha256', $context->tenantId().'|'.$command->operationId()),
                0,
                32,
            );

            $this->connection->table('oneqay_pos_inventory_replenishments')->insert([
                'tenant_id' => $context->tenantId(),
                'replenishment_id' => $replenishmentId,
                'operation_id' => $command->operationId(),
                'payload_fingerprint' => $fingerprint,
                'actor_identity_id' => $context->actorId(),
                'organization_id' => $context->organizationId(),
                'outlet_id' => $context->outletId(),
                'device_id' => $context->deviceId(),
                'product_id' => $command->productId()->value(),
                'before_available_quantity' => $before,
                'replenished_quantity' => $quantity,
                'after_available_quantity' => $after,
                'correlation_id' => $correlationId,
                'occurred_at_unix' => $occurredAtUnix,
            ]);

            return new InventoryReplenishmentResult(
                $replenishmentId,
                $command->operationId(),
                $context->tenantId(),
                $context->outletId(),
                $command->productId()->value(),
                $before,
                $quantity,
                $after,
                $occurredAtUnix,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function hydrateResult(object $row): InventoryReplenishmentResult
    {
        return new InventoryReplenishmentResult(
            (string) $row->replenishment_id,
            (string) $row->operation_id,
            (string) $row->tenant_id,
            (string) $row->outlet_id,
            (string) $row->product_id,
            $this->safeUnsignedBigIntToInt($row->before_available_quantity),
            $this->safeUnsignedBigIntToInt($row->replenished_quantity),
            $this->safeUnsignedBigIntToInt($row->after_available_quantity),
            $this->safeUnsignedBigIntToInt($row->occurred_at_unix),
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
