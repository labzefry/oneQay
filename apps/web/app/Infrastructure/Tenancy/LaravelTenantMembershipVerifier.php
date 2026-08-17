<?php

namespace App\Infrastructure\Tenancy;

use App\Application\Access\DurableOrganizationalAccessRepository;
use App\Application\Access\DurableOrganizationalAccessViolation;
use App\Application\Tenancy\TenantMembershipVerifier;
use App\Application\Tenancy\VerifiedTenantContext;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class LaravelTenantMembershipVerifier implements TenantMembershipVerifier
{
    public function __construct(private DurableOrganizationalAccessRepository $access)
    {
    }

    public function verify(string $principalId, string $tenantHint): ?VerifiedTenantContext
    {
        try {
            $identityId = PlatformIdentityId::fromString($principalId);
            $tenantId = TenantId::fromString($tenantHint);

            if (! $this->access->hasTenantMembership($tenantId, $identityId)) {
                return null;
            }

            return new ServerVerifiedTenantContext($tenantId);
        } catch (InvalidArgumentException|DurableOrganizationalAccessViolation) {
            return null;
        }
    }
}
