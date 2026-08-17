<?php

declare(strict_types=1);

namespace OneQay\SchemaPlanning;

use OneQay\DataDefinition\AttributeDefinition;
use OneQay\DataDefinition\DataDefinitionManifest;
use OneQay\DataDefinition\DataDefinitionPolicyValidator;
use OneQay\DataDefinition\DefaultValuePolicy;
use OneQay\DataDefinition\EntityDefinition;
use OneQay\DataDefinition\NullabilityPolicy;
use OneQay\PhysicalMapping\CollationPolicy;
use OneQay\PhysicalMapping\PhysicalAttributeMapping;
use OneQay\PhysicalMapping\PhysicalEntityMapping;
use OneQay\PhysicalMapping\PhysicalIndexMapping;
use OneQay\PhysicalMapping\PhysicalMappingManifest;
use OneQay\PhysicalMapping\PhysicalReferenceMapping;
use OneQay\PhysicalMapping\PhysicalTypeIdentifier;

final class LaravelMigrationGenerationException extends \RuntimeException
{
    public const SOURCE_ARTIFACT_MISMATCH = 'LARAVEL_MIGRATION_GENERATION_SOURCE_ARTIFACT_MISMATCH';
    public const SOURCE_METADATA_MISMATCH = 'LARAVEL_MIGRATION_GENERATION_SOURCE_METADATA_MISMATCH';
    public const TARGET_MANIFEST_MISMATCH = 'LARAVEL_MIGRATION_GENERATION_TARGET_MANIFEST_MISMATCH';
    public const GOVERNED_BINDING_MISMATCH = 'LARAVEL_MIGRATION_GENERATION_GOVERNED_BINDING_MISMATCH';
    public const CHANGE_KIND_NOT_ALLOWED = 'LARAVEL_MIGRATION_GENERATION_CHANGE_KIND_NOT_ALLOWED';
    public const TARGET_ENTITY_MISSING = 'LARAVEL_MIGRATION_GENERATION_TARGET_ENTITY_MISSING';
    public const TARGET_COMPONENT_MISSING = 'LARAVEL_MIGRATION_GENERATION_TARGET_COMPONENT_MISSING';
    public const TARGET_DEFINITION_MISMATCH = 'LARAVEL_MIGRATION_GENERATION_TARGET_DEFINITION_MISMATCH';
    public const AFTER_FINGERPRINT_MISMATCH = 'LARAVEL_MIGRATION_GENERATION_AFTER_FINGERPRINT_MISMATCH';
    public const DEFAULT_POLICY_UNSUPPORTED = 'LARAVEL_MIGRATION_GENERATION_DEFAULT_POLICY_UNSUPPORTED';
    public const REQUIRED_ATTRIBUTE_UNSAFE = 'LARAVEL_MIGRATION_GENERATION_REQUIRED_ATTRIBUTE_UNSAFE';
    public const SCALAR_MAPPING_UNSUPPORTED = 'LARAVEL_MIGRATION_GENERATION_SCALAR_MAPPING_UNSUPPORTED';
    public const REFERENCE_ORDER_UNRESOLVED = 'LARAVEL_MIGRATION_GENERATION_REFERENCE_ORDER_UNRESOLVED';
    public const GENERATED_ARTIFACT_INVALID = 'LARAVEL_MIGRATION_GENERATION_ARTIFACT_INVALID';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

final readonly class LaravelMigrationFileArtifact implements \JsonSerializable
{
    public string $relativePath;
    public string $sourceFingerprint;

    public function __construct(
        public StableChangeIdentifier $sourceChangeIdentifier,
        public \OneQay\Migration\MigrationIdentifier $migrationIdentifier,
        public CorrelationId $generationCorrelationId,
        string $relativePath,
        public string $source,
    ) {
        $path = trim($relativePath);
        if (preg_match('/^database\/migrations\/0000_00_00_[0-9]{6}_[a-z0-9_]+_[a-f0-9]{12}\.php$/D', $path) !== 1
            || !str_starts_with($source, "<?php\n")) {
            throw new LaravelMigrationGenerationException(
                LaravelMigrationGenerationException::GENERATED_ARTIFACT_INVALID,
                'Generated Laravel migration file artifact is invalid.',
            );
        }
        $this->relativePath = $path;
        $this->sourceFingerprint = hash('sha256', $source);
    }

    public function jsonSerialize(): array
    {
        return [
            'source_change_id' => $this->sourceChangeIdentifier->value,
            'migration_identifier' => $this->migrationIdentifier->value,
            'generation_correlation_id' => $this->generationCorrelationId->value,
            'relative_path' => $this->relativePath,
            'source_fingerprint' => $this->sourceFingerprint,
            'source' => $this->source,
        ];
    }
}

final readonly class LaravelMigrationGenerationArtifact implements \JsonSerializable
{
    public const FRAMEWORK = 'LARAVEL';
    public const FRAMEWORK_VERSION = '12.64.0';

    /** @var list<LaravelMigrationFileArtifact> */
    private array $files;

    /** @param list<LaravelMigrationFileArtifact> $files */
    public function __construct(
        public ManifestFingerprint $sourcePlanningArtifactFingerprint,
        public ManifestFingerprint $targetDefinitionFingerprint,
        public ManifestFingerprint $targetManifestFingerprint,
        public CorrelationId $generationCorrelationId,
        array $files,
    ) {
        if ($files === []) {
            throw new LaravelMigrationGenerationException(
                LaravelMigrationGenerationException::GENERATED_ARTIFACT_INVALID,
                'Laravel migration generation artifact is empty.',
            );
        }
        $paths = [];
        $migrationIds = [];
        foreach ($files as $file) {
            if (!$file instanceof LaravelMigrationFileArtifact
                || isset($paths[$file->relativePath])
                || isset($migrationIds[$file->migrationIdentifier->value])) {
                throw new LaravelMigrationGenerationException(
                    LaravelMigrationGenerationException::GENERATED_ARTIFACT_INVALID,
                    'Laravel migration generation artifact contains invalid or duplicated files.',
                );
            }
            $paths[$file->relativePath] = true;
            $migrationIds[$file->migrationIdentifier->value] = true;
        }
        $ordered = array_keys($paths);
        $sorted = $ordered;
        sort($sorted, SORT_STRING);
        if ($ordered !== $sorted) {
            throw new LaravelMigrationGenerationException(
                LaravelMigrationGenerationException::GENERATED_ARTIFACT_INVALID,
                'Laravel migration generation files are not deterministically ordered.',
            );
        }
        $this->files = array_values($files);
    }

    /** @return list<LaravelMigrationFileArtifact> */
    public function files(): array
    {
        return $this->files;
    }

    public function jsonSerialize(): array
    {
        return [
            'framework' => self::FRAMEWORK,
            'framework_version' => self::FRAMEWORK_VERSION,
            'source_planning_artifact_fingerprint' => $this->sourcePlanningArtifactFingerprint->value,
            'target_definition_fingerprint' => $this->targetDefinitionFingerprint->value,
            'target_manifest_fingerprint' => $this->targetManifestFingerprint->value,
            'generation_correlation_id' => $this->generationCorrelationId->value,
            'file_count' => count($this->files),
            'files' => $this->files,
        ];
    }
}

final class DeterministicLaravelMigrationGenerator
{
    public function __construct(
        private readonly PhysicalManifestCanonicalizer $canonicalizer = new PhysicalManifestCanonicalizer(),
        private readonly DataDefinitionPolicyValidator $definitionValidator = new DataDefinitionPolicyValidator(),
    ) {}

    public function generate(
        MigrationPlanningArtifact $planning,
        GovernedMigrationManifestArtifact $governed,
        DataDefinitionManifest $definitions,
        PhysicalMappingManifest $target,
        CorrelationId|string $correlationId,
    ): LaravelMigrationGenerationArtifact {
        $correlation = $correlationId instanceof CorrelationId ? $correlationId : new CorrelationId($correlationId);
        $planningFingerprint = new ManifestFingerprint(hash('sha256', $this->encode($planning)));
        if (!$planningFingerprint->equals($governed->sourcePlanningArtifactFingerprint)) {
            $this->fail(self::SOURCE_ARTIFACT_MISMATCH, 'Sprint 14 planning artifact does not match the governed Sprint 15 fingerprint.');
        }
        if (!hash_equals($planning->planningCorrelationId->value, $governed->sourcePlanningCorrelationId->value)
            || !hash_equals($planning->sourceReviewCorrelationId->value, $governed->sourceReviewCorrelationId->value)
            || !$planning->baselineFingerprint->equals($governed->baselineFingerprint)
            || !$planning->targetFingerprint->equals($governed->targetFingerprint)) {
            $this->fail(self::SOURCE_METADATA_MISMATCH, 'Sprint 14 and Sprint 15 source metadata do not match.');
        }

        $report = $this->definitionValidator->validate($definitions, $correlation->value . ':definition');
        if (!$report->isValid) {
            $this->fail(self::TARGET_DEFINITION_MISMATCH, 'Target data definition violates the published policy.');
        }
        $definitionFingerprint = new ManifestFingerprint(hash('sha256', $this->encode($this->canonicalDefinitions($definitions))));
        $targetCanonical = $this->canonicalizer->canonicalize($target);
        $targetFingerprint = $this->canonicalizer->fingerprint($targetCanonical);
        if (!$targetFingerprint->equals($governed->targetFingerprint)) {
            $this->fail(self::TARGET_MANIFEST_MISMATCH, 'Target physical mapping does not match the governed target fingerprint.');
        }

        $steps = $planning->steps();
        $bindings = $governed->bindings();
        $entries = $governed->manifest->entries();
        if (count($steps) !== count($bindings) || count($steps) !== count($entries)) {
            $this->fail(self::GOVERNED_BINDING_MISMATCH, 'Governed migration bindings do not match planning steps.');
        }
        $created = [];
        foreach ($steps as $index => $step) {
            if ($step->kind === SchemaChangeKind::ENTITY_CREATED) {
                $created[$step->entityIdentifier] = $index;
            }
        }

        $definitionEntities = $definitions->entityIndex();
        $physicalEntities = $target->entityIndex();
        $files = [];
        foreach ($steps as $index => $step) {
            if (!MigrationPlanningStep::isAllowedKind($step->kind)) {
                $this->fail(self::CHANGE_KIND_NOT_ALLOWED, 'Schema change kind is not allowed for Laravel migration generation.');
            }
            $binding = $bindings[$index];
            $entry = $entries[$index];
            if (!hash_equals($binding->sourceChangeIdentifier->value, $step->sourceChangeIdentifier->value)
                || !$binding->migrationIdentifier->equals($entry->identifier)) {
                $this->fail(self::GOVERNED_BINDING_MISMATCH, 'Governed binding order does not match the planning artifact.');
            }
            $definitionEntity = $definitionEntities[$step->entityIdentifier] ?? null;
            $physicalEntity = $physicalEntities[$step->entityIdentifier] ?? null;
            if (!$definitionEntity instanceof EntityDefinition || !$physicalEntity instanceof PhysicalEntityMapping) {
                $this->fail(self::TARGET_ENTITY_MISSING, 'Target entity required for generation is missing.');
            }
            $this->assertEntityMatches($definitionEntity, $physicalEntity);
            $source = match ($step->kind) {
                SchemaChangeKind::ENTITY_CREATED => $this->entityCreated($step, $definitionEntity, $physicalEntity, $targetCanonical, $physicalEntities, $created, $index),
                SchemaChangeKind::ATTRIBUTE_ADDED => $this->attributeAdded($step, $definitionEntity, $physicalEntity, $targetCanonical),
                SchemaChangeKind::UNIQUE_INDEX_ADDED => $this->uniqueAdded($step, $physicalEntity, $targetCanonical),
                SchemaChangeKind::REFERENCE_ADDED => $this->referenceAdded($step, $physicalEntity, $targetCanonical, $physicalEntities, $created, $index),
                default => throw new LaravelMigrationGenerationException(self::CHANGE_KIND_NOT_ALLOWED, 'Schema change kind is not allowed.'),
            };
            $files[] = new LaravelMigrationFileArtifact(
                $step->sourceChangeIdentifier,
                $binding->migrationIdentifier,
                $correlation,
                sprintf('database/migrations/0000_00_00_%06d_%s_%s.php', $index + 1, strtolower($step->kind->value), substr($step->sourceChangeIdentifier->value, 0, 12)),
                $source,
            );
        }

        return new LaravelMigrationGenerationArtifact($planningFingerprint, $definitionFingerprint, $targetFingerprint, $correlation, $files);
    }

    private function entityCreated(MigrationPlanningStep $step, EntityDefinition $definition, PhysicalEntityMapping $entity, array $canonical, array $entities, array $created, int $current): string
    {
        $this->assertAfter($step, $canonical['entities'][$step->entityIdentifier] ?? null);
        $defs = $definition->attributeIndex();
        $lines = [];
        foreach ($entity->attributes() as $attribute) {
            $def = $defs[$attribute->logicalIdentifier->value] ?? null;
            if (!$def instanceof AttributeDefinition) {
                $this->fail(self::TARGET_DEFINITION_MISMATCH, 'Entity creation is missing an attribute definition.');
            }
            $lines[] = '            ' . $this->column($attribute, $def, false) . ';';
        }
        $lines[] = '            ' . $this->primary($entity) . ';';
        foreach ($entity->uniqueIndexes() as $index) {
            $lines[] = '            ' . $this->unique($entity, $index) . ';';
        }
        foreach ($entity->references() as $reference) {
            $this->assertReferenceOrder($reference, $created, $current);
            $lines[] = '            ' . $this->foreign($entity, $reference, $entities) . ';';
        }
        return $this->wrap(sprintf("        Schema::create('%s', function (Blueprint \$table): void {\n%s\n        });", $entity->physicalIdentifier->value, implode("\n", $lines)));
    }

    private function attributeAdded(MigrationPlanningStep $step, EntityDefinition $definition, PhysicalEntityMapping $entity, array $canonical): string
    {
        $component = $step->componentIdentifier;
        $attribute = $component === null ? null : ($entity->attributeIndex()[$component] ?? null);
        $def = $component === null ? null : ($definition->attributeIndex()[$component] ?? null);
        if (!$attribute instanceof PhysicalAttributeMapping || !$def instanceof AttributeDefinition) {
            $this->fail(self::TARGET_COMPONENT_MISSING, 'Target attribute required for generation is missing.');
        }
        $this->assertAfter($step, $canonical['entities'][$step->entityIdentifier]['attributes'][$component] ?? null);
        return $this->wrap(sprintf("        Schema::table('%s', function (Blueprint \$table): void {\n            %s;\n        });", $entity->physicalIdentifier->value, $this->column($attribute, $def, true)));
    }

    private function uniqueAdded(MigrationPlanningStep $step, PhysicalEntityMapping $entity, array $canonical): string
    {
        $component = $step->componentIdentifier;
        $index = null;
        foreach ($entity->uniqueIndexes() as $candidate) {
            if ($candidate->identifier->value === $component) {
                $index = $candidate;
                break;
            }
        }
        if (!$index instanceof PhysicalIndexMapping) {
            $this->fail(self::TARGET_COMPONENT_MISSING, 'Target unique index required for generation is missing.');
        }
        $this->assertAfter($step, $canonical['entities'][$step->entityIdentifier]['unique_indexes'][$component] ?? null);
        return $this->wrap(sprintf("        Schema::table('%s', function (Blueprint \$table): void {\n            %s;\n        });", $entity->physicalIdentifier->value, $this->unique($entity, $index)));
    }

    private function referenceAdded(MigrationPlanningStep $step, PhysicalEntityMapping $entity, array $canonical, array $entities, array $created, int $current): string
    {
        $component = $step->componentIdentifier;
        $reference = null;
        foreach ($entity->references() as $candidate) {
            if ($candidate->identifier->value === $component) {
                $reference = $candidate;
                break;
            }
        }
        if (!$reference instanceof PhysicalReferenceMapping) {
            $this->fail(self::TARGET_COMPONENT_MISSING, 'Target reference required for generation is missing.');
        }
        $this->assertAfter($step, $canonical['entities'][$step->entityIdentifier]['references'][$component] ?? null);
        $this->assertReferenceOrder($reference, $created, $current);
        return $this->wrap(sprintf("        Schema::table('%s', function (Blueprint \$table): void {\n            %s;\n        });", $entity->physicalIdentifier->value, $this->foreign($entity, $reference, $entities)));
    }

    private function column(PhysicalAttributeMapping $mapping, AttributeDefinition $definition, bool $existing): string
    {
        $this->assertAttributeMatches($definition, $mapping);
        if (in_array($definition->defaultValue->policy, [DefaultValuePolicy::LITERAL_FINGERPRINT, DefaultValuePolicy::GENERATED_IDENTIFIER], true)
            || ($definition->defaultValue->policy === DefaultValuePolicy::NULL_VALUE && $definition->nullability !== NullabilityPolicy::NULLABLE)) {
            $this->fail(self::DEFAULT_POLICY_UNSUPPORTED, 'Target default policy cannot be reconstructed safely.');
        }
        if ($existing && $definition->nullability === NullabilityPolicy::REQUIRED && $definition->defaultValue->policy === DefaultValuePolicy::NONE) {
            $this->fail(self::REQUIRED_ATTRIBUTE_UNSAFE, 'Required attribute addition without a reconstructible safe default is not generation-safe.');
        }
        $name = $mapping->physicalIdentifier->value;
        $scalar = $mapping->scalarMapping;
        $line = match ($scalar->physicalType->value) {
            PhysicalTypeIdentifier::VARCHAR => sprintf("\$table->string('%s', %d)", $name, $scalar->length),
            PhysicalTypeIdentifier::BIGINT_SIGNED => sprintf("\$table->bigInteger('%s')", $name),
            PhysicalTypeIdentifier::DECIMAL => sprintf("\$table->decimal('%s', %d, %d)", $name, $scalar->precision, $scalar->scale),
            PhysicalTypeIdentifier::TINYINT_BOOLEAN => sprintf("\$table->boolean('%s')", $name),
            PhysicalTypeIdentifier::CHAR_UUID => sprintf("\$table->char('%s', 36)", $name),
            PhysicalTypeIdentifier::DATE => sprintf("\$table->date('%s')", $name),
            PhysicalTypeIdentifier::DATETIME => sprintf("\$table->dateTime('%s')", $name),
            PhysicalTypeIdentifier::JSON_DOCUMENT => sprintf("\$table->json('%s')", $name),
            default => throw new LaravelMigrationGenerationException(self::SCALAR_MAPPING_UNSUPPORTED, 'Physical scalar mapping is unsupported.'),
        };
        if ($scalar->charset !== null) {
            $line .= "->charset('utf8mb4')";
        }
        if ($scalar->collation !== null) {
            $collation = match ($scalar->collation->value) {
                CollationPolicy::UNICODE_CI => 'utf8mb4_unicode_ci',
                CollationPolicy::BINARY => 'utf8mb4_bin',
                default => throw new LaravelMigrationGenerationException(self::SCALAR_MAPPING_UNSUPPORTED, 'Character collation is unsupported.'),
            };
            $line .= "->collation('{$collation}')";
        }
        if ($definition->nullability === NullabilityPolicy::NULLABLE) {
            $line .= '->nullable()';
        }
        if ($definition->defaultValue->policy === DefaultValuePolicy::NULL_VALUE) {
            $line .= '->default(null)';
        }
        return $line;
    }

    private function primary(PhysicalEntityMapping $entity): string
    {
        return sprintf("\$table->primary(%s, '%s')", $this->phpArray($this->physicalNames($entity, $entity->primaryIndex->attributes())), $entity->primaryIndex->identifier->value);
    }

    private function unique(PhysicalEntityMapping $entity, PhysicalIndexMapping $index): string
    {
        return sprintf("\$table->unique(%s, '%s')", $this->phpArray($this->physicalNames($entity, $index->attributes())), $index->identifier->value);
    }

    private function foreign(PhysicalEntityMapping $source, PhysicalReferenceMapping $reference, array $entities): string
    {
        $target = $entities[$reference->targetEntity->value] ?? null;
        if (!$target instanceof PhysicalEntityMapping) {
            $this->fail(self::TARGET_ENTITY_MISSING, 'Reference target entity is missing.');
        }
        $sourceNames = [];
        $targetNames = [];
        foreach ($reference->attributeMap() as $sourceId => $targetId) {
            $sourceAttribute = $source->attributeIndex()[$sourceId] ?? null;
            $targetAttribute = $target->attributeIndex()[$targetId] ?? null;
            if (!$sourceAttribute instanceof PhysicalAttributeMapping || !$targetAttribute instanceof PhysicalAttributeMapping) {
                $this->fail(self::TARGET_COMPONENT_MISSING, 'Reference column mapping is incomplete.');
            }
            $sourceNames[] = $sourceAttribute->physicalIdentifier->value;
            $targetNames[] = $targetAttribute->physicalIdentifier->value;
        }
        return sprintf("\$table->foreign(%s, '%s')->references(%s)->on('%s')", $this->phpArray($sourceNames), $reference->identifier->value, $this->phpArray($targetNames), $target->physicalIdentifier->value);
    }

    private function assertEntityMatches(EntityDefinition $definition, PhysicalEntityMapping $mapping): void
    {
        if ($definition->identifier->value !== $mapping->logicalIdentifier->value
            || $definition->tenantScope !== $mapping->tenantScope
            || $definition->tenantKey?->value !== $mapping->tenantKey?->value
            || count($definition->attributes()) !== count($mapping->attributes())
            || $definition->primaryKey->attributes() !== $mapping->primaryIndex->attributes()) {
            $this->fail(self::TARGET_DEFINITION_MISMATCH, 'Target entity definition does not match its physical mapping.');
        }
        foreach ($mapping->attributes() as $attribute) {
            $definitionAttribute = $definition->attributeIndex()[$attribute->logicalIdentifier->value] ?? null;
            if (!$definitionAttribute instanceof AttributeDefinition) {
                $this->fail(self::TARGET_DEFINITION_MISMATCH, 'Mapped attribute definition is missing.');
            }
            $this->assertAttributeMatches($definitionAttribute, $attribute);
        }
        $logicalUnique = array_map(static fn ($constraint): array => $constraint->attributes(), $definition->uniqueConstraints());
        if (count($logicalUnique) !== count($mapping->uniqueIndexes())) {
            $this->fail(self::TARGET_DEFINITION_MISMATCH, 'Unique constraint definitions do not match physical mapping.');
        }
        foreach ($mapping->uniqueIndexes() as $index) {
            if (!in_array($index->attributes(), $logicalUnique, true)) {
                $this->fail(self::TARGET_DEFINITION_MISMATCH, 'Unique constraint definitions do not match physical mapping.');
            }
        }
        $logicalReferences = array_map(static fn ($reference): array => ['target' => $reference->targetEntity->value, 'map' => $reference->attributeMap()], $definition->references());
        if (count($logicalReferences) !== count($mapping->references())) {
            $this->fail(self::TARGET_DEFINITION_MISMATCH, 'Reference definitions do not match physical mapping.');
        }
        foreach ($mapping->references() as $reference) {
            if (!in_array(['target' => $reference->targetEntity->value, 'map' => $reference->attributeMap()], $logicalReferences, true)) {
                $this->fail(self::TARGET_DEFINITION_MISMATCH, 'Reference definitions do not match physical mapping.');
            }
        }
    }

    private function assertAttributeMatches(AttributeDefinition $definition, PhysicalAttributeMapping $mapping): void
    {
        $constraint = $definition->constraint;
        $scalar = $mapping->scalarMapping;
        if ($definition->identifier->value !== $mapping->logicalIdentifier->value
            || !$constraint->type->equals($scalar->logicalType)
            || $constraint->length !== ($scalar->logicalType->value === 'STRING' ? $scalar->length : null)
            || $constraint->precision !== ($scalar->logicalType->value === 'DECIMAL' ? $scalar->precision : null)
            || $constraint->scale !== ($scalar->logicalType->value === 'DECIMAL' ? $scalar->scale : null)) {
            $this->fail(self::TARGET_DEFINITION_MISMATCH, 'Target attribute definition does not match its physical mapping.');
        }
    }

    private function assertAfter(MigrationPlanningStep $step, mixed $canonical): void
    {
        if ($canonical === null) {
            $this->fail(self::TARGET_COMPONENT_MISSING, 'Canonical target component is missing.');
        }
        if (!$this->canonicalizer->fingerprint($canonical)->equals($step->afterFingerprint)) {
            $this->fail(self::AFTER_FINGERPRINT_MISMATCH, 'Target component fingerprint does not match the approved planning step.');
        }
    }

    private function assertReferenceOrder(PhysicalReferenceMapping $reference, array $created, int $current): void
    {
        $targetIndex = $created[$reference->targetEntity->value] ?? null;
        if ($targetIndex !== null && $targetIndex > $current) {
            $this->fail(self::REFERENCE_ORDER_UNRESOLVED, 'Reference target is created later than the referencing migration.');
        }
    }

    private function physicalNames(PhysicalEntityMapping $entity, array $logical): array
    {
        $names = [];
        foreach ($logical as $id) {
            $attribute = $entity->attributeIndex()[$id] ?? null;
            if (!$attribute instanceof PhysicalAttributeMapping) {
                $this->fail(self::TARGET_COMPONENT_MISSING, 'Index attribute does not resolve to a physical column.');
            }
            $names[] = $attribute->physicalIdentifier->value;
        }
        return $names;
    }

    private function wrap(string $up): string
    {
        return "<?php\n\ndeclare(strict_types=1);\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n{$up}\n    }\n\n    public function down(): void\n    {\n        throw new \\LogicException('Forward-only generated migration; rollback is not authorized.');\n    }\n};\n";
    }

    private function phpArray(array $values): string
    {
        return '[' . implode(', ', array_map(static fn (string $value): string => "'{$value}'", $values)) . ']';
    }

    private function canonicalDefinitions(DataDefinitionManifest $manifest): array
    {
        $entities = [];
        foreach ($manifest->entities() as $entity) {
            $attributes = [];
            foreach ($entity->attributes() as $attribute) {
                $attributes[$attribute->identifier->value] = ['constraint' => $attribute->constraint, 'nullability' => $attribute->nullability->value, 'default' => $attribute->defaultValue];
            }
            ksort($attributes, SORT_STRING);
            $unique = [];
            foreach ($entity->uniqueConstraints() as $constraint) {
                $unique[$constraint->identifier->value] = $constraint->attributes();
            }
            ksort($unique, SORT_STRING);
            $references = [];
            foreach ($entity->references() as $reference) {
                $map = $reference->attributeMap();
                ksort($map, SORT_STRING);
                $references[$reference->identifier->value] = ['target_entity' => $reference->targetEntity->value, 'attribute_map' => $map];
            }
            ksort($references, SORT_STRING);
            $entities[$entity->identifier->value] = [
                'tenant_scope' => $entity->tenantScope->value,
                'tenant_key' => $entity->tenantKey?->value,
                'attributes' => $attributes,
                'primary_key' => $entity->primaryKey->attributes(),
                'unique_constraints' => $unique,
                'references' => $references,
            ];
        }
        ksort($entities, SORT_STRING);
        return ['entities' => $entities];
    }

    private function encode(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }

    private function fail(string $code, string $message): never
    {
        throw new LaravelMigrationGenerationException($code, $message);
    }

    private const SOURCE_ARTIFACT_MISMATCH = LaravelMigrationGenerationException::SOURCE_ARTIFACT_MISMATCH;
    private const SOURCE_METADATA_MISMATCH = LaravelMigrationGenerationException::SOURCE_METADATA_MISMATCH;
    private const TARGET_MANIFEST_MISMATCH = LaravelMigrationGenerationException::TARGET_MANIFEST_MISMATCH;
    private const GOVERNED_BINDING_MISMATCH = LaravelMigrationGenerationException::GOVERNED_BINDING_MISMATCH;
    private const CHANGE_KIND_NOT_ALLOWED = LaravelMigrationGenerationException::CHANGE_KIND_NOT_ALLOWED;
    private const TARGET_ENTITY_MISSING = LaravelMigrationGenerationException::TARGET_ENTITY_MISSING;
    private const TARGET_COMPONENT_MISSING = LaravelMigrationGenerationException::TARGET_COMPONENT_MISSING;
    private const TARGET_DEFINITION_MISMATCH = LaravelMigrationGenerationException::TARGET_DEFINITION_MISMATCH;
    private const AFTER_FINGERPRINT_MISMATCH = LaravelMigrationGenerationException::AFTER_FINGERPRINT_MISMATCH;
    private const DEFAULT_POLICY_UNSUPPORTED = LaravelMigrationGenerationException::DEFAULT_POLICY_UNSUPPORTED;
    private const REQUIRED_ATTRIBUTE_UNSAFE = LaravelMigrationGenerationException::REQUIRED_ATTRIBUTE_UNSAFE;
    private const SCALAR_MAPPING_UNSUPPORTED = LaravelMigrationGenerationException::SCALAR_MAPPING_UNSUPPORTED;
    private const REFERENCE_ORDER_UNRESOLVED = LaravelMigrationGenerationException::REFERENCE_ORDER_UNRESOLVED;
}
