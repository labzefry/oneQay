<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final readonly class InventoryReplenishmentResult
{
    public function __construct(
        private string $replenishmentId,
        private string $operationId,
        private string $tenantId,
        private string $outletId,
        private string $productId,
        private int $beforeAvailableQuantity,
        private int $replenishedQuantity,
        private int $afterAvailableQuantity,
        private int $occurredAtUnix,
    ) {}

    public function replenishmentId(): string { return $this->replenishmentId; }
    public function operationId(): string { return $this->operationId; }
    public function tenantId(): string { return $this->tenantId; }
    public function outletId(): string { return $this->outletId; }
    public function productId(): string { return $this->productId; }
    public function beforeAvailableQuantity(): int { return $this->beforeAvailableQuantity; }
    public function replenishedQuantity(): int { return $this->replenishedQuantity; }
    public function afterAvailableQuantity(): int { return $this->afterAvailableQuantity; }
    public function occurredAtUnix(): int { return $this->occurredAtUnix; }
}
