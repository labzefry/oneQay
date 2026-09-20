<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Development;

use JsonException;
use Throwable;

// Author by Lab | zefry
final class GovernedDevelopmentUpdateRequest
{
    private const REQUEST_TTL_SECONDS = 900;

    /** @return array<string,mixed> */
    public function status(): array
    {
        $enabled = $this->enabledForCurrentRuntime();
        $request = null;
        $result = null;
        $candidate = null;

        if ($enabled) {
            try {
                $request = $this->readJsonIfPresent($this->pendingPath());
                $result = $this->readJsonIfPresent($this->lastResultPath());
                $candidateRaw = $this->readJsonIfPresent($this->candidatePath());
                if (is_array($candidateRaw)) {
                    $candidate = $this->validateCandidate($candidateRaw, time(), null);
                }
            } catch (Throwable) {
                $request = null;
                $result = null;
                $candidate = null;
            }
        }

        $now = time();
        $pending = is_array($request)
            && ($request['request_state'] ?? null) === 'PENDING'
            && is_int($request['expires_at_unix'] ?? null)
            && $request['expires_at_unix'] >= $now;

        return [
            'enabled' => $enabled,
            'mode' => 'GOVERNED_DEVELOPMENT_STAGING_ONLY',
            'request_pending' => $pending,
            'request_id' => $pending && is_string($request['request_id'] ?? null) ? $request['request_id'] : null,
            'request_expires_at_unix' => $pending ? $request['expires_at_unix'] : null,
            'candidate' => is_array($candidate) ? [
                'state' => $candidate['candidate_state'],
                'release_id' => $candidate['release_id'],
                'source_commit' => $candidate['source_commit'],
                'github_outer_sha256' => $candidate['github_outer_sha256'],
                'fingerprint' => $candidate['candidate_fingerprint'],
                'discovered_at_unix' => $candidate['discovered_at_unix'],
                'expires_at_unix' => $candidate['expires_at_unix'],
                'update_available' => $candidate['candidate_state'] === 'AVAILABLE',
            ] : null,
            'last_result' => is_array($result) ? [
                'state' => is_string($result['state'] ?? null) ? $result['state'] : null,
                'release_id' => is_string($result['release_id'] ?? null) ? $result['release_id'] : null,
                'source_commit' => is_string($result['source_commit'] ?? null) ? $result['source_commit'] : null,
                'completed_at_unix' => is_int($result['completed_at_unix'] ?? null) ? $result['completed_at_unix'] : null,
                'safe_code' => is_string($result['safe_code'] ?? null) ? $result['safe_code'] : null,
            ] : null,
            'production_allowed' => false,
            'migration_execution_allowed' => false,
            'repository' => 'labzefry/oneQay',
            'channel' => 'STAGING',
            'attribution' => 'Lab | zefry',
        ];
    }

    /** @return array<string,mixed> */
    public function create(string $operatorToken, string $totpCode, string $candidateFingerprint, int $nowUnix): array
    {
        $this->assertEnabledForCurrentRuntime();

        if ($nowUnix <= 0) {
            throw new DevelopmentUpdaterViolation('invalid_time');
        }

        $expectedHash = strtolower(trim((string) config('oneqay.development_updater.operator_token_sha256', '')));
        if (preg_match('/\A[0-9a-f]{64}\z/', $expectedHash) !== 1
            || $operatorToken === ''
            || strlen($operatorToken) > 1024
            || ! hash_equals($expectedHash, hash('sha256', $operatorToken))) {
            throw new DevelopmentUpdaterViolation('operator_authorization_denied');
        }

        $secret = trim((string) config('oneqay.development_updater.totp_secret', ''));
        if (! $this->verifyTotp($secret, $totpCode, $nowUnix)) {
            throw new DevelopmentUpdaterViolation('operator_authorization_denied');
        }

        $hmacKey = (string) config('oneqay.development_updater.request_hmac_key', '');
        if (strlen($hmacKey) < 32 || strlen($hmacKey) > 4096) {
            throw new DevelopmentUpdaterViolation('request_signing_key_invalid');
        }

        $privateRoot = $this->privateRoot();
        $this->ensurePrivateDirectory($privateRoot);
        $this->ensurePrivateDirectory($privateRoot.'/requests');
        $this->ensurePrivateDirectory($privateRoot.'/evidence');
        $this->ensurePrivateDirectory($privateRoot.'/history');
        $this->ensurePrivateDirectory($privateRoot.'/work');

        $existing = $this->readJsonIfPresent($this->pendingPath());
        if (is_array($existing)
            && ($existing['request_state'] ?? null) === 'PENDING'
            && is_int($existing['expires_at_unix'] ?? null)
            && $existing['expires_at_unix'] >= $nowUnix) {
            throw new DevelopmentUpdaterViolation('request_already_pending');
        }

        if (preg_match('/\A[0-9a-f]{64}\z/', $candidateFingerprint) !== 1) {
            throw new DevelopmentUpdaterViolation('candidate_fingerprint_invalid');
        }
        $candidate = $this->validateCandidate(
            $this->readJsonIfPresent($this->candidatePath()),
            $nowUnix,
            $candidateFingerprint,
        );
        if (($candidate['candidate_state'] ?? null) !== 'AVAILABLE') {
            throw new DevelopmentUpdaterViolation('candidate_not_available');
        }

        $source = strtolower(trim((string) config('oneqay.development_updater.running_source_commit', '')));
        $artifact = strtolower(trim((string) config('oneqay.development_updater.running_artifact_sha256', '')));
        if (preg_match('/\A[0-9a-f]{40}\z/', $source) !== 1
            || preg_match('/\A[0-9a-f]{64}\z/', $artifact) !== 1
            || ($candidate['current_source_commit'] ?? null) !== $source) {
            throw new DevelopmentUpdaterViolation('running_release_identity_invalid');
        }

        $requestId = 'durable-staging-deployment-request-'.substr(bin2hex(random_bytes(16)), 0, 24);
        $authorityId = 'durable-staging-deployment-authority-'.substr(bin2hex(random_bytes(16)), 0, 24);
        $authority = [
            'schema_version' => 1,
            'authority_state' => 'EXTERNAL_DURABLE_STAGING_DEPLOYMENT_AUTHORITY_QUALIFIED_NOT_EXECUTED',
            'authority_id' => $authorityId,
            'request_id' => $requestId,
            'candidate_fingerprint' => $candidate['candidate_fingerprint'],
            'release_id' => $candidate['release_id'],
            'source_commit' => $candidate['source_commit'],
            'github_run_id' => $candidate['github_run_id'],
            'github_artifact_id' => $candidate['github_artifact_id'],
            'github_outer_sha256' => $candidate['github_outer_sha256'],
            'authorized_at_unix' => $nowUnix,
            'expires_at_unix' => $nowUnix + self::REQUEST_TTL_SECONDS,
            'deployment_allowed' => true,
            'production_allowed' => false,
            'migration_execution_allowed' => false,
            'production_traffic_activation_allowed' => false,
            'attribution' => 'Lab | zefry',
        ];
        $authoritySha256 = hash('sha256', $this->canonicalJson($authority));

        $request = [
            'schema_version' => 1,
            'request_state' => 'PENDING',
            'request_id' => $requestId,
            'scope' => 'INSTALL_EXACT_GOVERNED_DURABLE_STAGING_RELEASE',
            'requested_at_unix' => $nowUnix,
            'expires_at_unix' => $nowUnix + self::REQUEST_TTL_SECONDS,
            'current_source_commit' => $source,
            'current_artifact_sha256' => $artifact,
            'candidate_fingerprint' => $candidate['candidate_fingerprint'],
            'candidate_release_id' => $candidate['release_id'],
            'candidate_source_commit' => $candidate['source_commit'],
            'github_run_id' => $candidate['github_run_id'],
            'github_artifact_id' => $candidate['github_artifact_id'],
            'github_outer_sha256' => $candidate['github_outer_sha256'],
            'deployment_authority_id' => $authorityId,
            'deployment_authority_sha256' => $authoritySha256,
            'repository' => 'labzefry/oneQay',
            'workflow' => 'durable-staging-release-publication.yml',
            'production_allowed' => false,
            'migration_execution_allowed' => false,
            'attribution' => 'Lab | zefry',
        ];
        $request['signature'] = hash_hmac('sha256', $this->canonicalJson($request), $hmacKey);

        $this->atomicWriteJson($this->authorityPath($requestId), $authority);
        $this->atomicWriteJson($this->pendingPath(), $request);

        return [
            'state' => 'PENDING',
            'request_id' => $requestId,
            'deployment_authority_id' => $authorityId,
            'deployment_authority_sha256' => $authoritySha256,
            'expires_at_unix' => $request['expires_at_unix'],
            'production_allowed' => false,
            'migration_execution_allowed' => false,
            'attribution' => 'Lab | zefry',
        ];
    }

    /** @return array<string,mixed> */
    public function requireCurrentPending(int $nowUnix): array
    {
        $this->assertEnabledForCurrentRuntime();
        $request = $this->readJsonIfPresent($this->pendingPath());
        if (! is_array($request)) {
            throw new DevelopmentUpdaterViolation('request_missing');
        }

        $signature = $request['signature'] ?? null;
        if (! is_string($signature) || preg_match('/\A[0-9a-f]{64}\z/', $signature) !== 1) {
            throw new DevelopmentUpdaterViolation('request_signature_invalid');
        }

        $unsigned = $request;
        unset($unsigned['signature']);
        $hmacKey = (string) config('oneqay.development_updater.request_hmac_key', '');
        if (strlen($hmacKey) < 32
            || ! hash_equals($signature, hash_hmac('sha256', $this->canonicalJson($unsigned), $hmacKey))) {
            throw new DevelopmentUpdaterViolation('request_signature_invalid');
        }

        if (($request['schema_version'] ?? null) !== 1
            || ($request['request_state'] ?? null) !== 'PENDING'
            || ($request['scope'] ?? null) !== 'INSTALL_EXACT_GOVERNED_DURABLE_STAGING_RELEASE'
            || ($request['repository'] ?? null) !== 'labzefry/oneQay'
            || ($request['workflow'] ?? null) !== 'durable-staging-release-publication.yml'
            || ($request['production_allowed'] ?? null) !== false
            || ($request['migration_execution_allowed'] ?? null) !== false
            || ($request['attribution'] ?? null) !== 'Lab | zefry') {
            throw new DevelopmentUpdaterViolation('request_contract_invalid');
        }

        $requestId = $request['request_id'] ?? null;
        $requestedAt = $request['requested_at_unix'] ?? null;
        $expiresAt = $request['expires_at_unix'] ?? null;
        if (! is_string($requestId)
            || preg_match('/\Adurable-staging-deployment-request-[0-9a-f]{24}\z/', $requestId) !== 1
            || ! is_int($requestedAt)
            || ! is_int($expiresAt)
            || $requestedAt <= 0
            || $expiresAt !== $requestedAt + self::REQUEST_TTL_SECONDS
            || $nowUnix < $requestedAt
            || $nowUnix > $expiresAt) {
            throw new DevelopmentUpdaterViolation('request_expired_or_invalid');
        }

        if (preg_match('/\A[0-9a-f]{64}\z/', (string) ($request['candidate_fingerprint'] ?? '')) !== 1
            || preg_match('/\Adurable-staging-[0-9a-f]{12}\z/', (string) ($request['candidate_release_id'] ?? '')) !== 1
            || preg_match('/\A[0-9a-f]{40}\z/', (string) ($request['candidate_source_commit'] ?? '')) !== 1
            || ! is_int($request['github_run_id'] ?? null)
            || ($request['github_run_id'] ?? 0) <= 0
            || ! is_int($request['github_artifact_id'] ?? null)
            || ($request['github_artifact_id'] ?? 0) <= 0
            || preg_match('/\A[0-9a-f]{64}\z/', (string) ($request['github_outer_sha256'] ?? '')) !== 1
            || preg_match('/\Adurable-staging-deployment-authority-[0-9a-f]{24}\z/', (string) ($request['deployment_authority_id'] ?? '')) !== 1
            || preg_match('/\A[0-9a-f]{64}\z/', (string) ($request['deployment_authority_sha256'] ?? '')) !== 1
            || $request['candidate_release_id'] !== 'durable-staging-'.substr($request['candidate_source_commit'], 0, 12)) {
            throw new DevelopmentUpdaterViolation('request_candidate_binding_invalid');
        }

        $authority = $this->readJsonIfPresent($this->authorityPath($requestId));
        if (! is_array($authority)
            || ($authority['schema_version'] ?? null) !== 1
            || ($authority['authority_state'] ?? null) !== 'EXTERNAL_DURABLE_STAGING_DEPLOYMENT_AUTHORITY_QUALIFIED_NOT_EXECUTED'
            || ($authority['authority_id'] ?? null) !== $request['deployment_authority_id']
            || ($authority['request_id'] ?? null) !== $requestId
            || ($authority['candidate_fingerprint'] ?? null) !== $request['candidate_fingerprint']
            || ($authority['release_id'] ?? null) !== $request['candidate_release_id']
            || ($authority['source_commit'] ?? null) !== $request['candidate_source_commit']
            || ($authority['github_run_id'] ?? null) !== $request['github_run_id']
            || ($authority['github_artifact_id'] ?? null) !== $request['github_artifact_id']
            || ($authority['github_outer_sha256'] ?? null) !== $request['github_outer_sha256']
            || ($authority['authorized_at_unix'] ?? null) !== $requestedAt
            || ($authority['expires_at_unix'] ?? null) !== $expiresAt
            || ($authority['deployment_allowed'] ?? null) !== true
            || ($authority['production_allowed'] ?? null) !== false
            || ($authority['migration_execution_allowed'] ?? null) !== false
            || ($authority['production_traffic_activation_allowed'] ?? null) !== false
            || ($authority['attribution'] ?? null) !== 'Lab | zefry'
            || ! hash_equals(
                (string) $request['deployment_authority_sha256'],
                hash('sha256', $this->canonicalJson($authority)),
            )) {
            throw new DevelopmentUpdaterViolation('deployment_authority_binding_invalid');
        }

        $runningSource = strtolower(trim((string) config('oneqay.development_updater.running_source_commit', '')));
        $runningArtifact = strtolower(trim((string) config('oneqay.development_updater.running_artifact_sha256', '')));
        if (($request['current_source_commit'] ?? null) !== $runningSource
            || ($request['current_artifact_sha256'] ?? null) !== $runningArtifact) {
            throw new DevelopmentUpdaterViolation('request_running_identity_drift');
        }

        return $request;
    }

    /** @param array<string,mixed> $candidate */
    public function storeCandidate(array $candidate, int $nowUnix): array
    {
        $this->assertEnabledForCurrentRuntime();
        $this->ensurePrivateDirectory($this->privateRoot());

        $source = strtolower((string) ($candidate['source_commit'] ?? ''));
        $current = strtolower((string) ($candidate['current_source_commit'] ?? ''));
        $releaseId = (string) ($candidate['release_id'] ?? '');
        $runId = $candidate['github_run_id'] ?? null;
        $artifactId = $candidate['github_artifact_id'] ?? null;
        $outer = strtolower((string) ($candidate['github_outer_sha256'] ?? ''));

        if ($nowUnix <= 0
            || preg_match('/\A[0-9a-f]{40}\z/', $source) !== 1
            || preg_match('/\A[0-9a-f]{40}\z/', $current) !== 1
            || $releaseId !== 'durable-staging-'.substr($source, 0, 12)
            || ! is_int($runId) || $runId <= 0
            || ! is_int($artifactId) || $artifactId <= 0
            || preg_match('/\A[0-9a-f]{64}\z/', $outer) !== 1) {
            throw new DevelopmentUpdaterViolation('candidate_contract_invalid');
        }

        $state = hash_equals($current, $source) ? 'CURRENT' : 'AVAILABLE';
        $payload = [
            'schema_version' => 1,
            'candidate_state' => $state,
            'discovered_at_unix' => $nowUnix,
            'expires_at_unix' => $nowUnix + 3600,
            'release_id' => $releaseId,
            'source_commit' => $source,
            'current_source_commit' => $current,
            'github_run_id' => $runId,
            'github_artifact_id' => $artifactId,
            'github_outer_sha256' => $outer,
            'repository' => 'labzefry/oneQay',
            'workflow' => 'durable-staging-release-publication.yml',
            'production_allowed' => false,
            'migration_execution_allowed' => false,
            'attribution' => 'Lab | zefry',
        ];
        $payload['candidate_fingerprint'] = hash('sha256', $this->canonicalJson($payload));

        $hmacKey = (string) config('oneqay.development_updater.request_hmac_key', '');
        if (strlen($hmacKey) < 32 || strlen($hmacKey) > 4096) {
            throw new DevelopmentUpdaterViolation('request_signing_key_invalid');
        }
        $payload['signature'] = hash_hmac('sha256', $this->canonicalJson($payload), $hmacKey);
        $this->atomicWriteJson($this->candidatePath(), $payload);

        return $payload;
    }

    /** @return array<string,mixed> */
    private function validateCandidate(?array $candidate, int $nowUnix, ?string $expectedFingerprint): array
    {
        if (! is_array($candidate)) {
            throw new DevelopmentUpdaterViolation('candidate_missing');
        }

        $signature = $candidate['signature'] ?? null;
        if (! is_string($signature) || preg_match('/\A[0-9a-f]{64}\z/', $signature) !== 1) {
            throw new DevelopmentUpdaterViolation('candidate_signature_invalid');
        }

        $unsigned = $candidate;
        unset($unsigned['signature']);
        $hmacKey = (string) config('oneqay.development_updater.request_hmac_key', '');
        if (strlen($hmacKey) < 32
            || ! hash_equals($signature, hash_hmac('sha256', $this->canonicalJson($unsigned), $hmacKey))) {
            throw new DevelopmentUpdaterViolation('candidate_signature_invalid');
        }

        $fingerprint = $candidate['candidate_fingerprint'] ?? null;
        $withoutFingerprint = $unsigned;
        unset($withoutFingerprint['candidate_fingerprint']);
        if (! is_string($fingerprint)
            || preg_match('/\A[0-9a-f]{64}\z/', $fingerprint) !== 1
            || ! hash_equals($fingerprint, hash('sha256', $this->canonicalJson($withoutFingerprint)))
            || ($expectedFingerprint !== null && ! hash_equals($expectedFingerprint, $fingerprint))) {
            throw new DevelopmentUpdaterViolation('candidate_fingerprint_mismatch');
        }

        if (($candidate['schema_version'] ?? null) !== 1
            || ! in_array($candidate['candidate_state'] ?? null, ['AVAILABLE', 'CURRENT'], true)
            || ($candidate['repository'] ?? null) !== 'labzefry/oneQay'
            || ($candidate['workflow'] ?? null) !== 'durable-staging-release-publication.yml'
            || ($candidate['production_allowed'] ?? null) !== false
            || ($candidate['migration_execution_allowed'] ?? null) !== false
            || ($candidate['attribution'] ?? null) !== 'Lab | zefry'
            || preg_match('/\Adurable-staging-[0-9a-f]{12}\z/', (string) ($candidate['release_id'] ?? '')) !== 1
            || preg_match('/\A[0-9a-f]{40}\z/', (string) ($candidate['source_commit'] ?? '')) !== 1
            || preg_match('/\A[0-9a-f]{40}\z/', (string) ($candidate['current_source_commit'] ?? '')) !== 1
            || ! is_int($candidate['github_run_id'] ?? null) || ($candidate['github_run_id'] ?? 0) <= 0
            || ! is_int($candidate['github_artifact_id'] ?? null) || ($candidate['github_artifact_id'] ?? 0) <= 0
            || preg_match('/\A[0-9a-f]{64}\z/', (string) ($candidate['github_outer_sha256'] ?? '')) !== 1
            || $candidate['release_id'] !== 'durable-staging-'.substr($candidate['source_commit'], 0, 12)
            || ! is_int($candidate['discovered_at_unix'] ?? null)
            || ! is_int($candidate['expires_at_unix'] ?? null)
            || $candidate['expires_at_unix'] !== $candidate['discovered_at_unix'] + 3600
            || $nowUnix < $candidate['discovered_at_unix']
            || $nowUnix > $candidate['expires_at_unix']) {
            throw new DevelopmentUpdaterViolation('candidate_contract_invalid');
        }

        return $candidate;
    }

    /** @param array<string,mixed> $result */
    public function complete(array $result): void
    {
        $request = $this->readJsonIfPresent($this->pendingPath());
        if (is_array($request) && is_string($request['request_id'] ?? null)) {
            $history = $this->privateRoot().'/history/'.$request['request_id'].'.json';
            $this->atomicWriteJson($history, $request);
        }

        $this->atomicWriteJson($this->lastResultPath(), $result);
        if (is_file($this->pendingPath()) && ! unlink($this->pendingPath())) {
            throw new DevelopmentUpdaterViolation('request_cleanup_failed');
        }
    }

    public function privateRoot(): string
    {
        $path = trim((string) config('oneqay.development_updater.private_root', ''));
        if (! $this->absoluteSafePath($path)) {
            throw new DevelopmentUpdaterViolation('private_root_invalid');
        }

        $public = realpath(public_path());
        $candidate = realpath($path);
        if (is_string($public) && is_string($candidate)
            && ($candidate === $public || str_starts_with($candidate.'/', rtrim($public, '/').'/'))) {
            throw new DevelopmentUpdaterViolation('private_root_public_forbidden');
        }

        return rtrim($path, '/');
    }

    private function pendingPath(): string
    {
        return $this->privateRoot().'/requests/pending.json';
    }

    private function candidatePath(): string
    {
        return $this->privateRoot().'/candidate.json';
    }

    private function authorityPath(string $requestId): string
    {
        if (preg_match('/\Adurable-staging-deployment-request-[0-9a-f]{24}\z/', $requestId) !== 1) {
            throw new DevelopmentUpdaterViolation('authority_request_id_invalid');
        }

        return $this->privateRoot().'/requests/'.$requestId.'.authority.json';
    }

    private function lastResultPath(): string
    {
        return $this->privateRoot().'/last-result.json';
    }

    private function enabledForCurrentRuntime(): bool
    {
        $enabled = (bool) config('oneqay.development_updater.enabled', false);
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $appEnv = strtolower(trim((string) config('app.env', '')));
        $productionDataAllowed = filter_var(
            env('ONEQAY_PRODUCTION_DATA_ALLOWED', false),
            FILTER_VALIDATE_BOOL,
        );

        return $enabled
            && $runtime === 'durable-staging'
            && $appEnv !== 'production'
            && $productionDataAllowed === false;
    }

    private function assertEnabledForCurrentRuntime(): void
    {
        if (! $this->enabledForCurrentRuntime()) {
            throw new DevelopmentUpdaterViolation('development_updater_disabled');
        }
    }

    private function ensurePrivateDirectory(string $path): void
    {
        if (is_link($path)) {
            throw new DevelopmentUpdaterViolation('private_directory_symlink_forbidden');
        }

        if (! is_dir($path) && ! mkdir($path, 0700, true) && ! is_dir($path)) {
            throw new DevelopmentUpdaterViolation('private_directory_unavailable');
        }

        @chmod($path, 0700);
        if (is_link($path) || ! is_writable($path)) {
            throw new DevelopmentUpdaterViolation('private_directory_unsafe');
        }
    }

    /** @return array<string,mixed>|null */
    private function readJsonIfPresent(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }
        if (is_link($path) || ! is_readable($path)) {
            throw new DevelopmentUpdaterViolation('private_state_unreadable');
        }

        $size = filesize($path);
        if (! is_int($size) || $size < 2 || $size > 65536) {
            throw new DevelopmentUpdaterViolation('private_state_size_invalid');
        }

        try {
            $value = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new DevelopmentUpdaterViolation('private_state_json_invalid');
        }

        if (! is_array($value) || array_is_list($value)) {
            throw new DevelopmentUpdaterViolation('private_state_shape_invalid');
        }

        return $value;
    }

    /** @param array<string,mixed> $payload */
    private function atomicWriteJson(string $path, array $payload): void
    {
        $directory = dirname($path);
        $this->ensurePrivateDirectory($directory);

        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        ).PHP_EOL;

        $temporary = tempnam($directory, '.oneqay-update-');
        if ($temporary === false) {
            throw new DevelopmentUpdaterViolation('private_state_temp_failed');
        }

        try {
            if (file_put_contents($temporary, $json, LOCK_EX) !== strlen($json)) {
                throw new DevelopmentUpdaterViolation('private_state_write_failed');
            }
            @chmod($temporary, 0600);
            if (! rename($temporary, $path)) {
                throw new DevelopmentUpdaterViolation('private_state_commit_failed');
            }
            @chmod($path, 0600);
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }

    /** @param array<string,mixed> $value */
    private function canonicalJson(array $value): string
    {
        $canonical = $this->canonicalize($value);

        return json_encode(
            $canonical,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $entry): mixed => $this->canonicalize($entry), $value);
        }

        ksort($value, SORT_STRING);
        foreach ($value as $key => $entry) {
            $value[$key] = $this->canonicalize($entry);
        }

        return $value;
    }

    private function absoluteSafePath(string $path): bool
    {
        return $path !== ''
            && $path !== '/'
            && strlen($path) <= 4096
            && str_starts_with($path, '/')
            && ! str_contains($path, "\0")
            && ! str_contains($path, '\\')
            && preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $path) !== 1
            && preg_match('#//+#', $path) !== 1;
    }

    private function verifyTotp(string $encodedSecret, string $code, int $unixTime): bool
    {
        if ($unixTime <= 0 || preg_match('/\A[0-9]{6}\z/', $code) !== 1) {
            return false;
        }

        $secret = $this->decodeBase32($encodedSecret);
        if ($secret === null || $secret === '') {
            return false;
        }

        $counter = intdiv($unixTime, 30);
        $matched = false;
        for ($offset = -1; $offset <= 1; $offset++) {
            $candidate = $counter + $offset;
            if ($candidate < 0) {
                continue;
            }

            $high = intdiv($candidate, 4_294_967_296);
            $low = $candidate % 4_294_967_296;
            $digest = hash_hmac('sha1', pack('N2', $high, $low), $secret, true);
            $position = ord($digest[19]) & 0x0f;
            $binary = ((ord($digest[$position]) & 0x7f) << 24)
                | ((ord($digest[$position + 1]) & 0xff) << 16)
                | ((ord($digest[$position + 2]) & 0xff) << 8)
                | (ord($digest[$position + 3]) & 0xff);
            $expected = str_pad((string) ($binary % 1_000_000), 6, '0', STR_PAD_LEFT);
            $matched = hash_equals($expected, $code) || $matched;
        }

        return $matched;
    }

    private function decodeBase32(string $encoded): ?string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $normalized = rtrim(strtoupper(trim($encoded)), '=');
        if ($normalized === '' || preg_match('/\A[A-Z2-7]+\z/', $normalized) !== 1) {
            return null;
        }

        $buffer = 0;
        $bits = 0;
        $decoded = '';
        foreach (str_split($normalized) as $character) {
            $value = strpos($alphabet, $character);
            if ($value === false) {
                return null;
            }

            $buffer = ($buffer << 5) | $value;
            $bits += 5;
            while ($bits >= 8) {
                $bits -= 8;
                $decoded .= chr(($buffer >> $bits) & 0xff);
                $buffer &= (1 << $bits) - 1;
            }
        }

        return $decoded;
    }
}
