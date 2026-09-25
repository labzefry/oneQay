<?php

declare(strict_types=1);

require_once __DIR__.'/prepare-durable-staging-operator-deployment-plan.php';

// Author by Lab | zefry

final class DurableStagingPostActivationPlanException extends RuntimeException
{
}

/** @param array<string,mixed> $plan @return array<string,mixed> */
function dsPostActivationPromotePlan(array $plan): array
{
    if (($plan['operational_boundary']['feature_activation'] ?? null) !== 'INACTIVE') {
        throw new DurableStagingPostActivationPlanException('legacy_plan_feature_boundary_invalid');
    }

    $plan['operational_boundary']['feature_activation'] = 'ACTIVE';
    $plan['readback_expectations']['feature_activation_state'] = 'ACTIVE';
    $plan['required_evidence'][] = 'post_activation_feature_state_preserved';
    unset($plan['plan_fingerprint']);

    $fingerprint = hash('sha256', dsPlanCanonicalJson($plan));

    return ['plan_fingerprint' => $fingerprint] + $plan;
}

/** @return array<string,mixed> */
function dsPostActivationPlanPrepare(string $handoffPath, string $targetPath): array
{
    return dsPostActivationPromotePlan(dsPlanPrepare($handoffPath, $targetPath));
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 4) {
        fwrite(STDERR, "Usage: php tools/prepare-durable-staging-post-activation-operator-deployment-plan.php <handoff.json> <target.json> <output.json>\n");
        exit(64);
    }

    try {
        $plan = dsPostActivationPlanPrepare($argv[1], $argv[2]);
        dsPlanWrite($argv[3], $plan);
        fwrite(STDOUT, "durable_staging_post_activation_operator_deployment_plan_prepared\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "durable_staging_post_activation_operator_deployment_plan_failed\n");
        exit(1);
    }
}
