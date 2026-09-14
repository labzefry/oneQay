<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PosCashVarianceReconciliationWorkspaceSnapshot
{
    public function __construct(
        private PosCashVarianceReconciliationWorkspaceData $data,
        private bool $canExplain,
        private bool $canReview,
    ) {
        $selected = $data->selected();
        if ($selected === null) {
            return;
        }

        $direction = $selected['variance_direction'] ?? null;
        if (! in_array($direction, [
            CashVarianceResult::DIRECTION_MATCH,
            CashVarianceResult::DIRECTION_OVER,
            CashVarianceResult::DIRECTION_SHORT,
        ], true)) {
            throw new InvalidArgumentException('Cash variance reconciliation direction is invalid.');
        }

        $explanation = $selected['explanation'] ?? null;
        $review = $selected['review'] ?? null;
        if ($review !== null && $explanation === null) {
            throw new InvalidArgumentException('Cash variance review cannot exist without explanation evidence.');
        }

        if ($review !== null && ! in_array(
            $review['outcome'] ?? null,
            [
                CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
                CashVarianceReviewDecisionCommand::REVIEW_REJECTED,
            ],
            true,
        )) {
            throw new InvalidArgumentException('Cash variance review outcome is invalid.');
        }

        if ($direction === CashVarianceResult::DIRECTION_MATCH && ($explanation !== null || $review !== null)) {
            throw new InvalidArgumentException('MATCH variance cannot carry explanation or review evidence.');
        }
    }

    public function tenantId(): string { return $this->data->tenantId(); }
    public function organizationId(): string { return $this->data->organizationId(); }
    public function outletId(): string { return $this->data->outletId(); }
    public function actorId(): string { return $this->data->actorId(); }
    public function canExplain(): bool { return $this->canExplain; }
    public function canReview(): bool { return $this->canReview; }

    /** @return list<array<string, mixed>> */
    public function cases(): array { return $this->data->cases(); }

    /** @return array<string, mixed>|null */
    public function selected(): ?array
    {
        $selected = $this->data->selected();
        if ($selected === null) {
            return null;
        }

        $direction = (string) $selected['variance_direction'];
        $explanation = $selected['explanation'];
        $review = $selected['review'];
        $nonZero = $direction !== CashVarianceResult::DIRECTION_MATCH;
        $selfReview = is_array($explanation)
            && hash_equals($this->data->actorId(), (string) $explanation['actor_identity_id']);
        $reviewOutcome = is_array($review) ? (string) $review['outcome'] : null;

        return $selected + [
            'can_record_explanation' => $this->canExplain && $nonZero && $explanation === null,
            'can_record_review' => $this->canReview
                && $nonZero
                && is_array($explanation)
                && $review === null
                && ! $selfReview,
            'review_ready_for_final_close' => $direction === CashVarianceResult::DIRECTION_MATCH
                || $reviewOutcome === CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
            'review_rejected_terminal' => $reviewOutcome === CashVarianceReviewDecisionCommand::REVIEW_REJECTED,
            'self_review_blocked' => $selfReview && $review === null,
        ];
    }
}
