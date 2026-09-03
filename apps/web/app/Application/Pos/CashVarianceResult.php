<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final readonly class CashVarianceResult
{
    public const DIRECTION_MATCH = 'MATCH';
    public const DIRECTION_OVER = 'OVER';
    public const DIRECTION_SHORT = 'SHORT';

    public function __construct(
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $shiftId,
        private string $openingCashEvidenceId,
        private string $closingCashEvidenceId,
        private int $cutoffAtUnix,
        private int $expectedCashAtomic,
        private int $observedClosingAtomic,
        private int $varianceAtomic,
        private string $direction,
        private string $currency,
        private int $currencyScale,
    ) {}

    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function shiftId(): string { return $this->shiftId; }
    public function openingCashEvidenceId(): string { return $this->openingCashEvidenceId; }
    public function closingCashEvidenceId(): string { return $this->closingCashEvidenceId; }
    public function cutoffAtUnix(): int { return $this->cutoffAtUnix; }
    public function expectedCashAtomic(): int { return $this->expectedCashAtomic; }
    public function observedClosingAtomic(): int { return $this->observedClosingAtomic; }
    public function varianceAtomic(): int { return $this->varianceAtomic; }
    public function direction(): string { return $this->direction; }
    public function currency(): string { return $this->currency; }
    public function currencyScale(): int { return $this->currencyScale; }
}
