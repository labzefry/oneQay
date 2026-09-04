<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface CashVarianceReviewDecisionRepository
{
    public function resolveExplanation(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        string $cashVarianceExplanationEvidenceId,
    ): CashVarianceExplanationResult;

    public function record(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        CashVarianceExplanationResult $explanation,
        CashVarianceReviewDecisionCommand $command,
        string $correlationId,
        int $reviewedAtUnix,
    ): CashVarianceReviewDecisionResult;
}
