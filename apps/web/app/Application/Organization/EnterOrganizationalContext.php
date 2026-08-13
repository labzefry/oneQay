<?php

namespace App\Application\Organization;

use App\Application\Identity\RequireVerifiedPlatformIdentity;
use App\Application\Identity\VerifiedPlatformIdentity;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Application\Tenancy\TenantMembershipVerifier;
use App\Application\Tenancy\VerifiedTenantContext;
use App\Domain\Device\DeviceId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final class EnterOrganizationalContext
{
    public function __construct(
        private readonly RequireVerifiedPlatformIdentity $requireIdentity,
        private readonly RequireVerifiedTenantContext $requireTenant,
        private readonly TenantMembershipVerifier $memberships,
        private readonly OrganizationalRelationshipVerifier $relationships,
        private readonly OrganizationalContextStore $contexts,
    ) {
    }

    public function enter(
        ?VerifiedPlatformIdentity $identity,
        ?VerifiedTenantContext $tenantContext,
        string $organizationHint,
        ?string $outletHint = null,
        ?string $deviceHint = null,
    ): VerifiedOrganizationalContext {
        $this->contexts->clear();

        $identityId = $this->requireIdentity->require($identity);
        $verifiedTenant = $this->requireTenant->require($tenantContext);
        $tenantId = TenantId::fromString($verifiedTenant->tenantId());

        try {
            $organizationId = OrganizationId::fromString($organizationHint);
            $outletId = $outletHint === null ? null : OutletId::fromString($outletHint);
            $deviceId = $deviceHint === null ? null : DeviceId::fromString($deviceHint);
        } catch (InvalidArgumentException) {
            throw new OrganizationalAccessViolation('Organizational context denied.');
        }

        if ($deviceId !== null && $outletId === null) {
            throw new OrganizationalAccessViolation('Organizational context denied.');
        }

        $membership = $this->memberships->verify($identityId->value(), $tenantId->value());

        if ($membership === null) {
            throw new OrganizationalAccessViolation('Organizational context denied.');
        }

        try {
            $membershipTenant = TenantId::fromString($membership->tenantId());
        } catch (InvalidArgumentException) {
            throw new OrganizationalAccessViolation('Organizational context denied.');
        }

        if (! $tenantId->equals($membershipTenant)) {
            throw new OrganizationalAccessViolation('Organizational context denied.');
        }

        if (! $this->relationships->verify(
            $identityId,
            $tenantId,
            $organizationId,
            $outletId,
            $deviceId,
        )) {
            throw new OrganizationalAccessViolation('Organizational context denied.');
        }

        $context = new VerifiedOrganizationalContext(
            $identityId,
            $tenantId,
            $organizationId,
            $outletId,
            $deviceId,
        );

        $this->contexts->setVerified($context);

        return $context;
    }

    public function clear(): void
    {
        $this->contexts->clear();
    }
}
