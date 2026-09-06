<?php

declare(strict_types=1);

namespace App\Application\Pos;

use InvalidArgumentException;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeBindingMaterializationRequest
{
    private const KEYS = [
        'manifest',
        'manifest_sha256',
        'operation',
        'operation_id',
        'schema_version',
    ];

    private const OPERATION = 'final-shift-close.runtime-binding.materialize';

    private FinalShiftCloseRuntimeBindingManifest $manifest;

    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(private readonly array $payload)
    {
        $keys = array_keys($payload);
        sort($keys, SORT_STRING);
        $expected = self::KEYS;
        sort($expected, SORT_STRING);
        if ($keys !== $expected) {
            throw new InvalidArgumentException('Runtime binding materialization request field set is invalid.');
        }

        if (($payload['schema_version'] ?? null) !== 1
            || ($payload['operation'] ?? null) !== self::OPERATION
            || ! is_array($payload['manifest'] ?? null)
        ) {
            throw new InvalidArgumentException('Runtime binding materialization request state is invalid.');
        }

        $this->manifest = new FinalShiftCloseRuntimeBindingManifest($payload['manifest']);
        $canonical = $this->manifest->toCanonicalJson();
        $expectedSha256 = hash('sha256', $canonical);
        $manifestSha256 = $payload['manifest_sha256'] ?? null;
        if (! is_string($manifestSha256)
            || preg_match('/\A[0-9a-f]{64}\z/D', $manifestSha256) !== 1
            || ! hash_equals($expectedSha256, $manifestSha256)
        ) {
            throw new InvalidArgumentException('Runtime binding materialization manifest fingerprint is invalid.');
        }

        $expectedOperationId = 'fscbind_'.substr($expectedSha256, 0, 32);
        if (($payload['operation_id'] ?? null) !== $expectedOperationId) {
            throw new InvalidArgumentException('Runtime binding materialization operation identity is invalid.');
        }
    }

    public function operationId(): string
    {
        return (string) $this->payload['operation_id'];
    }

    public function manifestSha256(): string
    {
        return (string) $this->payload['manifest_sha256'];
    }

    public function manifest(): FinalShiftCloseRuntimeBindingManifest
    {
        return $this->manifest;
    }
}
