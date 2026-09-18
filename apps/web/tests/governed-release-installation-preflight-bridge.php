<?php

declare(strict_types=1);

use App\Infrastructure\Installation\SecureInstallationReadiness;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry

$root = dirname(__DIR__, 2);
$builderPath = $root.'/tools/build-m7-5-preview-release.sh';
$validatorPath = $root.'/tools/validate-installation-readiness-manifest.php';
$workflowPath = $root.'/.github/workflows/m7-5-preview-release-artifact.yml';

foreach ([$builderPath, $validatorPath, $workflowPath] as $path) {
    if (! is_file($path)) {
        throw new RuntimeException('Sprint182 bridge source is missing: '.$path);
    }
}

$builder = (string) file_get_contents($builderPath);
$validator = (string) file_get_contents($validatorPath);
$workflow = (string) file_get_contents($workflowPath);

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint182 governed release installation preflight bridge regression failed: '.$message);
    }
};

foreach ([
    'installation_manifest_path="dist/manifest.json"',
    '"schema_version": 1',
    '"product": "oneQay"',
    '"repository": "labzefry/oneQay"',
    '"release_channel": "preview"',
    '"source_commit": "\${source_sha}"',
    '"artifact_filename": "\${archive_name}"',
    '"artifact_size": \${artifact_size}',
    '"artifact_sha256": "\${artifact_sha256}"',
    '"php_min": "8.2.0"',
    '"memory_bytes_min": 268435456',
    '"execution_time_seconds_min": 60',
    '"disk_bytes_min": 1073741824',
    '"deployment_compatibility": "TECHNICAL_PREVIEW_V1"',
    '"rollback_compatibility": "NO_SCHEMA_CHANGE_ROLLBACK_SAFE"',
    '"public_bootstrap_layout_compatibility": "M7_5_PREVIEW_PUBLIC_SURFACE_V1"',
    '"migration_classification": "NO_SCHEMA_CHANGE"',
    '"attribution": "Lab | zefry"',
    'validate-installation-readiness-manifest.php',
    'installation_manifest_path=$installation_manifest_path',
] as $marker) {
    $assert(str_contains($builder, $marker), 'builder marker missing: '.$marker);
}

foreach ([
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
] as $extension) {
    $assert(str_contains($builder, '"'.$extension.'"'), 'runtime extension policy missing '.$extension);
}

foreach ([
    'https',
    'dns',
    'time_sync',
    'outbound_allowlist',
    'scheduler',
    'archive',
    'temp_directory',
    'required_tools',
] as $capability) {
    $assert(str_contains($builder, '"'.$capability.'"'), 'host capability policy missing '.$capability);
}

foreach ([
    'SecureInstallationReadiness',
    "'release_manifest'",
    "'artifact_integrity'",
    'observeInstallationArtifact',
    'Synthetic host/database inputs below are validator fixtures only',
] as $marker) {
    $assert(str_contains($validator, $marker), 'validator contract missing '.$marker);
}

$assert(
    substr_count($workflow, 'steps.package.outputs.installation_manifest_path') >= 3,
    'M7.5 workflow must validate, reproduce, and upload the installer sidecar.',
);
$assert(
    str_contains($workflow, 'validate-installation-readiness-manifest.php'),
    'M7.5 workflow does not validate the installer sidecar.',
);

$extensions = ['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pdo', 'session', 'tokenizer', 'xml'];
$artifact = [
    'filename' => 'm75-preview-aaaaaaaaaaaa.tar.gz',
    'size' => 4096,
    'sha256' => str_repeat('b', 64),
];
$manifest = [
    'schema_version' => 1,
    'product' => 'oneQay',
    'repository' => 'labzefry/oneQay',
    'release_id' => 'm75-preview-aaaaaaaaaaaa',
    'release_channel' => 'preview',
    'source_commit' => str_repeat('a', 40),
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
        'release_version' => '0.1.0-preview.aaaaaaaaaaaa',
        'build_provenance_ref' => 'local://source/'.str_repeat('a', 40),
        'supported_current_version_range' => [
            'min' => '0.0.0',
            'max' => '0.1.0-preview.aaaaaaaaaaaa',
        ],
        'deployment_compatibility' => 'TECHNICAL_PREVIEW_V1',
        'rollback_compatibility' => 'NO_SCHEMA_CHANGE_ROLLBACK_SAFE',
        'public_bootstrap_layout_compatibility' => 'M7_5_PREVIEW_PUBLIC_SURFACE_V1',
        'release_notes_reference' => 'RELEASE.md#release-lifecycle',
    ],
    'migration_classification' => 'NO_SCHEMA_CHANGE',
    'attribution' => 'Lab | zefry',
];

$subject = new SecureInstallationReadiness();
$environment = [
    'APP_ENV' => 'production',
    'APP_KEY' => 'base64:sprint182-regression-key',
    'APP_URL' => 'https://sprint182.oneqay.invalid',
    'APP_DEBUG' => 'false',
    'ONEQAY_DB_DRIVER' => 'mysql',
    'ONEQAY_DB_HOST' => '127.0.0.1',
    'ONEQAY_DB_DATABASE' => 'oneqay',
    'ONEQAY_DB_USERNAME' => 'oneqay',
];
$writable = [
    'bootstrap/cache' => true,
    'storage/framework/cache' => true,
    'storage/framework/sessions' => true,
    'storage/framework/views' => true,
    'storage/logs' => true,
];
$database = [
    'connected' => true,
    'engine' => 'mysql',
    'server_version' => '8.0.36',
    'charset' => 'utf8mb4',
    'timezone' => '+00:00',
    'schema_state' => 'empty',
    'least_privilege' => true,
];
$host = [
    'os_family' => 'linux',
    'web_server_interface' => 'fpm-fcgi',
    'memory_bytes' => 268435456,
    'execution_time_seconds' => 60,
    'disk_free_bytes' => 1073741824,
    'capabilities' => [
        'https' => true,
        'dns' => true,
        'time_sync' => true,
        'outbound_allowlist' => true,
        'scheduler' => true,
        'archive' => true,
        'temp_directory' => true,
        'required_tools' => true,
    ],
];

$ready = $subject->assess($environment, $extensions, '8.2.0', $writable, $manifest, $artifact, $database, $host);
$assert($ready['ready'] === true, 'canonical readiness authority rejected the bridge fixture.');
$assert($ready['checks']['release_manifest']['ready'] === true, 'release manifest projection was rejected.');
$assert($ready['checks']['artifact_integrity']['ready'] === true, 'artifact binding was rejected.');

$tamperedArtifact = $artifact;
$tamperedArtifact['sha256'] = str_repeat('c', 64);
$tampered = $subject->assess($environment, $extensions, '8.2.0', $writable, $manifest, $tamperedArtifact, $database, $host);
$assert($tampered['ready'] === false, 'tampered artifact unexpectedly passed readiness.');
$assert($tampered['checks']['artifact_integrity']['ready'] === false, 'tampered artifact integrity did not fail closed.');

echo "Sprint182 governed release installation preflight bridge regression passed.\n";
