<?php

namespace App\Infrastructure\Tenancy;

use App\Application\Tenancy\TenantContextStore;
use App\Application\Tenancy\VerifiedTenantContext;
use App\Domain\Tenancy\TenantId;

final class RequestTenantContextStore implements TenantContextStore
{
    private ?VerifiedTenantContext $context = null;

    public function current(): ?VerifiedTenantContext
    {
        return $this->context;
    }

    public function setVerified(VerifiedTenantContext $context): void
    {
        TenantId::fromString($context->tenantId());
        $this->context = $context;
    }

    public function clear(): void
    {
        $this->context = null;
    }
}
