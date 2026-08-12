<?php

namespace App\Application\Tenancy;

final class RequireVerifiedTenantContext
{
    public function require(?VerifiedTenantContext $context): VerifiedTenantContext
    {
        if ($context === null || trim($context->tenantId()) === '') {
            throw new MissingTenantContext('Verified tenant context is required.');
        }

        return $context;
    }
}
