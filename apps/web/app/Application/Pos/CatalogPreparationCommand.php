<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class CatalogPreparationCommand
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    private string $displayName;

    public function __construct(
        private string $operationId,
        private ProductId $productId,
        string $displayName,
        private Money $unitPrice,
        private bool $sellable,
        private string $correlationId,
    ) {
        if (preg_match(self::IDENTIFIER_PATTERN, $this->operationId) !== 1) {
            throw new InvalidArgumentException('Stable operation identifier format is invalid.');
        }

        if (preg_match(self::IDENTIFIER_PATTERN, $this->correlationId) !== 1) {
            throw new InvalidArgumentException('Correlation identifier format is invalid.');
        }

        $canonicalDisplayName = trim($displayName);
        if ($canonicalDisplayName === ''
            || strlen($canonicalDisplayName) > 160
            || preg_match('/[\x00-\x1F\x7F]/', $canonicalDisplayName) === 1) {
            throw new InvalidArgumentException('Catalog display name is invalid.');
        }

        $this->displayName = $canonicalDisplayName;
    }

    public function operationId(): string { return $this->operationId; }
    public function productId(): ProductId { return $this->productId; }
    public function displayName(): string { return $this->displayName; }
    public function unitPrice(): Money { return $this->unitPrice; }
    public function sellable(): bool { return $this->sellable; }
    public function correlationId(): string { return $this->correlationId; }

    public function semanticFingerprintPart(): string
    {
        return implode('|', [
            $this->productId->value(),
            $this->displayName,
            $this->unitPrice->canonicalFingerprintPart(),
            $this->sellable ? 'sellable' : 'not-sellable',
        ]);
    }
}
