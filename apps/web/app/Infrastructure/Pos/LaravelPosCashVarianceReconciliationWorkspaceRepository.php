<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\CashVarianceReviewDecisionCommand;
use App\Application\Pos\DeriveCashVariance;
use App\Application\Pos\PosCashVarianceReconciliationWorkspaceData;
use App\Application\Pos\PosCashVarianceReconciliationWorkspaceRepository;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftClosingCashResult;
use App\Domain\Pos\Money;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelPosCashVarianceReconciliationWorkspaceRepository implements PosCashVarianceReconciliationWorkspaceRepository
{
    private const MAX_CASES = 50;
    private const CLOSING_MODE = 'OPERATOR_OBSERVED_CLOSING_CASH';
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private Connection $connection,
        private LaravelExpectedCashSnapshotReader $expectedCash,
        private DeriveCashVariance $cashVariance,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
        private bool $closingCashEnabled,
    ) {}

    public function read(
        PosExecutionContext $context,
        ?string $selectedClosingCashEvidenceId,
    ): PosCashVarianceReconciliationWorkspaceData {
        $this->assertOperational();
        if ($selectedClosingCashEvidenceId !== null) {
            $this->assertIdentifier($selectedClosingCashEvidenceId);
        }

        try {
            $rows = $this->connection
                ->table('oneqay_pos_shift_closing_cash_evidence as closing')
                ->join('oneqay_pos_shifts as shifts', function ($join): void {
                    $join->on('shifts.tenant_id', '=', 'closing.tenant_id')
                        ->on('shifts.shift_id', '=', 'closing.shift_id');
                })
                ->where('closing.tenant_id', $context->tenantId())
                ->where('closing.organization_id', $context->organizationId())
                ->where('closing.outlet_id', $context->outletId())
                ->where('shifts.organization_id', $context->organizationId())
                ->where('shifts.outlet_id', $context->outletId())
                ->where('shifts.active_slot', 1)
                ->orderByDesc('closing.recorded_at_unix')
                ->limit(self::MAX_CASES)
                ->get([
                    'closing.evidence_id as closing_evidence_id',
                    'closing.shift_id',
                    'closing.device_id',
                    'closing.actor_identity_id as closing_actor_identity_id',
                    'closing.recorded_at_unix',
                    'closing.evidence_mode',
                    'shifts.device_id as shift_device_id',
                ]);

            $closingIds = [];
            $baseCases = [];
            foreach ($rows as $row) {
                if (! is_object($row)) {
                    throw new PosTransactionViolation();
                }

                $closingId = $this->requiredIdentifier($row->closing_evidence_id ?? null);
                $shiftId = $this->requiredString($row->shift_id ?? null);
                $deviceId = $this->requiredString($row->device_id ?? null);
                $this->assertEquals($row->shift_device_id ?? null, $deviceId);
                $this->assertEquals($row->evidence_mode ?? null, self::CLOSING_MODE);

                if (isset($baseCases[$closingId])) {
                    throw new PosTransactionViolation();
                }

                $closingIds[] = $closingId;
                $baseCases[$closingId] = [
                    'closing_evidence_id' => $closingId,
                    'shift_id' => $shiftId,
                    'device_id' => $deviceId,
                    'closing_actor_identity_id' => $this->requiredString($row->closing_actor_identity_id ?? null),
                    'recorded_at_unix' => $this->safeUnsignedInt($row->recorded_at_unix ?? null),
                ];
            }

            $explanations = $this->explanationIndex($context, $closingIds);
            $reviews = $this->reviewIndex($context, $explanations);

            $cases = [];
            foreach ($baseCases as $closingId => $case) {
                $explanation = $explanations[$closingId] ?? null;
                $review = $explanation === null ? null : ($reviews[$explanation['evidence_id']] ?? null);
                $cases[] = $case + [
                    'has_explanation' => $explanation !== null,
                    'has_review' => $review !== null,
                    'review_outcome' => $review['outcome'] ?? null,
                ];
            }

            $selectedId = $selectedClosingCashEvidenceId;
            if ($selectedId === null && $cases !== []) {
                $selectedId = (string) $cases[0]['closing_evidence_id'];
            }
            if ($selectedId !== null && ! isset($baseCases[$selectedId])) {
                throw new PosTransactionViolation();
            }

            $selected = $selectedId === null
                ? null
                : $this->selectedState($context, $selectedId)[1];

            return new PosCashVarianceReconciliationWorkspaceData(
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $context->actorId(),
                $cases,
                $selected,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    public function resolveVariance(
        PosExecutionContext $context,
        string $closingCashEvidenceId,
    ): CashVarianceResult {
        $this->assertOperational();
        $this->assertIdentifier($closingCashEvidenceId);

        return $this->selectedState($context, $closingCashEvidenceId)[0];
    }

    /** @return array{0:CashVarianceResult,1:array<string,mixed>} */
    private function selectedState(PosExecutionContext $context, string $closingEvidenceId): array
    {
        try {
            return $this->connection->transaction(function () use ($context, $closingEvidenceId): array {
                $closingRows = $this->connection
                    ->table('oneqay_pos_shift_closing_cash_evidence')
                    ->where('tenant_id', $context->tenantId())
                    ->where('organization_id', $context->organizationId())
                    ->where('outlet_id', $context->outletId())
                    ->where('evidence_id', $closingEvidenceId)
                    ->get();
                if ($closingRows->count() !== 1 || ! is_object($closingRows->first())) {
                    throw new PosTransactionViolation();
                }
                $closing = $closingRows->first();

                $shiftId = $this->requiredString($closing->shift_id ?? null);
                $shiftRows = $this->connection
                    ->table('oneqay_pos_shifts')
                    ->where('tenant_id', $context->tenantId())
                    ->where('organization_id', $context->organizationId())
                    ->where('outlet_id', $context->outletId())
                    ->where('shift_id', $shiftId)
                    ->where('active_slot', 1)
                    ->get();
                if ($shiftRows->count() !== 1 || ! is_object($shiftRows->first())) {
                    throw new PosTransactionViolation();
                }
                $shift = $shiftRows->first();

                $deviceId = $this->requiredString($closing->device_id ?? null);
                $this->assertEquals($shift->device_id ?? null, $deviceId);
                $this->assertEquals($closing->evidence_mode ?? null, self::CLOSING_MODE);

                $closingCash = Money::fromAtomicUnits(
                    $this->safeUnsignedInt($closing->closing_cash_atomic ?? null),
                    $this->canonicalCurrency($closing->currency ?? null),
                    $this->safeScale($closing->currency_scale ?? null),
                );
                $closingResult = new ShiftClosingCashResult(
                    $this->requiredIdentifier($closing->evidence_id ?? null),
                    $this->requiredIdentifier($closing->opening_cash_evidence_id ?? null),
                    $shiftId,
                    $this->requiredIdentifier($closing->operation_id ?? null),
                    $context->tenantId(),
                    $context->outletId(),
                    $deviceId,
                    $closingCash,
                    self::CLOSING_MODE,
                    $this->requiredIdentifier($closing->correlation_id ?? null),
                    $this->safeUnsignedInt($closing->recorded_at_unix ?? null),
                );

                $expected = $this->expectedCash->deriveFrom($closingResult);
                $variance = $this->cashVariance->derive($expected, $closingResult);

                $explanation = $this->selectedExplanation($context, $variance);
                $review = $explanation === null
                    ? null
                    : $this->selectedReview($context, $variance, $explanation);

                if ($variance->direction() === CashVarianceResult::DIRECTION_MATCH
                    && ($explanation !== null || $review !== null)) {
                    throw new PosTransactionViolation();
                }

                return [$variance, [
                    'closing_evidence_id' => $variance->closingCashEvidenceId(),
                    'shift_id' => $variance->shiftId(),
                    'device_id' => $deviceId,
                    'opener_actor_identity_id' => $this->requiredString($shift->actor_identity_id ?? null),
                    'closing_actor_identity_id' => $this->requiredString($closing->actor_identity_id ?? null),
                    'recorded_at_unix' => $variance->cutoffAtUnix(),
                    'expected_cash_atomic' => $variance->expectedCashAtomic(),
                    'observed_closing_cash_atomic' => $variance->observedClosingAtomic(),
                    'variance_atomic' => $variance->varianceAtomic(),
                    'variance_direction' => $variance->direction(),
                    'currency' => $variance->currency(),
                    'currency_scale' => $variance->currencyScale(),
                    'explanation' => $explanation,
                    'review' => $review,
                ]];
            }, 1);
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    /** @return array<string,array{evidence_id:string,actor_identity_id:string}> */
    private function explanationIndex(PosExecutionContext $context, array $closingIds): array
    {
        if ($closingIds === []) {
            return [];
        }

        $index = [];
        $rows = $this->connection
            ->table('oneqay_pos_cash_variance_explanation_evidence')
            ->where('tenant_id', $context->tenantId())
            ->whereIn('closing_cash_evidence_id', $closingIds)
            ->get();
        foreach ($rows as $row) {
            if (! is_object($row)) {
                throw new PosTransactionViolation();
            }
            $this->assertEquals($row->organization_id ?? null, $context->organizationId());
            $this->assertEquals($row->outlet_id ?? null, $context->outletId());
            $closingId = $this->requiredIdentifier($row->closing_cash_evidence_id ?? null);
            if (isset($index[$closingId])) {
                throw new PosTransactionViolation();
            }
            $index[$closingId] = [
                'evidence_id' => $this->requiredIdentifier($row->evidence_id ?? null),
                'actor_identity_id' => $this->requiredString($row->actor_identity_id ?? null),
            ];
        }

        return $index;
    }

    /** @param array<string,array{evidence_id:string,actor_identity_id:string}> $explanations */
    private function reviewIndex(PosExecutionContext $context, array $explanations): array
    {
        if ($explanations === []) {
            return [];
        }

        $ids = array_map(static fn (array $value): string => $value['evidence_id'], array_values($explanations));
        $index = [];
        $rows = $this->connection
            ->table('oneqay_pos_cash_variance_review_decision_evidence')
            ->where('tenant_id', $context->tenantId())
            ->whereIn('cash_variance_explanation_evidence_id', $ids)
            ->get();
        foreach ($rows as $row) {
            if (! is_object($row)) {
                throw new PosTransactionViolation();
            }
            $this->assertEquals($row->organization_id ?? null, $context->organizationId());
            $this->assertEquals($row->outlet_id ?? null, $context->outletId());
            $explanationId = $this->requiredIdentifier($row->cash_variance_explanation_evidence_id ?? null);
            if (isset($index[$explanationId])) {
                throw new PosTransactionViolation();
            }
            $outcome = $this->reviewOutcome($row->review_outcome ?? null);
            $index[$explanationId] = ['outcome' => $outcome];
        }

        return $index;
    }

    /** @return array{evidence_id:string,actor_identity_id:string,text:string,recorded_at_unix:int}|null */
    private function selectedExplanation(PosExecutionContext $context, CashVarianceResult $variance): ?array
    {
        $rows = $this->connection
            ->table('oneqay_pos_cash_variance_explanation_evidence')
            ->where('tenant_id', $context->tenantId())
            ->where('closing_cash_evidence_id', $variance->closingCashEvidenceId())
            ->get();
        if ($rows->count() > 1) {
            throw new PosTransactionViolation();
        }
        $row = $rows->first();
        if ($row === null) {
            return null;
        }
        if (! is_object($row)) {
            throw new PosTransactionViolation();
        }

        $this->assertVarianceEvidence($row, $context, $variance);
        $text = $this->requiredString($row->explanation_text ?? null);
        if (strlen($text) > 4096 || preg_match('//u', $text) !== 1 || str_contains($text, "\0")) {
            throw new PosTransactionViolation();
        }

        return [
            'evidence_id' => $this->requiredIdentifier($row->evidence_id ?? null),
            'actor_identity_id' => $this->requiredString($row->actor_identity_id ?? null),
            'text' => $text,
            'recorded_at_unix' => $this->safeUnsignedInt($row->recorded_at_unix ?? null),
        ];
    }

    /** @param array{evidence_id:string,actor_identity_id:string,text:string,recorded_at_unix:int} $explanation */
    private function selectedReview(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        array $explanation,
    ): ?array {
        $rows = $this->connection
            ->table('oneqay_pos_cash_variance_review_decision_evidence')
            ->where('tenant_id', $context->tenantId())
            ->where('cash_variance_explanation_evidence_id', $explanation['evidence_id'])
            ->get();
        if ($rows->count() > 1) {
            throw new PosTransactionViolation();
        }
        $row = $rows->first();
        if ($row === null) {
            return null;
        }
        if (! is_object($row)) {
            throw new PosTransactionViolation();
        }

        $this->assertVarianceEvidence($row, $context, $variance);
        $this->assertEquals($row->cash_variance_explanation_evidence_id ?? null, $explanation['evidence_id']);
        $this->assertEquals($row->explanation_actor_identity_id ?? null, $explanation['actor_identity_id']);
        $reviewer = $this->requiredString($row->reviewer_actor_identity_id ?? null);
        if (hash_equals($reviewer, $explanation['actor_identity_id'])) {
            throw new PosTransactionViolation();
        }

        return [
            'review_evidence_id' => $this->requiredIdentifier($row->review_evidence_id ?? null),
            'reviewer_actor_identity_id' => $reviewer,
            'outcome' => $this->reviewOutcome($row->review_outcome ?? null),
            'reviewed_at_unix' => $this->safeUnsignedInt($row->reviewed_at_unix ?? null),
        ];
    }

    private function assertVarianceEvidence(object $row, PosExecutionContext $context, CashVarianceResult $variance): void
    {
        $this->assertEquals($row->tenant_id ?? null, $context->tenantId());
        $this->assertEquals($row->organization_id ?? null, $context->organizationId());
        $this->assertEquals($row->outlet_id ?? null, $context->outletId());
        $this->assertEquals($row->shift_id ?? null, $variance->shiftId());
        $this->assertEquals($row->opening_cash_evidence_id ?? null, $variance->openingCashEvidenceId());
        $this->assertEquals($row->closing_cash_evidence_id ?? null, $variance->closingCashEvidenceId());
        $this->assertEquals($row->variance_direction ?? null, $variance->direction());
        $this->assertEquals($row->currency ?? null, $variance->currency());

        if (
            $this->safeUnsignedInt($row->cutoff_at_unix ?? null) !== $variance->cutoffAtUnix()
            || $this->safeUnsignedInt($row->expected_cash_atomic ?? null) !== $variance->expectedCashAtomic()
            || $this->safeUnsignedInt($row->observed_closing_cash_atomic ?? null) !== $variance->observedClosingAtomic()
            || $this->safeSignedInt($row->variance_atomic ?? null) !== $variance->varianceAtomic()
            || $this->safeScale($row->currency_scale ?? null) !== $variance->currencyScale()
        ) {
            throw new PosTransactionViolation();
        }
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled || ! $this->featureEnabled || ! $this->closingCashEnabled) {
            throw new PosTransactionViolation();
        }
        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new PosTransactionViolation();
        }
    }

    private function reviewOutcome(mixed $value): string
    {
        if (! is_string($value) || ! in_array($value, [
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
            CashVarianceReviewDecisionCommand::REVIEW_REJECTED,
        ], true)) {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function canonicalCurrency(mixed $value): string
    {
        if (! is_string($value) || preg_match('/\A[A-Z]{3}\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function requiredIdentifier(mixed $value): string
    {
        if (! is_string($value) || preg_match(self::IDENTIFIER_PATTERN, $value) !== 1) {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function assertIdentifier(string $value): void
    {
        if (preg_match(self::IDENTIFIER_PATTERN, $value) !== 1) {
            throw new PosTransactionViolation();
        }
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value) || trim($value) === '') {
            throw new PosTransactionViolation();
        }
        return $value;
    }

    private function assertEquals(mixed $actual, string $expected): void
    {
        if (! is_string($actual) || ! hash_equals($expected, $actual)) {
            throw new PosTransactionViolation();
        }
    }

    private function safeScale(mixed $value): int
    {
        $scale = $this->safeUnsignedInt($value);
        if ($scale > 6) {
            throw new PosTransactionViolation();
        }
        return $scale;
    }

    private function safeUnsignedInt(mixed $value): int
    {
        if (is_int($value)) {
            if ($value < 0) {
                throw new PosTransactionViolation();
            }
            return $value;
        }
        if (! is_string($value) || preg_match('/\A[0-9]+\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }
        $normalized = ltrim($value, '0');
        $normalized = $normalized === '' ? '0' : $normalized;
        $maximum = (string) PHP_INT_MAX;
        if (strlen($normalized) > strlen($maximum)
            || (strlen($normalized) === strlen($maximum) && strcmp($normalized, $maximum) > 0)) {
            throw new PosTransactionViolation();
        }
        return (int) $normalized;
    }

    private function safeSignedInt(mixed $value): int
    {
        if (is_int($value)) {
            if ($value === PHP_INT_MIN) {
                throw new PosTransactionViolation();
            }
            return $value;
        }
        if (! is_string($value) || preg_match('/\A-?[0-9]+\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }
        $negative = str_starts_with($value, '-');
        $digits = ltrim($negative ? substr($value, 1) : $value, '0');
        $digits = $digits === '' ? '0' : $digits;
        $maximum = (string) PHP_INT_MAX;
        if (strlen($digits) > strlen($maximum)
            || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)) {
            throw new PosTransactionViolation();
        }
        $integer = (int) $digits;
        return $negative ? -$integer : $integer;
    }
}
