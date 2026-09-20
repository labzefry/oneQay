<?php

declare(strict_types=1);

// Author by Lab | zefry

final class ProductionOperatorTargetCandidateException extends RuntimeException {}

function prodCandidateFail(string $code): never { throw new ProductionOperatorTargetCandidateException($code); }

function prodCandidateLoad(string $path): array
{
    if ($path === '' || ! is_file($path) || is_link($path) || ! is_readable($path)) prodCandidateFail('input_unavailable');
    try { $value = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR); }
    catch (JsonException) { prodCandidateFail('json_invalid'); }
    if (! is_array($value) || array_is_list($value)) prodCandidateFail('shape_invalid');
    return $value;
}

function prodCandidateEq(mixed $actual, mixed $expected, string $code): void { if ($actual !== $expected) prodCandidateFail($code); }
function prodCandidateBool(mixed $actual, bool $expected, string $code): void { if (! is_bool($actual) || $actual !== $expected) prodCandidateFail($code); }
function prodCandidatePattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) prodCandidateFail($code);
    return $value;
}

/** @return array<string,mixed> */
function prodCandidatePrepare(array $profile): array
{
    prodCandidateEq($profile['schema_version'] ?? null, 1, 'schema_invalid');
    prodCandidateEq($profile['profile_state'] ?? null, 'PRODUCTION_OPERATOR_TARGET_PROFILE_OBSERVED', 'state_invalid');
    $targetClass = prodCandidatePattern($profile['target_class'] ?? null, '/\A(?:OPERATOR_MANAGED_FILESYSTEM|CPANEL_NO_SSH)\z/', 'target_class_invalid');
    $channel = prodCandidatePattern($profile['execution_channel'] ?? null, '/\A(?:PHP_CLI|CPANEL_CRON_PHP_CLI_NO_SSH)\z/', 'channel_invalid');
    $environment = prodCandidatePattern($profile['environment_id'] ?? null, '/\A[a-z0-9][a-z0-9-]{2,62}\z/', 'environment_invalid');
    prodCandidateEq($profile['runtime_class'] ?? null, 'production', 'runtime_invalid');
    prodCandidateBool($profile['production'] ?? null, true, 'production_required');
    prodCandidateBool($profile['production_data_allowed'] ?? null, true, 'production_data_required');
    prodCandidateBool($profile['isolated_environment'] ?? null, true, 'isolation_required');
    prodCandidateBool($profile['secrets_embedded'] ?? null, false, 'secret_forbidden');
    prodCandidateEq($profile['attribution'] ?? null, 'Lab | zefry', 'attribution_invalid');

    $releaseId = prodCandidatePattern($profile['release_binding']['release_id'] ?? null, '/\Aproduction-[0-9a-f]{12}\z/', 'release_invalid');
    $source = prodCandidatePattern($profile['release_binding']['source_commit'] ?? null, '/\A[0-9a-f]{40}\z/', 'source_invalid');
    $artifact = prodCandidatePattern($profile['release_binding']['artifact_sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'artifact_invalid');
    prodCandidateEq($releaseId, 'production-'.substr($source, 0, 12), 'release_source_mismatch');

    foreach (['deployment_root','release_root','shared_runtime_root','active_release_pointer','document_root'] as $field) {
        if (! is_string($profile['filesystem'][$field] ?? null) || $profile['filesystem'][$field] === '') prodCandidateFail('filesystem_'.$field.'_invalid');
    }
    foreach (['deployment_root_writable','release_root_writable','shared_runtime_root_writable','active_pointer_parent_writable','atomic_rename_supported','symlink_supported','document_root_shape_valid'] as $field) {
        prodCandidateBool($profile['filesystem'][$field] ?? null, true, 'filesystem_'.$field.'_required');
    }
    prodCandidateEq($profile['health']['path'] ?? null, '/health/live', 'health_path_invalid');
    prodCandidateBool($profile['health']['https_required'] ?? null, true, 'health_https_required');
    if (! is_string($profile['health']['url'] ?? null) || ! str_starts_with($profile['health']['url'], 'https://')) prodCandidateFail('health_url_invalid');
    prodCandidateBool($profile['runtime']['php_version_supported'] ?? null, true, 'runtime_version_required');
    prodCandidateBool($profile['runtime']['required_extensions_present'] ?? null, true, 'runtime_extensions_required');
    prodCandidateBool($profile['runtime']['https_client_available'] ?? null, true, 'https_client_required');
    prodCandidateBool($profile['configuration']['binding_file_private'] ?? null, true, 'binding_private_required');
    prodCandidateBool($profile['configuration']['required_binding_identity_matches'] ?? null, true, 'binding_identity_required');
    prodCandidateBool($profile['configuration']['secret_values_embedded'] ?? null, false, 'binding_secret_forbidden');

    $capabilities = [
        'durable_database_persistence','durable_session','authorization','transaction_durability','pos_durability',
        'authenticated_configuration_channel','read_before_write','read_after_write','non_mutating_health_attestation','verified_rollback',
    ];
    $resolved = [];
    foreach ($capabilities as $capability) {
        prodCandidateBool($profile['operator_assertions'][$capability] ?? null, true, 'capability_'.$capability.'_required');
        $resolved[$capability] = true;
    }

    return [
        'schema_version' => 1,
        'target_state' => 'PRODUCTION_TARGET_CANDIDATE',
        'target_class' => $targetClass,
        'execution_channel' => $channel,
        'environment_id' => $environment,
        'runtime_class' => 'production',
        'production' => true,
        'production_data_allowed' => true,
        'isolated_environment' => true,
        'release_binding' => ['release_id' => $releaseId, 'source_commit' => $source, 'artifact_sha256' => $artifact],
        'filesystem' => [
            'deployment_root' => $profile['filesystem']['deployment_root'],
            'release_root' => $profile['filesystem']['release_root'],
            'shared_runtime_root' => $profile['filesystem']['shared_runtime_root'],
            'active_release_pointer' => $profile['filesystem']['active_release_pointer'],
            'document_root' => $profile['filesystem']['document_root'],
        ],
        'health' => ['url' => $profile['health']['url'], 'path' => '/health/live'],
        'capabilities' => $resolved,
        'configuration' => [
            'secret_values_embedded' => false,
            'required_binding_presence' => $profile['configuration']['required_binding_presence'],
        ],
        'attribution' => 'Lab | zefry',
    ];
}

function prodCandidateWrite(string $path, array $payload): void
{
    $directory = dirname($path);
    if ($path === '' || is_link($path) || is_dir($path) || ! is_dir($directory) || ! is_writable($directory)) prodCandidateFail('output_invalid');
    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    $temp = tempnam($directory, '.oneqay-production-candidate-');
    if ($temp === false) prodCandidateFail('temp_failed');
    try {
        if (file_put_contents($temp, $json, LOCK_EX) !== strlen($json)) prodCandidateFail('write_failed');
        @chmod($temp, 0600);
        if (! rename($temp, $path)) prodCandidateFail('commit_failed');
        @chmod($path, 0600);
    } finally {
        if (is_file($temp)) @unlink($temp);
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 3) exit(64);
    try {
        prodCandidateWrite($argv[2], prodCandidatePrepare(prodCandidateLoad($argv[1])));
        fwrite(STDOUT, "production_target_candidate_prepared\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "production_target_candidate_failed:".$failure->getMessage()."\n");
        exit(1);
    }
}
