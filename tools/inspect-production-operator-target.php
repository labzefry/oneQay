<?php

declare(strict_types=1);

// Author by Lab | zefry

final class ProductionOperatorTargetInspectionException extends RuntimeException {}

function prodTargetFail(string $code): never
{
    throw new ProductionOperatorTargetInspectionException($code);
}

/** @return array<string,mixed> */
function prodTargetLoadJson(string $path, bool $private = false): array
{
    if ($path === '' || str_contains($path, "\0") || ! is_file($path) || is_link($path) || ! is_readable($path)) {
        prodTargetFail('input_unavailable');
    }
    $size = filesize($path);
    if (! is_int($size) || $size < 2 || $size > 262144) {
        prodTargetFail('input_size_invalid');
    }
    if ($private) {
        $mode = fileperms($path);
        if (! is_int($mode) || (($mode & 0077) !== 0)) {
            prodTargetFail('private_binding_permissions_invalid');
        }
    }
    $raw = file_get_contents($path);
    if (! is_string($raw)) {
        prodTargetFail('input_read_failed');
    }
    try {
        $value = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        prodTargetFail('input_json_invalid');
    }
    if (! is_array($value) || array_is_list($value)) {
        prodTargetFail('input_shape_invalid');
    }
    return $value;
}

function prodTargetEq(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) {
        prodTargetFail($code);
    }
}

function prodTargetBool(mixed $actual, bool $expected, string $code): void
{
    if (! is_bool($actual) || $actual !== $expected) {
        prodTargetFail($code);
    }
}

function prodTargetPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) {
        prodTargetFail($code);
    }
    return $value;
}

function prodTargetSafePath(mixed $value, string $code): string
{
    if (! is_string($value)
        || $value === ''
        || strlen($value) > 4096
        || $value === '/'
        || ! str_starts_with($value, '/')
        || str_contains($value, "\0")
        || str_contains($value, '\\')
        || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $value) === 1
        || preg_match('#//+#', $value) === 1
    ) {
        prodTargetFail($code);
    }
    return rtrim($value, '/');
}

function prodTargetNested(string $child, string $parent, string $code): void
{
    if (! str_starts_with($child.'/', $parent.'/')) {
        prodTargetFail($code);
    }
}

/** @return list<string> */
function prodTargetDisabledFunctions(): array
{
    $raw = (string) ini_get('disable_functions');
    if ($raw === '') {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode(',', $raw)), static fn (string $v): bool => $v !== ''));
}

function prodTargetFunctionAvailable(string $name): bool
{
    return function_exists($name) && ! in_array($name, prodTargetDisabledFunctions(), true);
}

/** @return array{atomic_rename_supported:bool,symlink_supported:bool} */
function prodTargetProbeFilesystem(string $deploymentRoot): array
{
    if (! is_dir($deploymentRoot) || ! is_writable($deploymentRoot)) {
        prodTargetFail('deployment_root_not_writable');
    }

    $root = $deploymentRoot.'/.oneqay-production-probe-'.bin2hex(random_bytes(8));
    $from = $root.'/from';
    $to = $root.'/to';
    $linkTarget = $root.'/target';
    $link = $root.'/link';
    $renamed = false;
    $symlinked = false;

    try {
        if (! mkdir($root, 0700) || ! mkdir($linkTarget, 0700)) {
            prodTargetFail('probe_directory_create_failed');
        }
        if (file_put_contents($from, 'oneqay-production-probe', LOCK_EX) !== 23) {
            prodTargetFail('probe_write_failed');
        }
        $renamed = rename($from, $to) && is_file($to) && file_get_contents($to) === 'oneqay-production-probe';
        $symlinked = prodTargetFunctionAvailable('symlink')
            && @symlink($linkTarget, $link)
            && is_link($link)
            && realpath($link) === realpath($linkTarget);
    } finally {
        if (is_link($link)) @unlink($link);
        if (is_file($from)) @unlink($from);
        if (is_file($to)) @unlink($to);
        if (is_dir($linkTarget)) @rmdir($linkTarget);
        if (is_dir($root)) @rmdir($root);
    }

    return ['atomic_rename_supported' => $renamed, 'symlink_supported' => $symlinked];
}

/** @return array{url:string,path:string,host:string} */
function prodTargetValidateHealth(array $health): array
{
    prodTargetEq($health['path'] ?? null, '/health/live', 'health_path_invalid');
    $url = $health['url'] ?? null;
    if (! is_string($url) || strlen($url) > 2048) {
        prodTargetFail('health_url_invalid');
    }
    $parts = parse_url($url);
    if (! is_array($parts)
        || ($parts['scheme'] ?? null) !== 'https'
        || ! is_string($parts['host'] ?? null)
        || $parts['host'] === ''
        || ($parts['path'] ?? '') !== '/health/live'
        || array_key_exists('user', $parts)
        || array_key_exists('pass', $parts)
        || array_key_exists('query', $parts)
        || array_key_exists('fragment', $parts)
    ) {
        prodTargetFail('health_url_invalid');
    }
    return ['url' => $url, 'path' => '/health/live', 'host' => $parts['host']];
}

/** @return array<string,mixed> */
function prodTargetInspect(array $input, array $bindings, string $bindingPath): array
{
    prodTargetEq($input['schema_version'] ?? null, 1, 'schema_version_invalid');
    $targetClass = prodTargetPattern($input['target_class'] ?? null, '/\A(?:OPERATOR_MANAGED_FILESYSTEM|CPANEL_NO_SSH)\z/', 'target_class_invalid');
    $channel = prodTargetPattern($input['execution_channel'] ?? null, '/\A(?:PHP_CLI|CPANEL_CRON_PHP_CLI_NO_SSH)\z/', 'execution_channel_invalid');
    if ($targetClass === 'CPANEL_NO_SSH' && $channel !== 'CPANEL_CRON_PHP_CLI_NO_SSH') {
        prodTargetFail('cpanel_execution_channel_invalid');
    }
    prodTargetEq($input['runtime_class'] ?? null, 'production', 'runtime_class_invalid');
    prodTargetBool($input['production'] ?? null, true, 'production_required');
    prodTargetBool($input['production_data_allowed'] ?? null, true, 'production_data_required');
    prodTargetBool($input['isolated_environment'] ?? null, true, 'environment_isolation_required');
    prodTargetEq($input['attribution'] ?? null, 'Lab | zefry', 'attribution_invalid');

    $environmentId = prodTargetPattern($input['environment_id'] ?? null, '/\A[a-z0-9][a-z0-9-]{2,62}\z/', 'environment_id_invalid');
    $releaseId = prodTargetPattern($input['release_binding']['release_id'] ?? null, '/\Aproduction-[0-9a-f]{12}\z/', 'release_id_invalid');
    $source = prodTargetPattern($input['release_binding']['source_commit'] ?? null, '/\A[0-9a-f]{40}\z/', 'source_commit_invalid');
    $artifact = prodTargetPattern($input['release_binding']['artifact_sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'artifact_sha256_invalid');
    prodTargetEq($releaseId, 'production-'.substr($source, 0, 12), 'release_source_mismatch');

    $deploymentRoot = prodTargetSafePath($input['filesystem']['deployment_root'] ?? null, 'deployment_root_invalid');
    $releaseRoot = prodTargetSafePath($input['filesystem']['release_root'] ?? null, 'release_root_invalid');
    $sharedRoot = prodTargetSafePath($input['filesystem']['shared_runtime_root'] ?? null, 'shared_root_invalid');
    $activePointer = prodTargetSafePath($input['filesystem']['active_release_pointer'] ?? null, 'active_pointer_invalid');
    $documentRoot = prodTargetSafePath($input['filesystem']['document_root'] ?? null, 'document_root_invalid');

    foreach ([$releaseRoot, $sharedRoot, $activePointer] as $path) {
        prodTargetNested($path, $deploymentRoot, 'filesystem_escape');
    }
    if (count(array_unique([$deploymentRoot, $releaseRoot, $sharedRoot, $activePointer], SORT_STRING)) !== 4) {
        prodTargetFail('filesystem_collision');
    }
    prodTargetEq($documentRoot, $activePointer.'/apps/web/public', 'document_root_shape_invalid');

    foreach ([$deploymentRoot, $releaseRoot, $sharedRoot] as $path) {
        if (! is_dir($path) || ! is_writable($path)) {
            prodTargetFail('required_directory_not_writable');
        }
    }
    if (! is_dir(dirname($activePointer)) || ! is_writable(dirname($activePointer))) {
        prodTargetFail('active_pointer_parent_not_writable');
    }

    $bindingReal = realpath($bindingPath);
    if (! is_string($bindingReal) || str_starts_with($bindingReal.'/', $documentRoot.'/')) {
        prodTargetFail('binding_file_location_invalid');
    }

    if (PHP_VERSION_ID < 80200) {
        prodTargetFail('php_version_unsupported');
    }
    foreach (['json', 'openssl', 'pdo', 'pdo_mysql', 'phar'] as $extension) {
        if (! extension_loaded($extension)) {
            prodTargetFail('required_php_extension_missing_'.$extension);
        }
    }
    if (! extension_loaded('curl') && ! filter_var((string) ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
        prodTargetFail('https_client_unavailable');
    }

    $probe = prodTargetProbeFilesystem($deploymentRoot);
    prodTargetBool($probe['atomic_rename_supported'], true, 'atomic_rename_unavailable');
    prodTargetBool($probe['symlink_supported'], true, 'symlink_unavailable');
    $health = prodTargetValidateHealth(is_array($input['health'] ?? null) ? $input['health'] : []);

    $requiredBindings = [
        'ONEQAY_PRODUCTION_ATTESTATION_TOKEN',
        'ONEQAY_PRODUCTION_ENVIRONMENT_ID',
        'ONEQAY_RUNNING_ARTIFACT_SHA256',
        'ONEQAY_RUNNING_SOURCE_COMMIT',
        'ONEQAY_RUNTIME_CLASS',
    ];
    $actualBindings = array_keys($bindings);
    sort($actualBindings, SORT_STRING);
    if ($actualBindings !== $requiredBindings) {
        prodTargetFail('binding_set_invalid');
    }
    foreach ($requiredBindings as $name) {
        if (! is_string($bindings[$name] ?? null) || $bindings[$name] === '') {
            prodTargetFail('binding_value_missing_'.$name);
        }
    }
    prodTargetEq($bindings['ONEQAY_RUNTIME_CLASS'], 'production', 'binding_runtime_mismatch');
    prodTargetEq($bindings['ONEQAY_RUNNING_SOURCE_COMMIT'], $source, 'binding_source_mismatch');
    prodTargetEq($bindings['ONEQAY_RUNNING_ARTIFACT_SHA256'], $artifact, 'binding_artifact_mismatch');
    prodTargetEq($bindings['ONEQAY_PRODUCTION_ENVIRONMENT_ID'], $environmentId, 'binding_environment_mismatch');
    $token = $bindings['ONEQAY_PRODUCTION_ATTESTATION_TOKEN'];
    if (strlen($token) < 32 || strlen($token) > 256 || str_contains($token, "\0")) {
        prodTargetFail('attestation_token_invalid');
    }

    $capabilities = [
        'authorization','authenticated_configuration_channel','durable_database_persistence','durable_session',
        'non_mutating_health_attestation','pos_durability','read_after_write','read_before_write',
        'transaction_durability','verified_rollback',
    ];
    $assertions = $input['operator_assertions'] ?? null;
    if (! is_array($assertions) || array_is_list($assertions)) {
        prodTargetFail('operator_assertions_invalid');
    }
    $actualAssertions = array_keys($assertions);
    sort($actualAssertions, SORT_STRING);
    if ($actualAssertions !== $capabilities) {
        prodTargetFail('operator_assertion_set_invalid');
    }
    foreach ($capabilities as $capability) {
        prodTargetBool($assertions[$capability] ?? null, true, 'operator_capability_missing_'.$capability);
    }

    $presence = [];
    foreach ($requiredBindings as $name) {
        $presence[$name] = true;
    }

    return [
        'schema_version' => 1,
        'profile_state' => 'PRODUCTION_OPERATOR_TARGET_PROFILE_OBSERVED',
        'target_class' => $targetClass,
        'execution_channel' => $channel,
        'environment_id' => $environmentId,
        'runtime_class' => 'production',
        'production' => true,
        'production_data_allowed' => true,
        'isolated_environment' => true,
        'release_binding' => ['release_id' => $releaseId, 'source_commit' => $source, 'artifact_sha256' => $artifact],
        'filesystem' => [
            'deployment_root' => $deploymentRoot,
            'release_root' => $releaseRoot,
            'shared_runtime_root' => $sharedRoot,
            'active_release_pointer' => $activePointer,
            'document_root' => $documentRoot,
            'deployment_root_writable' => true,
            'release_root_writable' => true,
            'shared_runtime_root_writable' => true,
            'active_pointer_parent_writable' => true,
            'atomic_rename_supported' => true,
            'symlink_supported' => true,
            'document_root_shape_valid' => true,
        ],
        'health' => ['url' => $health['url'], 'path' => $health['path'], 'https_required' => true],
        'runtime' => [
            'php_version' => PHP_VERSION,
            'php_version_supported' => true,
            'required_extensions_present' => true,
            'https_client_available' => true,
        ],
        'configuration' => [
            'binding_file_private' => true,
            'required_binding_presence' => $presence,
            'required_binding_identity_matches' => true,
            'secret_values_embedded' => false,
        ],
        'operator_assertions' => $assertions,
        'secrets_embedded' => false,
        'attribution' => 'Lab | zefry',
    ];
}

/** @param array<string,mixed> $payload */
function prodTargetWrite(string $path, array $payload): void
{
    if ($path === '' || is_link($path) || is_dir($path)) {
        prodTargetFail('output_path_invalid');
    }
    $directory = dirname($path);
    if (! is_dir($directory) || ! is_writable($directory)) {
        prodTargetFail('output_directory_invalid');
    }
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    $temp = tempnam($directory, '.oneqay-production-target-');
    if ($temp === false) {
        prodTargetFail('output_temp_create_failed');
    }
    try {
        if (file_put_contents($temp, $json, LOCK_EX) !== strlen($json)) {
            prodTargetFail('output_write_failed');
        }
        @chmod($temp, 0600);
        if (! rename($temp, $path)) {
            prodTargetFail('output_commit_failed');
        }
        @chmod($path, 0600);
    } finally {
        if (is_file($temp)) @unlink($temp);
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 4) {
        fwrite(STDERR, "Usage: php tools/inspect-production-operator-target.php <target-input.json> <private-bindings.json> <target-profile.json>\n");
        exit(64);
    }
    try {
        $profile = prodTargetInspect(prodTargetLoadJson($argv[1]), prodTargetLoadJson($argv[2], true), $argv[2]);
        prodTargetWrite($argv[3], $profile);
        fwrite(STDOUT, "production_operator_target_profile_observed\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "production_operator_target_inspection_failed:".$failure->getMessage()."\n");
        exit(1);
    }
}
