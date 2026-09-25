<?php

declare(strict_types=1);

// Author by Lab | zefry

const ONEQAY_POST_ACTIVATION_DEPLOYMENT_EVIDENCE_MAX_BYTES = 32768;

function paFail(string $code): never { throw new RuntimeException($code); }
function paExact(mixed $actual, mixed $expected, string $code): void { if ($actual !== $expected) paFail($code); }
function paPattern(mixed $value, string $pattern, string $code): string { if (!is_string($value) || preg_match($pattern, $value) !== 1) paFail($code); return $value; }
function paObject(mixed $value, string $code): array { if (!is_array($value) || array_is_list($value)) paFail($code); return $value; }
function paCanonical(mixed $value): mixed {
    if (!is_array($value)) return $value;
    if (array_is_list($value)) return array_map('paCanonical', $value);
    ksort($value, SORT_STRING);
    foreach ($value as $key => $entry) $value[$key] = paCanonical($entry);
    return $value;
}
function paLoad(string $path): array {
    if ($path === '' || !is_file($path) || is_link($path) || !is_readable($path)) paFail('evidence_unavailable');
    $size = filesize($path);
    if (!is_int($size) || $size < 2 || $size > ONEQAY_POST_ACTIVATION_DEPLOYMENT_EVIDENCE_MAX_BYTES) paFail('evidence_size_invalid');
    $decoded = json_decode((string) file_get_contents($path), true, 32, JSON_THROW_ON_ERROR);
    return paObject($decoded, 'evidence_shape_invalid');
}

if ($argc !== 8) {
    fwrite(STDERR, "Usage: php tools/qualify-durable-staging-post-activation-deployment-evidence.php <evidence.json> <environment-id> <runtime-class> <source-commit> <artifact-sha256> <plan-fingerprint> <authority-sha256>\n");
    exit(64);
}

try {
    [$script, $path, $environment, $runtimeClass, $source, $artifact, $planFingerprint, $authoritySha] = $argv;
    $evidence = paLoad($path);
    paExact($runtimeClass, 'durable-staging', 'runtime_class_invalid');
    paPattern($environment, '/\A[a-z0-9][a-z0-9-]{2,62}\z/', 'environment_invalid');
    paPattern($source, '/\A[0-9a-f]{40}\z/', 'source_invalid');
    paPattern($artifact, '/\A[0-9a-f]{64}\z/', 'artifact_invalid');
    paPattern($planFingerprint, '/\A[0-9a-f]{64}\z/', 'plan_invalid');
    paPattern($authoritySha, '/\A[0-9a-f]{64}\z/', 'authority_invalid');

    paExact($evidence['schema_version'] ?? null, 1, 'schema_invalid');
    paExact($evidence['product'] ?? null, 'oneQay', 'product_invalid');
    paExact($evidence['evidence_state'] ?? null, 'DEPLOYED_VERIFIED_NOT_SELECTED', 'state_invalid');
    paExact($evidence['environment_id'] ?? null, $environment, 'environment_mismatch');
    paExact($evidence['runtime_class'] ?? null, 'durable-staging', 'runtime_mismatch');
    paPattern($evidence['release_id'] ?? null, '/\Adurable-staging-[0-9a-f]{12}\z/', 'release_invalid');
    paExact($evidence['source_commit'] ?? null, $source, 'source_mismatch');
    paExact($evidence['artifact_sha256'] ?? null, $artifact, 'artifact_mismatch');
    paExact($evidence['deployment_plan_fingerprint'] ?? null, $planFingerprint, 'plan_mismatch');
    paExact($evidence['deployment_authority_sha256'] ?? null, $authoritySha, 'authority_mismatch');
    paPattern($evidence['deployment_authority_id'] ?? null, '/\Adurable-staging-deployment-authority-[0-9a-f]{24}\z/', 'authority_id_invalid');
    paPattern($evidence['deployment_request_id'] ?? null, '/\Adurable-staging-deployment-request-[0-9a-f]{24}\z/', 'request_id_invalid');
    paPattern($evidence['deployment_request_sha256'] ?? null, '/\A[0-9a-f]{64}\z/', 'request_sha_invalid');

    $verification = paObject($evidence['verification'] ?? null, 'verification_invalid');
    foreach (['preflight_passed','previous_active_release_preserved','immutable_release_extracted','public_document_root_verified','external_runtime_configuration_bound','provenance_readback_verified','configuration_read_before_write_verified','configuration_read_after_write_verified','non_mutating_health_attestation_verified','rollback_path_verified'] as $field) paExact($verification[$field] ?? null, true, 'verification_'.$field.'_invalid');

    $readback = paObject($evidence['runtime_readback'] ?? null, 'readback_invalid');
    paExact($readback['environment_id'] ?? null, $environment, 'readback_environment_invalid');
    paExact($readback['runtime_class'] ?? null, 'durable-staging', 'readback_runtime_invalid');
    paExact($readback['exact_running_source_commit'] ?? null, $source, 'readback_source_invalid');
    paExact($readback['exact_running_artifact_sha256'] ?? null, $artifact, 'readback_artifact_invalid');
    paExact($readback['durable_staging_runtime_enabled'] ?? null, true, 'readback_runtime_enabled_invalid');
    paExact($readback['production_data_allowed'] ?? null, false, 'readback_production_data_invalid');

    $boundary = paObject($evidence['operational_boundary'] ?? null, 'boundary_invalid');
    foreach ([
        'migration27_execution'=>'NOT_PERFORMED',
        'permission_provisioning'=>'NONE',
        'feature_activation'=>'ACTIVE',
        'technical_preview_activation'=>'NOT_AUTHORIZED',
        'production_activation'=>'NOT_AUTHORIZED',
        'updater_activation'=>'INACTIVE',
        'target_selection'=>'NOT_PERFORMED',
        'producer_dispatch'=>'NOT_PERFORMED',
    ] as $field => $expected) paExact($boundary[$field] ?? null, $expected, 'boundary_'.$field.'_invalid');
    paExact(array_key_exists('selected_target', $boundary) ? $boundary['selected_target'] : '__missing__', null, 'boundary_selected_target_invalid');
    paExact($evidence['secrets_embedded'] ?? null, false, 'secret_boundary_invalid');
    paExact($evidence['attribution'] ?? null, 'Lab | zefry', 'attribution_invalid');

    $sha = hash('sha256', json_encode(paCanonical($evidence), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    echo json_encode([
        'schema_version'=>1,
        'qualification_state'=>'QUALIFIED_POST_ACTIVATION_DEPLOYMENT_EVIDENCE_NOT_SELECTED',
        'deployment_evidence_sha256'=>$sha,
        'environment_id'=>$environment,
        'runtime_class'=>'durable-staging',
        'exact_running_source_commit'=>$source,
        'exact_running_artifact_sha256'=>$artifact,
        'deployment_plan_fingerprint'=>$planFingerprint,
        'deployment_authority_sha256'=>$authoritySha,
        'feature_activation_state'=>'ACTIVE',
        'selected_target'=>null,
        'producer_dispatch'=>'NOT_PERFORMED',
        'attribution'=>'Lab | zefry',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    exit(0);
} catch (Throwable) {
    fwrite(STDERR, "Durable staging post-activation deployment evidence qualification failed.\n");
    exit(1);
}
