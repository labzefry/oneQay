<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Identity\PlatformIdentityId;

// Author by Lab | zefry
interface ProtectedControlAdministratorLifecycleRepository
{
    public const CONTROL_ROLE = InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE;
    public const CONTROL_PERMISSION = AdministrationPermission::MANAGE;
    public const OUTCOME_APPLIED = 'applied';
    public const OUTCOME_NO_CHANGE = 'no_change';

    public function hasTenantControlAuthority(VerifiedOrganizationalContext $actor): bool;

    public function assertTargetEligible(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
    ): void;

    public function assertProtectedRoleState(VerifiedOrganizationalContext $actor): void;

    /** Returns a prior deterministic outcome for exact replay, or null when fresh. */
    public function replayOutcome(
        VerifiedOrganizationalContext $actor,
        ProtectedControlAdministratorMutation $mutation,
    ): ?string;

    /** Validates last-principal and operation-specific preconditions before transaction entry. */
    public function assertOperationAllowed(
        VerifiedOrganizationalContext $actor,
        ProtectedControlAdministratorMutation $mutation,
    ): void;

    /** Called only inside PersistenceTransaction after Application preflight. */
    public function applyFresh(
        VerifiedOrganizationalContext $actor,
        ProtectedControlAdministratorMutation $mutation,
        int $occurredAtUnix,
    ): string;
}
