<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final class FinalShiftCloseDurableRuntimeReadiness
{
    public const RUNTIME_MODEL = 'NON_SYNTHETIC_DURABLE_RUNTIME';
    public const ENVIRONMENT_ISOLATION = 'ISOLATED_NON_PRODUCTION';
    public const ACTIVATION_AUTHORITY_BINDING = 'SEPARATE_EXPLICIT_ACTIVATION_AUTHORITY';
    public const FEATURE_ACTIVATION_STATE = 'INACTIVE';

    private const DISALLOWED_RUNTIME_CLASSES = [
        'local',
        'test',
        'testing',
        'ci',
        'preview',
        'synthetic-preview',
        'production',
        'prod',
    ];

    private const REQUIRED_FIELDS = [
        'schema_version',
        'environment_id',
        'runtime_class',
        'runtime_model',
        'environment_isolation',
        'serving_application_runtime',
        'synthetic_fixture_runtime',
        'production_traffic_served',
        'durable_persistence_enabled',
        'durable_session_control_enabled',
        'durable_authorization_enabled',
        'durable_transaction_boundary_enabled',
        'durable_pos_persistence_enabled',
        'exact_running_source_commit',
        'exact_running_artifact_sha256',
        'authenticated_configuration_mutation_channel',
        'read_before_write_read_after_supported',
        'non_mutating_health_attestation_supported',
        'verified_flag_rollback_supported',
        'activation_authority_binding',
        'feature_activation_state',
        'secrets_embedded',
    ];

    /**
     * @param array<string, mixed> $attestation
     * @return list<string>
     */
    public function violations(array $attestation): array
    {
        $violations = [];

        foreach (array_diff(self::REQUIRED_FIELDS, array_keys($attestation)) as $field) {
            $violations[] = 'missing_field:'.$field;
        }
        foreach (array_diff(array_keys($attestation), self::REQUIRED_FIELDS) as $field) {
            $violations[] = 'unexpected_field:'.$field;
        }

        if (($attestation['schema_version'] ?? null) !== 1) {
            $violations[] = 'schema_version_invalid';
        }

        if (! $this->stableIdentifier($attestation['environment_id'] ?? null)) {
            $violations[] = 'environment_id_invalid';
        }

        $runtimeClass = $attestation['runtime_class'] ?? null;
        if (! $this->stableIdentifier($runtimeClass)
            || ! is_string($runtimeClass)
            || in_array($runtimeClass, self::DISALLOWED_RUNTIME_CLASSES, true)) {
            $violations[] = 'runtime_class_not_non_synthetic_non_production';
        }

        if (($attestation['runtime_model'] ?? null) !== self::RUNTIME_MODEL) {
            $violations[] = 'runtime_model_invalid';
        }
        if (($attestation['environment_isolation'] ?? null) !== self::ENVIRONMENT_ISOLATION) {
            $violations[] = 'environment_isolation_invalid';
        }
        if (($attestation['serving_application_runtime'] ?? null) !== true) {
            $violations[] = 'serving_application_runtime_required';
        }
        if (($attestation['synthetic_fixture_runtime'] ?? null) !== false) {
            $violations[] = 'synthetic_fixture_runtime_forbidden';
        }
        if (($attestation['production_traffic_served'] ?? null) !== false) {
            $violations[] = 'production_traffic_forbidden';
        }

        foreach ([
            'durable_persistence_enabled',
            'durable_session_control_enabled',
            'durable_authorization_enabled',
            'durable_transaction_boundary_enabled',
            'durable_pos_persistence_enabled',
            'authenticated_configuration_mutation_channel',
            'read_before_write_read_after_supported',
            'non_mutating_health_attestation_supported',
            'verified_flag_rollback_supported',
        ] as $field) {
            if (($attestation[$field] ?? null) !== true) {
                $violations[] = $field.'_required';
            }
        }

        if (! is_string($attestation['exact_running_source_commit'] ?? null)
            || preg_match('/\A[0-9a-f]{40}\z/', $attestation['exact_running_source_commit']) !== 1) {
            $violations[] = 'exact_running_source_commit_invalid';
        }
        if (! is_string($attestation['exact_running_artifact_sha256'] ?? null)
            || preg_match('/\A[0-9a-f]{64}\z/', $attestation['exact_running_artifact_sha256']) !== 1) {
            $violations[] = 'exact_running_artifact_sha256_invalid';
        }

        if (($attestation['activation_authority_binding'] ?? null) !== self::ACTIVATION_AUTHORITY_BINDING) {
            $violations[] = 'activation_authority_binding_invalid';
        }
        if (($attestation['feature_activation_state'] ?? null) !== self::FEATURE_ACTIVATION_STATE) {
            $violations[] = 'feature_must_remain_inactive_during_target_readiness';
        }
        if (($attestation['secrets_embedded'] ?? null) !== false) {
            $violations[] = 'attestation_must_not_embed_secrets';
        }

        return array_values(array_unique($violations));
    }

    /** @param array<string, mixed> $attestation */
    public function qualifies(array $attestation): bool
    {
        return $this->violations($attestation) === [];
    }

    /** @param array<string, mixed> $attestation */
    public function requireQualified(array $attestation): void
    {
        $violations = $this->violations($attestation);
        if ($violations !== []) {
            throw new InvalidArgumentException(
                'final_shift_close_durable_runtime_readiness_rejected:'.implode(',', $violations),
            );
        }
    }

    private function stableIdentifier(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/\A[a-z0-9][a-z0-9._:-]{7,127}\z/', $value) === 1;
    }
}
