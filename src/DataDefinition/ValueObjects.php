<?php

declare(strict_types=1);

namespace OneQay\DataDefinition;

final class DataDefinitionException extends \RuntimeException
{
    public const IDENTIFIER_INVALID = 'DATA_DEFINITION_IDENTIFIER_INVALID';
    public const RESERVED_NAMESPACE = 'DATA_DEFINITION_RESERVED_NAMESPACE';
    public const SCALAR_TYPE_INVALID = 'DATA_DEFINITION_SCALAR_TYPE_INVALID';
    public const CONSTRAINT_INVALID = 'DATA_DEFINITION_CONSTRAINT_INVALID';
    public const DEFAULT_INVALID = 'DATA_DEFINITION_DEFAULT_INVALID';
    public const PRIMARY_KEY_INVALID = 'DATA_DEFINITION_PRIMARY_KEY_INVALID';
    public const UNIQUE_POLICY_INVALID = 'DATA_DEFINITION_UNIQUE_POLICY_INVALID';
    public const REFERENCE_INVALID = 'DATA_DEFINITION_REFERENCE_INVALID';
    public const TENANT_KEY_REQUIRED = 'DATA_DEFINITION_TENANT_KEY_REQUIRED';
    public const TENANT_KEY_INVALID = 'DATA_DEFINITION_TENANT_KEY_INVALID';
    public const CROSS_TENANT_REFERENCE_DENIED = 'DATA_DEFINITION_CROSS_TENANT_REFERENCE_DENIED';
    public const DUPLICATE_ENTITY = 'DATA_DEFINITION_DUPLICATE_ENTITY';
    public const DUPLICATE_ATTRIBUTE = 'DATA_DEFINITION_DUPLICATE_ATTRIBUTE';
    public const MANIFEST_INVALID = 'DATA_DEFINITION_MANIFEST_INVALID';
    public const NOT_READY = 'DATA_DEFINITION_NOT_READY';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

final readonly class DataDefinitionIdentifier implements \JsonSerializable
{
    private const RESERVED_PREFIXES = [
        'SYS',
        'SQL',
        'MYSQL',
        'PG',
        'INFORMATION_SCHEMA',
        'ONEQAY_INTERNAL',
        'INTERNAL',
    ];

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));

        if ($normalized === ''
            || strlen($normalized) > 64
            || preg_match('/^[A-Z][A-Z0-9]*(?:_[A-Z0-9]+)*$/D', $normalized) !== 1) {
            throw new DataDefinitionException(
                DataDefinitionException::IDENTIFIER_INVALID,
                'Data definition identifier is invalid.'
            );
        }

        foreach (self::RESERVED_PREFIXES as $prefix) {
            if ($normalized === $prefix || str_starts_with($normalized, $prefix . '_')) {
                throw new DataDefinitionException(
                    DataDefinitionException::RESERVED_NAMESPACE,
                    'Data definition identifier uses a reserved namespace.'
                );
            }
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

final readonly class PortableScalarType implements \JsonSerializable
{
    public const STRING = 'STRING';
    public const INTEGER = 'INTEGER';
    public const DECIMAL = 'DECIMAL';
    public const BOOLEAN = 'BOOLEAN';
    public const UUID = 'UUID';
    public const DATE = 'DATE';
    public const DATETIME = 'DATETIME';
    public const JSON = 'JSON';

    private const ALLOWED = [
        self::STRING,
        self::INTEGER,
        self::DECIMAL,
        self::BOOLEAN,
        self::UUID,
        self::DATE,
        self::DATETIME,
        self::JSON,
    ];

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new DataDefinitionException(
                DataDefinitionException::SCALAR_TYPE_INVALID,
                'Portable scalar type is invalid.'
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

enum NullabilityPolicy: string
{
    case REQUIRED = 'REQUIRED';
    case NULLABLE = 'NULLABLE';
}

enum TenantScope: string
{
    case GLOBAL = 'GLOBAL';
    case TENANT_SCOPED = 'TENANT_SCOPED';
}

enum DefaultValuePolicy: string
{
    case NONE = 'NONE';
    case NULL_VALUE = 'NULL_VALUE';
    case LITERAL_FINGERPRINT = 'LITERAL_FINGERPRINT';
    case GENERATED_IDENTIFIER = 'GENERATED_IDENTIFIER';
}

final readonly class ValueConstraint implements \JsonSerializable
{
    public function __construct(
        public PortableScalarType $type,
        public ?int $length = null,
        public ?int $precision = null,
        public ?int $scale = null,
    ) {
        if ($this->type->value === PortableScalarType::STRING) {
            if ($this->length === null || $this->length < 1 || $this->length > 4096
                || $this->precision !== null || $this->scale !== null) {
                throw new DataDefinitionException(
                    DataDefinitionException::CONSTRAINT_INVALID,
                    'String constraint is invalid.'
                );
            }
            return;
        }

        if ($this->type->value === PortableScalarType::DECIMAL) {
            if ($this->precision === null || $this->precision < 1 || $this->precision > 38
                || $this->scale === null || $this->scale < 0 || $this->scale > $this->precision
                || $this->length !== null) {
                throw new DataDefinitionException(
                    DataDefinitionException::CONSTRAINT_INVALID,
                    'Decimal constraint is invalid.'
                );
            }
            return;
        }

        if ($this->length !== null || $this->precision !== null || $this->scale !== null) {
            throw new DataDefinitionException(
                DataDefinitionException::CONSTRAINT_INVALID,
                'Scalar constraint is invalid for this type.'
            );
        }
    }

    /** @return array{type:string,length:?int,precision:?int,scale:?int} */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->value,
            'length' => $this->length,
            'precision' => $this->precision,
            'scale' => $this->scale,
        ];
    }
}

final readonly class DefaultValueDefinition implements \JsonSerializable
{
    private function __construct(
        public DefaultValuePolicy $policy,
        public ?string $fingerprint = null,
        public ?DataDefinitionIdentifier $generatedIdentifier = null,
    ) {
    }

    public static function none(): self
    {
        return new self(DefaultValuePolicy::NONE);
    }

    public static function nullValue(): self
    {
        return new self(DefaultValuePolicy::NULL_VALUE);
    }

    public static function literal(string|int|float|bool $value): self
    {
        $canonical = get_debug_type($value) . ':' . json_encode($value, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
        return new self(DefaultValuePolicy::LITERAL_FINGERPRINT, hash('sha256', $canonical));
    }

    public static function generated(DataDefinitionIdentifier|string $identifier): self
    {
        $id = $identifier instanceof DataDefinitionIdentifier
            ? $identifier
            : new DataDefinitionIdentifier($identifier);

        return new self(DefaultValuePolicy::GENERATED_IDENTIFIER, null, $id);
    }

    /** @return array{policy:string,fingerprint:?string,generated_identifier:?string} */
    public function jsonSerialize(): array
    {
        return [
            'policy' => $this->policy->value,
            'fingerprint' => $this->fingerprint,
            'generated_identifier' => $this->generatedIdentifier?->value,
        ];
    }
}

