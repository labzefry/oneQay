<?php

declare(strict_types=1);

namespace OneQay\PhysicalMapping;

use OneQay\DataDefinition\PortableScalarType;
use OneQay\DataDefinition\TenantScope;

final readonly class VendorCompatibilityReport implements \JsonSerializable
{
    /** @param list<string> $errorCodes @param list<string> $entityIdentifiers */
    private function __construct(
        public bool $isCompatible,
        public array $errorCodes,
        public array $entityIdentifiers,
        public string $vendor,
        public string $correlationId,
    ) {
        if ($this->correlationId === '') {
            throw new \InvalidArgumentException('Correlation ID is required.');
        }
    }

    /** @param list<string> $entityIdentifiers */
    public static function success(string $vendor, string $correlationId, array $entityIdentifiers): self
    {
        return new self(true, [], array_values($entityIdentifiers), $vendor, $correlationId);
    }

    /** @param list<string> $errorCodes @param list<string> $entityIdentifiers */
    public static function failure(string $vendor, string $correlationId, array $errorCodes, array $entityIdentifiers): self
    {
        $codes = array_values(array_unique($errorCodes));
        sort($codes);
        return new self(false, $codes, array_values($entityIdentifiers), $vendor, $correlationId);
    }

    /** @return array{compatible:bool,error_codes:list<string>,entity_identifiers:list<string>,vendor:string,correlation_id:string} */
    public function jsonSerialize(): array
    {
        return [
            'compatible' => $this->isCompatible,
            'error_codes' => $this->errorCodes,
            'entity_identifiers' => $this->entityIdentifiers,
            'vendor' => $this->vendor,
            'correlation_id' => $this->correlationId,
        ];
    }
}

final class VendorCompatibilityValidator
{
    private const MAX_INDEX_BYTES = 3072;

    public function validate(PhysicalMappingManifest $manifest, string $correlationId): VendorCompatibilityReport
    {
        if ($correlationId === '') {
            throw new \InvalidArgumentException('Correlation ID is required.');
        }

        $errors = [];
        $entities = $manifest->entityIndex();
        foreach ($manifest->entities() as $entity) {
            $attributes = $entity->attributeIndex();
            $this->validateTenantPolicy($entity, $attributes, $errors);
            $this->validateIndex($entity->primaryIndex, $attributes, $errors);
            foreach ($entity->uniqueIndexes() as $index) {
                $this->validateIndex($index, $attributes, $errors);
                if ($entity->tenantScope === TenantScope::TENANT_SCOPED
                    && $entity->tenantKey !== null
                    && !in_array($entity->tenantKey->value, $index->attributes(), true)) {
                    $errors[] = PhysicalMappingException::TENANT_KEY_REQUIRED;
                }
            }
            $this->validateReferences($entity, $attributes, $entities, $errors);
        }

        $identifiers = array_keys($entities);
        sort($identifiers);

        return $errors === []
            ? VendorCompatibilityReport::success($manifest->vendor->value, $correlationId, $identifiers)
            : VendorCompatibilityReport::failure($manifest->vendor->value, $correlationId, $errors, $identifiers);
    }

    /** @param array<string,PhysicalAttributeMapping> $attributes @param list<string> $errors */
    private function validateTenantPolicy(PhysicalEntityMapping $entity, array $attributes, array &$errors): void
    {
        if ($entity->tenantScope === TenantScope::TENANT_SCOPED) {
            if ($entity->tenantKey === null || !isset($attributes[$entity->tenantKey->value])) {
                $errors[] = PhysicalMappingException::TENANT_KEY_REQUIRED;
                return;
            }

            $tenant = $attributes[$entity->tenantKey->value];
            if (!in_array($tenant->scalarMapping->logicalType->value, [PortableScalarType::UUID, PortableScalarType::STRING], true)
                || !in_array($entity->tenantKey->value, $entity->primaryIndex->attributes(), true)) {
                $errors[] = PhysicalMappingException::TENANT_KEY_REQUIRED;
            }
            return;
        }

        if ($entity->tenantKey !== null) {
            $errors[] = PhysicalMappingException::TENANT_KEY_REQUIRED;
        }
    }

    /** @param array<string,PhysicalAttributeMapping> $attributes @param list<string> $errors */
    private function validateIndex(PhysicalIndexMapping $index, array $attributes, array &$errors): void
    {
        $bytes = 0;
        foreach ($index->attributes() as $attributeId) {
            $attribute = $attributes[$attributeId] ?? null;
            if ($attribute === null) {
                $errors[] = PhysicalMappingException::INDEX_INVALID;
                continue;
            }
            try {
                $bytes += $attribute->scalarMapping->estimatedIndexBytes();
            } catch (PhysicalMappingException) {
                $errors[] = PhysicalMappingException::INDEX_INVALID;
            }
        }
        if ($bytes > self::MAX_INDEX_BYTES) {
            $errors[] = PhysicalMappingException::INDEX_BUDGET_EXCEEDED;
        }
    }

    /**
     * @param array<string,PhysicalAttributeMapping> $sourceAttributes
     * @param array<string,PhysicalEntityMapping> $entities
     * @param list<string> $errors
     */
    private function validateReferences(
        PhysicalEntityMapping $source,
        array $sourceAttributes,
        array $entities,
        array &$errors,
    ): void {
        foreach ($source->references() as $reference) {
            $target = $entities[$reference->targetEntity->value] ?? null;
            if ($target === null) {
                $errors[] = PhysicalMappingException::FOREIGN_KEY_INCOMPATIBLE;
                continue;
            }

            $targetAttributes = $target->attributeIndex();
            $mappedTargets = [];
            $map = $reference->attributeMap();
            foreach ($map as $sourceId => $targetId) {
                $sourceAttribute = $sourceAttributes[$sourceId] ?? null;
                $targetAttribute = $targetAttributes[$targetId] ?? null;
                if ($sourceAttribute === null || $targetAttribute === null
                    || !$sourceAttribute->scalarMapping->isForeignKeyCompatibleWith($targetAttribute->scalarMapping)) {
                    $errors[] = PhysicalMappingException::FOREIGN_KEY_INCOMPATIBLE;
                    continue;
                }
                $mappedTargets[] = $targetId;
            }

            if (!$this->matchesTargetIndex($target, $mappedTargets)) {
                $errors[] = PhysicalMappingException::FOREIGN_KEY_INCOMPATIBLE;
            }

            if ($target->tenantScope === TenantScope::TENANT_SCOPED) {
                if ($source->tenantScope !== TenantScope::TENANT_SCOPED
                    || $source->tenantKey === null
                    || $target->tenantKey === null
                    || ($map[$source->tenantKey->value] ?? null) !== $target->tenantKey->value) {
                    $errors[] = PhysicalMappingException::TENANT_KEY_REQUIRED;
                }
            }
        }
    }

    /** @param list<string> $mappedTargets */
    private function matchesTargetIndex(PhysicalEntityMapping $target, array $mappedTargets): bool
    {
        $normalized = array_values(array_unique($mappedTargets));
        sort($normalized);

        $primary = $target->primaryIndex->attributes();
        sort($primary);
        if ($normalized === $primary) {
            return true;
        }

        foreach ($target->uniqueIndexes() as $index) {
            $candidate = $index->attributes();
            sort($candidate);
            if ($normalized === $candidate) {
                return true;
            }
        }
        return false;
    }
}
