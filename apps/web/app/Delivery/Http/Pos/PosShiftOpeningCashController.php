<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\RecordShiftOpeningCash;
use App\Application\Pos\ShiftOpeningCashCommand;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Domain\Pos\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class PosShiftOpeningCashController
{
    private const ALLOWED_FIELDS = [
        'currency',
        'currency_scale',
        'opening_cash_atomic',
        'operation_id',
    ];

    public function __construct(private readonly RecordShiftOpeningCash $evidence) {}

    public function __invoke(Request $request): JsonResponse
    {
        $correlationId = (string) $request->attributes->get(
            'oneqay.correlation_id',
            'correlation-missing',
        );

        try {
            $payload = $request->except('_token');
            $this->assertClosedPayload($payload);

            if (! is_string($payload['operation_id'])
                || ! is_int($payload['opening_cash_atomic'])
                || $payload['opening_cash_atomic'] < 0
                || ! is_string($payload['currency'])
                || ! is_int($payload['currency_scale'])) {
                throw new InvalidArgumentException('POS shift opening cash request is invalid.');
            }

            $openingCash = Money::fromAtomicUnits(
                $payload['opening_cash_atomic'],
                $payload['currency'],
                $payload['currency_scale'],
            );

            $result = $this->evidence->record(
                new ShiftOpeningCashCommand($payload['operation_id'], $openingCash),
                $correlationId,
            );

            return response()->json([
                'status' => 'recorded',
                'evidence_id' => $result->evidenceId(),
                'shift_id' => $result->shiftId(),
                'operation_id' => $result->operationId(),
                'tenant_id' => $result->tenantId(),
                'outlet_id' => $result->outletId(),
                'register_context' => ['device_id' => $result->deviceId()],
                'opening_cash' => [
                    'atomic' => $result->openingCash()->atomicUnits(),
                    'currency' => $result->openingCash()->currency(),
                    'scale' => $result->openingCash()->scale(),
                ],
                'evidence_mode' => $result->evidenceMode(),
                'recorded_at_unix' => $result->recordedAtUnix(),
                'correlation_id' => $result->correlationId(),
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (DurableAuthorizationViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SHIFT_OPENING_CASH_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (InvalidArgumentException|PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SHIFT_OPENING_CASH_REJECTED', $correlationId),
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
            throw new InvalidArgumentException('POS shift opening cash request is invalid.');
        }
    }
}
