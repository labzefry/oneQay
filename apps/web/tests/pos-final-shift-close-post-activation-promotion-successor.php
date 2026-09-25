<?php

declare(strict_types=1);

// Author by Lab | zefry

require_once dirname(__DIR__, 3).'/tools/prepare-durable-staging-post-activation-operator-deployment-plan.php';
require_once dirname(__DIR__, 3).'/tools/cpanel/execute-durable-staging-cpanel-no-ssh-post-activation-deployment.php';

$legacyPlan = [
    'schema_version' => 1,
    'operational_boundary' => ['feature_activation' => 'INACTIVE'],
    'readback_expectations' => [],
    'required_evidence' => [],
    'attribution' => 'Lab | zefry',
];
$promotedPlan = dsPostActivationPromotePlan($legacyPlan);
if (($promotedPlan['operational_boundary']['feature_activation'] ?? null) !== 'ACTIVE'
    || ($promotedPlan['readback_expectations']['feature_activation_state'] ?? null) !== 'ACTIVE'
    || !in_array('post_activation_feature_state_preserved', $promotedPlan['required_evidence'] ?? [], true)
    || preg_match('/\A[0-9a-f]{64}\z/', (string) ($promotedPlan['plan_fingerprint'] ?? '')) !== 1
) {
    fwrite(STDERR, "POST_ACTIVATION_PLAN_SUCCESSOR_FAILED\n");
    exit(1);
}

$readiness = ['feature_activation_state' => 'ACTIVE'];
$legacyReadiness = cpanelPostActivationNormalizeReadiness($readiness);
if (($legacyReadiness['feature_activation_state'] ?? null) !== 'INACTIVE') {
    fwrite(STDERR, "POST_ACTIVATION_READINESS_SUCCESSOR_FAILED\n");
    exit(1);
}

$evidence = ['operational_boundary' => ['feature_activation' => 'INACTIVE']];
$promotedEvidence = cpanelPostActivationPromoteEvidence($evidence);
if (($promotedEvidence['operational_boundary']['feature_activation'] ?? null) !== 'ACTIVE') {
    fwrite(STDERR, "POST_ACTIVATION_EVIDENCE_SUCCESSOR_FAILED\n");
    exit(1);
}

try {
    cpanelPostActivationNormalizeReadiness(['feature_activation_state' => 'INACTIVE']);
    fwrite(STDERR, "POST_ACTIVATION_FAIL_CLOSED_MISSING\n");
    exit(1);
} catch (CpanelPostActivationDeploymentException) {
}

echo "POST_ACTIVATION_PROMOTION_SUCCESSOR_OK\n";
