<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\RecordCashRefund;
use App\Application\Pos\SaleCashRefundCommand;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class PosSaleCashRefundController
{
    private const ALLOWED_FIELDS = ['operation_id', 'sale_id'];

    public function __construct(private readonly RecordCashRefund $refunds) {}

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
                throw new InvalidArgumentException('POS cash refund request is invalid.');
            }

            $result = $this->refunds->record(
                new SaleCashRefundCommand($payload['operation_id'], $payload['sale_id']),
                $correlationId,
            );

            return response()->json([
                'status' => 'cash_refund_recorded',
                'refund_id' => $result->refundId(),
                'sale_id' => $result->saleId(),
                'void_id' => $result->voidId(),
                'operation_id' => $result->operationId(),
                'tenant_id' => $result->tenantId(),
                'outlet_id' => $result->outletId(),
                'refunded' => [
                    'atomic_units' => $result->refundedAmount()->atomicUnits(),
                    'currency' => $result->refundedAmount()->currency(),
                    'scale' => $result->refundedAmount()->scale(),
                ],
                'tender_category' => $result->tenderCategory()->value,
                'evidence_mode' => $result->evidenceMode(),
                'refunded_at_unix' => $result->refundedAtUnix(),
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
                SafeErrorEnvelope::make('POS_SALE_CASH_REFUND_REJECTED', $correlationId),
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
            throw new InvalidArgumentException('POS cash refund request is invalid.');
        }
    }
}
