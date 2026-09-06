<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final readonly class CloseShiftResult
{
    public function __construct(
        private string $evidenceId,
        private string $operationId,
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $deviceId,
        private string $shiftId,
        private string $openingCashEvidenceId,
        private string $closingCashEvidenceId,
        private string $closerActorIdentityId,
        private int $cutoffAtUnix,
        private int $expectedCashAtomic,
        private int $observedClosingCashAtomic,
        private int $varianceAtomic,
        private string $varianceDirection,
        private string $currency,
        private int $currencyScale,
        private ?string $reviewEvidenceId,
        private ?string $reviewOutcome,
        private string $correlationId,
        private int $closedAtUnix,
    ) {}

    public function evidenceId(): string { return $this->evidenceId; }
    public function operationId(): string { return $this->operationId; }
    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function deviceId(): string { return $this->deviceId; }
    public function shiftId(): string { return $this->shiftId; }
    public function openingCashEvidenceId(): string { return $this->openingCashEvidenceId; }
    public function closingCashEvidenceId(): string { return $this->closingCashEvidenceId; }
    public function closerActorIdentityId(): string { return $this->closerActorIdentityId; }
    public function cutoffAtUnix(): int { return $this->cutoffAtUnix; }
    public function expectedCashAtomic(): int { return $this->expectedCashAtomic; }
    public function observedClosingCashAtomic(): int { return $this->observedClosingCashAtomic; }
    public function varianceAtomic(): int { return $this->varianceAtomic; }
    public function varianceDirection(): string { return $this->varianceDirection; }
    public function currency(): string { return $this->currency; }
    public function currencyScale(): int { return $this->currencyScale; }
    public function reviewEvidenceId(): ?string { return $this->reviewEvidenceId; }
    public function reviewOutcome(): ?string { return $this->reviewOutcome; }
    public function correlationId(): string { return $this->correlationId; }
    public function closedAtUnix(): int { return $this->closedAtUnix; }
}
