<?php

declare(strict_types=1);

// Author by Lab | zefry

final class ProductionFixedPublicAuthorityRequestException extends RuntimeException {}

function pfpReqFail(string $code): never { throw new ProductionFixedPublicAuthorityRequestException($code); }
/** @return array{v:array<string,mixed>,raw:string} */
function pfpReqLoad(string $path): array
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) pfpReqFail('input_unavailable');
    $raw = file_get_contents($path);
    if (! is_string($raw) || strlen($raw) < 2 || strlen($raw) > 1048576) pfpReqFail('input_invalid');
    try { $value = json_decode($raw, true, 64, JSON_THROW_ON_ERROR); }
    catch (JsonException) { pfpReqFail('json_invalid'); }
    if (! is_array($value) || array_is_list($value)) pfpReqFail('shape_invalid');
    return ['v'=>$value,'raw'=>$raw];
}
function pfpReqEq(mixed $actual, mixed $expected, string $code): void { if ($actual !== $expected) pfpReqFail($code); }
function pfpReqBool(mixed $actual, bool $expected, string $code): void { if (! is_bool($actual) || $actual !== $expected) pfpReqFail($code); }
function pfpReqPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) pfpReqFail($code);
    return $value;
}
/** @return array<string,mixed>|list<mixed> */
function pfpReqCanonicalize(array $value): array
{
    if (array_is_list($value)) return array_map(static fn (mixed $v): mixed => is_array($v) ? pfpReqCanonicalize($v) : $v, $value);
    ksort($value, SORT_STRING);
    foreach ($value as $key=>$item) if (is_array($item)) $value[$key] = pfpReqCanonicalize($item);
    return $value;
}
function pfpReqCanonical(array $value): string
{
    return json_encode(pfpReqCanonicalize($value), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
}
function pfpReqPath(mixed $value, string $code): string
{
    if (! is_string($value) || $value === '' || $value === '/' || strlen($value) > 4096
        || ! str_starts_with($value, '/') || str_contains($value, "\0") || str_contains($value, '\\')
        || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $value) === 1 || preg_match('#//+#', $value) === 1) pfpReqFail($code);
    return rtrim($value, '/');
}
function pfpReqNested(string $child, string $parent, string $code): void
{
    if (! str_starts_with($child.'/', $parent.'/')) pfpReqFail($code);
}
function pfpReqHealth(array $health): void
{
    pfpReqEq($health['path'] ?? null, '/health/live', 'health_path_invalid');
    $url = $health['url'] ?? null;
    if (! is_string($url)) pfpReqFail('health_url_invalid');
    $parts = parse_url($url);
    if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https'
        || ! is_string($parts['host'] ?? null) || $parts['host'] === '' || ($parts['path'] ?? '') !== '/health/live'
        || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) pfpReqFail('health_url_invalid');
}

/** @return array<string,mixed> */
function pfpReqPrepare(string $manifestPath, string $artifactPath, string $stagingEvidencePath, string $targetPath): array
{
    $manifestLoaded = pfpReqLoad($manifestPath);
    $manifest = $manifestLoaded['v'];
    $source = pfpReqPattern($manifest['source']['commit_sha'] ?? null, '/\A[0-9a-f]{40}\z/', 'manifest_source_invalid');
    $releaseId = 'production-'.substr($source, 0, 12);
    pfpReqEq($manifest['release']['id'] ?? null, $releaseId, 'manifest_release_invalid');
    pfpReqEq($manifest['release']['channel'] ?? null, 'PRODUCTION_CANDIDATE', 'manifest_channel_invalid');
    pfpReqBool($manifest['release']['production'] ?? null, true, 'manifest_production_invalid');
    pfpReqEq($manifest['runtime']['required_runtime_class'] ?? null, 'production', 'manifest_runtime_invalid');
    pfpReqBool($manifest['runtime']['business_runtime_activation_ready'] ?? null, false, 'manifest_business_runtime_must_be_dark');
    pfpReqEq($manifest['runtime']['dark_deploy_health_endpoint'] ?? null, '/health/live', 'manifest_health_invalid');
    pfpReqBool($manifest['migration']['execution_authorized'] ?? null, false, 'manifest_migration_forbidden');
    pfpReqBool($manifest['promotion_gate']['verified_durable_staging_evidence_required'] ?? null, true, 'staging_gate_missing');
    pfpReqBool($manifest['promotion_gate']['same_source_commit_required'] ?? null, true, 'same_source_gate_missing');
    pfpReqBool($manifest['promotion_gate']['production_traffic_activation_authorized'] ?? null, false, 'manifest_traffic_forbidden');
    $artifactSha = pfpReqPattern($manifest['artifact']['sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'manifest_artifact_invalid');
    if (! is_file($artifactPath) || is_link($artifactPath) || ! hash_equals($artifactSha, (string) hash_file('sha256', $artifactPath))) pfpReqFail('artifact_hash_mismatch');

    $stagingLoaded = pfpReqLoad($stagingEvidencePath);
    $staging = $stagingLoaded['v'];
    pfpReqEq($staging['evidence_state'] ?? null, 'DEPLOYED_VERIFIED_NOT_SELECTED', 'staging_state_invalid');
    pfpReqEq($staging['runtime_class'] ?? null, 'durable-staging', 'staging_runtime_invalid');
    pfpReqEq($staging['source_commit'] ?? null, $source, 'staging_source_mismatch');
    foreach (['preflight_passed','previous_active_release_preserved','immutable_release_extracted','public_document_root_verified','external_runtime_configuration_bound','provenance_readback_verified','configuration_read_before_write_verified','configuration_read_after_write_verified','non_mutating_health_attestation_verified','rollback_path_verified'] as $field) {
        pfpReqBool($staging['verification'][$field] ?? null, true, 'staging_verification_'.$field.'_required');
    }
    pfpReqBool($staging['secrets_embedded'] ?? null, false, 'staging_secret_forbidden');

    $target = pfpReqLoad($targetPath)['v'];
    pfpReqEq($target['schema_version'] ?? null, 1, 'target_schema_invalid');
    pfpReqEq($target['target_state'] ?? null, 'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_TARGET_CANDIDATE', 'target_state_invalid');
    pfpReqEq($target['target_class'] ?? null, 'CPANEL_NO_SSH', 'target_class_invalid');
    pfpReqEq($target['execution_channel'] ?? null, 'CPANEL_CRON_PHP_CLI_NO_SSH', 'target_channel_invalid');
    $environment = pfpReqPattern($target['environment_id'] ?? null, '/\A[a-z0-9][a-z0-9-]{2,62}\z/', 'environment_invalid');
    pfpReqEq($target['runtime_class'] ?? null, 'production', 'target_runtime_invalid');
    pfpReqBool($target['production'] ?? null, true, 'target_production_invalid');
    pfpReqBool($target['production_data_allowed'] ?? null, true, 'target_data_invalid');
    pfpReqBool($target['isolated_environment'] ?? null, true, 'target_isolation_invalid');
    pfpReqEq($target['release_binding']['release_id'] ?? null, $releaseId, 'target_release_mismatch');
    pfpReqEq($target['release_binding']['source_commit'] ?? null, $source, 'target_source_mismatch');
    pfpReqEq($target['release_binding']['artifact_sha256'] ?? null, $artifactSha, 'target_artifact_mismatch');

    $deploymentRoot = pfpReqPath($target['filesystem']['deployment_root'] ?? null, 'deployment_root_invalid');
    $releaseRoot = pfpReqPath($target['filesystem']['release_root'] ?? null, 'release_root_invalid');
    $sharedRoot = pfpReqPath($target['filesystem']['shared_runtime_root'] ?? null, 'shared_root_invalid');
    $activePointer = pfpReqPath($target['filesystem']['active_release_pointer'] ?? null, 'active_pointer_invalid');
    $documentRoot = pfpReqPath($target['filesystem']['document_root'] ?? null, 'document_root_invalid');
    foreach ([$releaseRoot,$sharedRoot,$activePointer] as $path) pfpReqNested($path, $deploymentRoot, 'private_filesystem_escape');
    pfpReqEq($target['filesystem']['document_root_mode'] ?? null, 'FIXED_PUBLIC_BRIDGE', 'document_root_mode_invalid');
    if (str_starts_with($documentRoot.'/', $deploymentRoot.'/') || str_starts_with($deploymentRoot.'/', $documentRoot.'/')) pfpReqFail('public_private_paths_not_disjoint');
    foreach (['atomic_rename_supported','atomic_public_swap_supported','rewrite_to_index_verified','public_private_paths_disjoint'] as $field) pfpReqBool($target['filesystem'][$field] ?? null, true, $field.'_required');
    $symlink = $target['filesystem']['symlink_supported'] ?? null;
    $hardlink = $target['filesystem']['hardlink_supported'] ?? null;
    if (! is_bool($symlink) || ! is_bool($hardlink) || ($symlink !== true && $hardlink !== true)) pfpReqFail('runtime_binding_strategy_unavailable');
    $expectedStrategy = $symlink ? 'ACTIVE_POINTER_PLUS_FIXED_PUBLIC_BRIDGE' : 'DIRECT_RELEASE_FIXED_PUBLIC_BRIDGE_WITH_HARDLINK_RUNTIME_BINDING';
    pfpReqEq($target['filesystem']['activation_strategy'] ?? null, $expectedStrategy, 'activation_strategy_invalid');
    pfpReqHealth(is_array($target['health'] ?? null) ? $target['health'] : []);

    foreach (['durable_database_persistence','durable_session','authorization','transaction_durability','pos_durability','authenticated_configuration_channel','read_before_write','read_after_write','non_mutating_health_attestation','verified_rollback'] as $capability) pfpReqBool($target['capabilities'][$capability] ?? null, true, 'capability_'.$capability.'_required');
    pfpReqBool($target['configuration']['secret_values_embedded'] ?? null, false, 'target_secret_forbidden');
    foreach (['ONEQAY_RUNTIME_CLASS','ONEQAY_RUNNING_SOURCE_COMMIT','ONEQAY_RUNNING_ARTIFACT_SHA256','ONEQAY_PRODUCTION_ENVIRONMENT_ID','ONEQAY_PRODUCTION_ATTESTATION_TOKEN'] as $binding) pfpReqBool($target['configuration']['required_binding_presence'][$binding] ?? null, true, 'binding_'.$binding.'_required');
    foreach (['migration_execution_allowed','production_traffic_activation_allowed','target_selection_allowed','producer_dispatch_allowed','provider_disable_functions_bypass_allowed','shell_execution_allowed'] as $field) pfpReqBool($target['safety'][$field] ?? null, false, 'safety_'.$field.'_invalid');
    pfpReqBool($target['safety']['ssh_required'] ?? null, false, 'safety_ssh_invalid');
    pfpReqEq($target['attribution'] ?? null, 'Lab | zefry', 'target_attribution_invalid');

    $targetSha = hash('sha256', pfpReqCanonical($target));
    $stagingSha = hash('sha256', $stagingLoaded['raw']);
    $requestId = 'production-deployment-request-'.substr(hash('sha256', implode('|', [$source,$artifactSha,$stagingSha,$environment,$targetSha,'FIXED_PUBLIC_BRIDGE'])), 0, 24);

    return [
        'schema_version'=>1,
        'request_state'=>'PRODUCTION_DEPLOYMENT_AUTHORITY_REQUEST_PENDING_APPROVAL',
        'request_id'=>$requestId,
        'requested_scope'=>'DEPLOY_EXACT_PRODUCTION_ARTIFACT_TO_EXACT_FIXED_PUBLIC_CPANEL_TARGET_NOT_ACTIVATE_TRAFFIC',
        'release_id'=>$releaseId,
        'source_commit'=>$source,
        'artifact_sha256'=>$artifactSha,
        'manifest_sha256'=>(string) hash_file('sha256', $manifestPath),
        'staging_evidence_sha256'=>$stagingSha,
        'environment_id'=>$environment,
        'target_descriptor_sha256'=>$targetSha,
        'presentation'=>[
            'document_root_mode'=>'FIXED_PUBLIC_BRIDGE',
            'activation_strategy'=>$expectedStrategy,
            'php_symlink_supported'=>$symlink,
            'php_hardlink_supported'=>$hardlink,
        ],
        'required_authority'=>[
            'state'=>'NOT_GRANTED',
            'separate_operational_authority_required'=>true,
            'approval_token_required'=>true,
            'maximum_lifetime_seconds'=>900,
        ],
        'migration_execution_authorized'=>false,
        'production_authorized'=>false,
        'production_traffic_activation_authorized'=>false,
        'target_selection_authorized'=>false,
        'producer_dispatch_authorized'=>false,
        'attribution'=>'Lab | zefry',
    ];
}

function pfpReqWrite(string $path, array $payload): void
{
    $dir = dirname($path);
    if ($path === '' || is_link($path) || is_dir($path) || ! is_dir($dir) || ! is_writable($dir)) pfpReqFail('output_invalid');
    $json = json_encode($payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
    $tmp = tempnam($dir, '.oneqay-production-fixed-public-request-');
    if ($tmp === false) pfpReqFail('temp_failed');
    try {
        if (file_put_contents($tmp, $json, LOCK_EX) !== strlen($json)) pfpReqFail('write_failed');
        @chmod($tmp, 0600);
        if (! rename($tmp, $path)) pfpReqFail('commit_failed');
        @chmod($path, 0600);
    } finally { if (is_file($tmp)) @unlink($tmp); }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 6) exit(64);
    try {
        pfpReqWrite($argv[5], pfpReqPrepare($argv[1],$argv[2],$argv[3],$argv[4]));
        fwrite(STDOUT, "production_fixed_public_deployment_authority_request_prepared\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "production_fixed_public_deployment_authority_request_failed:".$failure->getMessage()."\n");
        exit(1);
    }
}
