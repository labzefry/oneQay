<?php

namespace App\Application\Organization;

use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class VerifiedOrganizationalContext
{
    public function __construct(
        private PlatformIdentityId $identityId,
        private TenantId $tenantId,
        private OrganizationId $organizationId,
        private ?OutletId $outletId = null,
        private ?DeviceId $deviceId = null,
    ) {
        if ($this->deviceId !== null && $this->outletId === null) {
            throw new InvalidArgumentException('A verified device context requires a verified outlet context.');
        }
    }

    public function identityId(): PlatformIdentityId
    {
        return $this->identityId;
    }

    public function tenantId(): TenantId
    {
        return $this->tenantId;
    }

    public function organizationId(): OrganizationId
    {
        return $this->organizationId;
    }

    public function outletId(): ?OutletId
    {
        return $this->outletId;
    }

    public function deviceId(): ?DeviceId
    {
        return $this->deviceId;
    }
}
