<?php

namespace App\Application\Tenancy;

use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

final class RequireVerifiedTenantContext
{
    public function require(?VerifiedTenantContext $context): VerifiedTenantContext
    {
        if ($context === null || trim($context->tenantId()) === '') {
            throw new MissingTenantContext('Verified tenant context is required.');
        }

        try {
            TenantId::fromString($context->tenantId());
        } catch (InvalidArgumentException) {
            throw new MissingTenantContext('Verified tenant context is invalid.');
        }

        return $context;
    }
}
