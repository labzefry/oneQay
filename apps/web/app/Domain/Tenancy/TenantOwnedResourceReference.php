<?php

namespace App\Domain\Tenancy;

use InvalidArgumentException;

final readonly class TenantOwnedResourceReference
{
    public function __construct(
        private string $resourceId,
        private TenantId $tenantId,
    ) {
        if (trim($resourceId) === '' || strlen($resourceId) > 128) {
            throw new InvalidArgumentException('Resource identifier is required and must not exceed 128 characters.');
        }
    }

    public function resourceId(): string
    {
        return $this->resourceId;
    }

    public function tenantId(): TenantId
    {
        return $this->tenantId;
    }
}
