<?php

declare(strict_types=1);

namespace OneQay\Portability;

final class PortabilityContractException extends \RuntimeException
{
    public const SOURCE_UNIT_INVALID = 'PORTABILITY_SOURCE_UNIT_INVALID';
    public const PROFILE_DIRECTION_UNSUPPORTED = 'PORTABILITY_PROFILE_DIRECTION_UNSUPPORTED';
    public const VENDOR_DEPENDENCY_IN_LOGICAL_BUSINESS = 'PORTABILITY_VENDOR_DEPENDENCY_IN_LOGICAL_BUSINESS';
    public const RAW_SQL_IN_LOGICAL_BUSINESS = 'PORTABILITY_RAW_SQL_IN_LOGICAL_BUSINESS';
    public const NO_LOGICAL_BUSINESS_EVIDENCE = 'PORTABILITY_NO_LOGICAL_BUSINESS_EVIDENCE';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

enum PortabilityLayer: string
{
    case LOGICAL_BUSINESS = 'LOGICAL_BUSINESS';
    case INFRASTRUCTURE = 'INFRASTRUCTURE';
}

final readonly class EngineProfileDirection implements \JsonSerializable
{
    public const MARIADB = 'MARIADB';
    public const MYSQL = 'MYSQL';
    public const POSTGRESQL = 'POSTGRESQL';

    private const KNOWN = [
        self::MARIADB,
        self::MYSQL,
        self::POSTGRESQL,
    ];

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));
        if (!in_array($normalized, self::KNOWN, true)) {
            throw new PortabilityContractException(
                PortabilityContractException::PROFILE_DIRECTION_UNSUPPORTED,
                'Relational engine profile direction is unsupported.'
            );
        }

        $this->value = $normalized;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}

final readonly class PortabilitySourceUnit
{
    public function __construct(
        public PortabilityLayer $layer,
        public string $path,
        public string $source,
    ) {
        if ($this->path === '' || $this->source === '') {
            throw new PortabilityContractException(
                PortabilityContractException::SOURCE_UNIT_INVALID,
                'Portability source unit is invalid.'
            );
        }
    }
}

final readonly class PortabilityContractReport implements \JsonSerializable
{
    /**
     * @param list<string> $errorCodes
     * @param list<string> $inspectedPaths
     */
    public function __construct(
        public bool $isConformant,
        public array $errorCodes,
        public array $inspectedPaths,
        public int $logicalBusinessFiles,
        public int $infrastructureFiles,
        public string $correlationId,
    ) {
    }

    /** @return array<string, bool|int|string|list<string>> */
    public function jsonSerialize(): array
    {
        return [
            'is_conformant' => $this->isConformant,
            'error_codes' => $this->errorCodes,
            'inspected_paths' => $this->inspectedPaths,
            'logical_business_files' => $this->logicalBusinessFiles,
            'infrastructure_files' => $this->infrastructureFiles,
            'correlation_id' => $this->correlationId,
        ];
    }
}

final class DatabasePortabilityContract
{
    private const CONCRETE_VENDOR_DEPENDENCY_PATTERN = '/(?:\bpdo_(?:mysql|pgsql)\b|\bmysqli\b|\bmysql:|\bpgsql:|\bPdoMySql\b|\bPdoPgSql\b|\b(?:MariaDb|MySql|PostgreSql)(?:Adapter|Connector|Connection|Repository)\b|(?:===|!==|==|!=)\s*[\'\"](?:mysql|mariadb|postgresql|postgres)[\'\"]|[\'\"](?:mysql|mariadb|postgresql|postgres)[\'\"]\s*(?:===|!==|==|!=))/i';
    private const RAW_SQL_PATTERN = '/\b(?:CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE|INSERT\s+INTO|DELETE\s+FROM|SELECT\s+.+?\s+FROM|UPDATE\s+.+?\s+SET)\b/is';

    /**
     * @param list<PortabilitySourceUnit> $units
     */
    public function evaluate(array $units, string $correlationId): PortabilityContractReport
    {
        if ($correlationId === '') {
            throw new \InvalidArgumentException('Correlation ID is required.');
        }

        $errorCodes = [];
        $inspectedPaths = [];
        $logicalBusinessFiles = 0;
        $infrastructureFiles = 0;

        foreach ($units as $unit) {
            if (!$unit instanceof PortabilitySourceUnit) {
                throw new \InvalidArgumentException('Portability source units are required.');
            }

            $inspectedPaths[] = $unit->path;

            if ($unit->layer === PortabilityLayer::INFRASTRUCTURE) {
                $infrastructureFiles++;
                continue;
            }

            $logicalBusinessFiles++;

            if (preg_match(self::CONCRETE_VENDOR_DEPENDENCY_PATTERN, $unit->source) === 1) {
                $errorCodes[] = PortabilityContractException::VENDOR_DEPENDENCY_IN_LOGICAL_BUSINESS;
            }

            if (preg_match(self::RAW_SQL_PATTERN, $unit->source) === 1) {
                $errorCodes[] = PortabilityContractException::RAW_SQL_IN_LOGICAL_BUSINESS;
            }
        }

        if ($logicalBusinessFiles === 0) {
            $errorCodes[] = PortabilityContractException::NO_LOGICAL_BUSINESS_EVIDENCE;
        }

        $errorCodes = array_values(array_unique($errorCodes));
        sort($inspectedPaths);

        return new PortabilityContractReport(
            $errorCodes === [],
            $errorCodes,
            $inspectedPaths,
            $logicalBusinessFiles,
            $infrastructureFiles,
            $correlationId,
        );
    }
}
