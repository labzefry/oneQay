<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry
interface DurablePolicyAdministrationRepository
{
    /** Returns the prior deterministic outcome for an exact replay, or null for a fresh mutation. */
    public function replayOutcome(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): ?string;

    /**
     * Confirms that the acting principal's control permission is assigned at the
     * requested mutation scope or an ancestor scope. Narrower control authority
     * must never authorize a broader mutation.
     */
    public function hasControlAuthorityForScope(
        VerifiedOrganizationalContext $actor,
        PolicyAssignmentScope $scope,
    ): bool;

    public function isProtectedControlRole(VerifiedOrganizationalContext $actor, RoleIdentifier $role): bool;

    public function assertTargetEligible(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): void;

    /** Called only inside PersistenceTransaction after preflight. */
    public function applyFresh(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation, int $occurredAtUnix): string;
}
