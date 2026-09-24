<?php

declare(strict_types=1);

require_once __DIR__.'/../../../tools/cpanel/execute-final-shift-close-feature-activation-readiness-successor.php';

// Author by Lab | zefry

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint247 regression failed: '.$message);
    }
};

$environment = 'oneqay-durable-staging-01';
$runtime = 'durable-staging';
$source = str_repeat('5', 40);
$artifact = str_repeat('6', 64);

$canonical = [
    'schema_version' => 1,
    'environment_id' => $environment,
    'runtime_class' => $runtime,
    'runtime_model' => 'NON_SYNTHETIC_DURABLE_RUNTIME',
    'environment_isolation' => 'ISOLATED_NON_PRODUCTION',
    'serving_application_runtime' => true,
    'synthetic_fixture_runtime' => false,
    'production_traffic_served' => false,
    'durable_persistence_enabled' => true,
    'durable_session_control_enabled' => true,
    'durable_authorization_enabled' => true,
    'durable_transaction_boundary_enabled' => true,
    'durable_pos_persistence_enabled' => true,
    'exact_running_source_commit' => $source,
    'exact_running_artifact_sha256' => $artifact,
    'authenticated_configuration_mutation_channel' => true,
    'read_before_write_read_after_supported' => true,
    'non_mutating_health_attestation_supported' => true,
    'verified_flag_rollback_supported' => true,
    'activation_authority_binding' => 'SEPARATE_EXPLICIT_ACTIVATION_AUTHORITY',
    'feature_activation_state' => 'ACTIVE',
    'secrets_embedded' => false,
];

$mapped = fscActivationMapCanonicalDurableStagingReadiness($canonical);
$assert(($mapped['environment_id'] ?? null) === $environment, 'environment mapping drifted');
$assert(($mapped['runtime_class'] ?? null) === $runtime, 'runtime mapping drifted');
$assert(($mapped['running_source_commit'] ?? null) === $source, 'source mapping drifted');
$assert(($mapped['running_artifact_sha256'] ?? null) === $artifact, 'artifact mapping drifted');
$assert(($mapped['durable_staging_runtime_enabled'] ?? null) === true, 'runtime enabled mapping drifted');
$assert(($mapped['production_data_allowed'] ?? null) === false, 'production boundary mapping drifted');

$inactiveDenied = false;
try {
    $inactive = $canonical;
    $inactive['feature_activation_state'] = 'INACTIVE';
    fscActivationMapCanonicalDurableStagingReadiness($inactive);
} catch (FinalShiftCloseActivationTransportException $exception) {
    $inactiveDenied = $exception->getMessage() === 'readiness_feature_activation_state_invalid';
}
$assert($inactiveDenied, 'inactive post-write readiness did not fail closed');

$productionDenied = false;
try {
    $production = $canonical;
    $production['production_traffic_served'] = true;
    fscActivationMapCanonicalDurableStagingReadiness($production);
} catch (FinalShiftCloseActivationTransportException $exception) {
    $productionDenied = $exception->getMessage() === 'readiness_production_traffic_invalid';
}
$assert($productionDenied, 'production traffic readiness did not fail closed');

$legacyDenied = false;
try {
    fscActivationMapCanonicalDurableStagingReadiness([
        'environment_id' => $environment,
        'runtime_class' => $runtime,
        'running_source_commit' => $source,
        'running_artifact_sha256' => $artifact,
        'durable_staging_runtime_enabled' => true,
        'production_data_allowed' => false,
    ]);
} catch (FinalShiftCloseActivationTransportException $exception) {
    $legacyDenied = $exception->getMessage() === 'readiness_schema_invalid';
}
$assert($legacyDenied, 'legacy readiness schema unexpectedly qualified');

$sourceText = (string) file_get_contents(__DIR__.'/../../../tools/cpanel/execute-final-shift-close-feature-activation-readiness-successor.php');
foreach ([
    'exact_running_source_commit',
    'exact_running_artifact_sha256',
    'NON_SYNTHETIC_DURABLE_RUNTIME',
    'ISOLATED_NON_PRODUCTION',
    'SEPARATE_EXPLICIT_ACTIVATION_AUTHORITY',
    'feature_activation_state',
    'fscActivationExecuteReadinessSuccessor',
] as $required) {
    $assert(str_contains($sourceText, $required), 'successor source missing '.$required);
}

foreach (['Artisan::call', 'migrate', 'production_activation = true'] as $forbidden) {
    $assert(! str_contains($sourceText, $forbidden), 'successor source contains forbidden primitive '.$forbidden);
}

fwrite(STDOUT, "Sprint247 Final Shift Close canonical readiness schema successor regression passed.\n");
