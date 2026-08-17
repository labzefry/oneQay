<?php

namespace App\Application\Persistence;

use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
final readonly class DurableContextGraph
{
    public function __construct(
        public TenantId $tenantId,
        public PlatformIdentityId $identityId,
        public OrganizationId $organizationId,
        public OutletId $outletId,
        public DeviceId $deviceId,
    ) {
    }

    public function equals(self $other): bool
    {
        return $this->tenantId->equals($other->tenantId)
            && $this->identityId->equals($other->identityId)
            && $this->organizationId->equals($other->organizationId)
            && $this->outletId->equals($other->outletId)
            && $this->deviceId->equals($other->deviceId);
    }
}
