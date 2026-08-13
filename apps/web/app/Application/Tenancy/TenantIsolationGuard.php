<?php

namespace App\Application\Tenancy;

use App\Domain\Tenancy\TenantId;
use App\Domain\Tenancy\TenantOwnedResourceReference;

final class TenantIsolationGuard
{
    public function __construct(private readonly RequireVerifiedTenantContext $requireContext)
    {
    }

    public function assertAccessible(
        ?VerifiedTenantContext $context,
        TenantOwnedResourceReference $resource,
    ): void {
        $verified = $this->requireContext->require($context);
        $contextTenant = TenantId::fromString($verified->tenantId());

        if (! $contextTenant->equals($resource->tenantId())) {
            throw new TenantIsolationViolation('Tenant isolation denied.');
        }
    }
}
