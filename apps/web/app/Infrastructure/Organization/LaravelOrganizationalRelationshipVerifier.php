<?php

namespace App\Infrastructure\Organization;

use App\Application\Access\DurableOrganizationalAccessGrant;
use App\Application\Access\DurableOrganizationalAccessRepository;
use App\Application\Access\DurableOrganizationalAccessViolation;
use App\Application\Organization\OrganizationalRelationshipVerifier;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class LaravelOrganizationalRelationshipVerifier implements OrganizationalRelationshipVerifier
{
    public function __construct(private DurableOrganizationalAccessRepository $access)
    {
    }

    public function verify(
        PlatformIdentityId $identityId,
        TenantId $tenantId,
        OrganizationId $organizationId,
        ?OutletId $outletId,
        ?DeviceId $deviceId,
    ): bool {
        if ($deviceId !== null && $outletId === null) {
            return false;
        }

        try {
            return $this->access->allows(new DurableOrganizationalAccessGrant(
                $tenantId,
                $identityId,
                $organizationId,
                $outletId,
                $deviceId,
            ));
        } catch (InvalidArgumentException|DurableOrganizationalAccessViolation) {
            return false;
        }
    }
}
