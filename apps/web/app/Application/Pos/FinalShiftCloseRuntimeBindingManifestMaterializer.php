<?php

declare(strict_types=1);

namespace App\Application\Pos;

use RuntimeException;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeBindingManifestMaterializer
{
    private const MAX_SELECTION_BYTES = 65536;

    private const SELECTED_TARGET_KEYS = [
        'environment_id',
        'exact_running_artifact_sha256',
        'exact_running_source_commit',
        'readiness_attestation_sha256',
        'selection_fingerprint_sha256',
        'trusted_ingestion',
        'runtime_class',
    ];

    private const TRUSTED_INGESTION_KEYS = [
        'ingestion_fingerprint_sha256',
        'run_attempt',
        'run_id',
    ];

    public function __construct(
        private readonly FinalShiftCloseRuntimeBindingManifestWriter $writer,
        private readonly string $selectionPath,
    ) {}

    /** @return array{operation_id:string,selection_fingerprint_sha256:string,materialization_state:string} */
    public function materialize(FinalShiftCloseRuntimeBindingManifestMaterializationRequest $request): array
    {
        $selection = $this->readCanonicalSelection();

        if (($selection['selection_state'] ?? null) !== 'SELECTED_NOT_AUTHORIZED') {
            throw new RuntimeException('Canonical durable target is not selected.');
        }

        $selected = $selection['selected_target'] ?? null;
        if (! is_array($selected)) {
            throw new RuntimeException('Canonical selected target is unavailable.');
        }

        $selectedKeys = array_keys($selected);
        sort($selectedKeys, SORT_STRING);
        $expectedKeys = self::SELECTED_TARGET_KEYS;
        sort($expectedKeys, SORT_STRING);
        if ($selectedKeys !== $expectedKeys) {
            throw new RuntimeException('Canonical selected target field set is invalid.');
        }

        $ingestion = $selected['trusted_ingestion'] ?? null;
        if (! is_array($ingestion)) {
            throw new RuntimeException('Canonical trusted ingestion is invalid.');
        }
        $ingestionKeys = array_keys($ingestion);
        sort($ingestionKeys, SORT_STRING);
        if ($ingestionKeys !== self::TRUSTED_INGESTION_KEYS) {
            throw new RuntimeException('Canonical trusted ingestion field set is invalid.');
        }

        $selectionFingerprint = $selected['selection_fingerprint_sha256'] ?? null;
        if (! is_string($selectionFingerprint)
            || preg_match('/\A[0-9a-f]{64}\z/D', $selectionFingerprint) !== 1
            || ! hash_equals($selectionFingerprint, $request->expectedSelectionFingerprintSha256)
        ) {
            throw new RuntimeException('Canonical selection fingerprint does not match request.');
        }

        $manifest = new FinalShiftCloseRuntimeBindingManifest([
            'schema_version' => 1,
            'feature' => 'final-shift-close',
            'selection_state' => 'SELECTED_NOT_AUTHORIZED',
            'environment_id' => $selected['environment_id'] ?? null,
            'runtime_class' => $selected['runtime_class'] ?? null,
            'exact_running_source_commit' => $selected['exact_running_source_commit'] ?? null,
            'exact_running_artifact_sha256' => $selected['exact_running_artifact_sha256'] ?? null,
            'readiness_attestation_sha256' => $selected['readiness_attestation_sha256'] ?? null,
            'selection_fingerprint_sha256' => $selectionFingerprint,
            'trusted_ingestion' => [
                'run_id' => $ingestion['run_id'] ?? null,
                'run_attempt' => $ingestion['run_attempt'] ?? null,
                'ingestion_fingerprint_sha256' => $ingestion['ingestion_fingerprint_sha256'] ?? null,
            ],
            'secrets_embedded' => false,
        ]);

        $this->writer->write($manifest);

        return [
            'operation_id' => $request->operationId,
            'selection_fingerprint_sha256' => $selectionFingerprint,
            'materialization_state' => 'MATERIALIZED_SELECTED_TARGET_BINDING_MANIFEST',
        ];
    }

    /** @return array<string,mixed> */
    private function readCanonicalSelection(): array
    {
        $path = $this->selectionPath;
        if ($path === '' || ! str_starts_with($path, DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('Canonical durable target selection path is invalid.');
        }

        clearstatcache(true, $path);
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            throw new RuntimeException('Canonical durable target selection is unavailable.');
        }

        $size = filesize($path);
        if (! is_int($size) || $size < 2 || $size > self::MAX_SELECTION_BYTES) {
            throw new RuntimeException('Canonical durable target selection size is invalid.');
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException('Canonical durable target selection cannot be read.');
        }

        $selection = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        if (! is_array($selection)) {
            throw new RuntimeException('Canonical durable target selection must be an object.');
        }

        return $selection;
    }
}
