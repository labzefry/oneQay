<?php

declare(strict_types=1);

// Author by Lab | zefry

final class ProductionFixedPublicTargetInspectionException extends RuntimeException {}

function pfpInspectFail(string $code): never
{
    throw new ProductionFixedPublicTargetInspectionException($code);
}

/** @return array<string,mixed> */
function pfpInspectLoad(string $path, bool $private = false): array
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) {
        pfpInspectFail('input_unavailable');
    }
    $size = filesize($path);
    if (! is_int($size) || $size < 2 || $size > 262144) {
        pfpInspectFail('input_size_invalid');
    }
    if ($private) {
        $mode = fileperms($path);
        if (! is_int($mode) || (($mode & 0077) !== 0)) {
            pfpInspectFail('private_binding_permissions_invalid');
        }
    }
    $raw = file_get_contents($path);
    if (! is_string($raw)) {
        pfpInspectFail('input_read_failed');
    }
    try {
        $value = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        pfpInspectFail('input_json_invalid');
    }
    if (! is_array($value) || array_is_list($value)) {
        pfpInspectFail('input_shape_invalid');
    }
    return $value;
}

function pfpInspectEq(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) pfpInspectFail($code);
}

function pfpInspectBool(mixed $actual, bool $expected, string $code): void
{
    if (! is_bool($actual) || $actual !== $expected) pfpInspectFail($code);
}

function pfpInspectPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) pfpInspectFail($code);
    return $value;
}

function pfpInspectPath(mixed $value, string $code): string
{
    if (! is_string($value) || $value === '' || $value === '/' || strlen($value) > 4096
        || ! str_starts_with($value, '/') || str_contains($value, "\0") || str_contains($value, '\\')
        || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $value) === 1 || preg_match('#//+#', $value) === 1) {
        pfpInspectFail($code);
    }
    return rtrim($value, '/');
}

function pfpInspectNested(string $child, string $parent, string $code): void
{
    if (! str_starts_with($child.'/', $parent.'/')) pfpInspectFail($code);
}

/** @return list<string> */
function pfpInspectDisabledFunctions(): array
{
    $raw = (string) ini_get('disable_functions');
    if ($raw === '') return [];
    return array_values(array_filter(array_map('trim', explode(',', $raw)), static fn (string $v): bool => $v !== ''));
}

function pfpInspectFunctionAvailable(string $name): bool
{
    return function_exists($name) && ! in_array($name, pfpInspectDisabledFunctions(), true);
}

/** @return array{atomic_rename_supported:bool,symlink_supported:bool,hardlink_supported:bool} */
function pfpInspectPrivateFilesystem(string $deploymentRoot): array
{
    if (! is_dir($deploymentRoot) || ! is_writable($deploymentRoot)) pfpInspectFail('deployment_root_not_writable');

    $root = $deploymentRoot.'/.oneqay-production-fixed-public-probe-'.bin2hex(random_bytes(8));
    $source = $root.'/source';
    $target = $root.'/target';
    $dir = $root.'/dir';
    $symlink = $root.'/symlink';
    $hardlink = $root.'/hardlink';
    $atomic = false;
    $symlinkOk = false;
    $hardlinkOk = false;

    try {
        if (! mkdir($root, 0700) || ! mkdir($dir, 0700)) pfpInspectFail('probe_create_failed');
        $payload = 'oneqay-production-fixed-public-probe';
        if (file_put_contents($source, $payload, LOCK_EX) !== strlen($payload)) pfpInspectFail('probe_write_failed');
        $atomic = rename($source, $target) && is_file($target) && file_get_contents($target) === $payload;
        $symlinkOk = pfpInspectFunctionAvailable('symlink')
            && @symlink($dir, $symlink) && is_link($symlink) && realpath($symlink) === realpath($dir);
        $hardlinkOk = pfpInspectFunctionAvailable('link')
            && is_file($target) && @link($target, $hardlink) && is_file($hardlink) && ! is_link($hardlink)
            && fileinode($target) === fileinode($hardlink)
            && hash_equals((string) hash_file('sha256', $target), (string) hash_file('sha256', $hardlink));
    } finally {
        if (is_link($symlink)) @unlink($symlink);
        if (is_file($hardlink) && ! is_link($hardlink)) @unlink($hardlink);
        if (is_file($source)) @unlink($source);
        if (is_file($target)) @unlink($target);
        if (is_dir($dir)) @rmdir($dir);
        if (is_dir($root)) @rmdir($root);
    }

    return [
        'atomic_rename_supported' => $atomic,
        'symlink_supported' => $symlinkOk,
        'hardlink_supported' => $hardlinkOk,
    ];
}

/** @return array{atomic_public_swap_supported:bool,rewrite_to_index_verified:bool} */
function pfpInspectPublicFilesystem(string $documentRoot): array
{
    if (! is_dir($documentRoot) || ! is_writable($documentRoot)) pfpInspectFail('public_document_root_not_writable');
    $htaccess = $documentRoot.'/.htaccess';
    if (! is_file($htaccess) || is_link($htaccess) || ! is_readable($htaccess)) pfpInspectFail('public_htaccess_unavailable');
    $raw = file_get_contents($htaccess);
    if (! is_string($raw) || stripos($raw, 'RewriteEngine On') === false
        || preg_match('/RewriteRule\s+\^\s+index\.php\b/i', $raw) !== 1) {
        pfpInspectFail('public_rewrite_to_index_unverified');
    }

    $root = $documentRoot.'/.oneqay-production-public-probe-'.bin2hex(random_bytes(8));
    $source = $root.'/source';
    $target = $root.'/target';
    $dirSource = $root.'/dir-source';
    $dirTarget = $root.'/dir-target';
    $atomic = false;
    try {
        if (! mkdir($root, 0700) || ! mkdir($dirSource, 0700)) pfpInspectFail('public_probe_create_failed');
        if (file_put_contents($source, 'oneqay-public-probe', LOCK_EX) !== 19) pfpInspectFail('public_probe_write_failed');
        $atomic = rename($source, $target) && rename($dirSource, $dirTarget)
            && is_file($target) && is_dir($dirTarget);
    } finally {
        if (is_file($source)) @unlink($source);
        if (is_file($target)) @unlink($target);
        if (is_dir($dirSource)) @rmdir($dirSource);
        if (is_dir($dirTarget)) @rmdir($dirTarget);
        if (is_dir($root)) @rmdir($root);
    }
    if (! $atomic) pfpInspectFail('public_atomic_rename_unavailable');
    return ['atomic_public_swap_supported' => true, 'rewrite_to_index_verified' => true];
}

/** @return array{url:string,path:string,host:string} */
function pfpInspectHealth(array $health): array
{
    pfpInspectEq($health['path'] ?? null, '/health/live', 'health_path_invalid');
    $url = $health['url'] ?? null;
    if (! is_string($url) || strlen($url) > 2048) pfpInspectFail('health_url_invalid');
    $parts = parse_url($url);
    if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https'
        || ! is_string($parts['host'] ?? null) || $parts['host'] === ''
        || ($parts['path'] ?? '') !== '/health/live'
        || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
        pfpInspectFail('health_url_invalid');
    }
    return ['url' => $url, 'path' => '/health/live', 'host' => $parts['host']];
}

/** @return array<string,mixed> */
function pfpInspectTarget(array $input, array $bindings, string $bindingPath): array
{
    pfpInspectEq($input['schema_version'] ?? null, 1, 'schema_invalid');
    pfpInspectEq($input['target_class'] ?? null, 'CPANEL_NO_SSH', 'target_class_invalid');
    pfpInspectEq($input['execution_channel'] ?? null, 'CPANEL_CRON_PHP_CLI_NO_SSH', 'execution_channel_invalid');
    pfpInspectEq($input['runtime_class'] ?? null, 'production', 'runtime_class_invalid');
    pfpInspectBool($input['production'] ?? null, true, 'production_required');
    pfpInspectBool($input['production_data_allowed'] ?? null, true, 'production_data_required');
    pfpInspectBool($input['isolated_environment'] ?? null, true, 'isolated_environment_required');
    pfpInspectEq($input['attribution'] ?? null, 'Lab | zefry', 'attribution_invalid');

    $environmentId = pfpInspectPattern($input['environment_id'] ?? null, '/\A[a-z0-9][a-z0-9-]{2,62}\z/', 'environment_invalid');
    $releaseId = pfpInspectPattern($input['release_binding']['release_id'] ?? null, '/\Aproduction-[0-9a-f]{12}\z/', 'release_invalid');
    $source = pfpInspectPattern($input['release_binding']['source_commit'] ?? null, '/\A[0-9a-f]{40}\z/', 'source_invalid');
    $artifact = pfpInspectPattern($input['release_binding']['artifact_sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'artifact_invalid');
    pfpInspectEq($releaseId, 'production-'.substr($source, 0, 12), 'release_source_mismatch');

    $deploymentRoot = pfpInspectPath($input['filesystem']['deployment_root'] ?? null, 'deployment_root_invalid');
    $releaseRoot = pfpInspectPath($input['filesystem']['release_root'] ?? null, 'release_root_invalid');
    $sharedRoot = pfpInspectPath($input['filesystem']['shared_runtime_root'] ?? null, 'shared_root_invalid');
    $activePointer = pfpInspectPath($input['filesystem']['active_release_pointer'] ?? null, 'active_pointer_invalid');
    $documentRoot = pfpInspectPath($input['filesystem']['document_root'] ?? null, 'document_root_invalid');
    pfpInspectEq($input['filesystem']['document_root_mode'] ?? null, 'FIXED_PUBLIC_BRIDGE', 'document_root_mode_invalid');

    foreach ([$releaseRoot, $sharedRoot, $activePointer] as $path) pfpInspectNested($path, $deploymentRoot, 'private_filesystem_escape');
    if (count(array_unique([$deploymentRoot, $releaseRoot, $sharedRoot, $activePointer], SORT_STRING)) !== 4) pfpInspectFail('private_filesystem_collision');
    if (str_starts_with($documentRoot.'/', $deploymentRoot.'/') || str_starts_with($deploymentRoot.'/', $documentRoot.'/')) {
        pfpInspectFail('public_private_paths_not_disjoint');
    }
    foreach ([$deploymentRoot, $releaseRoot, $sharedRoot] as $path) {
        if (! is_dir($path) || ! is_writable($path)) pfpInspectFail('required_private_directory_not_writable');
    }
    if (! is_dir(dirname($activePointer)) || ! is_writable(dirname($activePointer))) pfpInspectFail('active_pointer_parent_not_writable');

    $privateProbe = pfpInspectPrivateFilesystem($deploymentRoot);
    pfpInspectBool($privateProbe['atomic_rename_supported'], true, 'private_atomic_rename_required');
    if ($privateProbe['symlink_supported'] !== true && $privateProbe['hardlink_supported'] !== true) {
        pfpInspectFail('runtime_binding_strategy_unavailable');
    }
    $publicProbe = pfpInspectPublicFilesystem($documentRoot);

    $bindingReal = realpath($bindingPath);
    if (! is_string($bindingReal) || str_starts_with($bindingReal.'/', $documentRoot.'/')) pfpInspectFail('binding_file_location_invalid');

    if (PHP_VERSION_ID < 80200) pfpInspectFail('php_version_unsupported');
    foreach (['json', 'openssl', 'pdo', 'pdo_mysql', 'phar'] as $extension) {
        if (! extension_loaded($extension)) pfpInspectFail('required_php_extension_missing_'.$extension);
    }
    if (! extension_loaded('curl') && ! filter_var((string) ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
        pfpInspectFail('https_client_unavailable');
    }

    $requiredBindings = [
        'ONEQAY_PRODUCTION_ATTESTATION_TOKEN',
        'ONEQAY_PRODUCTION_ENVIRONMENT_ID',
        'ONEQAY_RUNNING_ARTIFACT_SHA256',
        'ONEQAY_RUNNING_SOURCE_COMMIT',
        'ONEQAY_RUNTIME_CLASS',
    ];
    $actualBindings = array_keys($bindings);
    sort($actualBindings, SORT_STRING);
    if ($actualBindings !== $requiredBindings) pfpInspectFail('binding_set_invalid');
    foreach ($requiredBindings as $name) {
        if (! is_string($bindings[$name] ?? null) || $bindings[$name] === '') pfpInspectFail('binding_missing_'.$name);
    }
    pfpInspectEq($bindings['ONEQAY_RUNTIME_CLASS'], 'production', 'binding_runtime_mismatch');
    pfpInspectEq($bindings['ONEQAY_RUNNING_SOURCE_COMMIT'], $source, 'binding_source_mismatch');
    pfpInspectEq($bindings['ONEQAY_RUNNING_ARTIFACT_SHA256'], $artifact, 'binding_artifact_mismatch');
    pfpInspectEq($bindings['ONEQAY_PRODUCTION_ENVIRONMENT_ID'], $environmentId, 'binding_environment_mismatch');
    $token = $bindings['ONEQAY_PRODUCTION_ATTESTATION_TOKEN'];
    if (strlen($token) < 32 || strlen($token) > 256 || str_contains($token, "\0")) pfpInspectFail('attestation_token_invalid');

    $capabilityNames = [
        'durable_database_persistence','durable_session','authorization','transaction_durability','pos_durability',
        'authenticated_configuration_channel','read_before_write','read_after_write','non_mutating_health_attestation','verified_rollback',
    ];
    $assertions = is_array($input['operator_assertions'] ?? null) ? $input['operator_assertions'] : [];
    foreach ($capabilityNames as $name) pfpInspectBool($assertions[$name] ?? null, true, 'operator_assertion_'.$name.'_required');

    $health = pfpInspectHealth(is_array($input['health'] ?? null) ? $input['health'] : []);
    $activationStrategy = $privateProbe['symlink_supported'] === true
        ? 'ACTIVE_POINTER_PLUS_FIXED_PUBLIC_BRIDGE'
        : 'DIRECT_RELEASE_FIXED_PUBLIC_BRIDGE_WITH_HARDLINK_RUNTIME_BINDING';

    return [
        'schema_version' => 1,
        'profile_state' => 'PRODUCTION_FIXED_PUBLIC_CPANEL_NO_SSH_TARGET_PROFILE_OBSERVED',
        'target_class' => 'CPANEL_NO_SSH',
        'execution_channel' => 'CPANEL_CRON_PHP_CLI_NO_SSH',
        'environment_id' => $environmentId,
        'runtime_class' => 'production',
        'production' => true,
        'production_data_allowed' => true,
        'isolated_environment' => true,
        'release_binding' => ['release_id'=>$releaseId,'source_commit'=>$source,'artifact_sha256'=>$artifact],
        'filesystem' => [
            'deployment_root'=>$deploymentRoot,
            'release_root'=>$releaseRoot,
            'shared_runtime_root'=>$sharedRoot,
            'active_release_pointer'=>$activePointer,
            'document_root'=>$documentRoot,
            'document_root_mode'=>'FIXED_PUBLIC_BRIDGE',
            'deployment_root_writable'=>true,
            'release_root_writable'=>true,
            'shared_runtime_root_writable'=>true,
            'active_pointer_parent_writable'=>true,
            'atomic_rename_supported'=>true,
            'symlink_supported'=>$privateProbe['symlink_supported'],
            'hardlink_supported'=>$privateProbe['hardlink_supported'],
            'atomic_public_swap_supported'=>$publicProbe['atomic_public_swap_supported'],
            'rewrite_to_index_verified'=>$publicProbe['rewrite_to_index_verified'],
            'public_private_paths_disjoint'=>true,
            'activation_strategy'=>$activationStrategy,
        ],
        'health' => ['url'=>$health['url'],'path'=>'/health/live','https_required'=>true],
        'runtime' => [
            'php_version'=>PHP_VERSION,
            'php_version_supported'=>true,
            'required_extensions_present'=>true,
            'https_client_available'=>true,
            'php_symlink_available'=>$privateProbe['symlink_supported'],
            'php_hardlink_available'=>$privateProbe['hardlink_supported'],
        ],
        'configuration' => [
            'binding_file_private'=>true,
            'required_binding_identity_matches'=>true,
            'required_binding_presence'=>array_fill_keys($requiredBindings, true),
            'secret_values_embedded'=>false,
        ],
        'operator_assertions'=>$assertions,
        'secrets_embedded'=>false,
        'attribution'=>'Lab | zefry',
    ];
}

function pfpInspectWrite(string $path, array $payload): void
{
    $dir = dirname($path);
    if ($path === '' || is_link($path) || is_dir($path) || ! is_dir($dir) || ! is_writable($dir)) pfpInspectFail('output_invalid');
    $json = json_encode($payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR).PHP_EOL;
    $tmp = tempnam($dir, '.oneqay-production-fixed-public-profile-');
    if ($tmp === false) pfpInspectFail('temp_failed');
    try {
        if (file_put_contents($tmp, $json, LOCK_EX) !== strlen($json)) pfpInspectFail('write_failed');
        @chmod($tmp, 0600);
        if (! rename($tmp, $path)) pfpInspectFail('commit_failed');
        @chmod($path, 0600);
    } finally {
        if (is_file($tmp)) @unlink($tmp);
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 4) {
        fwrite(STDERR, "Usage: php tools/production/cpanel/inspect-production-fixed-public-cpanel-no-ssh-target.php <target-input.json> <private-bindings.json> <target-profile.json>\n");
        exit(64);
    }
    try {
        pfpInspectWrite($argv[3], pfpInspectTarget(pfpInspectLoad($argv[1]), pfpInspectLoad($argv[2], true), $argv[2]));
        fwrite(STDOUT, "production_fixed_public_target_profile_observed\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "production_fixed_public_target_inspection_failed:".$failure->getMessage()."\n");
        exit(1);
    }
}
