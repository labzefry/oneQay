<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Domain\Pos\Money;

// Author by Lab | zefry
final readonly class ExpectedCashResult
{
    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $shiftId,
        private string $openingCashEvidenceId,
        private string $closingCashEvidenceId,
        private int $cutoffAtUnix,
        private Money $expectedCash,
    ) {}

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function shiftId(): string { return $this->shiftId; }
    public function openingCashEvidenceId(): string { return $this->openingCashEvidenceId; }
    public function closingCashEvidenceId(): string { return $this->closingCashEvidenceId; }
    public function cutoffAtUnix(): int { return $this->cutoffAtUnix; }
    public function expectedCash(): Money { return $this->expectedCash; }
}
