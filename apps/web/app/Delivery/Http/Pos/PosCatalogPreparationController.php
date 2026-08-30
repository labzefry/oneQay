<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\CatalogPreparationCommand;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\PrepareCatalogItem;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class PosCatalogPreparationController
{
    private const ALLOWED_FIELDS = [
        'operation_id',
        'product_id',
        'display_name',
        'unit_price_atomic',
        'currency',
        'currency_scale',
        'sellable',
    ];

    public function __construct(private readonly PrepareCatalogItem $catalog) {}

    public function __invoke(Request $request): JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $payload = $request->except('_token');
            $this->assertClosedPayload($payload);

            if (! is_string($payload['operation_id'])
                || ! is_string($payload['product_id'])
                || ! is_string($payload['display_name'])
                || ! is_int($payload['unit_price_atomic'])
                || ! is_string($payload['currency'])
                || ! is_int($payload['currency_scale'])
                || ! is_bool($payload['sellable'])) {
                throw new InvalidArgumentException('POS catalog preparation request is invalid.');
            }

            $result = $this->catalog->prepare(
                new CatalogPreparationCommand(
                    $payload['operation_id'],
                    ProductId::fromString($payload['product_id']),
                    $payload['display_name'],
                    Money::fromAtomicUnits(
                        $payload['unit_price_atomic'],
                        $payload['currency'],
                        $payload['currency_scale'],
                    ),
                    $payload['sellable'],
                ),
                $correlationId,
            );

            return response()->json([
                'status' => 'prepared',
                'mutation_id' => $result->mutationId(),
                'operation_id' => $result->operationId(),
                'tenant_id' => $result->tenantId(),
                'outlet_id' => $result->outletId(),
                'product_id' => $result->productId(),
                'catalog' => [
                    'display_name' => $result->displayName(),
                    'unit_price' => [
                        'atomic_units' => $result->unitPrice()->atomicUnits(),
                        'currency' => $result->unitPrice()->currency(),
                        'scale' => $result->unitPrice()->scale(),
                    ],
                    'sellable' => $result->sellable(),
                ],
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (DurableAuthorizationViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_CATALOG_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (InvalidArgumentException|PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_CATALOG_PREPARATION_REJECTED', $correlationId),
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
            throw new InvalidArgumentException('POS catalog preparation request is invalid.');
        }
    }
}
