<?php

declare(strict_types=1);

namespace App\Domain\Pos;

// Author by Lab | zefry
final readonly class SaleLineResult
{
    public function __construct(
        private ProductId $productId,
        private int $quantity,
        private Money $unitPrice,
        private Money $lineTotal,
    ) {
    }

    public function productId(): ProductId { return $this->productId; }
    public function quantity(): int { return $this->quantity; }
    public function unitPrice(): Money { return $this->unitPrice; }
    public function lineTotal(): Money { return $this->lineTotal; }
}
