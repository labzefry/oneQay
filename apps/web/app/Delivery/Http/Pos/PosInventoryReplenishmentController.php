<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\InventoryReplenishmentCommand;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ReplenishInventory;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Pos\ProductId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class PosInventoryReplenishmentController
{
    private const ALLOWED_FIELDS = [
        'operation_id',
        'product_id',
        'replenished_quantity',
    ];

    public function __construct(private readonly ReplenishInventory $inventory) {}

    public function __invoke(Request $request): JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $payload = $request->except('_token');
            $this->assertClosedPayload($payload);

            if (! is_string($payload['operation_id'])
                || ! is_string($payload['product_id'])
                || ! is_int($payload['replenished_quantity'])
                || $payload['replenished_quantity'] <= 0) {
                throw new InvalidArgumentException('POS inventory replenishment request is invalid.');
            }

            $result = $this->inventory->replenish(
                new InventoryReplenishmentCommand(
                    $payload['operation_id'],
                    ProductId::fromString($payload['product_id']),
                    $payload['replenished_quantity'],
                ),
                $correlationId,
            );

            return response()->json([
                'status' => 'replenished',
                'replenishment_id' => $result->replenishmentId(),
                'operation_id' => $result->operationId(),
                'tenant_id' => $result->tenantId(),
                'outlet_id' => $result->outletId(),
                'product_id' => $result->productId(),
                'before_available_quantity' => (string) $result->beforeAvailableQuantity(),
                'replenished_quantity' => (string) $result->replenishedQuantity(),
                'after_available_quantity' => (string) $result->afterAvailableQuantity(),
                'occurred_at_unix' => $result->occurredAtUnix(),
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (DurableAuthorizationViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_INVENTORY_REPLENISHMENT_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (InvalidArgumentException|PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_INVENTORY_REPLENISHMENT_REJECTED', $correlationId),
                422,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }

    /** @param array<string, mixed> $payload */
    private function assertClosedPayload(array $payload): void
    {
        $keys = array_keys($payload);
        sort($keys);
        $allowed = self::ALLOWED_FIELDS;
        sort($allowed);

        if ($keys !== $allowed) {
            throw new InvalidArgumentException('POS inventory replenishment request is invalid.');
        }
    }
}
