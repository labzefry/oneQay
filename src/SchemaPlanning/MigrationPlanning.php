<?php

declare(strict_types=1);

namespace OneQay\SchemaPlanning;

final class MigrationPlanningException extends \RuntimeException
{
    public const REVIEW_NOT_APPROVED = 'MIGRATION_PLANNING_REVIEW_NOT_APPROVED';
    public const SOURCE_PLAN_FINGERPRINT_MISMATCH = 'MIGRATION_PLANNING_SOURCE_PLAN_FINGERPRINT_MISMATCH';
    public const SOURCE_DISPOSITION_MISMATCH = 'MIGRATION_PLANNING_SOURCE_DISPOSITION_MISMATCH';
    public const SOURCE_CORRELATION_MISMATCH = 'MIGRATION_PLANNING_SOURCE_CORRELATION_MISMATCH';
    public const PLAN_DISPOSITION_INVALID = 'MIGRATION_PLANNING_PLAN_DISPOSITION_INVALID';
    public const CHANGE_KIND_NOT_ALLOWED = 'MIGRATION_PLANNING_CHANGE_KIND_NOT_ALLOWED';
    public const CHANGE_RISK_INVALID = 'MIGRATION_PLANNING_CHANGE_RISK_INVALID';
    public const CHANGE_FINGERPRINT_INVALID = 'MIGRATION_PLANNING_CHANGE_FINGERPRINT_INVALID';

    public function __construct(
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }
}

final readonly class MigrationPlanningStep implements \JsonSerializable
{
    public function __construct(
        public StableChangeIdentifier $sourceChangeIdentifier,
        public SchemaChangeKind $kind,
        public string $entityIdentifier,
        public ?string $componentIdentifier,
        public ?ManifestFingerprint $beforeFingerprint,
        public ManifestFingerprint $afterFingerprint,
    ) {
        if (!self::isAllowedKind($kind)) {
            throw new MigrationPlanningException(
                MigrationPlanningException::CHANGE_KIND_NOT_ALLOWED,
                'Schema change kind is not allowed for migration planning.',
            );
        }

        if ($this->beforeFingerprint !== null) {
            throw new MigrationPlanningException(
                MigrationPlanningException::CHANGE_FINGERPRINT_INVALID,
                'Additive migration planning steps must not contain a before fingerprint.',
            );
        }
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'source_change_id' => $this->sourceChangeIdentifier->value,
            'kind' => $this->kind->value,
            'entity_identifier' => $this->entityIdentifier,
            'component_identifier' => $this->componentIdentifier,
            'before_fingerprint' => $this->beforeFingerprint?->value,
            'after_fingerprint' => $this->afterFingerprint->value,
        ];
    }

    public static function isAllowedKind(SchemaChangeKind $kind): bool
    {
        return in_array($kind, [
            SchemaChangeKind::ENTITY_CREATED,
            SchemaChangeKind::ATTRIBUTE_ADDED,
            SchemaChangeKind::UNIQUE_INDEX_ADDED,
            SchemaChangeKind::REFERENCE_ADDED,
        ], true);
    }
}

final readonly class MigrationPlanningArtifact implements \JsonSerializable
{
    /** @var list<MigrationPlanningStep> */
    private array $steps;

    /** @param list<MigrationPlanningStep> $steps */
    public function __construct(
        public PlanFingerprint $sourcePlanFingerprint,
        public CorrelationId $sourceReviewCorrelationId,
        public CorrelationId $planningCorrelationId,
        public ReviewerReference $reviewerReference,
        public ManifestFingerprint $baselineFingerprint,
        public ManifestFingerprint $targetFingerprint,
        array $steps,
    ) {
        if ($steps === []) {
            throw new MigrationPlanningException(
                MigrationPlanningException::PLAN_DISPOSITION_INVALID,
                'Migration planning artifact requires at least one approved additive step.',
            );
        }

        $sourceIdentifiers = [];
        foreach ($steps as $step) {
            if (!$step instanceof MigrationPlanningStep) {
                throw new MigrationPlanningException(
                    MigrationPlanningException::CHANGE_KIND_NOT_ALLOWED,
                    'Migration planning step collection is invalid.',
                );
            }
            if (isset($sourceIdentifiers[$step->sourceChangeIdentifier->value])) {
                throw new MigrationPlanningException(
                    MigrationPlanningException::CHANGE_KIND_NOT_ALLOWED,
                    'Migration planning source change identifier is duplicated.',
                );
            }
            $sourceIdentifiers[$step->sourceChangeIdentifier->value] = true;
        }

        $this->steps = array_values($steps);
    }

    /** @return list<MigrationPlanningStep> */
    public function steps(): array
    {
        return $this->steps;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'source_plan_fingerprint' => $this->sourcePlanFingerprint->value,
            'source_review_correlation_id' => $this->sourceReviewCorrelationId->value,
            'planning_correlation_id' => $this->planningCorrelationId->value,
            'reviewer_reference' => $this->reviewerReference->value,
            'baseline_fingerprint' => $this->baselineFingerprint->value,
            'target_fingerprint' => $this->targetFingerprint->value,
            'step_count' => count($this->steps),
            'steps' => $this->steps,
        ];
    }
}

final class DeterministicMigrationPlanningArtifactBuilder
{
    public function build(
        PhysicalSchemaPlan $plan,
        SchemaChangeReviewEnvelope $review,
        CorrelationId|string $correlationId,
    ): MigrationPlanningArtifact {
        if ($plan->disposition !== PlanDisposition::REVIEW_REQUIRED) {
            throw new MigrationPlanningException(
                MigrationPlanningException::PLAN_DISPOSITION_INVALID,
                'Only REVIEW_REQUIRED schema plans may produce migration planning artifacts.',
            );
        }

        if ($review->decision !== ReviewDecision::APPROVED_FOR_MIGRATION_PLANNING
            || $review->reasonCode !== ReviewReasonCode::REVIEW_APPROVED) {
            throw new MigrationPlanningException(
                MigrationPlanningException::REVIEW_NOT_APPROVED,
                'Schema review is not approved for migration planning.',
            );
        }

        if ($review->sourceDisposition !== $plan->disposition) {
            throw new MigrationPlanningException(
                MigrationPlanningException::SOURCE_DISPOSITION_MISMATCH,
                'Schema review source disposition does not match the supplied plan.',
            );
        }

        if (!hash_equals($review->sourceCorrelationId->value, $plan->correlationId->value)) {
            throw new MigrationPlanningException(
                MigrationPlanningException::SOURCE_CORRELATION_MISMATCH,
                'Schema review source correlation does not match the supplied plan.',
            );
        }

        $planFingerprint = hash('sha256', $this->encode($plan));
        if (!hash_equals($review->sourcePlanFingerprint->value, $planFingerprint)) {
            throw new MigrationPlanningException(
                MigrationPlanningException::SOURCE_PLAN_FINGERPRINT_MISMATCH,
                'Schema review fingerprint does not match the supplied plan.',
            );
        }

        $steps = [];
        foreach ($plan->changes() as $change) {
            if ($change->risk !== ChangeRisk::REVIEW_REQUIRED) {
                throw new MigrationPlanningException(
                    MigrationPlanningException::CHANGE_RISK_INVALID,
                    'Only REVIEW_REQUIRED additive schema changes may be planned.',
                );
            }
            if (!MigrationPlanningStep::isAllowedKind($change->kind)) {
                throw new MigrationPlanningException(
                    MigrationPlanningException::CHANGE_KIND_NOT_ALLOWED,
                    'Schema change kind is not allowed for migration planning.',
                );
            }
            if ($change->beforeFingerprint !== null || $change->afterFingerprint === null) {
                throw new MigrationPlanningException(
                    MigrationPlanningException::CHANGE_FINGERPRINT_INVALID,
                    'Additive schema change fingerprints are invalid for migration planning.',
                );
            }

            $steps[] = new MigrationPlanningStep(
                $change->identifier,
                $change->kind,
                $change->entityIdentifier,
                $change->componentIdentifier,
                $change->beforeFingerprint,
                $change->afterFingerprint,
            );
        }

        return new MigrationPlanningArtifact(
            new PlanFingerprint($planFingerprint),
            $review->correlationId,
            $correlationId instanceof CorrelationId ? $correlationId : new CorrelationId($correlationId),
            $review->reviewerReference,
            $plan->baselineFingerprint,
            $plan->targetFingerprint,
            $steps,
        );
    }

    private function encode(PhysicalSchemaPlan $plan): string
    {
        return json_encode(
            $plan,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION,
        );
    }
}
