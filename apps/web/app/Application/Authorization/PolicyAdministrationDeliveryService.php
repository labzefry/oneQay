<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Organization\VerifiedOrganizationalContext;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PolicyAdministrationDeliveryService
{
    public function __construct(private DurablePolicyAdministrationService $administration) {}

    /** @param array<string, mixed> $payload */
    public function apply(VerifiedOrganizationalContext $actor, array $payload): string
    {
        try {
            $command = PolicyAdministrationDeliveryCommand::fromPayload($payload);
            return $this->administration->apply($actor, $command->toMutation($actor));
        } catch (InvalidArgumentException) {
            throw new PolicyAdministrationDeliveryViolation(
                PolicyAdministrationDeliveryViolation::INVALID_PAYLOAD,
                'Policy administration request is invalid.',
            );
        } catch (DurablePolicyAdministrationViolation $exception) {
            $code = match ($exception->errorCode) {
                DurablePolicyAdministrationViolation::AUTHORIZATION_DENIED,
                DurablePolicyAdministrationViolation::PROTECTED_CONTROL_AUTHORITY,
                DurablePolicyAdministrationViolation::TARGET_SCOPE_INVALID,
                DurablePolicyAdministrationViolation::TARGET_ACCESS_DENIED
                    => PolicyAdministrationDeliveryViolation::AUTHORIZATION_DENIED,
                DurablePolicyAdministrationViolation::MUTATION_CONFLICT,
                DurablePolicyAdministrationViolation::RELATIONSHIP_CONFLICT
                    => PolicyAdministrationDeliveryViolation::MUTATION_CONFLICT,
                DurablePolicyAdministrationViolation::INVALID_MUTATION
                    => PolicyAdministrationDeliveryViolation::INVALID_PAYLOAD,
                DurablePolicyAdministrationViolation::PERSISTENCE_DISABLED,
                DurablePolicyAdministrationViolation::RUNTIME_DENIED
                    => PolicyAdministrationDeliveryViolation::PERSISTENCE_UNAVAILABLE,
                default => PolicyAdministrationDeliveryViolation::MUTATION_FAILED,
            };

            throw new PolicyAdministrationDeliveryViolation(
                $code,
                'Policy administration request was rejected.',
            );
        }
    }
}
