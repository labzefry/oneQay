<?php

declare(strict_types=1);



namespace OneQay\DataDefinition;

final readonly class DataDefinitionValidationReport implements \JsonSerializable
{
    /**
     * @param list<string> $errorCodes
     * @param list<string> $entityIdentifiers
     */
    private function __construct(
        public bool $isValid,
        public array $errorCodes,
        public array $entityIdentifiers,
        public string $correlationId,
    ) {
        if ($this->correlationId === '') {
            throw new \InvalidArgumentException('Correlation ID is required.');
        }
    }

    /** @param list<string> $entityIdentifiers */
    public static function success(string $correlationId, array $entityIdentifiers): self
    {
        return new self(true, [], array_values($entityIdentifiers), $correlationId);
    }

    /** @param list<string> $errorCodes @param list<string> $entityIdentifiers */
    public static function failure(string $correlationId, array $errorCodes, array $entityIdentifiers): self
    {
        $normalized = array_values(array_unique($errorCodes));
        sort($normalized);
        return new self(false, $normalized, array_values($entityIdentifiers), $correlationId);
    }

    /** @return array{valid:bool,error_codes:list<string>,entity_identifiers:list<string>,correlation_id:string} */
    public function jsonSerialize(): array
    {
        return [
            'valid' => $this->isValid,
            'error_codes' => $this->errorCodes,
            'entity_identifiers' => $this->entityIdentifiers,
            'correlation_id' => $this->correlationId,
        ];
    }
}

final class DataDefinitionPolicyValidator
{
    public function validate(DataDefinitionManifest $manifest, string $correlationId): DataDefinitionValidationReport
    {
        if ($correlationId === '') {
            throw new \InvalidArgumentException('Correlation ID is required.');
        }

        $errors = [];
        $entities = $manifest->entityIndex();

        foreach ($manifest->entities() as $entity) {
            $attributes = $entity->attributeIndex();
            $this->validateTenantPolicy($entity, $attributes, $errors);
            $this->validatePrimaryKey($entity, $attributes, $errors);
            $this->validateUniqueConstraints($entity, $attributes, $errors);
            $this->validateReferences($entity, $attributes, $entities, $errors);
        }

        $identifiers = array_keys($entities);
        sort($identifiers);

        return $errors === []
            ? DataDefinitionValidationReport::success($correlationId, $identifiers)
            : DataDefinitionValidationReport::failure($correlationId, $errors, $identifiers);
    }

    /** @param array<string,AttributeDefinition> $attributes @param list<string> $errors */
    private function validateTenantPolicy(EntityDefinition $entity, array $attributes, array &$errors): void
    {
        if ($entity->tenantScope === TenantScope::TENANT_SCOPED) {
            if ($entity->tenantKey === null) {
                $errors[] = DataDefinitionException::TENANT_KEY_REQUIRED;
                return;
            }

            $tenantKey = $attributes[$entity->tenantKey->value] ?? null;
            if ($tenantKey === null
                || $tenantKey->nullability !== NullabilityPolicy::REQUIRED
                || !in_array($tenantKey->constraint->type->value, [PortableScalarType::UUID, PortableScalarType::STRING], true)
                || $tenantKey->defaultValue->policy === DefaultValuePolicy::NULL_VALUE
                || $tenantKey->defaultValue->policy === DefaultValuePolicy::LITERAL_FINGERPRINT) {
                $errors[] = DataDefinitionException::TENANT_KEY_INVALID;
            }
            return;
        }

        if ($entity->tenantKey !== null) {
            $errors[] = DataDefinitionException::TENANT_KEY_INVALID;
        }
    }

    /** @param array<string,AttributeDefinition> $attributes @param list<string> $errors */
    private function validatePrimaryKey(EntityDefinition $entity, array $attributes, array &$errors): void
    {
        $primaryAttributes = $entity->primaryKey->attributes();
        foreach ($primaryAttributes as $attributeId) {
            $attribute = $attributes[$attributeId] ?? null;
            if ($attribute === null || $attribute->nullability !== NullabilityPolicy::REQUIRED) {
                $errors[] = DataDefinitionException::PRIMARY_KEY_INVALID;
            }
        }

        if ($entity->tenantScope === TenantScope::TENANT_SCOPED
            && $entity->tenantKey !== null
            && !in_array($entity->tenantKey->value, $primaryAttributes, true)) {
            $errors[] = DataDefinitionException::PRIMARY_KEY_INVALID;
        }
    }

    /** @param array<string,AttributeDefinition> $attributes @param list<string> $errors */
    private function validateUniqueConstraints(EntityDefinition $entity, array $attributes, array &$errors): void
    {
        $constraintIds = [];
        foreach ($entity->uniqueConstraints() as $unique) {
            if (isset($constraintIds[$unique->identifier->value])) {
                $errors[] = DataDefinitionException::UNIQUE_POLICY_INVALID;
            }
            $constraintIds[$unique->identifier->value] = true;

            foreach ($unique->attributes() as $attributeId) {
                if (!isset($attributes[$attributeId])) {
                    $errors[] = DataDefinitionException::UNIQUE_POLICY_INVALID;
                }
            }

            if ($entity->tenantScope === TenantScope::TENANT_SCOPED
                && $entity->tenantKey !== null
                && !in_array($entity->tenantKey->value, $unique->attributes(), true)) {
                $errors[] = DataDefinitionException::UNIQUE_POLICY_INVALID;
            }
        }
    }

    /**
     * @param array<string,AttributeDefinition> $sourceAttributes
     * @param array<string,EntityDefinition> $entities
     * @param list<string> $errors
     */
    private function validateReferences(
        EntityDefinition $source,
        array $sourceAttributes,
        array $entities,
        array &$errors,
    ): void {
        $referenceIds = [];
        foreach ($source->references() as $reference) {
            if (isset($referenceIds[$reference->identifier->value])) {
                $errors[] = DataDefinitionException::REFERENCE_INVALID;
            }
            $referenceIds[$reference->identifier->value] = true;

            $target = $entities[$reference->targetEntity->value] ?? null;
            if ($target === null) {
                $errors[] = DataDefinitionException::REFERENCE_INVALID;
                continue;
            }

            $targetAttributes = $target->attributeIndex();
            $mappedTargets = [];
            $map = $reference->attributeMap();
            foreach ($map as $sourceId => $targetId) {
                $sourceAttribute = $sourceAttributes[$sourceId] ?? null;
                $targetAttribute = $targetAttributes[$targetId] ?? null;
                if ($sourceAttribute === null || $targetAttribute === null
                    || !$sourceAttribute->constraint->type->equals($targetAttribute->constraint->type)) {
                    $errors[] = DataDefinitionException::REFERENCE_INVALID;
                    continue;
                }
                $mappedTargets[] = $targetId;
            }

            if (!$this->matchesTargetKey($target, $mappedTargets)) {
                $errors[] = DataDefinitionException::REFERENCE_INVALID;
            }

            if ($target->tenantScope === TenantScope::TENANT_SCOPED) {
                if ($source->tenantScope !== TenantScope::TENANT_SCOPED
                    || $source->tenantKey === null
                    || $target->tenantKey === null
                    || ($map[$source->tenantKey->value] ?? null) !== $target->tenantKey->value) {
                    $errors[] = DataDefinitionException::CROSS_TENANT_REFERENCE_DENIED;
                }
            }
        }
    }

    /** @param list<string> $mappedTargets */
    private function matchesTargetKey(EntityDefinition $target, array $mappedTargets): bool
    {
        $normalized = array_values(array_unique($mappedTargets));
        sort($normalized);

        $primary = $target->primaryKey->attributes();
        sort($primary);
        if ($normalized === $primary) {
            return true;
        }

        foreach ($target->uniqueConstraints() as $unique) {
            $candidate = $unique->attributes();
            sort($candidate);
            if ($normalized === $candidate) {
                return true;
            }
        }
        return false;
    }
}
