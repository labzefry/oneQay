<?php

namespace App\Application\Tenancy;

interface TenantMembershipVerifier
{
    public function verify(string $principalId, string $tenantHint): ?VerifiedTenantContext;
}
