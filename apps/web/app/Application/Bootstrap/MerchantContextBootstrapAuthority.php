<?php

declare(strict_types=1);

namespace App\Application\Bootstrap;

use App\Application\Authorization\InitialTenantAdministratorProvisioningAuthority;
use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Persistence\DurableContextGraph;

// Author by Lab | zefry
interface MerchantContextBootstrapAuthority extends InitialTenantAdministratorProvisioningAuthority
{
    public function authorizesBootstrap(
        DurableContextGraph $graph,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): bool;
}
