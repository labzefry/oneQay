<?php

declare(strict_types=1);

use App\Infrastructure\Installation\SecureInstallationReadiness;

require __DIR__.'/../apps/web/vendor/autoload.php';

// Author by Lab | zefry

final class InstallationReadinessManifestBuildException extends RuntimeException
{
}

function failInstallationManifestBuild(string $message): never
{
    throw new InstallationReadinessManifestBuildException($message);
}

/**
 * @return array{
 *   filename: string,
 *   size: int,
 *   sha256: string
 * }
 */
function observeGovernedArtifact(string $artifactPath): array
{
    if (! is_file($artifactPath) || ! is_readable($artifactPath)) {
        failInstallationManifestBuild('Governed release artifact is missing or unreadable.');
    }

    $size = filesize($artifactPath);
    $sha256 = hash_file('sha256', $artifactPath);

    if (! is_int($size) || $size <= 0 || ! is_string($sha256)) {
        failInstallationManifestBuild('Governed release artifact identity cannot be observed.');
    }

    return [
        'filename' => basename($artifactPath),
        'size' => $size,
        'sha256' => $sha256,
    ];
}

/**
 * @param array{filename: string, size: int, sha256: string} $artifact
 * @return array<string, mixed>
 */
function buildInstallationManifest(
    array $artifact,
    string $sourceSha,
    string $releaseId,
    string $buildProvenance
): array {
    $extensions = [
        'ctype',
        'curl',
        'dom',
        'fileinfo',
        'filter',
        'hash',
        'mbstring',
        'openssl',
        'pdo',
        'session',
        'tokenizer',
        'xml',
    ];

    return [
        'schema_version' => 1,
        'product' => 'oneQay',
        'repository' => 'labzefry/oneQay',
        'release_id' => $releaseId,
        'release_channel' => 'preview',
        'source_commit' => $sourceSha,
        'artifact_filename' => $artifact['filename'],
        'artifact_type' => 'tar.gz',
        'artifact_size' => $artifact['size'],
        'artifact_sha256' => $artifact['sha256'],
        'runtime_requirements' => [
            'php_min' => '8.2.0',
            'php_extensions' => $extensions,
        ],
        'host_requirements' => [
            'os_families' => ['linux'],
            'web_server_interfaces' => ['fpm-fcgi', 'cgi-fcgi', 'apache2handler'],
            'memory_bytes_min' => 268435456,
            'execution_time_seconds_min' => 60,
            'disk_bytes_min' => 1073741824,
            'required_capabilities' => [
                'https',
                'dns',
                'time_sync',
                'outbound_allowlist',
                'scheduler',
                'archive',
                'temp_directory',
                'required_tools',
            ],
        ],
        'compatibility_policy' => [
            'release_version' => '0.1.0-preview.'.substr($sourceSha, 0, 12),
            'build_provenance_ref' => $buildProvenance,
            'supported_current_version_range' => [
                'min' => '0.0.0',
                'max' => '0.1.0-preview.'.substr($sourceSha, 0, 12),
            ],
            'deployment_compatibility' => 'TECHNICAL_PREVIEW_V1',
            'rollback_compatibility' => 'NO_SCHEMA_CHANGE_ROLLBACK_SAFE',
            'public_bootstrap_layout_compatibility' => 'M7_5_PREVIEW_PUBLIC_SURFACE_V1',
            'release_notes_reference' => 'RELEASE.md#release-lifecycle',
        ],
        'migration_classification' => 'NO_SCHEMA_CHANGE',
        'attribution' => 'Lab | zefry',
    ];
}

/**
 * Build-time validation uses deterministic fixtures only to prove that the
 * manifest policy is accepted by SecureInstallationReadiness. These fixtures
 * are not observations of a deployment target and grant no operational state.
 *
 * @param array<string, mixed> $manifest
 * @param array{filename: string, size: int, sha256: string} $artifact
 */
function validateInstallationManifest(array $manifest, array $artifact): void
{
    $runtime = is_array($manifest['runtime_requirements'] ?? null)
        ? $manifest['runtime_requirements']
        : [];
    $extensions = is_array($runtime['php_extensions'] ?? null)
        ? array_values(array_filter($runtime['php_extensions'], 'is_string'))
        : [];

    $hostRequirements = is_array($manifest['host_requirements'] ?? null)
        ? $manifest['host_requirements']
        : [];
    $capabilities = [];
    foreach (($hostRequirements['required_capabilities'] ?? []) as $capability) {
        if (is_string($capability)) {
            $capabilities[strtolower(trim($capability))] = true;
        }
    }

    $subject = new SecureInstallationReadiness();
    $assessment = $subject->assess(
        [
            'APP_ENV' => 'production',
            'APP_KEY' => 'base64:trusted-build-validator-key',
            'APP_URL' => 'https://build-validator.oneqay.invalid',
            'APP_DEBUG' => 'false',
            'ONEQAY_DB_DRIVER' => 'mysql',
            'ONEQAY_DB_HOST' => '127.0.0.1',
            'ONEQAY_DB_DATABASE' => 'oneqay_validator',
            'ONEQAY_DB_USERNAME' => 'oneqay_validator',
        ],
        $extensions,
        '8.3.0',
        [
            'bootstrap/cache' => true,
            'storage/framework/cache' => true,
            'storage/framework/sessions' => true,
            'storage/framework/views' => true,
            'storage/logs' => true,
        ],
        $manifest,
        $artifact,
        [
            'connected' => true,
            'engine' => 'mysql',
            'server_version' => '8.0.36',
            'charset' => 'utf8mb4',
            'timezone' => '+00:00',
            'schema_state' => 'empty',
            'least_privilege' => true,
        ],
        [
            'os_family' => 'linux',
            'web_server_interface' => 'fpm-fcgi',
            'memory_bytes' => 536870912,
            'execution_time_seconds' => 120,
            'disk_free_bytes' => 5368709120,
            'capabilities' => $capabilities,
        ],
    );

    foreach ([
        'php_version',
        'php_extensions',
        'host_platform',
        'release_manifest',
        'artifact_integrity',
    ] as $check) {
        if (($assessment['checks'][$check]['ready'] ?? false) !== true) {
            failInstallationManifestBuild('Canonical readiness authority rejected '.$check.'.');
        }
    }

    if (($assessment['ready'] ?? false) !== true) {
        failInstallationManifestBuild('Canonical readiness authority rejected the installer-readiness manifest.');
    }

    $tampered = $artifact;
    $tampered['sha256'] = str_repeat('0', 64);
    $tamperedAssessment = $subject->assess(
        [
            'APP_ENV' => 'production',
            'APP_KEY' => 'base64:trusted-build-validator-key',
            'APP_URL' => 'https://build-validator.oneqay.invalid',
            'APP_DEBUG' => 'false',
            'ONEQAY_DB_DRIVER' => 'mysql',
            'ONEQAY_DB_HOST' => '127.0.0.1',
            'ONEQAY_DB_DATABASE' => 'oneqay_validator',
            'ONEQAY_DB_USERNAME' => 'oneqay_validator',
        ],
        $extensions,
        '8.3.0',
        [
            'bootstrap/cache' => true,
            'storage/framework/cache' => true,
            'storage/framework/sessions' => true,
            'storage/framework/views' => true,
            'storage/logs' => true,
        ],
        $manifest,
        $tampered,
        [
            'connected' => true,
            'engine' => 'mysql',
            'server_version' => '8.0.36',
            'charset' => 'utf8mb4',
            'timezone' => '+00:00',
            'schema_state' => 'empty',
            'least_privilege' => true,
        ],
        [
            'os_family' => 'linux',
            'web_server_interface' => 'fpm-fcgi',
            'memory_bytes' => 536870912,
            'execution_time_seconds' => 120,
            'disk_free_bytes' => 5368709120,
            'capabilities' => $capabilities,
        ],
    );

    if (($tamperedAssessment['checks']['artifact_integrity']['ready'] ?? true) !== false) {
        failInstallationManifestBuild('Tampered artifact digest did not fail closed.');
    }
}

try {
    [$script, $artifactPath, $sourceSha, $releaseId, $buildProvenance, $outputPath] = array_pad($argv, 6, null);

    foreach ([
        'artifact path' => $artifactPath,
        'source SHA' => $sourceSha,
        'release ID' => $releaseId,
        'build provenance' => $buildProvenance,
        'output path' => $outputPath,
    ] as $label => $value) {
        if (! is_string($value) || trim($value) === '') {
            failInstallationManifestBuild('Missing '.$label.'.');
        }
    }

    if (preg_match('/\A[0-9a-f]{40}\z/i', $sourceSha) !== 1) {
        failInstallationManifestBuild('Source SHA must be an exact 40-character Git commit.');
    }

    if (preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId) !== 1
        || strtolower(substr($sourceSha, 0, 12)) !== strtolower(substr($releaseId, -12))) {
        failInstallationManifestBuild('Release ID is not bound to the exact source commit.');
    }

    $artifact = observeGovernedArtifact($artifactPath);
    $manifest = buildInstallationManifest($artifact, strtolower($sourceSha), $releaseId, $buildProvenance);
    validateInstallationManifest($manifest, $artifact);

    $directory = dirname($outputPath);
    if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
        failInstallationManifestBuild('Installer-readiness manifest directory cannot be created.');
    }

    $encoded = json_encode(
        $manifest,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    ).PHP_EOL;

    if (file_put_contents($outputPath, $encoded, LOCK_EX) === false) {
        failInstallationManifestBuild('Installer-readiness manifest cannot be written.');
    }

    fwrite(
        STDOUT,
        sprintf(
            "Installer-readiness manifest built and validated for %s (%d bytes, sha256 %s).\n",
            $artifact['filename'],
            $artifact['size'],
            $artifact['sha256']
        )
    );
} catch (InstallationReadinessManifestBuildException $exception) {
    fwrite(STDERR, "Installer-readiness manifest build failed: {$exception->getMessage()}\n");
    exit(1);
} catch (Throwable $exception) {
    fwrite(STDERR, "Installer-readiness manifest build failed closed.\n");
    exit(1);
}
