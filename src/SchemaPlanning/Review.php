<?php

declare(strict_types=1);

namespace OneQay\SchemaPlanning;

final class SchemaReviewException extends \RuntimeException
{
    public const REVIEWER_REFERENCE_INVALID = 'SCHEMA_REVIEW_REVIEWER_REFERENCE_INVALID';
    public const DECISION_INVALID = 'SCHEMA_REVIEW_DECISION_INVALID';
    public const REASON_CODE_INVALID = 'SCHEMA_REVIEW_REASON_CODE_INVALID';
    public const DECISION_NOT_ALLOWED = 'SCHEMA_REVIEW_DECISION_NOT_ALLOWED';
    public const REASON_CODE_NOT_ALLOWED = 'SCHEMA_REVIEW_REASON_CODE_NOT_ALLOWED';
    public const BLOCKED_APPROVAL_DENIED = 'SCHEMA_REVIEW_BLOCKED_APPROVAL_DENIED';
    public const PLAN_FINGERPRINT_INVALID = 'SCHEMA_REVIEW_PLAN_FINGERPRINT_INVALID';

    public function __construct(
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }
}

enum ReviewDecision: string
{
    case NOT_REQUIRED = 'NOT_REQUIRED';
    case APPROVED_FOR_MIGRATION_PLANNING = 'APPROVED_FOR_MIGRATION_PLANNING';
    case REJECTED = 'REJECTED';
}

enum ReviewReasonCode: string
{
    case NO_CHANGES = 'NO_CHANGES';
    case REVIEW_APPROVED = 'REVIEW_APPROVED';
    case REVIEW_REJECTED = 'REVIEW_REJECTED';
    case PLAN_BLOCKED = 'PLAN_BLOCKED';
}

final readonly class ReviewerReference implements \JsonSerializable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === ''
            || strlen($normalized) > 128
            || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:@-]*$/D', $normalized) !== 1) {
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

final readonly class PlanFingerprint implements \JsonSerializable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (preg_match('/^[a-f0-9]{64}$/D', $normalized) !== 1) {
            throw new SchemaReviewException(
                SchemaReviewException::PLAN_FINGERPRINT_INVALID,
                'Schema review plan fingerprint is invalid.',
            );
        }
        $this->value = $normalized;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

final readonly class SchemaChangeReviewEnvelope implements \JsonSerializable
{
    public function __construct(
        public PlanFingerprint $sourcePlanFingerprint,
        public PlanDisposition $sourceDisposition,
        public CorrelationId $sourceCorrelationId,
        public CorrelationId $correlationId,
        public ReviewerReference $reviewerReference,
        public ReviewDecision $decision,
        public ReviewReasonCode $reasonCode,
    ) {
        self::assertDecisionBoundary($sourceDisposition, $decision, $reasonCode);
    }

    /** @return array<string,string> */
    public function jsonSerialize(): array
    {
        return [
            'source_plan_fingerprint' => $this->sourcePlanFingerprint->value,
            'source_disposition' => $this->sourceDisposition->value,
            'source_correlation_id' => $this->sourceCorrelationId->value,
            'correlation_id' => $this->correlationId->value,
            'reviewer_reference' => $this->reviewerReference->value,
            'decision' => $this->decision->value,
            'reason_code' => $this->reasonCode->value,
        ];
    }

    private static function assertDecisionBoundary(
        PlanDisposition $disposition,
        ReviewDecision $decision,
        ReviewReasonCode $reasonCode,
    ): void {
        if ($disposition === PlanDisposition::NO_CHANGES) {
            if ($decision !== ReviewDecision::NOT_REQUIRED) {
                throw new SchemaReviewException(
                    SchemaReviewException::DECISION_NOT_ALLOWED,
                    'NO_CHANGES schema plans do not require review approval.',
                );
            }
            if ($reasonCode !== ReviewReasonCode::NO_CHANGES) {
                throw new SchemaReviewException(
                    SchemaReviewException::REASON_CODE_NOT_ALLOWED,
                    'NO_CHANGES schema plans require the NO_CHANGES reason code.',
                );
            }
            return;
        }

        if ($disposition === PlanDisposition::BLOCKED) {
            if ($decision === ReviewDecision::APPROVED_FOR_MIGRATION_PLANNING) {
                throw new SchemaReviewException(
                    SchemaReviewException::BLOCKED_APPROVAL_DENIED,
                    'BLOCKED schema plans cannot be approved for migration planning.',
                );
            }
            if ($decision !== ReviewDecision::REJECTED) {
                throw new SchemaReviewException(
                    SchemaReviewException::DECISION_NOT_ALLOWED,
                    'BLOCKED schema plans must remain rejected.',
                );
            }
            if ($reasonCode !== ReviewReasonCode::PLAN_BLOCKED) {
                throw new SchemaReviewException(
                    SchemaReviewException::REASON_CODE_NOT_ALLOWED,
                    'BLOCKED schema plans require the PLAN_BLOCKED reason code.',
                );
            }
            return;
        }

        if ($decision === ReviewDecision::NOT_REQUIRED) {
            throw new SchemaReviewException(
                SchemaReviewException::DECISION_NOT_ALLOWED,
                'REVIEW_REQUIRED schema plans require an explicit review decision.',
            );
        }

        $expectedReason = $decision === ReviewDecision::APPROVED_FOR_MIGRATION_PLANNING
            ? ReviewReasonCode::REVIEW_APPROVED
            : ReviewReasonCode::REVIEW_REJECTED;
        if ($reasonCode !== $expectedReason) {
            throw new SchemaReviewException(
                SchemaReviewException::REASON_CODE_NOT_ALLOWED,
                'Schema review reason code does not match the review decision.',
            );
        }
    }
}

final class DeterministicSchemaChangeReviewer
{
    public function review(
        PhysicalSchemaPlan $plan,
        ReviewerReference|string $reviewerReference,
        CorrelationId|string $correlationId,
        ReviewDecision|string $decision,
        ReviewReasonCode|string $reasonCode,
    ): SchemaChangeReviewEnvelope {
        $reviewer = $reviewerReference instanceof ReviewerReference
            ? $reviewerReference
            : new ReviewerReference($reviewerReference);
        $correlation = $correlationId instanceof CorrelationId
            ? $correlationId
            : new CorrelationId($correlationId);
        $normalizedDecision = $this->decision($decision);
        $normalizedReasonCode = $this->reasonCode($reasonCode);

        return new SchemaChangeReviewEnvelope(
            new PlanFingerprint(hash('sha256', $this->encode($plan))),
            $plan->disposition,
            $plan->correlationId,
            $correlation,
            $reviewer,
            $normalizedDecision,
            $normalizedReasonCode,
        );
    }

    private function decision(ReviewDecision|string $decision): ReviewDecision
    {
        if ($decision instanceof ReviewDecision) {
            return $decision;
        }

        $normalized = trim($decision);
        $parsed = ReviewDecision::tryFrom($normalized);
        if ($parsed === null) {
            throw new SchemaReviewException(
                SchemaReviewException::DECISION_INVALID,
                'Schema review decision is invalid.',
            );
        }
        return $parsed;
    }

    private function reasonCode(ReviewReasonCode|string $reasonCode): ReviewReasonCode
    {
        if ($reasonCode instanceof ReviewReasonCode) {
            return $reasonCode;
        }

        $normalized = trim($reasonCode);
        $parsed = ReviewReasonCode::tryFrom($normalized);
        if ($parsed === null) {
            throw new SchemaReviewException(
                SchemaReviewException::REASON_CODE_INVALID,
                'Schema review reason code is invalid.',
            );
        }
        return $parsed;
    }

    private function encode(PhysicalSchemaPlan $plan): string
    {
        return json_encode(
            $plan,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION,
        );
    }
}
