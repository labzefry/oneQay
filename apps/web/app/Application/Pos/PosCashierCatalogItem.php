<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosCashierCatalogItem
{
    public function __construct(
        private ProductId $productId,
        private string $displayName,
        private int $availableQuantity,
        private Money $unitPrice,
    ) {
        if (trim($this->displayName) === '' || $this->availableQuantity <= 0) {
            throw new InvalidArgumentException('Cashier catalog item is invalid.');
        }
    }

    public function productId(): string { return $this->productId->value(); }
    public function displayName(): string { return $this->displayName; }
    public function availableQuantity(): int { return $this->availableQuantity; }
    public function unitPrice(): Money { return $this->unitPrice; }
}
