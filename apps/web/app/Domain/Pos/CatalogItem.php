<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class CatalogItem
{
    public function __construct(
        private TenantId $tenantId,
        private OutletId $outletId,
        private ProductId $productId,
        private string $displayName,
        private Money $unitPrice,
    ) {
        $canonicalName = trim($this->displayName);

        if ($canonicalName === '' || strlen($canonicalName) > 160) {
            throw new InvalidArgumentException('Catalog display name is required and must not exceed 160 characters.');
        }
    }

    public function tenantId(): TenantId
    {
        return $this->tenantId;
    }

    public function outletId(): OutletId
    {
        return $this->outletId;
    }

    public function productId(): ProductId
    {
        return $this->productId;
    }

    public function displayName(): string
    {
        return trim($this->displayName);
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }
}
