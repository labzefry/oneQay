<?php

declare(strict_types=1);

namespace App\Application\Pos;

use JsonException;

// Author by Lab | zefry
final readonly class FinalShiftCloseDurableRuntimeAttestationIngestion
{
    public const FEATURE = 'final-shift-close';
    public const INGESTION_STATE = 'ACCEPTED_NOT_SELECTED';
    public const CANONICAL_SELECTION_STATE = 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET';
    public const ACTIVATION_AUTHORITY_STATE = 'NOT_GRANTED';
    public const FEATURE_ACTIVATION_STATE = 'INACTIVE';
    public const RUNTIME_ALLOWLIST_CHANGE = 'NOT_IMPLEMENTED';
    public const PERSISTENCE_STATE = 'NOT_PERFORMED';

    public function __construct(
        private FinalShiftCloseDurableRuntimeReadiness $readiness,
        private FinalShiftCloseDurableRuntimeAttestationProvenance $provenance,
    ) {}

    /**
     * Build a deterministic, unpersisted ingestion record from a qualified runtime
     * readiness attestation and matching trusted-provenance envelope.
     *
     * This method intentionally does not select a target, persist canonical state,
     * widen runtime allowlists, grant authority, deploy, or activate Final Shift Close.
     *
     * @param array<string, mixed> $attestation
     * @param array<string, mixed> $provenance
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function ingest(array $attestation, array $provenance): array
    {
        $this->readiness->requireQualified($attestation);
        $this->provenance->requireQualified($attestation, $provenance);

        $attestationSha256 = $this->provenance->attestationSha256($attestation);
        $provenanceSha256 = $this->canonicalSha256($provenance);

        $environmentId = (string) $attestation['environment_id'];
        $runtimeClass = (string) $attestation['runtime_class'];
        $sourceCommit = (string) $attestation['exact_running_source_commit'];
        $artifactSha256 = (string) $attestation['exact_running_artifact_sha256'];

        $ingestionFingerprint = hash('sha256', implode('|', [
            self::FEATURE,
            self::INGESTION_STATE,
            $environmentId,
            $runtimeClass,
            $sourceCommit,
            $artifactSha256,
            $attestationSha256,
            $provenanceSha256,
        ]));

        return [
            'schema_version' => 1,
            'feature' => self::FEATURE,
            'ingestion_state' => self::INGESTION_STATE,
            'candidate_target' => [
                'environment_id' => $environmentId,
                'runtime_class' => $runtimeClass,
                'exact_running_source_commit' => $sourceCommit,
                'exact_running_artifact_sha256' => $artifactSha256,
            ],
            'readiness_attestation_sha256' => $attestationSha256,
            'provenance_sha256' => $provenanceSha256,
            'ingestion_fingerprint_sha256' => $ingestionFingerprint,
            'canonical_selection_state' => self::CANONICAL_SELECTION_STATE,
            'selected_target' => null,
            'activation_authority_state' => self::ACTIVATION_AUTHORITY_STATE,
            'feature_activation_state' => self::FEATURE_ACTIVATION_STATE,
            'runtime_allowlist_change' => self::RUNTIME_ALLOWLIST_CHANGE,
            'persistence_state' => self::PERSISTENCE_STATE,
        ];
    }

    /** @param array<string, mixed> $value */
    private function canonicalSha256(array $value): string
    {
        ksort($value, SORT_STRING);

        return hash('sha256', json_encode(
            $value,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        ));
    }
}
