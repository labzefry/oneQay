<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;
use JsonException;

// Author by Lab | zefry
final readonly class FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer
{
    public const FEATURE = 'final-shift-close';
    public const OBSERVATION_STATE = 'TARGET_BOUND_DURABLE_RUNTIME_DEPENDENCY_OBSERVATIONS';
    public const PRODUCER_STATE = 'TARGET_BOUND_DEPENDENCY_ENVELOPE_EVIDENCE_PRODUCED_NOT_AUTHORIZED';

    /** @var array<string, string> */
    private const REQUIRED_COMPONENTS = [
        'final_shift_close_delivery' => 'apps/web/app/Providers/FinalShiftCloseServiceProvider.php',
        'pos_session_context' => 'apps/web/app/Delivery/Http/Middleware/RequirePosSessionContextMiddleware.php',
        'active_first_party_session_middleware' => 'apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php',
        'first_party_session_authority_repository' => 'apps/web/app/Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php',
        'first_party_identity_eligibility' => 'apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityVerifier.php',
        'durable_role_permission_authorization' => 'apps/web/app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php',
        'durable_persistence_transaction' => 'apps/web/app/Infrastructure/Persistence/LaravelPersistenceTransaction.php',
        'final_shift_close_repository' => 'apps/web/app/Infrastructure/Pos/LaravelCloseShiftRepository.php',
        'durable_pos_sale_lifecycle' => 'apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php',
    ];

    private const REQUIRED_OBSERVATION_FIELDS = [
        'schema_version',
        'feature',
        'observation_state',
        'components',
        'synthetic_mixing',
        'secrets_embedded',
    ];

    private const REQUIRED_COMPONENT_OBSERVATION_FIELDS = [
        'state',
        'source_path',
        'environment_id',
        'runtime_class',
        'exact_running_source_commit',
        'exact_running_artifact_sha256',
        'evidence_payload_sha256',
        'synthetic_dependency',
        'secrets_embedded',
    ];

    public function __construct(
        private FinalShiftCloseDurableRuntimeDependencyEnvelope $dependencyQualifier,
        private FinalShiftCloseDurableRuntimeCapabilityEvidence $capabilityQualifier,
    ) {}

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $capabilityEvidence
     * @param array<string, mixed> $observations
     * @return list<string>
     */
    public function violations(
        array $selection,
        array $attestation,
        array $capabilityEvidence,
        array $observations,
    ): array {
        $violations = [];
        $capabilityQualification = null;

        try {
            $capabilityQualification = $this->capabilityQualifier->qualify(
                $selection,
                $attestation,
                $capabilityEvidence,
            );
        } catch (InvalidArgumentException|JsonException) {
            $violations[] = 'target_bound_capability_evidence_unqualified';
        }

        $targetBinding = $this->capabilityQualifier->targetBindingSha256($selection);
        if (! $this->lowerHex64($targetBinding)) {
            $violations[] = 'target_binding_unavailable';
        }

        foreach (array_diff(self::REQUIRED_OBSERVATION_FIELDS, array_keys($observations)) as $field) {
            $violations[] = 'missing_dependency_observation_field:'.$field;
        }
        foreach (array_diff(array_keys($observations), self::REQUIRED_OBSERVATION_FIELDS) as $field) {
            $violations[] = 'unexpected_dependency_observation_field:'.$field;
        }

        if (($observations['schema_version'] ?? null) !== 1) {
            $violations[] = 'dependency_observation_schema_version_invalid';
        }
        if (($observations['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'dependency_observation_feature_invalid';
        }
        if (($observations['observation_state'] ?? null) !== self::OBSERVATION_STATE) {
            $violations[] = 'dependency_observation_state_invalid';
        }
        if (($observations['synthetic_mixing'] ?? null) !== false) {
            $violations[] = 'synthetic_dependency_observation_mixing_forbidden';
        }
        if (($observations['secrets_embedded'] ?? null) !== false) {
            $violations[] = 'dependency_observations_must_not_embed_secrets';
        }

        $components = is_array($observations['components'] ?? null)
            ? $observations['components']
            : [];
        $actualNames = array_keys($components);
        $expectedNames = array_keys(self::REQUIRED_COMPONENTS);
        sort($actualNames, SORT_STRING);
        sort($expectedNames, SORT_STRING);
        if ($actualNames !== $expectedNames) {
            $violations[] = 'dependency_observation_component_set_invalid';
        }

        $selectedTarget = is_array($selection['selected_target'] ?? null)
            ? $selection['selected_target']
            : [];

        foreach (self::REQUIRED_COMPONENTS as $component => $expectedPath) {
            $record = is_array($components[$component] ?? null)
                ? $components[$component]
                : [];

            foreach (array_diff(self::REQUIRED_COMPONENT_OBSERVATION_FIELDS, array_keys($record)) as $field) {
                $violations[] = 'missing_dependency_component_observation_field:'.$component.':'.$field;
            }
            foreach (array_diff(array_keys($record), self::REQUIRED_COMPONENT_OBSERVATION_FIELDS) as $field) {
                $violations[] = 'unexpected_dependency_component_observation_field:'.$component.':'.$field;
            }

            if (($record['state'] ?? null) !== 'VERIFIED') {
                $violations[] = 'dependency_component_observation_not_verified:'.$component;
            }
            if (($record['source_path'] ?? null) !== $expectedPath) {
                $violations[] = 'dependency_component_observation_source_path_invalid:'.$component;
            }
            foreach ([
                'environment_id',
                'runtime_class',
                'exact_running_source_commit',
                'exact_running_artifact_sha256',
            ] as $field) {
                if (($record[$field] ?? null) !== ($selectedTarget[$field] ?? null)) {
                    $violations[] = 'dependency_component_observation_target_mismatch:'.$component.':'.$field;
                }
            }
            if (! $this->lowerHex64($record['evidence_payload_sha256'] ?? null)) {
                $violations[] = 'dependency_component_observation_payload_sha256_invalid:'.$component;
            }
            if (($record['synthetic_dependency'] ?? null) !== false) {
                $violations[] = 'synthetic_dependency_component_observation_forbidden:'.$component;
            }
            if (($record['secrets_embedded'] ?? null) !== false) {
                $violations[] = 'dependency_component_observation_must_not_embed_secrets:'.$component;
            }
        }

        if ($violations === [] && is_array($capabilityQualification)) {
            $evidence = $this->buildEvidence(
                $selection,
                $capabilityQualification,
                $observations,
            );
            if ($this->dependencyQualifier->violations(
                $selection,
                $attestation,
                $capabilityEvidence,
                $evidence,
            ) !== []) {
                $violations[] = 'produced_dependency_evidence_fails_sprint150_qualification';
            }
        }

        return array_values(array_unique($violations));
    }

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $capabilityEvidence
     * @param array<string, mixed> $observations
     */
    public function canProduce(
        array $selection,
        array $attestation,
        array $capabilityEvidence,
        array $observations,
    ): bool {
        return $this->violations($selection, $attestation, $capabilityEvidence, $observations) === [];
    }

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $capabilityEvidence
     * @param array<string, mixed> $observations
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function produce(
        array $selection,
        array $attestation,
        array $capabilityEvidence,
        array $observations,
    ): array {
        $violations = $this->violations($selection, $attestation, $capabilityEvidence, $observations);
        if ($violations !== []) {
            throw new InvalidArgumentException(
                'final_shift_close_durable_runtime_dependency_envelope_evidence_producer_rejected:'.implode(',', $violations),
            );
        }

        $capabilityQualification = $this->capabilityQualifier->qualify(
            $selection,
            $attestation,
            $capabilityEvidence,
        );
        $evidence = $this->buildEvidence($selection, $capabilityQualification, $observations);
        $qualification = $this->dependencyQualifier->qualify(
            $selection,
            $attestation,
            $capabilityEvidence,
            $evidence,
        );

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
                'dependency_envelope_sha256' => $qualification['dependency_envelope_sha256'],
            ])),
            'activation_authority_state' => 'NOT_GRANTED',
            'feature_activation_state' => 'INACTIVE',
            'runtime_allowlist_change' => 'NOT_IMPLEMENTED',
            'secrets_embedded' => false,
        ];
    }

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $capabilityQualification
     * @param array<string, mixed> $observations
     * @return array<string, mixed>
     */
    private function buildEvidence(
        array $selection,
        array $capabilityQualification,
        array $observations,
    ): array {
        $target = is_array($selection['selected_target'] ?? null)
            ? $selection['selected_target']
            : [];
        $targetBinding = $this->capabilityQualifier->targetBindingSha256($selection);
        $components = [];

        foreach (self::REQUIRED_COMPONENTS as $component => $sourcePath) {
            $record = is_array($observations['components'][$component] ?? null)
                ? $observations['components'][$component]
                : [];
            $identity = [
                'feature' => self::FEATURE,
                'component' => $component,
                'state' => 'VERIFIED',
                'source_path' => $sourcePath,
                'environment_id' => $target['environment_id'] ?? null,
                'runtime_class' => $target['runtime_class'] ?? null,
                'exact_running_source_commit' => $target['exact_running_source_commit'] ?? null,
                'exact_running_artifact_sha256' => $target['exact_running_artifact_sha256'] ?? null,
                'selection_fingerprint_sha256' => $selection['selection_fingerprint_sha256'] ?? null,
                'target_binding_sha256' => $targetBinding,
                'capability_evidence_bundle_sha256' => $capabilityQualification['capability_evidence_bundle_sha256'] ?? null,
                'evidence_payload_sha256' => $record['evidence_payload_sha256'] ?? null,
                'synthetic_dependency' => false,
            ];

            $components[$component] = [
                'state' => 'VERIFIED',
                'source_path' => $sourcePath,
                'runtime_class' => $target['runtime_class'] ?? null,
                'evidence_sha256' => hash('sha256', $this->canonicalJson($identity)),
                'target_binding_sha256' => $targetBinding,
                'synthetic_dependency' => false,
                'secrets_embedded' => false,
            ];
        }

        return [
            'schema_version' => 1,
            'feature' => FinalShiftCloseDurableRuntimeDependencyEnvelope::FEATURE,
            'evidence_state' => FinalShiftCloseDurableRuntimeDependencyEnvelope::EVIDENCE_STATE,
            'environment_id' => $target['environment_id'] ?? null,
            'runtime_class' => $target['runtime_class'] ?? null,
            'target_binding_sha256' => $targetBinding,
            'capability_evidence_bundle_sha256' => $capabilityQualification['capability_evidence_bundle_sha256'] ?? null,
            'components' => $components,
            'synthetic_mixing' => false,
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
