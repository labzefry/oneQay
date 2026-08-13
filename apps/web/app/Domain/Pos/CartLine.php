<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class CartLine
{
    public function __construct(
        private ProductId $productId,
        private int $quantity,
    ) {
        if ($this->quantity <= 0) {
            throw new InvalidArgumentException('Cart line quantity must be positive.');
        }
    }

    public function productId(): ProductId
    {
        return $this->productId;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function canonicalFingerprintPart(): string
    {
        return $this->productId->value().':'.$this->quantity;
    }
}
