<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Money;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class ShiftOpeningCashCommand
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private string $operationId,
        private Money $openingCash,
    ) {
        if (preg_match(self::IDENTIFIER_PATTERN, $this->operationId) !== 1) {
            throw new InvalidArgumentException('Stable operation identifier format is invalid.');
        }
    }

    public function operationId(): string
    {
        return $this->operationId;
    }

    public function openingCash(): Money
    {
        return $this->openingCash;
    }

    public function semanticFingerprintPart(): string
    {
        return 'OPENING_CASH|'.$this->openingCash->canonicalFingerprintPart();
    }
}
