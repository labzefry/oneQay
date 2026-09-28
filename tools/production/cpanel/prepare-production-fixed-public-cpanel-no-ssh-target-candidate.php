<?php

declare(strict_types=1);

// Author by Lab | zefry

final class ProductionFixedPublicTargetCandidateException extends RuntimeException {}

function pfpCandidateFail(string $code): never { throw new ProductionFixedPublicTargetCandidateException($code); }
function pfpCandidateLoad(string $path): array
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) pfpCandidateFail('input_unavailable');
    try { $value = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR); }
    catch (JsonException) { pfpCandidateFail('json_invalid'); }
    if (! is_array($value) || array_is_list($value)) pfpCandidateFail('shape_invalid');
    return $value;
}
function pfpCandidateEq(mixed $actual, mixed $expected, string $code): void { if ($actual !== $expected) pfpCandidateFail($code); }
function pfpCandidateBool(mixed $actual, bool $expected, string $code): void { if (! is_bool($actual) || $actual !== $expected) pfpCandidateFail($code); }
function pfpCandidatePattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) pfpCandidateFail($code);
    return $value;
}

/** @return array<string,mixed> */
function pfpCandidatePrepare(array $profile): array
{
    pfpCandidateEq($profile['schema_version'] ?? null, 1, 'schema_invalid');
    pfpCandidateEq($profile['profile_state'] ?? null, 'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_TARGET_PROFILE_OBSERVED', 'state_invalid');
    pfpCandidateEq($profile['target_class'] ?? null, 'CPANEL_NO_SSH', 'target_class_invalid');
    pfpCandidateEq($profile['execution_channel'] ?? null, 'CPANEL_CRON_PHP_CLI_NO_SSH', 'execution_channel_invalid');
    $environment = pfpCandidatePattern($profile['environment_id'] ?? null, '/\A[a-z0-9][a-z0-9-]{2,62}\z/', 'environment_invalid');
    pfpCandidateEq($profile['runtime_class'] ?? null, 'production', 'runtime_invalid');
    pfpCandidateBool($profile['production'] ?? null, true, 'production_required');
    pfpCandidateBool($profile['production_data_allowed'] ?? null, true, 'production_data_required');
    pfpCandidateBool($profile['isolated_environment'] ?? null, true, 'isolation_required');
    pfpCandidateBool($profile['secrets_embedded'] ?? null, false, 'secret_forbidden');
    pfpCandidateEq($profile['attribution'] ?? null, 'Lab | zefry', 'attribution_invalid');

    $releaseId = pfpCandidatePattern($profile['release_binding']['release_id'] ?? null, '/\Aproduction-[0-9a-f]{12}\z/', 'release_invalid');
    $source = pfpCandidatePattern($profile['release_binding']['source_commit'] ?? null, '/\A[0-9a-f]{40}\z/', 'source_invalid');
    $artifact = pfpCandidatePattern($profile['release_binding']['artifact_sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'artifact_invalid');
    pfpCandidateEq($releaseId, 'production-'.substr($source, 0, 12), 'release_source_mismatch');

    foreach (['deployment_root','release_root','shared_runtime_root','active_release_pointer','document_root'] as $field) {
        if (! is_string($profile['filesystem'][$field] ?? null) || $profile['filesystem'][$field] === '') pfpCandidateFail('filesystem_'.$field.'_invalid');
    }
    pfpCandidateEq($profile['filesystem']['document_root_mode'] ?? null, 'FIXED_PUBLIC_BRIDGE', 'document_root_mode_invalid');
    foreach (['deployment_root_writable','release_root_writable','shared_runtime_root_writable','active_pointer_parent_writable','atomic_rename_supported','atomic_public_swap_supported','rewrite_to_index_verified','public_private_paths_disjoint'] as $field) {
        pfpCandidateBool($profile['filesystem'][$field] ?? null, true, 'filesystem_'.$field.'_required');
    }
    $symlinkSupported = $profile['filesystem']['symlink_supported'] ?? null;
    $hardlinkSupported = $profile['filesystem']['hardlink_supported'] ?? null;
    if (! is_bool($symlinkSupported) || ! is_bool($hardlinkSupported) || ($symlinkSupported !== true && $hardlinkSupported !== true)) {
        pfpCandidateFail('runtime_binding_strategy_unavailable');
    }
    $strategy = $profile['filesystem']['activation_strategy'] ?? null;
    $expectedStrategy = $symlinkSupported
        ? 'ACTIVE_POINTER_PLUS_FIXED_PUBLIC_BRIDGE'
        : 'DIRECT_RELEASE_FIXED_PUBLIC_BRIDGE_WITH_HARDLINK_RUNTIME_BINDING';
    pfpCandidateEq($strategy, $expectedStrategy, 'activation_strategy_invalid');

    pfpCandidateEq($profile['health']['path'] ?? null, '/health/live', 'health_path_invalid');
    pfpCandidateBool($profile['health']['https_required'] ?? null, true, 'health_https_required');
    if (! is_string($profile['health']['url'] ?? null) || ! str_starts_with($profile['health']['url'], 'https://')) pfpCandidateFail('health_url_invalid');
    foreach (['php_version_supported','required_extensions_present','https_client_available'] as $field) {
        pfpCandidateBool($profile['runtime'][$field] ?? null, true, 'runtime_'.$field.'_required');
    }
    pfpCandidateBool($profile['configuration']['binding_file_private'] ?? null, true, 'binding_private_required');
    pfpCandidateBool($profile['configuration']['required_binding_identity_matches'] ?? null, true, 'binding_identity_required');
    pfpCandidateBool($profile['configuration']['secret_values_embedded'] ?? null, false, 'binding_secret_forbidden');

    $capabilityNames = [
        'durable_database_persistence','durable_session','authorization','transaction_durability','pos_durability',
        'authenticated_configuration_channel','read_before_write','read_after_write','non_mutating_health_attestation','verified_rollback',
    ];
    $resolved = [];
    foreach ($capabilityNames as $capability) {
        pfpCandidateBool($profile['operator_assertions'][$capability] ?? null, true, 'capability_'.$capability.'_required');
        $resolved[$capability] = true;
    }

    return [
        'schema_version'=>1,
        'target_state'=>'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_TARGET_CANDIDATE',
        'target_class'=>'CPANEL_NO_SSH',
        'execution_channel'=>'CPANEL_CRON_PHP_CLI_NO_SSH',
        'environment_id'=>$environment,
        'runtime_class'=>'production',
        'production'=>true,
        'production_data_allowed'=>true,
        'isolated_environment'=>true,
        'release_binding'=>['release_id'=>$releaseId,'source_commit'=>$source,'artifact_sha256'=>$artifact],
        'filesystem'=>[
            'deployment_root'=>$profile['filesystem']['deployment_root'],
            'release_root'=>$profile['filesystem']['release_root'],
            'shared_runtime_root'=>$profile['filesystem']['shared_runtime_root'],
            'active_release_pointer'=>$profile['filesystem']['active_release_pointer'],
            'document_root'=>$profile['filesystem']['document_root'],
            'document_root_mode'=>'FIXED_PUBLIC_BRIDGE',
            'symlink_supported'=>$symlinkSupported,
            'hardlink_supported'=>$hardlinkSupported,
            'atomic_rename_supported'=>true,
            'atomic_public_swap_supported'=>true,
            'rewrite_to_index_verified'=>true,
            'public_private_paths_disjoint'=>true,
            'activation_strategy'=>$strategy,
        ],
        'health'=>['url'=>$profile['health']['url'],'path'=>'/health/live'],
        'capabilities'=>$resolved,
        'configuration'=>[
            'secret_values_embedded'=>false,
            'required_binding_presence'=>$profile['configuration']['required_binding_presence'],
        ],
        'safety'=>[
            'migration_execution_allowed'=>false,
            'production_traffic_activation_allowed'=>false,
            'target_selection_allowed'=>false,
            'producer_dispatch_allowed'=>false,
            'provider_disable_functions_bypass_allowed'=>false,
            'shell_execution_allowed'=>false,
            'ssh_required'=>false,
        ],
        'attribution'=>'Lab | zefry',
    ];
}

function pfpCandidateWrite(string $path, array $payload): void
{
    $dir = dirname($path);
    if ($path === '' || is_link($path) || is_dir($path) || ! is_dir($dir) || ! is_writable($dir)) pfpCandidateFail('output_invalid');
    $json = json_encode($payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
    $tmp = tempnam($dir, '.oneqay-production-fixed-public-candidate-');
    if ($tmp === false) pfpCandidateFail('temp_failed');
    try {
        if (file_put_contents($tmp, $json, LOCK_EX) !== strlen($json)) pfpCandidateFail('write_failed');
        @chmod($tmp, 0600);
        if (! rename($tmp, $path)) pfpCandidateFail('commit_failed');
        @chmod($path, 0600);
    } finally { if (is_file($tmp)) @unlink($tmp); }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 3) exit(64);
    try {
        pfpCandidateWrite($argv[2], pfpCandidatePrepare(pfpCandidateLoad($argv[1])));
        fwrite(STDOUT, "production_fixed_public_target_candidate_prepared\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "production_fixed_public_target_candidate_failed:".$failure->getMessage()."\n");
        exit(1);
    }
}
