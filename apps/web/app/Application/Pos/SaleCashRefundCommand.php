<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class SaleCashRefundCommand
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';
    private const SALE_ID_PATTERN = '/\Asale-[a-f0-9]{24}\z/';

    public function __construct(
        private string $operationId,
        private string $saleId,
    ) {
        if (preg_match(self::IDENTIFIER_PATTERN, $this->operationId) !== 1) {
            throw new InvalidArgumentException('Stable operation identifier format is invalid.');
        }

        if (preg_match(self::SALE_ID_PATTERN, $this->saleId) !== 1) {
            throw new InvalidArgumentException('Canonical sale identifier format is invalid.');
        }
    }

    public function operationId(): string
    {
        return $this->operationId;
    }

    public function saleId(): string
    {
        return $this->saleId;
    }

    public function semanticFingerprintPart(): string
    {
        return 'FULL_CASH_REFUND|'.$this->saleId;
    }
}
