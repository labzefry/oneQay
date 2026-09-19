<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use Closure;
use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootInstallationConfiguration
{
    public const AUTHORITY_SCHEMA_VERSION = 1;

    private const MAX_AUTHORITY_BYTES = 4096;
    private const MAX_PASSWORD_BYTES = 1024;

    /** @var Closure(array<string, mixed>): array<string, mixed> */
    private readonly Closure $databaseVerification;

    public function __construct(
        private readonly string $sharedRoot,
        private readonly string $releaseId,
        private readonly int $nowUnix,
        ?Closure $databaseVerification = null,
    ) {
        if (! preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId)) {
            throw new RuntimeException('invalid_release_identity');
        }

        $this->databaseVerification = $databaseVerification
            ?? static fn (array $configuration): array => (new PrebootDatabaseCompatibilityVerification())->verify($configuration);
    }

    /**
     * @return array{state: string, can_prepare: bool, activation_handoff_ready: bool, promotion_request_pending: bool, activation_authorized: false}
     */
    public function inspect(): array
    {
        $this->assertPrivateBoundaryShape();

        if (is_file($this->activeEnvironmentPath())) {
            return $this->state('ACTIVE_ENV_PRESENT', false);
        }

        if (is_file($this->pendingEnvironmentPath())) {
            $verified = $this->pendingConfigurationIsDatabaseVerified();
            $handoffReady = false;
            $promotionRequestPending = false;
            if ($verified) {
                $handoff = (new PrebootInstallationActivationReadiness(
                    $this->sharedRoot,
                    $this->releaseId,
                    $this->nowUnix,
                ))->inspect();
                $handoffReady = ($handoff['ready'] ?? false) === true;

                if ($handoffReady) {
                    $request = (new PrebootRuntimeConfigurationPromotionRequest(
                        $this->sharedRoot,
                        $this->releaseId,
                        $this->nowUnix,
                    ))->inspect();
                    $promotionRequestPending = ($request['request_ready'] ?? false) === true;
                }
            }

            return $this->state(
                $verified ? 'PENDING_CONFIGURATION_VERIFIED' : 'PENDING_CONFIGURATION_PRESENT',
                false,
                $handoffReady,
                $promotionRequestPending,
            );
        }

        $authority = $this->loadAuthority();
        if ($authority === null) {
            return $this->state('AUTHORITY_MISSING', false);
        }

        if (! $this->authorityIsStructurallyValid($authority)) {
            return $this->state('AUTHORITY_INVALID', false);
        }

        if ((int) $authority['expires_at'] <= $this->nowUnix) {
            return $this->state('AUTHORITY_EXPIRED', false);
        }

        return $this->state('READY_FOR_CONFIGURATION', true);
    }

    /**
     * @param array<string, mixed> $input
     * @return array{state: string, prepared: true, activation_handoff_ready: true, promotion_request_pending: true, activation_authorized: false}
     */
    public function prepare(array $input): array
    {
        $this->assertPrivateBoundaryShape();

        $installDirectory = $this->installDirectory();
        if (! is_dir($installDirectory) || is_link($installDirectory)) {
            throw new RuntimeException('installation_authority_boundary_missing');
        }

        $lockPath = $installDirectory.DIRECTORY_SEPARATOR.'prepare.lock';
        $lock = @fopen($lockPath, 'c+b');
        if (! is_resource($lock)) {
            throw new RuntimeException('installation_lock_unavailable');
        }

        try {
            @chmod($lockPath, 0600);
            if (! flock($lock, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('installation_preparation_in_progress');
            }

            if (is_file($this->activeEnvironmentPath())) {
                throw new RuntimeException('active_environment_already_present');
            }

            if (is_file($this->pendingEnvironmentPath())) {
                throw new RuntimeException('pending_configuration_already_present');
            }

            $authority = $this->loadAuthority();
            if ($authority === null || ! $this->authorityIsStructurallyValid($authority)) {
                throw new RuntimeException('installation_authority_invalid');
            }

            if ((int) $authority['expires_at'] <= $this->nowUnix) {
                throw new RuntimeException('installation_authority_expired');
            }

            $token = $this->requiredString($input, 'installation_token', 32, 256);
            if (! preg_match('/\A[A-Za-z0-9._~-]{32,256}\z/', $token)
                || ! hash_equals(
                    strtolower((string) $authority['token_sha256']),
                    hash('sha256', $token),
                )) {
                throw new RuntimeException('installation_authority_denied');
            }

            $configuration = $this->validatedConfiguration($input);
            $verification = ($this->databaseVerification)($configuration);
            $facts = is_array($verification['facts'] ?? null) ? $verification['facts'] : [];
            if (($verification['ready'] ?? false) !== true
                || ! $this->databaseVerificationFactsAreCompatible($facts)) {
                throw new RuntimeException('database_compatibility_failed');
            }

            $runtimeDirectory = $this->runtimeDirectory();
            if (! is_dir($runtimeDirectory)
                && ! @mkdir($runtimeDirectory, 0700, true)
                && ! is_dir($runtimeDirectory)) {
                throw new RuntimeException('runtime_directory_unavailable');
            }
            if (is_link($runtimeDirectory)) {
                throw new RuntimeException('runtime_directory_symlink_rejected');
            }
            @chmod($runtimeDirectory, 0700);

            $sessionDirectory = $this->sessionDirectory();
            if (! is_dir($sessionDirectory)
                && ! @mkdir($sessionDirectory, 0700, true)
                && ! is_dir($sessionDirectory)) {
                throw new RuntimeException('preview_session_directory_unavailable');
            }
            if (is_link($sessionDirectory)) {
                throw new RuntimeException('preview_session_directory_symlink_rejected');
            }
            @chmod($sessionDirectory, 0700);
            $sessionPermissions = fileperms($sessionDirectory);
            if (is_int($sessionPermissions) && ($sessionPermissions & 0077) !== 0) {
                throw new RuntimeException('preview_session_directory_permissions_invalid');
            }

            $content = $this->renderPendingEnvironment($configuration, $facts);

            $pendingPath = $this->pendingEnvironmentPath();
            if (file_exists($pendingPath) || is_link($pendingPath)) {
                throw new RuntimeException('pending_configuration_already_present');
            }

            $temporaryPath = $runtimeDirectory.DIRECTORY_SEPARATOR.'.env.pending.tmp.'.bin2hex(random_bytes(12));
            $handle = @fopen($temporaryPath, 'x+b');
            if (! is_resource($handle)) {
                throw new RuntimeException('pending_configuration_temp_unavailable');
            }

            try {
                @chmod($temporaryPath, 0600);
                $length = strlen($content);
                $written = 0;
                while ($written < $length) {
                    $result = fwrite($handle, substr($content, $written));
                    if ($result === false || $result === 0) {
                        throw new RuntimeException('pending_configuration_write_failed');
                    }
                    $written += $result;
                }

                if (! fflush($handle)) {
                    throw new RuntimeException('pending_configuration_flush_failed');
                }

                if (function_exists('fsync')) {
                    @fsync($handle);
                }
            } catch (Throwable $exception) {
                fclose($handle);
                @unlink($temporaryPath);
                throw $exception;
            }

            fclose($handle);

            if (! @rename($temporaryPath, $pendingPath)) {
                @unlink($temporaryPath);
                throw new RuntimeException('pending_configuration_commit_failed');
            }
            @chmod($pendingPath, 0600);

            try {
                $handoff = (new PrebootInstallationActivationReadiness(
                    $this->sharedRoot,
                    $this->releaseId,
                    $this->nowUnix,
                ))->sealPendingEnvironment($content, $facts);

                $promotionRequest = (new PrebootRuntimeConfigurationPromotionRequest(
                    $this->sharedRoot,
                    $this->releaseId,
                    $this->nowUnix,
                ))->create();
            } catch (Throwable $exception) {
                @unlink($this->promotionRequestPath());
                @unlink($this->activationReadinessPath());
                @unlink($pendingPath);
                throw $exception;
            }

            // Authority is single-use only after verified pending configuration,
            // exact-release handoff, and the non-authorizing promotion request
            // are committed successfully.
            @unlink($this->authorityPath());

            return [
                'state' => 'CONFIGURATION_PREPARED_PENDING_ACTIVATION',
                'prepared' => true,
                'activation_handoff_ready' => ($handoff['ready'] ?? false) === true,
                'promotion_request_pending' => ($promotionRequest['state'] ?? null) === 'PROMOTION_REQUEST_PENDING_APPROVAL',
                'activation_authorized' => false,
            ];
        } finally {
            @flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return array{
     *   app_url: string,
     *   db_host: string,
     *   db_port: int,
     *   db_database: string,
     *   db_username: string,
     *   db_password: string
     * }
     */
    private function validatedConfiguration(array $input): array
    {
        $appUrl = $this->requiredString($input, 'app_url', 8, 255);
        $parts = parse_url($appUrl);
        if (! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || trim((string) ($parts['host'] ?? '')) === ''
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])) {
            throw new RuntimeException('invalid_app_url');
        }

        $host = $this->requiredString($input, 'db_host', 1, 255);
        if (preg_match('/\A[A-Za-z0-9][A-Za-z0-9._:-]{0,254}\z/', $host) !== 1
            || str_contains($host, '..')) {
            throw new RuntimeException('invalid_database_host');
        }

        $portRaw = $input['db_port'] ?? null;
        $port = filter_var($portRaw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 65535],
        ]);
        if (! is_int($port)) {
            throw new RuntimeException('invalid_database_port');
        }

        $database = $this->requiredString($input, 'db_database', 1, 64);
        if (preg_match('/\A[A-Za-z0-9][A-Za-z0-9_-]{0,63}\z/', $database) !== 1) {
            throw new RuntimeException('invalid_database_name');
        }

        $username = $this->requiredString($input, 'db_username', 1, 128);
        if (preg_match('/[\x00-\x20\x7f]/', $username) === 1) {
            throw new RuntimeException('invalid_database_username');
        }

        $password = $this->requiredSecretString($input, 'db_password', 1, self::MAX_PASSWORD_BYTES);
        if (preg_match('/[\x00\r\n]/', $password) === 1) {
            throw new RuntimeException('invalid_database_password');
        }

        return [
            'app_url' => rtrim($appUrl, '/'),
            'db_host' => $host,
            'db_port' => $port,
            'db_database' => $database,
            'db_username' => $username,
            'db_password' => $password,
        ];
    }

    /**
     * @param array{
     *   app_url: string,
     *   db_host: string,
     *   db_port: int,
     *   db_database: string,
     *   db_username: string,
     *   db_password: string
     * } $configuration
     * @param array<string, mixed> $databaseFacts
     */
    private function renderPendingEnvironment(array $configuration, array $databaseFacts): string
    {
        $values = [
            'APP_NAME' => 'oneQay',
            'APP_ENV' => 'production',
            'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
            'APP_DEBUG' => 'false',
            'APP_URL' => $configuration['app_url'],
            'ONEQAY_RUNTIME_CLASS' => 'preview',
            'ONEQAY_TECHNICAL_PREVIEW_ENABLED' => 'false',
            'ONEQAY_PERSISTENCE_ENABLED' => 'false',
            'ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED' => 'false',
            'ONEQAY_DB_DRIVER' => 'mysql',
            'ONEQAY_DB_HOST' => $configuration['db_host'],
            'ONEQAY_DB_PORT' => (string) $configuration['db_port'],
            'ONEQAY_DB_DATABASE' => $configuration['db_database'],
            'ONEQAY_DB_USERNAME' => $configuration['db_username'],
            'ONEQAY_DB_PASSWORD' => $configuration['db_password'],
            'ONEQAY_DB_SOCKET' => '',
            'ONEQAY_INSTALLATION_RELEASE_ID' => $this->releaseId,
            'ONEQAY_INSTALLATION_DATABASE_VERIFIED' => 'true',
            'ONEQAY_INSTALLATION_DATABASE_ENGINE' => (string) $databaseFacts['engine'],
            'ONEQAY_INSTALLATION_DATABASE_SERVER_VERSION' => (string) $databaseFacts['server_version'],
            'ONEQAY_INSTALLATION_DATABASE_CHARSET' => (string) $databaseFacts['charset'],
            'ONEQAY_INSTALLATION_DATABASE_TIMEZONE' => (string) $databaseFacts['timezone'],
            'ONEQAY_INSTALLATION_DATABASE_SCHEMA_STATE' => (string) $databaseFacts['schema_state'],
            'ONEQAY_INSTALLATION_DATABASE_LEAST_PRIVILEGE' => 'true',
            'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED' => 'false',
            'SESSION_DRIVER' => 'file',
            'SESSION_FILES' => $this->sessionDirectory(),
            'SESSION_LIFETIME' => '60',
            'SESSION_ENCRYPT' => 'true',
            'SESSION_SECURE_COOKIE' => 'true',
            'SESSION_COOKIE' => 'oneqay-preview-session',
            'CACHE_STORE' => 'file',
            'LOG_CHANNEL' => 'stack',
        ];

        $lines = [
            '# oneQay prepared runtime configuration',
            '# PREPARED ONLY — activation requires separate operational authority.',
            '# Author by Lab | zefry',
        ];

        foreach ($values as $key => $value) {
            $lines[] = $key.'='.$this->dotenvQuote($value);
        }

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    private function dotenvQuote(string $value): string
    {
        return '"'.strtr($value, [
            '\\' => '\\\\',
            '"' => '\\"',
            '$' => '\\$',
        ]).'"';
    }

    /** @param array<string, mixed> $facts */
    private function databaseVerificationFactsAreCompatible(array $facts): bool
    {
        foreach ([
            'connected',
            'engine',
            'server_version',
            'charset',
            'timezone',
            'schema_state',
            'least_privilege',
        ] as $requiredKey) {
            if (! array_key_exists($requiredKey, $facts)) {
                return false;
            }
        }

        return (new PrebootDatabaseCompatibilityVerification())->factsAreCompatible([
            'connected' => $facts['connected'] === true,
            'engine' => is_string($facts['engine']) ? $facts['engine'] : '',
            'server_version' => is_string($facts['server_version']) ? $facts['server_version'] : '',
            'charset' => is_string($facts['charset']) ? $facts['charset'] : '',
            'timezone' => is_string($facts['timezone']) ? $facts['timezone'] : '',
            'schema_state' => is_string($facts['schema_state']) ? $facts['schema_state'] : '',
            'least_privilege' => $facts['least_privilege'] === true,
        ]);
    }

    private function pendingConfigurationIsDatabaseVerified(): bool
    {
        $path = $this->pendingEnvironmentPath();
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            return false;
        }

        $size = filesize($path);
        if (! is_int($size) || $size <= 0 || $size > 65536) {
            return false;
        }

        $content = file_get_contents($path);
        if (! is_string($content)) {
            return false;
        }

        return str_contains($content, 'ONEQAY_INSTALLATION_DATABASE_VERIFIED="true"')
            && str_contains($content, 'ONEQAY_INSTALLATION_DATABASE_LEAST_PRIVILEGE="true"')
            && str_contains($content, 'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="false"');
    }

    /** @return array<string, mixed>|null */
    private function loadAuthority(): ?array
    {
        $path = $this->authorityPath();
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            return null;
        }

        $size = filesize($path);
        if (! is_int($size) || $size <= 0 || $size > self::MAX_AUTHORITY_BYTES) {
            return null;
        }

        try {
            $raw = file_get_contents($path);
            if (! is_string($raw) || $raw === '') {
                return null;
            }
            $decoded = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** @param array<string, mixed> $authority */
    private function authorityIsStructurallyValid(array $authority): bool
    {
        return ($authority['schema_version'] ?? null) === self::AUTHORITY_SCHEMA_VERSION
            && ($authority['product'] ?? null) === 'oneQay'
            && ($authority['release_id'] ?? null) === $this->releaseId
            && is_string($authority['token_sha256'] ?? null)
            && preg_match('/\A[0-9a-f]{64}\z/i', (string) $authority['token_sha256']) === 1
            && is_int($authority['expires_at'] ?? null)
            && (int) $authority['expires_at'] > 0
            && ($authority['activation_authorized'] ?? null) === false
            && ($authority['attribution'] ?? null) === 'Lab | zefry';
    }

    private function assertPrivateBoundaryShape(): void
    {
        if ($this->sharedRoot === ''
            || str_contains($this->sharedRoot, "\0")
            || (file_exists($this->sharedRoot) && is_link($this->sharedRoot))) {
            throw new RuntimeException('unsafe_shared_runtime_boundary');
        }

        foreach ([$this->activeEnvironmentPath(), $this->pendingEnvironmentPath(), $this->authorityPath()] as $path) {
            if (is_link($path)) {
                throw new RuntimeException('symlink_boundary_rejected');
            }
        }
    }

    private function requiredString(array $input, string $key, int $minimum, int $maximum): string
    {
        $value = $input[$key] ?? null;
        if (! is_string($value)) {
            throw new RuntimeException('invalid_'.$key);
        }

        $value = trim($value);
        $length = strlen($value);
        if ($length < $minimum || $length > $maximum) {
            throw new RuntimeException('invalid_'.$key);
        }

        return $value;
    }

    private function requiredSecretString(array $input, string $key, int $minimum, int $maximum): string
    {
        $value = $input[$key] ?? null;
        if (! is_string($value)) {
            throw new RuntimeException('invalid_'.$key);
        }

        $length = strlen($value);
        if ($length < $minimum || $length > $maximum) {
            throw new RuntimeException('invalid_'.$key);
        }

        return $value;
    }

    /** @return array{state: string, can_prepare: bool, activation_handoff_ready: bool, promotion_request_pending: bool, activation_authorized: false} */
    private function state(
        string $state,
        bool $canPrepare,
        bool $handoffReady = false,
        bool $promotionRequestPending = false,
    ): array {
        return [
            'state' => $state,
            'can_prepare' => $canPrepare,
            'activation_handoff_ready' => $handoffReady,
            'promotion_request_pending' => $promotionRequestPending,
            'activation_authorized' => false,
        ];
    }

    private function installDirectory(): string
    {
        return $this->sharedRoot.DIRECTORY_SEPARATOR.'install';
    }

    private function runtimeDirectory(): string
    {
        return $this->sharedRoot.DIRECTORY_SEPARATOR.'runtime';
    }

    private function sessionDirectory(): string
    {
        return $this->runtimeDirectory().DIRECTORY_SEPARATOR.'sessions';
    }

    private function authorityPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'authority.json';
    }

    private function activeEnvironmentPath(): string
    {
        return $this->runtimeDirectory().DIRECTORY_SEPARATOR.'.env';
    }

    private function pendingEnvironmentPath(): string
    {
        return $this->runtimeDirectory().DIRECTORY_SEPARATOR.'.env.pending';
    }

    private function activationReadinessPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'activation-readiness.json';
    }

    private function promotionRequestPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'runtime-configuration-promotion-request.json';
    }
}
