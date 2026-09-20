<?php

declare(strict_types=1);

// Author by Lab | zefry

final class ProductionDarkDeploymentExecutionException extends RuntimeException {}

function prodExecFail(string $code): never
{
    throw new ProductionDarkDeploymentExecutionException($code);
}

/** @return array<string,mixed> */
function prodExecLoadJson(string $path, bool $private = false): array
{
    if ($path === '' || str_contains($path, "\0") || ! is_file($path) || is_link($path) || ! is_readable($path)) {
        prodExecFail('input_unavailable');
    }
    $size = filesize($path);
    if (! is_int($size) || $size < 2 || $size > 524288) prodExecFail('input_size_invalid');
    if ($private) {
        $mode = fileperms($path);
        if (! is_int($mode) || (($mode & 0077) !== 0)) prodExecFail('private_file_permissions_invalid');
    }
    $raw = file_get_contents($path);
    if (! is_string($raw)) prodExecFail('input_read_failed');
    try { $value = json_decode($raw, true, 64, JSON_THROW_ON_ERROR); }
    catch (JsonException) { prodExecFail('input_json_invalid'); }
    if (! is_array($value) || array_is_list($value)) prodExecFail('input_shape_invalid');
    return $value;
}

function prodExecEq(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) prodExecFail($code);
}

function prodExecBool(mixed $actual, bool $expected, string $code): void
{
    if (! is_bool($actual) || $actual !== $expected) prodExecFail($code);
}

function prodExecPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) prodExecFail($code);
    return $value;
}

function prodExecPath(mixed $value, string $code): string
{
    if (! is_string($value) || $value === '' || $value === '/' || strlen($value) > 4096
        || ! str_starts_with($value, '/') || str_contains($value, "\0") || str_contains($value, '\\')
        || preg_match('#(?:^|/)\\.{1,2}(?:/|$)#', $value) === 1 || preg_match('#//+#', $value) === 1) {
        prodExecFail($code);
    }
    return rtrim($value, '/');
}

function prodExecNested(string $child, string $parent, string $code): void
{
    if (! str_starts_with($child.'/', $parent.'/')) prodExecFail($code);
}

/** @return array<string,mixed>|list<mixed> */
function prodExecCanonicalize(array $value): array
{
    if (array_is_list($value)) {
        return array_map(static fn (mixed $v): mixed => is_array($v) ? prodExecCanonicalize($v) : $v, $value);
    }
    ksort($value, SORT_STRING);
    foreach ($value as $key => $item) if (is_array($item)) $value[$key] = prodExecCanonicalize($item);
    return $value;
}

function prodExecCanonical(array $value): string
{
    return json_encode(prodExecCanonicalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

/** @return list<string> */
function prodExecDisabledFunctions(): array
{
    $raw = (string) ini_get('disable_functions');
    return $raw === '' ? [] : array_values(array_filter(array_map('trim', explode(',', $raw))));
}

function prodExecFunctionAvailable(string $name): bool
{
    return function_exists($name) && ! in_array($name, prodExecDisabledFunctions(), true);
}

function prodExecPrivateFile(string $path, string $sharedRoot, string $code): string
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) prodExecFail($code.'_unavailable');
    $mode = fileperms($path);
    if (! is_int($mode) || (($mode & 0077) !== 0)) prodExecFail($code.'_permissions_invalid');
    $real = realpath($path);
    $shared = realpath($sharedRoot);
    if (! is_string($real) || ! is_string($shared) || ! str_starts_with($real.'/', rtrim($shared, '/').'/')) {
        prodExecFail($code.'_outside_shared_runtime_root');
    }
    return $real;
}

/** @return array<string,mixed> */
function prodExecValidatePlan(array $plan): array
{
    prodExecEq($plan['schema_version'] ?? null, 1, 'plan_schema_invalid');
    prodExecEq($plan['plan_state'] ?? null, 'QUALIFIED_FOR_PRODUCTION_DEPLOYMENT_NOT_EXECUTED_NOT_ACTIVATED', 'plan_state_invalid');
    $fingerprint = prodExecPattern($plan['plan_fingerprint'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'plan_fingerprint_invalid');
    $core = $plan;
    unset($core['plan_fingerprint']);
    prodExecEq(hash('sha256', prodExecCanonical($core)), $fingerprint, 'plan_fingerprint_mismatch');

    $releaseId = prodExecPattern($plan['artifact']['release_id'] ?? null, '/\\Aproduction-[0-9a-f]{12}\\z/', 'release_id_invalid');
    $source = prodExecPattern($plan['artifact']['source_commit'] ?? null, '/\\A[0-9a-f]{40}\\z/', 'source_invalid');
    $artifactSha = prodExecPattern($plan['artifact']['artifact_sha256'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'artifact_sha_invalid');
    prodExecPattern($plan['artifact']['manifest_sha256'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'manifest_sha_invalid');
    prodExecEq($releaseId, 'production-'.substr($source, 0, 12), 'release_source_mismatch');

    $environment = prodExecPattern($plan['target']['environment_id'] ?? null, '/\\A[a-z0-9][a-z0-9-]{2,62}\\z/', 'environment_invalid');
    $targetClass = prodExecPattern($plan['target']['target_class'] ?? null, '/\\A(?:OPERATOR_MANAGED_FILESYSTEM|CPANEL_NO_SSH)\\z/', 'target_class_invalid');
    $channel = prodExecPattern($plan['target']['execution_channel'] ?? null, '/\\A(?:PHP_CLI|CPANEL_CRON_PHP_CLI_NO_SSH)\\z/', 'execution_channel_invalid');
    if ($targetClass === 'CPANEL_NO_SSH' && $channel !== 'CPANEL_CRON_PHP_CLI_NO_SSH') prodExecFail('cpanel_channel_invalid');
    prodExecEq($plan['target']['runtime_class'] ?? null, 'production', 'runtime_invalid');
    prodExecBool($plan['target']['production'] ?? null, true, 'production_required');
    prodExecBool($plan['target']['production_data_allowed'] ?? null, true, 'production_data_required');

    $deploymentRoot = prodExecPath($plan['target']['deployment_root'] ?? null, 'deployment_root_invalid');
    $releaseRoot = prodExecPath($plan['target']['release_root'] ?? null, 'release_root_invalid');
    $releaseDirectory = prodExecPath($plan['target']['release_directory'] ?? null, 'release_directory_invalid');
    $sharedRoot = prodExecPath($plan['target']['shared_runtime_root'] ?? null, 'shared_root_invalid');
    $activePointer = prodExecPath($plan['target']['active_release_pointer'] ?? null, 'active_pointer_invalid');
    $documentRoot = prodExecPath($plan['target']['document_root'] ?? null, 'document_root_invalid');

    foreach ([$releaseRoot, $sharedRoot, $activePointer] as $path) prodExecNested($path, $deploymentRoot, 'filesystem_escape');
    prodExecEq($releaseDirectory, $releaseRoot.'/'.$releaseId, 'release_directory_mismatch');
    prodExecEq($documentRoot, $activePointer.'/apps/web/public', 'document_root_shape_invalid');

    $healthUrl = $plan['target']['health_url'] ?? null;
    $healthPath = $plan['target']['health_path'] ?? null;
    prodExecEq($healthPath, '/health/live', 'health_path_invalid');
    if (! is_string($healthUrl) || strlen($healthUrl) > 2048) prodExecFail('health_url_invalid');
    $parts = parse_url($healthUrl);
    if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https' || ($parts['path'] ?? '') !== '/health/live'
        || ! is_string($parts['host'] ?? null) || ($parts['host'] ?? '') === ''
        || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
        prodExecFail('health_url_invalid');
    }

    prodExecEq($plan['authority_binding']['state'] ?? null, 'EXTERNAL_AUTHORITY_BOUND_TO_EXACT_PRODUCTION_TARGET', 'authority_state_invalid');
    $authorityId = prodExecPattern($plan['authority_binding']['authority_id'] ?? null, '/\\Aproduction-deployment-authority-[0-9a-f]{24}\\z/', 'authority_id_invalid');
    $authoritySha = prodExecPattern($plan['authority_binding']['authority_sha256'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'authority_sha_invalid');
    $requestId = prodExecPattern($plan['authority_binding']['request_id'] ?? null, '/\\Aproduction-deployment-request-[0-9a-f]{24}\\z/', 'request_id_invalid');
    $requestSha = prodExecPattern($plan['authority_binding']['request_sha256'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'request_sha_invalid');
    prodExecPattern($plan['authority_binding']['target_descriptor_sha256'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'target_descriptor_sha_invalid');
    prodExecBool($plan['authority_binding']['deployment_allowed'] ?? null, true, 'deployment_authority_missing');
    prodExecBool($plan['authority_binding']['production_allowed'] ?? null, true, 'production_authority_missing');
    prodExecBool($plan['authority_binding']['migration_execution_allowed'] ?? null, false, 'migration_authority_forbidden');
    prodExecBool($plan['authority_binding']['production_traffic_activation_allowed'] ?? null, false, 'traffic_authority_forbidden');

    $from = $plan['authority_binding']['authorized_at_unix'] ?? null;
    $to = $plan['authority_binding']['expires_at_unix'] ?? null;
    if (! is_int($from) || ! is_int($to) || $from <= 0 || $to <= $from || ($to - $from) > 900) prodExecFail('authority_window_invalid');
    $now = time();
    if ($now < $from || $now >= $to) prodExecFail('authority_not_current');

    prodExecEq($plan['readback_expectations']['environment_id'] ?? null, $environment, 'readback_environment_invalid');
    prodExecEq($plan['readback_expectations']['runtime_class'] ?? null, 'production', 'readback_runtime_invalid');
    prodExecEq($plan['readback_expectations']['running_source_commit'] ?? null, $source, 'readback_source_invalid');
    prodExecEq($plan['readback_expectations']['running_artifact_sha256'] ?? null, $artifactSha, 'readback_artifact_invalid');
    prodExecBool($plan['readback_expectations']['business_runtime_activation_ready'] ?? null, false, 'business_runtime_must_be_dark');
    prodExecBool($plan['readback_expectations']['production_traffic_active'] ?? null, false, 'traffic_must_be_dark');
    prodExecEq($plan['readback_expectations']['health_path'] ?? null, '/health/live', 'readback_health_invalid');

    prodExecBool($plan['rollback']['required'] ?? null, true, 'rollback_required');
    prodExecBool($plan['rollback']['previous_active_release_must_be_preserved'] ?? null, true, 'previous_release_preservation_required');
    prodExecBool($plan['rollback']['rollback_must_be_verified_before_acceptance'] ?? null, true, 'rollback_verification_required');
    prodExecBool($plan['rollback']['candidate_must_be_restored_after_rehearsal'] ?? null, true, 'candidate_restore_required');
    prodExecBool($plan['rollback']['database_rollback_implied'] ?? null, false, 'database_rollback_forbidden');
    prodExecBool($plan['rollback']['migration_rollback_implied'] ?? null, false, 'migration_rollback_forbidden');
    prodExecEq($plan['migration']['expected_count'] ?? null, 27, 'migration_count_invalid');
    prodExecEq($plan['migration']['execution_state'] ?? null, 'NOT_PERFORMED', 'migration_state_invalid');
    prodExecBool($plan['migration']['execution_authorized'] ?? null, false, 'migration_execution_forbidden');
    prodExecEq($plan['traffic_activation']['state'] ?? null, 'NOT_AUTHORIZED', 'traffic_state_invalid');
    prodExecBool($plan['traffic_activation']['automatic_activation'] ?? null, false, 'automatic_traffic_forbidden');

    return [
        'plan_fingerprint'=>$fingerprint,'release_id'=>$releaseId,'source_commit'=>$source,'artifact_sha256'=>$artifactSha,
        'environment_id'=>$environment,'target_class'=>$targetClass,'execution_channel'=>$channel,
        'deployment_root'=>$deploymentRoot,'release_root'=>$releaseRoot,'release_directory'=>$releaseDirectory,
        'shared_root'=>$sharedRoot,'active_pointer'=>$activePointer,'document_root'=>$documentRoot,
        'health_url'=>$healthUrl,'authority_id'=>$authorityId,'authority_sha256'=>$authoritySha,
        'request_id'=>$requestId,'request_sha256'=>$requestSha,
        'authority_authorized_at'=>$from,'authority_expires_at'=>$to,
    ];
}

function prodExecRequireAuthorityCurrent(array $id): void
{
    $from=$id['authority_authorized_at'] ?? null;
    $to=$id['authority_expires_at'] ?? null;
    $now=time();
    if (! is_int($from) || ! is_int($to) || $now < $from || $now >= $to) {
        prodExecFail('authority_not_current_at_mutation');
    }
}

/** @return array<string,mixed> */
function prodExecValidateProfile(array $profile, array $id): array
{
    prodExecEq($profile['schema_version'] ?? null, 1, 'profile_schema_invalid');
    prodExecEq($profile['profile_state'] ?? null, 'PRODUCTION_OPERATOR_TARGET_PROFILE_OBSERVED', 'profile_state_invalid');
    foreach (['target_class','execution_channel','environment_id'] as $field) {
        prodExecEq($profile[$field] ?? null, $id[$field], 'profile_'.$field.'_mismatch');
    }
    prodExecEq($profile['runtime_class'] ?? null, 'production', 'profile_runtime_invalid');
    prodExecBool($profile['production'] ?? null, true, 'profile_production_required');
    prodExecBool($profile['production_data_allowed'] ?? null, true, 'profile_production_data_required');
    prodExecBool($profile['isolated_environment'] ?? null, true, 'profile_isolation_required');
    prodExecEq($profile['release_binding']['release_id'] ?? null, $id['release_id'], 'profile_release_mismatch');
    prodExecEq($profile['release_binding']['source_commit'] ?? null, $id['source_commit'], 'profile_source_mismatch');
    prodExecEq($profile['release_binding']['artifact_sha256'] ?? null, $id['artifact_sha256'], 'profile_artifact_mismatch');

    foreach ([
        'deployment_root'=>$id['deployment_root'],'release_root'=>$id['release_root'],
        'shared_runtime_root'=>$id['shared_root'],'active_release_pointer'=>$id['active_pointer'],
        'document_root'=>$id['document_root'],
    ] as $field=>$expected) prodExecEq($profile['filesystem'][$field] ?? null, $expected, 'profile_'.$field.'_mismatch');

    foreach (['deployment_root_writable','release_root_writable','shared_runtime_root_writable','active_pointer_parent_writable','atomic_rename_supported','symlink_supported','document_root_shape_valid'] as $field) {
        prodExecBool($profile['filesystem'][$field] ?? null, true, 'profile_'.$field.'_invalid');
    }
    prodExecEq($profile['health']['url'] ?? null, $id['health_url'], 'profile_health_url_mismatch');
    prodExecEq($profile['health']['path'] ?? null, '/health/live', 'profile_health_path_invalid');
    prodExecBool($profile['health']['https_required'] ?? null, true, 'profile_https_required');
    prodExecBool($profile['runtime']['php_version_supported'] ?? null, true, 'profile_php_unsupported');
    prodExecBool($profile['runtime']['required_extensions_present'] ?? null, true, 'profile_extensions_missing');
    prodExecBool($profile['runtime']['https_client_available'] ?? null, true, 'profile_https_client_missing');
    prodExecBool($profile['configuration']['binding_file_private'] ?? null, true, 'profile_binding_private_invalid');
    prodExecBool($profile['configuration']['required_binding_identity_matches'] ?? null, true, 'profile_binding_identity_invalid');
    prodExecBool($profile['configuration']['secret_values_embedded'] ?? null, false, 'profile_secret_forbidden');
    foreach (['durable_database_persistence','durable_session','authorization','transaction_durability','pos_durability','authenticated_configuration_channel','read_before_write','read_after_write','non_mutating_health_attestation','verified_rollback'] as $capability) {
        prodExecBool($profile['operator_assertions'][$capability] ?? null, true, 'profile_capability_'.$capability.'_invalid');
    }
    prodExecBool($profile['secrets_embedded'] ?? null, false, 'profile_top_secret_forbidden');
    prodExecEq($profile['attribution'] ?? null, 'Lab | zefry', 'profile_attribution_invalid');
    return $profile;
}

/** @return array<string,string> */
function prodExecValidateBindings(array $bindings, array $id): array
{
    $expected = ['ONEQAY_PRODUCTION_ATTESTATION_TOKEN','ONEQAY_PRODUCTION_ENVIRONMENT_ID','ONEQAY_RUNNING_ARTIFACT_SHA256','ONEQAY_RUNNING_SOURCE_COMMIT','ONEQAY_RUNTIME_CLASS'];
    $actual = array_keys($bindings);
    sort($actual, SORT_STRING);
    if ($actual !== $expected) prodExecFail('binding_set_invalid');
    foreach ($expected as $name) if (! is_string($bindings[$name] ?? null) || $bindings[$name] === '' || str_contains($bindings[$name], "\0")) prodExecFail('binding_value_invalid');
    prodExecEq($bindings['ONEQAY_RUNTIME_CLASS'], 'production', 'binding_runtime_invalid');
    prodExecEq($bindings['ONEQAY_RUNNING_SOURCE_COMMIT'], $id['source_commit'], 'binding_source_invalid');
    prodExecEq($bindings['ONEQAY_RUNNING_ARTIFACT_SHA256'], $id['artifact_sha256'], 'binding_artifact_invalid');
    prodExecEq($bindings['ONEQAY_PRODUCTION_ENVIRONMENT_ID'], $id['environment_id'], 'binding_environment_invalid');
    $token=$bindings['ONEQAY_PRODUCTION_ATTESTATION_TOKEN'];
    if (strlen($token)<32 || strlen($token)>256) prodExecFail('attestation_token_invalid');
    return $bindings;
}

/** @return array{state:string,target:?string} */
function prodExecPrevious(string $activePointer, string $releaseRoot, string $newRelease): array
{
    if (! file_exists($activePointer) && ! is_link($activePointer)) return ['state'=>'ABSENT','target'=>null];
    if (! is_link($activePointer)) prodExecFail('active_pointer_not_symlink');
    $target=realpath($activePointer); $root=realpath($releaseRoot);
    if (! is_string($target) || ! is_string($root) || ! is_dir($target)) prodExecFail('previous_release_unavailable');
    if (! str_starts_with($target.'/', rtrim($root,'/').'/')) prodExecFail('previous_release_escape');
    if ($target === $newRelease) prodExecFail('candidate_already_active');
    return ['state'=>'SYMLINK','target'=>$target];
}

function prodExecAtomicPoint(string $activePointer, string $target): void
{
    if (! prodExecFunctionAvailable('symlink')) prodExecFail('symlink_unavailable');
    $parent=dirname($activePointer);
    if (! is_dir($parent) || ! is_writable($parent)) prodExecFail('active_pointer_parent_not_writable');
    $tmp=$parent.'/.oneqay-production-active-'.bin2hex(random_bytes(8));
    try {
        if (! symlink($target,$tmp)) prodExecFail('temp_symlink_failed');
        if (! rename($tmp,$activePointer)) prodExecFail('active_pointer_replace_failed');
    } finally { if (is_link($tmp)) @unlink($tmp); }
    if (! is_link($activePointer) || realpath($activePointer)!==realpath($target)) prodExecFail('active_pointer_verification_failed');
}

/** @param array{state:string,target:?string} $previous */
function prodExecRestore(string $activePointer, array $previous): void
{
    if ($previous['state']==='ABSENT') {
        if (is_link($activePointer) && ! unlink($activePointer)) prodExecFail('rollback_remove_failed');
        if (file_exists($activePointer) || is_link($activePointer)) prodExecFail('rollback_absent_verification_failed');
        return;
    }
    if (! is_string($previous['target'])) prodExecFail('rollback_target_invalid');
    prodExecAtomicPoint($activePointer,$previous['target']);
}

function prodExecRollbackBestEffort(string $activePointer, array $previous): void
{
    try { prodExecRestore($activePointer,$previous); } catch (Throwable) {}
}

function prodExecRemoveTree(string $path): void
{
    if (! file_exists($path) && ! is_link($path)) return;
    if (is_link($path) || is_file($path)) { @unlink($path); return; }
    $items=scandir($path); if (! is_array($items)) return;
    foreach ($items as $item) if ($item!=='.' && $item!=='..') prodExecRemoveTree($path.'/'.$item);
    @rmdir($path);
}

/** @return array<string,mixed> */
function prodExecExtract(string $archivePath, array $id): array
{
    if (! is_file($archivePath) || is_link($archivePath) || ! is_readable($archivePath)) prodExecFail('archive_unavailable');
    $size=filesize($archivePath);
    if (! is_int($size) || $size<1024 || $size>134217728) prodExecFail('archive_size_invalid');
    prodExecEq(hash_file('sha256',$archivePath),$id['artifact_sha256'],'archive_sha256_mismatch');
    if (file_exists($id['release_directory']) || is_link($id['release_directory'])) prodExecFail('release_directory_exists');
    if (! is_dir($id['release_root']) || ! is_writable($id['release_root'])) prodExecFail('release_root_not_writable');

    $stage=$id['release_root'].'/.oneqay-production-extract-'.bin2hex(random_bytes(8));
    if (! mkdir($stage,0700)) prodExecFail('extract_stage_failed');
    try {
        try { (new PharData($archivePath))->extractTo($stage,null,false); }
        catch (Throwable) { prodExecFail('archive_extraction_failed'); }
        $root=$stage.'/'.$id['release_id'];
        if (! is_dir($root)) prodExecFail('archive_release_root_missing');
        foreach (['RELEASE.json','apps/web/public/index.php','apps/web/public/.htaccess','apps/web/vendor/autoload.php','apps/web/bootstrap/app.php'] as $required) {
            if (! is_file($root.'/'.$required)) prodExecFail('required_file_missing');
        }
        if (file_exists($root.'/apps/web/.env') || is_link($root.'/apps/web/.env')) prodExecFail('embedded_runtime_env_forbidden');

        $release=prodExecLoadJson($root.'/RELEASE.json');
        prodExecEq($release['product']??null,'oneQay','release_product_invalid');
        prodExecEq($release['release_id']??null,$id['release_id'],'release_id_invalid');
        prodExecEq($release['environment']??null,'PRODUCTION','release_environment_invalid');
        prodExecEq($release['required_runtime_class']??null,'production','release_runtime_invalid');
        prodExecBool($release['production']??null,true,'release_production_invalid');
        prodExecBool($release['production_data_allowed']??null,true,'release_production_data_invalid');
        prodExecEq($release['source_commit']??null,$id['source_commit'],'release_source_invalid');
        prodExecBool($release['business_runtime_activation_ready']??null,false,'release_business_runtime_must_be_dark');
        prodExecEq($release['dark_deploy_health_endpoint']??null,'/health/live','release_health_invalid');
        prodExecEq($release['migration_count']??null,27,'release_migration_count_invalid');
        prodExecEq($release['migration_execution_state']??null,'NOT_PERFORMED_BY_ARTIFACT_BUILD','release_migration_state_invalid');
        prodExecBool($release['migration_execution_authorized']??null,false,'release_migration_forbidden');
        prodExecEq($release['production_traffic_activation']??null,'NOT_AUTHORIZED','release_traffic_invalid');
        prodExecEq($release['production_activation']??null,'NOT_AUTHORIZED','release_activation_invalid');

        $migrations=glob($root.'/apps/web/database/migrations/*.php');
        if (! is_array($migrations) || count($migrations)!==27) prodExecFail('migration_count_invalid');
        if (! rename($root,$id['release_directory'])) prodExecFail('release_commit_failed');
        return $release;
    } finally { prodExecRemoveTree($stage); }
}

function prodExecBindEnv(string $runtimeEnvPath, array $id): string
{
    $real=prodExecPrivateFile($runtimeEnvPath,$id['shared_root'],'runtime_env');
    $size=filesize($real);
    if (! is_int($size) || $size<2 || $size>1048576) prodExecFail('runtime_env_size_invalid');
    $hash=hash_file('sha256',$real);
    if (! is_string($hash)) prodExecFail('runtime_env_hash_failed');
    $link=$id['release_directory'].'/apps/web/.env';
    if (file_exists($link) || is_link($link)) prodExecFail('runtime_env_path_occupied');
    if (! prodExecFunctionAvailable('symlink') || ! symlink($real,$link)) prodExecFail('runtime_env_symlink_failed');
    if (! is_link($link) || realpath($link)!==$real) prodExecFail('runtime_env_symlink_verification_failed');
    $after=hash_file('sha256',$link);
    if (! is_string($after) || ! hash_equals($hash,$after)) prodExecFail('runtime_env_readback_failed');
    return $hash;
}

/** @param array<string,mixed> $value */
function prodExecValidateHealthPayload(array $value): void
{
    prodExecEq($value['status'] ?? null, 'ok', 'health_status_invalid');
    prodExecEq($value['service'] ?? null, 'oneqay-web', 'health_service_invalid');
    if (! is_string($value['correlation_id'] ?? null) || $value['correlation_id'] === '') {
        prodExecFail('health_correlation_invalid');
    }
}

/** @return array<string,mixed> */
function prodExecFetchHealth(string $url): array
{
    $body=null;
    if (extension_loaded('curl') && function_exists('curl_init')) {
        $h=curl_init($url); if ($h===false) prodExecFail('health_client_failed');
        curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Accept: application/json'],CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS]);
        $r=curl_exec($h); $status=curl_getinfo($h,CURLINFO_RESPONSE_CODE); curl_close($h);
        if (! is_string($r) || $status!==200) prodExecFail('health_http_failed');
        $body=$r;
    } else {
        if (! filter_var((string)ini_get('allow_url_fopen'),FILTER_VALIDATE_BOOLEAN)) prodExecFail('health_client_unavailable');
        $ctx=stream_context_create(['http'=>['method'=>'GET','header'=>"Accept: application/json\r\n",'timeout'=>20,'ignore_errors'=>false],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false]]);
        $r=@file_get_contents($url,false,$ctx); if (! is_string($r)) prodExecFail('health_https_failed'); $body=$r;
    }
    if (strlen($body)<2 || strlen($body)>32768) prodExecFail('health_size_invalid');
    try { $v=json_decode($body,true,16,JSON_THROW_ON_ERROR); } catch (JsonException) { prodExecFail('health_json_invalid'); }
    if (! is_array($v) || array_is_list($v)) prodExecFail('health_shape_invalid');
    prodExecValidateHealthPayload($v);
    return $v;
}

function prodExecWrite(string $path, array $payload): void
{
    if ($path==='' || is_link($path) || is_dir($path)) prodExecFail('output_invalid');
    $dir=dirname($path); if (! is_dir($dir) || ! is_writable($dir)) prodExecFail('output_directory_invalid');
    $json=json_encode($payload,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
    $tmp=tempnam($dir,'.oneqay-production-evidence-'); if ($tmp===false) prodExecFail('output_temp_failed');
    try {
        if (file_put_contents($tmp,$json,LOCK_EX)!==strlen($json)) prodExecFail('output_write_failed');
        @chmod($tmp,0600);
        if (! rename($tmp,$path)) prodExecFail('output_commit_failed');
        @chmod($path,0600);
    } finally { if (is_file($tmp)) @unlink($tmp); }
}

/**
 * @param callable(string):array<string,mixed> $healthFetcher
 * @return array<string,mixed>
 */
function prodExecExecute(string $planPath,string $profilePath,string $archivePath,string $bindingsPath,string $runtimeEnvPath,string $evidencePath,callable $healthFetcher): array
{
    $active=null; $previous=['state'=>'ABSENT','target'=>null]; $mutated=false;
    try {
        $plan=prodExecLoadJson($planPath);
        $profile=prodExecLoadJson($profilePath);
        $id=prodExecValidatePlan($plan);
        prodExecValidateProfile($profile,$id);

        foreach ([$id['deployment_root'],$id['release_root'],$id['shared_root'],dirname($id['active_pointer'])] as $dir) {
            if (! is_dir($dir) || ! is_writable($dir)) prodExecFail('required_directory_not_writable');
        }

        $privateBindings=prodExecPrivateFile($bindingsPath,$id['shared_root'],'private_bindings');
        $bindings=prodExecValidateBindings(prodExecLoadJson($privateBindings,true),$id);
        $active=$id['active_pointer'];
        $previous=prodExecPrevious($active,$id['release_root'],$id['release_directory']);

        prodExecExtract($archivePath,$id);
        $envHash=prodExecBindEnv($runtimeEnvPath,$id);
        prodExecRequireAuthorityCurrent($id);
        prodExecAtomicPoint($active,$id['release_directory']);
        $mutated=true;

        $resolvedDoc=realpath($id['document_root']);
        $expectedDoc=realpath($id['release_directory'].'/apps/web/public');
        if (! is_string($resolvedDoc) || ! is_string($expectedDoc) || $resolvedDoc!==$expectedDoc) prodExecFail('document_root_readback_failed');

        $first=$healthFetcher($id['health_url']);
        if (! is_array($first) || array_is_list($first)) prodExecFail('health_fetcher_shape_invalid');
        prodExecValidateHealthPayload($first);

        prodExecRestore($active,$previous);
        prodExecRequireAuthorityCurrent($id);
        prodExecAtomicPoint($active,$id['release_directory']);

        $second=$healthFetcher($id['health_url']);
        if (! is_array($second) || array_is_list($second)) prodExecFail('health_fetcher_shape_invalid');
        prodExecValidateHealthPayload($second);

        $after=hash_file('sha256',$id['release_directory'].'/apps/web/.env');
        if (! is_string($after) || ! hash_equals($envHash,$after)) prodExecFail('runtime_env_post_reactivation_invalid');
        if (realpath($active)!==realpath($id['release_directory'])) prodExecFail('candidate_restore_verification_failed');

        $release=prodExecLoadJson($id['release_directory'].'/RELEASE.json');
        prodExecEq($release['source_commit']??null,$id['source_commit'],'runtime_source_readback_invalid');
        prodExecBool($release['business_runtime_activation_ready']??null,false,'runtime_business_activation_must_be_dark');
        prodExecEq($release['production_traffic_activation']??null,'NOT_AUTHORIZED','runtime_traffic_must_be_dark');

        $evidence=[
            'schema_version'=>1,
            'evidence_state'=>'PRODUCTION_DEPLOYMENT_EVIDENCE_CANDIDATE',
            'environment_id'=>$id['environment_id'],
            'runtime_class'=>'production',
            'release_id'=>$id['release_id'],
            'source_commit'=>$id['source_commit'],
            'artifact_sha256'=>$id['artifact_sha256'],
            'deployment_plan_fingerprint'=>$id['plan_fingerprint'],
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
                'migration27_execution'=>'NOT_PERFORMED',
                'permission_provisioning'=>'NONE',
                'feature_activation'=>'INACTIVE',
                'technical_preview_activation'=>'NOT_AUTHORIZED',
                'production_activation'=>'NOT_AUTHORIZED',
                'production_traffic_activation'=>'NOT_AUTHORIZED',
                'updater_activation'=>'INACTIVE',
                'target_selection'=>'NOT_PERFORMED',
                'selected_target'=>null,
                'producer_dispatch'=>'NOT_PERFORMED',
            ],
            'secrets_embedded'=>false,
            'attribution'=>'Lab | zefry',
        ];

        prodExecWrite($evidencePath,$evidence);
        $mutated=false;
        return $evidence;
    } catch (Throwable $failure) {
        if ($mutated && is_string($active)) prodExecRollbackBestEffort($active,$previous);
        if (is_file($evidencePath)) @unlink($evidencePath);
        throw $failure;
    }
}

if (PHP_SAPI==='cli' && realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__) {
    if ($argc!==7) {
        fwrite(STDERR,"Usage: php tools/execute-production-dark-deployment.php <deployment-plan.json> <target-profile.json> <production-artifact.tar.gz> <private-bindings.json> <private-runtime-env> <deployment-evidence-candidate.json>\n");
        exit(64);
    }
    try {
        prodExecExecute($argv[1],$argv[2],$argv[3],$argv[4],$argv[5],$argv[6],static fn(string $url):array=>prodExecFetchHealth($url));
        fwrite(STDOUT,"production_dark_deployment_candidate_verified_not_activated\n");
        exit(0);
    } catch (Throwable $failure) {
        $code=$failure instanceof ProductionDarkDeploymentExecutionException ? $failure->getMessage() : 'unexpected_failure';
        fwrite(STDERR,"production_dark_deployment_execution_failed:".$code."\n");
        exit(1);
    }
}
