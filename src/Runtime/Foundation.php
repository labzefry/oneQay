<?php

declare(strict_types=1);

namespace OneQay\Runtime;

use OneQay\Configuration\ConfigurationSource;
use OneQay\Configuration\StartupConfigurationValidator;

final class RuntimeException extends \RuntimeException
{
    public const PHP_VERSION_UNSUPPORTED = 'RUNTIME_PHP_VERSION_UNSUPPORTED';
    public const EXTENSION_REQUIRED = 'RUNTIME_EXTENSION_REQUIRED';
    public const CAPABILITY_UNKNOWN = 'RUNTIME_CAPABILITY_UNKNOWN';
    public const DOCUMENT_ROOT_UNSAFE = 'RUNTIME_DOCUMENT_ROOT_UNSAFE';
    public const REWRITE_UNAVAILABLE = 'RUNTIME_REWRITE_UNAVAILABLE';
    public const BOOTSTRAP_FAILED = 'RUNTIME_BOOTSTRAP_FAILED';
    public const CONFIGURATION_INVALID = 'RUNTIME_CONFIGURATION_INVALID';
    public const NOT_READY = 'RUNTIME_NOT_READY';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

final readonly class RuntimeCapabilityIdentifier
{
    public const PHP_VERSION = 'PHP_VERSION';
    public const PHP_EXTENSION_JSON = 'PHP_EXTENSION_JSON';
    public const PHP_EXTENSION_OPENSSL = 'PHP_EXTENSION_OPENSSL';
    public const PHP_EXTENSION_MBSTRING = 'PHP_EXTENSION_MBSTRING';
    public const PHP_EXTENSION_PDO = 'PHP_EXTENSION_PDO';
    public const PHP_EXTENSION_PDO_MYSQL = 'PHP_EXTENSION_PDO_MYSQL';
    public const PHP_EXTENSION_FILTER = 'PHP_EXTENSION_FILTER';
    public const PHP_EXTENSION_SESSION = 'PHP_EXTENSION_SESSION';
    public const PHP_EXTENSION_CTYPE = 'PHP_EXTENSION_CTYPE';
    public const DOCUMENT_ROOT_PUBLIC = 'DOCUMENT_ROOT_PUBLIC';
    public const URL_REWRITE = 'URL_REWRITE';
    public const COMPOSER_AVAILABLE = 'COMPOSER_AVAILABLE';
    public const CRON_AVAILABLE = 'CRON_AVAILABLE';
    public const BACKGROUND_WORKER_AVAILABLE = 'BACKGROUND_WORKER_AVAILABLE';
    public const LOG_ACCESS_AVAILABLE = 'LOG_ACCESS_AVAILABLE';

    public string $value;

    public function __construct(string $value)
    {
        if ($value === '' || $value !== strtoupper($value)
            || preg_match('/^[A-Z][A-Z0-9]*(?:_[A-Z0-9]+)*$/', $value) !== 1
            || strlen($value) > 96) {
            throw new \InvalidArgumentException('Runtime capability identifier is invalid.');
        }
        $this->value = $value;
    }
}

enum CapabilityStatus: string
{
    case AVAILABLE = 'AVAILABLE';
    case UNAVAILABLE = 'UNAVAILABLE';
    case UNKNOWN = 'UNKNOWN';
}

interface RuntimeCapabilityProvider
{
    public function phpVersion(): string;
    public function status(RuntimeCapabilityIdentifier $identifier): CapabilityStatus;
}

final readonly class SyntheticRuntimeCapabilityProvider implements RuntimeCapabilityProvider
{
    /** @param array<string, CapabilityStatus> $statuses */
    public function __construct(private string $version, private array $statuses) {}
    public function phpVersion(): string { return $this->version; }
    public function status(RuntimeCapabilityIdentifier $identifier): CapabilityStatus
    {
        return $this->statuses[$identifier->value] ?? CapabilityStatus::UNKNOWN;
    }
}

final readonly class NativeRuntimeCapabilityProvider implements RuntimeCapabilityProvider
{
    /** @param array<string, bool|null> $verifiedInfrastructure */
    public function __construct(private bool $documentRootIsPublic, private array $verifiedInfrastructure = []) {}
    public function phpVersion(): string { return PHP_VERSION; }
    public function status(RuntimeCapabilityIdentifier $identifier): CapabilityStatus
    {
        $extension = match ($identifier->value) {
            RuntimeCapabilityIdentifier::PHP_EXTENSION_JSON => 'json',
            RuntimeCapabilityIdentifier::PHP_EXTENSION_OPENSSL => 'openssl',
            RuntimeCapabilityIdentifier::PHP_EXTENSION_MBSTRING => 'mbstring',
            RuntimeCapabilityIdentifier::PHP_EXTENSION_PDO => 'pdo',
            RuntimeCapabilityIdentifier::PHP_EXTENSION_PDO_MYSQL => 'pdo_mysql',
            RuntimeCapabilityIdentifier::PHP_EXTENSION_FILTER => 'filter',
            RuntimeCapabilityIdentifier::PHP_EXTENSION_SESSION => 'session',
            RuntimeCapabilityIdentifier::PHP_EXTENSION_CTYPE => 'ctype',
            default => null,
        };
        if ($extension !== null) {
            return extension_loaded($extension) ? CapabilityStatus::AVAILABLE : CapabilityStatus::UNAVAILABLE;
        }
        if ($identifier->value === RuntimeCapabilityIdentifier::PHP_VERSION) {
            return CapabilityStatus::AVAILABLE;
        }
        if ($identifier->value === RuntimeCapabilityIdentifier::DOCUMENT_ROOT_PUBLIC) {
            return $this->documentRootIsPublic ? CapabilityStatus::AVAILABLE : CapabilityStatus::UNAVAILABLE;
        }
        $verified = $this->verifiedInfrastructure[$identifier->value] ?? null;
        return $verified === true ? CapabilityStatus::AVAILABLE : ($verified === false ? CapabilityStatus::UNAVAILABLE : CapabilityStatus::UNKNOWN);
    }
}

final readonly class RuntimeCapabilityReport
{
    /** @param list<string> $required @param list<string> $available @param list<string> $unavailable @param list<string> $unknown */
    public function __construct(public array $required, public array $available, public array $unavailable, public array $unknown) {}
    public function isReady(): bool { return $this->unavailable === [] && $this->unknown === []; }
}

final class RuntimeCapabilityValidator
{
    private const MINIMUM_PHP = '8.2.0';
    /** @var list<string> */
    private const REQUIRED = [
        RuntimeCapabilityIdentifier::PHP_VERSION,
        RuntimeCapabilityIdentifier::PHP_EXTENSION_JSON,
        RuntimeCapabilityIdentifier::PHP_EXTENSION_OPENSSL,
        RuntimeCapabilityIdentifier::PHP_EXTENSION_MBSTRING,
        RuntimeCapabilityIdentifier::PHP_EXTENSION_PDO,
        RuntimeCapabilityIdentifier::PHP_EXTENSION_PDO_MYSQL,
        RuntimeCapabilityIdentifier::PHP_EXTENSION_FILTER,
        RuntimeCapabilityIdentifier::PHP_EXTENSION_SESSION,
        RuntimeCapabilityIdentifier::PHP_EXTENSION_CTYPE,
        RuntimeCapabilityIdentifier::DOCUMENT_ROOT_PUBLIC,
    ];

    public function validate(RuntimeCapabilityProvider $provider): RuntimeCapabilityReport
    {
        $available = $unavailable = $unknown = [];
        foreach (self::REQUIRED as $capability) {
            $status = $capability === RuntimeCapabilityIdentifier::PHP_VERSION
                ? (version_compare($provider->phpVersion(), self::MINIMUM_PHP, '>=') ? CapabilityStatus::AVAILABLE : CapabilityStatus::UNAVAILABLE)
                : $provider->status(new RuntimeCapabilityIdentifier($capability));
            match ($status) {
                CapabilityStatus::AVAILABLE => $available[] = $capability,
                CapabilityStatus::UNAVAILABLE => $unavailable[] = $capability,
                CapabilityStatus::UNKNOWN => $unknown[] = $capability,
            };
        }
        return new RuntimeCapabilityReport(self::REQUIRED, $available, $unavailable, $unknown);
    }
}

interface CorrelationIdGenerator { public function generate(): string; }
final class RandomCorrelationIdGenerator implements CorrelationIdGenerator
{
    public function generate(): string { return bin2hex(random_bytes(16)); }
}
final readonly class FixedCorrelationIdGenerator implements CorrelationIdGenerator
{
    public function __construct(private string $value) {}
    public function generate(): string { return $this->value; }
}

final readonly class BootstrapResult
{
    /** @param list<string> $errorCodes @param array<string, scalar|list<string>> $metadata */
    private function __construct(public bool $isSuccessful, public array $errorCodes, public string $correlationId, public array $metadata) {}
    /** @param array<string, scalar|list<string>> $metadata */
    public static function success(string $correlationId, array $metadata = []): self { return new self(true, [], $correlationId, $metadata); }
    /** @param list<string> $errorCodes @param array<string, scalar|list<string>> $metadata */
    public static function failure(string $correlationId, array $errorCodes, array $metadata = []): self
    {
        return new self(false, array_values(array_unique($errorCodes)), $correlationId, $metadata);
    }
}

interface ApplicationBootstrap
{
    public function run(ConfigurationSource $configuration, RuntimeCapabilityProvider $runtime): BootstrapResult;
}

final readonly class SafeApplicationBootstrap implements ApplicationBootstrap
{
    public function __construct(private StartupConfigurationValidator $configurationValidator, private RuntimeCapabilityValidator $runtimeValidator, private CorrelationIdGenerator $correlationIds) {}
    public function run(ConfigurationSource $configuration, RuntimeCapabilityProvider $runtime): BootstrapResult
    {
        $correlationId = $this->correlationIds->generate();
        try {
            $configurationResult = $this->configurationValidator->validate($configuration);
            if (!$configurationResult->isValid) {
                return BootstrapResult::failure($correlationId, [RuntimeException::CONFIGURATION_INVALID], ['configuration_errors' => $configurationResult->errorCodes]);
            }
            $report = $this->runtimeValidator->validate($runtime);
            if ($report->unavailable !== [] || $report->unknown !== []) {
                $errors = [];
                if (in_array(RuntimeCapabilityIdentifier::PHP_VERSION, $report->unavailable, true)) $errors[] = RuntimeException::PHP_VERSION_UNSUPPORTED;
                if (array_intersect($report->unavailable, self::requiredExtensions()) !== []) $errors[] = RuntimeException::EXTENSION_REQUIRED;
                if (in_array(RuntimeCapabilityIdentifier::DOCUMENT_ROOT_PUBLIC, $report->unavailable, true)) $errors[] = RuntimeException::DOCUMENT_ROOT_UNSAFE;
                if ($report->unknown !== []) $errors[] = RuntimeException::CAPABILITY_UNKNOWN;
                $errors[] = RuntimeException::NOT_READY;
                return BootstrapResult::failure($correlationId, $errors, ['unavailable' => $report->unavailable, 'unknown' => $report->unknown]);
            }
            return BootstrapResult::success($correlationId, ['runtime' => 'ready']);
        } catch (\Throwable) {
            return BootstrapResult::failure($correlationId, [RuntimeException::BOOTSTRAP_FAILED]);
        }
    }
    /** @return list<string> */
    private static function requiredExtensions(): array
    {
        return [RuntimeCapabilityIdentifier::PHP_EXTENSION_JSON, RuntimeCapabilityIdentifier::PHP_EXTENSION_OPENSSL, RuntimeCapabilityIdentifier::PHP_EXTENSION_MBSTRING, RuntimeCapabilityIdentifier::PHP_EXTENSION_PDO, RuntimeCapabilityIdentifier::PHP_EXTENSION_PDO_MYSQL, RuntimeCapabilityIdentifier::PHP_EXTENSION_FILTER, RuntimeCapabilityIdentifier::PHP_EXTENSION_SESSION, RuntimeCapabilityIdentifier::PHP_EXTENSION_CTYPE];
    }
}

final readonly class HealthResult
{
    public function __construct(public bool $isHealthy, public string $correlationId) {}
    /** @return array{status:string,correlation_id:string} */
    public function toArray(): array { return ['status' => $this->isHealthy ? 'healthy' : 'unhealthy', 'correlation_id' => $this->correlationId]; }
}

final readonly class ReadinessResult
{
    /** @param list<string> $errorCodes */
    public function __construct(public bool $isReady, public string $correlationId, public array $errorCodes = []) {}
    public static function fromBootstrap(BootstrapResult $result): self { return new self($result->isSuccessful, $result->correlationId, $result->errorCodes); }
    /** @return array{status:string,correlation_id:string,error_codes:list<string>} */
    public function toArray(): array { return ['status' => $this->isReady ? 'ready' : 'not_ready', 'correlation_id' => $this->correlationId, 'error_codes' => $this->errorCodes]; }
}
