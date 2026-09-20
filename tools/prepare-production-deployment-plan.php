<?php

declare(strict_types=1);

// Author by Lab | zefry

final class ProductionDeploymentPlanException extends RuntimeException {}

function prodPlanFail(string $code): never { throw new ProductionDeploymentPlanException($code); }

function prodPlanLoad(string $path): array
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) prodPlanFail('input_unavailable');
    try { $value = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR); }
    catch (JsonException) { prodPlanFail('json_invalid'); }
    if (! is_array($value) || array_is_list($value)) prodPlanFail('shape_invalid');
    return $value;
}

function prodPlanEq(mixed $actual, mixed $expected, string $code): void { if ($actual !== $expected) prodPlanFail($code); }
function prodPlanBool(mixed $actual, bool $expected, string $code): void { if (! is_bool($actual) || $actual !== $expected) prodPlanFail($code); }

/** @return array<string,mixed>|list<mixed> */
function prodPlanCanonicalize(array $value): array
{
    if (array_is_list($value)) return array_map(static fn (mixed $v): mixed => is_array($v) ? prodPlanCanonicalize($v) : $v, $value);
    ksort($value, SORT_STRING);
    foreach ($value as $key => $item) if (is_array($item)) $value[$key] = prodPlanCanonicalize($item);
    return $value;
}

function prodPlanCanonical(array $value): string
{
    return json_encode(prodPlanCanonicalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

/** @return array<string,mixed> */
function prodPlanPrepare(string $manifestPath, string $requestPath, string $bindingPath, string $targetPath): array
{
    $manifest = prodPlanLoad($manifestPath);
    $request = prodPlanLoad($requestPath);
    $binding = prodPlanLoad($bindingPath);
    $target = prodPlanLoad($targetPath);

    prodPlanEq($request['request_state'] ?? null, 'PRODUCTION_DEPLOYMENT_AUTHORITY_REQUEST_PENDING_APPROVAL', 'request_state_invalid');
    prodPlanEq($binding['binding_state'] ?? null, 'EXTERNAL_PRODUCTION_DEPLOYMENT_AUTHORITY_QUALIFIED_NOT_EXECUTED', 'authority_state_invalid');
    foreach (['request_id','environment_id','target_descriptor_sha256','release_id','artifact_sha256'] as $field) {
        prodPlanEq($binding[$field] ?? null, $request[$field] ?? null, 'binding_'.$field.'_mismatch');
    }
    prodPlanBool($binding['deployment_allowed'] ?? null, true, 'deployment_not_allowed');
    prodPlanBool($binding['production_allowed'] ?? null, true, 'production_not_allowed');
    prodPlanBool($binding['migration_execution_allowed'] ?? null, false, 'migration_forbidden');
    prodPlanBool($binding['production_traffic_activation_allowed'] ?? null, false, 'traffic_activation_forbidden');
    if (! is_int($binding['expires_at_unix'] ?? null) || time() > $binding['expires_at_unix']) prodPlanFail('authority_expired');

    prodPlanEq($target['target_state'] ?? null, 'PRODUCTION_TARGET_CANDIDATE', 'target_state_invalid');
    prodPlanEq($target['environment_id'] ?? null, $request['environment_id'] ?? null, 'target_environment_mismatch');
    prodPlanEq($target['runtime_class'] ?? null, 'production', 'target_runtime_invalid');
    prodPlanEq($target['release_binding']['release_id'] ?? null, $request['release_id'] ?? null, 'target_release_mismatch');
    prodPlanEq($target['release_binding']['source_commit'] ?? null, $request['source_commit'] ?? null, 'target_source_mismatch');
    prodPlanEq($target['release_binding']['artifact_sha256'] ?? null, $request['artifact_sha256'] ?? null, 'target_artifact_mismatch');

    prodPlanEq($manifest['release']['id'] ?? null, $request['release_id'] ?? null, 'manifest_release_mismatch');
    prodPlanEq($manifest['source']['commit_sha'] ?? null, $request['source_commit'] ?? null, 'manifest_source_mismatch');
    prodPlanEq($manifest['artifact']['sha256'] ?? null, $request['artifact_sha256'] ?? null, 'manifest_artifact_mismatch');
    prodPlanEq($manifest['runtime']['dark_deploy_health_endpoint'] ?? null, '/health/live', 'manifest_health_invalid');

    $releaseDirectory = rtrim((string) $target['filesystem']['release_root'], '/').'/'.$request['release_id'];
    $plan = [
        'schema_version' => 1,
        'plan_state' => 'QUALIFIED_FOR_PRODUCTION_DEPLOYMENT_NOT_EXECUTED_NOT_ACTIVATED',
        'artifact' => [
            'release_id' => $request['release_id'],
            'source_commit' => $request['source_commit'],
            'artifact_filename' => $manifest['artifact']['filename'],
            'artifact_sha256' => $request['artifact_sha256'],
            'manifest_sha256' => $request['manifest_sha256'],
        ],
        'staging_promotion_evidence' => [
            'sha256' => $request['staging_evidence_sha256'],
            'same_source_commit_required' => true,
        ],
        'target' => [
            'target_class' => $target['target_class'],
            'execution_channel' => $target['execution_channel'],
            'environment_id' => $request['environment_id'],
            'runtime_class' => 'production',
            'production' => true,
            'production_data_allowed' => true,
            'deployment_root' => $target['filesystem']['deployment_root'],
            'release_root' => $target['filesystem']['release_root'],
            'release_directory' => $releaseDirectory,
            'shared_runtime_root' => $target['filesystem']['shared_runtime_root'],
            'active_release_pointer' => $target['filesystem']['active_release_pointer'],
            'document_root' => $target['filesystem']['document_root'],
            'health_url' => $target['health']['url'],
            'health_path' => $target['health']['path'],
        ],
        'authority_binding' => [
            'state' => 'EXTERNAL_AUTHORITY_BOUND_TO_EXACT_PRODUCTION_TARGET',
            'authority_id' => $binding['authority_id'],
            'authority_sha256' => $binding['authority_sha256'],
            'request_id' => $binding['request_id'],
            'request_sha256' => $binding['request_sha256'],
            'target_descriptor_sha256' => $binding['target_descriptor_sha256'],
            'authorized_at_unix' => $binding['authorized_at_unix'],
            'expires_at_unix' => $binding['expires_at_unix'],
            'deployment_allowed' => true,
            'production_allowed' => true,
            'migration_execution_allowed' => false,
            'production_traffic_activation_allowed' => false,
        ],
        'preflight' => [
            'required_before_mutation' => true,
            'checks' => [
                'authority_current','artifact_sha256_exact','manifest_exact','staging_evidence_exact','target_descriptor_exact',
                'private_runtime_bindings_present','active_pointer_readback','rollback_target_available','dark_health_endpoint_available',
            ],
        ],
        'execution_order' => [
            'reverify_plan_fingerprint','reverify_authority','reverify_artifact','preserve_current_active_release',
            'extract_immutable_release','bind_private_runtime_configuration','verify_configuration_readback',
            'switch_active_release_pointer','verify_exact_runtime_provenance','verify_dark_non_mutating_health',
            'rehearse_rollback','restore_candidate','verify_dark_non_mutating_health_after_reactivation',
        ],
        'readback_expectations' => [
            'environment_id' => $request['environment_id'],
            'runtime_class' => 'production',
            'running_source_commit' => $request['source_commit'],
            'running_artifact_sha256' => $request['artifact_sha256'],
            'business_runtime_activation_ready' => false,
            'production_traffic_active' => false,
            'health_path' => '/health/live',
        ],
        'rollback' => [
            'required' => true,
            'previous_active_release_must_be_preserved' => true,
            'rollback_must_be_verified_before_acceptance' => true,
            'candidate_must_be_restored_after_rehearsal' => true,
            'database_rollback_implied' => false,
            'migration_rollback_implied' => false,
        ],
        'migration' => [
            'source_included' => true,
            'expected_count' => 27,
            'execution_state' => 'NOT_PERFORMED',
            'execution_authorized' => false,
        ],
        'traffic_activation' => [
            'state' => 'NOT_AUTHORIZED',
            'separate_authority_required' => true,
            'automatic_activation' => false,
        ],
        'operational_boundary' => [
            'environment_deployment' => 'PLAN_ONLY_NOT_EXECUTED',
            'migration27_execution' => 'NOT_PERFORMED',
            'permission_provisioning' => 'NONE',
            'feature_activation' => 'INACTIVE',
            'technical_preview_activation' => 'NOT_AUTHORIZED',
            'production_activation' => 'NOT_AUTHORIZED',
            'updater_activation' => 'INACTIVE',
            'target_selection' => 'NOT_PERFORMED',
            'selected_target' => null,
            'producer_dispatch' => 'NOT_PERFORMED',
        ],
        'attribution' => 'Lab | zefry',
    ];

    $plan['plan_fingerprint'] = hash('sha256', prodPlanCanonical($plan));
    return $plan;
}

function prodPlanWrite(string $path, array $payload): void
{
    $directory = dirname($path);
    if ($path === '' || is_link($path) || is_dir($path) || ! is_dir($directory) || ! is_writable($directory)) prodPlanFail('output_invalid');
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    $temp = tempnam($directory, '.oneqay-production-plan-');
    if ($temp === false) prodPlanFail('temp_failed');
    try {
        if (file_put_contents($temp, $json, LOCK_EX) !== strlen($json)) prodPlanFail('write_failed');
        @chmod($temp, 0600);
        if (! rename($temp, $path)) prodPlanFail('commit_failed');
        @chmod($path, 0600);
    } finally { if (is_file($temp)) @unlink($temp); }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 6) exit(64);
    try {
        prodPlanWrite($argv[5], prodPlanPrepare($argv[1], $argv[2], $argv[3], $argv[4]));
        fwrite(STDOUT, "production_deployment_plan_prepared\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "production_deployment_plan_failed:".$failure->getMessage()."\n");
        exit(1);
    }
}
