<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseDurableRuntimeReadiness;
use App\Application\Pos\FinalShiftCloseDurableRuntimeTargetSelection;
use InvalidArgumentException;

require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeReadiness.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeTargetSelection.php';

// Author by Lab | zefry

function assertTrue(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, $message."\n");
        exit(1);
    }
}

function qualifiedAttestation(): array
{
    return [
        'schema_version' => 1,
        'environment_id' => 'oneqay-durable-stage-01',
        'runtime_class' => 'durable-stage',
        'runtime_model' => FinalShiftCloseDurableRuntimeReadiness::RUNTIME_MODEL,
        'environment_isolation' => FinalShiftCloseDurableRuntimeReadiness::ENVIRONMENT_ISOLATION,
        'serving_application_runtime' => true,
        'synthetic_fixture_runtime' => false,
        'production_traffic_served' => false,
        'durable_persistence_enabled' => true,
        'durable_session_control_enabled' => true,
        'durable_authorization_enabled' => true,
        'durable_transaction_boundary_enabled' => true,
        'durable_pos_persistence_enabled' => true,
        'exact_running_source_commit' => str_repeat('a', 40),
        'exact_running_artifact_sha256' => str_repeat('b', 64),
        'authenticated_configuration_mutation_channel' => true,
        'read_before_write_read_after_supported' => true,
        'non_mutating_health_attestation_supported' => true,
        'verified_flag_rollback_supported' => true,
        'activation_authority_binding' => FinalShiftCloseDurableRuntimeReadiness::ACTIVATION_AUTHORITY_BINDING,
        'feature_activation_state' => FinalShiftCloseDurableRuntimeReadiness::FEATURE_ACTIVATION_STATE,
        'secrets_embedded' => false,
    ];
}

$selector = new FinalShiftCloseDurableRuntimeTargetSelection(
    new FinalShiftCloseDurableRuntimeReadiness(),
);

$attestation = qualifiedAttestation();
$selection = $selector->select($attestation);

assertTrue($selection['selection_state'] === 'SELECTED_NOT_AUTHORIZED', 'qualified target must remain not authorized');
assertTrue($selection['activation_authority_state'] === 'NOT_GRANTED', 'selection must not grant activation authority');
assertTrue($selection['feature_activation_state'] === 'INACTIVE', 'selection must keep feature inactive');
assertTrue($selection['runtime_allowlist_change'] === 'NOT_IMPLEMENTED', 'selection must not widen runtime allowlist');
assertTrue($selection['selected_target']['environment_id'] === 'oneqay-durable-stage-01', 'environment binding mismatch');
assertTrue($selection['selected_target']['runtime_class'] === 'durable-stage', 'runtime binding mismatch');
assertTrue(strlen($selection['selected_target']['readiness_attestation_sha256']) === 64, 'attestation digest must be SHA-256');
assertTrue(strlen($selection['selection_fingerprint_sha256']) === 64, 'selection fingerprint must be SHA-256');

$reordered = array_reverse($attestation, true);
$selectionReordered = $selector->select($reordered);
assertTrue(
    $selectionReordered['selected_target']['readiness_attestation_sha256'] === $selection['selected_target']['readiness_attestation_sha256'],
    'attestation digest must be key-order independent',
);
assertTrue(
    $selectionReordered['selection_fingerprint_sha256'] === $selection['selection_fingerprint_sha256'],
    'selection fingerprint must be deterministic',
);

$changedArtifact = $attestation;
$changedArtifact['exact_running_artifact_sha256'] = str_repeat('c', 64);
$changedSelection = $selector->select($changedArtifact);
assertTrue(
    $changedSelection['selection_fingerprint_sha256'] !== $selection['selection_fingerprint_sha256'],
    'artifact drift must change selection fingerprint',
);

$invalid = $attestation;
$invalid['runtime_class'] = 'preview';
$rejected = false;
try {
    $selector->select($invalid);
} catch (InvalidArgumentException) {
    $rejected = true;
}
assertTrue($rejected, 'unqualified runtime must be rejected');

$invalid = $attestation;
$invalid['feature_activation_state'] = 'ACTIVE';
$rejected = false;
try {
    $selector->select($invalid);
} catch (InvalidArgumentException) {
    $rejected = true;
}
assertTrue($rejected, 'already-active feature attestation must be rejected');

echo "Sprint111 durable runtime target selection regression passed.\n";
