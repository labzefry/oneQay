<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final readonly class DeriveExpectedCash
{
    public function __construct(private ExpectedCashRepository $repository) {}

    public function derive(ShiftClosingCashResult $closingCashEvidence): ExpectedCashResult
    {
        return $this->repository->deriveFrom($closingCashEvidence);
    }
}
