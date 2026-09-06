<?php

declare(strict_types=1);

namespace App\Application\Pos;

use JsonException;

// Author by Lab | zefry
final readonly class FinalShiftCloseDurableRuntimeTargetSelection
{
    public const FEATURE = 'final-shift-close';
    public const SELECTION_STATE = 'SELECTED_NOT_AUTHORIZED';
    public const ACTIVATION_AUTHORITY_STATE = 'NOT_GRANTED';
    public const FEATURE_ACTIVATION_STATE = 'INACTIVE';
    public const RUNTIME_ALLOWLIST_CHANGE = 'NOT_IMPLEMENTED';

    public function __construct(
        private FinalShiftCloseDurableRuntimeReadiness $readiness,
    ) {}

    /**
     * Build a deterministic candidate-selection record from a qualified attestation.
     *
     * This method is intentionally pure. It does not persist selection state, mutate
     * runtime configuration, widen runtime allowlists, or activate Final Shift Close.
     *
     * @param array<string, mixed> $attestation
     * @return array<string, mixed>
     * @throws JsonException
     */
    public function select(array $attestation): array
    {
        $this->readiness->requireQualified($attestation);

        $canonicalAttestation = $attestation;
        ksort($canonicalAttestation, SORT_STRING);

        $attestationJson = json_encode(
            $canonicalAttestation,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
        $attestationSha256 = hash('sha256', $attestationJson);

        $environmentId = (string) $attestation['environment_id'];
        $runtimeClass = (string) $attestation['runtime_class'];
        $sourceCommit = (string) $attestation['exact_running_source_commit'];
        $artifactSha256 = (string) $attestation['exact_running_artifact_sha256'];

        $selectionFingerprint = hash('sha256', implode('|', [
            self::FEATURE,
            self::SELECTION_STATE,
            $environmentId,
            $runtimeClass,
            $sourceCommit,
            $artifactSha256,
            $attestationSha256,
        ]));

        return [
            'schema_version' => 1,
            'feature' => self::FEATURE,
            'selection_state' => self::SELECTION_STATE,
            'selected_target' => [
                'environment_id' => $environmentId,
                'runtime_class' => $runtimeClass,
                'exact_running_source_commit' => $sourceCommit,
                'exact_running_artifact_sha256' => $artifactSha256,
                'readiness_attestation_sha256' => $attestationSha256,
            ],
            'selection_fingerprint_sha256' => $selectionFingerprint,
            'activation_authority_state' => self::ACTIVATION_AUTHORITY_STATE,
            'feature_activation_state' => self::FEATURE_ACTIVATION_STATE,
            'runtime_allowlist_change' => self::RUNTIME_ALLOWLIST_CHANGE,
        ];
    }
}
