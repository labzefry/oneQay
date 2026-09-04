<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class RecordCashVarianceReviewDecision
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private CashVarianceReviewDecisionRepository $evidence,
        private OrganizationalContextStore $contexts,
        private DurableScopedAuthorizationPolicy $authorization,
        private PersistenceTransaction $transaction,
        private ShiftOpeningClock $clock,
    ) {}

    public function record(
        CashVarianceResult $variance,
        CashVarianceReviewDecisionCommand $command,
        string $correlationId,
    ): CashVarianceReviewDecisionResult {
        if (preg_match(self::IDENTIFIER_PATTERN, $correlationId) !== 1) {
            throw new InvalidArgumentException('Correlation identifier format is invalid.');
        }

        $this->assertCanonicalNonZeroVariance($variance);

        $verified = $this->contexts->current();
        $context = PosExecutionContext::fromVerified($verified);

        if (
            $context->tenantId() !== $variance->tenantId()
            || $context->organizationId() !== $variance->organizationId()
            || $context->outletId() !== $variance->outletId()
        ) {
            throw new PosTransactionViolation();
        }

        $this->authorization->require(
            $verified,
            PosPermission::recordCashVarianceReviewDecision(),
        );

        $explanation = $this->evidence->resolveExplanation(
            $context,
            $variance,
            $command->cashVarianceExplanationEvidenceId(),
        );

        $this->assertExplanationBinding($context, $variance, $explanation, $command);

        if (hash_equals($context->actorId(), $explanation->actorIdentityId())) {
            throw new PosTransactionViolation();
        }

        $reviewedAtUnix = $this->clock->nowUnix();
        if ($reviewedAtUnix <= 0) {
            throw new PosTransactionViolation();
        }

        return $this->transaction->run(
            fn (): CashVarianceReviewDecisionResult => $this->evidence->record(
                $context,
                $variance,
                $explanation,
                $command,
                $correlationId,
                $reviewedAtUnix,
            ),
        );
    }

    private function assertExplanationBinding(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        CashVarianceExplanationResult $explanation,
        CashVarianceReviewDecisionCommand $command,
    ): void {
        if (
            ! hash_equals($command->cashVarianceExplanationEvidenceId(), $explanation->evidenceId())
            || ! hash_equals($context->tenantId(), $explanation->tenantId())
            || ! hash_equals($variance->tenantId(), $explanation->tenantId())
            || ! hash_equals($variance->organizationId(), $explanation->organizationId())
            || ! hash_equals($variance->outletId(), $explanation->outletId())
            || ! hash_equals($variance->shiftId(), $explanation->shiftId())
            || ! hash_equals($variance->openingCashEvidenceId(), $explanation->openingCashEvidenceId())
            || ! hash_equals($variance->closingCashEvidenceId(), $explanation->closingCashEvidenceId())
            || $variance->cutoffAtUnix() !== $explanation->cutoffAtUnix()
            || $variance->expectedCashAtomic() !== $explanation->expectedCashAtomic()
            || $variance->observedClosingAtomic() !== $explanation->observedClosingCashAtomic()
            || $variance->varianceAtomic() !== $explanation->varianceAtomic()
            || ! hash_equals($variance->direction(), $explanation->varianceDirection())
            || ! hash_equals($variance->currency(), $explanation->currency())
            || $variance->currencyScale() !== $explanation->currencyScale()
            || trim($explanation->actorIdentityId()) === ''
            || trim($explanation->explanationText()) === ''
        ) {
            throw new PosTransactionViolation();
        }
    }

    private function assertCanonicalNonZeroVariance(CashVarianceResult $variance): void
    {
        foreach ([
            $variance->tenantId(),
            $variance->organizationId(),
            $variance->outletId(),
            $variance->shiftId(),
            $variance->openingCashEvidenceId(),
            $variance->closingCashEvidenceId(),
        ] as $identity) {
            if (trim($identity) === '') {
                throw new PosTransactionViolation();
            }
        }

        if (
            $variance->cutoffAtUnix() <= 0
            || $variance->expectedCashAtomic() < 0
            || $variance->observedClosingAtomic() < 0
            || preg_match('/\A[A-Z]{3}\z/', $variance->currency()) !== 1
            || $variance->currencyScale() < 0
            || $variance->currencyScale() > 6
        ) {
            throw new PosTransactionViolation();
        }

        $this->assertVarianceArithmetic(
            $variance->expectedCashAtomic(),
            $variance->observedClosingAtomic(),
            $variance->varianceAtomic(),
            $variance->direction(),
        );
    }

    private function assertVarianceArithmetic(
        int $expectedAtomic,
        int $observedAtomic,
        int $varianceAtomic,
        string $direction,
    ): void {
        if ($observedAtomic >= $expectedAtomic) {
            $delta = $observedAtomic - $expectedAtomic;
            if (
                $delta <= 0
                || $direction !== CashVarianceResult::DIRECTION_OVER
                || $varianceAtomic !== $delta
            ) {
                throw new PosTransactionViolation();
            }

            return;
        }

        $magnitude = $expectedAtomic - $observedAtomic;
        if (
            $magnitude <= 0
            || $direction !== CashVarianceResult::DIRECTION_SHORT
            || $varianceAtomic !== -$magnitude
        ) {
            throw new PosTransactionViolation();
        }
    }
}
