<?php

namespace App\Application\Tenancy;

interface VerifiedTenantContext
{
    public function tenantId(): string;
}
