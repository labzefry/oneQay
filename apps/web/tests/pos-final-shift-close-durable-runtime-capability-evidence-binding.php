<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseDurableRuntimeCapabilityEvidence;
use App\Application\Pos\FinalShiftCloseDurableRuntimeReadiness;
use App\Application\Pos\FinalShiftCloseDurableRuntimeTargetSelection;
use InvalidArgumentException;

require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeReadiness.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeTargetSelection.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeCapabilityEvidence.php';

// Author by Lab | zefry

function sprint148Assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, "Sprint148 regression failed: {$message}\n");
        exit(1);
    }
}

/** @return array<string, mixed> */
function sprint148QualifiedAttestation(): array
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

/**
 * @param array<string, mixed> $selection
 * @return array<string, mixed>
 */
function sprint148Evidence(
    FinalShiftCloseDurableRuntimeCapabilityEvidence $qualifier,
    array $selection,
): array {
    $target = $selection['selected_target'];
    $targetBinding = $qualifier->targetBindingSha256($selection);

    $record = static fn (string $kind, string $digest): array => [
        'state' => 'VERIFIED',
        'evidence_kind' => $kind,
        'evidence_sha256' => $digest,
        'target_binding_sha256' => $targetBinding,
        'secrets_embedded' => false,
    ];

    return [
        'schema_version' => 1,
        'feature' => FinalShiftCloseDurableRuntimeCapabilityEvidence::FEATURE,
        'evidence_state' => FinalShiftCloseDurableRuntimeCapabilityEvidence::EVIDENCE_STATE,
        'environment_id' => $target['environment_id'],
        'runtime_class' => $target['runtime_class'],
        'exact_running_source_commit' => $target['exact_running_source_commit'],
        'exact_running_artifact_sha256' => $target['exact_running_artifact_sha256'],
        'readiness_attestation_sha256' => $target['readiness_attestation_sha256'],
        'selection_fingerprint_sha256' => $selection['selection_fingerprint_sha256'],
        'target_binding_sha256' => $targetBinding,
        'capabilities' => [
            'authenticated_configuration_mutation_channel' => $record(
                'AUTHENTICATED_CONFIGURATION_CHANNEL_IDENTITY_EVIDENCE',
                str_repeat('1', 64),
            ),
            'read_before_write_read_after' => $record(
                'READ_BEFORE_WRITE_READ_AFTER_VERIFICATION_EVIDENCE',
                str_repeat('2', 64),
            ),
            'non_mutating_health_attestation' => $record(
                'NON_MUTATING_HEALTH_ATTESTATION_EVIDENCE',
                str_repeat('3', 64),
            ),
            'verified_flag_rollback' => $record(
                'VERIFIED_FLAG_ROLLBACK_EVIDENCE',
                str_repeat('4', 64),
            ),
        ],
        'secrets_embedded' => false,
    ];
}

$readiness = new FinalShiftCloseDurableRuntimeReadiness();
$selector = new FinalShiftCloseDurableRuntimeTargetSelection($readiness);
$qualifier = new FinalShiftCloseDurableRuntimeCapabilityEvidence($readiness, $selector);
$attestation = sprint148QualifiedAttestation();
$selection = $selector->select($attestation);
$evidence = sprint148Evidence($qualifier, $selection);

sprint148Assert($qualifier->qualifies($selection, $attestation, $evidence), 'canonical target-bound capability evidence must qualify');
$qualified = $qualifier->qualify($selection, $attestation, $evidence);
sprint148Assert(
    $qualified['qualification_state'] === FinalShiftCloseDurableRuntimeCapabilityEvidence::QUALIFICATION_STATE,
    'qualification state mismatch',
);
sprint148Assert($qualified['activation_authority_state'] === 'NOT_GRANTED', 'qualification must not grant activation authority');
sprint148Assert($qualified['feature_activation_state'] === 'INACTIVE', 'qualification must keep feature inactive');
sprint148Assert($qualified['runtime_allowlist_change'] === 'NOT_IMPLEMENTED', 'qualification must not widen runtime allowlist');
sprint148Assert(strlen($qualified['target_binding_sha256']) === 64, 'target binding must be SHA-256');
sprint148Assert(strlen($qualified['capability_evidence_bundle_sha256']) === 64, 'evidence bundle fingerprint must be SHA-256');

$reorderedEvidence = array_reverse($evidence, true);
$reorderedEvidence['capabilities'] = array_reverse($evidence['capabilities'], true);
$reorderedQualified = $qualifier->qualify($selection, $attestation, $reorderedEvidence);
sprint148Assert(
    $reorderedQualified['capability_evidence_bundle_sha256'] === $qualified['capability_evidence_bundle_sha256'],
    'evidence bundle fingerprint must be key-order independent',
);

$missingEvidence = $evidence;
unset($missingEvidence['capabilities']['verified_flag_rollback']);
sprint148Assert(
    ! $qualifier->qualifies($selection, $attestation, $missingEvidence),
    'missing capability evidence must fail closed',
);

$malformedDigest = $evidence;
$malformedDigest['capabilities']['non_mutating_health_attestation']['evidence_sha256'] = str_repeat('G', 64);
sprint148Assert(
    ! $qualifier->qualifies($selection, $attestation, $malformedDigest),
    'malformed capability digest must fail closed',
);

$wrongKind = $evidence;
$wrongKind['capabilities']['read_before_write_read_after']['evidence_kind'] = 'GENERIC_BOOLEAN_ASSERTION';
sprint148Assert(
    ! $qualifier->qualifies($selection, $attestation, $wrongKind),
    'generic boolean evidence kind must fail closed',
);

$wrongBinding = $evidence;
$wrongBinding['capabilities']['verified_flag_rollback']['target_binding_sha256'] = str_repeat('f', 64);
sprint148Assert(
    ! $qualifier->qualifies($selection, $attestation, $wrongBinding),
    'capability evidence bound to another target must fail closed',
);

$identityDrift = $evidence;
$identityDrift['runtime_class'] = 'durable-stage-alias';
sprint148Assert(
    ! $qualifier->qualifies($selection, $attestation, $identityDrift),
    'runtime identity drift must fail closed',
);

$secretEvidence = $evidence;
$secretEvidence['capabilities']['authenticated_configuration_mutation_channel']['secrets_embedded'] = true;
sprint148Assert(
    ! $qualifier->qualifies($selection, $attestation, $secretEvidence),
    'secret-bearing capability evidence must fail closed',
);

$booleanOnlyAttestation = $attestation;
$booleanOnlyAttestation['verified_flag_rollback_supported'] = false;
sprint148Assert(
    ! $qualifier->qualifies($selection, $booleanOnlyAttestation, $evidence),
    'capability evidence must not rescue an unqualified Sprint110 readiness attestation',
);

$selectionDrift = $selection;
$selectionDrift['selection_fingerprint_sha256'] = str_repeat('e', 64);
sprint148Assert(
    ! $qualifier->qualifies($selectionDrift, $attestation, $evidence),
    'selection fingerprint drift must fail closed',
);

$authorityDrift = $selection;
$authorityDrift['activation_authority_state'] = 'GRANTED';
sprint148Assert(
    ! $qualifier->qualifies($authorityDrift, $attestation, $evidence),
    'capability qualification must never accept activation authority drift',
);

$threw = false;
try {
    $qualifier->qualify($selection, $attestation, $wrongBinding);
} catch (InvalidArgumentException $exception) {
    $threw = str_starts_with(
        $exception->getMessage(),
        'final_shift_close_durable_runtime_capability_evidence_rejected:',
    );
}
sprint148Assert($threw, 'require qualification path must reject with canonical prefix');

fwrite(STDOUT, "Sprint148 Final Shift Close durable runtime capability evidence binding regression passed.\n");
