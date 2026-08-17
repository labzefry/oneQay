<?php

namespace App\Application\Persistence;

use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface DurableContextGraphRepository
{
    public function persist(DurableContextGraph $graph): void;

    public function findForTenant(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        DeviceId $deviceId,
    ): ?DurableContextGraph;
}
