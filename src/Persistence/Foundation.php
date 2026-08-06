<?php

declare(strict_types=1);

namespace OneQay\Persistence;

use OneQay\Configuration\ConfigurationException;
use OneQay\Configuration\ConfigurationKey;
use OneQay\Configuration\ConfigurationSource;
use OneQay\Configuration\SecretValue;

final class PersistenceException extends \RuntimeException
{
    public const CONFIGURATION_INVALID = 'PERSISTENCE_CONFIGURATION_INVALID';
    public const DRIVER_UNSUPPORTED = 'PERSISTENCE_DRIVER_UNSUPPORTED';
    public const CAPABILITY_UNKNOWN = 'PERSISTENCE_CAPABILITY_UNKNOWN';
    public const CAPABILITY_UNAVAILABLE = 'PERSISTENCE_CAPABILITY_UNAVAILABLE';
    public const CONNECTION_FAILED = 'PERSISTENCE_CONNECTION_FAILED';
    public const CONNECTION_UNAVAILABLE = 'PERSISTENCE_CONNECTION_UNAVAILABLE';
    public const NOT_READY = 'PERSISTENCE_NOT_READY';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

final readonly class PersistenceCapabilityIdentifier
{
    public const PDO_MYSQL_DRIVER = 'PDO_MYSQL_DRIVER';
    public const MARIADB_SERVER = 'MARIADB_SERVER';
    public const DATABASE_CREDENTIALS = 'DATABASE_CREDENTIALS';
    public const DATABASE_CONNECTION = 'DATABASE_CONNECTION';
    public const DATABASE_TLS = 'DATABASE_TLS';

    public string $value;

    public function __construct(string $value)
    {
        if ($value === ''
            || $value !== strtoupper($value)
            || preg_match('/^[A-Z][A-Z0-9]*(?:_[A-Z0-9]+)*$/', $value) !== 1
            || strlen($value) > 96) {
            throw new \InvalidArgumentException('Persistence capability identifier is invalid.');
        }

        $this->value = $value;
    }
}

enum PersistenceCapabilityStatus: string
{
    case AVAILABLE = 'AVAILABLE';
    case UNAVAILABLE = 'UNAVAILABLE';
    case UNKNOWN = 'UNKNOWN';
}

interface PersistenceCapabilityProvider
{
    public function status(PersistenceCapabilityIdentifier $identifier): PersistenceCapabilityStatus;
}

final readonly class SyntheticPersistenceCapabilityProvider implements PersistenceCapabilityProvider
{
    /** @param array<string, PersistenceCapabilityStatus> $statuses */
    public function __construct(private array $statuses)
    {
    }

    public function status(PersistenceCapabilityIdentifier $identifier): PersistenceCapabilityStatus
    {
        return $this->statuses[$identifier->value] ?? PersistenceCapabilityStatus::UNKNOWN;
    }
}

final readonly class NativePersistenceCapabilityProvider implements PersistenceCapabilityProvider
{
    /** @param array<string, bool|null> $verifiedInfrastructure */
    public function __construct(private array $verifiedInfrastructure = [])
    {
    }

    public function status(PersistenceCapabilityIdentifier $identifier): PersistenceCapabilityStatus
    {
        if ($identifier->value === PersistenceCapabilityIdentifier::PDO_MYSQL_DRIVER) {
            return extension_loaded('pdo_mysql')
                ? PersistenceCapabilityStatus::AVAILABLE
                : PersistenceCapabilityStatus::UNAVAILABLE;
        }

        $verified = $this->verifiedInfrastructure[$identifier->value] ?? null;

        return $verified === true
            ? PersistenceCapabilityStatus::AVAILABLE
            : ($verified === false ? PersistenceCapabilityStatus::UNAVAILABLE : PersistenceCapabilityStatus::UNKNOWN);
    }
}

final readonly class PersistenceCapabilityReport
{
    /**
     * @param list<string> $required
     * @param list<string> $available
     * @param list<string> $unavailable
     * @param list<string> $unknown
     */
    public function __construct(
        public array $required,
        public array $available,
        public array $unavailable,
        public array $unknown,
    ) {
    }

    public function isFoundationReady(): bool
    {
        return $this->unavailable === [] && $this->unknown === [];
    }
}

final class PersistenceCapabilityValidator
{
    /** @var list<string> */
    private const REQUIRED = [
        PersistenceCapabilityIdentifier::PDO_MYSQL_DRIVER,
        PersistenceCapabilityIdentifier::MARIADB_SERVER,
    ];

    public function validate(PersistenceCapabilityProvider $provider): PersistenceCapabilityReport
    {
        $available = [];
        $unavailable = [];
        $unknown = [];

        foreach (self::REQUIRED as $capability) {
            $status = $provider->status(new PersistenceCapabilityIdentifier($capability));

            match ($status) {
                PersistenceCapabilityStatus::AVAILABLE => $available[] = $capability,
                PersistenceCapabilityStatus::UNAVAILABLE => $unavailable[] = $capability,
                PersistenceCapabilityStatus::UNKNOWN => $unknown[] = $capability,
            };
        }

        return new PersistenceCapabilityReport(self::REQUIRED, $available, $unavailable, $unknown);
    }
}

final readonly class DatabaseDriverIdentifier
{
    public const PDO_MYSQL = 'PDO_MYSQL';

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));

        if ($normalized !== self::PDO_MYSQL) {
            throw new PersistenceException(PersistenceException::DRIVER_UNSUPPORTED, 'Database driver is unsupported.');
        }

        $this->value = $normalized;
    }
}

final readonly class DatabaseConnectionConfiguration
{
    public function __construct(
        public DatabaseDriverIdentifier $driver,
        public string $host,
        public int $port,
        public string $database,
        public string $username,
        public SecretValue $password,
        public string $charset = 'utf8mb4',
    ) {
        self::assertHost($this->host);

        if ($this->port < 1 || $this->port > 65535) {
            throw new PersistenceException(PersistenceException::CONFIGURATION_INVALID, 'Database port is invalid.');
        }

        if (preg_match('/^[A-Za-z0-9_]{1,64}$/', $this->database) !== 1) {
            throw new PersistenceException(PersistenceException::CONFIGURATION_INVALID, 'Database name is invalid.');
        }

        if (preg_match('/^[A-Za-z0-9_@.-]{1,128}$/', $this->username) !== 1) {
            throw new PersistenceException(PersistenceException::CONFIGURATION_INVALID, 'Database username is invalid.');
        }

        if ($this->charset !== 'utf8mb4') {
            throw new PersistenceException(PersistenceException::CONFIGURATION_INVALID, 'Database charset must be utf8mb4.');
        }
    }

    private static function assertHost(string $host): void
    {
        if ($host === ''
            || strlen($host) > 255
            || preg_match('/^[A-Za-z0-9._:-]+$/D', $host) !== 1) {
            throw new PersistenceException(PersistenceException::CONFIGURATION_INVALID, 'Database host is invalid.');
        }
    }
}

final class DatabaseConfigurationLoader
{
    public function load(ConfigurationSource $source): DatabaseConnectionConfiguration
    {
        try {
            $driver = new DatabaseDriverIdentifier(
                $source->requiredString(new ConfigurationKey('DB_DRIVER'))
            );
            $host = $source->requiredString(new ConfigurationKey('DB_HOST'));
            $portValue = $source->optionalString(new ConfigurationKey('DB_PORT'), '3306');
            $database = $source->requiredString(new ConfigurationKey('DB_NAME'));
            $username = $source->requiredString(new ConfigurationKey('DB_USER'));
            $password = $source->secret(new ConfigurationKey('DB_PASSWORD'));
            $charset = strtolower($source->optionalString(new ConfigurationKey('DB_CHARSET'), 'utf8mb4'));
        } catch (ConfigurationException|PersistenceException $exception) {
            throw new PersistenceException(PersistenceException::CONFIGURATION_INVALID, 'Database configuration is invalid.');
        }

        if (filter_var($portValue, FILTER_VALIDATE_INT) === false) {
            throw new PersistenceException(PersistenceException::CONFIGURATION_INVALID, 'Database port is invalid.');
        }

        return new DatabaseConnectionConfiguration(
            $driver,
            $host,
            (int) $portValue,
            $database,
            $username,
            $password,
            $charset,
        );
    }
}

final readonly class DatabaseConnectionPolicy
{
    /** @return array<int, int|bool> */
    public function pdoOptions(): array
    {
        return [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::ATTR_PERSISTENT => false,
        ];
    }
}

interface DatabaseConnection
{
    public function isConnected(): bool;

    public function close(): void;
}

interface DatabaseConnector
{
    public function connect(DatabaseConnectionConfiguration $configuration): DatabaseConnection;
}

final class PdoMySqlConnection implements DatabaseConnection
{
    public function __construct(private ?\PDO $pdo)
    {
    }

    public function isConnected(): bool
    {
        return $this->pdo instanceof \PDO;
    }

    public function close(): void
    {
        $this->pdo = null;
    }
}

final readonly class PdoMySqlConnector implements DatabaseConnector
{
    public function __construct(private DatabaseConnectionPolicy $policy)
    {
    }

    public function connect(DatabaseConnectionConfiguration $configuration): DatabaseConnection
    {
        if ($configuration->driver->value !== DatabaseDriverIdentifier::PDO_MYSQL) {
            throw new PersistenceException(PersistenceException::DRIVER_UNSUPPORTED, 'Database driver is unsupported.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $configuration->host,
            $configuration->port,
            $configuration->database,
            $configuration->charset,
        );

        try {
            $pdo = new \PDO(
                $dsn,
                $configuration->username,
                $configuration->password->reveal(),
                $this->policy->pdoOptions(),
            );
        } catch (\PDOException) {
            throw new PersistenceException(PersistenceException::CONNECTION_FAILED, 'Database connection failed.');
        }

        return new PdoMySqlConnection($pdo);
    }
}

final class SyntheticDatabaseConnection implements DatabaseConnection
{
    public function __construct(private bool $connected = true)
    {
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function close(): void
    {
        $this->connected = false;
    }
}

final class SyntheticDatabaseConnector implements DatabaseConnector
{
    public int $connectCalls = 0;

    public function __construct(
        private readonly bool $shouldConnect = true,
        private readonly bool $shouldThrow = false,
    ) {
    }

    public function connect(DatabaseConnectionConfiguration $configuration): DatabaseConnection
    {
        $this->connectCalls++;

        if ($this->shouldThrow) {
            throw new PersistenceException(PersistenceException::CONNECTION_FAILED, 'Synthetic connection failed.');
        }

        return new SyntheticDatabaseConnection($this->shouldConnect);
    }
}

final readonly class DatabaseConnectionResult
{
    /** @param list<string> $errorCodes @param array<string, scalar|list<string>> $metadata */
    private function __construct(
        public bool $isSuccessful,
        public array $errorCodes,
        public string $correlationId,
        public array $metadata,
    ) {
        if ($this->correlationId === '') {
            throw new \InvalidArgumentException('Correlation ID is required.');
        }
    }

    /** @param array<string, scalar|list<string>> $metadata */
    public static function success(string $correlationId, array $metadata = []): self
    {
        return new self(true, [], $correlationId, $metadata);
    }

    /** @param list<string> $errorCodes @param array<string, scalar|list<string>> $metadata */
    public static function failure(string $correlationId, array $errorCodes, array $metadata = []): self
    {
        return new self(false, array_values(array_unique($errorCodes)), $correlationId, $metadata);
    }
}

final readonly class DatabaseConnectionService
{
    public function __construct(private DatabaseConfigurationLoader $loader)
    {
    }

    public function connect(
        ConfigurationSource $source,
        DatabaseConnector $connector,
        string $correlationId,
    ): DatabaseConnectionResult {
        if ($correlationId === '') {
            throw new \InvalidArgumentException('Correlation ID is required.');
        }

        try {
            $configuration = $this->loader->load($source);
        } catch (PersistenceException) {
            return DatabaseConnectionResult::failure(
                $correlationId,
                [PersistenceException::CONFIGURATION_INVALID, PersistenceException::NOT_READY],
            );
        }

        try {
            $connection = $connector->connect($configuration);
        } catch (PersistenceException) {
            return DatabaseConnectionResult::failure(
                $correlationId,
                [PersistenceException::CONNECTION_FAILED, PersistenceException::NOT_READY],
            );
        }

        if (!$connection->isConnected()) {
            return DatabaseConnectionResult::failure(
                $correlationId,
                [PersistenceException::CONNECTION_UNAVAILABLE, PersistenceException::NOT_READY],
            );
        }

        return DatabaseConnectionResult::success(
            $correlationId,
            ['driver' => $configuration->driver->value],
        );
    }
}
