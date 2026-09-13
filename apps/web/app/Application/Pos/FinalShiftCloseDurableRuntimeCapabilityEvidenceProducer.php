<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;
use JsonException;

// Author by Lab | zefry
final readonly class FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer
{
    public const FEATURE = 'final-shift-close';
    public const OBSERVATION_STATE = 'TARGET_BOUND_CAPABILITY_OBSERVATIONS';
    public const PRODUCER_STATE = 'TARGET_BOUND_CAPABILITY_EVIDENCE_PRODUCED_NOT_AUTHORIZED';

    private const CAPABILITY_EVIDENCE_KINDS = [
        'authenticated_configuration_mutation_channel' => 'AUTHENTICATED_CONFIGURATION_CHANNEL_IDENTITY_EVIDENCE',
        'read_before_write_read_after' => 'READ_BEFORE_WRITE_READ_AFTER_VERIFICATION_EVIDENCE',
        'non_mutating_health_attestation' => 'NON_MUTATING_HEALTH_ATTESTATION_EVIDENCE',
        'verified_flag_rollback' => 'VERIFIED_FLAG_ROLLBACK_EVIDENCE',
    ];

    private const REQUIRED_OBSERVATION_FIELDS = [
        'schema_version',
        'feature',
        'observation_state',
        'capabilities',
        'secrets_embedded',
    ];

    private const REQUIRED_CAPABILITY_OBSERVATION_FIELDS = [
        'state',
        'evidence_kind',
        'environment_id',
        'runtime_class',
        'exact_running_source_commit',
        'exact_running_artifact_sha256',
        'evidence_payload_sha256',
        'secrets_embedded',
    ];

    public function __construct(
        private FinalShiftCloseDurableRuntimeCapabilityEvidence $qualifier,
    ) {}

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $observations
     * @return list<string>
     */
    public function violations(array $selection, array $attestation, array $observations): array
    {
        $violations = [];
        $targetBinding = $this->qualifier->targetBindingSha256($selection);
        if (! $this->lowerHex64($targetBinding)) {
            $violations[] = 'target_binding_unavailable';
        }

        foreach (array_diff(self::REQUIRED_OBSERVATION_FIELDS, array_keys($observations)) as $field) {
            $violations[] = 'missing_observation_field:'.$field;
        }
        foreach (array_diff(array_keys($observations), self::REQUIRED_OBSERVATION_FIELDS) as $field) {
            $violations[] = 'unexpected_observation_field:'.$field;
        }

        if (($observations['schema_version'] ?? null) !== 1) {
            $violations[] = 'observation_schema_version_invalid';
        }
        if (($observations['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'observation_feature_invalid';
        }
        if (($observations['observation_state'] ?? null) !== self::OBSERVATION_STATE) {
            $violations[] = 'observation_state_invalid';
        }
        if (($observations['secrets_embedded'] ?? null) !== false) {
            $violations[] = 'observations_must_not_embed_secrets';
        }

        $capabilities = is_array($observations['capabilities'] ?? null)
            ? $observations['capabilities']
            : [];
        $actualNames = array_keys($capabilities);
        $expectedNames = array_keys(self::CAPABILITY_EVIDENCE_KINDS);
        sort($actualNames, SORT_STRING);
        sort($expectedNames, SORT_STRING);
        if ($actualNames !== $expectedNames) {
            $violations[] = 'capability_observation_set_invalid';
        }

        $selectedTarget = is_array($selection['selected_target'] ?? null)
            ? $selection['selected_target']
            : [];

        foreach (self::CAPABILITY_EVIDENCE_KINDS as $capability => $expectedKind) {
            $record = is_array($capabilities[$capability] ?? null)
                ? $capabilities[$capability]
                : [];

            foreach (array_diff(self::REQUIRED_CAPABILITY_OBSERVATION_FIELDS, array_keys($record)) as $field) {
                $violations[] = 'missing_capability_observation_field:'.$capability.':'.$field;
            }
            foreach (array_diff(array_keys($record), self::REQUIRED_CAPABILITY_OBSERVATION_FIELDS) as $field) {
                $violations[] = 'unexpected_capability_observation_field:'.$capability.':'.$field;
            }

            if (($record['state'] ?? null) !== 'VERIFIED') {
                $violations[] = 'capability_observation_not_verified:'.$capability;
            }
            if (($record['evidence_kind'] ?? null) !== $expectedKind) {
                $violations[] = 'capability_observation_kind_invalid:'.$capability;
            }
            foreach ([
                'environment_id',
                'runtime_class',
                'exact_running_source_commit',
                'exact_running_artifact_sha256',
            ] as $field) {
                if (($record[$field] ?? null) !== ($selectedTarget[$field] ?? null)) {
                    $violations[] = 'capability_observation_target_mismatch:'.$capability.':'.$field;
                }
            }
            if (! $this->lowerHex64($record['evidence_payload_sha256'] ?? null)) {
                $violations[] = 'capability_observation_payload_sha256_invalid:'.$capability;
            }
            if (($record['secrets_embedded'] ?? null) !== false) {
                $violations[] = 'capability_observation_must_not_embed_secrets:'.$capability;
            }
        }

        if ($violations === [] && $this->qualifier->violations(
            $selection,
            $attestation,
            $this->buildEvidence($selection, $observations),
        ) !== []) {
            $violations[] = 'produced_evidence_fails_sprint148_qualification';
        }

        return array_values(array_unique($violations));
    }

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $observations
     */
    public function canProduce(array $selection, array $attestation, array $observations): bool
    {
        return $this->violations($selection, $attestation, $observations) === [];
    }

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $observations
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function produce(array $selection, array $attestation, array $observations): array
    {
        $violations = $this->violations($selection, $attestation, $observations);
        if ($violations !== []) {
            throw new InvalidArgumentException(
                'final_shift_close_durable_runtime_capability_evidence_producer_rejected:'.implode(',', $violations),
            );
        }

        $evidence = $this->buildEvidence($selection, $observations);
        $qualification = $this->qualifier->qualify($selection, $attestation, $evidence);

        return [
            'schema_version' => 1,
            'feature' => self::FEATURE,
            'producer_state' => self::PRODUCER_STATE,
            'evidence' => $evidence,
            'qualification' => $qualification,
            'producer_fingerprint_sha256' => hash('sha256', $this->canonicalJson([
                'selection_fingerprint_sha256' => $selection['selection_fingerprint_sha256'],
                'target_binding_sha256' => $qualification['target_binding_sha256'],
                'capability_evidence_bundle_sha256' => $qualification['capability_evidence_bundle_sha256'],
            ])),
            'activation_authority_state' => 'NOT_GRANTED',
            'feature_activation_state' => 'INACTIVE',
            'runtime_allowlist_change' => 'NOT_IMPLEMENTED',
            'secrets_embedded' => false,
        ];
    }

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $observations
     * @return array<string, mixed>
     */
    private function buildEvidence(array $selection, array $observations): array
    {
        $target = is_array($selection['selected_target'] ?? null)
            ? $selection['selected_target']
            : [];
        $targetBinding = $this->qualifier->targetBindingSha256($selection);
        $capabilities = [];

        foreach (self::CAPABILITY_EVIDENCE_KINDS as $capability => $kind) {
            $record = is_array($observations['capabilities'][$capability] ?? null)
                ? $observations['capabilities'][$capability]
                : [];
            $identity = [
                'feature' => self::FEATURE,
                'capability' => $capability,
                'state' => 'VERIFIED',
                'evidence_kind' => $kind,
                'environment_id' => $target['environment_id'] ?? null,
                'runtime_class' => $target['runtime_class'] ?? null,
                'exact_running_source_commit' => $target['exact_running_source_commit'] ?? null,
                'exact_running_artifact_sha256' => $target['exact_running_artifact_sha256'] ?? null,
                'readiness_attestation_sha256' => $target['readiness_attestation_sha256'] ?? null,
                'selection_fingerprint_sha256' => $selection['selection_fingerprint_sha256'] ?? null,
                'target_binding_sha256' => $targetBinding,
                'evidence_payload_sha256' => $record['evidence_payload_sha256'] ?? null,
            ];

            $capabilities[$capability] = [
                'state' => 'VERIFIED',
                'evidence_kind' => $kind,
                'evidence_sha256' => hash('sha256', $this->canonicalJson($identity)),
                'target_binding_sha256' => $targetBinding,
                'secrets_embedded' => false,
            ];
        }

        return [
            'schema_version' => 1,
            'feature' => FinalShiftCloseDurableRuntimeCapabilityEvidence::FEATURE,
            'evidence_state' => FinalShiftCloseDurableRuntimeCapabilityEvidence::EVIDENCE_STATE,
            'environment_id' => $target['environment_id'] ?? null,
            'runtime_class' => $target['runtime_class'] ?? null,
            'exact_running_source_commit' => $target['exact_running_source_commit'] ?? null,
            'exact_running_artifact_sha256' => $target['exact_running_artifact_sha256'] ?? null,
            'readiness_attestation_sha256' => $target['readiness_attestation_sha256'] ?? null,
            'selection_fingerprint_sha256' => $selection['selection_fingerprint_sha256'] ?? null,
            'target_binding_sha256' => $targetBinding,
            'capabilities' => $capabilities,
            'secrets_embedded' => false,
        ];
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
