<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseDurableRuntimeCapabilityEvidence;
use App\Application\Pos\FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer;
use App\Application\Pos\FinalShiftCloseDurableRuntimeReadiness;
use App\Application\Pos\FinalShiftCloseDurableRuntimeTargetSelection;

require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeReadiness.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeTargetSelection.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeCapabilityEvidence.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer.php';

// Author by Lab | zefry

function sprint149Assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, "Sprint149 regression failed: {$message}\n");
        exit(1);
    }
}

/** @return array<string, mixed> */
function sprint149Attestation(): array
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

/** @param array<string, mixed> $selection @return array<string, mixed> */
function sprint149Observations(array $selection): array
{
    $target = $selection['selected_target'];
    $record = static fn (string $kind, string $payload): array => [
        'state' => 'VERIFIED',
        'evidence_kind' => $kind,
        'environment_id' => $target['environment_id'],
        'runtime_class' => $target['runtime_class'],
        'exact_running_source_commit' => $target['exact_running_source_commit'],
        'exact_running_artifact_sha256' => $target['exact_running_artifact_sha256'],
        'evidence_payload_sha256' => $payload,
        'secrets_embedded' => false,
    ];

    return [
        'schema_version' => 1,
        'feature' => FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer::FEATURE,
        'observation_state' => FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer::OBSERVATION_STATE,
        'capabilities' => [
            'authenticated_configuration_mutation_channel' => $record('AUTHENTICATED_CONFIGURATION_CHANNEL_IDENTITY_EVIDENCE', str_repeat('1', 64)),
            'read_before_write_read_after' => $record('READ_BEFORE_WRITE_READ_AFTER_VERIFICATION_EVIDENCE', str_repeat('2', 64)),
            'non_mutating_health_attestation' => $record('NON_MUTATING_HEALTH_ATTESTATION_EVIDENCE', str_repeat('3', 64)),
            'verified_flag_rollback' => $record('VERIFIED_FLAG_ROLLBACK_EVIDENCE', str_repeat('4', 64)),
        ],
        'secrets_embedded' => false,
    ];
}

$readiness = new FinalShiftCloseDurableRuntimeReadiness();
$selector = new FinalShiftCloseDurableRuntimeTargetSelection($readiness);
$qualifier = new FinalShiftCloseDurableRuntimeCapabilityEvidence($readiness, $selector);
$producer = new FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer($qualifier);
$attestation = sprint149Attestation();
$selection = $selector->select($attestation);
$observations = sprint149Observations($selection);

sprint149Assert($producer->canProduce($selection, $attestation, $observations), 'canonical observations must be producible');
$package = $producer->produce($selection, $attestation, $observations);
sprint149Assert($package['producer_state'] === FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer::PRODUCER_STATE, 'producer state mismatch');
sprint149Assert($qualifier->qualifies($selection, $attestation, $package['evidence']), 'produced evidence must satisfy Sprint148 qualifier');
sprint149Assert($package['activation_authority_state'] === 'NOT_GRANTED', 'producer must not grant authority');
sprint149Assert($package['feature_activation_state'] === 'INACTIVE', 'producer must keep feature inactive');
sprint149Assert($package['runtime_allowlist_change'] === 'NOT_IMPLEMENTED', 'producer must not change runtime allowlist');
sprint149Assert($package['secrets_embedded'] === false, 'producer package must be secret free');
sprint149Assert(strlen($package['producer_fingerprint_sha256']) === 64, 'producer fingerprint must be SHA-256');

$reordered = array_reverse($observations, true);
$reordered['capabilities'] = array_reverse($observations['capabilities'], true);
$reorderedPackage = $producer->produce($selection, $attestation, $reordered);
sprint149Assert($reorderedPackage['producer_fingerprint_sha256'] === $package['producer_fingerprint_sha256'], 'producer identity must be key-order independent');
sprint149Assert($reorderedPackage['evidence'] === $package['evidence'], 'produced evidence must be key-order independent');

$missing = $observations;
unset($missing['capabilities']['verified_flag_rollback']);
sprint149Assert(! $producer->canProduce($selection, $attestation, $missing), 'missing capability observation must fail closed');

$wrongKind = $observations;
$wrongKind['capabilities']['read_before_write_read_after']['evidence_kind'] = 'GENERIC_BOOLEAN_ASSERTION';
sprint149Assert(! $producer->canProduce($selection, $attestation, $wrongKind), 'generic boolean assertion must fail closed');

$wrongTarget = $observations;
$wrongTarget['capabilities']['non_mutating_health_attestation']['environment_id'] = 'another-durable-stage';
sprint149Assert(! $producer->canProduce($selection, $attestation, $wrongTarget), 'observation target drift must fail closed');

$badPayload = $observations;
$badPayload['capabilities']['verified_flag_rollback']['evidence_payload_sha256'] = str_repeat('G', 64);
sprint149Assert(! $producer->canProduce($selection, $attestation, $badPayload), 'malformed observation payload digest must fail closed');

$secret = $observations;
$secret['capabilities']['authenticated_configuration_mutation_channel']['secrets_embedded'] = true;
sprint149Assert(! $producer->canProduce($selection, $attestation, $secret), 'secret-bearing observations must fail closed');

$unexpected = $observations;
$unexpected['capabilities']['authenticated_configuration_mutation_channel']['token'] = 'must-never-be-accepted';
sprint149Assert(! $producer->canProduce($selection, $attestation, $unexpected), 'unexpected credential-like fields must fail closed');

$authorityDrift = $selection;
$authorityDrift['activation_authority_state'] = 'GRANTED';
sprint149Assert(! $producer->canProduce($authorityDrift, $attestation, $observations), 'authority drift must fail Sprint148 qualification');

$threw = false;
try {
    $producer->produce($selection, $attestation, $wrongTarget);
} catch (\InvalidArgumentException $exception) {
    $threw = str_starts_with($exception->getMessage(), 'final_shift_close_durable_runtime_capability_evidence_producer_rejected:');
}
sprint149Assert($threw, 'producer rejection must use canonical prefix');

fwrite(STDOUT, "Sprint149 Final Shift Close durable runtime capability evidence producer regression passed.\n");
