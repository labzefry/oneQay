<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\ProductId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class InventoryBaselineCommand
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private string $operationId,
        private ProductId $productId,
        private int $openingQuantity,
    ) {
        if (preg_match(self::IDENTIFIER_PATTERN, $this->operationId) !== 1) {
            throw new InvalidArgumentException('Stable operation identifier format is invalid.');
        }

        if ($this->openingQuantity < 0) {
            throw new InvalidArgumentException('Opening inventory quantity is invalid.');
        }
    }

    public function operationId(): string { return $this->operationId; }
    public function productId(): ProductId { return $this->productId; }
    public function openingQuantity(): int { return $this->openingQuantity; }

    public function semanticFingerprintPart(): string
    {
        return $this->productId->value().'|'.$this->openingQuantity;
    }
}
