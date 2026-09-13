<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;
use JsonException;

// Author by Lab | zefry
final readonly class FinalShiftCloseDurableRuntimeDependencyEnvelope
{
    public const FEATURE = 'final-shift-close';
    public const EVIDENCE_STATE = 'TARGET_BOUND_DURABLE_RUNTIME_DEPENDENCY_ENVELOPE';
    public const QUALIFICATION_STATE = 'FULL_DURABLE_DEPENDENCY_ENVELOPE_QUALIFIED_NOT_ALLOWLISTED';

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

    private const REQUIRED_EVIDENCE_FIELDS = [
        'schema_version',
        'feature',
        'evidence_state',
        'environment_id',
        'runtime_class',
        'target_binding_sha256',
        'capability_evidence_bundle_sha256',
        'components',
        'synthetic_mixing',
        'secrets_embedded',
    ];

    private const REQUIRED_COMPONENT_FIELDS = [
        'state',
        'source_path',
        'runtime_class',
        'evidence_sha256',
        'target_binding_sha256',
        'synthetic_dependency',
        'secrets_embedded',
    ];

    public function __construct(
        private FinalShiftCloseDurableRuntimeCapabilityEvidence $capabilityEvidence,
    ) {}

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $capabilityEvidence
     * @param array<string, mixed> $dependencyEvidence
     * @return list<string>
     */
    public function violations(
        array $selection,
        array $attestation,
        array $capabilityEvidence,
        array $dependencyEvidence,
    ): array {
        $violations = [];
        $capabilityQualification = null;

        try {
            $capabilityQualification = $this->capabilityEvidence->qualify(
                $selection,
                $attestation,
                $capabilityEvidence,
            );
        } catch (InvalidArgumentException|JsonException) {
            $violations[] = 'target_bound_capability_evidence_unqualified';
        }

        foreach (array_diff(self::REQUIRED_EVIDENCE_FIELDS, array_keys($dependencyEvidence)) as $field) {
            $violations[] = 'missing_dependency_evidence_field:'.$field;
        }
        foreach (array_diff(array_keys($dependencyEvidence), self::REQUIRED_EVIDENCE_FIELDS) as $field) {
            $violations[] = 'unexpected_dependency_evidence_field:'.$field;
        }

        if (($dependencyEvidence['schema_version'] ?? null) !== 1) {
            $violations[] = 'dependency_evidence_schema_version_invalid';
        }
        if (($dependencyEvidence['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'dependency_evidence_feature_invalid';
        }
        if (($dependencyEvidence['evidence_state'] ?? null) !== self::EVIDENCE_STATE) {
            $violations[] = 'dependency_evidence_state_invalid';
        }
        if (($dependencyEvidence['synthetic_mixing'] ?? null) !== false) {
            $violations[] = 'synthetic_dependency_mixing_forbidden';
        }
        if (($dependencyEvidence['secrets_embedded'] ?? null) !== false) {
            $violations[] = 'dependency_evidence_must_not_embed_secrets';
        }

        $target = is_array($selection['selected_target'] ?? null) ? $selection['selected_target'] : [];
        if (($dependencyEvidence['environment_id'] ?? null) !== ($target['environment_id'] ?? null)) {
            $violations[] = 'dependency_environment_identity_mismatch';
        }
        if (($dependencyEvidence['runtime_class'] ?? null) !== ($target['runtime_class'] ?? null)) {
            $violations[] = 'dependency_runtime_class_mismatch';
        }

        $targetBindingSha256 = $this->capabilityEvidence->targetBindingSha256($selection);
        if (! $this->lowerHex64($targetBindingSha256)
            || ($dependencyEvidence['target_binding_sha256'] ?? null) !== $targetBindingSha256) {
            $violations[] = 'dependency_target_binding_mismatch';
        }

        if (! is_array($capabilityQualification)
            || ($dependencyEvidence['capability_evidence_bundle_sha256'] ?? null)
                !== ($capabilityQualification['capability_evidence_bundle_sha256'] ?? null)) {
            $violations[] = 'dependency_capability_bundle_binding_mismatch';
        }

        $components = is_array($dependencyEvidence['components'] ?? null)
            ? $dependencyEvidence['components']
            : [];
        $actualComponents = array_keys($components);
        $expectedComponents = array_keys(self::REQUIRED_COMPONENTS);
        sort($actualComponents, SORT_STRING);
        sort($expectedComponents, SORT_STRING);
        if ($actualComponents !== $expectedComponents) {
            $violations[] = 'dependency_component_set_invalid';
        }

        foreach (self::REQUIRED_COMPONENTS as $component => $expectedPath) {
            $record = is_array($components[$component] ?? null) ? $components[$component] : [];

            foreach (array_diff(self::REQUIRED_COMPONENT_FIELDS, array_keys($record)) as $field) {
                $violations[] = 'missing_dependency_component_field:'.$component.':'.$field;
            }
            foreach (array_diff(array_keys($record), self::REQUIRED_COMPONENT_FIELDS) as $field) {
                $violations[] = 'unexpected_dependency_component_field:'.$component.':'.$field;
            }

            if (($record['state'] ?? null) !== 'VERIFIED') {
                $violations[] = 'dependency_component_not_verified:'.$component;
            }
            if (($record['source_path'] ?? null) !== $expectedPath) {
                $violations[] = 'dependency_component_source_path_invalid:'.$component;
            }
            if (($record['runtime_class'] ?? null) !== ($target['runtime_class'] ?? null)) {
                $violations[] = 'dependency_component_runtime_class_mismatch:'.$component;
            }
            if (! $this->lowerHex64($record['evidence_sha256'] ?? null)) {
                $violations[] = 'dependency_component_evidence_sha256_invalid:'.$component;
            }
            if (($record['target_binding_sha256'] ?? null) !== $targetBindingSha256) {
                $violations[] = 'dependency_component_target_binding_mismatch:'.$component;
            }
            if (($record['synthetic_dependency'] ?? null) !== false) {
                $violations[] = 'synthetic_dependency_forbidden:'.$component;
            }
            if (($record['secrets_embedded'] ?? null) !== false) {
                $violations[] = 'dependency_component_must_not_embed_secrets:'.$component;
            }
        }

        return array_values(array_unique($violations));
    }

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $capabilityEvidence
     * @param array<string, mixed> $dependencyEvidence
     */
    public function qualifies(
        array $selection,
        array $attestation,
        array $capabilityEvidence,
        array $dependencyEvidence,
    ): bool {
        return $this->violations($selection, $attestation, $capabilityEvidence, $dependencyEvidence) === [];
    }

    /**
     * @param array<string, mixed> $selection
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $capabilityEvidence
     * @param array<string, mixed> $dependencyEvidence
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function qualify(
        array $selection,
        array $attestation,
        array $capabilityEvidence,
        array $dependencyEvidence,
    ): array {
        $violations = $this->violations($selection, $attestation, $capabilityEvidence, $dependencyEvidence);
        if ($violations !== []) {
            throw new InvalidArgumentException(
                'final_shift_close_durable_runtime_dependency_envelope_rejected:'.implode(',', $violations),
            );
        }

        $componentEvidenceSha256 = [];
        foreach (self::REQUIRED_COMPONENTS as $component => $_path) {
            $componentEvidenceSha256[$component] = $dependencyEvidence['components'][$component]['evidence_sha256'];
        }

        return [
            'schema_version' => 1,
            'feature' => self::FEATURE,
            'qualification_state' => self::QUALIFICATION_STATE,
            'environment_id' => $dependencyEvidence['environment_id'],
            'runtime_class' => $dependencyEvidence['runtime_class'],
            'target_binding_sha256' => $dependencyEvidence['target_binding_sha256'],
            'capability_evidence_bundle_sha256' => $dependencyEvidence['capability_evidence_bundle_sha256'],
            'component_evidence_sha256' => $componentEvidenceSha256,
            'dependency_envelope_sha256' => hash('sha256', $this->canonicalJson($dependencyEvidence)),
            'runtime_allowlist_change' => FinalShiftCloseDurableRuntimeTargetSelection::RUNTIME_ALLOWLIST_CHANGE,
            'activation_authority_state' => FinalShiftCloseDurableRuntimeTargetSelection::ACTIVATION_AUTHORITY_STATE,
            'feature_activation_state' => FinalShiftCloseDurableRuntimeTargetSelection::FEATURE_ACTIVATION_STATE,
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
