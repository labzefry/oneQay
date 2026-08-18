<?php

declare(strict_types=1);

namespace App\Delivery\Http\Authorization;

use App\Application\Authorization\PolicyAdministrationDeliveryService;
use App\Application\Authorization\PolicyAdministrationDeliveryViolation;
use App\Application\Organization\OrganizationalContextStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Author by Lab | zefry
final class PolicyAdministrationController
{
    public function __construct(
        private readonly OrganizationalContextStore $contexts,
        private readonly PolicyAdministrationDeliveryService $delivery,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $actor = $this->contexts->current();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        if ($actor === null) {
            return response()->json($this->errorEnvelope('POLICY_ADMIN_CONTEXT_DENIED', $correlationId), 403);
        }

        try {
            $outcome = $this->delivery->apply($actor, $request->all());

            return response()->json([
                'status' => 'ok',
                'outcome' => $outcome,
                'correlation_id' => $correlationId,
            ]);
        } catch (PolicyAdministrationDeliveryViolation $exception) {
            $status = match ($exception->errorCode) {
                PolicyAdministrationDeliveryViolation::INVALID_PAYLOAD => 422,
                PolicyAdministrationDeliveryViolation::AUTHORIZATION_DENIED => 403,
                PolicyAdministrationDeliveryViolation::MUTATION_CONFLICT => 409,
                PolicyAdministrationDeliveryViolation::PERSISTENCE_UNAVAILABLE => 503,
                default => 500,
            };

            return response()->json($this->errorEnvelope('POLICY_ADMIN_MUTATION_REJECTED', $correlationId), $status);
        }
    }

    /** @return array{status:string,error:array{code:string,message:string},correlation_id:string} */
    private function errorEnvelope(string $code, string $correlationId): array
    {
        return [
            'status' => 'error',
            'error' => [
                'code' => $code,
                'message' => 'The policy administration request could not be completed.',
            ],
            'correlation_id' => $correlationId,
        ];
    }
}
