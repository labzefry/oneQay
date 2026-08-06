<?php

declare(strict_types=1);

namespace OneQay\SchemaPlanning;

final class SchemaPlanningException extends \RuntimeException
{
    public const CORRELATION_ID_INVALID = 'SCHEMA_PLANNING_CORRELATION_ID_INVALID';
    public const FINGERPRINT_INVALID = 'SCHEMA_PLANNING_FINGERPRINT_INVALID';
    public const CHANGE_IDENTIFIER_INVALID = 'SCHEMA_PLANNING_CHANGE_IDENTIFIER_INVALID';
    public const SAFE_IDENTIFIER_INVALID = 'SCHEMA_PLANNING_SAFE_IDENTIFIER_INVALID';
    public const MANIFEST_INCOMPATIBLE = 'SCHEMA_PLANNING_MANIFEST_INCOMPATIBLE';
    public const PLAN_INVALID = 'SCHEMA_PLANNING_PLAN_INVALID';

    /** @param list<string> $contextCodes */
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly array $contextCodes = [],
    ) {
        parent::__construct($message);
    }
}

enum PlanDisposition: string
{
    case NO_CHANGES = 'NO_CHANGES';
    case REVIEW_REQUIRED = 'REVIEW_REQUIRED';
    case BLOCKED = 'BLOCKED';
}

enum ChangeRisk: string
{
    case REVIEW_REQUIRED = 'REVIEW_REQUIRED';
    case BLOCKED = 'BLOCKED';
}

enum SchemaChangeKind: string
{
    case VENDOR_CHANGED = 'VENDOR_CHANGED';
    case ENTITY_CREATED = 'ENTITY_CREATED';
    case ENTITY_REMOVED = 'ENTITY_REMOVED';
    case ENTITY_PHYSICAL_IDENTIFIER_CHANGED = 'ENTITY_PHYSICAL_IDENTIFIER_CHANGED';
    case ATTRIBUTE_ADDED = 'ATTRIBUTE_ADDED';
    case ATTRIBUTE_REMOVED = 'ATTRIBUTE_REMOVED';
    case ATTRIBUTE_PHYSICAL_MAPPING_CHANGED = 'ATTRIBUTE_PHYSICAL_MAPPING_CHANGED';
    case ATTRIBUTE_SCALAR_MAPPING_CHANGED = 'ATTRIBUTE_SCALAR_MAPPING_CHANGED';
    case PRIMARY_INDEX_CHANGED = 'PRIMARY_INDEX_CHANGED';
    case UNIQUE_INDEX_ADDED = 'UNIQUE_INDEX_ADDED';
    case UNIQUE_INDEX_REMOVED = 'UNIQUE_INDEX_REMOVED';
    case UNIQUE_INDEX_CHANGED = 'UNIQUE_INDEX_CHANGED';
    case REFERENCE_ADDED = 'REFERENCE_ADDED';
    case REFERENCE_REMOVED = 'REFERENCE_REMOVED';
    case REFERENCE_CHANGED = 'REFERENCE_CHANGED';
    case TENANT_SCOPE_CHANGED = 'TENANT_SCOPE_CHANGED';
    case TENANT_KEY_CHANGED = 'TENANT_KEY_CHANGED';
}

final readonly class CorrelationId implements \JsonSerializable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === ''
            || strlen($normalized) > 128
            || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $normalized) !== 1) {
            throw new SchemaPlanningException(
                SchemaPlanningException::CORRELATION_ID_INVALID,
                'Schema planning correlation ID is invalid.'
            );
        }
        $this->value = $normalized;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

final readonly class ManifestFingerprint implements \JsonSerializable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (preg_match('/^[a-f0-9]{64}$/D', $normalized) !== 1) {
            throw new SchemaPlanningException(
                SchemaPlanningException::FINGERPRINT_INVALID,
                'Schema planning fingerprint is invalid.'
            );
        }
        $this->value = $normalized;
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

final readonly class StableChangeIdentifier implements \JsonSerializable
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if (preg_match('/^[a-f0-9]{64}$/D', $normalized) !== 1) {
            throw new SchemaPlanningException(
                SchemaPlanningException::CHANGE_IDENTIFIER_INVALID,
                'Stable schema change identifier is invalid.'
            );
        }
        $this->value = $normalized;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
