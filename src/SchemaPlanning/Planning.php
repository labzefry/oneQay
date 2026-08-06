<?php

declare(strict_types=1);

namespace OneQay\SchemaPlanning;

use OneQay\PhysicalMapping\PhysicalAttributeMapping;
use OneQay\PhysicalMapping\PhysicalEntityMapping;
use OneQay\PhysicalMapping\PhysicalIndexMapping;
use OneQay\PhysicalMapping\PhysicalMappingManifest;
use OneQay\PhysicalMapping\PhysicalReferenceMapping;
use OneQay\PhysicalMapping\VendorCompatibilityValidator;

final class PhysicalManifestCanonicalizer
{
    /** @return array<string,mixed> */
    public function canonicalize(PhysicalMappingManifest $manifest): array
    {
        $entities = [];
        foreach ($manifest->entities() as $entity) {
            $entities[$entity->logicalIdentifier->value] = $this->canonicalizeEntity($entity);
        }
        ksort($entities, SORT_STRING);

        return [
            'vendor' => $manifest->vendor->value,
            'entities' => $entities,
        ];
    }

    public function fingerprint(mixed $value): ManifestFingerprint
    {
        return new ManifestFingerprint(hash('sha256', $this->encode($value)));
    }

    public function encode(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION,
        );
    }

    /** @return array<string,mixed> */
    private function canonicalizeEntity(PhysicalEntityMapping $entity): array
    {
        $attributes = [];
        foreach ($entity->attributes() as $attribute) {
            $attributes[$attribute->logicalIdentifier->value] = $this->canonicalizeAttribute($attribute);
        }
        ksort($attributes, SORT_STRING);

        $uniqueIndexes = [];
        foreach ($entity->uniqueIndexes() as $index) {
            $uniqueIndexes[$index->identifier->value] = $this->canonicalizeIndex($index);
        }
        ksort($uniqueIndexes, SORT_STRING);

        $references = [];
        foreach ($entity->references() as $reference) {
            $references[$reference->identifier->value] = $this->canonicalizeReference($reference);
        }
        ksort($references, SORT_STRING);

        return [
            'logical_identifier' => $entity->logicalIdentifier->value,
            'physical_identifier' => $entity->physicalIdentifier->value,
            'tenant_scope' => $entity->tenantScope->value,
            'tenant_key' => $entity->tenantKey?->value,
            'attributes' => $attributes,
            'primary_index' => $this->canonicalizeIndex($entity->primaryIndex),
            'unique_indexes' => $uniqueIndexes,
            'references' => $references,
        ];
    }

    /** @return array<string,mixed> */
    private function canonicalizeAttribute(PhysicalAttributeMapping $attribute): array
    {
        return [
            'logical_identifier' => $attribute->logicalIdentifier->value,
            'physical_identifier' => $attribute->physicalIdentifier->value,
            'scalar_mapping' => [
                'logical_type' => $attribute->scalarMapping->logicalType->value,
                'physical_type' => $attribute->scalarMapping->physicalType->value,
                'length' => $attribute->scalarMapping->length,
                'precision' => $attribute->scalarMapping->precision,
                'scale' => $attribute->scalarMapping->scale,
                'charset' => $attribute->scalarMapping->charset?->value,
                'collation' => $attribute->scalarMapping->collation?->value,
            ],
        ];
    }

    /** @return array<string,mixed> */
    private function canonicalizeIndex(PhysicalIndexMapping $index): array
    {
        return [
            'identifier' => $index->identifier->value,
            'kind' => $index->kind->value,
            'attributes' => $index->attributes(),
        ];
    }

    /** @return array<string,mixed> */
    private function canonicalizeReference(PhysicalReferenceMapping $reference): array
    {
        $attributeMap = $reference->attributeMap();
        ksort($attributeMap, SORT_STRING);

        return [
            'identifier' => $reference->identifier->value,
            'target_entity' => $reference->targetEntity->value,
            'attribute_map' => $attributeMap,
        ];
    }
}

final class DeterministicPhysicalSchemaPlanner implements PhysicalSchemaPlanner
{
    public function __construct(
        private readonly VendorCompatibilityValidator $validator = new VendorCompatibilityValidator(),
        private readonly PhysicalManifestCanonicalizer $canonicalizer = new PhysicalManifestCanonicalizer(),
    ) {
    }

    public function plan(
        PhysicalMappingManifest $baseline,
        PhysicalMappingManifest $target,
        CorrelationId|string $correlationId,
    ): PhysicalSchemaPlan {
        $correlation = $correlationId instanceof CorrelationId
            ? $correlationId
            : new CorrelationId($correlationId);

        $this->assertCompatible($baseline, $correlation->value . ':baseline');
        $this->assertCompatible($target, $correlation->value . ':target');

        $baselineCanonical = $this->canonicalizer->canonicalize($baseline);
        $targetCanonical = $this->canonicalizer->canonicalize($target);
        $changes = [];

        if ($baselineCanonical['vendor'] !== $targetCanonical['vendor']) {
            $changes[] = $this->change(
                SchemaChangeKind::VENDOR_CHANGED,
                ChangeRisk::BLOCKED,
                'MANIFEST',
                null,
                ['vendor' => $baselineCanonical['vendor']],
                ['vendor' => $targetCanonical['vendor']],
            );
        }

        /** @var array<string,array<string,mixed>> $baselineEntities */
        $baselineEntities = $baselineCanonical['entities'];
        /** @var array<string,array<string,mixed>> $targetEntities */
        $targetEntities = $targetCanonical['entities'];
        $entityIds = array_values(array_unique(array_merge(array_keys($baselineEntities), array_keys($targetEntities))));
        sort($entityIds, SORT_STRING);

        foreach ($entityIds as $entityId) {
            $before = $baselineEntities[$entityId] ?? null;
            $after = $targetEntities[$entityId] ?? null;

            if ($before === null) {
                $changes[] = $this->change(
                    SchemaChangeKind::ENTITY_CREATED,
                    ChangeRisk::REVIEW_REQUIRED,
                    $entityId,
                    null,
                    null,
                    $after,
                );
                continue;
            }
            if ($after === null) {
                $changes[] = $this->change(
                    SchemaChangeKind::ENTITY_REMOVED,
                    ChangeRisk::BLOCKED,
                    $entityId,
                    null,
                    $before,
                    null,
                );
                continue;
            }

            array_push($changes, ...$this->compareEntity($entityId, $before, $after));
        }

        $disposition = $this->disposition($changes);

        return new PhysicalSchemaPlan(
            $this->canonicalizer->fingerprint($baselineCanonical),
            $this->canonicalizer->fingerprint($targetCanonical),
            $disposition,
            $correlation,
            $changes,
        );
    }

    private function assertCompatible(PhysicalMappingManifest $manifest, string $correlationId): void
    {
        $report = $this->validator->validate($manifest, $correlationId);
        if (!$report->isCompatible) {
            throw new SchemaPlanningException(
                SchemaPlanningException::MANIFEST_INCOMPATIBLE,
                'Physical mapping manifest is not compatible with the published vendor boundary.',
                $report->errorCodes,
            );
        }
    }

    /**
     * @param array<string,mixed> $before
     * @param array<string,mixed> $after
     * @return list<PhysicalSchemaChange>
     */
    private function compareEntity(string $entityId, array $before, array $after): array
    {
        $changes = [];

        if ($before['physical_identifier'] !== $after['physical_identifier']) {
            $changes[] = $this->change(
                SchemaChangeKind::ENTITY_PHYSICAL_IDENTIFIER_CHANGED,
                ChangeRisk::BLOCKED,
                $entityId,
                null,
                ['physical_identifier' => $before['physical_identifier']],
                ['physical_identifier' => $after['physical_identifier']],
            );
        }
        if ($before['tenant_scope'] !== $after['tenant_scope']) {
            $changes[] = $this->change(
                SchemaChangeKind::TENANT_SCOPE_CHANGED,
                ChangeRisk::BLOCKED,
                $entityId,
                null,
                ['tenant_scope' => $before['tenant_scope']],
                ['tenant_scope' => $after['tenant_scope']],
            );
        }
        if ($before['tenant_key'] !== $after['tenant_key']) {
            $changes[] = $this->change(
                SchemaChangeKind::TENANT_KEY_CHANGED,
                ChangeRisk::BLOCKED,
                $entityId,
                null,
                ['tenant_key' => $before['tenant_key']],
                ['tenant_key' => $after['tenant_key']],
            );
        }

        /** @var array<string,array<string,mixed>> $beforeAttributes */
        $beforeAttributes = $before['attributes'];
        /** @var array<string,array<string,mixed>> $afterAttributes */
        $afterAttributes = $after['attributes'];
        $attributeIds = array_values(array_unique(array_merge(array_keys($beforeAttributes), array_keys($afterAttributes))));
        sort($attributeIds, SORT_STRING);

        foreach ($attributeIds as $attributeId) {
            $beforeAttribute = $beforeAttributes[$attributeId] ?? null;
            $afterAttribute = $afterAttributes[$attributeId] ?? null;
            if ($beforeAttribute === null) {
                $changes[] = $this->change(
                    SchemaChangeKind::ATTRIBUTE_ADDED,
                    ChangeRisk::REVIEW_REQUIRED,
                    $entityId,
                    $attributeId,
                    null,
                    $afterAttribute,
                );
                continue;
            }
            if ($afterAttribute === null) {
                $changes[] = $this->change(
                    SchemaChangeKind::ATTRIBUTE_REMOVED,
                    ChangeRisk::BLOCKED,
                    $entityId,
                    $attributeId,
                    $beforeAttribute,
                    null,
                );
                continue;
            }

            /** @var array<string,mixed> $beforeScalar */
            $beforeScalar = $beforeAttribute['scalar_mapping'];
            /** @var array<string,mixed> $afterScalar */
            $afterScalar = $afterAttribute['scalar_mapping'];
            if ($beforeScalar['logical_type'] !== $afterScalar['logical_type']) {
                $changes[] = $this->change(
                    SchemaChangeKind::ATTRIBUTE_SCALAR_MAPPING_CHANGED,
                    ChangeRisk::BLOCKED,
                    $entityId,
                    $attributeId,
                    ['logical_type' => $beforeScalar['logical_type']],
                    ['logical_type' => $afterScalar['logical_type']],
                );
            }

            $beforePhysical = $beforeScalar;
            $afterPhysical = $afterScalar;
            unset($beforePhysical['logical_type'], $afterPhysical['logical_type']);
            $beforePhysical['physical_identifier'] = $beforeAttribute['physical_identifier'];
            $afterPhysical['physical_identifier'] = $afterAttribute['physical_identifier'];
            if ($beforePhysical !== $afterPhysical) {
                $changes[] = $this->change(
                    SchemaChangeKind::ATTRIBUTE_PHYSICAL_MAPPING_CHANGED,
                    ChangeRisk::BLOCKED,
                    $entityId,
                    $attributeId,
                    $beforePhysical,
                    $afterPhysical,
                );
            }
        }

        if ($before['primary_index'] !== $after['primary_index']) {
            $changes[] = $this->change(
                SchemaChangeKind::PRIMARY_INDEX_CHANGED,
                ChangeRisk::BLOCKED,
                $entityId,
                (string) $before['primary_index']['identifier'],
                $before['primary_index'],
                $after['primary_index'],
            );
        }

        array_push(
            $changes,
            ...$this->compareNamedComponents(
                $entityId,
                $before['unique_indexes'],
                $after['unique_indexes'],
                SchemaChangeKind::UNIQUE_INDEX_ADDED,
                SchemaChangeKind::UNIQUE_INDEX_REMOVED,
                SchemaChangeKind::UNIQUE_INDEX_CHANGED,
            ),
            ...$this->compareNamedComponents(
                $entityId,
                $before['references'],
                $after['references'],
                SchemaChangeKind::REFERENCE_ADDED,
                SchemaChangeKind::REFERENCE_REMOVED,
                SchemaChangeKind::REFERENCE_CHANGED,
            ),
        );

        return $changes;
    }

    /**
     * @param array<string,array<string,mixed>> $before
     * @param array<string,array<string,mixed>> $after
     * @return list<PhysicalSchemaChange>
     */
    private function compareNamedComponents(
        string $entityId,
        array $before,
        array $after,
        SchemaChangeKind $addedKind,
        SchemaChangeKind $removedKind,
        SchemaChangeKind $changedKind,
    ): array {
        $changes = [];
        $identifiers = array_values(array_unique(array_merge(array_keys($before), array_keys($after))));
        sort($identifiers, SORT_STRING);

        foreach ($identifiers as $identifier) {
            $beforeComponent = $before[$identifier] ?? null;
            $afterComponent = $after[$identifier] ?? null;
            if ($beforeComponent === null) {
                $changes[] = $this->change(
                    $addedKind,
                    ChangeRisk::REVIEW_REQUIRED,
                    $entityId,
                    $identifier,
                    null,
                    $afterComponent,
                );
                continue;
            }
            if ($afterComponent === null) {
                $changes[] = $this->change(
                    $removedKind,
                    ChangeRisk::BLOCKED,
                    $entityId,
                    $identifier,
                    $beforeComponent,
                    null,
                );
                continue;
            }
            if ($beforeComponent !== $afterComponent) {
                $changes[] = $this->change(
                    $changedKind,
                    ChangeRisk::BLOCKED,
                    $entityId,
                    $identifier,
                    $beforeComponent,
                    $afterComponent,
                );
            }
        }

        return $changes;
    }

    private function change(
        SchemaChangeKind $kind,
        ChangeRisk $risk,
        string $entityIdentifier,
        ?string $componentIdentifier,
        mixed $before,
        mixed $after,
    ): PhysicalSchemaChange {
        $beforeFingerprint = $before === null ? null : $this->canonicalizer->fingerprint($before);
        $afterFingerprint = $after === null ? null : $this->canonicalizer->fingerprint($after);
        $stablePayload = [
            'kind' => $kind->value,
            'risk' => $risk->value,
            'entity_identifier' => $entityIdentifier,
            'component_identifier' => $componentIdentifier,
            'before_fingerprint' => $beforeFingerprint?->value,
            'after_fingerprint' => $afterFingerprint?->value,
        ];

        return new PhysicalSchemaChange(
            new StableChangeIdentifier(hash('sha256', $this->canonicalizer->encode($stablePayload))),
            $kind,
            $risk,
            $entityIdentifier,
            $componentIdentifier,
            $beforeFingerprint,
            $afterFingerprint,
        );
    }

    /** @param list<PhysicalSchemaChange> $changes */
    private function disposition(array $changes): PlanDisposition
    {
        foreach ($changes as $change) {
            if ($change->risk === ChangeRisk::BLOCKED) {
                return PlanDisposition::BLOCKED;
            }
        }

        return $changes === [] ? PlanDisposition::NO_CHANGES : PlanDisposition::REVIEW_REQUIRED;
    }
}
