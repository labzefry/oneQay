<?php

declare(strict_types=1);

namespace OneQay\SchemaPlanning;

use OneQay\Migration\MigrationChecksum;
use OneQay\Migration\MigrationDefinition;
use OneQay\Migration\MigrationIdentifier;
use OneQay\Migration\MigrationManifest;
use OneQay\Migration\MigrationRollbackClassification;
use OneQay\Migration\MigrationSafetyClassification;

final class MigrationArtifactBridgeException extends \RuntimeException
{
    public const STEP_COUNT_INVALID = 'MIGRATION_ARTIFACT_BRIDGE_STEP_COUNT_INVALID';
    public const STEP_KIND_NOT_ALLOWED = 'MIGRATION_ARTIFACT_BRIDGE_STEP_KIND_NOT_ALLOWED';
    public const STEP_FINGERPRINT_INVALID = 'MIGRATION_ARTIFACT_BRIDGE_STEP_FINGERPRINT_INVALID';
    public const SOURCE_CHANGE_DUPLICATE = 'MIGRATION_ARTIFACT_BRIDGE_SOURCE_CHANGE_DUPLICATE';
    public const BINDING_INVALID = 'MIGRATION_ARTIFACT_BRIDGE_BINDING_INVALID';

    public function __construct(
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }
}

final readonly class GovernedMigrationBinding implements \JsonSerializable
{
    public function __construct(
        public StableChangeIdentifier $sourceChangeIdentifier,
        public MigrationIdentifier $migrationIdentifier,
    ) {}

    /** @return array{source_change_id: string, migration_identifier: string} */
    public function jsonSerialize(): array
    {
        return [
            'source_change_id' => $this->sourceChangeIdentifier->value,
            'migration_identifier' => $this->migrationIdentifier->value,
        ];
    }
}

final readonly class GovernedMigrationManifestArtifact implements \JsonSerializable
{
    /** @var list<GovernedMigrationBinding> */
    private array $bindings;

    /** @param list<GovernedMigrationBinding> $bindings */
    public function __construct(
        public ManifestFingerprint $sourcePlanningArtifactFingerprint,
        public CorrelationId $sourcePlanningCorrelationId,
        public CorrelationId $sourceReviewCorrelationId,
        public CorrelationId $bridgeCorrelationId,
        public ReviewerReference $reviewerReference,
        public ManifestFingerprint $baselineFingerprint,
        public ManifestFingerprint $targetFingerprint,
        array $bindings,
        public MigrationManifest $manifest,
    ) {
        $entries = $this->manifest->entries();
        if ($bindings === [] || count($bindings) !== count($entries)) {
            throw new MigrationArtifactBridgeException(
                MigrationArtifactBridgeException::BINDING_INVALID,
                'Governed migration bindings do not match the migration manifest.',
            );
        }

        $sourceIds = [];
        $migrationIds = [];
        foreach ($bindings as $index => $binding) {
            if (!$binding instanceof GovernedMigrationBinding) {
                throw new MigrationArtifactBridgeException(
                    MigrationArtifactBridgeException::BINDING_INVALID,
                    'Governed migration binding collection is invalid.',
                );
            }

            $sourceId = $binding->sourceChangeIdentifier->value;
            $migrationId = $binding->migrationIdentifier->value;
            if (isset($sourceIds[$sourceId]) || isset($migrationIds[$migrationId])) {
                throw new MigrationArtifactBridgeException(
                    MigrationArtifactBridgeException::BINDING_INVALID,
                    'Governed migration binding identity is duplicated.',
                );
            }
            if (!$binding->migrationIdentifier->equals($entries[$index]->identifier)) {
                throw new MigrationArtifactBridgeException(
                    MigrationArtifactBridgeException::BINDING_INVALID,
                    'Governed migration binding order does not match the manifest.',
                );
            }

            $sourceIds[$sourceId] = true;
            $migrationIds[$migrationId] = true;
        }

        $this->bindings = array_values($bindings);
    }

    /** @return list<GovernedMigrationBinding> */
    public function bindings(): array
    {
        return $this->bindings;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'source_planning_artifact_fingerprint' => $this->sourcePlanningArtifactFingerprint->value,
            'source_planning_correlation_id' => $this->sourcePlanningCorrelationId->value,
            'source_review_correlation_id' => $this->sourceReviewCorrelationId->value,
            'bridge_correlation_id' => $this->bridgeCorrelationId->value,
            'reviewer_reference' => $this->reviewerReference->value,
            'baseline_fingerprint' => $this->baselineFingerprint->value,
            'target_fingerprint' => $this->targetFingerprint->value,
            'binding_count' => count($this->bindings),
            'bindings' => $this->bindings,
            'manifest' => $this->manifest,
        ];
    }
}

final class DeterministicGovernedMigrationArtifactBridge
{
    public function build(
        MigrationPlanningArtifact $artifact,
        CorrelationId|string $correlationId,
    ): GovernedMigrationManifestArtifact {
        $bridgeCorrelationId = $correlationId instanceof CorrelationId
            ? $correlationId
            : new CorrelationId($correlationId);

        $steps = $artifact->steps();
        if ($steps === [] || count($steps) > 999999) {
            throw new MigrationArtifactBridgeException(
                MigrationArtifactBridgeException::STEP_COUNT_INVALID,
                'Migration planning artifact step count is invalid for bridge generation.',
            );
        }

        $sourceArtifactFingerprint = new ManifestFingerprint(
            hash('sha256', $this->encode($artifact)),
        );

        $definitions = [];
        $bindings = [];
        $seenSourceChanges = [];
        $previousIdentifier = null;

        foreach ($steps as $index => $step) {
            if (!$step instanceof MigrationPlanningStep || !MigrationPlanningStep::isAllowedKind($step->kind)) {
                throw new MigrationArtifactBridgeException(
                    MigrationArtifactBridgeException::STEP_KIND_NOT_ALLOWED,
                    'Migration planning step kind is not allowed for governed bridge generation.',
                );
            }

            if ($step->beforeFingerprint !== null) {
                throw new MigrationArtifactBridgeException(
                    MigrationArtifactBridgeException::STEP_FINGERPRINT_INVALID,
                    'Governed bridge accepts additive planning steps only.',
                );
            }

            $sourceChangeId = $step->sourceChangeIdentifier->value;
            if (isset($seenSourceChanges[$sourceChangeId])) {
                throw new MigrationArtifactBridgeException(
                    MigrationArtifactBridgeException::SOURCE_CHANGE_DUPLICATE,
                    'Migration planning source change identifier is duplicated.',
                );
            }
            $seenSourceChanges[$sourceChangeId] = true;

            $ordinal = $index + 1;
            $migrationIdentifier = new MigrationIdentifier(sprintf(
                'MIG_00000000_%06d_%s_%s',
                $ordinal,
                $this->kindCode($step->kind),
                strtoupper(substr($sourceChangeId, 0, 12)),
            ));

            $checksum = MigrationChecksum::fromCanonicalDescriptor($this->encode([
                'source_planning_artifact_fingerprint' => $sourceArtifactFingerprint->value,
                'ordinal' => $ordinal,
                'source_change_id' => $sourceChangeId,
                'kind' => $step->kind->value,
                'entity_identifier' => $step->entityIdentifier,
                'component_identifier' => $step->componentIdentifier,
                'after_fingerprint' => $step->afterFingerprint->value,
            ]));

            $dependencies = $previousIdentifier === null ? [] : [$previousIdentifier];
            $definition = new MigrationDefinition(
                $migrationIdentifier,
                $checksum,
                $checksum,
                $dependencies,
                MigrationSafetyClassification::CAUTION,
                MigrationRollbackClassification::FORWARD_ONLY,
            );

            $definitions[] = $definition;
            $bindings[] = new GovernedMigrationBinding(
                $step->sourceChangeIdentifier,
                $migrationIdentifier,
            );
            $previousIdentifier = $migrationIdentifier;
        }

        return new GovernedMigrationManifestArtifact(
            $sourceArtifactFingerprint,
            $artifact->planningCorrelationId,
            $artifact->sourceReviewCorrelationId,
            $bridgeCorrelationId,
            $artifact->reviewerReference,
            $artifact->baselineFingerprint,
            $artifact->targetFingerprint,
            $bindings,
            new MigrationManifest($definitions),
        );
    }

    private function kindCode(SchemaChangeKind $kind): string
    {
        return match ($kind) {
            SchemaChangeKind::ENTITY_CREATED => 'ENTITY_CREATED',
            SchemaChangeKind::ATTRIBUTE_ADDED => 'ATTRIBUTE_ADDED',
            SchemaChangeKind::UNIQUE_INDEX_ADDED => 'UNIQUE_INDEX_ADDED',
            SchemaChangeKind::REFERENCE_ADDED => 'REFERENCE_ADDED',
            default => throw new MigrationArtifactBridgeException(
                MigrationArtifactBridgeException::STEP_KIND_NOT_ALLOWED,
                'Migration planning step kind is not allowed for governed bridge generation.',
            ),
        };
    }

    private function encode(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION,
        );
    }
}
