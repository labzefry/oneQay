<?php

namespace App\Infrastructure\Tenancy;

use App\Application\Tenancy\VerifiedTenantContext;
use App\Domain\Tenancy\TenantId;

final readonly class ServerVerifiedTenantContext implements VerifiedTenantContext
{
    public function __construct(private TenantId $tenantId)
    {
    }

    public function tenantId(): string
    {
        return $this->tenantId->value();
    }
}
