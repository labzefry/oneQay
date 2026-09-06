<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\CashVarianceReviewDecisionCommand;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\CloseShiftCommand;
use App\Application\Pos\CloseShiftRepository;
use App\Application\Pos\CloseShiftResult;
use App\Application\Pos\DeriveCashVariance;
use App\Application\Pos\FinalShiftCloseAuthorizationPolicy;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftClosingCashResult;
use App\Domain\Pos\Money;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelCloseShiftRepository implements CloseShiftRepository
{
    private const CLOSING_MODE = 'OPERATOR_OBSERVED_CLOSING_CASH';

    public function __construct(
        private Connection $connection,
        private LaravelExpectedCashSnapshotReader $expectedCash,
        private DeriveCashVariance $cashVariance,
        private FinalShiftCloseAuthorizationPolicy $actorPolicy,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function close(
        PosExecutionContext $context,
        CloseShiftCommand $command,
        string $correlationId,
        int $closedAtUnix,
    ): CloseShiftResult {
        $this->assertOperational();
        if ($this->connection->transactionLevel() < 1) {
            throw new PosTransactionViolation();
        }

        $fingerprint = hash('sha256', implode('|', [
            $context->actorId(),
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $context->deviceId(),
            $command->semanticFingerprintPart(),
        ]));

        try {
            $existing = $this->connection->table('oneqay_pos_shift_close_evidence')
                ->where('tenant_id', $context->tenantId())
                ->where('operation_id', $command->operationId())
                ->lockForUpdate()
                ->first();
            if ($existing !== null) {
                return $this->replayOrFail($existing, $fingerprint);
            }

            $shift = $this->connection->table('oneqay_pos_shifts')
                ->where('tenant_id', $context->tenantId())
                ->where('outlet_id', $context->outletId())
                ->where('device_id', $context->deviceId())
                ->where('active_slot', 1)
                ->lockForUpdate()
                ->first();
            if ($shift === null) {
                throw new PosTransactionViolation();
            }

            $shiftId = $this->requiredString($shift->shift_id ?? null);
            $this->assertEquals($shift->organization_id ?? null, $context->organizationId());
            $this->assertEquals($shift->outlet_id ?? null, $context->outletId());
            $this->assertEquals($shift->device_id ?? null, $context->deviceId());
            $openerActorId = $this->requiredString($shift->actor_identity_id ?? null);

            $existingShiftClose = $this->connection->table('oneqay_pos_shift_close_evidence')
                ->where('tenant_id', $context->tenantId())
                ->where('shift_id', $shiftId)
                ->lockForUpdate()
                ->first();
            if ($existingShiftClose !== null) {
                throw new PosTransactionViolation();
            }

            $closing = $this->connection->table('oneqay_pos_shift_closing_cash_evidence')
                ->where('tenant_id', $context->tenantId())
                ->where('shift_id', $shiftId)
                ->lockForUpdate()
                ->first();
            if ($closing === null) {
                throw new PosTransactionViolation();
            }

            $closingResult = $this->closingCashResult($closing);
            if ($closingResult->tenantId() !== $context->tenantId()
                || $closingResult->outletId() !== $context->outletId()
                || $closingResult->deviceId() !== $context->deviceId()
                || $closingResult->shiftId() !== $shiftId) {
                throw new PosTransactionViolation();
            }

            $expected = $this->expectedCash->deriveFrom($closingResult);
            $variance = $this->cashVariance->derive($expected, $closingResult);
            if ($closedAtUnix < $variance->cutoffAtUnix()) {
                throw new PosTransactionViolation();
            }

            $reviewEvidenceId = null;
            $reviewOutcome = null;
            $explanationAuthorActorId = null;
            $reviewerActorId = null;

            if ($variance->direction() !== CashVarianceResult::DIRECTION_MATCH) {
                $review = $this->resolveAcceptedReview($context, $variance);
                $reviewEvidenceId = $this->requiredString($review->review_evidence_id ?? null);
                $reviewOutcome = $this->requiredString($review->review_outcome ?? null);
                $explanationAuthorActorId = $this->requiredString($review->explanation_actor_identity_id ?? null);
                $reviewerActorId = $this->requiredString($review->reviewer_actor_identity_id ?? null);
            }

            $this->actorPolicy->requireAuthorizedActors(
                $context->actorId(),
                $openerActorId,
                $variance->direction() !== CashVarianceResult::DIRECTION_MATCH,
                $explanationAuthorActorId,
                $reviewerActorId,
            );

            $evidenceId = 'shiftclose-'.substr(
                hash('sha256', $context->tenantId().'|'.$command->operationId()),
                0,
                21,
            );

            $this->connection->table('oneqay_pos_shift_close_evidence')->insert([
                'tenant_id' => $context->tenantId(),
                'evidence_id' => $evidenceId,
                'operation_id' => $command->operationId(),
                'payload_fingerprint' => $fingerprint,
                'shift_id' => $shiftId,
                'opening_cash_evidence_id' => $variance->openingCashEvidenceId(),
                'closing_cash_evidence_id' => $variance->closingCashEvidenceId(),
                'closer_actor_identity_id' => $context->actorId(),
                'organization_id' => $context->organizationId(),
                'outlet_id' => $context->outletId(),
                'device_id' => $context->deviceId(),
                'cutoff_at_unix' => $variance->cutoffAtUnix(),
                'expected_cash_atomic' => $variance->expectedCashAtomic(),
                'observed_closing_cash_atomic' => $variance->observedClosingAtomic(),
                'variance_atomic' => $variance->varianceAtomic(),
                'variance_direction' => $variance->direction(),
                'currency' => $variance->currency(),
                'currency_scale' => $variance->currencyScale(),
                'review_evidence_id' => $reviewEvidenceId,
                'review_outcome' => $reviewOutcome,
                'correlation_id' => $correlationId,
                'closed_at_unix' => $closedAtUnix,
            ]);

            $updated = $this->connection->table('oneqay_pos_shifts')
                ->where('tenant_id', $context->tenantId())
                ->where('shift_id', $shiftId)
                ->where('active_slot', 1)
                ->update(['active_slot' => null]);
            if ($updated !== 1) {
                throw new PosTransactionViolation();
            }

            return new CloseShiftResult(
                $evidenceId,
                $command->operationId(),
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $context->deviceId(),
                $shiftId,
                $variance->openingCashEvidenceId(),
                $variance->closingCashEvidenceId(),
                $context->actorId(),
                $variance->cutoffAtUnix(),
                $variance->expectedCashAtomic(),
                $variance->observedClosingAtomic(),
                $variance->varianceAtomic(),
                $variance->direction(),
                $variance->currency(),
                $variance->currencyScale(),
                $reviewEvidenceId,
                $reviewOutcome,
                $correlationId,
                $closedAtUnix,
            );
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function resolveAcceptedReview(PosExecutionContext $context, CashVarianceResult $variance): object
    {
        $rows = $this->connection->table('oneqay_pos_cash_variance_review_decision_evidence')
            ->where('tenant_id', $context->tenantId())
            ->where('shift_id', $variance->shiftId())
            ->where('opening_cash_evidence_id', $variance->openingCashEvidenceId())
            ->where('closing_cash_evidence_id', $variance->closingCashEvidenceId())
            ->where('organization_id', $context->organizationId())
            ->where('outlet_id', $context->outletId())
            ->where('cutoff_at_unix', $variance->cutoffAtUnix())
            ->where('expected_cash_atomic', $variance->expectedCashAtomic())
            ->where('observed_closing_cash_atomic', $variance->observedClosingAtomic())
            ->where('variance_atomic', $variance->varianceAtomic())
            ->where('variance_direction', $variance->direction())
            ->where('currency', $variance->currency())
            ->where('currency_scale', $variance->currencyScale())
            ->where('review_outcome', CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED)
            ->lockForUpdate()
            ->get();

        $review = $rows->first();
        if ($rows->count() !== 1 || ! is_object($review)) {
            throw new PosTransactionViolation();
        }

        return $review;
    }

    private function closingCashResult(object $row): ShiftClosingCashResult
    {
        $this->assertEquals($row->evidence_mode ?? null, self::CLOSING_MODE);

        return new ShiftClosingCashResult(
            $this->requiredString($row->evidence_id ?? null),
            $this->requiredString($row->opening_cash_evidence_id ?? null),
            $this->requiredString($row->shift_id ?? null),
            $this->requiredString($row->operation_id ?? null),
            $this->requiredString($row->tenant_id ?? null),
            $this->requiredString($row->outlet_id ?? null),
            $this->requiredString($row->device_id ?? null),
            Money::fromAtomicUnits(
                $this->safeUnsignedBigIntToInt($row->closing_cash_atomic ?? null),
                $this->requiredString($row->currency ?? null),
                $this->safeScale($row->currency_scale ?? null),
            ),
            self::CLOSING_MODE,
            $this->requiredString($row->correlation_id ?? null),
            $this->safeUnsignedBigIntToInt($row->recorded_at_unix ?? null),
        );
    }

    private function replayOrFail(object $row, string $fingerprint): CloseShiftResult
    {
        $this->assertEquals($row->payload_fingerprint ?? null, $fingerprint);
        $direction = $this->requiredString($row->variance_direction ?? null);
        $varianceAtomic = $this->safeSignedBigIntToInt($row->variance_atomic ?? null);
        $reviewEvidenceId = $this->nullableString($row->review_evidence_id ?? null);
        $reviewOutcome = $this->nullableString($row->review_outcome ?? null);
        $this->assertVarianceReviewState($direction, $varianceAtomic, $reviewEvidenceId, $reviewOutcome);

        return new CloseShiftResult(
            $this->requiredString($row->evidence_id ?? null),
            $this->requiredString($row->operation_id ?? null),
            $this->requiredString($row->tenant_id ?? null),
            $this->requiredString($row->organization_id ?? null),
            $this->requiredString($row->outlet_id ?? null),
            $this->requiredString($row->device_id ?? null),
            $this->requiredString($row->shift_id ?? null),
            $this->requiredString($row->opening_cash_evidence_id ?? null),
            $this->requiredString($row->closing_cash_evidence_id ?? null),
            $this->requiredString($row->closer_actor_identity_id ?? null),
            $this->safeUnsignedBigIntToInt($row->cutoff_at_unix ?? null),
            $this->safeUnsignedBigIntToInt($row->expected_cash_atomic ?? null),
            $this->safeUnsignedBigIntToInt($row->observed_closing_cash_atomic ?? null),
            $varianceAtomic,
            $direction,
            $this->requiredString($row->currency ?? null),
            $this->safeScale($row->currency_scale ?? null),
            $reviewEvidenceId,
            $reviewOutcome,
            $this->requiredString($row->correlation_id ?? null),
            $this->safeUnsignedBigIntToInt($row->closed_at_unix ?? null),
        );
    }

    private function assertVarianceReviewState(
        string $direction,
        int $varianceAtomic,
        ?string $reviewEvidenceId,
        ?string $reviewOutcome,
    ): void {
        if ($direction === CashVarianceResult::DIRECTION_MATCH
            && $varianceAtomic === 0
            && $reviewEvidenceId === null
            && $reviewOutcome === null) {
            return;
        }
        if (in_array($direction, [CashVarianceResult::DIRECTION_OVER, CashVarianceResult::DIRECTION_SHORT], true)
            && (($direction === CashVarianceResult::DIRECTION_OVER && $varianceAtomic > 0)
                || ($direction === CashVarianceResult::DIRECTION_SHORT && $varianceAtomic < 0))
            && $reviewEvidenceId !== null
            && $reviewOutcome === CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED) {
            return;
        }
        throw new PosTransactionViolation();
    }

    private function assertOperational(): void
    {
        if (! $this->persistenceEnabled || ! $this->featureEnabled) {
            throw new PosTransactionViolation();
        }
        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
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

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return $this->requiredString($value);
    }

    private function assertEquals(mixed $actual, string $expected): void
    {
        if (! is_string($actual) || ! hash_equals($expected, $actual)) {
            throw new PosTransactionViolation();
        }
    }

    private function safeScale(mixed $value): int
    {
        if (! is_int($value) && !(is_string($value) && preg_match('/\A[0-9]+\z/', $value) === 1)) {
            throw new PosTransactionViolation();
        }
        $scale = (int) $value;
        if ($scale < 0 || $scale > 6) {
            throw new PosTransactionViolation();
        }
        return $scale;
    }

    private function safeSignedBigIntToInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (! is_string($value) || preg_match('/\A-?[0-9]+\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }
        if ($value[0] === '-') {
            $magnitude = ltrim(substr($value, 1), '0');
            $magnitude = $magnitude === '' ? '0' : $magnitude;
            $minimumMagnitude = ltrim((string) PHP_INT_MIN, '-');
            if (strlen($magnitude) > strlen($minimumMagnitude)
                || (strlen($magnitude) === strlen($minimumMagnitude) && strcmp($magnitude, $minimumMagnitude) > 0)) {
                throw new PosTransactionViolation();
            }
            return -(int) $magnitude;
        }
        return $this->safeUnsignedBigIntToInt($value);
    }

    private function safeUnsignedBigIntToInt(mixed $value): int
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
}
