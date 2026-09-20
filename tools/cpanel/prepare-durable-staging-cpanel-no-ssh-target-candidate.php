<?php

declare(strict_types=1);

// Author by Lab | zefry

final class CpanelNoSshTargetCandidateException extends RuntimeException
{
}

function cpanelCandidateFail(string $code): never
{
    throw new CpanelNoSshTargetCandidateException($code);
}

/** @return array<string,mixed> */
function cpanelCandidateLoadObject(string $path): array
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) {
        cpanelCandidateFail('profile_unavailable');
    }

    $size = filesize($path);
    if (! is_int($size) || $size < 2 || $size > 262144) {
        cpanelCandidateFail('profile_size_invalid');
    }

    $raw = file_get_contents($path);
    if (! is_string($raw)) {
        cpanelCandidateFail('profile_read_failed');
    }

    try {
        $decoded = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        cpanelCandidateFail('profile_json_invalid');
    }

    if (! is_array($decoded) || array_is_list($decoded)) {
        cpanelCandidateFail('profile_shape_invalid');
    }

    return $decoded;
}

function cpanelCandidateLiteral(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) {
        cpanelCandidateFail($code);
    }
}

function cpanelCandidateBool(mixed $actual, bool $expected, string $code): void
{
    if (! is_bool($actual) || $actual !== $expected) {
        cpanelCandidateFail($code);
    }
}

function cpanelCandidatePattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) {
        cpanelCandidateFail($code);
    }

    return $value;
}

function cpanelCandidateSafePath(mixed $value, string $code): string
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
        cpanelCandidateFail($code);
    }

    return rtrim($value, '/');
}

/** @return array<string,mixed> */
function cpanelCandidatePrepare(array $profile): array
{
    cpanelCandidateLiteral($profile['schema_version'] ?? null, 1, 'profile_schema_version_invalid');
    cpanelCandidateLiteral(
        $profile['profile_state'] ?? null,
        'CPANEL_NO_SSH_TARGET_PROFILE_OBSERVED',
        'profile_state_invalid',
    );
    $environmentId = cpanelCandidatePattern(
        $profile['environment_id'] ?? null,
        '/\\A[a-z0-9][a-z0-9-]{2,62}\\z/',
        'environment_id_invalid',
    );
    cpanelCandidateLiteral($profile['runtime_class'] ?? null, 'durable-staging', 'runtime_class_invalid');
    cpanelCandidateBool($profile['production'] ?? null, false, 'production_forbidden');
    cpanelCandidateBool($profile['production_data_allowed'] ?? null, false, 'production_data_forbidden');
    cpanelCandidateBool($profile['synthetic_fixture_runtime'] ?? null, false, 'synthetic_runtime_forbidden');
    cpanelCandidateLiteral(
        $profile['execution_channel'] ?? null,
        'CPANEL_CRON_PHP_CLI_NO_SSH',
        'execution_channel_invalid',
    );
    cpanelCandidatePattern(
        $profile['release_binding']['source_commit'] ?? null,
        '/\\A[0-9a-f]{40}\\z/',
        'source_commit_invalid',
    );
    cpanelCandidatePattern(
        $profile['release_binding']['artifact_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'artifact_sha256_invalid',
    );

    $deploymentRoot = cpanelCandidateSafePath(
        $profile['filesystem']['deployment_root'] ?? null,
        'deployment_root_invalid',
    );
    $releaseRoot = cpanelCandidateSafePath(
        $profile['filesystem']['release_root'] ?? null,
        'release_root_invalid',
    );
    $sharedRoot = cpanelCandidateSafePath(
        $profile['filesystem']['shared_runtime_root'] ?? null,
        'shared_root_invalid',
    );
    $activePointer = cpanelCandidateSafePath(
        $profile['filesystem']['active_release_pointer'] ?? null,
        'active_pointer_invalid',
    );
    $documentRoot = cpanelCandidateSafePath(
        $profile['filesystem']['document_root'] ?? null,
        'document_root_invalid',
    );

    foreach ([$releaseRoot, $sharedRoot, $activePointer] as $path) {
        if (! str_starts_with($path.'/', $deploymentRoot.'/')) {
            cpanelCandidateFail('filesystem_escape');
        }
    }
    cpanelCandidateLiteral(
        $documentRoot,
        $activePointer.'/apps/web/public',
        'document_root_shape_invalid',
    );

    foreach ([
        'deployment_root_writable',
        'release_root_writable',
        'shared_runtime_root_writable',
        'active_pointer_parent_writable',
        'atomic_rename_supported',
        'symlink_supported',
        'document_root_shape_valid',
    ] as $field) {
        cpanelCandidateBool($profile['filesystem'][$field] ?? null, true, 'filesystem_observation_failed_'.$field);
    }

    cpanelCandidateBool($profile['runtime']['php_version_supported'] ?? null, true, 'php_version_unsupported');
    cpanelCandidateBool(
        $profile['runtime']['required_extensions_present'] ?? null,
        true,
        'php_extensions_missing',
    );
    cpanelCandidateBool($profile['runtime']['pdo_mysql_available'] ?? null, true, 'pdo_mysql_unavailable');

    cpanelCandidateBool(
        $profile['configuration']['binding_file_private'] ?? null,
        true,
        'binding_file_not_private',
    );
    cpanelCandidateBool(
        $profile['configuration']['required_binding_identity_matches'] ?? null,
        true,
        'binding_identity_mismatch',
    );
    cpanelCandidateBool(
        $profile['configuration']['secret_values_embedded'] ?? null,
        false,
        'embedded_secret_forbidden',
    );

    $requiredBindings = [
        'ONEQAY_RUNTIME_CLASS',
        'ONEQAY_RUNNING_SOURCE_COMMIT',
        'ONEQAY_RUNNING_ARTIFACT_SHA256',
        'ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID',
        'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN',
        'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED',
    ];
    $bindingPresence = $profile['configuration']['required_binding_presence'] ?? null;
    if (! is_array($bindingPresence) || array_is_list($bindingPresence)) {
        cpanelCandidateFail('binding_presence_invalid');
    }
    $actualBindingNames = array_keys($bindingPresence);
    sort($actualBindingNames, SORT_STRING);
    $sortedRequiredBindings = $requiredBindings;
    sort($sortedRequiredBindings, SORT_STRING);
    if ($actualBindingNames !== $sortedRequiredBindings) {
        cpanelCandidateFail('binding_set_invalid');
    }
    foreach ($requiredBindings as $binding) {
        cpanelCandidateBool($bindingPresence[$binding] ?? null, true, 'binding_missing_'.$binding);
    }

    $capabilities = [
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
    $assertions = $profile['operator_assertions'] ?? null;
    if (! is_array($assertions) || array_is_list($assertions)) {
        cpanelCandidateFail('operator_assertions_invalid');
    }
    $actualCapabilities = array_keys($assertions);
    sort($actualCapabilities, SORT_STRING);
    $sortedCapabilities = $capabilities;
    sort($sortedCapabilities, SORT_STRING);
    if ($actualCapabilities !== $sortedCapabilities) {
        cpanelCandidateFail('operator_capability_set_invalid');
    }
    foreach ($capabilities as $capability) {
        cpanelCandidateBool($assertions[$capability] ?? null, true, 'operator_capability_missing_'.$capability);
    }

    cpanelCandidateBool($profile['secrets_embedded'] ?? null, false, 'profile_secret_forbidden');
    cpanelCandidateLiteral($profile['attribution'] ?? null, 'Lab | zefry', 'profile_attribution_invalid');

    return [
        'schema_version' => 1,
        'target_state' => 'OPERATOR_TARGET_CANDIDATE',
        'environment_id' => $environmentId,
        'runtime_class' => 'durable-staging',
        'production' => false,
        'production_data_allowed' => false,
        'synthetic_fixture_runtime' => false,
        'filesystem' => [
            'deployment_root' => $deploymentRoot,
            'release_root' => $releaseRoot,
            'shared_runtime_root' => $sharedRoot,
            'active_release_pointer' => $activePointer,
        ],
        'capabilities' => $assertions,
        'configuration' => [
            'secret_values_embedded' => false,
            'required_binding_presence' => $bindingPresence,
        ],
        'attribution' => 'Lab | zefry',
    ];
}

/** @param array<string,mixed> $payload */
function cpanelCandidateWrite(string $outputPath, array $payload): void
{
    if ($outputPath === '' || is_link($outputPath) || is_dir($outputPath)) {
        cpanelCandidateFail('output_path_invalid');
    }

    $directory = dirname($outputPath);
    if (! is_dir($directory) || ! is_writable($directory)) {
        cpanelCandidateFail('output_directory_invalid');
    }

    $json = json_encode(
        $payload,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ).PHP_EOL;

    $temp = tempnam($directory, '.oneqay-cpanel-candidate-');
    if ($temp === false) {
        cpanelCandidateFail('output_temp_create_failed');
    }

    try {
        if (file_put_contents($temp, $json, LOCK_EX) !== strlen($json)) {
            cpanelCandidateFail('output_write_failed');
        }
        @chmod($temp, 0600);
        if (! rename($temp, $outputPath)) {
            cpanelCandidateFail('output_commit_failed');
        }
        @chmod($outputPath, 0600);
    } finally {
        if (is_file($temp)) {
            @unlink($temp);
        }
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 3) {
        fwrite(
            STDERR,
            "Usage: php tools/cpanel/prepare-durable-staging-cpanel-no-ssh-target-candidate.php <target-profile.json> <target-candidate.json>\n",
        );
        exit(64);
    }

    try {
        $profile = cpanelCandidateLoadObject($argv[1]);
        $candidate = cpanelCandidatePrepare($profile);
        cpanelCandidateWrite($argv[2], $candidate);
        fwrite(STDOUT, "cpanel_no_ssh_durable_staging_target_candidate_prepared\n");
        exit(0);
    } catch (CpanelNoSshTargetCandidateException $failure) {
        fwrite(STDERR, "cpanel_no_ssh_target_candidate_failed:".$failure->getMessage()."\n");
        exit(1);
    } catch (Throwable) {
        fwrite(STDERR, "cpanel_no_ssh_target_candidate_failed:unexpected_failure\n");
        exit(1);
    }
}
