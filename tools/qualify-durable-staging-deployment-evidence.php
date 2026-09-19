<?php

declare(strict_types=1);

// Author by Lab | zefry

const ONEQAY_DEPLOYMENT_EVIDENCE_MAX_BYTES = 32768;

if ($argc !== 8) {
    fwrite(STDERR, "Usage: php tools/qualify-durable-staging-deployment-evidence.php <evidence.json> <environment-id> <runtime-class> <source-commit> <artifact-sha256> <plan-fingerprint> <authority-sha256>\n");
    exit(64);
}

[$script, $evidencePath, $expectedEnvironment, $expectedRuntimeClass, $expectedSourceCommit, $expectedArtifactSha256, $expectedPlanFingerprint, $expectedAuthoritySha256] = $argv;

try {
    $evidence = readJsonObject($evidencePath);
    requireExact($evidence['schema_version'] ?? null, 1, 'schema_version');
    requireExact($evidence['product'] ?? null, 'oneQay', 'product');
    requireExact($evidence['evidence_state'] ?? null, 'DEPLOYED_VERIFIED_NOT_SELECTED', 'evidence_state');
    requirePattern($expectedEnvironment, '/\A[a-z0-9][a-z0-9-]{2,62}\z/', 'expected_environment_id');
    requireExact($expectedRuntimeClass, 'durable-staging', 'expected_runtime_class');
    requirePattern($expectedSourceCommit, '/\A[0-9a-f]{40}\z/', 'expected_source_commit');
    requirePattern($expectedArtifactSha256, '/\A[0-9a-f]{64}\z/', 'expected_artifact_sha256');
    requirePattern($expectedPlanFingerprint, '/\A[0-9a-f]{64}\z/', 'expected_plan_fingerprint');
    requirePattern($expectedAuthoritySha256, '/\A[0-9a-f]{64}\z/', 'expected_authority_sha256');

    requireExact($evidence['environment_id'] ?? null, $expectedEnvironment, 'environment_id');
    requireExact($evidence['runtime_class'] ?? null, $expectedRuntimeClass, 'runtime_class');
    requirePattern((string) ($evidence['release_id'] ?? ''), '/\Adurable-staging-[0-9a-f]{12}\z/', 'release_id');
    requireExact($evidence['source_commit'] ?? null, $expectedSourceCommit, 'source_commit');
    requireExact($evidence['artifact_sha256'] ?? null, $expectedArtifactSha256, 'artifact_sha256');
    requireExact($evidence['deployment_plan_fingerprint'] ?? null, $expectedPlanFingerprint, 'deployment_plan_fingerprint');
    requirePattern((string) ($evidence['deployment_authority_id'] ?? ''), '/\Adurable-staging-deployment-authority-[0-9a-f]{24}\z/', 'deployment_authority_id');
    requireExact($evidence['deployment_authority_sha256'] ?? null, $expectedAuthoritySha256, 'deployment_authority_sha256');
    requirePattern((string) ($evidence['deployment_request_id'] ?? ''), '/\Adurable-staging-deployment-request-[0-9a-f]{24}\z/', 'deployment_request_id');
    requirePattern((string) ($evidence['deployment_request_sha256'] ?? ''), '/\A[0-9a-f]{64}\z/', 'deployment_request_sha256');

    $verification = requireObject($evidence['verification'] ?? null, 'verification');
    foreach ([
        'preflight_passed',
        'previous_active_release_preserved',
        'immutable_release_extracted',
        'public_document_root_verified',
        'external_runtime_configuration_bound',
        'provenance_readback_verified',
        'configuration_read_before_write_verified',
        'configuration_read_after_write_verified',
        'non_mutating_health_attestation_verified',
        'rollback_path_verified',
    ] as $field) {
        requireExact($verification[$field] ?? null, true, "verification.$field");
    }

    $readback = requireObject($evidence['runtime_readback'] ?? null, 'runtime_readback');
    requireExact($readback['environment_id'] ?? null, $expectedEnvironment, 'runtime_readback.environment_id');
    requireExact($readback['runtime_class'] ?? null, 'durable-staging', 'runtime_readback.runtime_class');
    requireExact($readback['exact_running_source_commit'] ?? null, $expectedSourceCommit, 'runtime_readback.exact_running_source_commit');
    requireExact($readback['exact_running_artifact_sha256'] ?? null, $expectedArtifactSha256, 'runtime_readback.exact_running_artifact_sha256');
    requireExact($readback['durable_staging_runtime_enabled'] ?? null, true, 'runtime_readback.durable_staging_runtime_enabled');
    requireExact($readback['production_data_allowed'] ?? null, false, 'runtime_readback.production_data_allowed');

    $boundary = requireObject($evidence['operational_boundary'] ?? null, 'operational_boundary');
    foreach ([
        'migration27_execution' => 'NOT_PERFORMED',
        'permission_provisioning' => 'NONE',
        'feature_activation' => 'INACTIVE',
        'technical_preview_activation' => 'NOT_AUTHORIZED',
        'production_activation' => 'NOT_AUTHORIZED',
        'updater_activation' => 'INACTIVE',
        'target_selection' => 'NOT_PERFORMED',
        'producer_dispatch' => 'NOT_PERFORMED',
    ] as $field => $expected) {
        requireExact($boundary[$field] ?? null, $expected, "operational_boundary.$field");
    }
    requireExact(array_key_exists('selected_target', $boundary) ? $boundary['selected_target'] : '__missing__', null, 'operational_boundary.selected_target');
    requireExact($evidence['secrets_embedded'] ?? null, false, 'secrets_embedded');
    requireExact($evidence['attribution'] ?? null, 'Lab | zefry', 'attribution');

    $canonical = canonicalize($evidence);
    $sha256 = hash('sha256', json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

    $qualification = [
        'schema_version' => 1,
        'qualification_state' => 'QUALIFIED_DEPLOYMENT_EVIDENCE_NOT_SELECTED',
        'deployment_evidence_sha256' => $sha256,
        'environment_id' => $expectedEnvironment,
        'runtime_class' => 'durable-staging',
        'exact_running_source_commit' => $expectedSourceCommit,
        'exact_running_artifact_sha256' => $expectedArtifactSha256,
        'deployment_plan_fingerprint' => $expectedPlanFingerprint,
        'deployment_authority_sha256' => $expectedAuthoritySha256,
        'selected_target' => null,
        'producer_dispatch' => 'NOT_PERFORMED',
        'attribution' => 'Lab | zefry',
    ];

    fwrite(STDOUT, json_encode($qualification, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "Durable staging deployment evidence qualification failed.\n");
    exit(1);
}

/** @return array<string,mixed> */
function readJsonObject(string $path): array
{
    if ($path === '' || str_contains($path, "\0") || !is_file($path) || is_link($path) || !is_readable($path)) {
        throw new RuntimeException('evidence_unavailable');
    }

    $size = filesize($path);
    if (!is_int($size) || $size <= 1 || $size > ONEQAY_DEPLOYMENT_EVIDENCE_MAX_BYTES) {
        throw new RuntimeException('evidence_size_invalid');
    }

    $raw = file_get_contents($path);
    if (!is_string($raw)) {
        throw new RuntimeException('evidence_read_failed');
    }

    $decoded = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($decoded) || array_is_list($decoded)) {
        throw new RuntimeException('evidence_must_be_object');
    }

    return $decoded;
}

/** @return array<string,mixed> */
function requireObject(mixed $value, string $field): array
{
    if (!is_array($value) || array_is_list($value)) {
        throw new RuntimeException("invalid_$field");
    }

    return $value;
}

function requireExact(mixed $actual, mixed $expected, string $field): void
{
    if ($actual !== $expected) {
        throw new RuntimeException("mismatch_$field");
    }
}

function requirePattern(string $value, string $pattern, string $field): void
{
    if (preg_match($pattern, $value) !== 1) {
        throw new RuntimeException("invalid_$field");
    }
}

function canonicalize(mixed $value): mixed
{
    if (!is_array($value)) {
        return $value;
    }

    if (array_is_list($value)) {
        return array_map('canonicalize', $value);
    }

    ksort($value, SORT_STRING);
    foreach ($value as $key => $entry) {
        $value[$key] = canonicalize($entry);
    }

    return $value;
}
