<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseDurableRuntimeAttestationIngestion;
use App\Application\Pos\FinalShiftCloseDurableRuntimeAttestationProvenance;
use App\Application\Pos\FinalShiftCloseDurableRuntimeReadiness;

require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeReadiness.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeAttestationProvenance.php';
require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeAttestationIngestion.php';

// Author by Lab | zefry

function sprint112Assert(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

/** @return array<string, mixed> */
function sprint112ValidAttestation(): array
{
    return [
        'schema_version' => 1,
        'environment_id' => 'durable-stage-01',
        'runtime_class' => 'durable-stage',
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
        'exact_running_source_commit' => str_repeat('a', 40),
        'exact_running_artifact_sha256' => str_repeat('b', 64),
        'authenticated_configuration_mutation_channel' => true,
        'read_before_write_read_after_supported' => true,
        'non_mutating_health_attestation_supported' => true,
        'verified_flag_rollback_supported' => true,
        'activation_authority_binding' => 'SEPARATE_EXPLICIT_ACTIVATION_AUTHORITY',
        'feature_activation_state' => 'INACTIVE',
        'secrets_embedded' => false,
    ];
}

/**
 * @param array<string, mixed> $attestation
 * @return array<string, mixed>
 */
function sprint112ValidProvenance(
    array $attestation,
    FinalShiftCloseDurableRuntimeAttestationProvenance $validator,
): array {
    return [
        'schema_version' => 1,
        'feature' => 'final-shift-close',
        'evidence_source' => 'GITHUB_ACTIONS_PROTECTED_ENVIRONMENT',
        'evidence_state' => 'VERIFIED',
        'producer_repository' => 'labzefry/oneQay',
        'producer_workflow_path' => '.github/workflows/final-shift-close-durable-runtime-attestation.yml',
        'producer_environment' => 'final-shift-close-durable-runtime-attestation',
        'producer_ref' => 'refs/heads/main',
        'producer_source_commit' => str_repeat('c', 40),
        'producer_run_id' => 123456789,
        'producer_run_attempt' => 1,
        'attestation_sha256' => $validator->attestationSha256($attestation),
        'environment_id' => $attestation['environment_id'],
        'runtime_class' => $attestation['runtime_class'],
        'exact_running_source_commit' => $attestation['exact_running_source_commit'],
        'exact_running_artifact_sha256' => $attestation['exact_running_artifact_sha256'],
        'deployment_evidence_sha256' => str_repeat('d', 64),
        'deployment_plan_fingerprint' => str_repeat('e', 64),
        'deployment_authority_sha256' => str_repeat('f', 64),
        'evidence_status_context' => 'final-shift-close-durable-runtime-attestation-evidence',
        'evidence_status_state' => 'success',
        'secrets_embedded' => false,
    ];
}

$readiness = new FinalShiftCloseDurableRuntimeReadiness();
$provenanceValidator = new FinalShiftCloseDurableRuntimeAttestationProvenance();
$ingestion = new FinalShiftCloseDurableRuntimeAttestationIngestion($readiness, $provenanceValidator);

$attestation = sprint112ValidAttestation();
$provenance = sprint112ValidProvenance($attestation, $provenanceValidator);

sprint112Assert($readiness->qualifies($attestation), 'Sprint110-compatible readiness attestation must qualify.');
sprint112Assert(
    $provenanceValidator->qualifies($attestation, $provenance),
    'Trusted provenance envelope must qualify.',
);

$first = $ingestion->ingest($attestation, $provenance);
$second = $ingestion->ingest(array_reverse($attestation, true), array_reverse($provenance, true));

sprint112Assert($first === $second, 'Ingestion must be deterministic and key-order independent.');
sprint112Assert($first['ingestion_state'] === 'ACCEPTED_NOT_SELECTED', 'Ingestion must not select a target.');
sprint112Assert(
    $first['canonical_selection_state'] === 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET',
    'Canonical selection must remain blocked.',
);
sprint112Assert($first['selected_target'] === null, 'Canonical selected target must remain null.');
sprint112Assert($first['activation_authority_state'] === 'NOT_GRANTED', 'Ingestion must not grant activation authority.');
sprint112Assert($first['feature_activation_state'] === 'INACTIVE', 'Feature must remain inactive.');
sprint112Assert($first['runtime_allowlist_change'] === 'NOT_IMPLEMENTED', 'Runtime allowlist must remain unchanged.');
sprint112Assert($first['persistence_state'] === 'NOT_PERFORMED', 'Ingestion record must remain unpersisted.');
sprint112Assert(
    $first['readiness_attestation_sha256'] === $provenance['attestation_sha256'],
    'Ingestion must bind the exact readiness attestation digest.',
);
sprint112Assert(
    preg_match('/\A[0-9a-f]{64}\z/', (string) $first['provenance_sha256']) === 1,
    'Provenance digest must be lowercase SHA-256.',
);
sprint112Assert(
    $first['deployment_binding']['deployment_evidence_sha256'] === $provenance['deployment_evidence_sha256'],
    'Ingestion must carry exact deployment evidence digest.',
);
sprint112Assert(
    $first['deployment_binding']['deployment_plan_fingerprint'] === $provenance['deployment_plan_fingerprint'],
    'Ingestion must carry exact deployment plan fingerprint.',
);
sprint112Assert(
    $first['deployment_binding']['deployment_authority_sha256'] === $provenance['deployment_authority_sha256'],
    'Ingestion must carry exact deployment authority digest.',
);
sprint112Assert(
    preg_match('/\A[0-9a-f]{64}\z/', (string) $first['ingestion_fingerprint_sha256']) === 1,
    'Ingestion fingerprint must be lowercase SHA-256.',
);

$cases = [];

$wrongDigest = $provenance;
$wrongDigest['attestation_sha256'] = str_repeat('d', 64);
$cases['attestation digest drift'] = [$attestation, $wrongDigest, 'attestation_sha256_mismatch'];

$wrongWorkflow = $provenance;
$wrongWorkflow['producer_workflow_path'] = '.github/workflows/untrusted.yml';
$cases['wrong producer workflow'] = [$attestation, $wrongWorkflow, 'producer_workflow_path_invalid'];

$wrongEnvironment = $provenance;
$wrongEnvironment['producer_environment'] = 'unprotected-environment';
$cases['wrong protected environment'] = [$attestation, $wrongEnvironment, 'producer_environment_invalid'];

$wrongRepository = $provenance;
$wrongRepository['producer_repository'] = 'example/fork';
$cases['wrong producer repository'] = [$attestation, $wrongRepository, 'producer_repository_invalid'];

$wrongRef = $provenance;
$wrongRef['producer_ref'] = 'refs/heads/feature';
$cases['wrong producer ref'] = [$attestation, $wrongRef, 'producer_ref_invalid'];

$wrongStatus = $provenance;
$wrongStatus['evidence_status_state'] = 'pending';
$cases['non-success evidence'] = [$attestation, $wrongStatus, 'evidence_status_state_invalid'];

$wrongContext = $provenance;
$wrongContext['evidence_status_context'] = 'generic-attestation';
$cases['wrong evidence context'] = [$attestation, $wrongContext, 'evidence_status_context_invalid'];

$wrongRuntimeBinding = $provenance;
$wrongRuntimeBinding['runtime_class'] = 'different-stage';
$cases['runtime binding drift'] = [$attestation, $wrongRuntimeBinding, 'attestation_binding_mismatch:runtime_class'];

$secretBearing = $provenance;
$secretBearing['secrets_embedded'] = true;
$cases['secret-bearing provenance'] = [$attestation, $secretBearing, 'provenance_must_not_embed_secrets'];

$badRun = $provenance;
$badRun['producer_run_id'] = 0;
$cases['invalid run id'] = [$attestation, $badRun, 'producer_run_id_invalid'];

$missingDeploymentEvidence = $provenance;
unset($missingDeploymentEvidence['deployment_evidence_sha256']);
$cases['missing deployment evidence binding'] = [
    $attestation,
    $missingDeploymentEvidence,
    'missing_field:deployment_evidence_sha256',
];

$badDeploymentEvidence = $provenance;
$badDeploymentEvidence['deployment_evidence_sha256'] = str_repeat('0', 64);
$cases['invalid deployment evidence binding'] = [
    $attestation,
    $badDeploymentEvidence,
    'deployment_binding_invalid:deployment_evidence_sha256',
];

$badDeploymentPlan = $provenance;
$badDeploymentPlan['deployment_plan_fingerprint'] = 'not-a-sha256';
$cases['invalid deployment plan binding'] = [
    $attestation,
    $badDeploymentPlan,
    'deployment_binding_invalid:deployment_plan_fingerprint',
];

$badDeploymentAuthority = $provenance;
$badDeploymentAuthority['deployment_authority_sha256'] = str_repeat('0', 64);
$cases['invalid deployment authority binding'] = [
    $attestation,
    $badDeploymentAuthority,
    'deployment_binding_invalid:deployment_authority_sha256',
];

foreach ($cases as $name => [$caseAttestation, $caseProvenance, $expectedViolation]) {
    $violations = $provenanceValidator->violations($caseAttestation, $caseProvenance);
    sprint112Assert(
        in_array($expectedViolation, $violations, true),
        'Expected fail-closed provenance violation for '.$name.': '.$expectedViolation,
    );

    $thrown = false;
    try {
        $ingestion->ingest($caseAttestation, $caseProvenance);
    } catch (InvalidArgumentException $exception) {
        $thrown = str_contains($exception->getMessage(), $expectedViolation);
    }
    sprint112Assert($thrown, 'Ingestion must fail closed for '.$name.'.');
}

$invalidReadiness = $attestation;
$invalidReadiness['production_traffic_served'] = true;
$invalidProvenance = sprint112ValidProvenance($invalidReadiness, $provenanceValidator);
$readinessRejected = false;
try {
    $ingestion->ingest($invalidReadiness, $invalidProvenance);
} catch (InvalidArgumentException $exception) {
    $readinessRejected = str_contains($exception->getMessage(), 'production_traffic_forbidden');
}
sprint112Assert($readinessRejected, 'Sprint112 ingestion must preserve Sprint110 readiness fail-closed semantics.');

$artifactDrift = $attestation;
$artifactDrift['exact_running_artifact_sha256'] = str_repeat('e', 64);
$artifactDriftProvenance = sprint112ValidProvenance($artifactDrift, $provenanceValidator);
$artifactDriftResult = $ingestion->ingest($artifactDrift, $artifactDriftProvenance);
sprint112Assert(
    $artifactDriftResult['ingestion_fingerprint_sha256'] !== $first['ingestion_fingerprint_sha256'],
    'Artifact provenance drift must change the ingestion fingerprint.',
);

$deploymentDrift = $provenance;
$deploymentDrift['deployment_evidence_sha256'] = str_repeat('1', 64);
$deploymentDriftResult = $ingestion->ingest($attestation, $deploymentDrift);
sprint112Assert(
    $deploymentDriftResult['ingestion_fingerprint_sha256'] !== $first['ingestion_fingerprint_sha256'],
    'Deployment evidence drift must change the ingestion fingerprint.',
);
sprint112Assert(
    $deploymentDriftResult['deployment_binding']['deployment_evidence_sha256'] === str_repeat('1', 64),
    'Deployment evidence binding must be carried exactly into ingestion output.',
);

echo "Sprint112 durable runtime attestation ingestion regression: PASS\n";
