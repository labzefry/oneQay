<?php

declare(strict_types=1);



namespace OneQay\DataDefinition;

final readonly class AttributeDefinition implements \JsonSerializable
{
    public function __construct(
        public DataDefinitionIdentifier $identifier,
        public ValueConstraint $constraint,
        public NullabilityPolicy $nullability,
        public DefaultValueDefinition $defaultValue,
    ) {
        if ($this->nullability === NullabilityPolicy::REQUIRED
            && $this->defaultValue->policy === DefaultValuePolicy::NULL_VALUE) {
            throw new DataDefinitionException(
                DataDefinitionException::DEFAULT_INVALID,
                'Required attribute cannot use a null default policy.'
            );
        }
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'identifier' => $this->identifier->value,
            'constraint' => $this->constraint,
            'nullability' => $this->nullability->value,
            'default' => $this->defaultValue,
        ];
    }
}

final readonly class PrimaryKeyDefinition implements \JsonSerializable
{
    /** @var list<string> */
    private array $attributes;

    /** @param list<DataDefinitionIdentifier|string> $attributes */
    public function __construct(array $attributes)
    {
        if ($attributes === [] || count($attributes) > 4) {
            throw new DataDefinitionException(
                DataDefinitionException::PRIMARY_KEY_INVALID,
                'Primary key definition is invalid.'
            );
        }

        $normalized = [];
        foreach ($attributes as $attribute) {
            $id = $attribute instanceof DataDefinitionIdentifier
                ? $attribute
                : new DataDefinitionIdentifier($attribute);
            if (isset($normalized[$id->value])) {
                throw new DataDefinitionException(
                    DataDefinitionException::PRIMARY_KEY_INVALID,
                    'Primary key attribute is duplicated.'
                );
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

    /** @return array{attributes:list<string>} */
    public function jsonSerialize(): array
    {
        return ['attributes' => $this->attributes];
    }
}

final readonly class UniqueConstraintDefinition implements \JsonSerializable
{
    /** @var list<string> */
    private array $attributes;

    /** @param list<DataDefinitionIdentifier|string> $attributes */
    public function __construct(public DataDefinitionIdentifier $identifier, array $attributes)
    {
        if ($attributes === [] || count($attributes) > 8) {
            throw new DataDefinitionException(
                DataDefinitionException::UNIQUE_POLICY_INVALID,
                'Unique constraint definition is invalid.'
            );
        }

        $normalized = [];
        foreach ($attributes as $attribute) {
            $id = $attribute instanceof DataDefinitionIdentifier
                ? $attribute
                : new DataDefinitionIdentifier($attribute);
            if (isset($normalized[$id->value])) {
                throw new DataDefinitionException(
                    DataDefinitionException::UNIQUE_POLICY_INVALID,
                    'Unique constraint attribute is duplicated.'
                );
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

    /** @return array{identifier:string,attributes:list<string>} */
    public function jsonSerialize(): array
    {
        return ['identifier' => $this->identifier->value, 'attributes' => $this->attributes];
    }
}

final readonly class ReferenceDefinition implements \JsonSerializable
{
    /** @var array<string,string> */
    private array $attributeMap;

    /** @param array<DataDefinitionIdentifier|string,DataDefinitionIdentifier|string> $attributeMap */
    public function __construct(
        public DataDefinitionIdentifier $identifier,
        public DataDefinitionIdentifier $targetEntity,
        array $attributeMap,
    ) {
        if ($attributeMap === [] || count($attributeMap) > 8) {
            throw new DataDefinitionException(
                DataDefinitionException::REFERENCE_INVALID,
                'Reference definition is invalid.'
            );
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
                throw new DataDefinitionException(
                    DataDefinitionException::REFERENCE_INVALID,
                    'Reference source attribute is duplicated.'
                );
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

final readonly class EntityDefinition implements \JsonSerializable
{
    /** @var list<AttributeDefinition> */
    private array $attributes;

    /** @var list<UniqueConstraintDefinition> */
    private array $uniqueConstraints;

    /** @var list<ReferenceDefinition> */
    private array $references;

    /**
     * @param list<AttributeDefinition> $attributes
     * @param list<UniqueConstraintDefinition> $uniqueConstraints
     * @param list<ReferenceDefinition> $references
     */
    public function __construct(
        public DataDefinitionIdentifier $identifier,
        public TenantScope $tenantScope,
        array $attributes,
        public PrimaryKeyDefinition $primaryKey,
        array $uniqueConstraints = [],
        array $references = [],
        public ?DataDefinitionIdentifier $tenantKey = null,
    ) {
        if ($attributes === []) {
            throw new DataDefinitionException(
                DataDefinitionException::MANIFEST_INVALID,
                'Entity definition requires attributes.'
            );
        }

        $attributeIndex = [];
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof AttributeDefinition) {
                throw new DataDefinitionException(
                    DataDefinitionException::MANIFEST_INVALID,
                    'Entity attribute is invalid.'
                );
            }
            $id = $attribute->identifier->value;
            if (isset($attributeIndex[$id])) {
                throw new DataDefinitionException(
                    DataDefinitionException::DUPLICATE_ATTRIBUTE,
                    'Entity attribute is duplicated.'
                );
            }
            $attributeIndex[$id] = true;
        }

        foreach ($uniqueConstraints as $unique) {
            if (!$unique instanceof UniqueConstraintDefinition) {
                throw new DataDefinitionException(
                    DataDefinitionException::MANIFEST_INVALID,
                    'Unique constraint is invalid.'
                );
            }
        }
        foreach ($references as $reference) {
            if (!$reference instanceof ReferenceDefinition) {
                throw new DataDefinitionException(
                    DataDefinitionException::MANIFEST_INVALID,
                    'Reference definition is invalid.'
                );
            }
        }

        $this->attributes = array_values($attributes);
        $this->uniqueConstraints = array_values($uniqueConstraints);
        $this->references = array_values($references);
    }

    /** @return list<AttributeDefinition> */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /** @return array<string,AttributeDefinition> */
    public function attributeIndex(): array
    {
        $index = [];
        foreach ($this->attributes as $attribute) {
            $index[$attribute->identifier->value] = $attribute;
        }
        return $index;
    }

    /** @return list<UniqueConstraintDefinition> */
    public function uniqueConstraints(): array
    {
        return $this->uniqueConstraints;
    }

    /** @return list<ReferenceDefinition> */
    public function references(): array
    {
        return $this->references;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'identifier' => $this->identifier->value,
            'tenant_scope' => $this->tenantScope->value,
            'tenant_key' => $this->tenantKey?->value,
            'attributes' => $this->attributes,
            'primary_key' => $this->primaryKey,
            'unique_constraints' => $this->uniqueConstraints,
            'references' => $this->references,
        ];
    }
}

final readonly class DataDefinitionManifest implements \JsonSerializable
{
    /** @var list<EntityDefinition> */
    private array $entities;

    /** @param list<EntityDefinition> $entities */
    public function __construct(array $entities)
    {
        if ($entities === []) {
            throw new DataDefinitionException(
                DataDefinitionException::MANIFEST_INVALID,
                'Data definition manifest is empty.'
            );
        }

        $index = [];
        foreach ($entities as $entity) {
            if (!$entity instanceof EntityDefinition) {
                throw new DataDefinitionException(
                    DataDefinitionException::MANIFEST_INVALID,
                    'Data definition manifest entity is invalid.'
                );
            }
            $id = $entity->identifier->value;
            if (isset($index[$id])) {
                throw new DataDefinitionException(
                    DataDefinitionException::DUPLICATE_ENTITY,
                    'Data definition entity is duplicated.'
                );
            }
            $index[$id] = true;
        }
        $this->entities = array_values($entities);
    }

    /** @return list<EntityDefinition> */
    public function entities(): array
    {
        return $this->entities;
    }

    /** @return array<string,EntityDefinition> */
    public function entityIndex(): array
    {
        $index = [];
        foreach ($this->entities as $entity) {
            $index[$entity->identifier->value] = $entity;
        }
        return $index;
    }

    /** @return array{entities:list<EntityDefinition>} */
    public function jsonSerialize(): array
    {
        return ['entities' => $this->entities];
    }
}

