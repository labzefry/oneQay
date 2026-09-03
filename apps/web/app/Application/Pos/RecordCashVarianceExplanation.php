<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class RecordCashVarianceExplanation
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private CashVarianceExplanationRepository $evidence,
        private OrganizationalContextStore $contexts,
        private PersistenceTransaction $transaction,
        private ShiftOpeningClock $clock,
    ) {}

    public function record(
        CashVarianceResult $variance,
        CashVarianceExplanationCommand $command,
        string $correlationId,
    ): CashVarianceExplanationResult {
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

        $recordedAtUnix = $this->clock->nowUnix();
        if ($recordedAtUnix <= 0) {
            throw new PosTransactionViolation();
        }

        return $this->transaction->run(
            fn (): CashVarianceExplanationResult => $this->evidence->record(
                $context,
                $variance,
                $command,
                $correlationId,
                $recordedAtUnix,
            ),
        );
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
