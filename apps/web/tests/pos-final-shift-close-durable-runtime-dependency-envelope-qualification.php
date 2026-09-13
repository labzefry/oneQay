<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseDurableRuntimeCapabilityEvidence;
use App\Application\Pos\FinalShiftCloseDurableRuntimeDependencyEnvelope;
use App\Application\Pos\FinalShiftCloseDurableRuntimeReadiness;
use App\Application\Pos\FinalShiftCloseDurableRuntimeTargetSelection;
use InvalidArgumentException;

require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeReadiness.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeTargetSelection.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeCapabilityEvidence.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeDependencyEnvelope.php';

// Author by Lab | zefry

function sprint150Assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, "Sprint150 regression failed: {$message}\n");
        exit(1);
    }
}

/** @return array<string, mixed> */
function sprint150Attestation(): array
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
function sprint150CapabilityEvidence(
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

/**
 * @param array<string, mixed> $selection
 * @param array<string, mixed> $capabilityQualification
 * @return array<string, mixed>
 */
function sprint150DependencyEvidence(array $selection, array $capabilityQualification): array
{
    $runtimeClass = $selection['selected_target']['runtime_class'];
    $binding = $capabilityQualification['target_binding_sha256'];
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
            'runtime_class' => $runtimeClass,
            'evidence_sha256' => str_repeat(dechex($counter), 64),
            'target_binding_sha256' => $binding,
            'synthetic_dependency' => false,
            'secrets_embedded' => false,
        ];
        $counter++;
    }

    return [
        'schema_version' => 1,
        'feature' => FinalShiftCloseDurableRuntimeDependencyEnvelope::FEATURE,
        'evidence_state' => FinalShiftCloseDurableRuntimeDependencyEnvelope::EVIDENCE_STATE,
        'environment_id' => $selection['selected_target']['environment_id'],
        'runtime_class' => $runtimeClass,
        'target_binding_sha256' => $binding,
        'capability_evidence_bundle_sha256' => $capabilityQualification['capability_evidence_bundle_sha256'],
        'components' => $components,
        'synthetic_mixing' => false,
        'secrets_embedded' => false,
    ];
}

$readiness = new FinalShiftCloseDurableRuntimeReadiness();
$selector = new FinalShiftCloseDurableRuntimeTargetSelection($readiness);
$capabilityQualifier = new FinalShiftCloseDurableRuntimeCapabilityEvidence($readiness, $selector);
$dependencyQualifier = new FinalShiftCloseDurableRuntimeDependencyEnvelope($capabilityQualifier);
$attestation = sprint150Attestation();
$selection = $selector->select($attestation);
$capabilityEvidence = sprint150CapabilityEvidence($capabilityQualifier, $selection);
$capabilityQualification = $capabilityQualifier->qualify($selection, $attestation, $capabilityEvidence);
$dependencyEvidence = sprint150DependencyEvidence($selection, $capabilityQualification);

sprint150Assert(
    $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $dependencyEvidence),
    'canonical nine-component dependency envelope must qualify',
);
$qualified = $dependencyQualifier->qualify($selection, $attestation, $capabilityEvidence, $dependencyEvidence);
sprint150Assert(
    $qualified['qualification_state'] === FinalShiftCloseDurableRuntimeDependencyEnvelope::QUALIFICATION_STATE,
    'qualification state mismatch',
);
sprint150Assert($qualified['runtime_class'] === 'durable-stage', 'selected runtime class must be preserved');
sprint150Assert(count($qualified['component_evidence_sha256']) === 9, 'all nine Sprint107 components must be represented');
sprint150Assert(strlen($qualified['dependency_envelope_sha256']) === 64, 'dependency envelope fingerprint must be SHA-256');
sprint150Assert($qualified['runtime_allowlist_change'] === 'NOT_IMPLEMENTED', 'qualification must not widen runtime allowlist');
sprint150Assert($qualified['activation_authority_state'] === 'NOT_GRANTED', 'qualification must not grant activation authority');
sprint150Assert($qualified['feature_activation_state'] === 'INACTIVE', 'qualification must keep feature inactive');

$reordered = $dependencyEvidence;
$reordered['components'] = array_reverse($dependencyEvidence['components'], true);
$reordered = array_reverse($reordered, true);
$reorderedQualified = $dependencyQualifier->qualify($selection, $attestation, $capabilityEvidence, $reordered);
sprint150Assert(
    $reorderedQualified['dependency_envelope_sha256'] === $qualified['dependency_envelope_sha256'],
    'dependency envelope fingerprint must be key-order independent',
);

$missing = $dependencyEvidence;
unset($missing['components']['durable_pos_sale_lifecycle']);
sprint150Assert(
    ! $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $missing),
    'missing Sprint107 component must fail closed',
);

$wrongPath = $dependencyEvidence;
$wrongPath['components']['final_shift_close_repository']['source_path'] = 'apps/web/app/Infrastructure/Pos/UnexpectedRepository.php';
sprint150Assert(
    ! $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $wrongPath),
    'wrong canonical component source path must fail closed',
);

$wrongRuntime = $dependencyEvidence;
$wrongRuntime['components']['durable_persistence_transaction']['runtime_class'] = 'durable-stage-alias';
sprint150Assert(
    ! $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $wrongRuntime),
    'cross-runtime dependency evidence must fail closed',
);

$wrongBinding = $dependencyEvidence;
$wrongBinding['components']['first_party_session_authority_repository']['target_binding_sha256'] = str_repeat('f', 64);
sprint150Assert(
    ! $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $wrongBinding),
    'cross-target dependency evidence must fail closed',
);

$syntheticMix = $dependencyEvidence;
$syntheticMix['components']['first_party_identity_eligibility']['synthetic_dependency'] = true;
sprint150Assert(
    ! $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $syntheticMix),
    'synthetic dependency mixing must fail closed',
);

$topLevelSyntheticMix = $dependencyEvidence;
$topLevelSyntheticMix['synthetic_mixing'] = true;
sprint150Assert(
    ! $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $topLevelSyntheticMix),
    'aggregate synthetic mixing must fail closed',
);

$malformedDigest = $dependencyEvidence;
$malformedDigest['components']['pos_session_context']['evidence_sha256'] = str_repeat('G', 64);
sprint150Assert(
    ! $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $malformedDigest),
    'malformed component evidence digest must fail closed',
);

$secretBearing = $dependencyEvidence;
$secretBearing['components']['durable_role_permission_authorization']['secrets_embedded'] = true;
sprint150Assert(
    ! $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $secretBearing),
    'secret-bearing dependency evidence must fail closed',
);

$wrongCapabilityBinding = $dependencyEvidence;
$wrongCapabilityBinding['capability_evidence_bundle_sha256'] = str_repeat('e', 64);
sprint150Assert(
    ! $dependencyQualifier->qualifies($selection, $attestation, $capabilityEvidence, $wrongCapabilityBinding),
    'dependency evidence must bind exact Sprint148 capability bundle',
);

$unqualifiedCapabilityEvidence = $capabilityEvidence;
$unqualifiedCapabilityEvidence['capabilities']['verified_flag_rollback']['state'] = 'UNVERIFIED';
sprint150Assert(
    ! $dependencyQualifier->qualifies($selection, $attestation, $unqualifiedCapabilityEvidence, $dependencyEvidence),
    'dependency qualification must not rescue unqualified capability evidence',
);

$authorityDrift = $selection;
$authorityDrift['activation_authority_state'] = 'GRANTED';
sprint150Assert(
    ! $dependencyQualifier->qualifies($authorityDrift, $attestation, $capabilityEvidence, $dependencyEvidence),
    'dependency qualification must reject activation-authority drift',
);

$threw = false;
try {
    $dependencyQualifier->qualify($selection, $attestation, $capabilityEvidence, $wrongPath);
} catch (InvalidArgumentException $exception) {
    $threw = str_starts_with(
        $exception->getMessage(),
        'final_shift_close_durable_runtime_dependency_envelope_rejected:',
    );
}
sprint150Assert($threw, 'require qualification path must reject with canonical prefix');

fwrite(STDOUT, "Sprint150 Final Shift Close durable runtime dependency envelope qualification regression passed.\n");
