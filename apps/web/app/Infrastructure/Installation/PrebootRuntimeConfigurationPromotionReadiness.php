<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootRuntimeConfigurationPromotionReadiness
{
    public const READINESS_SCHEMA_VERSION = 1;

    private const MAX_PENDING_BYTES = 65536;
    private const MAX_HANDOFF_BYTES = 16384;
    private const MAX_REQUEST_BYTES = 16384;
    private const MAX_AUTHORITY_BYTES = 16384;
    private const MAX_READINESS_BYTES = 16384;

    public function __construct(
        private readonly string $sharedRoot,
        private readonly string $releaseId,
        private readonly int $nowUnix,
    ) {
        if (! preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId)) {
            throw new RuntimeException('invalid_release_identity');
        }

        if ($nowUnix <= 0) {
            throw new RuntimeException('invalid_readiness_clock');
        }
    }

    /**
     * Persist exact-bound execution-readiness evidence after successful
     * authority qualification. This does not promote .env.pending.
     *
     * @return array{
     *   state:'PROMOTION_EXECUTION_READY_NOT_EXECUTED',
     *   request_id:string,
     *   authority_id:string,
     *   qualification_fingerprint:string,
     *   readiness_sha256:string,
     *   promotion_executed:false
     * }
     */
    public function attest(string $approvalToken): array
    {
        $this->assertPrivateBoundaryShape();

        if (is_file($this->activeEnvironmentPath())) {
            throw new RuntimeException('active_environment_already_present');
        }

        $qualification = (new PrebootRuntimeConfigurationPromotionQualification(
            $this->sharedRoot,
            $this->releaseId,
            $this->nowUnix,
        ))->qualify($approvalToken);

        if (($qualification['state'] ?? null) !== 'PROMOTION_QUALIFIED_NOT_EXECUTED'
            || ($qualification['promotion_qualified'] ?? false) !== true
            || ($qualification['promotion_executed'] ?? true) !== false) {
            throw new RuntimeException('promotion_qualification_not_ready');
        }

        $context = $this->loadContext();
        if ($context === null) {
            throw new RuntimeException('promotion_readiness_context_invalid');
        }

        if (($qualification['request_id'] ?? null) !== $context['request_id']
            || ($qualification['authority_id'] ?? null) !== $context['authority_id']) {
            throw new RuntimeException('promotion_qualification_binding_mismatch');
        }

        $readiness = [
            'schema_version' => self::READINESS_SCHEMA_VERSION,
            'product' => 'oneQay',
            'readiness_state' => 'PROMOTION_EXECUTION_READY_NOT_EXECUTED',
            'release_id' => $this->releaseId,
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
            'qualification_fingerprint' => (string) $qualification['qualification_fingerprint'],
            'pending_environment_sha256' => $context['pending_sha256'],
            'activation_readiness_sha256' => $context['handoff_sha256'],
            'promotion_request_sha256' => $context['request_sha256'],
            'promotion_authority_sha256' => $context['authority_sha256'],
            'qualified_at_unix' => $this->nowUnix,
            'authority_expires_at_unix' => $context['authority_expires_at_unix'],
            'execution_contract' => [
                'source_relative_path' => 'oneqay-preview/shared/runtime/.env.pending',
                'target_relative_path' => 'oneqay-preview/shared/runtime/.env',
                'preserve_configuration_bytes_exactly' => true,
                'active_environment_must_be_absent' => true,
                'fresh_authority_required' => true,
                'consume_authority_on_success' => true,
                'consume_readiness_on_success' => true,
                'rollback_on_post_promotion_validation_failure' => true,
            ],
            'promotion_executed' => false,
            'migration_execution_authorized' => false,
            'technical_preview_authorized' => false,
            'production_authorized' => false,
            'updater_authorized' => false,
            'deployment_authorized' => false,
            'attribution' => 'Lab | zefry',
        ];

        $this->writeReadiness($readiness);

        $raw = $this->readPrivateFile($this->readinessPath(), self::MAX_READINESS_BYTES);
        if ($raw === null) {
            throw new RuntimeException('promotion_readiness_commit_unavailable');
        }

        return [
            'state' => 'PROMOTION_EXECUTION_READY_NOT_EXECUTED',
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
            'qualification_fingerprint' => (string) $qualification['qualification_fingerprint'],
            'readiness_sha256' => hash('sha256', $raw),
            'promotion_executed' => false,
        ];
    }

    /**
     * @return array{
     *   state:string,
     *   execution_ready:bool,
     *   request_id:string,
     *   authority_id:string,
     *   promotion_executed:false
     * }
     */
    public function inspect(): array
    {
        try {
            $this->assertPrivateBoundaryShape();

            if (is_file($this->activeEnvironmentPath())) {
                return $this->state('ACTIVE_ENV_PRESENT', false);
            }

            $raw = $this->readPrivateFile($this->readinessPath(), self::MAX_READINESS_BYTES);
            if ($raw === null) {
                return $this->state('PROMOTION_READINESS_MISSING', false);
            }

            $readiness = $this->decodeJson($raw);
            $context = $this->loadContext();
            if ($readiness === null
                || $context === null
                || ! $this->readinessIsValid($readiness, $context)) {
                return $this->state('PROMOTION_READINESS_INVALID', false);
            }

            if ($this->nowUnix >= (int) $readiness['authority_expires_at_unix']) {
                return $this->state('PROMOTION_READINESS_EXPIRED', false);
            }

            return [
                'state' => 'PROMOTION_EXECUTION_READY_NOT_EXECUTED',
                'execution_ready' => true,
                'request_id' => (string) $readiness['request_id'],
                'authority_id' => (string) $readiness['authority_id'],
                'promotion_executed' => false,
            ];
        } catch (Throwable) {
            return $this->state('PROMOTION_READINESS_INVALID', false);
        }
    }

    /**
     * @return array{
     *   pending_sha256:string,
     *   handoff_sha256:string,
     *   request_sha256:string,
     *   authority_sha256:string,
     *   request_id:string,
     *   authority_id:string,
     *   authority_expires_at_unix:int
     * }|null
     */
    private function loadContext(): ?array
    {
        $pending = $this->readPrivateFile($this->pendingEnvironmentPath(), self::MAX_PENDING_BYTES);
        $handoffRaw = $this->readPrivateFile($this->activationReadinessPath(), self::MAX_HANDOFF_BYTES);
        $requestRaw = $this->readPrivateFile($this->requestPath(), self::MAX_REQUEST_BYTES);
        $authorityRaw = $this->readPrivateFile($this->authorityPath(), self::MAX_AUTHORITY_BYTES);

        if ($pending === null || $handoffRaw === null || $requestRaw === null || $authorityRaw === null) {
            return null;
        }

        $request = $this->decodeJson($requestRaw);
        $authority = $this->decodeJson($authorityRaw);
        if ($request === null || $authority === null) {
            return null;
        }

        if (! is_string($request['request_id'] ?? null)
            || ! is_string($authority['authority_id'] ?? null)
            || ($authority['request_id'] ?? null) !== $request['request_id']
            || ($authority['release_id'] ?? null) !== $this->releaseId
            || ! is_int($authority['expires_at_unix'] ?? null)
            || (int) $authority['expires_at_unix'] <= $this->nowUnix) {
            return null;
        }

        return [
            'pending_sha256' => hash('sha256', $pending),
            'handoff_sha256' => hash('sha256', $handoffRaw),
            'request_sha256' => hash('sha256', $requestRaw),
            'authority_sha256' => hash('sha256', $authorityRaw),
            'request_id' => (string) $request['request_id'],
            'authority_id' => (string) $authority['authority_id'],
            'authority_expires_at_unix' => (int) $authority['expires_at_unix'],
        ];
    }

    /**
     * @param array<string,mixed> $readiness
     * @param array{
     *   pending_sha256:string,
     *   handoff_sha256:string,
     *   request_sha256:string,
     *   authority_sha256:string,
     *   request_id:string,
     *   authority_id:string,
     *   authority_expires_at_unix:int
     * } $context
     */
    private function readinessIsValid(array $readiness, array $context): bool
    {
        $contract = is_array($readiness['execution_contract'] ?? null)
            ? $readiness['execution_contract']
            : [];

        return ($readiness['schema_version'] ?? null) === self::READINESS_SCHEMA_VERSION
            && ($readiness['product'] ?? null) === 'oneQay'
            && ($readiness['readiness_state'] ?? null) === 'PROMOTION_EXECUTION_READY_NOT_EXECUTED'
            && ($readiness['release_id'] ?? null) === $this->releaseId
            && ($readiness['request_id'] ?? null) === $context['request_id']
            && ($readiness['authority_id'] ?? null) === $context['authority_id']
            && is_string($readiness['qualification_fingerprint'] ?? null)
            && preg_match('/\A[0-9a-f]{64}\z/', (string) $readiness['qualification_fingerprint']) === 1
            && ($readiness['pending_environment_sha256'] ?? null) === $context['pending_sha256']
            && ($readiness['activation_readiness_sha256'] ?? null) === $context['handoff_sha256']
            && ($readiness['promotion_request_sha256'] ?? null) === $context['request_sha256']
            && ($readiness['promotion_authority_sha256'] ?? null) === $context['authority_sha256']
            && is_int($readiness['qualified_at_unix'] ?? null)
            && (int) $readiness['qualified_at_unix'] > 0
            && (int) $readiness['qualified_at_unix'] <= $this->nowUnix
            && ($readiness['authority_expires_at_unix'] ?? null) === $context['authority_expires_at_unix']
            && ($contract['source_relative_path'] ?? null) === 'oneqay-preview/shared/runtime/.env.pending'
            && ($contract['target_relative_path'] ?? null) === 'oneqay-preview/shared/runtime/.env'
            && ($contract['preserve_configuration_bytes_exactly'] ?? null) === true
            && ($contract['active_environment_must_be_absent'] ?? null) === true
            && ($contract['fresh_authority_required'] ?? null) === true
            && ($contract['consume_authority_on_success'] ?? null) === true
            && ($contract['consume_readiness_on_success'] ?? null) === true
            && ($contract['rollback_on_post_promotion_validation_failure'] ?? null) === true
            && ($readiness['promotion_executed'] ?? null) === false
            && ($readiness['migration_execution_authorized'] ?? null) === false
            && ($readiness['technical_preview_authorized'] ?? null) === false
            && ($readiness['production_authorized'] ?? null) === false
            && ($readiness['updater_authorized'] ?? null) === false
            && ($readiness['deployment_authorized'] ?? null) === false
            && ($readiness['attribution'] ?? null) === 'Lab | zefry';
    }

    /** @param array<string,mixed> $readiness */
    private function writeReadiness(array $readiness): void
    {
        $installDirectory = $this->installDirectory();
        if (! is_dir($installDirectory) || is_link($installDirectory)) {
            throw new RuntimeException('promotion_readiness_boundary_missing');
        }

        $path = $this->readinessPath();
        $json = json_encode(
            $readiness,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ).PHP_EOL;

        if (file_exists($path) || is_link($path)) {
            $existingRaw = $this->readPrivateFile($path, self::MAX_READINESS_BYTES);
            $existing = $existingRaw === null ? null : $this->decodeJson($existingRaw);
            $context = $this->loadContext();

            if ($existing !== null
                && $context !== null
                && $this->readinessIsValid($existing, $context)
                && hash_equals(
                    (string) ($existing['qualification_fingerprint'] ?? ''),
                    (string) ($readiness['qualification_fingerprint'] ?? ''),
                )) {
                return;
            }

            throw new RuntimeException('promotion_readiness_already_present');
        }

        $temporaryPath = $installDirectory.DIRECTORY_SEPARATOR.'promotion-readiness.tmp.'.bin2hex(random_bytes(12));
        $handle = @fopen($temporaryPath, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('promotion_readiness_temp_unavailable');
        }

        try {
            @chmod($temporaryPath, 0600);
            $length = strlen($json);
            $written = 0;
            while ($written < $length) {
                $result = fwrite($handle, substr($json, $written));
                if ($result === false || $result === 0) {
                    throw new RuntimeException('promotion_readiness_write_failed');
                }
                $written += $result;
            }

            if (! fflush($handle)) {
                throw new RuntimeException('promotion_readiness_flush_failed');
            }
            if (function_exists('fsync')) {
                @fsync($handle);
            }
        } catch (Throwable $exception) {
            fclose($handle);
            @unlink($temporaryPath);
            throw $exception;
        }

        fclose($handle);

        if (! @rename($temporaryPath, $path)) {
            @unlink($temporaryPath);
            throw new RuntimeException('promotion_readiness_commit_failed');
        }

        @chmod($path, 0600);
    }

    private function assertPrivateBoundaryShape(): void
    {
        if ($this->sharedRoot === ''
            || str_contains($this->sharedRoot, "\0")
            || (file_exists($this->sharedRoot) && is_link($this->sharedRoot))) {
            throw new RuntimeException('unsafe_shared_runtime_boundary');
        }

        foreach ([
            $this->activeEnvironmentPath(),
            $this->pendingEnvironmentPath(),
            $this->activationReadinessPath(),
            $this->requestPath(),
            $this->authorityPath(),
            $this->readinessPath(),
        ] as $path) {
            if (is_link($path)) {
                throw new RuntimeException('symlink_boundary_rejected');
            }
        }
    }

    private function readPrivateFile(string $path, int $maximumBytes): ?string
    {
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            return null;
        }

        $size = filesize($path);
        if (! is_int($size) || $size <= 0 || $size > $maximumBytes) {
            return null;
        }

        $permissions = fileperms($path);
        if (is_int($permissions) && ($permissions & 0077) !== 0) {
            return null;
        }

        $content = file_get_contents($path);

        return is_string($content) ? $content : null;
    }

    /** @return array<string,mixed>|null */
    private function decodeJson(string $json): ?array
    {
        try {
            $decoded = json_decode($json, true, 32, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array{state:string,execution_ready:bool,request_id:string,authority_id:string,promotion_executed:false} */
    private function state(string $state, bool $ready): array
    {
        return [
            'state' => $state,
            'execution_ready' => $ready,
            'request_id' => '',
            'authority_id' => '',
            'promotion_executed' => false,
        ];
    }

    private function installDirectory(): string
    {
        return $this->sharedRoot.DIRECTORY_SEPARATOR.'install';
    }

    private function runtimeDirectory(): string
    {
        return $this->sharedRoot.DIRECTORY_SEPARATOR.'runtime';
    }

    private function activeEnvironmentPath(): string
    {
        return $this->runtimeDirectory().DIRECTORY_SEPARATOR.'.env';
    }

    private function pendingEnvironmentPath(): string
    {
        return $this->runtimeDirectory().DIRECTORY_SEPARATOR.'.env.pending';
    }

    private function activationReadinessPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'activation-readiness.json';
    }

    private function requestPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'runtime-configuration-promotion-request.json';
    }

    private function authorityPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'runtime-configuration-promotion-authority.json';
    }

    private function readinessPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'runtime-configuration-promotion-readiness.json';
    }
}
