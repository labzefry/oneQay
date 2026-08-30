<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\OpenShift;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftOpeningCommand;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class PosShiftOpeningController
{
    private const ALLOWED_FIELDS = ['operation_id'];

    public function __construct(private readonly OpenShift $shifts) {}

    public function __invoke(Request $request): JsonResponse
    {
        $correlationId = (string) $request->attributes->get(
            'oneqay.correlation_id',
            'correlation-missing',
        );

        try {
            $payload = $request->except('_token');
            $this->assertClosedPayload($payload);

            if (! is_string($payload['operation_id'])) {
                throw new InvalidArgumentException('POS shift opening request is invalid.');
            }

            $result = $this->shifts->open(
                new ShiftOpeningCommand($payload['operation_id']),
                $correlationId,
            );

            return response()->json([
                'status' => 'opened',
                'shift_id' => $result->shiftId(),
                'operation_id' => $result->operationId(),
                'tenant_id' => $result->tenantId(),
                'outlet_id' => $result->outletId(),
                'register_context' => ['device_id' => $result->deviceId()],
                'opened_at_unix' => $result->openedAtUnix(),
                'active' => $result->active(),
                'correlation_id' => $result->correlationId(),
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (DurableAuthorizationViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SHIFT_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (InvalidArgumentException|PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SHIFT_OPEN_REJECTED', $correlationId),
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
            throw new InvalidArgumentException('POS shift opening request is invalid.');
        }
    }
}
