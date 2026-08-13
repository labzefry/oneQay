<?php

namespace App\Application\Tenancy;

interface TenantContextStore
{
    public function current(): ?VerifiedTenantContext;

    public function setVerified(VerifiedTenantContext $context): void;

    public function clear(): void;
}
