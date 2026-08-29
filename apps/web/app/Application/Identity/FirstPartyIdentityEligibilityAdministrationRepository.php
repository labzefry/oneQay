<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Identity\PlatformIdentityId;

// Author by Lab | zefry
interface FirstPartyIdentityEligibilityAdministrationRepository
{
    public const OPERATION_DISABLE = 'disable';
    public const OPERATION_REACTIVATE = 'reactivate';
    public const OUTCOME_APPLIED = 'applied';
    public const OUTCOME_NO_CHANGE = 'no_change';

    public function hasTenantControlAuthority(VerifiedOrganizationalContext $actor): bool;

    public function assertTargetEligible(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
    ): void;

    /** Returns a prior deterministic outcome for exact replay, or null when fresh. */
    public function replayOutcome(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        IdentityAuthenticationEligibilityMutationId $mutationId,
    ): ?string;

    /** Returns a prior deterministic reactivation outcome for exact replay, or null when fresh. */
    public function replayReactivationOutcome(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        IdentityAuthenticationEligibilityMutationId $mutationId,
    ): ?string;

    /** Called only inside PersistenceTransaction after Application preflight. */
    public function applyFresh(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        IdentityAuthenticationEligibilityMutationId $mutationId,
        int $occurredAtUnix,
    ): string;

    /** Called only inside PersistenceTransaction after Application preflight. */
    public function applyFreshReactivation(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        IdentityAuthenticationEligibilityMutationId $mutationId,
        int $occurredAtUnix,
    ): string;
}
