<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface InitialTenantAdministratorProvisioningRepository
{
    public const CONTROL_ROLE = 'authorization-policy-administrator';
    public const CONTROL_PERMISSION = AdministrationPermission::MANAGE;
    public const OUTCOME_APPLIED = 'applied';

    public function assertTargetEligible(TenantId $tenantId, PlatformIdentityId $identityId): void;

    /** Returns the prior deterministic successful outcome for an exact replay, or null when fresh. */
    public function replayOutcome(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): ?string;

    public function assertUninitialized(TenantId $tenantId): void;

    /** Called only inside PersistenceTransaction after Application preflight. */
    public function applyFresh(
        InitialTenantAdministratorProvisioningAuthority $authority,
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        InitialTenantAdministratorProvisioningId $provisioningId,
        int $occurredAtUnix,
    ): string;
}
