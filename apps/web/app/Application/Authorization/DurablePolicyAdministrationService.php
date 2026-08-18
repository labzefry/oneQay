<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;

// Author by Lab | zefry
final readonly class DurablePolicyAdministrationService
{
    public function __construct(
        private DurableScopedAuthorizationPolicy $authorization,
        private DurablePolicyAdministrationRepository $repository,
        private PersistenceTransaction $transaction,
        private PolicyAdministrationClock $clock,
    ) {}

    public function apply(?VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): string
    {
        if ($actor === null) {
            $this->fail(DurablePolicyAdministrationViolation::AUTHORIZATION_DENIED, 'Policy administration authorization denied.');
        }

        if (! $mutation->scope()->matchesActor($actor)) {
            $this->fail(DurablePolicyAdministrationViolation::TARGET_SCOPE_INVALID, 'Policy administration target scope is invalid.');
        }

        try {
            $this->authorization->require($actor, AdministrationPermission::manage());
        } catch (DurableAuthorizationViolation $exception) {
            $code = match ($exception->errorCode) {
                DurableAuthorizationViolation::PERSISTENCE_DISABLED => DurablePolicyAdministrationViolation::PERSISTENCE_DISABLED,
                DurableAuthorizationViolation::RUNTIME_DENIED => DurablePolicyAdministrationViolation::RUNTIME_DENIED,
                DurableAuthorizationViolation::STORAGE_FAILURE, DurableAuthorizationViolation::POLICY_DATA_INVALID => DurablePolicyAdministrationViolation::STORAGE_FAILURE,
                default => DurablePolicyAdministrationViolation::AUTHORIZATION_DENIED,
            };
            $this->fail($code, 'Policy administration authorization denied.');
        }

        if (! $this->repository->hasControlAuthorityForScope($actor, $mutation->scope())) {
            $this->fail(DurablePolicyAdministrationViolation::AUTHORIZATION_DENIED, 'Policy administration authorization denied.');
        }

        if ($mutation->permission() !== null && AdministrationPermission::isControl($mutation->permission())) {
            $this->fail(DurablePolicyAdministrationViolation::PROTECTED_CONTROL_AUTHORITY, 'Protected policy control authority cannot be mutated.');
        }

        if (($mutation->operation()->isPermissionMutation() || $mutation->operation()->isAssignmentMutation())
            && $this->repository->isProtectedControlRole($actor, $mutation->role())) {
            $this->fail(DurablePolicyAdministrationViolation::PROTECTED_CONTROL_AUTHORITY, 'Protected policy control authority cannot be mutated.');
        }

        if ($mutation->operation()->isAssignmentMutation()) {
            $this->repository->assertTargetEligible($actor, $mutation);
        }

        $prior = $this->repository->replayOutcome($actor, $mutation);
        if ($prior !== null) {
            return $prior;
        }

        $occurredAtUnix = $this->clock->nowUnix();
        if ($occurredAtUnix <= 0) {
            $this->fail(DurablePolicyAdministrationViolation::INVALID_MUTATION, 'Policy administration clock returned an invalid timestamp.');
        }

        try {
            return $this->transaction->run(
                fn (): string => $this->repository->applyFresh($actor, $mutation, $occurredAtUnix),
            );
        } catch (DurablePersistenceViolation $exception) {
            $code = match ($exception->errorCode) {
                DurablePersistenceViolation::PERSISTENCE_DISABLED => DurablePolicyAdministrationViolation::PERSISTENCE_DISABLED,
                DurablePersistenceViolation::RUNTIME_DENIED => DurablePolicyAdministrationViolation::RUNTIME_DENIED,
                DurablePersistenceViolation::RELATIONSHIP_CONFLICT => DurablePolicyAdministrationViolation::RELATIONSHIP_CONFLICT,
                DurablePersistenceViolation::STORAGE_FAILURE => DurablePolicyAdministrationViolation::STORAGE_FAILURE,
                default => DurablePolicyAdministrationViolation::TRANSACTION_FAILURE,
            };
            $this->fail($code, 'Policy administration transaction failed.');
        }
    }

    private function fail(string $code, string $message): never
    {
        throw new DurablePolicyAdministrationViolation($code, $message);
    }
}
