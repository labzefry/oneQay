<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\EstablishInventoryBaseline;
use App\Application\Pos\InventoryBaselineCommand;
use App\Application\Pos\PosTransactionViolation;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Pos\ProductId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class PosInventoryBaselineController
{
    private const ALLOWED_FIELDS = [
        'operation_id',
        'product_id',
        'opening_quantity',
    ];

    public function __construct(private readonly EstablishInventoryBaseline $inventory) {}

    public function __invoke(Request $request): JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $payload = $request->except('_token');
            $this->assertClosedPayload($payload);

            if (! is_string($payload['operation_id'])
                || ! is_string($payload['product_id'])
                || ! is_int($payload['opening_quantity'])
                || $payload['opening_quantity'] < 0) {
                throw new InvalidArgumentException('POS inventory baseline request is invalid.');
            }

            $result = $this->inventory->establish(
                new InventoryBaselineCommand(
                    $payload['operation_id'],
                    ProductId::fromString($payload['product_id']),
                    $payload['opening_quantity'],
                ),
                $correlationId,
            );

            return response()->json([
                'status' => 'established',
                'baseline_id' => $result->baselineId(),
                'operation_id' => $result->operationId(),
                'tenant_id' => $result->tenantId(),
                'outlet_id' => $result->outletId(),
                'product_id' => $result->productId(),
                'opening_quantity' => $result->openingQuantity(),
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (DurableAuthorizationViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_INVENTORY_BASELINE_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (InvalidArgumentException|PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_INVENTORY_BASELINE_REJECTED', $correlationId),
                422,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }

    private function assertClosedPayload(array $payload): void
    {
        $keys = array_keys($payload);
        sort($keys);
        $allowed = self::ALLOWED_FIELDS;
        sort($allowed);

        if ($keys !== $allowed) {
            throw new InvalidArgumentException('POS inventory baseline request is invalid.');
        }
    }
}
