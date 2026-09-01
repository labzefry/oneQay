<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class ShiftOpeningCommand
{
    private const IDENTIFIER_PATTERN = '/\\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\\z/';

    public function __construct(private string $operationId)
    {
        if (preg_match(self::IDENTIFIER_PATTERN, $this->operationId) !== 1) {
            throw new InvalidArgumentException('Stable operation identifier format is invalid.');
        }
    }

    public function operationId(): string
    {
        return $this->operationId;
    }

    public function semanticFingerprintPart(): string
    {
        return 'OPEN_SHIFT';
    }
}
