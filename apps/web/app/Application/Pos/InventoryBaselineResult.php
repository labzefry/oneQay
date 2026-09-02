<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final readonly class InventoryBaselineResult
{
    public function __construct(
        private string $baselineId,
        private string $operationId,
        private string $tenantId,
        private string $outletId,
        private string $productId,
        private int $openingQuantity,
    ) {}

    public function baselineId(): string { return $this->baselineId; }
    public function operationId(): string { return $this->operationId; }
    public function tenantId(): string { return $this->tenantId; }
    public function outletId(): string { return $this->outletId; }
    public function productId(): string { return $this->productId; }
    public function openingQuantity(): int { return $this->openingQuantity; }
}
