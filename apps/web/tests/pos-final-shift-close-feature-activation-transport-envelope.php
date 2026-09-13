<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseDurableRuntimeCapabilityEvidence;
use App\Application\Pos\FinalShiftCloseFeatureActivationExecutionPlan;
use App\Application\Pos\FinalShiftCloseFeatureActivationTransportEnvelope;

require dirname(__DIR__).'/vendor/autoload.php';

// Author by Lab | zefry

function expectTrue(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function expectRejected(callable $operation, string $needle): void
{
    try {
        $operation();
    } catch (\InvalidArgumentException $exception) {
        expectTrue(str_contains($exception->getMessage(), $needle), 'unexpected rejection: '.$exception->getMessage());
        return;
    }

    throw new RuntimeException('expected rejection containing: '.$needle);
}

$builder = new FinalShiftCloseFeatureActivationTransportEnvelope();
$source = str_repeat('a', 40);
$artifact = str_repeat('b', 64);
$readiness = str_repeat('c', 64);
$selectionFingerprint = str_repeat('d', 64);
$targetBinding = str_repeat('e', 64);

$activationPlan = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'plan_state' => FinalShiftCloseFeatureActivationExecutionPlan::PLAN_STATE,
    'environment_id' => 'durable-non-production-01',
    'runtime_class' => 'durable-staging',
    'exact_running_source_commit' => $source,
    'exact_running_artifact_sha256' => $artifact,
    'readiness_attestation_sha256' => $readiness,
    'selection_fingerprint_sha256' => $selectionFingerprint,
    'target_binding_sha256' => $targetBinding,
    'dependency_envelope_sha256' => str_repeat('f', 64),
    'activation_authority_sha256' => str_repeat('1', 64),
    'executor_source_commit' => str_repeat('2', 40),
    'runtime_flag' => 'ONEQAY_POS_SHIFT_CLOSE_ENABLED',
    'required_pre_activation_value' => false,
    'desired_activation_value' => true,
    'rollback_value' => false,
    'ordered_steps' => [
        'READ_FLAG_BEFORE',
        'WRITE_FLAG_TRUE',
        'READ_FLAG_AFTER_REQUIRE_TRUE',
        'NON_MUTATING_HEALTH_ATTESTATION',
        'ON_ANY_POST_WRITE_FAILURE_WRITE_FLAG_FALSE',
        'VERIFY_ROLLBACK_READBACK_FALSE',
    ],
    'concrete_configuration_transport' => 'NOT_IMPLEMENTED',
    'dispatch_state' => 'NOT_PERFORMED',
    'runtime_allowlist_change' => 'NOT_IMPLEMENTED',
    'feature_activation_state' => 'INACTIVE',
    'secrets_embedded' => false,
    'activation_plan_sha256' => str_repeat('3', 64),
];

$qualification = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'qualification_state' => FinalShiftCloseDurableRuntimeCapabilityEvidence::QUALIFICATION_STATE,
    'selected_target' => [
        'environment_id' => 'durable-non-production-01',
        'runtime_class' => 'durable-staging',
        'exact_running_source_commit' => $source,
        'exact_running_artifact_sha256' => $artifact,
        'readiness_attestation_sha256' => $readiness,
    ],
    'selection_fingerprint_sha256' => $selectionFingerprint,
    'target_binding_sha256' => $targetBinding,
    'capability_evidence_sha256' => [
        'authenticated_configuration_mutation_channel' => str_repeat('4', 64),
        'read_before_write_read_after' => str_repeat('5', 64),
        'non_mutating_health_attestation' => str_repeat('6', 64),
        'verified_flag_rollback' => str_repeat('7', 64),
    ],
    'capability_evidence_bundle_sha256' => str_repeat('8', 64),
    'activation_authority_state' => 'NOT_GRANTED',
    'feature_activation_state' => 'INACTIVE',
    'runtime_allowlist_change' => 'NOT_IMPLEMENTED',
];

$envelope = $builder->build($activationPlan, $qualification);
expectTrue($envelope['transport_state'] === 'TARGET_BOUND_FEATURE_ACTIVATION_TRANSPORT_ENVELOPE_SOURCE_ONLY', 'transport state mismatch');
expectTrue($envelope['target_binding_sha256'] === $targetBinding, 'target binding mismatch');
expectTrue($envelope['concrete_adapter'] === 'NOT_IMPLEMENTED', 'adapter boundary drifted');
expectTrue($envelope['network_dispatch'] === 'NOT_PERFORMED', 'dispatch boundary drifted');
expectTrue($envelope['feature_activation_state'] === 'INACTIVE', 'feature boundary drifted');
expectTrue($builder->build($activationPlan, $qualification) === $envelope, 'transport envelope must be deterministic');
expectTrue((bool) preg_match('/\A[0-9a-f]{64}\z/', $envelope['transport_envelope_sha256']), 'transport envelope digest invalid');

$targetDrift = $qualification;
$targetDrift['selected_target']['environment_id'] = 'different-environment';
expectRejected(fn () => $builder->build($activationPlan, $targetDrift), 'capability_selected_target_mismatch:environment_id');

$bindingDrift = $qualification;
$bindingDrift['target_binding_sha256'] = str_repeat('9', 64);
expectRejected(fn () => $builder->build($activationPlan, $bindingDrift), 'capability_target_binding_mismatch');

$missingCapability = $qualification;
unset($missingCapability['capability_evidence_sha256']['verified_flag_rollback']);
expectRejected(fn () => $builder->build($activationPlan, $missingCapability), 'capability_digest_set_invalid');

fwrite(STDOUT, "Final Shift Close feature activation transport envelope regression passed.\n");
