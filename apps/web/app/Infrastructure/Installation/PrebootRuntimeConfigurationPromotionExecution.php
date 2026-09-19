<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootRuntimeConfigurationPromotionExecution
{
    private const MAX_PENDING_BYTES = 65536;
    private const MAX_JSON_BYTES = 16384;

    public function __construct(
        private readonly string $sharedRoot,
        private readonly string $releaseId,
        private readonly int $nowUnix,
    ) {
        if (! preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId)) {
            throw new RuntimeException('invalid_release_identity');
        }

        if ($nowUnix <= 0) {
            throw new RuntimeException('invalid_execution_clock');
        }
    }

    /**
     * Source-only executor. No route or public installer action registers this method.
     *
     * @return array{
     *   state:'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED',
     *   request_id:string,
     *   authority_id:string,
     *   active_environment_sha256:string,
     *   receipt_sha256:string,
     *   technical_preview_authorized:false,
     *   production_authorized:false
     * }
     */
    public function execute(string $approvalToken): array
    {
        $this->assertPrivateBoundaryShape();

        if (is_file($this->activeEnvironmentPath())) {
            throw new RuntimeException('active_environment_already_present');
        }

        if (file_exists($this->executionReceiptPath()) || is_link($this->executionReceiptPath())) {
            throw new RuntimeException('promotion_execution_receipt_already_present');
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

        $readinessState = (new PrebootRuntimeConfigurationPromotionReadiness(
            $this->sharedRoot,
            $this->releaseId,
            $this->nowUnix,
        ))->inspect();

        if (($readinessState['state'] ?? null) !== 'PROMOTION_EXECUTION_READY_NOT_EXECUTED'
            || ($readinessState['execution_ready'] ?? false) !== true
            || ($readinessState['promotion_executed'] ?? true) !== false) {
            throw new RuntimeException('promotion_execution_readiness_required');
        }

        $context = $this->loadContext();
        if ($context === null) {
            throw new RuntimeException('promotion_execution_context_invalid');
        }

        if (($qualification['request_id'] ?? null) !== $context['request_id']
            || ($qualification['authority_id'] ?? null) !== $context['authority_id']
            || ($qualification['qualification_fingerprint'] ?? null) !== $context['qualification_fingerprint']) {
            throw new RuntimeException('promotion_execution_binding_mismatch');
        }

        if (! $this->pendingEnvironmentIsSafe($context['pending_raw'])) {
            throw new RuntimeException('pending_environment_contract_invalid');
        }

        $this->materializeActiveEnvironment($context['pending_raw']);

        try {
            $activeRaw = $this->readPrivateFile($this->activeEnvironmentPath(), self::MAX_PENDING_BYTES);
            if ($activeRaw === null
                || ! hash_equals($context['pending_sha256'], hash('sha256', $activeRaw))
                || ! $this->pendingEnvironmentIsSafe($activeRaw)) {
                throw new RuntimeException('active_environment_verification_failed');
            }

            if (! @unlink($this->pendingEnvironmentPath())) {
                throw new RuntimeException('pending_environment_consumption_failed');
            }

            $receipt = [
                'schema_version' => 1,
                'product' => 'oneQay',
                'execution_state' => 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED',
                'release_id' => $this->releaseId,
                'request_id' => $context['request_id'],
                'authority_id' => $context['authority_id'],
                'qualification_fingerprint' => $context['qualification_fingerprint'],
                'pending_environment_sha256' => $context['pending_sha256'],
                'activation_readiness_sha256' => $context['handoff_sha256'],
                'promotion_request_sha256' => $context['request_sha256'],
                'promotion_authority_sha256' => $context['authority_sha256'],
                'promotion_readiness_sha256' => $context['readiness_sha256'],
                'active_environment_sha256' => hash('sha256', $activeRaw),
                'executed_at_unix' => $this->nowUnix,
                'authority_consumption_state' => 'CONSUMED_BY_EXECUTION_RECEIPT',
                'readiness_consumption_state' => 'CONSUMED_BY_EXECUTION_RECEIPT',
                'pending_environment_state' => 'REMOVED_AFTER_VERIFIED_PROMOTION',
                'migration_execution_authorized' => false,
                'technical_preview_authorized' => false,
                'production_authorized' => false,
                'updater_authorized' => false,
                'deployment_authorized' => false,
                'attribution' => 'Lab | zefry',
            ];

            $this->writeJsonExclusive($this->executionReceiptPath(), $receipt);
            $receiptRaw = $this->readPrivateFile($this->executionReceiptPath(), self::MAX_JSON_BYTES);
            if ($receiptRaw === null) {
                throw new RuntimeException('promotion_execution_receipt_unavailable');
            }

            return [
                'state' => 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED',
                'request_id' => $context['request_id'],
                'authority_id' => $context['authority_id'],
                'active_environment_sha256' => hash('sha256', $activeRaw),
                'receipt_sha256' => hash('sha256', $receiptRaw),
                'technical_preview_authorized' => false,
                'production_authorized' => false,
            ];
        } catch (Throwable $exception) {
            @unlink($this->executionReceiptPath());
            @unlink($this->activeEnvironmentPath());

            if (! is_file($this->pendingEnvironmentPath())) {
                try {
                    $this->writePrivateFileExclusive($this->pendingEnvironmentPath(), $context['pending_raw']);
                } catch (Throwable) {
                    throw new RuntimeException('promotion_execution_rollback_failed', 0, $exception);
                }
            }

            throw $exception;
        }
    }

    /**
     * @return array{
     *   pending_raw:string,
     *   pending_sha256:string,
     *   handoff_sha256:string,
     *   request_sha256:string,
     *   authority_sha256:string,
     *   readiness_sha256:string,
     *   request_id:string,
     *   authority_id:string,
     *   qualification_fingerprint:string
     * }|null
     */
    private function loadContext(): ?array
    {
        $pending = $this->readPrivateFile($this->pendingEnvironmentPath(), self::MAX_PENDING_BYTES);
        $handoffRaw = $this->readPrivateFile($this->activationReadinessPath(), self::MAX_JSON_BYTES);
        $requestRaw = $this->readPrivateFile($this->requestPath(), self::MAX_JSON_BYTES);
        $authorityRaw = $this->readPrivateFile($this->authorityPath(), self::MAX_JSON_BYTES);
        $readinessRaw = $this->readPrivateFile($this->readinessPath(), self::MAX_JSON_BYTES);

        if ($pending === null || $handoffRaw === null || $requestRaw === null || $authorityRaw === null || $readinessRaw === null) {
            return null;
        }

        $request = $this->decodeJson($requestRaw);
        $authority = $this->decodeJson($authorityRaw);
        $readiness = $this->decodeJson($readinessRaw);

        if ($request === null || $authority === null || $readiness === null) {
            return null;
        }

        $requestId = $request['request_id'] ?? null;
        $authorityId = $authority['authority_id'] ?? null;
        $fingerprint = $readiness['qualification_fingerprint'] ?? null;

        if (! is_string($requestId)
            || ! is_string($authorityId)
            || ! is_string($fingerprint)
            || preg_match('/\A[0-9a-f]{64}\z/', $fingerprint) !== 1
            || ($authority['request_id'] ?? null) !== $requestId
            || ($authority['release_id'] ?? null) !== $this->releaseId
            || ($readiness['release_id'] ?? null) !== $this->releaseId
            || ($readiness['request_id'] ?? null) !== $requestId
            || ($readiness['authority_id'] ?? null) !== $authorityId
            || ($readiness['readiness_state'] ?? null) !== 'PROMOTION_EXECUTION_READY_NOT_EXECUTED'
            || ($readiness['promotion_executed'] ?? null) !== false
            || ! is_int($authority['expires_at_unix'] ?? null)
            || (int) $authority['expires_at_unix'] <= $this->nowUnix
            || ($readiness['authority_expires_at_unix'] ?? null) !== $authority['expires_at_unix']) {
            return null;
        }

        $pendingSha = hash('sha256', $pending);
        $handoffSha = hash('sha256', $handoffRaw);
        $requestSha = hash('sha256', $requestRaw);
        $authoritySha = hash('sha256', $authorityRaw);

        if (($readiness['pending_environment_sha256'] ?? null) !== $pendingSha
            || ($readiness['activation_readiness_sha256'] ?? null) !== $handoffSha
            || ($readiness['promotion_request_sha256'] ?? null) !== $requestSha
            || ($readiness['promotion_authority_sha256'] ?? null) !== $authoritySha) {
            return null;
        }

        return [
            'pending_raw' => $pending,
            'pending_sha256' => $pendingSha,
            'handoff_sha256' => $handoffSha,
            'request_sha256' => $requestSha,
            'authority_sha256' => $authoritySha,
            'readiness_sha256' => hash('sha256', $readinessRaw),
            'request_id' => $requestId,
            'authority_id' => $authorityId,
            'qualification_fingerprint' => $fingerprint,
        ];
    }

    private function pendingEnvironmentIsSafe(string $content): bool
    {
        foreach ([
            'APP_KEY="base64:',
            'ONEQAY_RUNTIME_CLASS="preview"',
            'ONEQAY_TECHNICAL_PREVIEW_ENABLED="false"',
            'ONEQAY_PERSISTENCE_ENABLED="false"',
            'ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED="false"',
            'ONEQAY_INSTALLATION_DATABASE_VERIFIED="true"',
            'ONEQAY_INSTALLATION_DATABASE_LEAST_PRIVILEGE="true"',
            'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="false"',
        ] as $required) {
            if (! str_contains($content, $required)) {
                return false;
            }
        }

        return str_contains($content, 'ONEQAY_INSTALLATION_RELEASE_ID="'.$this->releaseId.'"')
            && ! str_contains($content, 'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"')
            && ! str_contains($content, 'ONEQAY_PERSISTENCE_ENABLED="true"')
            && ! str_contains($content, 'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="true"');
    }

    private function materializeActiveEnvironment(string $content): void
    {
        $runtime = $this->runtimeDirectory();
        if (! is_dir($runtime) || is_link($runtime)) {
            throw new RuntimeException('runtime_boundary_unavailable');
        }

        $temporary = $runtime.DIRECTORY_SEPARATOR.'.env.promoting.'.bin2hex(random_bytes(12));
        $this->writePrivateFileExclusive($temporary, $content);

        if (file_exists($this->activeEnvironmentPath()) || is_link($this->activeEnvironmentPath())) {
            @unlink($temporary);
            throw new RuntimeException('active_environment_already_present');
        }

        if (! @link($temporary, $this->activeEnvironmentPath())) {
            @unlink($temporary);
            throw new RuntimeException('active_environment_atomic_commit_failed');
        }

        @chmod($this->activeEnvironmentPath(), 0600);
        @unlink($temporary);
    }

    private function writePrivateFileExclusive(string $path, string $content): void
    {
        $handle = @fopen($path, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('private_file_create_failed');
        }

        try {
            @chmod($path, 0600);
            $length = strlen($content);
            $written = 0;
            while ($written < $length) {
                $result = fwrite($handle, substr($content, $written));
                if ($result === false || $result === 0) {
                    throw new RuntimeException('private_file_write_failed');
                }
                $written += $result;
            }
            if (! fflush($handle)) {
                throw new RuntimeException('private_file_flush_failed');
            }
            if (function_exists('fsync')) {
                @fsync($handle);
            }
        } catch (Throwable $exception) {
            fclose($handle);
            @unlink($path);
            throw $exception;
        }

        fclose($handle);
    }

    /** @param array<string,mixed> $payload */
    private function writeJsonExclusive(string $path, array $payload): void
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL;
        $this->writePrivateFileExclusive($path, $json);
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
            $this->executionReceiptPath(),
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

    private function executionReceiptPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'runtime-configuration-promotion-execution.json';
    }
}
