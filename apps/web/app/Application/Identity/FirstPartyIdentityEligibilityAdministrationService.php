<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Identity\PlatformIdentityId;

// Author by Lab | zefry
final readonly class FirstPartyIdentityEligibilityAdministrationService
{
    public function __construct(
        private FirstPartyIdentityEligibilityAdministrationRepository $repository,
        private FirstPartyIdentityDisablementSessionTerminationRepository $sessionTermination,
        private PersistenceTransaction $transaction,
        private PolicyAdministrationClock $clock,
    ) {}

    public function disable(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        IdentityAuthenticationEligibilityMutationId $mutationId,
    ): string {
        $this->assertPreflight($actor, $targetIdentityId);

        $prior = $this->repository->replayOutcome($actor, $targetIdentityId, $mutationId);
        if ($prior !== null) {
            return $this->completePriorOutcome($actor, $targetIdentityId, $prior);
        }

        $occurredAtUnix = $this->positiveAdministrationTimestamp();

        try {
            return $this->transaction->run(function () use (
                $actor,
                $targetIdentityId,
                $mutationId,
                $occurredAtUnix,
            ): string {
                $outcome = $this->repository->applyFresh(
                    $actor,
                    $targetIdentityId,
                    $mutationId,
                    $occurredAtUnix,
                );

                $this->sessionTermination->revokeActiveForIdentityDisablement(
                    $actor->tenantId(),
                    $targetIdentityId,
                    $occurredAtUnix,
                );

                return $outcome;
            });
        } catch (DurablePersistenceViolation $exception) {
            $this->assertPreflight($actor, $targetIdentityId);

            $prior = $this->repository->replayOutcome($actor, $targetIdentityId, $mutationId);
            if ($prior !== null) {
                return $this->completePriorOutcome($actor, $targetIdentityId, $prior);
            }

            $this->failFromPersistence($exception);
        }
    }

    private function completePriorOutcome(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        string $prior,
    ): string {
        $revokedAtUnix = $this->positiveAdministrationTimestamp();

        try {
            return $this->transaction->run(function () use (
                $actor,
                $targetIdentityId,
                $prior,
                $revokedAtUnix,
            ): string {
                $this->sessionTermination->revokeActiveForIdentityDisablement(
                    $actor->tenantId(),
                    $targetIdentityId,
                    $revokedAtUnix,
                );

                return $prior;
            });
        } catch (DurablePersistenceViolation $exception) {
            $this->failFromPersistence($exception);
        }
    }

    private function positiveAdministrationTimestamp(): int
    {
        $timestamp = $this->clock->nowUnix();
        if ($timestamp <= 0) {
            $this->fail(
                FirstPartyIdentityEligibilityAdministrationViolation::INVALID_MUTATION,
                'Identity authentication eligibility administration clock returned an invalid timestamp.',
            );
        }

        return $timestamp;
    }

    private function assertPreflight(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
    ): void {
        if (! $this->repository->hasTenantControlAuthority($actor)) {
            $this->fail(
                FirstPartyIdentityEligibilityAdministrationViolation::AUTHORIZATION_DENIED,
                'Identity authentication eligibility administration authorization denied.',
            );
        }

        $this->repository->assertTargetEligible($actor, $targetIdentityId);
    }

    private function failFromPersistence(DurablePersistenceViolation $exception): never
    {
        $code = match ($exception->errorCode) {
            DurablePersistenceViolation::PERSISTENCE_DISABLED => FirstPartyIdentityEligibilityAdministrationViolation::PERSISTENCE_DISABLED,
            DurablePersistenceViolation::RUNTIME_DENIED => FirstPartyIdentityEligibilityAdministrationViolation::RUNTIME_DENIED,
            DurablePersistenceViolation::RELATIONSHIP_CONFLICT => FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT,
            DurablePersistenceViolation::STORAGE_FAILURE => FirstPartyIdentityEligibilityAdministrationViolation::STORAGE_FAILURE,
            default => FirstPartyIdentityEligibilityAdministrationViolation::TRANSACTION_FAILURE,
        };

        $this->fail($code, 'Identity authentication eligibility administration transaction failed.');
    }

    private function fail(string $code, string $message): never
    {
        throw new FirstPartyIdentityEligibilityAdministrationViolation($code, $message);
    }
}
