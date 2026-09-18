<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootInstallationActivationReadiness
{
    public const ATTESTATION_SCHEMA_VERSION = 1;

    private const MAX_PENDING_BYTES = 65536;
    private const MAX_ATTESTATION_BYTES = 16384;

    public function __construct(
        private readonly string $sharedRoot,
        private readonly string $releaseId,
        private readonly int $nowUnix,
    ) {
        if (! preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId)) {
            throw new RuntimeException('invalid_release_identity');
        }
    }

    /**
     * Seal the already-committed verified pending environment into a private,
     * non-authorizing activation-readiness attestation.
     *
     * @param array<string, mixed> $databaseFacts
     * @return array{
     *   state: string,
     *   ready: true,
     *   release_id: string,
     *   pending_environment_sha256: string,
     *   activation_authorized: false
     * }
     */
    public function sealPendingEnvironment(string $expectedPendingContent, array $databaseFacts): array
    {
        $this->assertPrivateBoundaryShape();

        if (is_file($this->activeEnvironmentPath())) {
            throw new RuntimeException('active_environment_already_present');
        }

        $pending = $this->readPendingEnvironment();
        if ($pending === null || ! hash_equals(hash('sha256', $expectedPendingContent), hash('sha256', $pending))) {
            throw new RuntimeException('pending_configuration_binding_failed');
        }

        if (! $this->pendingContentIsVerifiedAndReleaseBound($pending)) {
            throw new RuntimeException('pending_configuration_not_activation_ready');
        }

        $safeFacts = $this->safeDatabaseFacts($databaseFacts);
        if ($safeFacts === null) {
            throw new RuntimeException('database_verification_evidence_invalid');
        }

        $attestation = [
            'schema_version' => self::ATTESTATION_SCHEMA_VERSION,
            'product' => 'oneQay',
            'release_id' => $this->releaseId,
            'pending_environment_sha256' => hash('sha256', $pending),
            'pending_environment_bytes' => strlen($pending),
            'database' => $safeFacts,
            'sealed_at_unix' => $this->nowUnix,
            'activation_authorized' => false,
            'migration_execution_authorized' => false,
            'technical_preview_authorized' => false,
            'production_authorized' => false,
            'updater_authorized' => false,
            'attribution' => 'Lab | zefry',
        ];

        $this->writeAttestation($attestation);

        return [
            'state' => 'ACTIVATION_HANDOFF_READY',
            'ready' => true,
            'release_id' => $this->releaseId,
            'pending_environment_sha256' => (string) $attestation['pending_environment_sha256'],
            'activation_authorized' => false,
        ];
    }

    /**
     * @return array{
     *   state: string,
     *   ready: bool,
     *   release_id: string,
     *   pending_environment_sha256: string,
     *   activation_authorized: false
     * }
     */
    public function inspect(): array
    {
        try {
            $this->assertPrivateBoundaryShape();

            if (is_file($this->activeEnvironmentPath())) {
                return $this->state('ACTIVE_ENV_PRESENT', false);
            }

            $pending = $this->readPendingEnvironment();
            if ($pending === null) {
                return $this->state('PENDING_CONFIGURATION_MISSING', false);
            }

            if (! $this->pendingContentIsVerifiedAndReleaseBound($pending)) {
                return $this->state('PENDING_CONFIGURATION_NOT_VERIFIED', false);
            }

            $attestation = $this->readAttestation();
            if ($attestation === null) {
                return $this->state('ACTIVATION_HANDOFF_MISSING', false);
            }

            if (! $this->attestationIsValid($attestation, $pending)) {
                return $this->state('ACTIVATION_HANDOFF_INVALID', false);
            }

            return [
                'state' => 'ACTIVATION_HANDOFF_READY',
                'ready' => true,
                'release_id' => $this->releaseId,
                'pending_environment_sha256' => hash('sha256', $pending),
                'activation_authorized' => false,
            ];
        } catch (Throwable) {
            return $this->state('ACTIVATION_HANDOFF_INVALID', false);
        }
    }

    private function pendingContentIsVerifiedAndReleaseBound(string $content): bool
    {
        return str_contains($content, 'ONEQAY_INSTALLATION_RELEASE_ID="'.$this->releaseId.'"')
            && str_contains($content, 'ONEQAY_INSTALLATION_DATABASE_VERIFIED="true"')
            && str_contains($content, 'ONEQAY_INSTALLATION_DATABASE_LEAST_PRIVILEGE="true"')
            && str_contains($content, 'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="false"')
            && str_contains($content, 'ONEQAY_TECHNICAL_PREVIEW_ENABLED="false"')
            && str_contains($content, 'ONEQAY_PERSISTENCE_ENABLED="false"')
            && str_contains($content, 'ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED="false"');
    }

    /** @param array<string, mixed> $facts */
    private function safeDatabaseFacts(array $facts): ?array
    {
        foreach ([
            'engine',
            'server_version',
            'charset',
            'timezone',
            'schema_state',
            'least_privilege',
        ] as $requiredKey) {
            if (! array_key_exists($requiredKey, $facts)) {
                return null;
            }
        }

        $engine = is_string($facts['engine']) ? strtolower(trim($facts['engine'])) : '';
        $serverVersion = is_string($facts['server_version']) ? trim($facts['server_version']) : '';
        $charset = is_string($facts['charset']) ? strtolower(trim($facts['charset'])) : '';
        $timezone = is_string($facts['timezone']) ? strtoupper(trim($facts['timezone'])) : '';
        $schemaState = is_string($facts['schema_state']) ? strtolower(trim($facts['schema_state'])) : '';

        if (! in_array($engine, ['mysql', 'mariadb'], true)
            || preg_match('/\A\d+\.\d+(?:\.\d+)?(?:[-+._A-Za-z0-9]*)?\z/', $serverVersion) !== 1
            || $charset !== 'utf8mb4'
            || ! in_array($timezone, ['UTC', '+00:00'], true)
            || ! in_array($schemaState, ['empty', 'recognized'], true)
            || $facts['least_privilege'] !== true) {
            return null;
        }

        return [
            'engine' => $engine,
            'server_version' => $serverVersion,
            'charset' => $charset,
            'timezone' => $timezone,
            'schema_state' => $schemaState,
            'least_privilege' => true,
        ];
    }

    /** @param array<string, mixed> $attestation */
    private function attestationIsValid(array $attestation, string $pending): bool
    {
        $database = is_array($attestation['database'] ?? null) ? $attestation['database'] : null;

        return ($attestation['schema_version'] ?? null) === self::ATTESTATION_SCHEMA_VERSION
            && ($attestation['product'] ?? null) === 'oneQay'
            && ($attestation['release_id'] ?? null) === $this->releaseId
            && is_string($attestation['pending_environment_sha256'] ?? null)
            && preg_match('/\A[0-9a-f]{64}\z/', (string) $attestation['pending_environment_sha256']) === 1
            && hash_equals(hash('sha256', $pending), (string) $attestation['pending_environment_sha256'])
            && ($attestation['pending_environment_bytes'] ?? null) === strlen($pending)
            && is_int($attestation['sealed_at_unix'] ?? null)
            && (int) $attestation['sealed_at_unix'] > 0
            && $this->safeDatabaseFacts($database ?? []) !== null
            && ($attestation['activation_authorized'] ?? null) === false
            && ($attestation['migration_execution_authorized'] ?? null) === false
            && ($attestation['technical_preview_authorized'] ?? null) === false
            && ($attestation['production_authorized'] ?? null) === false
            && ($attestation['updater_authorized'] ?? null) === false
            && ($attestation['attribution'] ?? null) === 'Lab | zefry';
    }

    private function readPendingEnvironment(): ?string
    {
        $path = $this->pendingEnvironmentPath();
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            return null;
        }

        $size = filesize($path);
        if (! is_int($size) || $size <= 0 || $size > self::MAX_PENDING_BYTES) {
            return null;
        }

        $permissions = fileperms($path);
        if (is_int($permissions) && ($permissions & 0077) !== 0) {
            return null;
        }

        $content = file_get_contents($path);

        return is_string($content) ? $content : null;
    }

    /** @return array<string, mixed>|null */
    private function readAttestation(): ?array
    {
        $path = $this->attestationPath();
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            return null;
        }

        $size = filesize($path);
        if (! is_int($size) || $size <= 0 || $size > self::MAX_ATTESTATION_BYTES) {
            return null;
        }

        $permissions = fileperms($path);
        if (is_int($permissions) && ($permissions & 0077) !== 0) {
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

    /** @param array<string, mixed> $attestation */
    private function writeAttestation(array $attestation): void
    {
        $installDirectory = $this->installDirectory();
        if (! is_dir($installDirectory) || is_link($installDirectory)) {
            throw new RuntimeException('installation_handoff_boundary_missing');
        }

        $path = $this->attestationPath();
        if (file_exists($path) || is_link($path)) {
            throw new RuntimeException('activation_handoff_already_present');
        }

        $json = json_encode(
            $attestation,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ).PHP_EOL;

        $temporaryPath = $installDirectory.DIRECTORY_SEPARATOR.'activation-readiness.tmp.'.bin2hex(random_bytes(12));
        $handle = @fopen($temporaryPath, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('activation_handoff_temp_unavailable');
        }

        try {
            @chmod($temporaryPath, 0600);
            $length = strlen($json);
            $written = 0;
            while ($written < $length) {
                $result = fwrite($handle, substr($json, $written));
                if ($result === false || $result === 0) {
                    throw new RuntimeException('activation_handoff_write_failed');
                }
                $written += $result;
            }

            if (! fflush($handle)) {
                throw new RuntimeException('activation_handoff_flush_failed');
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

        if (! @rename($temporaryPath, $path)) {
            @unlink($temporaryPath);
            throw new RuntimeException('activation_handoff_commit_failed');
        }

        @chmod($path, 0600);
    }

    private function assertPrivateBoundaryShape(): void
    {
        if ($this->sharedRoot === ''
            || str_contains($this->sharedRoot, "\0")
            || (file_exists($this->sharedRoot) && is_link($this->sharedRoot))) {
            throw new RuntimeException('unsafe_shared_runtime_boundary');
        }

        foreach ([
            $this->activeEnvironmentPath(),
            $this->pendingEnvironmentPath(),
            $this->attestationPath(),
        ] as $path) {
            if (is_link($path)) {
                throw new RuntimeException('symlink_boundary_rejected');
            }
        }
    }

    /**
     * @return array{
     *   state: string,
     *   ready: bool,
     *   release_id: string,
     *   pending_environment_sha256: string,
     *   activation_authorized: false
     * }
     */
    private function state(string $state, bool $ready): array
    {
        return [
            'state' => $state,
            'ready' => $ready,
            'release_id' => $this->releaseId,
            'pending_environment_sha256' => '',
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

    private function activeEnvironmentPath(): string
    {
        return $this->runtimeDirectory().DIRECTORY_SEPARATOR.'.env';
    }

    private function pendingEnvironmentPath(): string
    {
        return $this->runtimeDirectory().DIRECTORY_SEPARATOR.'.env.pending';
    }

    private function attestationPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'activation-readiness.json';
    }
}
