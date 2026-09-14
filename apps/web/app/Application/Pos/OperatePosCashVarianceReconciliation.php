<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Organization\OrganizationalContextStore;

// Author by Lab | zefry
final readonly class OperatePosCashVarianceReconciliation
{
    public function __construct(
        private PosCashVarianceReconciliationWorkspaceRepository $workspace,
        private OrganizationalContextStore $contexts,
        private RecordCashVarianceExplanation $explanations,
        private RecordCashVarianceReviewDecision $reviews,
    ) {}

    public function recordExplanation(
        string $closingCashEvidenceId,
        string $operationId,
        string $explanationText,
        string $correlationId,
    ): CashVarianceExplanationResult {
        $context = PosExecutionContext::fromVerified($this->contexts->current());
        $variance = $this->workspace->resolveVariance($context, $closingCashEvidenceId);

        return $this->explanations->record(
            $variance,
            new CashVarianceExplanationCommand($operationId, $explanationText),
            $correlationId,
        );
    }

    public function recordReview(
        string $closingCashEvidenceId,
        string $operationId,
        string $explanationEvidenceId,
        string $reviewOutcome,
        string $correlationId,
    ): CashVarianceReviewDecisionResult {
        $context = PosExecutionContext::fromVerified($this->contexts->current());
        $variance = $this->workspace->resolveVariance($context, $closingCashEvidenceId);

        return $this->reviews->record(
            $variance,
            new CashVarianceReviewDecisionCommand(
                $operationId,
                $explanationEvidenceId,
                $reviewOutcome,
            ),
            $correlationId,
        );
    }
}
