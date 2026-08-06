<?php

declare(strict_types=1);

namespace OneQay\PhysicalMapping;

use OneQay\DataDefinition\DataDefinitionIdentifier;
use OneQay\DataDefinition\TenantScope;

final readonly class PhysicalAttributeMapping implements \JsonSerializable
{
    public function __construct(
        public DataDefinitionIdentifier $logicalIdentifier,
        public PhysicalIdentifier $physicalIdentifier,
        public PhysicalScalarMapping $scalarMapping,
    ) {
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'logical_identifier' => $this->logicalIdentifier->value,
            'physical_identifier' => $this->physicalIdentifier->value,
            'scalar_mapping' => $this->scalarMapping,
        ];
    }
}

final readonly class PhysicalIndexMapping implements \JsonSerializable
{
    /** @var list<string> */
    private array $attributes;

    /** @param list<DataDefinitionIdentifier|string> $attributes */
    public function __construct(
        public PhysicalIdentifier $identifier,
        public IndexKind $kind,
        array $attributes,
    ) {
        if ($attributes === [] || count($attributes) > 16) {
            throw new PhysicalMappingException(PhysicalMappingException::INDEX_INVALID, 'Physical index is invalid.');
        }

        $normalized = [];
        foreach ($attributes as $attribute) {
            $id = $attribute instanceof DataDefinitionIdentifier
                ? $attribute
                : new DataDefinitionIdentifier($attribute);
            if (isset($normalized[$id->value])) {
                throw new PhysicalMappingException(PhysicalMappingException::INDEX_INVALID, 'Physical index attribute is duplicated.');
            }
            $normalized[$id->value] = true;
        }
        $this->attributes = array_keys($normalized);
    }

    /** @return list<string> */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /** @return array{identifier:string,kind:string,attributes:list<string>} */
    public function jsonSerialize(): array
    {
        return [
            'identifier' => $this->identifier->value,
            'kind' => $this->kind->value,
            'attributes' => $this->attributes,
        ];
    }
}

final readonly class PhysicalReferenceMapping implements \JsonSerializable
{
    /** @var array<string,string> */
    private array $attributeMap;

    /** @param array<DataDefinitionIdentifier|string,DataDefinitionIdentifier|string> $attributeMap */
    public function __construct(
        public PhysicalIdentifier $identifier,
        public DataDefinitionIdentifier $targetEntity,
        array $attributeMap,
    ) {
        if ($attributeMap === [] || count($attributeMap) > 16) {
            throw new PhysicalMappingException(PhysicalMappingException::FOREIGN_KEY_INCOMPATIBLE, 'Physical reference is invalid.');
        }

        $normalized = [];
        foreach ($attributeMap as $source => $target) {
            $sourceId = $source instanceof DataDefinitionIdentifier
                ? $source
                : new DataDefinitionIdentifier((string) $source);
            $targetId = $target instanceof DataDefinitionIdentifier
                ? $target
                : new DataDefinitionIdentifier((string) $target);
            if (isset($normalized[$sourceId->value])) {
                throw new PhysicalMappingException(PhysicalMappingException::FOREIGN_KEY_INCOMPATIBLE, 'Physical reference source is duplicated.');
            }
            $normalized[$sourceId->value] = $targetId->value;
        }
        $this->attributeMap = $normalized;
    }

    /** @return array<string,string> */
    public function attributeMap(): array
    {
        return $this->attributeMap;
    }

    /** @return array{identifier:string,target_entity:string,attribute_map:array<string,string>} */
    public function jsonSerialize(): array
    {
        return [
            'identifier' => $this->identifier->value,
            'target_entity' => $this->targetEntity->value,
            'attribute_map' => $this->attributeMap,
        ];
    }
}

final readonly class PhysicalEntityMapping implements \JsonSerializable
{
    /** @var list<PhysicalAttributeMapping> */
    private array $attributes;

    /** @var list<PhysicalIndexMapping> */
    private array $uniqueIndexes;

    /** @var list<PhysicalReferenceMapping> */
    private array $references;

    /**
     * @param list<PhysicalAttributeMapping> $attributes
     * @param list<PhysicalIndexMapping> $uniqueIndexes
     * @param list<PhysicalReferenceMapping> $references
     */
    public function __construct(
        public DataDefinitionIdentifier $logicalIdentifier,
        public PhysicalIdentifier $physicalIdentifier,
        public TenantScope $tenantScope,
        array $attributes,
        public PhysicalIndexMapping $primaryIndex,
        array $uniqueIndexes = [],
        array $references = [],
        public ?DataDefinitionIdentifier $tenantKey = null,
    ) {
        if ($attributes === [] || $this->primaryIndex->kind !== IndexKind::PRIMARY) {
            throw new PhysicalMappingException(PhysicalMappingException::MANIFEST_INVALID, 'Physical entity mapping is invalid.');
        }

        $logical = [];
        $physical = [];
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof PhysicalAttributeMapping) {
                throw new PhysicalMappingException(PhysicalMappingException::MANIFEST_INVALID, 'Physical attribute mapping is invalid.');
            }
            $logicalId = $attribute->logicalIdentifier->value;
            $physicalId = $attribute->physicalIdentifier->value;
            if (isset($logical[$logicalId]) || isset($physical[$physicalId])) {
                throw new PhysicalMappingException(PhysicalMappingException::DUPLICATE_ATTRIBUTE, 'Physical attribute mapping is duplicated.');
            }
            $logical[$logicalId] = true;
            $physical[$physicalId] = true;
        }

        $indexIds = [$this->primaryIndex->identifier->value => true];
        foreach ($uniqueIndexes as $index) {
            if (!$index instanceof PhysicalIndexMapping || $index->kind !== IndexKind::UNIQUE) {
                throw new PhysicalMappingException(PhysicalMappingException::INDEX_INVALID, 'Unique physical index is invalid.');
            }
            if (isset($indexIds[$index->identifier->value])) {
                throw new PhysicalMappingException(PhysicalMappingException::INDEX_INVALID, 'Physical index identifier is duplicated.');
            }
            $indexIds[$index->identifier->value] = true;
        }

        $referenceIds = [];
        foreach ($references as $reference) {
            if (!$reference instanceof PhysicalReferenceMapping) {
                throw new PhysicalMappingException(PhysicalMappingException::MANIFEST_INVALID, 'Physical reference mapping is invalid.');
            }
            if (isset($referenceIds[$reference->identifier->value])) {
                throw new PhysicalMappingException(PhysicalMappingException::FOREIGN_KEY_INCOMPATIBLE, 'Physical reference identifier is duplicated.');
            }
            $referenceIds[$reference->identifier->value] = true;
        }

        $this->attributes = array_values($attributes);
        $this->uniqueIndexes = array_values($uniqueIndexes);
        $this->references = array_values($references);
    }

    /** @return list<PhysicalAttributeMapping> */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /** @return array<string,PhysicalAttributeMapping> */
    public function attributeIndex(): array
    {
        $index = [];
        foreach ($this->attributes as $attribute) {
            $index[$attribute->logicalIdentifier->value] = $attribute;
        }
        return $index;
    }

    /** @return list<PhysicalIndexMapping> */
    public function uniqueIndexes(): array
    {
        return $this->uniqueIndexes;
    }

    /** @return list<PhysicalReferenceMapping> */
    public function references(): array
    {
        return $this->references;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'logical_identifier' => $this->logicalIdentifier->value,
            'physical_identifier' => $this->physicalIdentifier->value,
            'tenant_scope' => $this->tenantScope->value,
            'tenant_key' => $this->tenantKey?->value,
            'attributes' => $this->attributes,
            'primary_index' => $this->primaryIndex,
            'unique_indexes' => $this->uniqueIndexes,
            'references' => $this->references,
        ];
    }
}

final readonly class PhysicalMappingManifest implements \JsonSerializable
{
    /** @var list<PhysicalEntityMapping> */
    private array $entities;

    /** @param list<PhysicalEntityMapping> $entities */
    public function __construct(public VendorIdentifier $vendor, array $entities)
    {
        if ($entities === []) {
            throw new PhysicalMappingException(PhysicalMappingException::MANIFEST_INVALID, 'Physical mapping manifest is empty.');
        }

        $logical = [];
        $physical = [];
        foreach ($entities as $entity) {
            if (!$entity instanceof PhysicalEntityMapping) {
                throw new PhysicalMappingException(PhysicalMappingException::MANIFEST_INVALID, 'Physical mapping entity is invalid.');
            }
            $logicalId = $entity->logicalIdentifier->value;
            $physicalId = $entity->physicalIdentifier->value;
            if (isset($logical[$logicalId]) || isset($physical[$physicalId])) {
                throw new PhysicalMappingException(PhysicalMappingException::DUPLICATE_ENTITY, 'Physical mapping entity is duplicated.');
            }
            $logical[$logicalId] = true;
            $physical[$physicalId] = true;
        }
        $this->entities = array_values($entities);
    }

    /** @return list<PhysicalEntityMapping> */
    public function entities(): array
    {
        return $this->entities;
    }

    /** @return array<string,PhysicalEntityMapping> */
    public function entityIndex(): array
    {
        $index = [];
        foreach ($this->entities as $entity) {
            $index[$entity->logicalIdentifier->value] = $entity;
        }
        return $index;
    }

    /** @return array{vendor:string,entities:list<PhysicalEntityMapping>} */
    public function jsonSerialize(): array
    {
        return ['vendor' => $this->vendor->value, 'entities' => $this->entities];
    }
}
