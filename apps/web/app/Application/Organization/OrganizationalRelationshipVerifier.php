<?php

namespace App\Application\Organization;

use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface OrganizationalRelationshipVerifier
{
    public function verify(
        PlatformIdentityId $identityId,
        TenantId $tenantId,
        OrganizationId $organizationId,
        ?OutletId $outletId,
        ?DeviceId $deviceId,
    ): bool;
}
