<?php

declare(strict_types=1);

namespace App\Application\Preview;

use App\Application\Pos\CashVarianceExplanationCommand;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\CashVarianceReviewDecisionCommand;
use App\Application\Pos\PosTransactionViolation;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class TechnicalPreviewVarianceReviewJourney
{
    private const IDENTIFIER_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/';

    public function __construct(
        private PreviewFixtureGateway $fixtures,
        private TechnicalPreviewJourney $preview,
    ) {}

    public function reviewerFor(PreviewProfile $operator): PreviewProfile
    {
        $reviewer = $this->fixtures->reviewerFor($operator->principalId());
        if (
            ! $reviewer instanceof PreviewProfile
            || hash_equals($reviewer->principalId(), $operator->principalId())
            || ! hash_equals($reviewer->tenantId(), $operator->tenantId())
            || ! hash_equals($reviewer->organizationId(), $operator->organizationId())
            || ! hash_equals($reviewer->outletId(), $operator->outletId())
        ) {
            throw new PosTransactionViolation();
        }

        return $reviewer;
    }

    /**
     * @param array<string, mixed> $reconciliation
     * @return array<string, mixed>
     */
    public function recordExplanation(
        PreviewProfile $operator,
        array $reconciliation,
        string $operationId,
        string $explanationText,
        string $correlationId,
        int $recordedAtUnix,
    ): array {
        $variance = $this->varianceFromSnapshot($operator, $reconciliation);
        $this->assertIdentifier($correlationId);
        if ($recordedAtUnix <= 0) {
            throw new PosTransactionViolation();
        }

        $command = new CashVarianceExplanationCommand($operationId, $explanationText);

        return $this->preview->withinVerifiedContext(
            $operator,
            function () use ($operator, $reconciliation, $variance, $command, $correlationId, $recordedAtUnix): array {
                $fingerprint = $this->explanationFingerprint($operator, $variance, $command);
                $existing = $reconciliation['explanation'] ?? null;

                if ($existing !== null) {
                    if (! is_array($existing)) {
                        throw new PosTransactionViolation();
                    }

                    $this->assertExplanationSnapshot($operator, $variance, $existing);
                    if (
                        ! hash_equals((string) $existing['operation_id'], $command->operationId())
                        || ! hash_equals((string) $existing['payload_fingerprint'], $fingerprint)
                    ) {
                        throw new PosTransactionViolation();
                    }

                    $existing['idempotent_replay'] = true;
                    return $existing;
                }

                return [
                    'evidence_id' => 'varexp-'.substr(
                        hash('sha256', $operator->tenantId().'|'.$command->operationId()),
                        0,
                        25,
                    ),
                    'operation_id' => $command->operationId(),
                    'payload_fingerprint' => $fingerprint,
                    'tenant_id' => $variance->tenantId(),
                    'organization_id' => $variance->organizationId(),
                    'outlet_id' => $variance->outletId(),
                    'shift_id' => $variance->shiftId(),
                    'opening_cash_evidence_id' => $variance->openingCashEvidenceId(),
                    'closing_cash_evidence_id' => $variance->closingCashEvidenceId(),
                    'actor_identity_id' => $operator->principalId(),
                    'cutoff_at_unix' => $variance->cutoffAtUnix(),
                    'expected_cash_atomic' => $variance->expectedCashAtomic(),
                    'observed_closing_cash_atomic' => $variance->observedClosingAtomic(),
                    'variance_atomic' => $variance->varianceAtomic(),
                    'variance_direction' => $variance->direction(),
                    'currency' => $variance->currency(),
                    'currency_scale' => $variance->currencyScale(),
                    'explanation_text' => $command->explanationText(),
                    'correlation_id' => $correlationId,
                    'recorded_at_unix' => $recordedAtUnix,
                    'idempotent_replay' => false,
                ];
            },
        );
    }

    /**
     * @param array<string, mixed> $reconciliation
     * @return array<string, mixed>
     */
    public function reviewDecision(
        PreviewProfile $operator,
        array $reconciliation,
        string $operationId,
        string $outcome,
        string $correlationId,
        int $reviewedAtUnix,
    ): array {
        $variance = $this->varianceFromSnapshot($operator, $reconciliation);
        $this->assertIdentifier($correlationId);
        if ($reviewedAtUnix <= 0) {
            throw new PosTransactionViolation();
        }

        $explanation = $reconciliation['explanation'] ?? null;
        if (! is_array($explanation)) {
            throw new PosTransactionViolation();
        }
        $explanationFingerprint = $this->assertExplanationSnapshot($operator, $variance, $explanation);

        $reviewer = $this->reviewerFor($operator);
        $command = new CashVarianceReviewDecisionCommand(
            $operationId,
            (string) $explanation['evidence_id'],
            $outcome,
        );

        return $this->preview->withinVerifiedContext(
            $reviewer,
            function () use (
                $operator,
                $reviewer,
                $reconciliation,
                $variance,
                $explanation,
                $explanationFingerprint,
                $command,
                $correlationId,
                $reviewedAtUnix,
            ): array {
                if (hash_equals($reviewer->principalId(), (string) $explanation['actor_identity_id'])) {
                    throw new PosTransactionViolation();
                }

                $fingerprint = $this->reviewFingerprint(
                    $reviewer,
                    $variance,
                    $explanation,
                    $explanationFingerprint,
                    $command,
                );
                $existing = $reconciliation['review'] ?? null;

                if ($existing !== null) {
                    if (! is_array($existing)) {
                        throw new PosTransactionViolation();
                    }

                    $this->assertReviewSnapshot($operator, $reviewer, $variance, $explanation, $existing);
                    if (
                        ! hash_equals((string) $existing['operation_id'], $command->operationId())
                        || ! hash_equals((string) $existing['payload_fingerprint'], $fingerprint)
                    ) {
                        throw new PosTransactionViolation();
                    }

                    $existing['idempotent_replay'] = true;
                    return $existing;
                }

                return [
                    'review_evidence_id' => 'varrev-'.substr(
                        hash('sha256', $reviewer->tenantId().'|'.$command->operationId()),
                        0,
                        25,
                    ),
                    'operation_id' => $command->operationId(),
                    'payload_fingerprint' => $fingerprint,
                    'tenant_id' => $variance->tenantId(),
                    'organization_id' => $variance->organizationId(),
                    'outlet_id' => $variance->outletId(),
                    'shift_id' => $variance->shiftId(),
                    'opening_cash_evidence_id' => $variance->openingCashEvidenceId(),
                    'closing_cash_evidence_id' => $variance->closingCashEvidenceId(),
                    'cash_variance_explanation_evidence_id' => $command->cashVarianceExplanationEvidenceId(),
                    'explanation_actor_identity_id' => (string) $explanation['actor_identity_id'],
                    'reviewer_actor_identity_id' => $reviewer->principalId(),
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
                    'idempotent_replay' => false,
                ];
            },
        );
    }

    /** @param array<string, mixed> $reconciliation */
    private function varianceFromSnapshot(PreviewProfile $operator, array $reconciliation): CashVarianceResult
    {
        foreach ([
            'tenant_id',
            'organization_id',
            'outlet_id',
            'device_id',
            'shift_id',
            'opening_cash_evidence_id',
            'closing_cash_evidence_id',
            'variance_direction',
            'currency',
        ] as $key) {
            if (! is_string($reconciliation[$key] ?? null) || trim((string) $reconciliation[$key]) === '') {
                throw new PosTransactionViolation();
            }
        }

        foreach ([
            'cutoff_at_unix',
            'expected_cash_atomic',
            'observed_closing_atomic',
            'variance_atomic',
        ] as $key) {
            if (! is_int($reconciliation[$key] ?? null)) {
                throw new PosTransactionViolation();
            }
        }

        if (
            ! hash_equals($operator->tenantId(), (string) $reconciliation['tenant_id'])
            || ! hash_equals($operator->organizationId(), (string) $reconciliation['organization_id'])
            || ! hash_equals($operator->outletId(), (string) $reconciliation['outlet_id'])
            || ! hash_equals($operator->deviceId(), (string) $reconciliation['device_id'])
            || $reconciliation['cutoff_at_unix'] <= 0
            || $reconciliation['expected_cash_atomic'] < 0
            || $reconciliation['observed_closing_atomic'] < 0
            || ! hash_equals('IDR', (string) $reconciliation['currency'])
        ) {
            throw new PosTransactionViolation();
        }

        $this->assertIdentifier((string) $reconciliation['shift_id']);
        $this->assertIdentifier((string) $reconciliation['opening_cash_evidence_id']);
        $this->assertIdentifier((string) $reconciliation['closing_cash_evidence_id']);

        $direction = (string) $reconciliation['variance_direction'];
        $expected = (int) $reconciliation['expected_cash_atomic'];
        $observed = (int) $reconciliation['observed_closing_atomic'];
        $variance = (int) $reconciliation['variance_atomic'];

        if ($direction === CashVarianceResult::DIRECTION_OVER) {
            if ($observed <= $expected || $variance !== $observed - $expected) {
                throw new PosTransactionViolation();
            }
        } elseif ($direction === CashVarianceResult::DIRECTION_SHORT) {
            if ($observed >= $expected || $variance !== -($expected - $observed)) {
                throw new PosTransactionViolation();
            }
        } else {
            throw new PosTransactionViolation();
        }

        return new CashVarianceResult(
            (string) $reconciliation['tenant_id'],
            (string) $reconciliation['organization_id'],
            (string) $reconciliation['outlet_id'],
            (string) $reconciliation['shift_id'],
            (string) $reconciliation['opening_cash_evidence_id'],
            (string) $reconciliation['closing_cash_evidence_id'],
            (int) $reconciliation['cutoff_at_unix'],
            $expected,
            $observed,
            $variance,
            $direction,
            'IDR',
            0,
        );
    }

    private function explanationFingerprint(
        PreviewProfile $operator,
        CashVarianceResult $variance,
        CashVarianceExplanationCommand $command,
    ): string {
        return hash('sha256', implode('|', [
            $operator->principalId(),
            $operator->tenantId(),
            $operator->organizationId(),
            $operator->outletId(),
            $variance->shiftId(),
            $variance->openingCashEvidenceId(),
            $variance->closingCashEvidenceId(),
            (string) $variance->cutoffAtUnix(),
            (string) $variance->expectedCashAtomic(),
            (string) $variance->observedClosingAtomic(),
            (string) $variance->varianceAtomic(),
            $variance->direction(),
            $variance->currency(),
            (string) $variance->currencyScale(),
            $command->semanticFingerprintPart(),
        ]));
    }

    /**
     * @param array<string, mixed> $explanation
     * @return string payload fingerprint
     */
    private function assertExplanationSnapshot(
        PreviewProfile $operator,
        CashVarianceResult $variance,
        array $explanation,
    ): string {
        foreach ([
            'evidence_id',
            'operation_id',
            'payload_fingerprint',
            'tenant_id',
            'organization_id',
            'outlet_id',
            'shift_id',
            'opening_cash_evidence_id',
            'closing_cash_evidence_id',
            'actor_identity_id',
            'variance_direction',
            'currency',
            'explanation_text',
            'correlation_id',
        ] as $key) {
            if (! is_string($explanation[$key] ?? null) || trim((string) $explanation[$key]) === '') {
                throw new PosTransactionViolation();
            }
        }

        foreach ([
            'cutoff_at_unix',
            'expected_cash_atomic',
            'observed_closing_cash_atomic',
            'variance_atomic',
            'currency_scale',
            'recorded_at_unix',
        ] as $key) {
            if (! is_int($explanation[$key] ?? null)) {
                throw new PosTransactionViolation();
            }
        }

        $command = new CashVarianceExplanationCommand(
            (string) $explanation['operation_id'],
            (string) $explanation['explanation_text'],
        );
        $expectedFingerprint = $this->explanationFingerprint($operator, $variance, $command);

        if (
            ! hash_equals($expectedFingerprint, (string) $explanation['payload_fingerprint'])
            || ! hash_equals($operator->principalId(), (string) $explanation['actor_identity_id'])
            || ! hash_equals($variance->tenantId(), (string) $explanation['tenant_id'])
            || ! hash_equals($variance->organizationId(), (string) $explanation['organization_id'])
            || ! hash_equals($variance->outletId(), (string) $explanation['outlet_id'])
            || ! hash_equals($variance->shiftId(), (string) $explanation['shift_id'])
            || ! hash_equals($variance->openingCashEvidenceId(), (string) $explanation['opening_cash_evidence_id'])
            || ! hash_equals($variance->closingCashEvidenceId(), (string) $explanation['closing_cash_evidence_id'])
            || $variance->cutoffAtUnix() !== $explanation['cutoff_at_unix']
            || $variance->expectedCashAtomic() !== $explanation['expected_cash_atomic']
            || $variance->observedClosingAtomic() !== $explanation['observed_closing_cash_atomic']
            || $variance->varianceAtomic() !== $explanation['variance_atomic']
            || ! hash_equals($variance->direction(), (string) $explanation['variance_direction'])
            || ! hash_equals($variance->currency(), (string) $explanation['currency'])
            || $variance->currencyScale() !== $explanation['currency_scale']
            || $explanation['recorded_at_unix'] <= 0
        ) {
            throw new PosTransactionViolation();
        }

        $this->assertIdentifier((string) $explanation['evidence_id']);
        $this->assertIdentifier((string) $explanation['correlation_id']);

        return $expectedFingerprint;
    }

    /**
     * @param array<string, mixed> $explanation
     */
    private function reviewFingerprint(
        PreviewProfile $reviewer,
        CashVarianceResult $variance,
        array $explanation,
        string $explanationFingerprint,
        CashVarianceReviewDecisionCommand $command,
    ): string {
        return hash('sha256', implode('|', [
            $reviewer->principalId(),
            $reviewer->tenantId(),
            $reviewer->organizationId(),
            $reviewer->outletId(),
            $variance->shiftId(),
            $variance->openingCashEvidenceId(),
            $variance->closingCashEvidenceId(),
            $command->cashVarianceExplanationEvidenceId(),
            (string) $explanation['actor_identity_id'],
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
    }

    /**
     * @param array<string, mixed> $explanation
     * @param array<string, mixed> $review
     */
    private function assertReviewSnapshot(
        PreviewProfile $operator,
        PreviewProfile $reviewer,
        CashVarianceResult $variance,
        array $explanation,
        array $review,
    ): void {
        foreach ([
            'review_evidence_id',
            'operation_id',
            'payload_fingerprint',
            'tenant_id',
            'organization_id',
            'outlet_id',
            'shift_id',
            'opening_cash_evidence_id',
            'closing_cash_evidence_id',
            'cash_variance_explanation_evidence_id',
            'explanation_actor_identity_id',
            'reviewer_actor_identity_id',
            'variance_direction',
            'currency',
            'explanation_payload_fingerprint',
            'review_outcome',
            'correlation_id',
        ] as $key) {
            if (! is_string($review[$key] ?? null) || trim((string) $review[$key]) === '') {
                throw new PosTransactionViolation();
            }
        }

        foreach ([
            'cutoff_at_unix',
            'expected_cash_atomic',
            'observed_closing_cash_atomic',
            'variance_atomic',
            'currency_scale',
            'reviewed_at_unix',
        ] as $key) {
            if (! is_int($review[$key] ?? null)) {
                throw new PosTransactionViolation();
            }
        }

        $explanationFingerprint = $this->assertExplanationSnapshot($operator, $variance, $explanation);
        $command = new CashVarianceReviewDecisionCommand(
            (string) $review['operation_id'],
            (string) $review['cash_variance_explanation_evidence_id'],
            (string) $review['review_outcome'],
        );
        $expectedFingerprint = $this->reviewFingerprint(
            $reviewer,
            $variance,
            $explanation,
            $explanationFingerprint,
            $command,
        );

        if (
            ! hash_equals($expectedFingerprint, (string) $review['payload_fingerprint'])
            || ! hash_equals((string) $explanation['evidence_id'], (string) $review['cash_variance_explanation_evidence_id'])
            || ! hash_equals($operator->principalId(), (string) $review['explanation_actor_identity_id'])
            || ! hash_equals($reviewer->principalId(), (string) $review['reviewer_actor_identity_id'])
            || hash_equals((string) $review['explanation_actor_identity_id'], (string) $review['reviewer_actor_identity_id'])
            || ! hash_equals($variance->tenantId(), (string) $review['tenant_id'])
            || ! hash_equals($variance->organizationId(), (string) $review['organization_id'])
            || ! hash_equals($variance->outletId(), (string) $review['outlet_id'])
            || ! hash_equals($variance->shiftId(), (string) $review['shift_id'])
            || ! hash_equals($variance->openingCashEvidenceId(), (string) $review['opening_cash_evidence_id'])
            || ! hash_equals($variance->closingCashEvidenceId(), (string) $review['closing_cash_evidence_id'])
            || $variance->cutoffAtUnix() !== $review['cutoff_at_unix']
            || $variance->expectedCashAtomic() !== $review['expected_cash_atomic']
            || $variance->observedClosingAtomic() !== $review['observed_closing_cash_atomic']
            || $variance->varianceAtomic() !== $review['variance_atomic']
            || ! hash_equals($variance->direction(), (string) $review['variance_direction'])
            || ! hash_equals($variance->currency(), (string) $review['currency'])
            || $variance->currencyScale() !== $review['currency_scale']
            || ! hash_equals($explanationFingerprint, (string) $review['explanation_payload_fingerprint'])
            || $review['reviewed_at_unix'] <= 0
        ) {
            throw new PosTransactionViolation();
        }

        $this->assertIdentifier((string) $review['review_evidence_id']);
        $this->assertIdentifier((string) $review['correlation_id']);
    }

    private function assertIdentifier(string $identifier): void
    {
        if (preg_match(self::IDENTIFIER_PATTERN, $identifier) !== 1) {
            throw new InvalidArgumentException('Technical Preview variance-review identifier is invalid.');
        }
    }
}
