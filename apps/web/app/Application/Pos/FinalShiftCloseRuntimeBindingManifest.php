<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeBindingManifest
{
    private const KEYS = [
        'environment_id',
        'exact_running_artifact_sha256',
        'exact_running_source_commit',
        'feature',
        'readiness_attestation_sha256',
        'schema_version',
        'secrets_embedded',
        'selection_fingerprint_sha256',
        'selection_state',
        'trusted_ingestion',
        'runtime_class',
    ];

    private const TRUSTED_INGESTION_KEYS = [
        'ingestion_fingerprint_sha256',
        'run_attempt',
        'run_id',
    ];

    /** @var array<string,mixed> */
    private array $payload;

    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(array $payload)
    {
        $this->assertExactPayload($payload);
        $this->payload = $payload;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'schema_version' => $this->payload['schema_version'],
            'feature' => $this->payload['feature'],
            'selection_state' => $this->payload['selection_state'],
            'environment_id' => $this->payload['environment_id'],
            'runtime_class' => $this->payload['runtime_class'],
            'exact_running_source_commit' => $this->payload['exact_running_source_commit'],
            'exact_running_artifact_sha256' => $this->payload['exact_running_artifact_sha256'],
            'readiness_attestation_sha256' => $this->payload['readiness_attestation_sha256'],
            'selection_fingerprint_sha256' => $this->payload['selection_fingerprint_sha256'],
            'trusted_ingestion' => [
                'run_id' => $this->payload['trusted_ingestion']['run_id'],
                'run_attempt' => $this->payload['trusted_ingestion']['run_attempt'],
                'ingestion_fingerprint_sha256' => $this->payload['trusted_ingestion']['ingestion_fingerprint_sha256'],
            ],
            'secrets_embedded' => false,
        ];
    }

    public function toCanonicalJson(): string
    {
        return json_encode(
            $this->toArray(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        )."\n";
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function assertExactPayload(array $payload): void
    {
        $keys = array_keys($payload);
        sort($keys, SORT_STRING);
        $expected = self::KEYS;
        sort($expected, SORT_STRING);
        if ($keys !== $expected) {
            throw new InvalidArgumentException('Runtime binding manifest field set is invalid.');
        }

        $ingestion = $payload['trusted_ingestion'] ?? null;
        if (! is_array($ingestion)) {
            throw new InvalidArgumentException('Runtime binding trusted ingestion is invalid.');
        }

        $ingestionKeys = array_keys($ingestion);
        sort($ingestionKeys, SORT_STRING);
        if ($ingestionKeys !== self::TRUSTED_INGESTION_KEYS) {
            throw new InvalidArgumentException('Runtime binding trusted ingestion field set is invalid.');
        }

        if (($payload['schema_version'] ?? null) !== 1
            || ($payload['feature'] ?? null) !== 'final-shift-close'
            || ($payload['selection_state'] ?? null) !== 'SELECTED_NOT_AUTHORIZED'
            || ($payload['secrets_embedded'] ?? null) !== false
        ) {
            throw new InvalidArgumentException('Runtime binding manifest state is invalid.');
        }

        $environment = $payload['environment_id'] ?? null;
        $runtime = $payload['runtime_class'] ?? null;
        if (! is_string($environment) || preg_match('/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D', $environment) !== 1) {
            throw new InvalidArgumentException('Runtime binding environment identity is invalid.');
        }
        if (! is_string($runtime) || preg_match('/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D', $runtime) !== 1) {
            throw new InvalidArgumentException('Runtime binding runtime class is invalid.');
        }
        if (in_array($runtime, ['local', 'test', 'testing', 'ci', 'preview', 'synthetic-preview', 'production', 'prod'], true)) {
            throw new InvalidArgumentException('Runtime binding runtime class is not eligible.');
        }

        $source = $payload['exact_running_source_commit'] ?? null;
        if (! is_string($source) || preg_match('/\A[0-9a-f]{40}\z/D', $source) !== 1) {
            throw new InvalidArgumentException('Runtime binding source commit is invalid.');
        }

        foreach ([
            $payload['exact_running_artifact_sha256'] ?? null,
            $payload['readiness_attestation_sha256'] ?? null,
            $payload['selection_fingerprint_sha256'] ?? null,
            $ingestion['ingestion_fingerprint_sha256'] ?? null,
        ] as $sha256) {
            if (! is_string($sha256) || preg_match('/\A[0-9a-f]{64}\z/D', $sha256) !== 1) {
                throw new InvalidArgumentException('Runtime binding SHA-256 provenance is invalid.');
            }
        }

        if (! is_int($ingestion['run_id'] ?? null) || ($ingestion['run_id'] ?? 0) < 1
            || ! is_int($ingestion['run_attempt'] ?? null) || ($ingestion['run_attempt'] ?? 0) < 1
        ) {
            throw new InvalidArgumentException('Runtime binding ingestion provenance is invalid.');
        }
    }
}
