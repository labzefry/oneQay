<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;
use JsonException;

// Author by Lab | zefry
final readonly class FinalShiftCloseDurableRuntimeCapabilityEvidence
{
    public const FEATURE = 'final-shift-close';
    public const EVIDENCE_STATE = 'TARGET_BOUND_CAPABILITY_EVIDENCE';
    public const QUALIFICATION_STATE = 'TARGET_BOUND_CAPABILITY_EVIDENCE_IDENTITY_QUALIFIED_NOT_AUTHORIZED';

    private const CAPABILITY_EVIDENCE_KINDS = [
        'authenticated_configuration_mutation_channel' => 'AUTHENTICATED_CONFIGURATION_CHANNEL_IDENTITY_EVIDENCE',
        'read_before_write_read_after' => 'READ_BEFORE_WRITE_READ_AFTER_VERIFICATION_EVIDENCE',
        'non_mutating_health_attestation' => 'NON_MUTATING_HEALTH_ATTESTATION_EVIDENCE',
        'verified_flag_rollback' => 'VERIFIED_FLAG_ROLLBACK_EVIDENCE',
    ];

    private const REQUIRED_EVIDENCE_FIELDS = [
        'schema_version',
        'feature',
        'evidence_state',
        'environment_id',
        'runtime_class',
        'exact_running_source_commit',
        'exact_running_artifact_sha256',
        'readiness_attestation_sha256',
        'selection_fingerprint_sha256',
        'target_binding_sha256',
        'capabilities',
        'secrets_embedded',
    ];

    private const REQUIRED_CAPABILITY_FIELDS = [
        'state',
        'evidence_kind',
        'evidence_sha256',
        'target_binding_sha256',
        'secrets_embedded',
    ];

    public function __construct(
        private FinalShiftCloseDurableRuntimeReadiness $readiness,
        private FinalShiftCloseDurableRuntimeTargetSelection $selector,
    ) {}

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $evidence
     * @return list<string>
     */
    public function violations(array $selection, array $attestation, array $evidence): array
    {
        $violations = [];

        if ($this->readiness->violations($attestation) !== []) {
            $violations[] = 'readiness_attestation_unqualified';
        }

        if (($selection['schema_version'] ?? null) !== 1) {
            $violations[] = 'selection_schema_version_invalid';
        }
        if (($selection['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'selection_feature_invalid';
        }
        if (($selection['selection_state'] ?? null) !== FinalShiftCloseDurableRuntimeTargetSelection::SELECTION_STATE) {
            $violations[] = 'selection_state_invalid';
        }
        if (($selection['activation_authority_state'] ?? null) !== FinalShiftCloseDurableRuntimeTargetSelection::ACTIVATION_AUTHORITY_STATE) {
            $violations[] = 'selection_activation_authority_must_remain_not_granted';
        }
        if (($selection['feature_activation_state'] ?? null) !== FinalShiftCloseDurableRuntimeTargetSelection::FEATURE_ACTIVATION_STATE) {
            $violations[] = 'selection_feature_must_remain_inactive';
        }
        if (($selection['runtime_allowlist_change'] ?? null) !== FinalShiftCloseDurableRuntimeTargetSelection::RUNTIME_ALLOWLIST_CHANGE) {
            $violations[] = 'selection_runtime_allowlist_must_remain_unchanged';
        }

        if ($this->readiness->violations($attestation) === []) {
            try {
                $expectedSelection = $this->selector->select($attestation);
                foreach ([
                    'schema_version',
                    'feature',
                    'selection_state',
                    'selection_fingerprint_sha256',
                    'activation_authority_state',
                    'feature_activation_state',
                    'runtime_allowlist_change',
                ] as $field) {
                    if (($selection[$field] ?? null) !== $expectedSelection[$field]) {
                        $violations[] = 'selection_identity_mismatch:'.$field;
                    }
                }

                $selectedTarget = is_array($selection['selected_target'] ?? null)
                    ? $selection['selected_target']
                    : [];
                $expectedTarget = $expectedSelection['selected_target'];
                foreach ([
                    'environment_id',
                    'runtime_class',
                    'exact_running_source_commit',
                    'exact_running_artifact_sha256',
                    'readiness_attestation_sha256',
                ] as $field) {
                    if (($selectedTarget[$field] ?? null) !== $expectedTarget[$field]) {
                        $violations[] = 'selected_target_identity_mismatch:'.$field;
                    }
                }
            } catch (InvalidArgumentException|JsonException) {
                $violations[] = 'selection_recomputation_failed';
            }
        }

        foreach (array_diff(self::REQUIRED_EVIDENCE_FIELDS, array_keys($evidence)) as $field) {
            $violations[] = 'missing_evidence_field:'.$field;
        }
        foreach (array_diff(array_keys($evidence), self::REQUIRED_EVIDENCE_FIELDS) as $field) {
            $violations[] = 'unexpected_evidence_field:'.$field;
        }

        if (($evidence['schema_version'] ?? null) !== 1) {
            $violations[] = 'evidence_schema_version_invalid';
        }
        if (($evidence['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'evidence_feature_invalid';
        }
        if (($evidence['evidence_state'] ?? null) !== self::EVIDENCE_STATE) {
            $violations[] = 'evidence_state_invalid';
        }
        if (($evidence['secrets_embedded'] ?? null) !== false) {
            $violations[] = 'evidence_must_not_embed_secrets';
        }

        $selectedTarget = is_array($selection['selected_target'] ?? null)
            ? $selection['selected_target']
            : [];
        foreach ([
            'environment_id',
            'runtime_class',
            'exact_running_source_commit',
            'exact_running_artifact_sha256',
            'readiness_attestation_sha256',
        ] as $field) {
            if (($evidence[$field] ?? null) !== ($selectedTarget[$field] ?? null)) {
                $violations[] = 'evidence_selected_target_mismatch:'.$field;
            }
        }
        if (($evidence['selection_fingerprint_sha256'] ?? null) !== ($selection['selection_fingerprint_sha256'] ?? null)) {
            $violations[] = 'evidence_selection_fingerprint_mismatch';
        }

        $targetBindingSha256 = $this->targetBindingSha256($selection);
        if (! $this->lowerHex64($targetBindingSha256)
            || ($evidence['target_binding_sha256'] ?? null) !== $targetBindingSha256) {
            $violations[] = 'evidence_target_binding_mismatch';
        }

        $capabilities = is_array($evidence['capabilities'] ?? null)
            ? $evidence['capabilities']
            : [];
        $actualCapabilityNames = array_keys($capabilities);
        $expectedCapabilityNames = array_keys(self::CAPABILITY_EVIDENCE_KINDS);
        sort($actualCapabilityNames, SORT_STRING);
        sort($expectedCapabilityNames, SORT_STRING);
        if ($actualCapabilityNames !== $expectedCapabilityNames) {
            $violations[] = 'capability_set_invalid';
        }

        foreach (self::CAPABILITY_EVIDENCE_KINDS as $capability => $expectedKind) {
            $record = is_array($capabilities[$capability] ?? null)
                ? $capabilities[$capability]
                : [];

            foreach (array_diff(self::REQUIRED_CAPABILITY_FIELDS, array_keys($record)) as $field) {
                $violations[] = 'missing_capability_field:'.$capability.':'.$field;
            }
            foreach (array_diff(array_keys($record), self::REQUIRED_CAPABILITY_FIELDS) as $field) {
                $violations[] = 'unexpected_capability_field:'.$capability.':'.$field;
            }

            if (($record['state'] ?? null) !== 'VERIFIED') {
                $violations[] = 'capability_not_verified:'.$capability;
            }
            if (($record['evidence_kind'] ?? null) !== $expectedKind) {
                $violations[] = 'capability_evidence_kind_invalid:'.$capability;
            }
            if (! $this->lowerHex64($record['evidence_sha256'] ?? null)) {
                $violations[] = 'capability_evidence_sha256_invalid:'.$capability;
            }
            if (($record['target_binding_sha256'] ?? null) !== $targetBindingSha256) {
                $violations[] = 'capability_target_binding_mismatch:'.$capability;
            }
            if (($record['secrets_embedded'] ?? null) !== false) {
                $violations[] = 'capability_evidence_must_not_embed_secrets:'.$capability;
            }
        }

        return array_values(array_unique($violations));
    }

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $evidence
     */
    public function qualifies(array $selection, array $attestation, array $evidence): bool
    {
        return $this->violations($selection, $attestation, $evidence) === [];
    }

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $evidence
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function qualify(array $selection, array $attestation, array $evidence): array
    {
        $violations = $this->violations($selection, $attestation, $evidence);
        if ($violations !== []) {
            throw new InvalidArgumentException(
                'final_shift_close_durable_runtime_capability_evidence_rejected:'.implode(',', $violations),
            );
        }

        $capabilityEvidenceSha256 = [];
        foreach (self::CAPABILITY_EVIDENCE_KINDS as $capability => $_kind) {
            $capabilityEvidenceSha256[$capability] = $evidence['capabilities'][$capability]['evidence_sha256'];
        }

        return [
            'schema_version' => 1,
            'feature' => self::FEATURE,
            'qualification_state' => self::QUALIFICATION_STATE,
            'selected_target' => [
                'environment_id' => $evidence['environment_id'],
                'runtime_class' => $evidence['runtime_class'],
                'exact_running_source_commit' => $evidence['exact_running_source_commit'],
                'exact_running_artifact_sha256' => $evidence['exact_running_artifact_sha256'],
                'readiness_attestation_sha256' => $evidence['readiness_attestation_sha256'],
            ],
            'selection_fingerprint_sha256' => $evidence['selection_fingerprint_sha256'],
            'target_binding_sha256' => $evidence['target_binding_sha256'],
            'capability_evidence_sha256' => $capabilityEvidenceSha256,
            'capability_evidence_bundle_sha256' => hash('sha256', $this->canonicalJson($evidence)),
            'activation_authority_state' => FinalShiftCloseDurableRuntimeTargetSelection::ACTIVATION_AUTHORITY_STATE,
            'feature_activation_state' => FinalShiftCloseDurableRuntimeTargetSelection::FEATURE_ACTIVATION_STATE,
            'runtime_allowlist_change' => FinalShiftCloseDurableRuntimeTargetSelection::RUNTIME_ALLOWLIST_CHANGE,
        ];
    }

    /** @param array<string, mixed> $selection */
    public function targetBindingSha256(array $selection): string
    {
        $target = is_array($selection['selected_target'] ?? null)
            ? $selection['selected_target']
            : [];

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
