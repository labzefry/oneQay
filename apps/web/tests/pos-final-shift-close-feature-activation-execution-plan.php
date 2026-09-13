<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseDurableRuntimeDependencyEnvelope;
use App\Application\Pos\FinalShiftCloseFeatureActivationExecutionPlan;
use InvalidArgumentException;

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
    } catch (InvalidArgumentException $exception) {
        expectTrue(str_contains($exception->getMessage(), $needle), 'unexpected rejection: '.$exception->getMessage());
        return;
    }

    throw new RuntimeException('expected rejection containing: '.$needle);
}

$planner = new FinalShiftCloseFeatureActivationExecutionPlan();
$source = str_repeat('a', 40);
$artifact = str_repeat('b', 64);
$readiness = str_repeat('c', 64);
$selectionFingerprint = str_repeat('d', 64);
$dependencyEnvelope = str_repeat('e', 64);
$executorSource = str_repeat('f', 40);

$selection = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'selected_target' => [
        'environment_id' => 'durable-non-production-01',
        'runtime_class' => 'durable-staging',
        'exact_running_source_commit' => $source,
        'exact_running_artifact_sha256' => $artifact,
        'readiness_attestation_sha256' => $readiness,
    ],
    'selection_fingerprint_sha256' => $selectionFingerprint,
    'activation_authority_state' => 'NOT_GRANTED',
    'feature_activation_state' => 'INACTIVE',
    'runtime_allowlist_change' => 'NOT_IMPLEMENTED',
];
$targetBinding = $planner->targetBindingSha256($selection);

$state = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'migration27' => ['state' => 'EXECUTED'],
    'permission_provisioning' => [
        'state' => 'PROVISIONED',
        'permission_id' => 'pos.shift.close',
        'default_grant' => 'NONE',
    ],
    'feature_activation' => [
        'state' => 'INACTIVE',
        'authority_context' => 'final-shift-close-feature-activation-authority',
        'runtime_flag' => 'ONEQAY_POS_SHIFT_CLOSE_ENABLED',
    ],
];

$dependency = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'qualification_state' => FinalShiftCloseDurableRuntimeDependencyEnvelope::QUALIFICATION_STATE,
    'environment_id' => 'durable-non-production-01',
    'runtime_class' => 'durable-staging',
    'target_binding_sha256' => $targetBinding,
    'dependency_envelope_sha256' => $dependencyEnvelope,
    'activation_authority_state' => 'NOT_GRANTED',
    'feature_activation_state' => 'INACTIVE',
];

$authority = [
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'authority_state' => 'GRANTED',
    'authority_context' => 'final-shift-close-feature-activation-authority',
    'environment_id' => 'durable-non-production-01',
    'runtime_class' => 'durable-staging',
    'exact_running_source_commit' => $source,
    'exact_running_artifact_sha256' => $artifact,
    'readiness_attestation_sha256' => $readiness,
    'selection_fingerprint_sha256' => $selectionFingerprint,
    'target_binding_sha256' => $targetBinding,
    'dependency_envelope_sha256' => $dependencyEnvelope,
    'executor_source_commit' => $executorSource,
    'secrets_embedded' => false,
];

$plan = $planner->qualify($state, $selection, $dependency, $authority);
expectTrue($plan['plan_state'] === 'TARGET_BOUND_FEATURE_ACTIVATION_EXECUTION_PLAN_SOURCE_ONLY', 'plan state mismatch');
expectTrue($plan['target_binding_sha256'] === $targetBinding, 'target binding mismatch');
expectTrue($plan['dependency_envelope_sha256'] === $dependencyEnvelope, 'dependency envelope mismatch');
expectTrue($plan['runtime_flag'] === 'ONEQAY_POS_SHIFT_CLOSE_ENABLED', 'runtime flag mismatch');
expectTrue($plan['required_pre_activation_value'] === false, 'pre-activation value must be false');
expectTrue($plan['desired_activation_value'] === true, 'desired activation value must be true');
expectTrue($plan['rollback_value'] === false, 'rollback value must be false');
expectTrue($plan['ordered_steps'] === [
    'READ_FLAG_BEFORE',
    'WRITE_FLAG_TRUE',
    'READ_FLAG_AFTER_REQUIRE_TRUE',
    'NON_MUTATING_HEALTH_ATTESTATION',
    'ON_ANY_POST_WRITE_FAILURE_WRITE_FLAG_FALSE',
    'VERIFY_ROLLBACK_READBACK_FALSE',
], 'activation step ordering drifted');
expectTrue($plan['concrete_configuration_transport'] === 'NOT_IMPLEMENTED', 'transport must remain unimplemented');
expectTrue($plan['dispatch_state'] === 'NOT_PERFORMED', 'dispatch must remain not performed');
expectTrue($plan['runtime_allowlist_change'] === 'NOT_IMPLEMENTED', 'allowlist must remain unchanged');
expectTrue($plan['feature_activation_state'] === 'INACTIVE', 'feature must remain inactive');
expectTrue($plan['secrets_embedded'] === false, 'plan must remain secret-free');
expectTrue((bool) preg_match('/\A[0-9a-f]{64}\z/', $plan['activation_plan_sha256']), 'plan SHA-256 invalid');
expectTrue($planner->qualify($state, $selection, $dependency, $authority) === $plan, 'plan must be deterministic');

$currentCanonicalState = $state;
$currentCanonicalState['migration27']['state'] = 'NOT_EXECUTED';
$currentCanonicalState['permission_provisioning']['state'] = 'NONE';
expectRejected(
    fn () => $planner->qualify($currentCanonicalState, $selection, $dependency, $authority),
    'migration27_not_executed',
);

$driftedDependency = $dependency;
$driftedDependency['environment_id'] = 'different-environment';
expectRejected(
    fn () => $planner->qualify($state, $selection, $driftedDependency, $authority),
    'dependency_environment_mismatch',
);

$driftedAuthority = $authority;
$driftedAuthority['target_binding_sha256'] = str_repeat('0', 64);
expectRejected(
    fn () => $planner->qualify($state, $selection, $dependency, $driftedAuthority),
    'authority_target_binding_mismatch',
);

$secretBearingAuthority = $authority;
$secretBearingAuthority['secrets_embedded'] = true;
expectRejected(
    fn () => $planner->qualify($state, $selection, $dependency, $secretBearingAuthority),
    'authority_must_not_embed_secrets',
);

$unexpectedAuthority = $authority;
$unexpectedAuthority['token'] = 'forbidden';
expectRejected(
    fn () => $planner->qualify($state, $selection, $dependency, $unexpectedAuthority),
    'unexpected_authority_field:token',
);

fwrite(STDOUT, "Final Shift Close feature activation execution plan regression passed.\n");
