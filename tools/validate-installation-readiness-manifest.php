<?php

declare(strict_types=1);

use App\Infrastructure\Installation\SecureInstallationReadiness;

require __DIR__.'/../apps/web/vendor/autoload.php';

// Author by Lab | zefry

final class InstallationReadinessManifestValidationException extends RuntimeException
{
}

function installationManifestFail(string $message): never
{
    throw new InstallationReadinessManifestValidationException($message);
}

/** @return array<string, mixed> */
function loadInstallationManifest(string $path): array
{
    if (! is_file($path) || ! is_readable($path)) {
        installationManifestFail('Installer-readiness manifest is missing or unreadable.');
    }

    try {
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        installationManifestFail('Installer-readiness manifest JSON is invalid.');
    }

    if (! is_array($decoded)) {
        installationManifestFail('Installer-readiness manifest must be a JSON object.');
    }

    return $decoded;
}

/** @return array{filename: string, size: int, sha256: string} */
function observeInstallationArtifact(string $path): array
{
    if (! is_file($path) || ! is_readable($path)) {
        installationManifestFail('Governed release artifact is missing or unreadable.');
    }

    $size = filesize($path);
    $sha256 = hash_file('sha256', $path);

    if (! is_int($size) || $size <= 0 || ! is_string($sha256)) {
        installationManifestFail('Unable to observe governed release artifact identity.');
    }

    return [
        'filename' => basename($path),
        'size' => $size,
        'sha256' => $sha256,
    ];
}

/**
 * Validate the installer-facing sidecar through the canonical readiness authority.
 * Synthetic host/database inputs below are validator fixtures only; they do not
 * assert anything about a deployment target and never create operational authority.
 *
 * @param array<string, mixed> $manifest
 * @param array{filename: string, size: int, sha256: string} $artifact
 */
function validateInstallationManifest(array $manifest, array $artifact): void
{
    $runtime = $manifest['runtime_requirements'] ?? null;
    $host = $manifest['host_requirements'] ?? null;

    if (! is_array($runtime) || ! is_array($host)) {
        installationManifestFail('Installer-readiness runtime or host policy is unavailable.');
    }

    $extensions = $runtime['php_extensions'] ?? null;
    $phpMin = $runtime['php_min'] ?? null;
    if (! is_array($extensions) || ! is_string($phpMin)) {
        installationManifestFail('Installer-readiness runtime policy is invalid.');
    }

    $requiredCapabilities = $host['required_capabilities'] ?? null;
    if (! is_array($requiredCapabilities)) {
        installationManifestFail('Installer-readiness host capability policy is invalid.');
    }

    $capabilities = [];
    foreach ($requiredCapabilities as $capability) {
        if (is_string($capability)) {
            $capabilities[strtolower(trim($capability))] = true;
        }
    }

    $allowedOs = $host['os_families'] ?? [];
    $allowedInterfaces = $host['web_server_interfaces'] ?? [];
    $os = is_array($allowedOs) && isset($allowedOs[0]) && is_string($allowedOs[0])
        ? $allowedOs[0]
        : '';
    $interface = is_array($allowedInterfaces) && isset($allowedInterfaces[0]) && is_string($allowedInterfaces[0])
        ? $allowedInterfaces[0]
        : '';

    $subject = new SecureInstallationReadiness();
    $assessment = $subject->assess(
        [
            'APP_ENV' => 'production',
            'APP_KEY' => 'base64:installation-sidecar-validator-key',
            'APP_URL' => 'https://installer-validator.oneqay.invalid',
            'APP_DEBUG' => 'false',
            'ONEQAY_DB_DRIVER' => 'mysql',
            'ONEQAY_DB_HOST' => '127.0.0.1',
            'ONEQAY_DB_DATABASE' => 'oneqay_validator',
            'ONEQAY_DB_USERNAME' => 'oneqay_validator',
        ],
        array_values(array_filter($extensions, 'is_string')),
        $phpMin,
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
            'os_family' => $os,
            'web_server_interface' => $interface,
            'memory_bytes' => max(1, (int) ($host['memory_bytes_min'] ?? 0)),
            'execution_time_seconds' => max(1, (int) ($host['execution_time_seconds_min'] ?? 0)),
            'disk_free_bytes' => max(1, (int) ($host['disk_bytes_min'] ?? 0)),
            'capabilities' => $capabilities,
        ],
    );

    foreach ([
        'php_version',
        'php_extensions',
        'host_platform',
        'release_manifest',
        'artifact_integrity',
    ] as $requiredCheck) {
        if (($assessment['checks'][$requiredCheck]['ready'] ?? false) !== true) {
            installationManifestFail('Canonical readiness authority rejected '.$requiredCheck.'.');
        }
    }

    if (($assessment['ready'] ?? false) !== true) {
        installationManifestFail('Canonical readiness authority rejected the installer-readiness sidecar.');
    }
}

try {
    $manifestPath = $argv[1] ?? null;
    $artifactPath = $argv[2] ?? null;

    if (! is_string($manifestPath) || ! is_string($artifactPath)) {
        fwrite(STDERR, "Usage: php tools/validate-installation-readiness-manifest.php <manifest.json> <artifact>\n");
        exit(2);
    }

    $manifest = loadInstallationManifest($manifestPath);
    $artifact = observeInstallationArtifact($artifactPath);
    validateInstallationManifest($manifest, $artifact);

    fwrite(STDOUT, "Installer-readiness manifest verified for ".basename($artifactPath).".\n");
} catch (InstallationReadinessManifestValidationException $exception) {
    fwrite(STDERR, "Installer-readiness manifest validation failed: {$exception->getMessage()}\n");
    exit(1);
}
