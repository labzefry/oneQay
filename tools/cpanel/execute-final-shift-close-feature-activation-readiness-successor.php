<?php

declare(strict_types=1);

// Author by Lab | zefry

require_once __DIR__.'/execute-final-shift-close-feature-activation.php';

/** @return array<string,mixed> */
function fscActivationMapCanonicalDurableStagingReadiness(array $readiness): array
{
    fscActivationLiteral($readiness['schema_version'] ?? null, 1, 'readiness_schema_invalid');
    fscActivationLiteral($readiness['runtime_model'] ?? null, 'NON_SYNTHETIC_DURABLE_RUNTIME', 'readiness_runtime_model_invalid');
    fscActivationLiteral($readiness['environment_isolation'] ?? null, 'ISOLATED_NON_PRODUCTION', 'readiness_environment_isolation_invalid');
    fscActivationBool($readiness['serving_application_runtime'] ?? null, true, 'readiness_serving_runtime_invalid');
    fscActivationBool($readiness['synthetic_fixture_runtime'] ?? null, false, 'readiness_synthetic_runtime_invalid');
    fscActivationBool($readiness['production_traffic_served'] ?? null, false, 'readiness_production_traffic_invalid');
    fscActivationBool($readiness['durable_persistence_enabled'] ?? null, true, 'readiness_persistence_invalid');
    fscActivationBool($readiness['durable_session_control_enabled'] ?? null, true, 'readiness_session_control_invalid');
    fscActivationBool($readiness['durable_authorization_enabled'] ?? null, true, 'readiness_authorization_invalid');
    fscActivationBool($readiness['durable_transaction_boundary_enabled'] ?? null, true, 'readiness_transaction_boundary_invalid');
    fscActivationBool($readiness['durable_pos_persistence_enabled'] ?? null, true, 'readiness_pos_persistence_invalid');
    fscActivationLiteral(
        $readiness['activation_authority_binding'] ?? null,
        'SEPARATE_EXPLICIT_ACTIVATION_AUTHORITY',
        'readiness_activation_authority_binding_invalid',
    );
    fscActivationLiteral($readiness['feature_activation_state'] ?? null, 'ACTIVE', 'readiness_feature_activation_state_invalid');
    fscActivationBool($readiness['secrets_embedded'] ?? null, false, 'readiness_secrets_boundary_invalid');

    $source = fscActivationHex(
        $readiness['exact_running_source_commit'] ?? null,
        40,
        'readiness_exact_source_invalid',
    );
    $artifact = fscActivationHex(
        $readiness['exact_running_artifact_sha256'] ?? null,
        64,
        'readiness_exact_artifact_invalid',
    );
    $environment = fscActivationString(
        $readiness['environment_id'] ?? null,
        '/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D',
        'readiness_environment_invalid',
    );
    $runtime = fscActivationString(
        $readiness['runtime_class'] ?? null,
        '/\Adurable-staging\z/D',
        'readiness_runtime_class_invalid',
    );

    return [
        'environment_id' => $environment,
        'runtime_class' => $runtime,
        'running_source_commit' => $source,
        'running_artifact_sha256' => $artifact,
        'durable_staging_runtime_enabled' => true,
        'production_data_allowed' => false,
    ];
}

/** @return array<string,mixed> */
function fscActivationFetchCanonicalDurableStagingReadiness(string $url, string $token): array
{
    return fscActivationMapCanonicalDurableStagingReadiness(
        fscActivationFetchReadiness($url, $token),
    );
}

/** @return array<string,mixed> */
function fscActivationExecuteReadinessSuccessor(
    string $transportEnvelopePath,
    string $authorityPath,
    string $runtimeManifestPath,
    string $runtimeEnvPath,
    string $approvalTokenPath,
    string $readinessUrl,
    string $outputPath,
): array {
    return fscActivationExecute(
        $transportEnvelopePath,
        $authorityPath,
        $runtimeManifestPath,
        $runtimeEnvPath,
        $approvalTokenPath,
        $readinessUrl,
        $outputPath,
        'fscActivationFetchCanonicalDurableStagingReadiness',
    );
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 8) {
        fwrite(
            STDERR,
            "Usage: php tools/cpanel/execute-final-shift-close-feature-activation-readiness-successor.php <transport-envelope.json> <private-authority.json> <runtime-manifest.json> <private-runtime-env> <private-approval-token-file> <https-readiness-url> <execution-evidence.json>\n",
        );
        exit(64);
    }

    try {
        $receipt = fscActivationExecuteReadinessSuccessor(
            $argv[1],
            $argv[2],
            $argv[3],
            $argv[4],
            $argv[5],
            $argv[6],
            $argv[7],
        );
        fwrite(STDOUT, 'RESULT=SUCCESS'.PHP_EOL);
        fwrite(STDOUT, 'EXECUTION_STATE='.(string) $receipt['execution_state'].PHP_EOL);
        fwrite(STDOUT, 'RECEIPT_SHA256='.(string) $receipt['receipt_sha256'].PHP_EOL);
        exit(0);
    } catch (Throwable $exception) {
        fwrite(STDERR, 'RESULT=FAILED'.PHP_EOL);
        fwrite(STDERR, 'ERROR_CODE='.$exception->getMessage().PHP_EOL);
        exit(1);
    }
}
