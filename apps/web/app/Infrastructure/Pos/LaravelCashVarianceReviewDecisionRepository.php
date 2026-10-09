<?php

declare(strict_types=1);

namespace App\Infrastructure\Pos;

use App\Application\Pos\CashVarianceExplanationResult;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\CashVarianceReviewDecisionCommand;
use App\Application\Pos\CashVarianceReviewDecisionRepository;
use App\Application\Pos\CashVarianceReviewDecisionResult;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelCashVarianceReviewDecisionRepository implements CashVarianceReviewDecisionRepository
{
    private const TABLE = 'oneqay_pos_cash_variance_review_decision_evidence';
    private const EXPLANATION_TABLE = 'oneqay_pos_cash_variance_explanation_evidence';
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private Connection $connection,
        private bool $persistenceEnabled,
        private string $runtimeClass,
        private bool $featureEnabled,
    ) {}

    public function resolveExplanation(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        string $cashVarianceExplanationEvidenceId,
    ): CashVarianceExplanationResult {
        try {
            $this->assertOperational();
            $this->assertVarianceContext($context, $variance);

            if (preg_match(self::IDENTIFIER_PATTERN, $cashVarianceExplanationEvidenceId) !== 1) {
                throw new PosTransactionViolation();
            }

            $row = $this->connection->table(self::EXPLANATION_TABLE)
                ->where('tenant_id', $context->tenantId())
                ->where('evidence_id', $cashVarianceExplanationEvidenceId)
                ->first();

            if ($row === null) {
                throw new PosTransactionViolation();
            }

            $this->assertExplanationRow($row, $context, $variance);

            return $this->explanationFromRow($row);
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    public function record(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        CashVarianceExplanationResult $explanation,
        CashVarianceReviewDecisionCommand $command,
        string $correlationId,
        int $reviewedAtUnix,
    ): CashVarianceReviewDecisionResult {
        try {
            $this->assertOperational();
            $this->assertVarianceContext($context, $variance, $reviewedAtUnix);

            if (
                preg_match(self::IDENTIFIER_PATTERN, $correlationId) !== 1
                || ! hash_equals($command->cashVarianceExplanationEvidenceId(), $explanation->evidenceId())
            ) {
                throw new PosTransactionViolation();
            }

            $row = $this->connection->table(self::EXPLANATION_TABLE)
                ->where('tenant_id', $context->tenantId())
                ->where('evidence_id', $command->cashVarianceExplanationEvidenceId())
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                throw new PosTransactionViolation();
            }

            $this->assertExplanationRow($row, $context, $variance);
            $this->assertResolvedMatchesRow($explanation, $row);

            $explanationFingerprint = (string) $row->payload_fingerprint;
            $explanationActor = (string) $row->actor_identity_id;

            if (hash_equals($context->actorId(), $explanationActor)) {
                throw new PosTransactionViolation();
            }

            $fingerprint = hash('sha256', implode('|', [
                $context->actorId(),
                $context->tenantId(),
                $context->organizationId(),
                $context->outletId(),
                $variance->shiftId(),
                $variance->openingCashEvidenceId(),
                $variance->closingCashEvidenceId(),
                $command->cashVarianceExplanationEvidenceId(),
                $explanationActor,
                (string) $variance->cutoffAtUnix(),
                (string) $variance->expectedCashAtomic(),
                (string) $variance->observedClosingAtomic(),
                (string) $variance->varianceAtomic(),
                $variance->direction(),
                $variance->currency(),
                (string) $variance->currencyScale(),
                $explanationFingerprint,
                $command->semanticFingerprintPart(),
            ]));

            $existingOperation = $this->connection->table(self::TABLE)
                ->where('tenant_id', $context->tenantId())
                ->where('operation_id', $command->operationId())
                ->lockForUpdate()
                ->first();

            if ($existingOperation !== null) {
                return $this->replayOrFail($existingOperation, $fingerprint);
            }

            $existingDecision = $this->connection->table(self::TABLE)
                ->where('tenant_id', $context->tenantId())
                ->where('cash_variance_explanation_evidence_id', $command->cashVarianceExplanationEvidenceId())
                ->lockForUpdate()
                ->first();

            if ($existingDecision !== null) {
                throw new PosTransactionViolation();
            }

            $reviewEvidenceId = 'varrev-'.substr(
                hash('sha256', $context->tenantId().'|'.$command->operationId()),
                0,
                25,
            );

            $payload = [
                'tenant_id' => $variance->tenantId(),
                'review_evidence_id' => $reviewEvidenceId,
                'operation_id' => $command->operationId(),
                'payload_fingerprint' => $fingerprint,
                'shift_id' => $variance->shiftId(),
                'opening_cash_evidence_id' => $variance->openingCashEvidenceId(),
                'closing_cash_evidence_id' => $variance->closingCashEvidenceId(),
                'cash_variance_explanation_evidence_id' => $command->cashVarianceExplanationEvidenceId(),
                'explanation_actor_identity_id' => $explanationActor,
                'reviewer_actor_identity_id' => $context->actorId(),
                'organization_id' => $variance->organizationId(),
                'outlet_id' => $variance->outletId(),
                'cutoff_at_unix' => $variance->cutoffAtUnix(),
                'expected_cash_atomic' => $variance->expectedCashAtomic(),
                'observed_closing_cash_atomic' => $variance->observedClosingAtomic(),
                'variance_atomic' => $variance->varianceAtomic(),
                'variance_direction' => $variance->direction(),
                'currency' => $variance->currency(),
                'currency_scale' => $variance->currencyScale(),
                'explanation_payload_fingerprint' => $explanationFingerprint,
                'review_outcome' => $command->reviewOutcome(),
                'correlation_id' => $correlationId,
                'reviewed_at_unix' => $reviewedAtUnix,
            ];

            $this->connection->table(self::TABLE)->insert($payload);

            return $this->resultFromPayload($payload);
        } catch (PosTransactionViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PosTransactionViolation();
        }
    }

    private function assertOperational(): void
    {
        if (
            ! $this->persistenceEnabled
            || ! $this->featureEnabled
            || ! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)
        ) {
            throw new PosTransactionViolation();
        }
    }

    private function assertVarianceContext(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        ?int $reviewedAtUnix = null,
    ): void {
        foreach ([
            $context->actorId(),
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
            ($reviewedAtUnix !== null && $reviewedAtUnix <= 0)
            || $context->tenantId() !== $variance->tenantId()
            || $context->organizationId() !== $variance->organizationId()
            || $context->outletId() !== $variance->outletId()
            || $variance->cutoffAtUnix() <= 0
            || $variance->expectedCashAtomic() < 0
            || $variance->observedClosingAtomic() < 0
            || preg_match('/\A[A-Z]{3}\z/', $variance->currency()) !== 1
            || $variance->currencyScale() < 0
            || $variance->currencyScale() > 6
        ) {
            throw new PosTransactionViolation();
        }

        if ($variance->observedClosingAtomic() >= $variance->expectedCashAtomic()) {
            $delta = $variance->observedClosingAtomic() - $variance->expectedCashAtomic();
            if (
                $delta <= 0
                || $variance->direction() !== CashVarianceResult::DIRECTION_OVER
                || $variance->varianceAtomic() !== $delta
            ) {
                throw new PosTransactionViolation();
            }

            return;
        }

        $magnitude = $variance->expectedCashAtomic() - $variance->observedClosingAtomic();
        if (
            $magnitude <= 0
            || $variance->direction() !== CashVarianceResult::DIRECTION_SHORT
            || $variance->varianceAtomic() !== -$magnitude
        ) {
            throw new PosTransactionViolation();
        }
    }

    private function assertExplanationRow(
        object $row,
        PosExecutionContext $context,
        CashVarianceResult $variance,
    ): void {
        $fingerprint = $row->payload_fingerprint ?? null;
        $actor = $row->actor_identity_id ?? null;
        $text = $row->explanation_text ?? null;

        if (
            ! is_string($fingerprint)
            || preg_match('/\A[a-f0-9]{64}\z/', $fingerprint) !== 1
            || ! is_string($actor)
            || trim($actor) === ''
            || ! is_string($text)
            || trim($text) === ''
            || ! hash_equals($context->tenantId(), (string) ($row->tenant_id ?? ''))
            || ! hash_equals($variance->organizationId(), (string) ($row->organization_id ?? ''))
            || ! hash_equals($variance->outletId(), (string) ($row->outlet_id ?? ''))
            || ! hash_equals($variance->shiftId(), (string) ($row->shift_id ?? ''))
            || ! hash_equals($variance->openingCashEvidenceId(), (string) ($row->opening_cash_evidence_id ?? ''))
            || ! hash_equals($variance->closingCashEvidenceId(), (string) ($row->closing_cash_evidence_id ?? ''))
            || $this->toUnsignedInt($row->cutoff_at_unix ?? null) !== $variance->cutoffAtUnix()
            || $this->toUnsignedInt($row->expected_cash_atomic ?? null) !== $variance->expectedCashAtomic()
            || $this->toUnsignedInt($row->observed_closing_cash_atomic ?? null) !== $variance->observedClosingAtomic()
            || $this->toSignedInt($row->variance_atomic ?? null) !== $variance->varianceAtomic()
            || ! hash_equals($variance->direction(), (string) ($row->variance_direction ?? ''))
            || ! hash_equals($variance->currency(), (string) ($row->currency ?? ''))
            || (int) ($row->currency_scale ?? -1) !== $variance->currencyScale()
        ) {
            throw new PosTransactionViolation();
        }
    }

    private function assertResolvedMatchesRow(CashVarianceExplanationResult $explanation, object $row): void
    {
        if (
            ! hash_equals($explanation->evidenceId(), (string) $row->evidence_id)
            || ! hash_equals($explanation->operationId(), (string) $row->operation_id)
            || ! hash_equals($explanation->tenantId(), (string) $row->tenant_id)
            || ! hash_equals($explanation->organizationId(), (string) $row->organization_id)
            || ! hash_equals($explanation->outletId(), (string) $row->outlet_id)
            || ! hash_equals($explanation->shiftId(), (string) $row->shift_id)
            || ! hash_equals($explanation->openingCashEvidenceId(), (string) $row->opening_cash_evidence_id)
            || ! hash_equals($explanation->closingCashEvidenceId(), (string) $row->closing_cash_evidence_id)
            || ! hash_equals($explanation->actorIdentityId(), (string) $row->actor_identity_id)
            || $explanation->cutoffAtUnix() !== $this->toUnsignedInt($row->cutoff_at_unix)
            || $explanation->expectedCashAtomic() !== $this->toUnsignedInt($row->expected_cash_atomic)
            || $explanation->observedClosingCashAtomic() !== $this->toUnsignedInt($row->observed_closing_cash_atomic)
            || $explanation->varianceAtomic() !== $this->toSignedInt($row->variance_atomic)
            || ! hash_equals($explanation->varianceDirection(), (string) $row->variance_direction)
            || ! hash_equals($explanation->currency(), (string) $row->currency)
            || $explanation->currencyScale() !== (int) $row->currency_scale
            || ! hash_equals($explanation->explanationText(), (string) $row->explanation_text)
            || ! hash_equals($explanation->correlationId(), (string) $row->correlation_id)
            || $explanation->recordedAtUnix() !== $this->toUnsignedInt($row->recorded_at_unix)
        ) {
            throw new PosTransactionViolation();
        }
    }

    private function replayOrFail(object $row, string $fingerprint): CashVarianceReviewDecisionResult
    {
        if (
            ! is_string($row->payload_fingerprint ?? null)
            || ! hash_equals((string) $row->payload_fingerprint, $fingerprint)
            || hash_equals((string) $row->explanation_actor_identity_id, (string) $row->reviewer_actor_identity_id)
            || ! in_array(
                (string) $row->review_outcome,
                [
                    CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
                    CashVarianceReviewDecisionCommand::REVIEW_REJECTED,
                ],
                true,
            )
        ) {
            throw new PosTransactionViolation();
        }

        return $this->resultFromPayload((array) $row);
    }

    private function explanationFromRow(object $row): CashVarianceExplanationResult
    {
        return new CashVarianceExplanationResult(
            (string) $row->evidence_id,
            (string) $row->operation_id,
            (string) $row->tenant_id,
            (string) $row->organization_id,
            (string) $row->outlet_id,
            (string) $row->shift_id,
            (string) $row->opening_cash_evidence_id,
            (string) $row->closing_cash_evidence_id,
            (string) $row->actor_identity_id,
            $this->toUnsignedInt($row->cutoff_at_unix),
            $this->toUnsignedInt($row->expected_cash_atomic),
            $this->toUnsignedInt($row->observed_closing_cash_atomic),
            $this->toSignedInt($row->variance_atomic),
            (string) $row->variance_direction,
            (string) $row->currency,
            (int) $row->currency_scale,
            (string) $row->explanation_text,
            (string) $row->correlation_id,
            $this->toUnsignedInt($row->recorded_at_unix),
        );
    }

    private function resultFromPayload(array $payload): CashVarianceReviewDecisionResult
    {
        $outcome = (string) $payload['review_outcome'];
        if (! in_array(
            $outcome,
            [CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED, CashVarianceReviewDecisionCommand::REVIEW_REJECTED],
            true,
        )) {
            throw new PosTransactionViolation();
        }

        return new CashVarianceReviewDecisionResult(
            (string) $payload['review_evidence_id'],
            (string) $payload['operation_id'],
            (string) $payload['tenant_id'],
            (string) $payload['organization_id'],
            (string) $payload['outlet_id'],
            (string) $payload['shift_id'],
            (string) $payload['opening_cash_evidence_id'],
            (string) $payload['closing_cash_evidence_id'],
            (string) $payload['cash_variance_explanation_evidence_id'],
            (string) $payload['explanation_actor_identity_id'],
            (string) $payload['reviewer_actor_identity_id'],
            $this->toUnsignedInt($payload['cutoff_at_unix']),
            $this->toUnsignedInt($payload['expected_cash_atomic']),
            $this->toUnsignedInt($payload['observed_closing_cash_atomic']),
            $this->toSignedInt($payload['variance_atomic']),
            (string) $payload['variance_direction'],
            (string) $payload['currency'],
            (int) $payload['currency_scale'],
            (string) $payload['explanation_payload_fingerprint'],
            $outcome,
            (string) $payload['correlation_id'],
            $this->toUnsignedInt($payload['reviewed_at_unix']),
        );
    }

    private function toUnsignedInt(mixed $value): int
    {
        $integer = $this->toSignedInt($value);
        if ($integer < 0) {
            throw new PosTransactionViolation();
        }

        return $integer;
    }

    private function toSignedInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_string($value) || preg_match('/\A-?[0-9]+\z/', $value) !== 1) {
            throw new PosTransactionViolation();
        }

        $negative = str_starts_with($value, '-');
        $digits = ltrim($negative ? substr($value, 1) : $value, '0');
        $digits = $digits === '' ? '0' : $digits;
        $maximum = (string) PHP_INT_MAX;

        if (
            strlen($digits) > strlen($maximum)
            || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)
        ) {
            throw new PosTransactionViolation();
        }

        $integer = (int) $digits;

        return $negative ? -$integer : $integer;
    }
}
