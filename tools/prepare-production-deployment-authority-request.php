<?php

declare(strict_types=1);

// Author by Lab | zefry

final class ProductionDeploymentRequestException extends RuntimeException {}

function prodReqFail(string $code): never { throw new ProductionDeploymentRequestException($code); }

/** @return array{v:array<string,mixed>,raw:string} */
function prodReqLoad(string $path): array
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) prodReqFail('input_unavailable');
    $raw = file_get_contents($path);
    if (! is_string($raw) || strlen($raw) < 2 || strlen($raw) > 1048576) prodReqFail('input_invalid');
    try { $value = json_decode($raw, true, 64, JSON_THROW_ON_ERROR); }
    catch (JsonException) { prodReqFail('json_invalid'); }
    if (! is_array($value) || array_is_list($value)) prodReqFail('shape_invalid');
    return ['v' => $value, 'raw' => $raw];
}

function prodReqEq(mixed $actual, mixed $expected, string $code): void { if ($actual !== $expected) prodReqFail($code); }
function prodReqBool(mixed $actual, bool $expected, string $code): void { if (! is_bool($actual) || $actual !== $expected) prodReqFail($code); }
function prodReqPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) prodReqFail($code);
    return $value;
}

/** @return array<string,mixed>|list<mixed> */
function prodReqCanonicalize(array $value): array
{
    if (array_is_list($value)) {
        return array_map(static fn (mixed $item): mixed => is_array($item) ? prodReqCanonicalize($item) : $item, $value);
    }
    ksort($value, SORT_STRING);
    foreach ($value as $key => $item) if (is_array($item)) $value[$key] = prodReqCanonicalize($item);
    return $value;
}

function prodReqCanonical(array $value): string
{
    return json_encode(prodReqCanonicalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

function prodReqSafePath(mixed $value, string $code): string
{
    if (! is_string($value)
        || $value === '' || $value === '/' || strlen($value) > 4096
        || ! str_starts_with($value, '/') || str_contains($value, "\0") || str_contains($value, '\\')
        || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $value) === 1 || preg_match('#//+#', $value) === 1
    ) prodReqFail($code);
    return rtrim($value, '/');
}

function prodReqNested(string $child, string $parent, string $code): void
{
    if (! str_starts_with($child.'/', $parent.'/')) prodReqFail($code);
}

/** @return array{url:string,path:string} */
function prodReqHealth(array $health): array
{
    prodReqEq($health['path'] ?? null, '/health/live', 'target_health_path_invalid');
    $url = $health['url'] ?? null;
    if (! is_string($url)) prodReqFail('target_health_url_invalid');
    $parts = parse_url($url);
    if (! is_array($parts)
        || ($parts['scheme'] ?? null) !== 'https'
        || ! is_string($parts['host'] ?? null) || $parts['host'] === ''
        || ($parts['path'] ?? '') !== '/health/live'
        || array_key_exists('user', $parts) || array_key_exists('pass', $parts)
        || array_key_exists('query', $parts) || array_key_exists('fragment', $parts)
    ) prodReqFail('target_health_url_invalid');
    return ['url' => $url, 'path' => '/health/live'];
}

/** @return array<string,mixed> */
function prodReqPrepare(string $manifestPath, string $artifactPath, string $stagingEvidencePath, string $targetPath): array
{
    $manifestLoaded = prodReqLoad($manifestPath);
    $manifest = $manifestLoaded['v'];
    $source = prodReqPattern($manifest['source']['commit_sha'] ?? null, '/\A[0-9a-f]{40}\z/', 'manifest_source_invalid');
    $releaseId = 'production-'.substr($source, 0, 12);
    prodReqEq($manifest['release']['id'] ?? null, $releaseId, 'manifest_release_invalid');
    prodReqEq($manifest['release']['channel'] ?? null, 'PRODUCTION_CANDIDATE', 'manifest_channel_invalid');
    prodReqBool($manifest['release']['production'] ?? null, true, 'manifest_production_invalid');
    prodReqEq($manifest['runtime']['required_runtime_class'] ?? null, 'production', 'manifest_runtime_invalid');
    prodReqBool($manifest['runtime']['business_runtime_activation_ready'] ?? null, false, 'manifest_business_runtime_must_be_disabled');
    prodReqEq($manifest['runtime']['dark_deploy_health_endpoint'] ?? null, '/health/live', 'manifest_dark_health_invalid');
    prodReqBool($manifest['migration']['execution_authorized'] ?? null, false, 'manifest_migration_forbidden');
    prodReqBool($manifest['promotion_gate']['verified_durable_staging_evidence_required'] ?? null, true, 'staging_evidence_gate_missing');
    prodReqBool($manifest['promotion_gate']['same_source_commit_required'] ?? null, true, 'same_source_gate_missing');
    prodReqBool($manifest['promotion_gate']['production_traffic_activation_authorized'] ?? null, false, 'manifest_traffic_activation_forbidden');
    $artifactSha = prodReqPattern($manifest['artifact']['sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'manifest_artifact_invalid');
    if (! is_file($artifactPath) || is_link($artifactPath) || ! hash_equals($artifactSha, (string) hash_file('sha256', $artifactPath))) {
        prodReqFail('artifact_hash_mismatch');
    }

    $stagingLoaded = prodReqLoad($stagingEvidencePath);
    $staging = $stagingLoaded['v'];
    prodReqEq($staging['evidence_state'] ?? null, 'DEPLOYED_VERIFIED_NOT_SELECTED', 'staging_evidence_state_invalid');
    prodReqEq($staging['runtime_class'] ?? null, 'durable-staging', 'staging_runtime_invalid');
    prodReqEq($staging['source_commit'] ?? null, $source, 'staging_source_mismatch');
    foreach ([
        'preflight_passed','previous_active_release_preserved','immutable_release_extracted','public_document_root_verified',
        'external_runtime_configuration_bound','provenance_readback_verified','configuration_read_before_write_verified',
        'configuration_read_after_write_verified','non_mutating_health_attestation_verified','rollback_path_verified',
    ] as $field) {
        prodReqBool($staging['verification'][$field] ?? null, true, 'staging_verification_'.$field.'_required');
    }
    prodReqBool($staging['secrets_embedded'] ?? null, false, 'staging_secret_forbidden');

    $target = prodReqLoad($targetPath)['v'];
    prodReqEq($target['schema_version'] ?? null, 1, 'target_schema_invalid');
    prodReqEq($target['target_state'] ?? null, 'PRODUCTION_TARGET_CANDIDATE', 'target_state_invalid');
    $targetClass = prodReqPattern($target['target_class'] ?? null, '/\A(?:OPERATOR_MANAGED_FILESYSTEM|CPANEL_NO_SSH)\z/', 'target_class_invalid');
    $channel = prodReqPattern($target['execution_channel'] ?? null, '/\A(?:PHP_CLI|CPANEL_CRON_PHP_CLI_NO_SSH)\z/', 'target_channel_invalid');
    if ($targetClass === 'CPANEL_NO_SSH' && $channel !== 'CPANEL_CRON_PHP_CLI_NO_SSH') prodReqFail('target_channel_class_mismatch');
    $environment = prodReqPattern($target['environment_id'] ?? null, '/\A[a-z0-9][a-z0-9-]{2,62}\z/', 'environment_invalid');
    prodReqEq($target['runtime_class'] ?? null, 'production', 'target_runtime_invalid');
    prodReqBool($target['production'] ?? null, true, 'target_production_invalid');
    prodReqBool($target['production_data_allowed'] ?? null, true, 'target_data_invalid');
    prodReqBool($target['isolated_environment'] ?? null, true, 'target_isolation_invalid');
    prodReqEq($target['release_binding']['release_id'] ?? null, $releaseId, 'target_release_mismatch');
    prodReqEq($target['release_binding']['source_commit'] ?? null, $source, 'target_source_mismatch');
    prodReqEq($target['release_binding']['artifact_sha256'] ?? null, $artifactSha, 'target_artifact_mismatch');

    $deploymentRoot = prodReqSafePath($target['filesystem']['deployment_root'] ?? null, 'target_deployment_root_invalid');
    $releaseRoot = prodReqSafePath($target['filesystem']['release_root'] ?? null, 'target_release_root_invalid');
    $sharedRoot = prodReqSafePath($target['filesystem']['shared_runtime_root'] ?? null, 'target_shared_root_invalid');
    $activePointer = prodReqSafePath($target['filesystem']['active_release_pointer'] ?? null, 'target_active_pointer_invalid');
    $documentRoot = prodReqSafePath($target['filesystem']['document_root'] ?? null, 'target_document_root_invalid');
    foreach ([$releaseRoot, $sharedRoot, $activePointer] as $path) prodReqNested($path, $deploymentRoot, 'target_filesystem_escape');
    prodReqEq($documentRoot, $activePointer.'/apps/web/public', 'target_document_root_shape_invalid');
    prodReqHealth(is_array($target['health'] ?? null) ? $target['health'] : []);

    foreach ([
        'durable_database_persistence','durable_session','authorization','transaction_durability','pos_durability',
        'authenticated_configuration_channel','read_before_write','read_after_write','non_mutating_health_attestation','verified_rollback',
    ] as $capability) {
        prodReqBool($target['capabilities'][$capability] ?? null, true, 'target_capability_'.$capability.'_required');
    }
    prodReqBool($target['configuration']['secret_values_embedded'] ?? null, false, 'target_secret_forbidden');
    foreach ([
        'ONEQAY_RUNTIME_CLASS','ONEQAY_RUNNING_SOURCE_COMMIT','ONEQAY_RUNNING_ARTIFACT_SHA256',
        'ONEQAY_PRODUCTION_ENVIRONMENT_ID','ONEQAY_PRODUCTION_ATTESTATION_TOKEN',
    ] as $binding) {
        prodReqBool($target['configuration']['required_binding_presence'][$binding] ?? null, true, 'target_binding_'.$binding.'_required');
    }
    prodReqEq($target['attribution'] ?? null, 'Lab | zefry', 'target_attribution_invalid');

    $targetSha = hash('sha256', prodReqCanonical($target));
    $stagingSha = hash('sha256', $stagingLoaded['raw']);
    $requestId = 'production-deployment-request-'.substr(hash('sha256', implode('|', [$source, $artifactSha, $stagingSha, $environment, $targetSha])), 0, 24);

    return [
        'schema_version' => 1,
        'request_state' => 'PRODUCTION_DEPLOYMENT_AUTHORITY_REQUEST_PENDING_APPROVAL',
        'request_id' => $requestId,
        'requested_scope' => 'DEPLOY_EXACT_PRODUCTION_ARTIFACT_TO_EXACT_PRODUCTION_TARGET_NOT_ACTIVATE_TRAFFIC',
        'release_id' => $releaseId,
        'source_commit' => $source,
        'artifact_sha256' => $artifactSha,
        'manifest_sha256' => hash_file('sha256', $manifestPath),
        'staging_evidence_sha256' => $stagingSha,
        'environment_id' => $environment,
        'target_descriptor_sha256' => $targetSha,
        'required_authority' => [
            'state' => 'NOT_GRANTED',
            'separate_operational_authority_required' => true,
            'approval_token_required' => true,
            'maximum_lifetime_seconds' => 900,
        ],
        'migration_execution_authorized' => false,
        'production_authorized' => false,
        'production_traffic_activation_authorized' => false,
        'target_selection_authorized' => false,
        'producer_dispatch_authorized' => false,
        'attribution' => 'Lab | zefry',
    ];
}

function prodReqWrite(string $path, array $payload): void
{
    $directory = dirname($path);
    if ($path === '' || is_link($path) || is_dir($path) || ! is_dir($directory) || ! is_writable($directory)) prodReqFail('output_invalid');
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    $temp = tempnam($directory, '.oneqay-production-request-');
    if ($temp === false) prodReqFail('temp_failed');
    try {
        if (file_put_contents($temp, $json, LOCK_EX) !== strlen($json)) prodReqFail('write_failed');
        @chmod($temp, 0600);
        if (! rename($temp, $path)) prodReqFail('commit_failed');
        @chmod($path, 0600);
    } finally { if (is_file($temp)) @unlink($temp); }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 6) exit(64);
    try {
        prodReqWrite($argv[5], prodReqPrepare($argv[1], $argv[2], $argv[3], $argv[4]));
        fwrite(STDOUT, "production_deployment_authority_request_prepared\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "production_deployment_authority_request_failed:".$failure->getMessage()."\n");
        exit(1);
    }
}
