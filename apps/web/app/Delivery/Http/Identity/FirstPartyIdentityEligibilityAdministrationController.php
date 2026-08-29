<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationService;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationViolation;
use App\Application\Identity\IdentityAuthenticationEligibilityMutationId;
use App\Application\Organization\OrganizationalContextStore;
use App\Domain\Identity\PlatformIdentityId;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

// Author by Lab | zefry
final class FirstPartyIdentityEligibilityAdministrationController
{
    public function __construct(
        private readonly OrganizationalContextStore $contexts,
        private readonly FirstPartyIdentityEligibilityAdministrationService $administration,
    ) {}

    public function __invoke(Request $request, string $identity_id): JsonResponse
    {
        $actor = $this->contexts->current();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        if ($actor === null) {
            return $this->rejected($correlationId, 403);
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->except('_token');
            if (
                count($payload) !== 1
                || ! array_key_exists('mutation_id', $payload)
                || ! is_string($payload['mutation_id'])
            ) {
                throw new InvalidArgumentException('Identity authentication eligibility administration payload is invalid.');
            }

            $mutationId = IdentityAuthenticationEligibilityMutationId::fromString($payload['mutation_id']);
            $targetIdentityId = PlatformIdentityId::fromString($identity_id);

            $outcome = $this->administration->disable($actor, $targetIdentityId, $mutationId);

            return response()->json([
                'status' => 'ok',
                'outcome' => $outcome,
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException) {
            return $this->rejected($correlationId, 422);
        } catch (FirstPartyIdentityEligibilityAdministrationViolation $exception) {
            $status = match ($exception->errorCode) {
                FirstPartyIdentityEligibilityAdministrationViolation::INVALID_MUTATION => 422,
                FirstPartyIdentityEligibilityAdministrationViolation::AUTHORIZATION_DENIED,
                FirstPartyIdentityEligibilityAdministrationViolation::TARGET_IDENTITY_DENIED,
                FirstPartyIdentityEligibilityAdministrationViolation::PROTECTED_TARGET => 403,
                FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT => 409,
                FirstPartyIdentityEligibilityAdministrationViolation::PERSISTENCE_DISABLED,
                FirstPartyIdentityEligibilityAdministrationViolation::RUNTIME_DENIED,
                FirstPartyIdentityEligibilityAdministrationViolation::STORAGE_FAILURE => 503,
                default => 500,
            };

            return $this->rejected($correlationId, $status);
        }
    }

    public function reactivate(Request $request, string $identity_id): JsonResponse
    {
        $actor = $this->contexts->current();
        $correlationId = (string) $request->attributes->get('oneqay.correlation_id', 'correlation-missing');

        if ($actor === null) {
            return $this->rejected($correlationId, 403);
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = $request->except('_token');
            if (
                count($payload) !== 1
                || ! array_key_exists('mutation_id', $payload)
                || ! is_string($payload['mutation_id'])
            ) {
                throw new InvalidArgumentException('Identity authentication eligibility reactivation payload is invalid.');
            }

            $mutationId = IdentityAuthenticationEligibilityMutationId::fromString($payload['mutation_id']);
            $targetIdentityId = PlatformIdentityId::fromString($identity_id);

            $outcome = $this->administration->reactivate($actor, $targetIdentityId, $mutationId);

            return response()->json([
                'status' => 'ok',
                'outcome' => $outcome,
                'correlation_id' => $correlationId,
            ], 200, ['Cache-Control' => 'no-store, private']);
        } catch (InvalidArgumentException) {
            return $this->rejected($correlationId, 422);
        } catch (FirstPartyIdentityEligibilityAdministrationViolation $exception) {
            $status = match ($exception->errorCode) {
                FirstPartyIdentityEligibilityAdministrationViolation::INVALID_MUTATION => 422,
                FirstPartyIdentityEligibilityAdministrationViolation::AUTHORIZATION_DENIED,
                FirstPartyIdentityEligibilityAdministrationViolation::TARGET_IDENTITY_DENIED,
                FirstPartyIdentityEligibilityAdministrationViolation::PROTECTED_TARGET => 403,
                FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT => 409,
                FirstPartyIdentityEligibilityAdministrationViolation::PERSISTENCE_DISABLED,
                FirstPartyIdentityEligibilityAdministrationViolation::RUNTIME_DENIED,
                FirstPartyIdentityEligibilityAdministrationViolation::STORAGE_FAILURE => 503,
                default => 500,
            };

            return $this->rejected($correlationId, $status);
        }
    }

    private function rejected(string $correlationId, int $status): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'error' => [
                'code' => 'IDENTITY_AUTHENTICATION_ELIGIBILITY_ADMINISTRATION_REJECTED',
                'message' => 'The identity authentication eligibility administration request could not be completed.',
            ],
            'correlation_id' => $correlationId,
        ], $status, ['Cache-Control' => 'no-store, private']);
    }
}
