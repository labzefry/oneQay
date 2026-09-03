<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class CashVarianceExplanationCommand
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';
    private const MAX_EXPLANATION_BYTES = 4096;

    private string $explanationText;

    public function __construct(
        private string $operationId,
        string $explanationText,
    ) {
        if (preg_match(self::IDENTIFIER_PATTERN, $this->operationId) !== 1) {
            throw new InvalidArgumentException('Stable operation identifier format is invalid.');
        }

        $canonical = str_replace(["\r\n", "\r"], "\n", $explanationText);
        $canonical = trim($canonical);

        if ($canonical === '') {
            throw new InvalidArgumentException('Cash variance explanation must not be empty.');
        }

        if (strlen($canonical) > self::MAX_EXPLANATION_BYTES) {
            throw new InvalidArgumentException('Cash variance explanation exceeds the supported byte length.');
        }

        if (preg_match('//u', $canonical) !== 1 || str_contains($canonical, "\0")) {
            throw new InvalidArgumentException('Cash variance explanation must be valid UTF-8 text.');
        }

        $this->explanationText = $canonical;
    }

    public function operationId(): string
    {
        return $this->operationId;
    }

    public function explanationText(): string
    {
        return $this->explanationText;
    }

    public function semanticFingerprintPart(): string
    {
        return 'CASH_VARIANCE_EXPLANATION|'.hash('sha256', $this->explanationText);
    }
}
