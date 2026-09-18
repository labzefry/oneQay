<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootInstallationActivationHandoff
{
    public const REQUEST_SCHEMA_VERSION = 1;

    private const MAX_PENDING_ENVIRONMENT_BYTES = 65536;
    private const MAX_REQUEST_BYTES = 8192;

    public function __construct(
        private readonly string $sharedRoot,
        private readonly string $releaseId,
        private readonly int $nowUnix,
    ) {
        if (! preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId)) {
            throw new RuntimeException('invalid_release_identity');
        }

        if ($nowUnix < 1) {
            throw new RuntimeException('invalid_handoff_clock');
        }
    }

    /** @param array<string, mixed> $databaseFacts */
    public function buildRequest(string $pendingEnvironment, array $databaseFacts): string
    {
        if (! $this->pendingEnvironmentIsVerified($pendingEnvironment)) {
            throw new RuntimeException('activation_handoff_pending_configuration_invalid');
        }

        $facts = $this->normalizedDatabaseFacts($databaseFacts);
        if (! (new PrebootDatabaseCompatibilityVerification())->factsAreCompatible($facts)) {
            throw new RuntimeException('activation_handoff_database_verification_invalid');
        }

        $databaseFingerprint = hash(
            'sha256',
            json_encode($facts, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );

        $payload = [
            'schema_version' => self::REQUEST_SCHEMA_VERSION,
            'product' => 'oneQay',
            'release_id' => $this->releaseId,
            'pending_environment_sha256' => hash('sha256', $pendingEnvironment),
            'database_verification_fingerprint' => $databaseFingerprint,
            'created_at_unix' => $this->nowUnix,
            'activation_authorized' => false,
            'activation_authority_required' => true,
            'migration_execution_authorized' => false,
            'technical_preview_authorized' => false,
            'production_authorized' => false,
            'attribution' => 'Lab | zefry',
        ];

        $encoded = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        return $encoded.PHP_EOL;
    }

    public function requestMatchesPending(string $pendingEnvironment): bool
    {
        if (! $this->pendingEnvironmentIsVerified($pendingEnvironment)) {
            return false;
        }

        $path = $this->requestPath();
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            return false;
        }

        $size = filesize($path);
        if (! is_int($size) || $size < 1 || $size > self::MAX_REQUEST_BYTES) {
            return false;
        }

        try {
            $raw = file_get_contents($path);
            if (! is_string($raw) || $raw === '') {
                return false;
            }

            $request = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
            if (! is_array($request)) {
                return false;
            }

            return ($request['schema_version'] ?? null) === self::REQUEST_SCHEMA_VERSION
                && ($request['product'] ?? null) === 'oneQay'
                && ($request['release_id'] ?? null) === $this->releaseId
                && is_string($request['pending_environment_sha256'] ?? null)
                && preg_match('/\A[0-9a-f]{64}\z/', (string) $request['pending_environment_sha256']) === 1
                && hash_equals(
                    hash('sha256', $pendingEnvironment),
                    strtolower((string) $request['pending_environment_sha256']),
                )
                && is_string($request['database_verification_fingerprint'] ?? null)
                && preg_match('/\A[0-9a-f]{64}\z/', (string) $request['database_verification_fingerprint']) === 1
                && is_int($request['created_at_unix'] ?? null)
                && (int) $request['created_at_unix'] > 0
                && ($request['activation_authorized'] ?? null) === false
                && ($request['activation_authority_required'] ?? null) === true
                && ($request['migration_execution_authorized'] ?? null) === false
                && ($request['technical_preview_authorized'] ?? null) === false
                && ($request['production_authorized'] ?? null) === false
                && ($request['attribution'] ?? null) === 'Lab | zefry';
        } catch (Throwable) {
            return false;
        }
    }

    public function requestPath(): string
    {
        return rtrim($this->sharedRoot, DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.'install'
            .DIRECTORY_SEPARATOR.'activation-request.json';
    }

    /** @param array<string, mixed> $databaseFacts
     *  @return array{
     *    connected: bool,
     *    engine: string,
     *    server_version: string,
     *    charset: string,
     *    timezone: string,
     *    schema_state: string,
     *    least_privilege: bool
     *  }
     */
    private function normalizedDatabaseFacts(array $databaseFacts): array
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
            if (! array_key_exists($requiredKey, $databaseFacts)) {
                throw new RuntimeException('activation_handoff_database_verification_invalid');
            }
        }

        return [
            'connected' => $databaseFacts['connected'] === true,
            'engine' => is_string($databaseFacts['engine']) ? strtolower(trim($databaseFacts['engine'])) : '',
            'server_version' => is_string($databaseFacts['server_version']) ? trim($databaseFacts['server_version']) : '',
            'charset' => is_string($databaseFacts['charset']) ? strtolower(trim($databaseFacts['charset'])) : '',
            'timezone' => is_string($databaseFacts['timezone']) ? strtoupper(trim($databaseFacts['timezone'])) : '',
            'schema_state' => is_string($databaseFacts['schema_state']) ? strtolower(trim($databaseFacts['schema_state'])) : '',
            'least_privilege' => $databaseFacts['least_privilege'] === true,
        ];
    }

    private function pendingEnvironmentIsVerified(string $pendingEnvironment): bool
    {
        $length = strlen($pendingEnvironment);
        if ($length < 1 || $length > self::MAX_PENDING_ENVIRONMENT_BYTES) {
            return false;
        }

        return str_contains($pendingEnvironment, 'ONEQAY_INSTALLATION_DATABASE_VERIFIED="true"')
            && str_contains($pendingEnvironment, 'ONEQAY_INSTALLATION_DATABASE_LEAST_PRIVILEGE="true"')
            && str_contains($pendingEnvironment, 'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="false"')
            && str_contains($pendingEnvironment, 'ONEQAY_TECHNICAL_PREVIEW_ENABLED="false"')
            && str_contains($pendingEnvironment, 'ONEQAY_PERSISTENCE_ENABLED="false"');
    }
}
