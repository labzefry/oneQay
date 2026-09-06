<?php

declare(strict_types=1);

namespace App\Application\Pos;

use RuntimeException;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeDbBindingAttestation
{
    private const MAX_MANIFEST_BYTES = 32768;

    private const MANIFEST_KEYS = [
        'environment_id',
        'exact_running_artifact_sha256',
        'exact_running_source_commit',
        'feature',
        'readiness_attestation_sha256',
        'schema_version',
        'secrets_embedded',
        'selection_fingerprint_sha256',
        'selection_state',
        'trusted_ingestion',
        'runtime_class',
    ];

    private const TRUSTED_INGESTION_KEYS = [
        'ingestion_fingerprint_sha256',
        'run_attempt',
        'run_id',
    ];

    public function __construct(
        private readonly FinalShiftCloseRuntimeDatabaseIdentityReader $databaseIdentityReader,
        private readonly string $manifestPath,
    ) {}

    /**
     * @return array<string,mixed>
     */
    public function attest(): array
    {
        $manifest = $this->readQualifiedManifest();
        $identity = $this->databaseIdentityReader->readPreMigration27Identity();

        return [
            'schema_version' => 1,
            'feature' => 'final-shift-close',
            'binding_state' => 'VERIFIED_SELECTED_TARGET_DATABASE',
            'environment_id' => $manifest['environment_id'],
            'runtime_class' => $manifest['runtime_class'],
            'exact_running_source_commit' => $manifest['exact_running_source_commit'],
            'exact_running_artifact_sha256' => $manifest['exact_running_artifact_sha256'],
            'readiness_attestation_sha256' => $manifest['readiness_attestation_sha256'],
            'selection_fingerprint_sha256' => $manifest['selection_fingerprint_sha256'],
            'trusted_ingestion' => $manifest['trusted_ingestion'],
            'migration27_state' => 'NOT_EXECUTED',
            'database_binding_algorithm' => FinalShiftCloseRuntimeDatabaseIdentity::ALGORITHM,
            'database_binding_sha256' => $identity->fingerprintSha256(),
            'database_identity_source' => 'ACTIVE_APPLICATION_DATABASE_CONNECTION',
            'attestation_mode' => 'READ_ONLY',
            'secrets_embedded' => false,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function readQualifiedManifest(): array
    {
        $path = $this->manifestPath;
        if ($path === '' || ! str_starts_with($path, DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Runtime binding manifest path is invalid.');
        }

        clearstatcache(true, $path);
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            throw new RuntimeException('Runtime binding manifest is unavailable.');
        }

        $size = filesize($path);
        if (! is_int($size) || $size < 2 || $size > self::MAX_MANIFEST_BYTES) {
            throw new RuntimeException('Runtime binding manifest size is invalid.');
        }

        $permissions = fileperms($path);
        if (! is_int($permissions) || (($permissions & 0077) !== 0)) {
            throw new RuntimeException('Runtime binding manifest permissions are invalid.');
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException('Runtime binding manifest cannot be read.');
        }

        $manifest = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        if (! is_array($manifest)) {
            throw new RuntimeException('Runtime binding manifest must be an object.');
        }

        $keys = array_keys($manifest);
        sort($keys, SORT_STRING);
        $expected = self::MANIFEST_KEYS;
        sort($expected, SORT_STRING);
        if ($keys !== $expected) {
            throw new RuntimeException('Runtime binding manifest field set is invalid.');
        }

        $ingestion = $manifest['trusted_ingestion'] ?? null;
        if (! is_array($ingestion)) {
            throw new RuntimeException('Runtime binding trusted ingestion is invalid.');
        }
        $ingestionKeys = array_keys($ingestion);
        sort($ingestionKeys, SORT_STRING);
        if ($ingestionKeys !== self::TRUSTED_INGESTION_KEYS) {
            throw new RuntimeException('Runtime binding trusted ingestion field set is invalid.');
        }

        $runtime = $manifest['runtime_class'] ?? null;
        if (! is_string($runtime) || preg_match('/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D', $runtime) !== 1) {
            throw new RuntimeException('Runtime binding runtime class is invalid.');
        }
        if (in_array($runtime, ['local', 'test', 'testing', 'ci', 'preview', 'synthetic-preview', 'production', 'prod'], true)) {
            throw new RuntimeException('Runtime binding runtime class is not eligible.');
        }

        $environment = $manifest['environment_id'] ?? null;
        if (! is_string($environment) || preg_match('/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D', $environment) !== 1) {
            throw new RuntimeException('Runtime binding environment identity is invalid.');
        }

        $source = $manifest['exact_running_source_commit'] ?? null;
        $artifact = $manifest['exact_running_artifact_sha256'] ?? null;
        $readiness = $manifest['readiness_attestation_sha256'] ?? null;
        $selection = $manifest['selection_fingerprint_sha256'] ?? null;
        $ingestionFingerprint = $ingestion['ingestion_fingerprint_sha256'] ?? null;

        if (! is_string($source) || preg_match('/\A[0-9a-f]{40}\z/D', $source) !== 1) {
            throw new RuntimeException('Runtime binding source commit is invalid.');
        }
        foreach ([$artifact, $readiness, $selection, $ingestionFingerprint] as $sha256) {
            if (! is_string($sha256) || preg_match('/\A[0-9a-f]{64}\z/D', $sha256) !== 1) {
                throw new RuntimeException('Runtime binding SHA-256 provenance is invalid.');
            }
        }

        if (($manifest['schema_version'] ?? null) !== 1
            || ($manifest['feature'] ?? null) !== 'final-shift-close'
            || ($manifest['selection_state'] ?? null) !== 'SELECTED_NOT_AUTHORIZED'
            || ($manifest['secrets_embedded'] ?? null) !== false
            || ! is_int($ingestion['run_id'] ?? null)
            || ($ingestion['run_id'] ?? 0) < 1
            || ! is_int($ingestion['run_attempt'] ?? null)
            || ($ingestion['run_attempt'] ?? 0) < 1
        ) {
            throw new RuntimeException('Runtime binding manifest provenance is invalid.');
        }

        return $manifest;
    }
}
