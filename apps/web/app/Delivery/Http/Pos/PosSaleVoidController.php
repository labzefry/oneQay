<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleVoidCommand;
use App\Application\Pos\VoidSale;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class PosSaleVoidController
{
    private const ALLOWED_FIELDS = ['operation_id', 'sale_id'];

    public function __construct(private readonly VoidSale $sales) {}

    public function __invoke(Request $request): JsonResponse
    {
        $correlationId = (string) $request->attributes->get(
            'oneqay.correlation_id',
            'correlation-missing',
        );

        try {
            $payload = $request->except('_token');
            $this->assertClosedPayload($payload);

            if (! is_string($payload['operation_id']) || ! is_string($payload['sale_id'])) {
                throw new InvalidArgumentException('POS sale void request is invalid.');
            }

            $result = $this->sales->execute(
                new SaleVoidCommand($payload['operation_id'], $payload['sale_id']),
                $correlationId,
            );

            return response()->json([
                'status' => 'voided',
                'void_id' => $result->voidId(),
                'sale_id' => $result->saleId(),
                'operation_id' => $result->operationId(),
                'tenant_id' => $result->tenantId(),
                'outlet_id' => $result->outletId(),
                'reversed' => [
                    'atomic_units' => $result->reversedAmount()->atomicUnits(),
                    'currency' => $result->reversedAmount()->currency(),
                    'scale' => $result->reversedAmount()->scale(),
                ],
                'tender_category' => $result->tenderCategory()->value,
                'evidence_mode' => $result->evidenceMode(),
                'voided_at_unix' => $result->voidedAtUnix(),
                'correlation_id' => $result->correlationId(),
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (DurableAuthorizationViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (InvalidArgumentException|PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SALE_VOID_REJECTED', $correlationId),
                422,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }

    private function assertClosedPayload(array $payload): void
    {
        $keys = array_keys($payload);
        sort($keys);

        if ($keys !== self::ALLOWED_FIELDS) {
            throw new InvalidArgumentException('POS sale void request is invalid.');
        }
    }
}
