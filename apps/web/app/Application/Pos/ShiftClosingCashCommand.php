<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Money;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class ShiftClosingCashCommand
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private string $operationId,
        private Money $closingCash,
    ) {
        if (preg_match(self::IDENTIFIER_PATTERN, $this->operationId) !== 1) {
            throw new InvalidArgumentException('Stable operation identifier format is invalid.');
        }
    }

    public function operationId(): string
    {
        return $this->operationId;
    }

    public function closingCash(): Money
    {
        return $this->closingCash;
    }

    public function semanticFingerprintPart(): string
    {
        return 'CLOSING_CASH|'.$this->closingCash->canonicalFingerprintPart();
    }
}
