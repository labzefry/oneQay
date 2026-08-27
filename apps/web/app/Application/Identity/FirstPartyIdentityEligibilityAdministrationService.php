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
            return $prior;
        }

        $occurredAtUnix = $this->clock->nowUnix();
        if ($occurredAtUnix <= 0) {
            $this->fail(
                FirstPartyIdentityEligibilityAdministrationViolation::INVALID_MUTATION,
                'Identity authentication eligibility administration clock returned an invalid timestamp.',
            );
        }

        try {
            return $this->transaction->run(
                fn (): string => $this->repository->applyFresh(
                    $actor,
                    $targetIdentityId,
                    $mutationId,
                    $occurredAtUnix,
                ),
            );
        } catch (DurablePersistenceViolation $exception) {
            $this->assertPreflight($actor, $targetIdentityId);

            $prior = $this->repository->replayOutcome($actor, $targetIdentityId, $mutationId);
            if ($prior !== null) {
                return $prior;
            }

            $code = match ($exception->errorCode) {
                DurablePersistenceViolation::PERSISTENCE_DISABLED => FirstPartyIdentityEligibilityAdministrationViolation::PERSISTENCE_DISABLED,
                DurablePersistenceViolation::RUNTIME_DENIED => FirstPartyIdentityEligibilityAdministrationViolation::RUNTIME_DENIED,
                DurablePersistenceViolation::RELATIONSHIP_CONFLICT => FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT,
                DurablePersistenceViolation::STORAGE_FAILURE => FirstPartyIdentityEligibilityAdministrationViolation::STORAGE_FAILURE,
                default => FirstPartyIdentityEligibilityAdministrationViolation::TRANSACTION_FAILURE,
            };

            $this->fail($code, 'Identity authentication eligibility administration transaction failed.');
        }
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

    private function fail(string $code, string $message): never
    {
        throw new FirstPartyIdentityEligibilityAdministrationViolation($code, $message);
    }
}
