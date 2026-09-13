<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;
use JsonException;

// Author by Lab | zefry
final readonly class FinalShiftCloseFeatureActivationTransportEnvelope
{
    public const FEATURE = 'final-shift-close';
    public const TRANSPORT_STATE = 'TARGET_BOUND_FEATURE_ACTIVATION_TRANSPORT_ENVELOPE_SOURCE_ONLY';
    public const CONCRETE_ADAPTER = 'NOT_IMPLEMENTED';
    public const NETWORK_DISPATCH = 'NOT_PERFORMED';
    public const FEATURE_ACTIVATION_STATE = 'INACTIVE';
    public const RUNTIME_ALLOWLIST_CHANGE = 'NOT_IMPLEMENTED';

    private const REQUIRED_CAPABILITY_NAMES = [
        'authenticated_configuration_mutation_channel',
        'read_before_write_read_after',
        'non_mutating_health_attestation',
        'verified_flag_rollback',
    ];

    /**
     * @param array<string, mixed> $activationPlan
     * @param array<string, mixed> $capabilityQualification
     * @return list<string>
     */
    public function violations(array $activationPlan, array $capabilityQualification): array
    {
        $violations = [];

        if (($activationPlan['schema_version'] ?? null) !== 1
            || ($activationPlan['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'activation_plan_identity_invalid';
        }
        if (($activationPlan['plan_state'] ?? null)
            !== FinalShiftCloseFeatureActivationExecutionPlan::PLAN_STATE) {
            $violations[] = 'activation_plan_state_invalid';
        }
        foreach (['environment_id', 'runtime_class'] as $field) {
            if (! is_string($activationPlan[$field] ?? null) || trim((string) $activationPlan[$field]) === '') {
                $violations[] = 'activation_plan_target_field_invalid:'.$field;
            }
        }
        foreach ([
            'exact_running_source_commit' => 40,
            'exact_running_artifact_sha256' => 64,
            'readiness_attestation_sha256' => 64,
            'selection_fingerprint_sha256' => 64,
            'target_binding_sha256' => 64,
            'dependency_envelope_sha256' => 64,
            'activation_authority_sha256' => 64,
            'executor_source_commit' => 40,
            'activation_plan_sha256' => 64,
        ] as $field => $length) {
            if (! $this->lowerHex($activationPlan[$field] ?? null, $length)) {
                $violations[] = 'activation_plan_digest_invalid:'.$field;
            }
        }
        if (($activationPlan['runtime_flag'] ?? null) !== FinalShiftCloseFeatureActivationExecutionPlan::FEATURE_FLAG) {
            $violations[] = 'activation_plan_runtime_flag_invalid';
        }
        if (($activationPlan['required_pre_activation_value'] ?? null) !== false
            || ($activationPlan['desired_activation_value'] ?? null) !== true
            || ($activationPlan['rollback_value'] ?? null) !== false) {
            $violations[] = 'activation_plan_values_invalid';
        }
        if (($activationPlan['ordered_steps'] ?? null) !== [
            'READ_FLAG_BEFORE',
            'WRITE_FLAG_TRUE',
            'READ_FLAG_AFTER_REQUIRE_TRUE',
            'NON_MUTATING_HEALTH_ATTESTATION',
            'ON_ANY_POST_WRITE_FAILURE_WRITE_FLAG_FALSE',
            'VERIFY_ROLLBACK_READBACK_FALSE',
        ]) {
            $violations[] = 'activation_plan_order_invalid';
        }
        if (($activationPlan['concrete_configuration_transport'] ?? null) !== 'NOT_IMPLEMENTED') {
            $violations[] = 'activation_plan_transport_boundary_invalid';
        }
        if (($activationPlan['dispatch_state'] ?? null) !== 'NOT_PERFORMED') {
            $violations[] = 'activation_plan_dispatch_boundary_invalid';
        }
        if (($activationPlan['runtime_allowlist_change'] ?? null) !== 'NOT_IMPLEMENTED') {
            $violations[] = 'activation_plan_allowlist_boundary_invalid';
        }
        if (($activationPlan['feature_activation_state'] ?? null) !== 'INACTIVE') {
            $violations[] = 'activation_plan_feature_must_remain_inactive';
        }
        if (($activationPlan['secrets_embedded'] ?? null) !== false) {
            $violations[] = 'activation_plan_must_be_secret_free';
        }

        if (($capabilityQualification['schema_version'] ?? null) !== 1
            || ($capabilityQualification['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'capability_qualification_identity_invalid';
        }
        if (($capabilityQualification['qualification_state'] ?? null)
            !== FinalShiftCloseDurableRuntimeCapabilityEvidence::QUALIFICATION_STATE) {
            $violations[] = 'capability_qualification_state_invalid';
        }
        $selectedTarget = is_array($capabilityQualification['selected_target'] ?? null)
            ? $capabilityQualification['selected_target']
            : [];
        foreach (['environment_id', 'runtime_class', 'exact_running_source_commit', 'exact_running_artifact_sha256', 'readiness_attestation_sha256'] as $field) {
            if (($selectedTarget[$field] ?? null) !== ($activationPlan[$field] ?? null)) {
                $violations[] = 'capability_selected_target_mismatch:'.$field;
            }
        }
        if (($capabilityQualification['selection_fingerprint_sha256'] ?? null)
            !== ($activationPlan['selection_fingerprint_sha256'] ?? null)) {
            $violations[] = 'capability_selection_fingerprint_mismatch';
        }
        if (($capabilityQualification['target_binding_sha256'] ?? null)
            !== ($activationPlan['target_binding_sha256'] ?? null)) {
            $violations[] = 'capability_target_binding_mismatch';
        }
        if (! $this->lowerHex($capabilityQualification['capability_evidence_bundle_sha256'] ?? null, 64)) {
            $violations[] = 'capability_evidence_bundle_sha256_invalid';
        }
        if (($capabilityQualification['activation_authority_state'] ?? null) !== 'NOT_GRANTED') {
            $violations[] = 'capability_qualification_must_not_grant_activation_authority';
        }
        if (($capabilityQualification['feature_activation_state'] ?? null) !== 'INACTIVE') {
            $violations[] = 'capability_qualification_feature_state_invalid';
        }
        if (($capabilityQualification['runtime_allowlist_change'] ?? null) !== 'NOT_IMPLEMENTED') {
            $violations[] = 'capability_qualification_allowlist_boundary_invalid';
        }

        $capabilityDigests = is_array($capabilityQualification['capability_evidence_sha256'] ?? null)
            ? $capabilityQualification['capability_evidence_sha256']
            : [];
        $actualNames = array_keys($capabilityDigests);
        $expectedNames = self::REQUIRED_CAPABILITY_NAMES;
        sort($actualNames, SORT_STRING);
        sort($expectedNames, SORT_STRING);
        if ($actualNames !== $expectedNames) {
            $violations[] = 'capability_digest_set_invalid';
        }
        foreach (self::REQUIRED_CAPABILITY_NAMES as $name) {
            if (! $this->lowerHex($capabilityDigests[$name] ?? null, 64)) {
                $violations[] = 'capability_digest_invalid:'.$name;
            }
        }

        return array_values(array_unique($violations));
    }

    /**
     * @param array<string, mixed> $activationPlan
     * @param array<string, mixed> $capabilityQualification
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function build(array $activationPlan, array $capabilityQualification): array
    {
        $violations = $this->violations($activationPlan, $capabilityQualification);
        if ($violations !== []) {
            throw new InvalidArgumentException(
                'final_shift_close_feature_activation_transport_envelope_rejected:'.implode(',', $violations),
            );
        }

        $capabilityDigests = $capabilityQualification['capability_evidence_sha256'];
        $operations = [
            ['sequence' => 1, 'operation' => 'READ_FLAG', 'expected_value' => false],
            ['sequence' => 2, 'operation' => 'WRITE_FLAG', 'value' => true],
            ['sequence' => 3, 'operation' => 'READ_FLAG', 'expected_value' => true],
            ['sequence' => 4, 'operation' => 'NON_MUTATING_HEALTH_ATTESTATION'],
            ['sequence' => 5, 'operation' => 'ROLLBACK_WRITE_FLAG_ON_POST_WRITE_FAILURE', 'value' => false],
            ['sequence' => 6, 'operation' => 'VERIFY_ROLLBACK_READBACK', 'expected_value' => false],
        ];

        $envelope = [
            'schema_version' => 1,
            'feature' => self::FEATURE,
            'transport_state' => self::TRANSPORT_STATE,
            'environment_id' => $activationPlan['environment_id'],
            'runtime_class' => $activationPlan['runtime_class'],
            'exact_running_source_commit' => $activationPlan['exact_running_source_commit'],
            'exact_running_artifact_sha256' => $activationPlan['exact_running_artifact_sha256'],
            'readiness_attestation_sha256' => $activationPlan['readiness_attestation_sha256'],
            'selection_fingerprint_sha256' => $activationPlan['selection_fingerprint_sha256'],
            'target_binding_sha256' => $activationPlan['target_binding_sha256'],
            'dependency_envelope_sha256' => $activationPlan['dependency_envelope_sha256'],
            'activation_authority_sha256' => $activationPlan['activation_authority_sha256'],
            'activation_plan_sha256' => $activationPlan['activation_plan_sha256'],
            'capability_evidence_bundle_sha256' => $capabilityQualification['capability_evidence_bundle_sha256'],
            'authenticated_configuration_channel_evidence_sha256' => $capabilityDigests['authenticated_configuration_mutation_channel'],
            'read_before_write_read_after_evidence_sha256' => $capabilityDigests['read_before_write_read_after'],
            'non_mutating_health_attestation_evidence_sha256' => $capabilityDigests['non_mutating_health_attestation'],
            'verified_flag_rollback_evidence_sha256' => $capabilityDigests['verified_flag_rollback'],
            'runtime_flag' => FinalShiftCloseFeatureActivationExecutionPlan::FEATURE_FLAG,
            'operations' => $operations,
            'concrete_adapter' => self::CONCRETE_ADAPTER,
            'network_dispatch' => self::NETWORK_DISPATCH,
            'runtime_allowlist_change' => self::RUNTIME_ALLOWLIST_CHANGE,
            'feature_activation_state' => self::FEATURE_ACTIVATION_STATE,
            'secrets_embedded' => false,
        ];
        $envelope['transport_envelope_sha256'] = hash('sha256', $this->canonicalJson($envelope));

        return $envelope;
    }

    private function lowerHex(mixed $value, int $length): bool
    {
        return is_string($value) && preg_match('/\A[0-9a-f]{'.$length.'}\z/', $value) === 1;
    }

    /** @param array<string, mixed> $value */
    private function canonicalJson(array $value): string
    {
        return json_encode(
            $this->canonicalize($value),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_is_list($value)) {
            return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->canonicalize($item);
        }

        return $value;
    }
}
