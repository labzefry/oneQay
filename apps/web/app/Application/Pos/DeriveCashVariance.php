<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final readonly class DeriveCashVariance
{
    public function derive(
        ExpectedCashResult $expectedCash,
        ShiftClosingCashResult $closingCashEvidence,
    ): CashVarianceResult {
        $this->assertCanonicalInputs($expectedCash, $closingCashEvidence);

        $expectedMoney = $expectedCash->expectedCash();
        $observedMoney = $closingCashEvidence->closingCash();

        if (
            $expectedMoney->currency() !== $observedMoney->currency()
            || $expectedMoney->scale() !== $observedMoney->scale()
        ) {
            throw new PosTransactionViolation();
        }

        $expectedAtomic = $expectedMoney->atomicUnits();
        $observedAtomic = $observedMoney->atomicUnits();

        if ($observedAtomic >= $expectedAtomic) {
            $varianceAtomic = $observedAtomic - $expectedAtomic;
            $direction = $varianceAtomic === 0
                ? CashVarianceResult::DIRECTION_MATCH
                : CashVarianceResult::DIRECTION_OVER;
        } else {
            $magnitude = $expectedAtomic - $observedAtomic;
            $varianceAtomic = -$magnitude;
            $direction = CashVarianceResult::DIRECTION_SHORT;
        }

        return new CashVarianceResult(
            $expectedCash->tenantId(),
            $expectedCash->organizationId(),
            $expectedCash->outletId(),
            $expectedCash->shiftId(),
            $expectedCash->openingCashEvidenceId(),
            $expectedCash->closingCashEvidenceId(),
            $expectedCash->cutoffAtUnix(),
            $expectedAtomic,
            $observedAtomic,
            $varianceAtomic,
            $direction,
            $expectedMoney->currency(),
            $expectedMoney->scale(),
        );
    }

    private function assertCanonicalInputs(
        ExpectedCashResult $expectedCash,
        ShiftClosingCashResult $closingCashEvidence,
    ): void {
        foreach ([
            $expectedCash->tenantId(),
            $expectedCash->organizationId(),
            $expectedCash->outletId(),
            $expectedCash->shiftId(),
            $expectedCash->openingCashEvidenceId(),
            $expectedCash->closingCashEvidenceId(),
        ] as $identity) {
            if (trim($identity) === '') {
                throw new PosTransactionViolation();
            }
        }

        if ($expectedCash->cutoffAtUnix() <= 0) {
            throw new PosTransactionViolation();
        }

        if (
            $expectedCash->tenantId() !== $closingCashEvidence->tenantId()
            || $expectedCash->outletId() !== $closingCashEvidence->outletId()
            || $expectedCash->shiftId() !== $closingCashEvidence->shiftId()
            || $expectedCash->openingCashEvidenceId() !== $closingCashEvidence->openingCashEvidenceId()
            || $expectedCash->closingCashEvidenceId() !== $closingCashEvidence->evidenceId()
            || $expectedCash->cutoffAtUnix() !== $closingCashEvidence->recordedAtUnix()
        ) {
            throw new PosTransactionViolation();
        }
    }
}
