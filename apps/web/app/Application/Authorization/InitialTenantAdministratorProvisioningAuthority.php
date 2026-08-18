<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface InitialTenantAdministratorProvisioningAuthority
{
    public function authorizes(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): bool;
}
