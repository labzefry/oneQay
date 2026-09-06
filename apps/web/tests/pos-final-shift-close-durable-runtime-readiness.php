<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseDurableRuntimeReadiness;
use InvalidArgumentException;

require_once __DIR__.'/../app/Application/Pos/FinalShiftCloseDurableRuntimeReadiness.php';

// Author by Lab | zefry

function sprint110Assert(bool $condition, string $message): void
{
    if (! $condition) {
        fwrite(STDERR, "Sprint110 regression failed: {$message}\n");
        exit(1);
    }
}

/** @return array<string, mixed> */
function sprint110ValidAttestation(): array
{
    return [
        'schema_version' => 1,
        'environment_id' => 'oneqay-durable-stage-01',
        'runtime_class' => 'durable-staging',
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

/** @param array<string, mixed> $attestation */
function sprint110With(array $attestation, string $field, mixed $value): array
{
    $attestation[$field] = $value;

    return $attestation;
}

$validator = new FinalShiftCloseDurableRuntimeReadiness();
$valid = sprint110ValidAttestation();

sprint110Assert($validator->qualifies($valid), 'canonical future durable target shape must qualify');
sprint110Assert($validator->violations($valid) === [], 'qualified target must have no violations');
$validator->requireQualified($valid);

foreach (['local', 'test', 'testing', 'ci', 'preview', 'synthetic-preview', 'production', 'prod'] as $runtimeClass) {
    sprint110Assert(
        ! $validator->qualifies(sprint110With($valid, 'runtime_class', $runtimeClass)),
        "runtime class {$runtimeClass} must be rejected",
    );
}

sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'runtime_model', 'SYNTHETIC_FIXTURE_RUNTIME')),
    'synthetic runtime model must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'synthetic_fixture_runtime', true)),
    'synthetic fixture runtime must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'production_traffic_served', true)),
    'production traffic must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'serving_application_runtime', false)),
    'non-serving foundation must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'durable_persistence_enabled', false)),
    'non-durable persistence must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'durable_session_control_enabled', false)),
    'missing durable session control must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'durable_authorization_enabled', false)),
    'missing durable authorization must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'durable_transaction_boundary_enabled', false)),
    'missing durable transaction boundary must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'durable_pos_persistence_enabled', false)),
    'missing durable POS persistence must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'authenticated_configuration_mutation_channel', false)),
    'unauthenticated configuration mutation must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'read_before_write_read_after_supported', false)),
    'configuration verification gap must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'non_mutating_health_attestation_supported', false)),
    'missing non-mutating health attestation must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'verified_flag_rollback_supported', false)),
    'missing verified rollback must be rejected',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'feature_activation_state', 'ACTIVE')),
    'target readiness must not activate Final Shift Close',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'secrets_embedded', true)),
    'attestation must remain secret-free',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'exact_running_source_commit', str_repeat('A', 40))),
    'source commit must use canonical lowercase hex',
);
sprint110Assert(
    ! $validator->qualifies(sprint110With($valid, 'exact_running_artifact_sha256', str_repeat('g', 64))),
    'artifact digest must be valid lowercase hex',
);

$missing = $valid;
unset($missing['verified_flag_rollback_supported']);
sprint110Assert(! $validator->qualifies($missing), 'missing required field must be rejected');

$unexpected = $valid;
$unexpected['credential'] = 'must-not-exist';
sprint110Assert(! $validator->qualifies($unexpected), 'unexpected attestation field must be rejected');

$threw = false;
try {
    $validator->requireQualified(sprint110With($valid, 'runtime_class', 'ci'));
} catch (InvalidArgumentException $exception) {
    $threw = str_starts_with(
        $exception->getMessage(),
        'final_shift_close_durable_runtime_readiness_rejected:',
    );
}
sprint110Assert($threw, 'requireQualified must fail closed with canonical rejection prefix');

fwrite(STDOUT, "Sprint110 Final Shift Close durable runtime readiness regression passed.\n");
