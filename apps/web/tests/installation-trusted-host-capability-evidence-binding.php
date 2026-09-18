<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('h', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'https://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED' => 'false',
    'ONEQAY_DB_DATABASE' => '',
    'ONEQAY_DB_USERNAME' => '',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint184 trusted host capability evidence regression failed: '.$message);
    }
};

$releaseDir = dirname(__DIR__, 3).DIRECTORY_SEPARATOR.'release';
$manifestPath = $releaseDir.DIRECTORY_SEPARATOR.'manifest.json';
$evidencePath = $releaseDir.DIRECTORY_SEPARATOR.'host-platform-evidence.json';
$artifactName = 'oneqay-sprint184-host-evidence-fixture.tar.gz';
$artifactPath = $releaseDir.DIRECTORY_SEPARATOR.$artifactName;

$original = [];
foreach ([$manifestPath, $evidencePath, $artifactPath] as $path) {
    $original[$path] = is_file($path) ? file_get_contents($path) : null;
}

$restore = static function () use ($original): void {
    foreach ($original as $path => $content) {
        if (is_string($content)) {
            file_put_contents($path, $content);
        } elseif (is_file($path)) {
            unlink($path);
        }
    }
};

try {
    $artifactBytes = "oneQay Sprint184 trusted host capability evidence fixture\n";
    file_put_contents($artifactPath, $artifactBytes);
    $artifactSize = filesize($artifactPath);
    $artifactSha256 = hash_file('sha256', $artifactPath);
    $assert(is_int($artifactSize) && $artifactSize > 0, 'fixture artifact size is invalid.');
    $assert(is_string($artifactSha256), 'fixture artifact digest is invalid.');

    $sourceCommit = str_repeat('b', 40);
    $releaseId = 'sprint184-host-evidence-fixture';
    $extensions = array_values(array_filter(
        ['ctype', 'fileinfo', 'filter', 'hash', 'openssl', 'pdo', 'session', 'tokenizer'],
        static fn (string $extension): bool => extension_loaded($extension),
    ));
    $assert($extensions !== [], 'no stable PHP extension fixture is available.');

    $manifest = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'repository' => 'labzefry/oneQay',
        'release_id' => $releaseId,
        'release_channel' => 'preview',
        'source_commit' => $sourceCommit,
        'artifact_filename' => $artifactName,
        'artifact_type' => 'tar.gz',
        'artifact_size' => $artifactSize,
        'artifact_sha256' => $artifactSha256,
        'runtime_requirements' => [
            'php_min' => '8.2.0',
            'php_extensions' => $extensions,
        ],
        'host_requirements' => [
            'os_families' => [strtolower(PHP_OS_FAMILY)],
            'web_server_interfaces' => [strtolower(PHP_SAPI)],
            'memory_bytes_min' => 1,
            'execution_time_seconds_min' => 1,
            'disk_bytes_min' => 1,
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
            'release_version' => '0.1.0-preview.184',
            'build_provenance_ref' => 'github-actions:sprint184-host-evidence',
            'supported_current_version_range' => [
                'min' => '0.0.0',
                'max' => '0.1.0-preview.184',
            ],
            'deployment_compatibility' => 'TECHNICAL_PREVIEW_V1',
            'rollback_compatibility' => 'NO_SCHEMA_CHANGE_ROLLBACK_SAFE',
            'public_bootstrap_layout_compatibility' => 'M7_5_PREVIEW_PUBLIC_SURFACE_V1',
            'release_notes_reference' => 'RELEASE.md#release-lifecycle',
        ],
        'migration_classification' => 'NO_SCHEMA_CHANGE',
        'attribution' => 'Lab | zefry',
    ];
    file_put_contents(
        $manifestPath,
        json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
    );

    $hostEvidence = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'repository' => 'labzefry/oneQay',
        'release_id' => $releaseId,
        'source_commit' => $sourceCommit,
        'artifact_sha256' => $artifactSha256,
        'host_binding' => [
            'os_family' => strtolower(PHP_OS_FAMILY),
            'web_server_interface' => strtolower(PHP_SAPI),
        ],
        'capabilities' => [
            'time_sync' => ['ready' => true, 'evidence_ref' => 'operator-evidence:time-sync:sprint184'],
            'outbound_allowlist' => ['ready' => true, 'evidence_ref' => 'operator-evidence:outbound:sprint184'],
            'scheduler' => ['ready' => true, 'evidence_ref' => 'operator-evidence:scheduler:sprint184'],
            'required_tools' => ['ready' => true, 'evidence_ref' => 'operator-evidence:tools:sprint184'],
        ],
        'attribution' => 'Lab | zefry',
    ];
    file_put_contents(
        $evidencePath,
        json_encode($hostEvidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
    );

    $request = Request::create('/system/update', 'GET', server: [
        'HTTPS' => 'on',
        'HTTP_ACCEPT' => 'text/html',
        'HTTP_X_INERTIA' => 'true',
        'HTTP_X_INERTIA_VERSION' => '',
    ]);
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);

    $assert($response->getStatusCode() === 200, 'system update page did not render.');
    $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $props = $payload['props'] ?? null;
    $assert(is_array($props), 'Inertia props are missing.');

    $preflight = $props['installation_preflight'] ?? null;
    $assert(is_array($preflight), 'installation preflight props are missing.');
    $assert(($preflight['evidence']['host_platform'] ?? null) === 'SERVER_PLUS_TRUSTED_ATTESTATION', 'trusted host evidence was not surfaced.');
    $assert(($preflight['checks']['host_platform']['ready'] ?? null) === true, 'trusted host evidence did not satisfy host-platform readiness.');
    $assert(($preflight['actions_exposed'] ?? null) === false, 'trusted evidence must not expose installation actions.');
    $assert(($props['status']['install'] ?? null) === 'DISABLED', 'trusted evidence must not enable installer authority.');
    $assert(($props['status']['deployment_authorized'] ?? null) === false, 'trusted evidence must not grant deployment authority.');

    $encoded = json_encode($props, JSON_THROW_ON_ERROR);
    foreach ([
        'operator-evidence:time-sync:sprint184',
        'operator-evidence:outbound:sprint184',
        'operator-evidence:scheduler:sprint184',
        'operator-evidence:tools:sprint184',
    ] as $privateReference) {
        $assert(! str_contains($encoded, $privateReference), 'private evidence reference leaked into page props.');
    }

    $hostEvidence['artifact_sha256'] = str_repeat('0', 64);
    file_put_contents(
        $evidencePath,
        json_encode($hostEvidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
    );

    $tamperedRequest = Request::create('/system/update', 'GET', server: [
        'HTTPS' => 'on',
        'HTTP_ACCEPT' => 'text/html',
        'HTTP_X_INERTIA' => 'true',
        'HTTP_X_INERTIA_VERSION' => '',
    ]);
    $tamperedResponse = $kernel->handle($tamperedRequest);
    $kernel->terminate($tamperedRequest, $tamperedResponse);

    $tamperedPayload = json_decode((string) $tamperedResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $tamperedPreflight = $tamperedPayload['props']['installation_preflight'] ?? null;
    $assert(is_array($tamperedPreflight), 'tampered preflight props are missing.');
    $assert(($tamperedPreflight['evidence']['host_platform'] ?? null) === 'SERVER_OBSERVED_PARTIAL', 'tampered evidence did not fail closed.');
    $assert(($tamperedPreflight['checks']['host_platform']['ready'] ?? null) === false, 'tampered evidence unexpectedly satisfied host readiness.');

    $controller = (string) file_get_contents(__DIR__.'/../app/Delivery/Http/SystemUpdate/SystemUpdatePageController.php');
    $evidenceSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Installation/TrustedInstallationHostCapabilityEvidence.php');
    $page = (string) file_get_contents(__DIR__.'/../resources/js/pages/System/UpdateDeployment.vue');

    foreach ([
        'TrustedInstallationHostCapabilityEvidence',
        'host-platform-evidence.json',
        'SERVER_PLUS_TRUSTED_ATTESTATION',
    ] as $needle) {
        $assert(str_contains($controller.$page, $needle), 'delivery binding is missing '.$needle);
    }

    foreach ([
        'curl_exec',
        'curl_multi_exec',
        'fsockopen',
        'stream_socket_client',
        'shell_exec',
        'proc_open',
        'passthru(',
        'system(',
        'exec(',
        'file_put_contents',
        'fwrite(',
        'unlink(',
        'rename(',
        'chmod(',
        'chown(',
        'mkdir(',
        'Artisan::call',
    ] as $forbidden) {
        $assert(! str_contains($controller.$evidenceSource, $forbidden), 'read-only trusted evidence source contains forbidden primitive '.$forbidden);
    }

    echo "Sprint184 trusted installation host capability evidence binding regression passed.\n";
} finally {
    $restore();
}
