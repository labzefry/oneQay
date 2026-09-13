<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;
use JsonException;

// Author by Lab | zefry
final readonly class FinalShiftCloseFeatureActivationExecutionPlan
{
    public const FEATURE = 'final-shift-close';
    public const PLAN_STATE = 'TARGET_BOUND_FEATURE_ACTIVATION_EXECUTION_PLAN_SOURCE_ONLY';
    public const AUTHORITY_STATE = 'GRANTED';
    public const AUTHORITY_CONTEXT = 'final-shift-close-feature-activation-authority';
    public const FEATURE_FLAG = 'ONEQAY_POS_SHIFT_CLOSE_ENABLED';
    public const CONCRETE_TRANSPORT = 'NOT_IMPLEMENTED';
    public const DISPATCH_STATE = 'NOT_PERFORMED';

    private const REQUIRED_AUTHORITY_FIELDS = [
        'schema_version',
        'feature',
        'authority_state',
        'authority_context',
        'environment_id',
        'runtime_class',
        'exact_running_source_commit',
        'exact_running_artifact_sha256',
        'readiness_attestation_sha256',
        'selection_fingerprint_sha256',
        'target_binding_sha256',
        'dependency_envelope_sha256',
        'executor_source_commit',
        'secrets_embedded',
    ];

    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $dependencyQualification
     * @param array<string, mixed> $authority
     * @return list<string>
     */
    public function violations(
        array $state,
        array $selection,
        array $dependencyQualification,
        array $authority,
    ): array {
        $violations = [];

        if (($state['schema_version'] ?? null) !== 1 || ($state['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'operational_state_identity_invalid';
        }
        if (($state['migration27']['state'] ?? null) !== 'EXECUTED') {
            $violations[] = 'migration27_not_executed';
        }
        if (($state['permission_provisioning']['state'] ?? null) !== 'PROVISIONED') {
            $violations[] = 'permission_not_provisioned';
        }
        if (($state['permission_provisioning']['permission_id'] ?? null) !== 'pos.shift.close') {
            $violations[] = 'permission_identity_invalid';
        }
        if (($state['permission_provisioning']['default_grant'] ?? null) !== 'NONE') {
            $violations[] = 'default_grant_forbidden';
        }
        if (($state['feature_activation']['state'] ?? null) !== 'INACTIVE') {
            $violations[] = 'feature_must_be_inactive_before_plan';
        }
        if (($state['feature_activation']['authority_context'] ?? null) !== self::AUTHORITY_CONTEXT) {
            $violations[] = 'feature_authority_context_invalid';
        }
        if (($state['feature_activation']['runtime_flag'] ?? null) !== self::FEATURE_FLAG) {
            $violations[] = 'feature_flag_identity_invalid';
        }

        if (($selection['schema_version'] ?? null) !== 1 || ($selection['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'selection_identity_invalid';
        }
        if (($selection['selection_state'] ?? null) !== FinalShiftCloseDurableRuntimeTargetSelection::SELECTION_STATE) {
            $violations[] = 'selection_state_invalid';
        }
        if (($selection['activation_authority_state'] ?? null) !== FinalShiftCloseDurableRuntimeTargetSelection::ACTIVATION_AUTHORITY_STATE) {
            $violations[] = 'selection_embedded_authority_must_remain_not_granted';
        }
        if (($selection['feature_activation_state'] ?? null) !== FinalShiftCloseDurableRuntimeTargetSelection::FEATURE_ACTIVATION_STATE) {
            $violations[] = 'selection_feature_must_remain_inactive';
        }
        if (($selection['runtime_allowlist_change'] ?? null) !== FinalShiftCloseDurableRuntimeTargetSelection::RUNTIME_ALLOWLIST_CHANGE) {
            $violations[] = 'selection_runtime_allowlist_state_invalid';
        }

        $target = is_array($selection['selected_target'] ?? null) ? $selection['selected_target'] : [];
        foreach ([
            'environment_id',
            'runtime_class',
            'exact_running_source_commit',
            'exact_running_artifact_sha256',
            'readiness_attestation_sha256',
        ] as $field) {
            if (! is_string($target[$field] ?? null) || trim((string) $target[$field]) === '') {
                $violations[] = 'selected_target_field_invalid:'.$field;
            }
        }
        if (! $this->lowerHex40($target['exact_running_source_commit'] ?? null)) {
            $violations[] = 'selected_target_source_commit_invalid';
        }
        foreach (['exact_running_artifact_sha256', 'readiness_attestation_sha256'] as $field) {
            if (! $this->lowerHex64($target[$field] ?? null)) {
                $violations[] = 'selected_target_sha256_invalid:'.$field;
            }
        }
        if (! $this->lowerHex64($selection['selection_fingerprint_sha256'] ?? null)) {
            $violations[] = 'selection_fingerprint_invalid';
        }

        $targetBindingSha256 = $this->targetBindingSha256($selection);
        if (! $this->lowerHex64($targetBindingSha256)) {
            $violations[] = 'target_binding_unavailable';
        }

        if (($dependencyQualification['schema_version'] ?? null) !== 1
            || ($dependencyQualification['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'dependency_qualification_identity_invalid';
        }
        if (($dependencyQualification['qualification_state'] ?? null)
            !== FinalShiftCloseDurableRuntimeDependencyEnvelope::QUALIFICATION_STATE) {
            $violations[] = 'dependency_envelope_not_fully_qualified';
        }
        if (($dependencyQualification['environment_id'] ?? null) !== ($target['environment_id'] ?? null)) {
            $violations[] = 'dependency_environment_mismatch';
        }
        if (($dependencyQualification['runtime_class'] ?? null) !== ($target['runtime_class'] ?? null)) {
            $violations[] = 'dependency_runtime_class_mismatch';
        }
        if (($dependencyQualification['target_binding_sha256'] ?? null) !== $targetBindingSha256) {
            $violations[] = 'dependency_target_binding_mismatch';
        }
        if (! $this->lowerHex64($dependencyQualification['dependency_envelope_sha256'] ?? null)) {
            $violations[] = 'dependency_envelope_sha256_invalid';
        }
        if (($dependencyQualification['activation_authority_state'] ?? null)
            !== FinalShiftCloseDurableRuntimeTargetSelection::ACTIVATION_AUTHORITY_STATE) {
            $violations[] = 'dependency_qualification_must_not_grant_authority';
        }
        if (($dependencyQualification['feature_activation_state'] ?? null)
            !== FinalShiftCloseDurableRuntimeTargetSelection::FEATURE_ACTIVATION_STATE) {
            $violations[] = 'dependency_qualification_feature_state_invalid';
        }

        foreach (array_diff(self::REQUIRED_AUTHORITY_FIELDS, array_keys($authority)) as $field) {
            $violations[] = 'missing_authority_field:'.$field;
        }
        foreach (array_diff(array_keys($authority), self::REQUIRED_AUTHORITY_FIELDS) as $field) {
            $violations[] = 'unexpected_authority_field:'.$field;
        }
        if (($authority['schema_version'] ?? null) !== 1 || ($authority['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'authority_identity_invalid';
        }
        if (($authority['authority_state'] ?? null) !== self::AUTHORITY_STATE
            || ($authority['authority_context'] ?? null) !== self::AUTHORITY_CONTEXT) {
            $violations[] = 'exact_feature_activation_authority_missing';
        }
        foreach ([
            'environment_id',
            'runtime_class',
            'exact_running_source_commit',
            'exact_running_artifact_sha256',
            'readiness_attestation_sha256',
        ] as $field) {
            if (($authority[$field] ?? null) !== ($target[$field] ?? null)) {
                $violations[] = 'authority_selected_target_mismatch:'.$field;
            }
        }
        if (($authority['selection_fingerprint_sha256'] ?? null) !== ($selection['selection_fingerprint_sha256'] ?? null)) {
            $violations[] = 'authority_selection_fingerprint_mismatch';
        }
        if (($authority['target_binding_sha256'] ?? null) !== $targetBindingSha256) {
            $violations[] = 'authority_target_binding_mismatch';
        }
        if (($authority['dependency_envelope_sha256'] ?? null)
            !== ($dependencyQualification['dependency_envelope_sha256'] ?? null)) {
            $violations[] = 'authority_dependency_envelope_mismatch';
        }
        if (! $this->lowerHex40($authority['executor_source_commit'] ?? null)) {
            $violations[] = 'authority_executor_source_commit_invalid';
        }
        if (($authority['secrets_embedded'] ?? null) !== false) {
            $violations[] = 'authority_must_not_embed_secrets';
        }

        return array_values(array_unique($violations));
    }

    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $dependencyQualification
     * @param array<string, mixed> $authority
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function qualify(
        array $state,
        array $selection,
        array $dependencyQualification,
        array $authority,
    ): array {
        $violations = $this->violations($state, $selection, $dependencyQualification, $authority);
        if ($violations !== []) {
            throw new InvalidArgumentException(
                'final_shift_close_feature_activation_execution_plan_rejected:'.implode(',', $violations),
            );
        }

        $target = $selection['selected_target'];
        $plan = [
            'schema_version' => 1,
            'feature' => self::FEATURE,
            'plan_state' => self::PLAN_STATE,
            'environment_id' => $target['environment_id'],
            'runtime_class' => $target['runtime_class'],
            'exact_running_source_commit' => $target['exact_running_source_commit'],
            'exact_running_artifact_sha256' => $target['exact_running_artifact_sha256'],
            'readiness_attestation_sha256' => $target['readiness_attestation_sha256'],
            'selection_fingerprint_sha256' => $selection['selection_fingerprint_sha256'],
            'target_binding_sha256' => $this->targetBindingSha256($selection),
            'dependency_envelope_sha256' => $dependencyQualification['dependency_envelope_sha256'],
            'activation_authority_sha256' => hash('sha256', $this->canonicalJson($authority)),
            'executor_source_commit' => $authority['executor_source_commit'],
            'runtime_flag' => self::FEATURE_FLAG,
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
            'concrete_configuration_transport' => self::CONCRETE_TRANSPORT,
            'dispatch_state' => self::DISPATCH_STATE,
            'runtime_allowlist_change' => FinalShiftCloseDurableRuntimeTargetSelection::RUNTIME_ALLOWLIST_CHANGE,
            'feature_activation_state' => FinalShiftCloseDurableRuntimeTargetSelection::FEATURE_ACTIVATION_STATE,
            'secrets_embedded' => false,
        ];
        $plan['activation_plan_sha256'] = hash('sha256', $this->canonicalJson($plan));

        return $plan;
    }

    /** @param array<string, mixed> $selection */
    public function targetBindingSha256(array $selection): string
    {
        $target = is_array($selection['selected_target'] ?? null) ? $selection['selected_target'] : [];
        $parts = [
            self::FEATURE,
            (string) ($selection['selection_state'] ?? ''),
            (string) ($target['environment_id'] ?? ''),
            (string) ($target['runtime_class'] ?? ''),
            (string) ($target['exact_running_source_commit'] ?? ''),
            (string) ($target['exact_running_artifact_sha256'] ?? ''),
            (string) ($target['readiness_attestation_sha256'] ?? ''),
            (string) ($selection['selection_fingerprint_sha256'] ?? ''),
        ];

        if (in_array('', $parts, true)) {
            return '';
        }

        return hash('sha256', implode('|', $parts));
    }

    private function lowerHex40(mixed $value): bool
    {
        return is_string($value) && preg_match('/\A[0-9a-f]{40}\z/', $value) === 1;
    }

    private function lowerHex64(mixed $value): bool
    {
        return is_string($value) && preg_match('/\A[0-9a-f]{64}\z/', $value) === 1;
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
