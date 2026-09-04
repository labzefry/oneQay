<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final readonly class CashVarianceReviewDecisionResult
{
    public function __construct(
        private string $reviewEvidenceId,
        private string $operationId,
        private string $tenantId,
        private string $organizationId,
        private string $outletId,
        private string $shiftId,
        private string $openingCashEvidenceId,
        private string $closingCashEvidenceId,
        private string $cashVarianceExplanationEvidenceId,
        private string $explanationActorIdentityId,
        private string $reviewerActorIdentityId,
        private int $cutoffAtUnix,
        private int $expectedCashAtomic,
        private int $observedClosingCashAtomic,
        private int $varianceAtomic,
        private string $varianceDirection,
        private string $currency,
        private int $currencyScale,
        private string $explanationPayloadFingerprint,
        private string $reviewOutcome,
        private string $correlationId,
        private int $reviewedAtUnix,
    ) {}

    public function reviewEvidenceId(): string { return $this->reviewEvidenceId; }
    public function operationId(): string { return $this->operationId; }
    public function tenantId(): string { return $this->tenantId; }
    public function organizationId(): string { return $this->organizationId; }
    public function outletId(): string { return $this->outletId; }
    public function shiftId(): string { return $this->shiftId; }
    public function openingCashEvidenceId(): string { return $this->openingCashEvidenceId; }
    public function closingCashEvidenceId(): string { return $this->closingCashEvidenceId; }
    public function cashVarianceExplanationEvidenceId(): string { return $this->cashVarianceExplanationEvidenceId; }
    public function explanationActorIdentityId(): string { return $this->explanationActorIdentityId; }
    public function reviewerActorIdentityId(): string { return $this->reviewerActorIdentityId; }
    public function cutoffAtUnix(): int { return $this->cutoffAtUnix; }
    public function expectedCashAtomic(): int { return $this->expectedCashAtomic; }
    public function observedClosingCashAtomic(): int { return $this->observedClosingCashAtomic; }
    public function varianceAtomic(): int { return $this->varianceAtomic; }
    public function varianceDirection(): string { return $this->varianceDirection; }
    public function currency(): string { return $this->currency; }
    public function currencyScale(): int { return $this->currencyScale; }
    public function explanationPayloadFingerprint(): string { return $this->explanationPayloadFingerprint; }
    public function reviewOutcome(): string { return $this->reviewOutcome; }
    public function correlationId(): string { return $this->correlationId; }
    public function reviewedAtUnix(): int { return $this->reviewedAtUnix; }
}
