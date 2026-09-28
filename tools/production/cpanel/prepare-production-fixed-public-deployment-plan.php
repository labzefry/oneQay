<?php

declare(strict_types=1);

// Author by Lab | zefry

final class ProductionFixedPublicPlanException extends RuntimeException {}

function pfpPlanFail(string $code): never { throw new ProductionFixedPublicPlanException($code); }
/** @return array{v:array<string,mixed>,raw:string} */
function pfpPlanLoad(string $path): array
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) pfpPlanFail('input_unavailable');
    $raw = file_get_contents($path);
    if (! is_string($raw)) pfpPlanFail('input_read_failed');
    try { $value = json_decode($raw, true, 64, JSON_THROW_ON_ERROR); }
    catch (JsonException) { pfpPlanFail('json_invalid'); }
    if (! is_array($value) || array_is_list($value)) pfpPlanFail('shape_invalid');
    return ['v'=>$value,'raw'=>$raw];
}
function pfpPlanEq(mixed $actual, mixed $expected, string $code): void { if ($actual !== $expected) pfpPlanFail($code); }
function pfpPlanBool(mixed $actual, bool $expected, string $code): void { if (! is_bool($actual) || $actual !== $expected) pfpPlanFail($code); }
/** @return array<string,mixed>|list<mixed> */
function pfpPlanCanonicalize(array $value): array
{
    if (array_is_list($value)) return array_map(static fn (mixed $v): mixed => is_array($v) ? pfpPlanCanonicalize($v) : $v, $value);
    ksort($value, SORT_STRING);
    foreach ($value as $key=>$item) if (is_array($item)) $value[$key] = pfpPlanCanonicalize($item);
    return $value;
}
function pfpPlanCanonical(array $value): string
{
    return json_encode(pfpPlanCanonicalize($value), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
}

/** @return array<string,mixed> */
function pfpPlanPrepare(string $manifestPath, string $requestPath, string $bindingPath, string $targetPath): array
{
    $manifestLoaded = pfpPlanLoad($manifestPath);
    $requestLoaded = pfpPlanLoad($requestPath);
    $bindingLoaded = pfpPlanLoad($bindingPath);
    $targetLoaded = pfpPlanLoad($targetPath);
    $manifest = $manifestLoaded['v'];
    $request = $requestLoaded['v'];
    $binding = $bindingLoaded['v'];
    $target = $targetLoaded['v'];

    pfpPlanEq($request['request_state'] ?? null, 'PRODUCTION_DEPLOYMENT_AUTHORITY_REQUEST_PENDING_APPROVAL', 'request_state_invalid');
    pfpPlanEq($request['requested_scope'] ?? null, 'DEPLOY_EXACT_PRODUCTION_ARTIFACT_TO_EXACT_FIXED_PUBLIC_CPANEL_TARGET_NOT_ACTIVATE_TRAFFIC', 'request_scope_invalid');
    pfpPlanEq($binding['binding_state'] ?? null, 'EXTERNAL_PRODUCTION_DEPLOYMENT_AUTHORITY_QUALIFIED_NOT_EXECUTED', 'authority_state_invalid');
    foreach (['request_id','environment_id','target_descriptor_sha256','release_id','artifact_sha256'] as $field) {
        pfpPlanEq($binding[$field] ?? null, $request[$field] ?? null, 'binding_'.$field.'_mismatch');
    }
    pfpPlanEq($binding['request_sha256'] ?? null, hash('sha256', $requestLoaded['raw']), 'binding_request_sha_mismatch');
    pfpPlanBool($binding['deployment_allowed'] ?? null, true, 'deployment_not_allowed');
    pfpPlanBool($binding['production_allowed'] ?? null, true, 'production_not_allowed');
    foreach (['migration_execution_allowed','production_traffic_activation_allowed','target_selection_allowed','producer_dispatch_allowed'] as $field) {
        pfpPlanBool($binding[$field] ?? null, false, 'authority_'.$field.'_forbidden');
    }
    $expires = $binding['expires_at_unix'] ?? null;
    if (! is_int($expires) || time() >= $expires) pfpPlanFail('authority_expired');

    pfpPlanEq($target['target_state'] ?? null, 'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_TARGET_CANDIDATE', 'target_state_invalid');
    pfpPlanEq(hash('sha256', pfpPlanCanonical($target)), $request['target_descriptor_sha256'] ?? null, 'target_descriptor_mismatch');
    pfpPlanEq($target['target_class'] ?? null, 'CPANEL_NO_SSH', 'target_class_invalid');
    pfpPlanEq($target['execution_channel'] ?? null, 'CPANEL_CRON_PHP_CLI_NO_SSH', 'execution_channel_invalid');
    pfpPlanEq($target['environment_id'] ?? null, $request['environment_id'] ?? null, 'target_environment_mismatch');
    pfpPlanEq($target['runtime_class'] ?? null, 'production', 'target_runtime_invalid');
    pfpPlanBool($target['production'] ?? null, true, 'target_production_invalid');
    pfpPlanBool($target['production_data_allowed'] ?? null, true, 'target_data_invalid');
    pfpPlanEq($target['release_binding']['release_id'] ?? null, $request['release_id'] ?? null, 'target_release_mismatch');
    pfpPlanEq($target['release_binding']['source_commit'] ?? null, $request['source_commit'] ?? null, 'target_source_mismatch');
    pfpPlanEq($target['release_binding']['artifact_sha256'] ?? null, $request['artifact_sha256'] ?? null, 'target_artifact_mismatch');
    pfpPlanEq($target['filesystem']['document_root_mode'] ?? null, 'FIXED_PUBLIC_BRIDGE', 'document_root_mode_invalid');
    foreach (['atomic_rename_supported','atomic_public_swap_supported','rewrite_to_index_verified','public_private_paths_disjoint'] as $field) pfpPlanBool($target['filesystem'][$field] ?? null, true, 'target_'.$field.'_required');
    $symlink = $target['filesystem']['symlink_supported'] ?? null;
    $hardlink = $target['filesystem']['hardlink_supported'] ?? null;
    if (! is_bool($symlink) || ! is_bool($hardlink) || ($symlink !== true && $hardlink !== true)) pfpPlanFail('runtime_binding_strategy_unavailable');
    $strategy = $target['filesystem']['activation_strategy'] ?? null;
    $expectedStrategy = $symlink ? 'ACTIVE_POINTER_PLUS_FIXED_PUBLIC_BRIDGE' : 'DIRECT_RELEASE_FIXED_PUBLIC_BRIDGE_WITH_HARDLINK_RUNTIME_BINDING';
    pfpPlanEq($strategy, $expectedStrategy, 'activation_strategy_invalid');

    pfpPlanEq($manifest['release']['id'] ?? null, $request['release_id'] ?? null, 'manifest_release_mismatch');
    pfpPlanEq($manifest['source']['commit_sha'] ?? null, $request['source_commit'] ?? null, 'manifest_source_mismatch');
    pfpPlanEq($manifest['artifact']['sha256'] ?? null, $request['artifact_sha256'] ?? null, 'manifest_artifact_mismatch');
    pfpPlanEq($manifest['runtime']['dark_deploy_health_endpoint'] ?? null, '/health/live', 'manifest_health_invalid');
    pfpPlanBool($manifest['runtime']['business_runtime_activation_ready'] ?? null, false, 'manifest_business_runtime_must_be_dark');
    pfpPlanBool($manifest['migration']['execution_authorized'] ?? null, false, 'manifest_migration_forbidden');
    pfpPlanBool($manifest['promotion_gate']['production_traffic_activation_authorized'] ?? null, false, 'manifest_traffic_forbidden');

    $releaseRoot = rtrim((string) $target['filesystem']['release_root'], '/');
    $releaseDirectory = $releaseRoot.'/'.$request['release_id'];
    $plan = [
        'schema_version'=>1,
        'plan_state'=>'QUALIFIED_FOR_PRODUCTION_DEPLOYMENT_NOT_EXECUTED_NOT_ACTIVATED',
        'plan_variant'=>'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_SUCCESSOR_V1',
        'artifact'=>[
            'release_id'=>$request['release_id'],
            'source_commit'=>$request['source_commit'],
            'artifact_filename'=>$manifest['artifact']['filename'],
            'artifact_sha256'=>$request['artifact_sha256'],
            'manifest_sha256'=>$request['manifest_sha256'],
        ],
        'staging_promotion_evidence'=>[
            'sha256'=>$request['staging_evidence_sha256'],
            'same_source_commit_required'=>true,
        ],
        'target'=>[
            'target_class'=>'CPANEL_NO_SSH',
            'execution_channel'=>'CPANEL_CRON_PHP_CLI_NO_SSH',
            'environment_id'=>$request['environment_id'],
            'runtime_class'=>'production',
            'production'=>true,
            'production_data_allowed'=>true,
            'deployment_root'=>$target['filesystem']['deployment_root'],
            'release_root'=>$releaseRoot,
            'release_directory'=>$releaseDirectory,
            'shared_runtime_root'=>$target['filesystem']['shared_runtime_root'],
            'active_release_pointer'=>$target['filesystem']['active_release_pointer'],
            'document_root'=>$target['filesystem']['document_root'],
            'document_root_mode'=>'FIXED_PUBLIC_BRIDGE',
            'activation_strategy'=>$strategy,
            'symlink_supported'=>$symlink,
            'hardlink_supported'=>$hardlink,
            'health_url'=>$target['health']['url'],
            'health_path'=>'/health/live',
        ],
        'authority_binding'=>[
            'state'=>'EXTERNAL_AUTHORITY_BOUND_TO_EXACT_PRODUCTION_TARGET',
            'authority_id'=>$binding['authority_id'],
            'authority_sha256'=>$binding['authority_sha256'],
            'request_id'=>$binding['request_id'],
            'request_sha256'=>$binding['request_sha256'],
            'target_descriptor_sha256'=>$binding['target_descriptor_sha256'],
            'authorized_at_unix'=>$binding['authorized_at_unix'],
            'expires_at_unix'=>$binding['expires_at_unix'],
            'deployment_allowed'=>true,
            'production_allowed'=>true,
            'migration_execution_allowed'=>false,
            'production_traffic_activation_allowed'=>false,
            'target_selection_allowed'=>false,
            'producer_dispatch_allowed'=>false,
        ],
        'preflight'=>[
            'required_before_mutation'=>true,
            'checks'=>[
                'authority_current','artifact_sha256_exact','manifest_exact','staging_evidence_exact','target_descriptor_exact',
                'private_runtime_bindings_present','fixed_public_rewrite_to_index_verified','public_private_paths_disjoint',
                'runtime_binding_strategy_available','public_atomic_swap_available','dark_health_endpoint_available',
            ],
        ],
        'execution_order'=>[
            'reverify_plan_fingerprint','reverify_authority','reverify_artifact','snapshot_existing_public_surface',
            'extract_immutable_release','bind_private_runtime_configuration',
            'activate_fixed_public_bridge','verify_exact_runtime_provenance','verify_dark_non_mutating_health',
            'restore_previous_public_surface','verify_rollback_surface','reactivate_fixed_public_bridge',
            'verify_exact_runtime_provenance_after_reactivation','verify_dark_non_mutating_health_after_reactivation',
        ],
        'readback_expectations'=>[
            'environment_id'=>$request['environment_id'],
            'runtime_class'=>'production',
            'running_source_commit'=>$request['source_commit'],
            'running_artifact_sha256'=>$request['artifact_sha256'],
            'business_runtime_activation_ready'=>false,
            'production_traffic_active'=>false,
            'health_path'=>'/health/live',
        ],
        'rollback'=>[
            'required'=>true,
            'previous_public_surface_must_be_preserved'=>true,
            'rollback_must_be_verified_before_acceptance'=>true,
            'candidate_must_be_restored_after_rehearsal'=>true,
            'database_rollback_implied'=>false,
            'migration_rollback_implied'=>false,
        ],
        'migration'=>[
            'source_included'=>true,
            'expected_count'=>27,
            'execution_state'=>'ALREADY_EXECUTED_NO_REPLAY',
            'execution_authorized'=>false,
        ],
        'traffic_activation'=>[
            'state'=>'NOT_AUTHORIZED',
            'separate_authority_required'=>true,
            'automatic_activation'=>false,
        ],
        'operational_boundary'=>[
            'environment_deployment'=>'PLAN_ONLY_NOT_EXECUTED',
            'migration27_execution'=>'ALREADY_EXECUTED_NO_REPLAY',
            'permission_provisioning'=>'ALREADY_PROVISIONED_NO_REPLAY',
            'feature_activation'=>'ACTIVE_PRESERVED_NO_REACTIVATION',
            'technical_preview_activation'=>'NOT_AUTHORIZED',
            'production_business_traffic_activation'=>'NOT_AUTHORIZED',
            'updater_activation'=>'INACTIVE',
            'target_reselection'=>'NOT_AUTHORIZED',
            'producer_dispatch'=>'NOT_AUTHORIZED',
        ],
        'attribution'=>'Lab | zefry',
    ];
    $plan['plan_fingerprint'] = hash('sha256', pfpPlanCanonical($plan));
    return $plan;
}

function pfpPlanWrite(string $path, array $payload): void
{
    $dir = dirname($path);
    if ($path === '' || is_link($path) || is_dir($path) || ! is_dir($dir) || ! is_writable($dir)) pfpPlanFail('output_invalid');
    $json = json_encode($payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
    $tmp = tempnam($dir, '.oneqay-production-fixed-public-plan-');
    if ($tmp === false) pfpPlanFail('temp_failed');
    try {
        if (file_put_contents($tmp, $json, LOCK_EX) !== strlen($json)) pfpPlanFail('write_failed');
        @chmod($tmp, 0600);
        if (! rename($tmp, $path)) pfpPlanFail('commit_failed');
        @chmod($path, 0600);
    } finally { if (is_file($tmp)) @unlink($tmp); }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 6) exit(64);
    try {
        pfpPlanWrite($argv[5], pfpPlanPrepare($argv[1],$argv[2],$argv[3],$argv[4]));
        fwrite(STDOUT, "production_fixed_public_deployment_plan_prepared\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "production_fixed_public_deployment_plan_failed:".$failure->getMessage()."\n");
        exit(1);
    }
}
