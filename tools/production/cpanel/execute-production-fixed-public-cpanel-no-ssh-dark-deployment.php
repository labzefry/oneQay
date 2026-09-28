<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2).'/cpanel/fixed-public-document-root-bridge.php';

// Author by Lab | zefry

final class ProductionFixedPublicExecutionException extends RuntimeException {}

function pfpExecFail(string $code): never { throw new ProductionFixedPublicExecutionException($code); }

/** @return array<string,mixed> */
function pfpExecLoad(string $path, bool $private = false): array
{
    if ($path === '' || str_contains($path, "\0") || ! is_file($path) || is_link($path) || ! is_readable($path)) pfpExecFail('input_unavailable');
    $size = filesize($path);
    if (! is_int($size) || $size < 2 || $size > 1048576) pfpExecFail('input_size_invalid');
    if ($private) {
        $mode = fileperms($path);
        if (! is_int($mode) || (($mode & 0077) !== 0)) pfpExecFail('private_file_permissions_invalid');
    }
    $raw = file_get_contents($path);
    if (! is_string($raw)) pfpExecFail('input_read_failed');
    try { $value = json_decode($raw, true, 64, JSON_THROW_ON_ERROR); }
    catch (JsonException) { pfpExecFail('input_json_invalid'); }
    if (! is_array($value) || array_is_list($value)) pfpExecFail('input_shape_invalid');
    return $value;
}
function pfpExecEq(mixed $actual, mixed $expected, string $code): void { if ($actual !== $expected) pfpExecFail($code); }
function pfpExecBool(mixed $actual, bool $expected, string $code): void { if (! is_bool($actual) || $actual !== $expected) pfpExecFail($code); }
function pfpExecPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) pfpExecFail($code);
    return $value;
}
function pfpExecPath(mixed $value, string $code): string
{
    if (! is_string($value) || $value === '' || $value === '/' || strlen($value) > 4096
        || ! str_starts_with($value, '/') || str_contains($value, "\0") || str_contains($value, '\\')
        || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $value) === 1 || preg_match('#//+#', $value) === 1) pfpExecFail($code);
    return rtrim($value, '/');
}
function pfpExecNested(string $child, string $parent, string $code): void
{
    if (! str_starts_with($child.'/', $parent.'/')) pfpExecFail($code);
}
/** @return array<string,mixed>|list<mixed> */
function pfpExecCanonicalize(array $value): array
{
    if (array_is_list($value)) return array_map(static fn (mixed $v): mixed => is_array($v) ? pfpExecCanonicalize($v) : $v, $value);
    ksort($value, SORT_STRING);
    foreach ($value as $key=>$item) if (is_array($item)) $value[$key] = pfpExecCanonicalize($item);
    return $value;
}
function pfpExecCanonical(array $value): string
{
    return json_encode(pfpExecCanonicalize($value), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
}
/** @return list<string> */
function pfpExecDisabledFunctions(): array
{
    $raw = (string) ini_get('disable_functions');
    return $raw === '' ? [] : array_values(array_filter(array_map('trim', explode(',', $raw)), static fn (string $v): bool => $v !== ''));
}
function pfpExecFunctionAvailable(string $name): bool
{
    return function_exists($name) && ! in_array($name, pfpExecDisabledFunctions(), true);
}
function pfpExecPrivateFile(string $path, string $sharedRoot, string $code): string
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) pfpExecFail($code.'_unavailable');
    $mode = fileperms($path);
    if (! is_int($mode) || (($mode & 0077) !== 0)) pfpExecFail($code.'_permissions_invalid');
    $real = realpath($path);
    $shared = realpath($sharedRoot);
    if (! is_string($real) || ! is_string($shared) || ! str_starts_with($real.'/', rtrim($shared, '/').'/')) pfpExecFail($code.'_outside_shared_runtime_root');
    return $real;
}

/** @return array<string,mixed> */
function pfpExecValidatePlan(array $plan): array
{
    pfpExecEq($plan['schema_version'] ?? null, 1, 'plan_schema_invalid');
    pfpExecEq($plan['plan_state'] ?? null, 'QUALIFIED_FOR_PRODUCTION_DEPLOYMENT_NOT_EXECUTED_NOT_ACTIVATED', 'plan_state_invalid');
    pfpExecEq($plan['plan_variant'] ?? null, 'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_SUCCESSOR_V1', 'plan_variant_invalid');
    $fingerprint = pfpExecPattern($plan['plan_fingerprint'] ?? null, '/\A[0-9a-f]{64}\z/', 'plan_fingerprint_invalid');
    $core = $plan; unset($core['plan_fingerprint']);
    pfpExecEq(hash('sha256', pfpExecCanonical($core)), $fingerprint, 'plan_fingerprint_mismatch');

    $releaseId = pfpExecPattern($plan['artifact']['release_id'] ?? null, '/\Aproduction-[0-9a-f]{12}\z/', 'release_invalid');
    $source = pfpExecPattern($plan['artifact']['source_commit'] ?? null, '/\A[0-9a-f]{40}\z/', 'source_invalid');
    $artifact = pfpExecPattern($plan['artifact']['artifact_sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'artifact_invalid');
    pfpExecEq($releaseId, 'production-'.substr($source, 0, 12), 'release_source_mismatch');

    pfpExecEq($plan['target']['target_class'] ?? null, 'CPANEL_NO_SSH', 'target_class_invalid');
    pfpExecEq($plan['target']['execution_channel'] ?? null, 'CPANEL_CRON_PHP_CLI_NO_SSH', 'execution_channel_invalid');
    $environment = pfpExecPattern($plan['target']['environment_id'] ?? null, '/\A[a-z0-9][a-z0-9-]{2,62}\z/', 'environment_invalid');
    pfpExecEq($plan['target']['runtime_class'] ?? null, 'production', 'runtime_invalid');
    pfpExecBool($plan['target']['production'] ?? null, true, 'production_required');
    pfpExecBool($plan['target']['production_data_allowed'] ?? null, true, 'production_data_required');
    pfpExecEq($plan['target']['document_root_mode'] ?? null, 'FIXED_PUBLIC_BRIDGE', 'document_root_mode_invalid');

    $deploymentRoot = pfpExecPath($plan['target']['deployment_root'] ?? null, 'deployment_root_invalid');
    $releaseRoot = pfpExecPath($plan['target']['release_root'] ?? null, 'release_root_invalid');
    $releaseDirectory = pfpExecPath($plan['target']['release_directory'] ?? null, 'release_directory_invalid');
    $sharedRoot = pfpExecPath($plan['target']['shared_runtime_root'] ?? null, 'shared_root_invalid');
    $activePointer = pfpExecPath($plan['target']['active_release_pointer'] ?? null, 'active_pointer_invalid');
    $documentRoot = pfpExecPath($plan['target']['document_root'] ?? null, 'document_root_invalid');
    foreach ([$releaseRoot,$sharedRoot,$activePointer] as $path) pfpExecNested($path, $deploymentRoot, 'private_filesystem_escape');
    pfpExecEq($releaseDirectory, $releaseRoot.'/'.$releaseId, 'release_directory_mismatch');
    if (str_starts_with($documentRoot.'/', $deploymentRoot.'/') || str_starts_with($deploymentRoot.'/', $documentRoot.'/')) pfpExecFail('public_private_paths_not_disjoint');

    $symlink = $plan['target']['symlink_supported'] ?? null;
    $hardlink = $plan['target']['hardlink_supported'] ?? null;
    if (! is_bool($symlink) || ! is_bool($hardlink) || ($symlink !== true && $hardlink !== true)) pfpExecFail('runtime_binding_strategy_unavailable');
    $strategy = $plan['target']['activation_strategy'] ?? null;
    $expectedStrategy = $symlink ? 'ACTIVE_POINTER_PLUS_FIXED_PUBLIC_BRIDGE' : 'DIRECT_RELEASE_FIXED_PUBLIC_BRIDGE_WITH_HARDLINK_RUNTIME_BINDING';
    pfpExecEq($strategy, $expectedStrategy, 'activation_strategy_invalid');

    $healthUrl = $plan['target']['health_url'] ?? null;
    pfpExecEq($plan['target']['health_path'] ?? null, '/health/live', 'health_path_invalid');
    if (! is_string($healthUrl)) pfpExecFail('health_url_invalid');
    $parts = parse_url($healthUrl);
    if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https' || ($parts['path'] ?? '') !== '/health/live'
        || ! is_string($parts['host'] ?? null) || $parts['host'] === '' || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) pfpExecFail('health_url_invalid');

    pfpExecEq($plan['authority_binding']['state'] ?? null, 'EXTERNAL_AUTHORITY_BOUND_TO_EXACT_PRODUCTION_TARGET', 'authority_state_invalid');
    $authorityId = pfpExecPattern($plan['authority_binding']['authority_id'] ?? null, '/\Aproduction-deployment-authority-[0-9a-f]{24}\z/', 'authority_id_invalid');
    $authoritySha = pfpExecPattern($plan['authority_binding']['authority_sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'authority_sha_invalid');
    $requestId = pfpExecPattern($plan['authority_binding']['request_id'] ?? null, '/\Aproduction-deployment-request-[0-9a-f]{24}\z/', 'request_id_invalid');
    $requestSha = pfpExecPattern($plan['authority_binding']['request_sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'request_sha_invalid');
    pfpExecPattern($plan['authority_binding']['target_descriptor_sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'target_descriptor_invalid');
    pfpExecBool($plan['authority_binding']['deployment_allowed'] ?? null, true, 'deployment_authority_missing');
    pfpExecBool($plan['authority_binding']['production_allowed'] ?? null, true, 'production_authority_missing');
    foreach (['migration_execution_allowed','production_traffic_activation_allowed','target_selection_allowed','producer_dispatch_allowed'] as $field) pfpExecBool($plan['authority_binding'][$field] ?? null, false, 'authority_'.$field.'_forbidden');
    $from = $plan['authority_binding']['authorized_at_unix'] ?? null;
    $to = $plan['authority_binding']['expires_at_unix'] ?? null;
    if (! is_int($from) || ! is_int($to) || $from <= 0 || $to <= $from || ($to-$from) > 900 || time() < $from || time() >= $to) pfpExecFail('authority_not_current');

    pfpExecEq($plan['migration']['expected_count'] ?? null, 27, 'migration_count_invalid');
    pfpExecEq($plan['migration']['execution_state'] ?? null, 'ALREADY_EXECUTED_NO_REPLAY', 'migration_state_invalid');
    pfpExecBool($plan['migration']['execution_authorized'] ?? null, false, 'migration_execution_forbidden');
    pfpExecEq($plan['traffic_activation']['state'] ?? null, 'NOT_AUTHORIZED', 'traffic_state_invalid');
    pfpExecBool($plan['traffic_activation']['automatic_activation'] ?? null, false, 'traffic_automatic_forbidden');

    return [
        'plan_fingerprint'=>$fingerprint,'release_id'=>$releaseId,'source_commit'=>$source,'artifact_sha256'=>$artifact,
        'environment_id'=>$environment,'deployment_root'=>$deploymentRoot,'release_root'=>$releaseRoot,'release_directory'=>$releaseDirectory,
        'shared_root'=>$sharedRoot,'active_pointer'=>$activePointer,'document_root'=>$documentRoot,'activation_strategy'=>$strategy,
        'symlink_supported'=>$symlink,'hardlink_supported'=>$hardlink,'health_url'=>$healthUrl,
        'authority_id'=>$authorityId,'authority_sha256'=>$authoritySha,'request_id'=>$requestId,'request_sha256'=>$requestSha,
        'authority_authorized_at'=>$from,'authority_expires_at'=>$to,
    ];
}

function pfpExecRequireAuthorityCurrent(array $id): void
{
    $from = $id['authority_authorized_at'] ?? null;
    $to = $id['authority_expires_at'] ?? null;
    $now = time();
    if (! is_int($from) || ! is_int($to) || $now < $from || $now >= $to) pfpExecFail('authority_not_current_at_mutation');
}

/** @return array<string,mixed> */
function pfpExecValidateProfile(array $profile, array $id): array
{
    pfpExecEq($profile['schema_version'] ?? null, 1, 'profile_schema_invalid');
    pfpExecEq($profile['profile_state'] ?? null, 'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_TARGET_PROFILE_OBSERVED', 'profile_state_invalid');
    pfpExecEq($profile['target_class'] ?? null, 'CPANEL_NO_SSH', 'profile_target_class_invalid');
    pfpExecEq($profile['execution_channel'] ?? null, 'CPANEL_CRON_PHP_CLI_NO_SSH', 'profile_channel_invalid');
    pfpExecEq($profile['environment_id'] ?? null, $id['environment_id'], 'profile_environment_mismatch');
    pfpExecEq($profile['runtime_class'] ?? null, 'production', 'profile_runtime_invalid');
    pfpExecBool($profile['production'] ?? null, true, 'profile_production_required');
    pfpExecBool($profile['production_data_allowed'] ?? null, true, 'profile_data_required');
    pfpExecBool($profile['isolated_environment'] ?? null, true, 'profile_isolation_required');
    pfpExecEq($profile['release_binding']['release_id'] ?? null, $id['release_id'], 'profile_release_mismatch');
    pfpExecEq($profile['release_binding']['source_commit'] ?? null, $id['source_commit'], 'profile_source_mismatch');
    pfpExecEq($profile['release_binding']['artifact_sha256'] ?? null, $id['artifact_sha256'], 'profile_artifact_mismatch');
    foreach (['deployment_root'=>$id['deployment_root'],'release_root'=>$id['release_root'],'shared_runtime_root'=>$id['shared_root'],'active_release_pointer'=>$id['active_pointer'],'document_root'=>$id['document_root']] as $field=>$expected) pfpExecEq($profile['filesystem'][$field] ?? null, $expected, 'profile_'.$field.'_mismatch');
    pfpExecEq($profile['filesystem']['document_root_mode'] ?? null, 'FIXED_PUBLIC_BRIDGE', 'profile_document_root_mode_invalid');
    pfpExecEq($profile['filesystem']['activation_strategy'] ?? null, $id['activation_strategy'], 'profile_activation_strategy_mismatch');
    pfpExecEq($profile['filesystem']['symlink_supported'] ?? null, $id['symlink_supported'], 'profile_symlink_mismatch');
    pfpExecEq($profile['filesystem']['hardlink_supported'] ?? null, $id['hardlink_supported'], 'profile_hardlink_mismatch');
    foreach (['deployment_root_writable','release_root_writable','shared_runtime_root_writable','active_pointer_parent_writable','atomic_rename_supported','atomic_public_swap_supported','rewrite_to_index_verified','public_private_paths_disjoint'] as $field) pfpExecBool($profile['filesystem'][$field] ?? null, true, 'profile_'.$field.'_invalid');
    pfpExecEq($profile['health']['url'] ?? null, $id['health_url'], 'profile_health_url_mismatch');
    pfpExecEq($profile['health']['path'] ?? null, '/health/live', 'profile_health_path_invalid');
    pfpExecBool($profile['health']['https_required'] ?? null, true, 'profile_health_https_invalid');
    pfpExecBool($profile['configuration']['binding_file_private'] ?? null, true, 'profile_binding_private_invalid');
    pfpExecBool($profile['configuration']['required_binding_identity_matches'] ?? null, true, 'profile_binding_identity_invalid');
    pfpExecBool($profile['configuration']['secret_values_embedded'] ?? null, false, 'profile_secret_invalid');
    pfpExecEq($profile['attribution'] ?? null, 'Lab | zefry', 'profile_attribution_invalid');
    return $profile;
}

/** @return array<string,string> */
function pfpExecValidateBindings(array $bindings, array $id): array
{
    $expected = ['ONEQAY_PRODUCTION_ATTESTATION_TOKEN','ONEQAY_PRODUCTION_ENVIRONMENT_ID','ONEQAY_RUNNING_ARTIFACT_SHA256','ONEQAY_RUNNING_SOURCE_COMMIT','ONEQAY_RUNTIME_CLASS'];
    $actual = array_keys($bindings); sort($actual, SORT_STRING);
    if ($actual !== $expected) pfpExecFail('binding_set_invalid');
    foreach ($expected as $name) if (! is_string($bindings[$name] ?? null) || $bindings[$name] === '') pfpExecFail('binding_missing_'.$name);
    pfpExecEq($bindings['ONEQAY_RUNTIME_CLASS'], 'production', 'binding_runtime_invalid');
    pfpExecEq($bindings['ONEQAY_PRODUCTION_ENVIRONMENT_ID'], $id['environment_id'], 'binding_environment_invalid');
    pfpExecEq($bindings['ONEQAY_RUNNING_SOURCE_COMMIT'], $id['source_commit'], 'binding_source_invalid');
    pfpExecEq($bindings['ONEQAY_RUNNING_ARTIFACT_SHA256'], $id['artifact_sha256'], 'binding_artifact_invalid');
    $token = $bindings['ONEQAY_PRODUCTION_ATTESTATION_TOKEN'];
    if (strlen($token) < 32 || strlen($token) > 256 || str_contains($token, "\0")) pfpExecFail('attestation_token_invalid');
    return $bindings;
}

/** @return array{state:string,target:?string} */
function pfpExecPreviousPointer(string $activePointer, string $releaseRoot, string $newRelease): array
{
    if (! file_exists($activePointer) && ! is_link($activePointer)) return ['state'=>'ABSENT','target'=>null];
    if (! is_link($activePointer)) pfpExecFail('active_pointer_not_symlink');
    $target = realpath($activePointer); $root = realpath($releaseRoot);
    if (! is_string($target) || ! is_string($root) || ! is_dir($target) || ! str_starts_with($target.'/', rtrim($root,'/').'/')) pfpExecFail('previous_release_unavailable');
    if ($target === $newRelease) pfpExecFail('candidate_already_active');
    return ['state'=>'SYMLINK','target'=>$target];
}
function pfpExecAtomicPoint(string $activePointer, string $target): void
{
    if (! pfpExecFunctionAvailable('symlink')) pfpExecFail('symlink_unavailable');
    $parent = dirname($activePointer);
    if (! is_dir($parent) || ! is_writable($parent)) pfpExecFail('active_pointer_parent_not_writable');
    $tmp = $parent.'/.oneqay-production-fixed-public-active-'.bin2hex(random_bytes(8));
    try {
        if (! @symlink($target, $tmp)) pfpExecFail('active_pointer_stage_failed');
        if ((file_exists($activePointer) || is_link($activePointer)) && ! @unlink($activePointer)) pfpExecFail('active_pointer_remove_failed');
        if (! @rename($tmp, $activePointer)) pfpExecFail('active_pointer_commit_failed');
    } finally { if (is_link($tmp)) @unlink($tmp); }
    if (! is_link($activePointer) || realpath($activePointer) !== realpath($target)) pfpExecFail('active_pointer_verification_failed');
}
function pfpExecRestorePointer(string $activePointer, array $previous): void
{
    if (($previous['state'] ?? null) === 'ABSENT') {
        if (is_link($activePointer) && ! @unlink($activePointer)) pfpExecFail('rollback_pointer_remove_failed');
        return;
    }
    if (! is_string($previous['target'] ?? null)) pfpExecFail('rollback_pointer_target_invalid');
    pfpExecAtomicPoint($activePointer, $previous['target']);
}

function pfpExecRemoveTree(string $path): void
{
    if (! file_exists($path) && ! is_link($path)) return;
    if (is_link($path) || is_file($path)) { @unlink($path); return; }
    $items = scandir($path); if (! is_array($items)) return;
    foreach ($items as $item) if ($item !== '.' && $item !== '..') pfpExecRemoveTree($path.'/'.$item);
    @rmdir($path);
}

/** @return array<string,mixed> */
function pfpExecExtract(string $archivePath, array $id): array
{
    if (! is_file($archivePath) || is_link($archivePath) || ! is_readable($archivePath)) pfpExecFail('archive_unavailable');
    $size = filesize($archivePath);
    if (! is_int($size) || $size < 1024 || $size > 134217728) pfpExecFail('archive_size_invalid');
    pfpExecEq((string) hash_file('sha256', $archivePath), $id['artifact_sha256'], 'archive_sha256_mismatch');
    if (file_exists($id['release_directory']) || is_link($id['release_directory'])) pfpExecFail('release_directory_occupied');

    $stage = $id['release_root'].'/.oneqay-production-fixed-public-extract-'.bin2hex(random_bytes(8));
    if (! mkdir($stage, 0700)) pfpExecFail('extract_stage_failed');
    try {
        try { (new PharData($archivePath))->extractTo($stage, null, false); }
        catch (Throwable) { pfpExecFail('archive_extraction_failed'); }
        $root = $stage.'/'.$id['release_id'];
        if (! is_dir($root)) pfpExecFail('archive_release_root_missing');
        foreach (['RELEASE.json','apps/web/public/index.php','apps/web/public/.htaccess','apps/web/public/build/manifest.json','apps/web/vendor/autoload.php','apps/web/bootstrap/app.php'] as $required) {
            if (! is_file($root.'/'.$required) || is_link($root.'/'.$required)) pfpExecFail('required_file_missing_'.$required);
        }
        if (file_exists($root.'/apps/web/.env') || is_link($root.'/apps/web/.env')) pfpExecFail('embedded_runtime_env_forbidden');
        $release = pfpExecLoad($root.'/RELEASE.json');
        pfpExecEq($release['product'] ?? null, 'oneQay', 'release_product_invalid');
        pfpExecEq($release['release_id'] ?? null, $id['release_id'], 'release_id_invalid');
        pfpExecEq($release['environment'] ?? null, 'PRODUCTION', 'release_environment_invalid');
        pfpExecEq($release['required_runtime_class'] ?? null, 'production', 'release_runtime_invalid');
        pfpExecBool($release['production'] ?? null, true, 'release_production_invalid');
        pfpExecBool($release['production_data_allowed'] ?? null, true, 'release_data_invalid');
        pfpExecEq($release['source_commit'] ?? null, $id['source_commit'], 'release_source_invalid');
        pfpExecBool($release['business_runtime_activation_ready'] ?? null, false, 'release_business_runtime_must_be_dark');
        pfpExecEq($release['dark_deploy_health_endpoint'] ?? null, '/health/live', 'release_health_invalid');
        pfpExecEq($release['migration_count'] ?? null, 27, 'release_migration_count_invalid');
        pfpExecEq($release['migration_execution_state'] ?? null, 'NOT_PERFORMED_BY_ARTIFACT_BUILD', 'release_migration_state_invalid');
        pfpExecBool($release['migration_execution_authorized'] ?? null, false, 'release_migration_forbidden');
        pfpExecEq($release['production_traffic_activation'] ?? null, 'NOT_AUTHORIZED', 'release_traffic_invalid');
        pfpExecEq($release['production_activation'] ?? null, 'NOT_AUTHORIZED', 'release_activation_invalid');
        $migrations = glob($root.'/apps/web/database/migrations/*.php');
        if (! is_array($migrations) || count($migrations) !== 27) pfpExecFail('migration_count_invalid');
        if (! rename($root, $id['release_directory'])) pfpExecFail('release_commit_failed');
        return $release;
    } finally { pfpExecRemoveTree($stage); }
}

function pfpExecBindEnv(string $runtimeEnvPath, array $id): string
{
    $real = pfpExecPrivateFile($runtimeEnvPath, $id['shared_root'], 'runtime_env');
    $size = filesize($real);
    if (! is_int($size) || $size < 2 || $size > 1048576) pfpExecFail('runtime_env_size_invalid');
    $hash = hash_file('sha256', $real);
    if (! is_string($hash)) pfpExecFail('runtime_env_hash_failed');
    $target = $id['release_directory'].'/apps/web/.env';
    if (file_exists($target) || is_link($target)) pfpExecFail('runtime_env_path_occupied');
    if ($id['symlink_supported'] === true) {
        if (! pfpExecFunctionAvailable('symlink') || ! @symlink($real, $target) || ! is_link($target) || realpath($target) !== $real) pfpExecFail('runtime_env_symlink_failed');
    } else {
        if ($id['hardlink_supported'] !== true || ! pfpExecFunctionAvailable('link') || ! @link($real, $target) || is_link($target)) pfpExecFail('runtime_env_hardlink_failed');
        $a = fileinode($real); $b = fileinode($target);
        if (! is_int($a) || ! is_int($b) || $a !== $b) pfpExecFail('runtime_env_hardlink_verification_failed');
    }
    $after = hash_file('sha256', $target);
    if (! is_string($after) || ! hash_equals($hash, $after)) pfpExecFail('runtime_env_readback_failed');
    return $hash;
}

/** @param array<string,mixed> $value */
function pfpExecValidateHealth(array $value): void
{
    pfpExecEq($value['status'] ?? null, 'ok', 'health_status_invalid');
    pfpExecEq($value['service'] ?? null, 'oneqay-web', 'health_service_invalid');
    if (! is_string($value['correlation_id'] ?? null) || $value['correlation_id'] === '') pfpExecFail('health_correlation_invalid');
}
/** @return array<string,mixed> */
function pfpExecFetchHealth(string $url): array
{
    $body = null;
    if (extension_loaded('curl') && function_exists('curl_init')) {
        $h = curl_init($url); if ($h === false) pfpExecFail('health_client_failed');
        curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Accept: application/json'],CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS]);
        $r = curl_exec($h); $status = curl_getinfo($h, CURLINFO_RESPONSE_CODE); curl_close($h);
        if (! is_string($r) || $status !== 200) pfpExecFail('health_http_failed');
        $body = $r;
    } else {
        $context = stream_context_create(['http'=>['method'=>'GET','timeout'=>20,'ignore_errors'=>true,'header'=>"Accept: application/json\r\n"],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true]]);
        $r = @file_get_contents($url, false, $context);
        if (! is_string($r)) pfpExecFail('health_http_failed');
        $body = $r;
    }
    try { $value = json_decode($body, true, 32, JSON_THROW_ON_ERROR); }
    catch (JsonException) { pfpExecFail('health_json_invalid'); }
    if (! is_array($value) || array_is_list($value)) pfpExecFail('health_shape_invalid');
    pfpExecValidateHealth($value);
    return $value;
}

/** @param array<string,mixed> $state */
function pfpExecPersistRollbackSnapshot(array $state, array $id): string
{
    $root = $id['shared_root'].'/production-fixed-public-rollbacks/'.$id['release_id'].'-'.bin2hex(random_bytes(6));
    if (! is_dir(dirname($root)) && ! mkdir(dirname($root), 0700, true)) pfpExecFail('rollback_snapshot_parent_create_failed');
    if (! mkdir($root, 0700)) pfpExecFail('rollback_snapshot_create_failed');
    $indexExisted = ($state['index_existed'] ?? null) === true;
    $buildExisted = ($state['build_existed'] ?? null) === true;
    if ($indexExisted) {
        $source = (string) ($state['index_backup'] ?? '');
        if (! is_file($source) || is_link($source) || ! copy($source, $root.'/index.php')) pfpExecFail('rollback_index_snapshot_failed');
        @chmod($root.'/index.php', 0600);
    }
    if ($buildExisted) {
        $source = (string) ($state['build_backup'] ?? '');
        if (! is_dir($source) || is_link($source)) pfpExecFail('rollback_build_snapshot_unavailable');
        cpanelBridgeCopyTree($source, $root.'/build');
    }
    $meta = [
        'schema_version'=>1,
        'snapshot_state'=>'PRODUCTION_FIXED_PUBLIC_PREVIOUS_SURFACE_PRESERVED',
        'release_id'=>$id['release_id'],
        'document_root'=>$id['document_root'],
        'previous_index_existed'=>$indexExisted,
        'previous_build_existed'=>$buildExisted,
        'attribution'=>'Lab | zefry',
    ];
    $json = json_encode($meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
    if (file_put_contents($root.'/ROLLBACK.json', $json, LOCK_EX) !== strlen($json)) pfpExecFail('rollback_snapshot_metadata_failed');
    @chmod($root.'/ROLLBACK.json', 0600);
    return $root;
}

function pfpExecWrite(string $path, array $payload): void
{
    $dir = dirname($path);
    if ($path === '' || is_link($path) || is_dir($path) || ! is_dir($dir) || ! is_writable($dir)) pfpExecFail('output_invalid');
    $json = json_encode($payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
    $tmp = tempnam($dir, '.oneqay-production-fixed-public-evidence-');
    if ($tmp === false) pfpExecFail('output_temp_failed');
    try {
        if (file_put_contents($tmp, $json, LOCK_EX) !== strlen($json)) pfpExecFail('output_write_failed');
        @chmod($tmp, 0600);
        if (! rename($tmp, $path)) pfpExecFail('output_commit_failed');
        @chmod($path, 0600);
    } finally { if (is_file($tmp)) @unlink($tmp); }
}

/** @param callable(string):array<string,mixed> $healthFetcher @return array<string,mixed> */
function pfpExecExecute(string $planPath,string $profilePath,string $archivePath,string $bindingsPath,string $runtimeEnvPath,string $evidencePath,callable $healthFetcher): array
{
    $id = null;
    $previousPointer = ['state'=>'ABSENT','target'=>null];
    $bridgeState = null;
    $mutated = false;
    try {
        $plan = pfpExecLoad($planPath);
        $profile = pfpExecLoad($profilePath);
        $id = pfpExecValidatePlan($plan);
        pfpExecValidateProfile($profile, $id);
        foreach ([$id['deployment_root'],$id['release_root'],$id['shared_root'],dirname($id['active_pointer']),$id['document_root']] as $dir) {
            if (! is_dir($dir) || ! is_writable($dir)) pfpExecFail('required_directory_not_writable');
        }
        $bindingsReal = pfpExecPrivateFile($bindingsPath, $id['shared_root'], 'private_bindings');
        pfpExecValidateBindings(pfpExecLoad($bindingsReal, true), $id);
        if ($id['symlink_supported'] === true) $previousPointer = pfpExecPreviousPointer($id['active_pointer'],$id['release_root'],$id['release_directory']);

        pfpExecExtract($archivePath, $id);
        $envHash = pfpExecBindEnv($runtimeEnvPath, $id);

        pfpExecRequireAuthorityCurrent($id);
        if ($id['symlink_supported'] === true) pfpExecAtomicPoint($id['active_pointer'], $id['release_directory']);
        $bridgeState = cpanelBridgeInstall($id['document_root'],$id['active_pointer'],$id['release_directory'],$id['shared_root'],$id['symlink_supported'] !== true);
        $mutated = true;

        $first = $healthFetcher($id['health_url']);
        if (! is_array($first) || array_is_list($first)) pfpExecFail('health_fetcher_shape_invalid');
        pfpExecValidateHealth($first);

        cpanelBridgeRestore($bridgeState);
        cpanelBridgeFinalize($bridgeState);
        $bridgeState = null;
        if ($id['symlink_supported'] === true) pfpExecRestorePointer($id['active_pointer'], $previousPointer);

        pfpExecRequireAuthorityCurrent($id);
        if ($id['symlink_supported'] === true) pfpExecAtomicPoint($id['active_pointer'], $id['release_directory']);
        $bridgeState = cpanelBridgeInstall($id['document_root'],$id['active_pointer'],$id['release_directory'],$id['shared_root'],$id['symlink_supported'] !== true);
        $second = $healthFetcher($id['health_url']);
        if (! is_array($second) || array_is_list($second)) pfpExecFail('health_fetcher_shape_invalid');
        pfpExecValidateHealth($second);

        $after = hash_file('sha256', $id['release_directory'].'/apps/web/.env');
        if (! is_string($after) || ! hash_equals($envHash, $after)) pfpExecFail('runtime_env_post_reactivation_invalid');
        $release = pfpExecLoad($id['release_directory'].'/RELEASE.json');
        pfpExecEq($release['source_commit'] ?? null, $id['source_commit'], 'runtime_source_readback_invalid');
        pfpExecBool($release['business_runtime_activation_ready'] ?? null, false, 'runtime_business_activation_must_be_dark');
        pfpExecEq($release['production_traffic_activation'] ?? null, 'NOT_AUTHORIZED', 'runtime_traffic_must_be_dark');

        $rollbackSnapshot = pfpExecPersistRollbackSnapshot($bridgeState, $id);
        cpanelBridgeFinalize($bridgeState);
        $bridgeState = null;

        $evidence = [
            'schema_version'=>1,
            'evidence_state'=>'PRODUCTION_DEPLOYMENT_EVIDENCE_CANDIDATE',
            'evidence_variant'=>'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_SUCCESSOR_V1',
            'environment_id'=>$id['environment_id'],
            'runtime_class'=>'production',
            'release_id'=>$id['release_id'],
            'source_commit'=>$id['source_commit'],
            'artifact_sha256'=>$id['artifact_sha256'],
            'deployment_plan_fingerprint'=>$id['plan_fingerprint'],
            'presentation'=>[
                'document_root_mode'=>'FIXED_PUBLIC_BRIDGE',
                'activation_strategy'=>$id['activation_strategy'],
                'php_symlink_supported'=>$id['symlink_supported'],
                'php_hardlink_supported'=>$id['hardlink_supported'],
                'private_rollback_snapshot_retained'=>true,
                'private_rollback_snapshot_sha256'=>hash('sha256', $rollbackSnapshot),
            ],
            'verification'=>[
                'preflight_passed'=>true,
                'previous_active_release_preserved'=>true,
                'immutable_release_extracted'=>true,
                'public_document_root_verified'=>true,
                'external_runtime_configuration_bound'=>true,
                'provenance_readback_verified'=>true,
                'configuration_read_before_write_verified'=>true,
                'configuration_read_after_write_verified'=>true,
                'non_mutating_health_attestation_verified'=>true,
                'rollback_path_verified'=>true,
                'candidate_restored_after_rollback_rehearsal'=>true,
                'fixed_public_bridge_verified'=>true,
                'provider_disable_functions_policy_respected'=>true,
            ],
            'runtime_readback'=>[
                'environment_id'=>$id['environment_id'],
                'runtime_class'=>'production',
                'exact_running_source_commit'=>$id['source_commit'],
                'exact_running_artifact_sha256'=>$id['artifact_sha256'],
                'business_runtime_activation_ready'=>false,
                'production_traffic_active'=>false,
                'dark_health_status'=>'ok',
                'dark_health_service'=>'oneqay-web',
            ],
            'operational_boundary'=>[
                'migration27_execution'=>'ALREADY_EXECUTED_NO_REPLAY',
                'permission_provisioning'=>'ALREADY_PROVISIONED_NO_REPLAY',
                'feature_activation'=>'ACTIVE_PRESERVED_NO_REACTIVATION',
                'technical_preview_activation'=>'NOT_AUTHORIZED',
                'production_business_traffic_activation'=>'NOT_AUTHORIZED',
                'updater_activation'=>'INACTIVE',
                'target_reselection'=>'NOT_AUTHORIZED',
                'producer_dispatch'=>'NOT_AUTHORIZED',
            ],
            'secrets_embedded'=>false,
            'attribution'=>'Lab | zefry',
        ];
        pfpExecWrite($evidencePath, $evidence);
        return $evidence;
    } catch (Throwable $failure) {
        if (is_array($bridgeState)) {
            try { cpanelBridgeRestore($bridgeState); cpanelBridgeFinalize($bridgeState); } catch (Throwable) {}
        }
        if ($mutated && is_array($id) && ($id['symlink_supported'] ?? false) === true) {
            try { pfpExecRestorePointer($id['active_pointer'], $previousPointer); } catch (Throwable) {}
        }
        throw $failure;
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 7) {
        fwrite(STDERR, "Usage: php tools/production/cpanel/execute-production-fixed-public-cpanel-no-ssh-dark-deployment.php <deployment-plan.json> <target-profile.json> <production-artifact.tar.gz> <private-bindings.json> <private-runtime-env> <deployment-evidence-candidate.json>\n");
        exit(64);
    }
    try {
        pfpExecExecute($argv[1],$argv[2],$argv[3],$argv[4],$argv[5],$argv[6],'pfpExecFetchHealth');
        fwrite(STDOUT, "production_fixed_public_dark_deployment_verified_not_activated\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "production_fixed_public_dark_deployment_failed:".$failure->getMessage()."\n");
        exit(1);
    }
}
