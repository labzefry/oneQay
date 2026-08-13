<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Cart;
use App\Domain\Pos\Money;
use App\Domain\Pos\TenderCategory;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class SaleCommand
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private string $operationId,
        private Cart $cart,
        private TenderCategory $tenderCategory,
        private Money $tenderedAmount,
        private string $correlationId,
    ) {
        if (preg_match(self::IDENTIFIER_PATTERN, $this->operationId) !== 1) {
            throw new InvalidArgumentException('Stable operation identifier format is invalid.');
        }

        if (preg_match(self::IDENTIFIER_PATTERN, $this->correlationId) !== 1) {
            throw new InvalidArgumentException('Correlation identifier format is invalid.');
        }
    }

    public function operationId(): string { return $this->operationId; }
    public function cart(): Cart { return $this->cart; }
    public function tenderCategory(): TenderCategory { return $this->tenderCategory; }
    public function tenderedAmount(): Money { return $this->tenderedAmount; }
    public function correlationId(): string { return $this->correlationId; }

    public function semanticFingerprintPart(): string
    {
        return implode('|', [
            $this->cart->canonicalFingerprintPart(),
            $this->tenderCategory->value,
            $this->tenderedAmount->canonicalFingerprintPart(),
        ]);
    }
}
