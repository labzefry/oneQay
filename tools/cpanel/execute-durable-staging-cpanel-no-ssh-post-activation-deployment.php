<?php

declare(strict_types=1);

require_once __DIR__.'/execute-durable-staging-cpanel-no-ssh-deployment.php';

// Author by Lab | zefry

final class CpanelPostActivationDeploymentException extends RuntimeException
{
}

/** @param array<string,mixed> $attestation @return array<string,mixed> */
function cpanelPostActivationNormalizeReadiness(array $attestation): array
{
    if (($attestation['feature_activation_state'] ?? null) !== 'ACTIVE') {
        throw new CpanelPostActivationDeploymentException('post_activation_readiness_feature_state_invalid');
    }

    $legacy = $attestation;
    $legacy['feature_activation_state'] = 'INACTIVE';

    return $legacy;
}

/** @param array<string,mixed> $evidence @return array<string,mixed> */
function cpanelPostActivationPromoteEvidence(array $evidence): array
{
    if (($evidence['operational_boundary']['feature_activation'] ?? null) !== 'INACTIVE') {
        throw new CpanelPostActivationDeploymentException('legacy_evidence_feature_boundary_invalid');
    }

    $evidence['operational_boundary']['feature_activation'] = 'ACTIVE';

    return $evidence;
}

/** @return array<string,mixed> */
function cpanelPostActivationExecute(
    string $planPath,
    string $profilePath,
    string $archivePath,
    string $bindingsPath,
    string $runtimeEnvPath,
    string $readinessUrl,
    string $evidencePath,
): array {
    $plan = cpanelExecLoadJson($planPath);
    cpanelExecLiteral(
        $plan['operational_boundary']['feature_activation'] ?? null,
        'ACTIVE',
        'post_activation_plan_feature_state_invalid',
    );
    cpanelExecLiteral(
        $plan['readback_expectations']['feature_activation_state'] ?? null,
        'ACTIVE',
        'post_activation_plan_readback_feature_state_invalid',
    );

    $evidence = cpanelExecExecute(
        $planPath,
        $profilePath,
        $archivePath,
        $bindingsPath,
        $runtimeEnvPath,
        $readinessUrl,
        $evidencePath,
        static function (string $url, string $token): array {
            $raw = cpanelExecFetchReadiness($url, $token);
            return cpanelPostActivationNormalizeReadiness($raw);
        },
    );

    $evidence = cpanelPostActivationPromoteEvidence($evidence);
    cpanelExecWriteJson($evidencePath, $evidence);

    return $evidence;
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 8) {
        fwrite(
            STDERR,
            "Usage: php tools/cpanel/execute-durable-staging-cpanel-no-ssh-post-activation-deployment.php <deployment-plan.json> <target-profile.json> <artifact.tar.gz> <private-bindings.json> <private-runtime-env> <readiness-url> <deployment-evidence.json>\n",
        );
        exit(64);
    }

    try {
        cpanelPostActivationExecute($argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6], $argv[7]);
        fwrite(STDOUT, "cpanel_no_ssh_durable_staging_post_activation_deployed_verified_not_selected\n");
        exit(0);
    } catch (Throwable $failure) {
        fwrite(STDERR, "cpanel_no_ssh_post_activation_deployment_execution_failed\n");
        exit(1);
    }
}
