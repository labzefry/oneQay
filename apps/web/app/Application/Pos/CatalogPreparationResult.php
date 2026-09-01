<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Money;

// Author by Lab | zefry
final readonly class CatalogPreparationResult
{
    public function __construct(
        private string $mutationId,
        private string $operationId,
        private string $tenantId,
        private string $outletId,
        private string $productId,
        private string $displayName,
        private Money $unitPrice,
        private bool $sellable,
    ) {}

    public function mutationId(): string { return $this->mutationId; }
    public function operationId(): string { return $this->operationId; }
    public function tenantId(): string { return $this->tenantId; }
    public function outletId(): string { return $this->outletId; }
    public function productId(): string { return $this->productId; }
    public function displayName(): string { return $this->displayName; }
    public function unitPrice(): Money { return $this->unitPrice; }
    public function sellable(): bool { return $this->sellable; }
}

// Sprint48 JRN-005 Sprint47 JRN-006 compatibility preservation anchor.
