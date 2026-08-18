<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Identity\VerifiedPlatformIdentity;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Identity\PlatformIdentityId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class ProtectedControlAdministratorLifecycleService
{
    public function __construct(
        private ProtectedControlAdministratorLifecycleRepository $repository,
        private PersistenceTransaction $transaction,
        private PolicyAdministrationClock $clock,
    ) {}

    public function apply(
        VerifiedOrganizationalContext $actor,
        ?VerifiedPlatformIdentity $verifiedTarget,
        ProtectedControlAdministratorMutationId $mutationId,
        ProtectedControlAdministratorOperation $operation,
    ): string {
        if ($verifiedTarget === null) {
            $this->fail(
                ProtectedControlAdministratorLifecycleViolation::TARGET_IDENTITY_MISMATCH,
                'Verified target identity is required for protected control lifecycle mutation.',
            );
        }

        try {
            $targetIdentityId = PlatformIdentityId::fromString($verifiedTarget->identityId());
        } catch (InvalidArgumentException) {
            $this->fail(
                ProtectedControlAdministratorLifecycleViolation::TARGET_IDENTITY_MISMATCH,
                'Verified target identity is invalid for protected control lifecycle mutation.',
            );
        }

        $mutation = new ProtectedControlAdministratorMutation($mutationId, $operation, $targetIdentityId);

        $this->assertPreflight($actor, $mutation);

        $prior = $this->repository->replayOutcome($actor, $mutation);
        if ($prior !== null) {
            return $prior;
        }

        $this->repository->assertOperationAllowed($actor, $mutation);

        $occurredAtUnix = $this->clock->nowUnix();
        if ($occurredAtUnix <= 0) {
            $this->fail(
                ProtectedControlAdministratorLifecycleViolation::INVALID_MUTATION,
                'Protected control lifecycle clock returned an invalid timestamp.',
            );
        }

        try {
            return $this->transaction->run(
                fn (): string => $this->repository->applyFresh($actor, $mutation, $occurredAtUnix),
            );
        } catch (DurablePersistenceViolation $exception) {
            $this->assertPreflight($actor, $mutation);

            $prior = $this->repository->replayOutcome($actor, $mutation);
            if ($prior !== null) {
                return $prior;
            }

            $this->repository->assertOperationAllowed($actor, $mutation);

            $code = match ($exception->errorCode) {
                DurablePersistenceViolation::PERSISTENCE_DISABLED => ProtectedControlAdministratorLifecycleViolation::PERSISTENCE_DISABLED,
                DurablePersistenceViolation::RUNTIME_DENIED => ProtectedControlAdministratorLifecycleViolation::RUNTIME_DENIED,
                DurablePersistenceViolation::STORAGE_FAILURE => ProtectedControlAdministratorLifecycleViolation::STORAGE_FAILURE,
                default => ProtectedControlAdministratorLifecycleViolation::TRANSACTION_FAILURE,
            };

            $this->fail($code, 'Protected control administrator lifecycle transaction failed.');
        }
    }

    private function assertPreflight(
        VerifiedOrganizationalContext $actor,
        ProtectedControlAdministratorMutation $mutation,
    ): void {
        if (! $this->repository->hasTenantControlAuthority($actor)) {
            $this->fail(
                ProtectedControlAdministratorLifecycleViolation::AUTHORIZATION_DENIED,
                'Protected control administrator lifecycle authorization denied.',
            );
        }

        $this->repository->assertTargetEligible($actor, $mutation->targetIdentityId());
        $this->repository->assertProtectedRoleState($actor);
    }

    private function fail(string $code, string $message): never
    {
        throw new ProtectedControlAdministratorLifecycleViolation($code, $message);
    }
}
