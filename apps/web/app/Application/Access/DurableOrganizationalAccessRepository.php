<?php

namespace App\Application\Access;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface DurableOrganizationalAccessRepository
{
    public function record(DurableOrganizationalAccessGrant $grant): void;

    public function hasTenantMembership(TenantId $tenantId, PlatformIdentityId $identityId): bool;

    public function allows(DurableOrganizationalAccessGrant $grant): bool;
}
