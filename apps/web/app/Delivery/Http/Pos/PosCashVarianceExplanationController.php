<?php

declare(strict_types=1);

namespace App\Delivery\Http\Pos;

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Pos\OperatePosCashVarianceReconciliation;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class PosCashVarianceExplanationController
{
    public function __construct(
        private readonly OperatePosCashVarianceReconciliation $reconciliation,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        try {
            $result = $this->reconciliation->recordExplanation(
                $this->requiredString($request->input('closing_evidence_id')),
                $this->requiredString($request->input('operation_id')),
                $this->requiredString($request->input('explanation_text')),
                $correlationId,
            );

            return response()->json([
                'evidence_id' => $result->evidenceId(),
                'operation_id' => $result->operationId(),
                'closing_evidence_id' => $result->closingCashEvidenceId(),
                'actor_identity_id' => $result->actorIdentityId(),
                'recorded_at_unix' => $result->recordedAtUnix(),
                'refresh_required' => true,
                'correlation_id' => $result->correlationId(),
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException) {
            return response()->json(
                SafeErrorEnvelope::make('POS_CASH_VARIANCE_EXPLANATION_INVALID', $correlationId),
                422,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (DurableAuthorizationViolation|PosAccessViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_CASH_VARIANCE_EXPLANATION_AUTHORIZATION_DENIED', $correlationId),
                403,
                ['Cache-Control' => 'no-store, private'],
            );
        } catch (PosTransactionViolation) {
            return response()->json(
                SafeErrorEnvelope::make('POS_CASH_VARIANCE_EXPLANATION_CONFLICT', $correlationId),
                409,
                ['Cache-Control' => 'no-store, private'],
            );
        }
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('Required reconciliation input is invalid.');
        }

        return $value;
    }
}
