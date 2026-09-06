<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;
use JsonException;

// Author by Lab | zefry
final class FinalShiftCloseDurableRuntimeAttestationProvenance
{
    public const FEATURE = 'final-shift-close';
    public const EVIDENCE_SOURCE = 'GITHUB_ACTIONS_PROTECTED_ENVIRONMENT';
    public const EVIDENCE_STATE = 'VERIFIED';
    public const PRODUCER_REPOSITORY = 'labzefry/oneQay';
    public const PRODUCER_WORKFLOW_PATH = '.github/workflows/final-shift-close-durable-runtime-attestation.yml';
    public const PRODUCER_ENVIRONMENT = 'final-shift-close-durable-runtime-attestation';
    public const PRODUCER_REF = 'refs/heads/main';
    public const EVIDENCE_STATUS_CONTEXT = 'final-shift-close-durable-runtime-attestation-evidence';
    public const EVIDENCE_STATUS_STATE = 'success';

    private const REQUIRED_FIELDS = [
        'schema_version',
        'feature',
        'evidence_source',
        'evidence_state',
        'producer_repository',
        'producer_workflow_path',
        'producer_environment',
        'producer_ref',
        'producer_source_commit',
        'producer_run_id',
        'producer_run_attempt',
        'attestation_sha256',
        'environment_id',
        'runtime_class',
        'exact_running_source_commit',
        'exact_running_artifact_sha256',
        'evidence_status_context',
        'evidence_status_state',
        'secrets_embedded',
    ];

    /**
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $provenance
     * @return list<string>
     * @throws JsonException
     */
    public function violations(array $attestation, array $provenance): array
    {
        $violations = [];

        foreach (array_diff(self::REQUIRED_FIELDS, array_keys($provenance)) as $field) {
            $violations[] = 'missing_field:'.$field;
        }
        foreach (array_diff(array_keys($provenance), self::REQUIRED_FIELDS) as $field) {
            $violations[] = 'unexpected_field:'.$field;
        }

        if (($provenance['schema_version'] ?? null) !== 1) {
            $violations[] = 'schema_version_invalid';
        }
        if (($provenance['feature'] ?? null) !== self::FEATURE) {
            $violations[] = 'feature_invalid';
        }
        if (($provenance['evidence_source'] ?? null) !== self::EVIDENCE_SOURCE) {
            $violations[] = 'evidence_source_invalid';
        }
        if (($provenance['evidence_state'] ?? null) !== self::EVIDENCE_STATE) {
            $violations[] = 'evidence_state_invalid';
        }
        if (($provenance['producer_repository'] ?? null) !== self::PRODUCER_REPOSITORY) {
            $violations[] = 'producer_repository_invalid';
        }
        if (($provenance['producer_workflow_path'] ?? null) !== self::PRODUCER_WORKFLOW_PATH) {
            $violations[] = 'producer_workflow_path_invalid';
        }
        if (($provenance['producer_environment'] ?? null) !== self::PRODUCER_ENVIRONMENT) {
            $violations[] = 'producer_environment_invalid';
        }
        if (($provenance['producer_ref'] ?? null) !== self::PRODUCER_REF) {
            $violations[] = 'producer_ref_invalid';
        }

        if (! $this->sha40($provenance['producer_source_commit'] ?? null)) {
            $violations[] = 'producer_source_commit_invalid';
        }
        if (! is_int($provenance['producer_run_id'] ?? null) || $provenance['producer_run_id'] < 1) {
            $violations[] = 'producer_run_id_invalid';
        }
        if (! is_int($provenance['producer_run_attempt'] ?? null) || $provenance['producer_run_attempt'] < 1) {
            $violations[] = 'producer_run_attempt_invalid';
        }

        $expectedAttestationSha256 = $this->attestationSha256($attestation);
        if (($provenance['attestation_sha256'] ?? null) !== $expectedAttestationSha256) {
            $violations[] = 'attestation_sha256_mismatch';
        }

        foreach ([
            'environment_id',
            'runtime_class',
            'exact_running_source_commit',
            'exact_running_artifact_sha256',
        ] as $field) {
            if (($provenance[$field] ?? null) !== ($attestation[$field] ?? null)) {
                $violations[] = 'attestation_binding_mismatch:'.$field;
            }
        }

        if (($provenance['evidence_status_context'] ?? null) !== self::EVIDENCE_STATUS_CONTEXT) {
            $violations[] = 'evidence_status_context_invalid';
        }
        if (($provenance['evidence_status_state'] ?? null) !== self::EVIDENCE_STATUS_STATE) {
            $violations[] = 'evidence_status_state_invalid';
        }
        if (($provenance['secrets_embedded'] ?? null) !== false) {
            $violations[] = 'provenance_must_not_embed_secrets';
        }

        return array_values(array_unique($violations));
    }

    /**
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $provenance
     * @throws JsonException
     */
    public function qualifies(array $attestation, array $provenance): bool
    {
        return $this->violations($attestation, $provenance) === [];
    }

    /**
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $provenance
     * @throws JsonException
     */
    public function requireQualified(array $attestation, array $provenance): void
    {
        $violations = $this->violations($attestation, $provenance);
        if ($violations !== []) {
            throw new InvalidArgumentException(
                'final_shift_close_durable_runtime_attestation_provenance_rejected:'.implode(',', $violations),
            );
        }
    }

    /** @param array<string, mixed> $attestation */
    public function attestationSha256(array $attestation): string
    {
        $canonical = $attestation;
        ksort($canonical, SORT_STRING);

        return hash('sha256', json_encode(
            $canonical,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        ));
    }

    private function sha40(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/\A[0-9a-f]{40}\z/', $value) === 1
            && $value !== str_repeat('0', 40);
    }
}
