<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseDurableRuntimeCapabilityEvidence;
use App\Application\Pos\FinalShiftCloseDurableRuntimeDependencyEnvelope;
use App\Application\Pos\FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer;
use App\Application\Pos\FinalShiftCloseDurableRuntimeReadiness;
use App\Application\Pos\FinalShiftCloseDurableRuntimeTargetSelection;
use InvalidArgumentException;

require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeReadiness.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeTargetSelection.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeCapabilityEvidence.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeDependencyEnvelope.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer.php';

// Author by Lab | zefry

function sprint151Assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, "Sprint151 regression failed: {$message}\n");
        exit(1);
    }
}

/** @return array<string, mixed> */
function sprint151Attestation(): array
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
function sprint151CapabilityEvidence(
    FinalShiftCloseDurableRuntimeCapabilityEvidence $qualifier,
    array $selection,
): array {
    $target = $selection['selected_target'];
    $binding = $qualifier->targetBindingSha256($selection);
    $record = static fn (string $kind, string $digest): array => [
        'state' => 'VERIFIED',
        'evidence_kind' => $kind,
        'evidence_sha256' => $digest,
        'target_binding_sha256' => $binding,
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
        'target_binding_sha256' => $binding,
        'capabilities' => [
            'authenticated_configuration_mutation_channel' => $record('AUTHENTICATED_CONFIGURATION_CHANNEL_IDENTITY_EVIDENCE', str_repeat('1', 64)),
            'read_before_write_read_after' => $record('READ_BEFORE_WRITE_READ_AFTER_VERIFICATION_EVIDENCE', str_repeat('2', 64)),
            'non_mutating_health_attestation' => $record('NON_MUTATING_HEALTH_ATTESTATION_EVIDENCE', str_repeat('3', 64)),
            'verified_flag_rollback' => $record('VERIFIED_FLAG_ROLLBACK_EVIDENCE', str_repeat('4', 64)),
        ],
        'secrets_embedded' => false,
    ];
}

/** @param array<string, mixed> $selection @return array<string, mixed> */
function sprint151Observations(array $selection): array
{
    $target = $selection['selected_target'];
    $paths = [
        'final_shift_close_delivery' => 'apps/web/app/Providers/FinalShiftCloseServiceProvider.php',
        'pos_session_context' => 'apps/web/app/Delivery/Http/Middleware/RequirePosSessionContextMiddleware.php',
        'active_first_party_session_middleware' => 'apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php',
        'first_party_session_authority_repository' => 'apps/web/app/Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php',
        'first_party_identity_eligibility' => 'apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityVerifier.php',
        'durable_role_permission_authorization' => 'apps/web/app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php',
        'durable_persistence_transaction' => 'apps/web/app/Infrastructure/Persistence/LaravelPersistenceTransaction.php',
        'final_shift_close_repository' => 'apps/web/app/Infrastructure/Pos/LaravelCloseShiftRepository.php',
        'durable_pos_sale_lifecycle' => 'apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php',
    ];

    $components = [];
    $counter = 5;
    foreach ($paths as $component => $path) {
        $components[$component] = [
            'state' => 'VERIFIED',
            'source_path' => $path,
            'environment_id' => $target['environment_id'],
            'runtime_class' => $target['runtime_class'],
            'exact_running_source_commit' => $target['exact_running_source_commit'],
            'exact_running_artifact_sha256' => $target['exact_running_artifact_sha256'],
            'evidence_payload_sha256' => str_repeat(dechex($counter), 64),
            'synthetic_dependency' => false,
            'secrets_embedded' => false,
        ];
        $counter++;
    }

    return [
        'schema_version' => 1,
        'feature' => FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer::FEATURE,
        'observation_state' => FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer::OBSERVATION_STATE,
        'components' => $components,
        'synthetic_mixing' => false,
        'secrets_embedded' => false,
    ];
}

$readiness = new FinalShiftCloseDurableRuntimeReadiness();
$selector = new FinalShiftCloseDurableRuntimeTargetSelection($readiness);
$capabilityQualifier = new FinalShiftCloseDurableRuntimeCapabilityEvidence($readiness, $selector);
$dependencyQualifier = new FinalShiftCloseDurableRuntimeDependencyEnvelope($capabilityQualifier);
$producer = new FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer(
    $dependencyQualifier,
    $capabilityQualifier,
);
$attestation = sprint151Attestation();
$selection = $selector->select($attestation);
$capabilityEvidence = sprint151CapabilityEvidence($capabilityQualifier, $selection);
$observations = sprint151Observations($selection);

sprint151Assert(
    $producer->canProduce($selection, $attestation, $capabilityEvidence, $observations),
    'canonical dependency observations must be producible',
);
$package = $producer->produce($selection, $attestation, $capabilityEvidence, $observations);
sprint151Assert(
    $package['producer_state'] === FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer::PRODUCER_STATE,
    'producer state mismatch',
);
sprint151Assert(
    $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $package['evidence']),
    'produced dependency evidence must satisfy Sprint150 qualifier',
);
sprint151Assert(count($package['qualification']['component_evidence_sha256']) === 9, 'all nine components must qualify');
sprint151Assert(strlen($package['producer_fingerprint_sha256']) === 64, 'producer fingerprint must be SHA-256');
sprint151Assert($package['activation_authority_state'] === 'NOT_GRANTED', 'producer must not grant activation authority');
sprint151Assert($package['feature_activation_state'] === 'INACTIVE', 'producer must keep feature inactive');
sprint151Assert($package['runtime_allowlist_change'] === 'NOT_IMPLEMENTED', 'producer must not widen runtime allowlist');
sprint151Assert($package['secrets_embedded'] === false, 'producer package must be secret free');

$reordered = array_reverse($observations, true);
$reordered['components'] = array_reverse($observations['components'], true);
$reorderedPackage = $producer->produce($selection, $attestation, $capabilityEvidence, $reordered);
sprint151Assert(
    $reorderedPackage['producer_fingerprint_sha256'] === $package['producer_fingerprint_sha256'],
    'producer fingerprint must be key-order independent',
);
sprint151Assert($reorderedPackage['evidence'] === $package['evidence'], 'produced evidence must be key-order independent');

$missing = $observations;
unset($missing['components']['durable_pos_sale_lifecycle']);
sprint151Assert(! $producer->canProduce($selection, $attestation, $capabilityEvidence, $missing), 'missing component observation must fail closed');

$wrongPath = $observations;
$wrongPath['components']['final_shift_close_repository']['source_path'] = 'apps/web/app/Infrastructure/Pos/UnexpectedRepository.php';
sprint151Assert(! $producer->canProduce($selection, $attestation, $capabilityEvidence, $wrongPath), 'wrong canonical source path must fail closed');

$wrongTarget = $observations;
$wrongTarget['components']['durable_persistence_transaction']['environment_id'] = 'another-durable-stage';
sprint151Assert(! $producer->canProduce($selection, $attestation, $capabilityEvidence, $wrongTarget), 'cross-target observation must fail closed');

$wrongRuntime = $observations;
$wrongRuntime['components']['first_party_session_authority_repository']['runtime_class'] = 'durable-stage-alias';
sprint151Assert(! $producer->canProduce($selection, $attestation, $capabilityEvidence, $wrongRuntime), 'cross-runtime observation must fail closed');

$badPayload = $observations;
$badPayload['components']['pos_session_context']['evidence_payload_sha256'] = str_repeat('G', 64);
sprint151Assert(! $producer->canProduce($selection, $attestation, $capabilityEvidence, $badPayload), 'malformed observation digest must fail closed');

$synthetic = $observations;
$synthetic['components']['first_party_identity_eligibility']['synthetic_dependency'] = true;
sprint151Assert(! $producer->canProduce($selection, $attestation, $capabilityEvidence, $synthetic), 'synthetic dependency observation must fail closed');

$secret = $observations;
$secret['components']['durable_role_permission_authorization']['secrets_embedded'] = true;
sprint151Assert(! $producer->canProduce($selection, $attestation, $capabilityEvidence, $secret), 'secret-bearing observation must fail closed');

$unexpected = $observations;
$unexpected['components']['final_shift_close_delivery']['token'] = 'must-never-be-accepted';
sprint151Assert(! $producer->canProduce($selection, $attestation, $capabilityEvidence, $unexpected), 'unexpected credential-like field must fail closed');

$unqualifiedCapability = $capabilityEvidence;
$unqualifiedCapability['capabilities']['verified_flag_rollback']['state'] = 'UNVERIFIED';
sprint151Assert(
    ! $producer->canProduce($selection, $attestation, $unqualifiedCapability, $observations),
    'producer must not rescue unqualified Sprint148 capability evidence',
);

$authorityDrift = $selection;
$authorityDrift['activation_authority_state'] = 'GRANTED';
sprint151Assert(
    ! $producer->canProduce($authorityDrift, $attestation, $capabilityEvidence, $observations),
    'authority drift must fail closed',
);

$threw = false;
try {
    $producer->produce($selection, $attestation, $capabilityEvidence, $wrongTarget);
} catch (InvalidArgumentException $exception) {
    $threw = str_starts_with(
        $exception->getMessage(),
        'final_shift_close_durable_runtime_dependency_envelope_evidence_producer_rejected:',
    );
}
sprint151Assert($threw, 'producer rejection must use canonical prefix');

fwrite(STDOUT, "Sprint151 Final Shift Close durable runtime dependency envelope evidence producer regression passed.\n");
