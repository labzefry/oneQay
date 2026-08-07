<?php

declare(strict_types=1);

namespace OneQay\SchemaPlanning;

final class SchemaReviewException extends \RuntimeException
{
    public const REVIEWER_REFERENCE_INVALID = 'SCHEMA_REVIEW_REVIEWER_REFERENCE_INVALID';
    public const DECISION_INVALID = 'SCHEMA_REVIEW_DECISION_INVALID';
    public const REASON_CODE_INVALID = 'SCHEMA_REVIEW_REASON_CODE_INVALID';
    public const TRANSITION_INVALID = 'SCHEMA_REVIEW_TRANSITION_INVALID';
    public const APPROVAL_FORBIDDEN = 'SCHEMA_REVIEW_APPROVAL_FORBIDDEN';

    public function __construct(
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }
}

enum SchemaReviewDecision: string
{
    case NOT_REQUIRED = 'NOT_REQUIRED';
    case APPROVED_FOR_MIGRATION_PLANNING = 'APPROVED_FOR_MIGRATION_PLANNING';
    case REJECTED = 'REJECTED';
}

enum SchemaReviewReasonCode: string
{
    case NO_CHANGES = 'NO_CHANGES';
    case REVIEW_ACCEPTED = 'REVIEW_ACCEPTED';
    case REVIEW_REJECTED = 'REVIEW_REJECTED';
    case BLOCKED_CHANGE_REJECTED = 'BLOCKED_CHANGE_REJECTED';
}

final readonly class ReviewerReference implements \JsonSerializable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === ''
            || strlen($normalized) > 64
            || preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*$/D', $normalized) !== 1) {
            throw new SchemaReviewException(
                SchemaReviewException::REVIEWER_REFERENCE_INVALID,
                'Schema review reviewer reference is invalid.',
            );
        }
        $this->value = $normalized;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

final readonly class SchemaPlanFingerprint implements \JsonSerializable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (preg_match('/^[a-f0-9]{64}$/D', $normalized) !== 1) {
            throw new SchemaReviewException(
                SchemaReviewException::TRANSITION_INVALID,
                'Schema review source plan fingerprint is invalid.',
            );
        }
        $this->value = $normalized;
    }

    public static function fromPlan(PhysicalSchemaPlan $plan): self
    {
        return new self(hash('sha256', self::canonicalJson($plan->jsonSerialize())));
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    private static function canonicalJson(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION,
        );
    }
}

final readonly class SchemaChangeReviewEnvelope implements \JsonSerializable
{
    public function __construct(
        public SchemaPlanFingerprint $sourcePlanFingerprint,
        public ManifestFingerprint $sourceBaselineFingerprint,
        public ManifestFingerprint $sourceTargetFingerprint,
        public PlanDisposition $sourcePlanDisposition,
        public CorrelationId $sourceCorrelationId,
        public CorrelationId $reviewCorrelationId,
        public ReviewerReference $reviewerReference,
        public SchemaReviewDecision $decision,
        public SchemaReviewReasonCode $reasonCode,
    ) {
    }

    /** @return array<string,string> */
    public function jsonSerialize(): array
    {
        return [
            'source_plan_fingerprint' => $this->sourcePlanFingerprint->value,
            'source_baseline_fingerprint' => $this->sourceBaselineFingerprint->value,
            'source_target_fingerprint' => $this->sourceTargetFingerprint->value,
            'source_plan_disposition' => $this->sourcePlanDisposition->value,
            'source_correlation_id' => $this->sourceCorrelationId->value,
            'review_correlation_id' => $this->reviewCorrelationId->value,
            'reviewer_reference' => $this->reviewerReference->value,
            'decision' => $this->decision->value,
            'reason_code' => $this->reasonCode->value,
        ];
    }

    public function toCanonicalJson(): string
    {
        return json_encode(
            $this->jsonSerialize(),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION,
        );
    }
}

final class DeterministicSchemaChangeReviewer
{
    public function review(
        PhysicalSchemaPlan $plan,
        ReviewerReference|string $reviewerReference,
        CorrelationId|string $reviewCorrelationId,
        SchemaReviewDecision|string $decision,
        SchemaReviewReasonCode|string $reasonCode,
    ): SchemaChangeReviewEnvelope {
        $reviewer = $reviewerReference instanceof ReviewerReference
            ? $reviewerReference
            : new ReviewerReference($reviewerReference);
        $correlation = $reviewCorrelationId instanceof CorrelationId
            ? $reviewCorrelationId
            : new CorrelationId($reviewCorrelationId);
        $normalizedDecision = $this->decision($decision);
        $normalizedReasonCode = $this->reasonCode($reasonCode);

        $this->assertTransition($plan->disposition, $normalizedDecision, $normalizedReasonCode);

        return new SchemaChangeReviewEnvelope(
            SchemaPlanFingerprint::fromPlan($plan),
            $plan->baselineFingerprint,
            $plan->targetFingerprint,
            $plan->disposition,
            $plan->correlationId,
            $correlation,
            $reviewer,
            $normalizedDecision,
            $normalizedReasonCode,
        );
    }

    private function decision(SchemaReviewDecision|string $decision): SchemaReviewDecision
    {
        if ($decision instanceof SchemaReviewDecision) {
            return $decision;
        }

        $normalized = SchemaReviewDecision::tryFrom(trim($decision));
        if ($normalized === null) {
            throw new SchemaReviewException(
                SchemaReviewException::DECISION_INVALID,
                'Schema review decision is invalid.',
            );
        }

        return $normalized;
    }

    private function reasonCode(SchemaReviewReasonCode|string $reasonCode): SchemaReviewReasonCode
    {
        if ($reasonCode instanceof SchemaReviewReasonCode) {
            return $reasonCode;
        }

        $normalized = SchemaReviewReasonCode::tryFrom(trim($reasonCode));
        if ($normalized === null) {
            throw new SchemaReviewException(
                SchemaReviewException::REASON_CODE_INVALID,
                'Schema review reason code is invalid.',
            );
        }

        return $normalized;
    }

    private function assertTransition(
        PlanDisposition $disposition,
        SchemaReviewDecision $decision,
        SchemaReviewReasonCode $reasonCode,
    ): void {
        if ($disposition === PlanDisposition::BLOCKED
            && $decision === SchemaReviewDecision::APPROVED_FOR_MIGRATION_PLANNING) {
            throw new SchemaReviewException(
                SchemaReviewException::APPROVAL_FORBIDDEN,
                'Blocked schema plans cannot be approved.',
            );
        }

        $valid = match ($disposition) {
            PlanDisposition::NO_CHANGES => $decision === SchemaReviewDecision::NOT_REQUIRED
                && $reasonCode === SchemaReviewReasonCode::NO_CHANGES,
            PlanDisposition::REVIEW_REQUIRED => (
                $decision === SchemaReviewDecision::APPROVED_FOR_MIGRATION_PLANNING
                && $reasonCode === SchemaReviewReasonCode::REVIEW_ACCEPTED
            ) || (
                $decision === SchemaReviewDecision::REJECTED
                && $reasonCode === SchemaReviewReasonCode::REVIEW_REJECTED
            ),
            PlanDisposition::BLOCKED => $decision === SchemaReviewDecision::REJECTED
                && $reasonCode === SchemaReviewReasonCode::BLOCKED_CHANGE_REJECTED,
        };

        if (!$valid) {
            throw new SchemaReviewException(
                SchemaReviewException::TRANSITION_INVALID,
                'Schema review transition is invalid.',
            );
        }
    }
}
