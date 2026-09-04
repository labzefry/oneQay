<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class CashVarianceReviewDecisionCommand
{
    public const REVIEW_ACCEPTED = 'REVIEW_ACCEPTED';
    public const REVIEW_REJECTED = 'REVIEW_REJECTED';

    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private string $operationId,
        private string $cashVarianceExplanationEvidenceId,
        private string $reviewOutcome,
    ) {
        if (preg_match(self::IDENTIFIER_PATTERN, $this->operationId) !== 1) {
            throw new InvalidArgumentException('Stable operation identifier format is invalid.');
        }

        if (preg_match(self::IDENTIFIER_PATTERN, $this->cashVarianceExplanationEvidenceId) !== 1) {
            throw new InvalidArgumentException('Cash variance explanation evidence identifier format is invalid.');
        }

        if (! in_array($this->reviewOutcome, [self::REVIEW_ACCEPTED, self::REVIEW_REJECTED], true)) {
            throw new InvalidArgumentException('Cash variance review outcome is invalid.');
        }
    }

    public function operationId(): string
    {
        return $this->operationId;
    }

    public function cashVarianceExplanationEvidenceId(): string
    {
        return $this->cashVarianceExplanationEvidenceId;
    }

    public function reviewOutcome(): string
    {
        return $this->reviewOutcome;
    }

    public function semanticFingerprintPart(): string
    {
        return 'CASH_VARIANCE_REVIEW_DECISION|'.hash(
            'sha256',
            $this->cashVarianceExplanationEvidenceId.'|'.$this->reviewOutcome,
        );
    }
}
