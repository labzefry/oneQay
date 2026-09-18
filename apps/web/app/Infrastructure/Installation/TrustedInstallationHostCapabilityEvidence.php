<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use Throwable;

/**
 * Read-only verifier for operator-provided host capability evidence.
 *
 * The evidence may only satisfy capabilities that cannot be truthfully derived
 * from the PHP request process itself. It never grants installation,
 * deployment, migration, updater, Technical Preview, or Production authority.
 *
 * Author by Lab | zefry
 */
final class TrustedInstallationHostCapabilityEvidence
{
    /** @var list<string> */
    private const TOP_LEVEL_KEYS = [
        'schema_version',
        'product',
        'repository',
        'release_id',
        'source_commit',
        'artifact_sha256',
        'host_binding',
        'capabilities',
        'attribution',
    ];

    /** @var list<string> */
    private const HOST_BINDING_KEYS = [
        'os_family',
        'web_server_interface',
    ];

    /** @var list<string> */
    private const ATTESTED_CAPABILITIES = [
        'time_sync',
        'outbound_allowlist',
        'scheduler',
        'required_tools',
    ];

    /** @var list<string> */
    private const CAPABILITY_KEYS = [
        'ready',
        'evidence_ref',
    ];

    private const MAX_EVIDENCE_BYTES = 65536;

    /**
     * @param array<string, mixed> $releaseManifest
     * @param array<string, mixed> $observedHost
     * @return array<string, bool>|null
     */
    public function load(string $path, array $releaseManifest, array $observedHost): ?array
    {
        if ($path === ''
            || is_link($path)
            || ! is_file($path)
            || ! is_readable($path)) {
            return null;
        }

        $size = filesize($path);
        if (! is_int($size) || $size <= 0 || $size > self::MAX_EVIDENCE_BYTES) {
            return null;
        }

        try {
            $raw = file_get_contents($path);
            if (! is_string($raw) || $raw === '') {
                return null;
            }

            $evidence = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($evidence)
            || ! $this->hasExactKeys($evidence, self::TOP_LEVEL_KEYS)
            || $evidence['schema_version'] !== 1
            || $evidence['product'] !== 'oneQay'
            || $evidence['repository'] !== 'labzefry/oneQay'
            || $evidence['attribution'] !== 'Lab | zefry') {
            return null;
        }

        $releaseId = $releaseManifest['release_id'] ?? null;
        $sourceCommit = $releaseManifest['source_commit'] ?? null;
        $artifactSha256 = $releaseManifest['artifact_sha256'] ?? null;
        if (! is_string($releaseId)
            || ! is_string($sourceCommit)
            || ! is_string($artifactSha256)
            || ! is_string($evidence['release_id'])
            || ! is_string($evidence['source_commit'])
            || ! is_string($evidence['artifact_sha256'])
            || $evidence['release_id'] !== $releaseId
            || preg_match('/\A[0-9a-f]{40}\z/i', $sourceCommit) !== 1
            || preg_match('/\A[0-9a-f]{64}\z/i', $artifactSha256) !== 1
            || ! hash_equals(strtolower($sourceCommit), strtolower($evidence['source_commit']))
            || ! hash_equals(strtolower($artifactSha256), strtolower($evidence['artifact_sha256']))) {
            return null;
        }

        $binding = $evidence['host_binding'] ?? null;
        if (! is_array($binding)
            || ! $this->hasExactKeys($binding, self::HOST_BINDING_KEYS)
            || ! is_string($binding['os_family'])
            || ! is_string($binding['web_server_interface'])
            || ! is_string($observedHost['os_family'] ?? null)
            || ! is_string($observedHost['web_server_interface'] ?? null)
            || strtolower(trim($binding['os_family'])) !== strtolower(trim($observedHost['os_family']))
            || strtolower(trim($binding['web_server_interface'])) !== strtolower(trim($observedHost['web_server_interface']))) {
            return null;
        }

        $capabilities = $evidence['capabilities'] ?? null;
        if (! is_array($capabilities)
            || ! $this->hasExactKeys($capabilities, self::ATTESTED_CAPABILITIES)) {
            return null;
        }

        $verified = [];
        foreach (self::ATTESTED_CAPABILITIES as $capability) {
            $attestation = $capabilities[$capability] ?? null;
            if (! is_array($attestation)
                || ! $this->hasExactKeys($attestation, self::CAPABILITY_KEYS)
                || ! is_bool($attestation['ready'] ?? null)
                || ! is_string($attestation['evidence_ref'] ?? null)
                || ! $this->isSafeEvidenceReference($attestation['evidence_ref'])) {
                return null;
            }

            $verified[$capability] = $attestation['ready'];
        }

        return $verified;
    }

    /** @param array<string, mixed> $values @param list<string> $expected */
    private function hasExactKeys(array $values, array $expected): bool
    {
        $actual = array_keys($values);
        sort($actual);
        $expectedKeys = $expected;
        sort($expectedKeys);

        return $actual === $expectedKeys;
    }

    private function isSafeEvidenceReference(string $value): bool
    {
        $value = trim($value);

        return $value !== ''
            && strlen($value) <= 256
            && preg_match('/[\x00-\x1F\x7F]/', $value) !== 1
            && ! str_contains($value, '<')
            && ! str_contains($value, '>');
    }
}
