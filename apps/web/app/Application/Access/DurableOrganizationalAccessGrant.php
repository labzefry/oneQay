<?php

namespace App\Application\Access;

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class DurableOrganizationalAccessGrant
{
    public function __construct(
        public TenantId $tenantId,
        public PlatformIdentityId $identityId,
        public OrganizationId $organizationId,
        public ?OutletId $outletId = null,
        public ?DeviceId $deviceId = null,
    ) {
        if ($this->deviceId !== null && $this->outletId === null) {
            throw new InvalidArgumentException('A durable device access grant requires an outlet scope.');
        }
    }

    public static function fromVerifiedContext(VerifiedOrganizationalContext $context): self
    {
        return new self(
            $context->tenantId(),
            $context->identityId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
        );
    }
}
