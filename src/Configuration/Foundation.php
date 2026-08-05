<?php

declare(strict_types=1);

namespace OneQay\Configuration;

final class ConfigurationException extends \RuntimeException
{
    public const REQUIRED = 'CONFIGURATION_REQUIRED';
    public const EMPTY = 'CONFIGURATION_EMPTY';
    public const INVALID = 'CONFIGURATION_INVALID';
    public const SECRET_REQUIRED = 'CONFIGURATION_SECRET_REQUIRED';
    public const ENVIRONMENT_UNSAFE = 'CONFIGURATION_ENVIRONMENT_UNSAFE';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

final readonly class EnvironmentIdentifier
{
    private const ALLOWED = ['local', 'test', 'preview', 'production'];

    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new ConfigurationException(
                ConfigurationException::INVALID,
                'Application environment is invalid.'
            );
        }

        $this->value = $normalized;
    }

    public function isRestricted(): bool
    {
        return $this->value === 'preview' || $this->value === 'production';
    }
}

final readonly class ConfigurationKey
{
    public string $value;

    public function __construct(string $value)
    {
        if ($value === '' || preg_match('/\s/', $value) === 1) {
            throw new ConfigurationException(ConfigurationException::INVALID, 'Configuration key is invalid.');
        }

        $normalized = strtoupper($value);

        if (preg_match('/^[A-Z][A-Z0-9]*(?:_[A-Z0-9]+)*$/', $normalized) !== 1 || strlen($normalized) > 96) {
            throw new ConfigurationException(ConfigurationException::INVALID, 'Configuration key is invalid.');
        }

        $this->value = $normalized;
    }
}

final class SecretValue implements \JsonSerializable
{
    private const REDACTED = '[REDACTED]';

    public function __construct(private readonly string $value)
    {
        if ($this->value === '') {
            throw new ConfigurationException(
                ConfigurationException::SECRET_REQUIRED,
                'Required secret configuration is unavailable.'
            );
        }
    }

    public function reveal(): string
    {
        return $this->value;
    }

    public function redacted(): string
    {
        return self::REDACTED;
    }

    public function __toString(): string
    {
        return self::REDACTED;
    }

    /** @return array{value: string} */
    public function __debugInfo(): array
    {
        return ['value' => self::REDACTED];
    }

    public function jsonSerialize(): string
    {
        return self::REDACTED;
    }

    /** @return array{value: string} */
    public function __serialize(): array
    {
        return ['value' => self::REDACTED];
    }

    /** @param array{value?: mixed} $data */
    public function __unserialize(array $data): void
    {
        throw new \LogicException('Secret values cannot be unserialized.');
    }
}

interface ConfigurationSource
{
    public function requiredString(ConfigurationKey $key): string;

    public function optionalString(ConfigurationKey $key, string $default): string;

    public function requiredBoolean(ConfigurationKey $key): bool;

    public function optionalBoolean(ConfigurationKey $key, bool $default): bool;

    public function secret(ConfigurationKey $key): SecretValue;
}

abstract class AbstractConfigurationSource implements ConfigurationSource
{
    abstract protected function read(ConfigurationKey $key): ?string;

    public function requiredString(ConfigurationKey $key): string
    {
        $value = $this->read($key);

        if ($value === null) {
            throw new ConfigurationException(ConfigurationException::REQUIRED, 'Required configuration is missing.');
        }

        if ($value === '') {
            throw new ConfigurationException(ConfigurationException::EMPTY, 'Required configuration is empty.');
        }

        return $value;
    }

    public function optionalString(ConfigurationKey $key, string $default): string
    {
        $value = $this->read($key);

        return $value === null ? $default : $value;
    }

    public function requiredBoolean(ConfigurationKey $key): bool
    {
        return self::parseBoolean($this->requiredString($key));
    }

    public function optionalBoolean(ConfigurationKey $key, bool $default): bool
    {
        $value = $this->read($key);

        return $value === null ? $default : self::parseBoolean($value);
    }

    public function secret(ConfigurationKey $key): SecretValue
    {
        $value = $this->read($key);

        if ($value === null || $value === '') {
            throw new ConfigurationException(
                ConfigurationException::SECRET_REQUIRED,
                'Required secret configuration is unavailable.'
            );
        }

        return new SecretValue($value);
    }

    private static function parseBoolean(string $value): bool
    {
        return match (strtolower($value)) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => throw new ConfigurationException(
                ConfigurationException::INVALID,
                'Boolean configuration is invalid.'
            ),
        };
    }
}

final class ArrayConfigurationSource extends AbstractConfigurationSource
{
    /** @var array<string, string> */
    private array $values = [];

    /** @param array<string, string|bool|int> $values */
    public function __construct(array $values)
    {
        foreach ($values as $key => $value) {
            $configurationKey = new ConfigurationKey((string) $key);
            $this->values[$configurationKey->value] = is_bool($value)
                ? ($value ? 'true' : 'false')
                : (string) $value;
        }
    }

    protected function read(ConfigurationKey $key): ?string
    {
        return array_key_exists($key->value, $this->values)
            ? $this->values[$key->value]
            : null;
    }
}

final class EnvironmentVariableConfigurationSource extends AbstractConfigurationSource
{
    protected function read(ConfigurationKey $key): ?string
    {
        $value = getenv($key->value);

        return $value === false ? null : $value;
    }
}

final readonly class StartupValidationResult
{
    /** @param list<string> $errorCodes */
    private function __construct(public bool $isValid, public array $errorCodes)
    {
    }

    public static function success(): self
    {
        return new self(true, []);
    }

    /** @param list<string> $errorCodes */
    public static function failure(array $errorCodes): self
    {
        return new self(false, array_values(array_unique($errorCodes)));
    }
}

final class StartupConfigurationValidator
{
    public function validate(ConfigurationSource $source): StartupValidationResult
    {
        $errors = [];
        $environment = null;
        $debug = null;
        $secureSession = null;

        try {
            $environment = new EnvironmentIdentifier(
                $source->requiredString(new ConfigurationKey('APP_ENV'))
            );
        } catch (ConfigurationException $exception) {
            $errors[] = $exception->errorCode;
        }

        try {
            $debug = $source->requiredBoolean(new ConfigurationKey('APP_DEBUG'));
        } catch (ConfigurationException $exception) {
            $errors[] = $exception->errorCode;
        }

        try {
            $secureSession = $source->requiredBoolean(new ConfigurationKey('SESSION_SECURE'));
        } catch (ConfigurationException $exception) {
            $errors[] = $exception->errorCode;
        }

        try {
            $source->secret(new ConfigurationKey('APP_KEY'));
        } catch (ConfigurationException $exception) {
            $errors[] = $exception->errorCode;
        }

        if ($environment?->isRestricted() === true && ($debug === true || $secureSession === false)) {
            $errors[] = ConfigurationException::ENVIRONMENT_UNSAFE;
        }

        return $errors === []
            ? StartupValidationResult::success()
            : StartupValidationResult::failure($errors);
    }
}
