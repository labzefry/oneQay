<?php

declare(strict_types=1);

namespace OneQay\PhysicalMapping;

use OneQay\DataDefinition\PortableScalarType;

final class PhysicalMappingException extends \RuntimeException
{
    public const IDENTIFIER_INVALID = 'PHYSICAL_MAPPING_IDENTIFIER_INVALID';
    public const RESERVED_NAMESPACE = 'PHYSICAL_MAPPING_RESERVED_NAMESPACE';
    public const VENDOR_UNSUPPORTED = 'PHYSICAL_MAPPING_VENDOR_UNSUPPORTED';
    public const SCALAR_UNSUPPORTED = 'PHYSICAL_MAPPING_SCALAR_UNSUPPORTED';
    public const CHARSET_INVALID = 'PHYSICAL_MAPPING_CHARSET_INVALID';
    public const COLLATION_INVALID = 'PHYSICAL_MAPPING_COLLATION_INVALID';
    public const LENGTH_INVALID = 'PHYSICAL_MAPPING_LENGTH_INVALID';
    public const PRECISION_INVALID = 'PHYSICAL_MAPPING_PRECISION_INVALID';
    public const INDEX_INVALID = 'PHYSICAL_MAPPING_INDEX_INVALID';
    public const INDEX_BUDGET_EXCEEDED = 'PHYSICAL_MAPPING_INDEX_BUDGET_EXCEEDED';
    public const FOREIGN_KEY_INCOMPATIBLE = 'PHYSICAL_MAPPING_FOREIGN_KEY_INCOMPATIBLE';
    public const TENANT_KEY_REQUIRED = 'PHYSICAL_MAPPING_TENANT_KEY_REQUIRED';
    public const DUPLICATE_ENTITY = 'PHYSICAL_MAPPING_DUPLICATE_ENTITY';
    public const DUPLICATE_ATTRIBUTE = 'PHYSICAL_MAPPING_DUPLICATE_ATTRIBUTE';
    public const MANIFEST_INVALID = 'PHYSICAL_MAPPING_MANIFEST_INVALID';
    public const NOT_READY = 'PHYSICAL_MAPPING_NOT_READY';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

final readonly class PhysicalIdentifier implements \JsonSerializable
{
    private const RESERVED_PREFIXES = [
        'mysql',
        'sys',
        'information_schema',
        'performance_schema',
        'oneqay_internal',
        'internal',
    ];

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if ($normalized === ''
            || strlen($normalized) > 64
            || preg_match('/^[a-z][a-z0-9]*(?:_[a-z0-9]+)*$/D', $normalized) !== 1) {
            throw new PhysicalMappingException(
                PhysicalMappingException::IDENTIFIER_INVALID,
                'Physical identifier is invalid.'
            );
        }

        foreach (self::RESERVED_PREFIXES as $prefix) {
            if ($normalized === $prefix || str_starts_with($normalized, $prefix . '_')) {
                throw new PhysicalMappingException(
                    PhysicalMappingException::RESERVED_NAMESPACE,
                    'Physical identifier uses a reserved namespace.'
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

final readonly class VendorIdentifier implements \JsonSerializable
{
    public const MARIADB_11 = 'MARIADB_11';

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));
        if ($normalized !== self::MARIADB_11) {
            throw new PhysicalMappingException(
                PhysicalMappingException::VENDOR_UNSUPPORTED,
                'Physical mapping vendor is unsupported.'
            );
        }
        $this->value = $normalized;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

final readonly class CharsetPolicy implements \JsonSerializable
{
    public const UTF8MB4 = 'UTF8MB4';

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));
        if ($normalized !== self::UTF8MB4) {
            throw new PhysicalMappingException(
                PhysicalMappingException::CHARSET_INVALID,
                'Physical mapping charset is invalid.'
            );
        }
        $this->value = $normalized;
    }

    public function bytesPerCharacter(): int
    {
        return 4;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

final readonly class CollationPolicy implements \JsonSerializable
{
    public const UNICODE_CI = 'UTF8MB4_UNICODE_CI';
    public const BINARY = 'UTF8MB4_BINARY';

    private const ALLOWED = [self::UNICODE_CI, self::BINARY];

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new PhysicalMappingException(
                PhysicalMappingException::COLLATION_INVALID,
                'Physical mapping collation is invalid.'
            );
        }
        $this->value = $normalized;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

final readonly class PhysicalTypeIdentifier implements \JsonSerializable
{
    public const VARCHAR = 'VARCHAR';
    public const BIGINT_SIGNED = 'BIGINT_SIGNED';
    public const DECIMAL = 'DECIMAL';
    public const TINYINT_BOOLEAN = 'TINYINT_BOOLEAN';
    public const CHAR_UUID = 'CHAR_UUID';
    public const DATE = 'DATE';
    public const DATETIME = 'DATETIME';
    public const JSON_DOCUMENT = 'JSON_DOCUMENT';

    private const ALLOWED = [
        self::VARCHAR,
        self::BIGINT_SIGNED,
        self::DECIMAL,
        self::TINYINT_BOOLEAN,
        self::CHAR_UUID,
        self::DATE,
        self::DATETIME,
        self::JSON_DOCUMENT,
    ];

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));
        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new PhysicalMappingException(
                PhysicalMappingException::SCALAR_UNSUPPORTED,
                'Physical type is unsupported.'
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

enum IndexKind: string
{
    case PRIMARY = 'PRIMARY';
    case UNIQUE = 'UNIQUE';
}

enum ForeignKeyCompatibility: string
{
    case COMPATIBLE = 'COMPATIBLE';
    case INCOMPATIBLE = 'INCOMPATIBLE';
}

final readonly class PhysicalScalarMapping implements \JsonSerializable
{
    public function __construct(
        public PortableScalarType $logicalType,
        public PhysicalTypeIdentifier $physicalType,
        public ?int $length = null,
        public ?int $precision = null,
        public ?int $scale = null,
        public ?CharsetPolicy $charset = null,
        public ?CollationPolicy $collation = null,
    ) {
        $this->assertCompatible();
    }

    public function estimatedIndexBytes(): int
    {
        return match ($this->physicalType->value) {
            PhysicalTypeIdentifier::VARCHAR => (int) $this->length * (int) $this->charset?->bytesPerCharacter(),
            PhysicalTypeIdentifier::CHAR_UUID => 36 * (int) $this->charset?->bytesPerCharacter(),
            PhysicalTypeIdentifier::BIGINT_SIGNED => 8,
            PhysicalTypeIdentifier::DECIMAL => intdiv((int) $this->precision + 1, 2) + 1,
            PhysicalTypeIdentifier::TINYINT_BOOLEAN => 1,
            PhysicalTypeIdentifier::DATE => 3,
            PhysicalTypeIdentifier::DATETIME => 8,
            default => throw new PhysicalMappingException(
                PhysicalMappingException::INDEX_INVALID,
                'Physical type is not index-compatible.'
            ),
        };
    }

    public function foreignKeyCompatibilityWith(self $target): ForeignKeyCompatibility
    {
        $compatible = $this->logicalType->equals($target->logicalType)
            && $this->physicalType->equals($target->physicalType)
            && $this->length === $target->length
            && $this->precision === $target->precision
            && $this->scale === $target->scale
            && $this->charset?->value === $target->charset?->value
            && $this->collation?->value === $target->collation?->value;

        return $compatible ? ForeignKeyCompatibility::COMPATIBLE : ForeignKeyCompatibility::INCOMPATIBLE;
    }

    public function isForeignKeyCompatibleWith(self $target): bool
    {
        return $this->foreignKeyCompatibilityWith($target) === ForeignKeyCompatibility::COMPATIBLE;
    }

    /** @return array<string,mixed> */
    public function jsonSerialize(): array
    {
        return [
            'logical_type' => $this->logicalType->value,
            'physical_type' => $this->physicalType->value,
            'length' => $this->length,
            'precision' => $this->precision,
            'scale' => $this->scale,
            'charset' => $this->charset?->value,
            'collation' => $this->collation?->value,
        ];
    }

    private function assertCompatible(): void
    {
        $logical = $this->logicalType->value;
        $physical = $this->physicalType->value;

        if ($logical === PortableScalarType::STRING) {
            if ($physical !== PhysicalTypeIdentifier::VARCHAR) {
                $this->unsupported();
            }
            if ($this->length === null || $this->length < 1 || $this->length > 4096) {
                throw new PhysicalMappingException(PhysicalMappingException::LENGTH_INVALID, 'String mapping length is invalid.');
            }
            if ($this->charset?->value !== CharsetPolicy::UTF8MB4 || $this->collation === null) {
                throw new PhysicalMappingException(PhysicalMappingException::COLLATION_INVALID, 'String mapping charset or collation is invalid.');
            }
            if ($this->precision !== null || $this->scale !== null) {
                throw new PhysicalMappingException(PhysicalMappingException::PRECISION_INVALID, 'String mapping precision is invalid.');
            }
            return;
        }

        if ($logical === PortableScalarType::DECIMAL) {
            if ($physical !== PhysicalTypeIdentifier::DECIMAL) {
                $this->unsupported();
            }
            if ($this->precision === null || $this->precision < 1 || $this->precision > 38
                || $this->scale === null || $this->scale < 0 || $this->scale > $this->precision) {
                throw new PhysicalMappingException(PhysicalMappingException::PRECISION_INVALID, 'Decimal mapping precision or scale is invalid.');
            }
            $this->assertNoCharacterOptions();
            if ($this->length !== null) {
                throw new PhysicalMappingException(PhysicalMappingException::LENGTH_INVALID, 'Decimal mapping length is invalid.');
            }
            return;
        }

        $expected = match ($logical) {
            PortableScalarType::INTEGER => PhysicalTypeIdentifier::BIGINT_SIGNED,
            PortableScalarType::BOOLEAN => PhysicalTypeIdentifier::TINYINT_BOOLEAN,
            PortableScalarType::UUID => PhysicalTypeIdentifier::CHAR_UUID,
            PortableScalarType::DATE => PhysicalTypeIdentifier::DATE,
            PortableScalarType::DATETIME => PhysicalTypeIdentifier::DATETIME,
            PortableScalarType::JSON => PhysicalTypeIdentifier::JSON_DOCUMENT,
            default => null,
        };

        if ($expected === null || $physical !== $expected) {
            $this->unsupported();
        }

        if ($logical === PortableScalarType::UUID) {
            if ($this->length !== 36
                || $this->charset?->value !== CharsetPolicy::UTF8MB4
                || $this->collation?->value !== CollationPolicy::BINARY
                || $this->precision !== null
                || $this->scale !== null) {
                throw new PhysicalMappingException(PhysicalMappingException::LENGTH_INVALID, 'UUID mapping is invalid.');
            }
            return;
        }

        if ($this->length !== null || $this->precision !== null || $this->scale !== null) {
            throw new PhysicalMappingException(PhysicalMappingException::LENGTH_INVALID, 'Scalar mapping options are invalid.');
        }
        $this->assertNoCharacterOptions();
    }

    private function assertNoCharacterOptions(): void
    {
        if ($this->charset !== null || $this->collation !== null) {
            throw new PhysicalMappingException(PhysicalMappingException::COLLATION_INVALID, 'Character options are invalid for this mapping.');
        }
    }

    private function unsupported(): never
    {
        throw new PhysicalMappingException(
            PhysicalMappingException::SCALAR_UNSUPPORTED,
            'Logical-to-physical scalar mapping is unsupported.'
        );
    }
}
