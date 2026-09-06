<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\CloseShift;
use App\Application\Pos\CloseShiftCommand;
use App\Application\Pos\PosTransactionViolation;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class PosShiftCloseController
{
    private const ALLOWED_FIELDS = ['operation_id'];

    public function __construct(private readonly CloseShift $closeShift) {}

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
                throw new InvalidArgumentException('POS Final Shift Close request is invalid.');
            }

            $result = $this->closeShift->close(
                new CloseShiftCommand($payload['operation_id']),
                $correlationId,
            );

            return response()->json([
                'status' => 'closed',
                'evidence_id' => $result->evidenceId(),
                'operation_id' => $result->operationId(),
                'shift_id' => $result->shiftId(),
                'reconciliation' => [
                    'expected_cash_atomic' => $result->expectedCashAtomic(),
                    'observed_closing_cash_atomic' => $result->observedClosingCashAtomic(),
                    'variance_atomic' => $result->varianceAtomic(),
                    'variance_direction' => $result->varianceDirection(),
                    'currency' => $result->currency(),
                    'currency_scale' => $result->currencyScale(),
                    'review_outcome' => $result->reviewOutcome(),
                ],
                'cutoff_at_unix' => $result->cutoffAtUnix(),
                'closed_at_unix' => $result->closedAtUnix(),
                'correlation_id' => $result->correlationId(),
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (DurableAuthorizationViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SHIFT_CLOSE_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (InvalidArgumentException|PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_SHIFT_CLOSE_REJECTED', $correlationId),
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
            throw new InvalidArgumentException('POS Final Shift Close request is invalid.');
        }
    }
}
