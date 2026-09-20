<?php

declare(strict_types=1);

// Author by Lab | zefry

final class CpanelNoSshDurableTargetInspectionException extends RuntimeException
{
}

function cpanelProbeFail(string $code): never
{
    throw new CpanelNoSshDurableTargetInspectionException($code);
}

/** @return array<string,mixed> */
function cpanelProbeLoadObject(string $path, bool $private = false): array
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) {
        cpanelProbeFail('input_unavailable');
    }

    $size = filesize($path);
    if (! is_int($size) || $size < 2 || $size > 262144) {
        cpanelProbeFail('input_size_invalid');
    }

    if ($private) {
        $mode = fileperms($path);
        if (! is_int($mode) || (($mode & 0077) !== 0)) {
            cpanelProbeFail('binding_file_permissions_not_private');
        }
    }

    $raw = file_get_contents($path);
    if (! is_string($raw)) {
        cpanelProbeFail('input_read_failed');
    }

    try {
        $decoded = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        cpanelProbeFail('input_json_invalid');
    }

    if (! is_array($decoded) || array_is_list($decoded)) {
        cpanelProbeFail('input_json_shape_invalid');
    }

    return $decoded;
}

function cpanelProbeLiteral(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) {
        cpanelProbeFail($code);
    }
}

function cpanelProbeBool(mixed $actual, bool $expected, string $code): void
{
    if (! is_bool($actual) || $actual !== $expected) {
        cpanelProbeFail($code);
    }
}

function cpanelProbePattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) {
        cpanelProbeFail($code);
    }

    return $value;
}

function cpanelProbeSafeAbsolutePath(mixed $value, string $code): string
{
    if (! is_string($value)
        || $value === ''
        || strlen($value) > 4096
        || ! str_starts_with($value, '/')
        || $value === '/'
        || str_contains($value, "\0")
        || str_contains($value, '\\')
        || preg_match('#(?:^|/)\\.{1,2}(?:/|$)#', $value) === 1
        || preg_match('#//+#', $value) === 1
    ) {
        cpanelProbeFail($code);
    }

    return rtrim($value, '/');
}

function cpanelProbeNested(string $child, string $parent, string $code): void
{
    if (! str_starts_with($child.'/', $parent.'/')) {
        cpanelProbeFail($code);
    }
}

/** @return list<string> */
function cpanelProbeDisabledFunctions(): array
{
    $raw = (string) ini_get('disable_functions');
    if ($raw === '') {
        return [];
    }

    $functions = array_map('trim', explode(',', $raw));

    return array_values(array_filter($functions, static fn (string $value): bool => $value !== ''));
}

function cpanelProbeFunctionAvailable(string $name): bool
{
    return function_exists($name) && ! in_array($name, cpanelProbeDisabledFunctions(), true);
}

/** @return array{atomic_rename_supported:bool,symlink_supported:bool} */
/** @return array{atomic_public_swap_supported:bool,rewrite_to_index_verified:bool} */
function cpanelProbeFixedPublicBridge(string $documentRoot): array
{
    if (! is_dir($documentRoot) || ! is_writable($documentRoot)) {
        cpanelProbeFail('fixed_public_document_root_not_writable');
    }

    $htaccess = $documentRoot.'/.htaccess';
    if (! is_file($htaccess) || is_link($htaccess) || ! is_readable($htaccess)) {
        cpanelProbeFail('fixed_public_htaccess_unavailable');
    }
    $raw = file_get_contents($htaccess);
    if (! is_string($raw)
        || stripos($raw, 'RewriteEngine On') === false
        || preg_match('/RewriteRule\\s+\\^\\s+index\\.php\\b/i', $raw) !== 1
    ) {
        cpanelProbeFail('fixed_public_rewrite_to_index_unverified');
    }

    $suffix = bin2hex(random_bytes(8));
    $root = $documentRoot.'/.oneqay-public-probe-'.$suffix;
    $sourceFile = $root.'/source';
    $targetFile = $root.'/target';
    $sourceDir = $root.'/dir-source';
    $targetDir = $root.'/dir-target';
    $atomic = false;

    try {
        if (! mkdir($root, 0700) || ! mkdir($sourceDir, 0700)) {
            cpanelProbeFail('fixed_public_probe_create_failed');
        }
        if (file_put_contents($sourceFile, 'oneqay-public-bridge-probe', LOCK_EX) !== 26) {
            cpanelProbeFail('fixed_public_probe_write_failed');
        }
        if (! rename($sourceFile, $targetFile) || ! rename($sourceDir, $targetDir)) {
            cpanelProbeFail('fixed_public_atomic_rename_failed');
        }
        $atomic = is_file($targetFile) && is_dir($targetDir);
    } finally {
        if (is_file($sourceFile)) @unlink($sourceFile);
        if (is_file($targetFile)) @unlink($targetFile);
        if (is_dir($sourceDir)) @rmdir($sourceDir);
        if (is_dir($targetDir)) @rmdir($targetDir);
        if (is_dir($root)) @rmdir($root);
    }

    if (! $atomic) {
        cpanelProbeFail('fixed_public_atomic_rename_unavailable');
    }

    return [
        'atomic_public_swap_supported' => true,
        'rewrite_to_index_verified' => true,
    ];
}

function cpanelProbeFilesystemOperations(string $deploymentRoot): array
{
    if (! is_dir($deploymentRoot) || ! is_writable($deploymentRoot)) {
        cpanelProbeFail('deployment_root_not_writable');
    }

    $suffix = bin2hex(random_bytes(8));
    $probeRoot = $deploymentRoot.'/.oneqay-cpanel-probe-'.$suffix;
    $sourceDir = $probeRoot.'/source';
    $targetDir = $probeRoot.'/target';
    $sourceFile = $probeRoot.'/rename-source';
    $targetFile = $probeRoot.'/rename-target';
    $linkPath = $probeRoot.'/link';

    $atomicRename = false;
    $symlink = false;

    try {
        if (! mkdir($probeRoot, 0700) || ! mkdir($sourceDir, 0700) || ! mkdir($targetDir, 0700)) {
            cpanelProbeFail('probe_directory_create_failed');
        }

        $payload = 'oneqay-cpanel-no-ssh-probe';
        if (file_put_contents($sourceFile, $payload, LOCK_EX) !== strlen($payload)) {
            cpanelProbeFail('probe_file_write_failed');
        }

        if (rename($sourceFile, $targetFile)
            && is_file($targetFile)
            && file_get_contents($targetFile) === $payload
        ) {
            $atomicRename = true;
        }

        if (cpanelProbeFunctionAvailable('symlink')
            && @symlink($targetDir, $linkPath)
            && is_link($linkPath)
            && realpath($linkPath) === realpath($targetDir)
        ) {
            $symlink = true;
        }
    } finally {
        if (is_link($linkPath)) {
            @unlink($linkPath);
        }
        if (is_file($sourceFile)) {
            @unlink($sourceFile);
        }
        if (is_file($targetFile)) {
            @unlink($targetFile);
        }
        if (is_dir($sourceDir)) {
            @rmdir($sourceDir);
        }
        if (is_dir($targetDir)) {
            @rmdir($targetDir);
        }
        if (is_dir($probeRoot)) {
            @rmdir($probeRoot);
        }
    }

    return [
        'atomic_rename_supported' => $atomicRename,
        'symlink_supported' => $symlink,
    ];
}

/**
 * @param array<string,mixed> $input
 * @param array<string,mixed> $bindings
 * @return array<string,mixed>
 */
function cpanelProbeInspect(array $input, array $bindings, string $bindingPath): array
{
    cpanelProbeLiteral($input['schema_version'] ?? null, 1, 'input_schema_version_invalid');
    cpanelProbeLiteral($input['target_class'] ?? null, 'CPANEL_NO_SSH', 'target_class_invalid');
    cpanelProbeLiteral($input['execution_channel'] ?? null, 'CPANEL_CRON_PHP_CLI_NO_SSH', 'execution_channel_invalid');
    cpanelProbeLiteral($input['runtime_class'] ?? null, 'durable-staging', 'runtime_class_invalid');
    cpanelProbeBool($input['production'] ?? null, false, 'production_forbidden');
    cpanelProbeBool($input['production_data_allowed'] ?? null, false, 'production_data_forbidden');
    cpanelProbeBool($input['synthetic_fixture_runtime'] ?? null, false, 'synthetic_runtime_forbidden');
    cpanelProbeLiteral($input['attribution'] ?? null, 'Lab | zefry', 'attribution_invalid');

    $environmentId = cpanelProbePattern(
        $input['environment_id'] ?? null,
        '/\\A[a-z0-9][a-z0-9-]{2,62}\\z/',
        'environment_id_invalid',
    );
    $sourceCommit = cpanelProbePattern(
        $input['release_binding']['source_commit'] ?? null,
        '/\\A[0-9a-f]{40}\\z/',
        'source_commit_invalid',
    );
    $artifactSha256 = cpanelProbePattern(
        $input['release_binding']['artifact_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'artifact_sha256_invalid',
    );

    $deploymentRoot = cpanelProbeSafeAbsolutePath(
        $input['filesystem']['deployment_root'] ?? null,
        'deployment_root_invalid',
    );
    $releaseRoot = cpanelProbeSafeAbsolutePath(
        $input['filesystem']['release_root'] ?? null,
        'release_root_invalid',
    );
    $sharedRoot = cpanelProbeSafeAbsolutePath(
        $input['filesystem']['shared_runtime_root'] ?? null,
        'shared_root_invalid',
    );
    $activePointer = cpanelProbeSafeAbsolutePath(
        $input['filesystem']['active_release_pointer'] ?? null,
        'active_pointer_invalid',
    );
    $documentRoot = cpanelProbeSafeAbsolutePath(
        $input['filesystem']['document_root'] ?? null,
        'document_root_invalid',
    );
    $documentRootMode = cpanelProbePattern(
        $input['filesystem']['document_root_mode'] ?? 'ACTIVE_RELEASE_PUBLIC',
        '/\\A(?:ACTIVE_RELEASE_PUBLIC|FIXED_PUBLIC_BRIDGE)\\z/',
        'document_root_mode_invalid',
    );

    foreach ([$releaseRoot, $sharedRoot, $activePointer] as $path) {
        cpanelProbeNested($path, $deploymentRoot, 'filesystem_escape');
    }
    if (count(array_unique([$deploymentRoot, $releaseRoot, $sharedRoot, $activePointer], SORT_STRING)) !== 4) {
        cpanelProbeFail('filesystem_collision');
    }

    if ($documentRootMode === 'ACTIVE_RELEASE_PUBLIC') {
        cpanelProbeLiteral(
            $documentRoot,
            $activePointer.'/apps/web/public',
            'document_root_must_follow_active_release_public',
        );
    } else {
        if (str_starts_with($documentRoot.'/', $deploymentRoot.'/')
            || str_starts_with($deploymentRoot.'/', $documentRoot.'/')
        ) {
            cpanelProbeFail('fixed_public_document_root_must_be_disjoint_from_private_deployment');
        }
        cpanelProbeFixedPublicBridge($documentRoot);
    }

    foreach ([$deploymentRoot, $releaseRoot, $sharedRoot] as $path) {
        if (! is_dir($path)) {
            cpanelProbeFail('required_directory_missing');
        }
        if (! is_writable($path)) {
            cpanelProbeFail('required_directory_not_writable');
        }
    }
    $activeParent = dirname($activePointer);
    if (! is_dir($activeParent) || ! is_writable($activeParent)) {
        cpanelProbeFail('active_pointer_parent_not_writable');
    }

    $bindingReal = realpath($bindingPath);
    if (! is_string($bindingReal)) {
        cpanelProbeFail('binding_file_realpath_unavailable');
    }
    if (str_starts_with($bindingReal.'/', $documentRoot.'/')) {
        cpanelProbeFail('binding_file_inside_document_root');
    }

    $requiredExtensions = ['json', 'openssl', 'pdo', 'pdo_mysql', 'phar'];
    foreach ($requiredExtensions as $extension) {
        if (! extension_loaded($extension)) {
            cpanelProbeFail('required_php_extension_missing_'.$extension);
        }
    }
    if (PHP_VERSION_ID < 80200) {
        cpanelProbeFail('php_version_unsupported');
    }

    $filesystemProbe = cpanelProbeFilesystemOperations($deploymentRoot);
    if ($filesystemProbe['atomic_rename_supported'] !== true) {
        cpanelProbeFail('atomic_rename_unavailable');
    }
    if ($filesystemProbe['symlink_supported'] !== true) {
        cpanelProbeFail('symlink_unavailable');
    }

    $requiredBindingNames = [
        'ONEQAY_RUNTIME_CLASS',
        'ONEQAY_RUNNING_SOURCE_COMMIT',
        'ONEQAY_RUNNING_ARTIFACT_SHA256',
        'ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID',
        'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN',
        'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED',
    ];
    $actualBindingNames = array_keys($bindings);
    sort($actualBindingNames, SORT_STRING);
    $sortedRequiredNames = $requiredBindingNames;
    sort($sortedRequiredNames, SORT_STRING);
    if ($actualBindingNames !== $sortedRequiredNames) {
        cpanelProbeFail('binding_set_invalid');
    }

    foreach ($requiredBindingNames as $bindingName) {
        if (! is_string($bindings[$bindingName] ?? null) || $bindings[$bindingName] === '') {
            cpanelProbeFail('binding_value_missing');
        }
    }

    cpanelProbeLiteral($bindings['ONEQAY_RUNTIME_CLASS'], 'durable-staging', 'binding_runtime_class_mismatch');
    cpanelProbeLiteral($bindings['ONEQAY_RUNNING_SOURCE_COMMIT'], $sourceCommit, 'binding_source_commit_mismatch');
    cpanelProbeLiteral($bindings['ONEQAY_RUNNING_ARTIFACT_SHA256'], $artifactSha256, 'binding_artifact_sha256_mismatch');
    cpanelProbeLiteral($bindings['ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID'], $environmentId, 'binding_environment_id_mismatch');
    if (! in_array(strtolower($bindings['ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED']), ['1', 'true'], true)) {
        cpanelProbeFail('binding_durable_runtime_enabled_invalid');
    }
    $attestationToken = $bindings['ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN'];
    if (strlen($attestationToken) < 32 || strlen($attestationToken) > 256 || str_contains($attestationToken, "\0")) {
        cpanelProbeFail('binding_attestation_token_invalid');
    }

    $capabilityNames = [
        'durable_database_persistence',
        'durable_session',
        'authorization',
        'transaction_durability',
        'pos_durability',
        'authenticated_configuration_channel',
        'read_before_write',
        'read_after_write',
        'non_mutating_health_attestation',
        'verified_rollback',
    ];
    $assertions = $input['operator_assertions'] ?? null;
    if (! is_array($assertions) || array_is_list($assertions)) {
        cpanelProbeFail('operator_assertions_invalid');
    }
    $actualAssertionNames = array_keys($assertions);
    sort($actualAssertionNames, SORT_STRING);
    $sortedCapabilityNames = $capabilityNames;
    sort($sortedCapabilityNames, SORT_STRING);
    if ($actualAssertionNames !== $sortedCapabilityNames) {
        cpanelProbeFail('operator_assertion_set_invalid');
    }
    foreach ($capabilityNames as $capability) {
        cpanelProbeBool($assertions[$capability] ?? null, true, 'operator_capability_not_confirmed_'.$capability);
    }

    $bindingPresence = [];
    foreach ($requiredBindingNames as $bindingName) {
        $bindingPresence[$bindingName] = true;
    }

    return [
        'schema_version' => 1,
        'profile_state' => 'CPANEL_NO_SSH_TARGET_PROFILE_OBSERVED',
        'environment_id' => $environmentId,
        'runtime_class' => 'durable-staging',
        'production' => false,
        'production_data_allowed' => false,
        'synthetic_fixture_runtime' => false,
        'execution_channel' => 'CPANEL_CRON_PHP_CLI_NO_SSH',
        'release_binding' => [
            'source_commit' => $sourceCommit,
            'artifact_sha256' => $artifactSha256,
        ],
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
        'runtime' => [
            'php_version' => PHP_VERSION,
            'php_version_supported' => true,
            'required_extensions_present' => true,
            'pdo_mysql_available' => true,
        ],
        'configuration' => [
            'binding_file_private' => true,
            'required_binding_presence' => $bindingPresence,
            'required_binding_identity_matches' => true,
            'secret_values_embedded' => false,
        ],
        'presentation' => [
            'mode' => $documentRootMode,
            'document_root' => $documentRoot,
        ],
        'operator_assertions' => $assertions,
        'secrets_embedded' => false,
        'attribution' => 'Lab | zefry',
    ];
}

/** @param array<string,mixed> $payload */
function cpanelProbeWrite(string $outputPath, array $payload): void
{
    if ($outputPath === '' || is_link($outputPath) || is_dir($outputPath)) {
        cpanelProbeFail('output_path_invalid');
    }

    $directory = dirname($outputPath);
    if (! is_dir($directory) || ! is_writable($directory)) {
        cpanelProbeFail('output_directory_invalid');
    }

    $json = json_encode(
        $payload,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ).PHP_EOL;

    $temp = tempnam($directory, '.oneqay-cpanel-profile-');
    if ($temp === false) {
        cpanelProbeFail('output_temp_create_failed');
    }

    try {
        if (file_put_contents($temp, $json, LOCK_EX) !== strlen($json)) {
            cpanelProbeFail('output_write_failed');
        }
        @chmod($temp, 0600);
        if (! rename($temp, $outputPath)) {
            cpanelProbeFail('output_commit_failed');
        }
        @chmod($outputPath, 0600);
    } finally {
        if (is_file($temp)) {
            @unlink($temp);
        }
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 4) {
        fwrite(
            STDERR,
            "Usage: php tools/cpanel/inspect-durable-staging-cpanel-no-ssh-target.php <target-input.json> <private-bindings.json> <target-profile.json>\n",
        );
        exit(64);
    }

    try {
        $input = cpanelProbeLoadObject($argv[1]);
        $bindings = cpanelProbeLoadObject($argv[2], true);
        $profile = cpanelProbeInspect($input, $bindings, $argv[2]);
        cpanelProbeWrite($argv[3], $profile);
        fwrite(STDOUT, "cpanel_no_ssh_durable_staging_target_profile_observed\n");
        exit(0);
    } catch (CpanelNoSshDurableTargetInspectionException $failure) {
        fwrite(STDERR, "cpanel_no_ssh_target_inspection_failed:".$failure->getMessage()."\n");
        exit(1);
    } catch (Throwable) {
        fwrite(STDERR, "cpanel_no_ssh_target_inspection_failed:unexpected_failure\n");
        exit(1);
    }
}
