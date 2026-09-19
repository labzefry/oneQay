<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootInstallationCompletionHandoff
{
    private const MAX_ENVIRONMENT_BYTES = 65536;
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
            throw new RuntimeException('invalid_completion_clock');
        }
    }

    /**
     * @return array{
     *   state:string,
     *   complete:bool,
     *   request_id:string,
     *   authority_id:string,
     *   active_environment_sha256:string,
     *   activation_authorized:false
     * }
     */
    public function inspect(): array
    {
        $this->assertPrivateBoundaryShape();

        $context = $this->loadContext();
        if ($context === null) {
            return $this->state('INSTALLATION_COMPLETION_NOT_READY', false);
        }

        if (! is_file($this->completionPath())) {
            return [
                'state' => 'INSTALLATION_COMPLETION_READY_TO_SEAL',
                'complete' => false,
                'request_id' => $context['request_id'],
                'authority_id' => $context['authority_id'],
                'active_environment_sha256' => $context['active_sha256'],
                'activation_authorized' => false,
            ];
        }

        $raw = $this->readPrivateFile($this->completionPath(), self::MAX_JSON_BYTES);
        $payload = $raw === null ? null : $this->decodeJson($raw);

        if ($payload === null || ! $this->completionMatchesContext($payload, $context)) {
            return $this->state('INSTALLATION_COMPLETION_INVALID', false);
        }

        return [
            'state' => 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED',
            'complete' => true,
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
            'active_environment_sha256' => $context['active_sha256'],
            'activation_authorized' => false,
        ];
    }

    /**
     * @return array{
     *   state:'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED',
     *   complete:true,
     *   request_id:string,
     *   authority_id:string,
     *   active_environment_sha256:string,
     *   completion_sha256:string,
     *   activation_authorized:false
     * }
     */
    public function seal(): array
    {
        $this->assertPrivateBoundaryShape();

        $context = $this->loadContext();
        if ($context === null) {
            throw new RuntimeException('installation_completion_not_ready');
        }

        if (is_file($this->completionPath())) {
            $existingRaw = $this->readPrivateFile($this->completionPath(), self::MAX_JSON_BYTES);
            $existing = $existingRaw === null ? null : $this->decodeJson($existingRaw);

            if ($existingRaw === null || $existing === null || ! $this->completionMatchesContext($existing, $context)) {
                throw new RuntimeException('installation_completion_invalid');
            }

            return [
                'state' => 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED',
                'complete' => true,
                'request_id' => $context['request_id'],
                'authority_id' => $context['authority_id'],
                'active_environment_sha256' => $context['active_sha256'],
                'completion_sha256' => hash('sha256', $existingRaw),
                'activation_authorized' => false,
            ];
        }

        $payload = [
            'schema_version' => 1,
            'product' => 'oneQay',
            'completion_state' => 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED',
            'release_id' => $this->releaseId,
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
            'execution_receipt_sha256' => $context['receipt_sha256'],
            'post_promotion_verification_sha256' => $context['verification_sha256'],
            'active_environment_sha256' => $context['active_sha256'],
            'completed_at_unix' => $this->nowUnix,
            'installer_reentry_state' => 'READ_ONLY_COMPLETION',
            'migration_execution_authorized' => false,
            'technical_preview_authorized' => false,
            'persistence_authorized' => false,
            'production_authorized' => false,
            'updater_authorized' => false,
            'deployment_authorized' => false,
            'attribution' => 'Lab | zefry',
        ];

        $this->writeJsonAtomic($this->completionPath(), $payload);

        $writtenRaw = $this->readPrivateFile($this->completionPath(), self::MAX_JSON_BYTES);
        $written = $writtenRaw === null ? null : $this->decodeJson($writtenRaw);

        if ($writtenRaw === null || $written === null || ! $this->completionMatchesContext($written, $context)) {
            @unlink($this->completionPath());
            throw new RuntimeException('installation_completion_commit_failed');
        }

        return [
            'state' => 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED',
            'complete' => true,
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
            'active_environment_sha256' => $context['active_sha256'],
            'completion_sha256' => hash('sha256', $writtenRaw),
            'activation_authorized' => false,
        ];
    }

    /**
     * @return array{
     *   active_sha256:string,
     *   receipt_sha256:string,
     *   verification_sha256:string,
     *   request_id:string,
     *   authority_id:string
     * }|null
     */
    private function loadContext(): ?array
    {
        if (file_exists($this->pendingEnvironmentPath()) || is_link($this->pendingEnvironmentPath())) {
            return null;
        }

        $verificationState = (new PrebootRuntimeConfigurationPostPromotionVerification(
            $this->sharedRoot,
            $this->releaseId,
            $this->nowUnix,
        ))->inspect();

        if (($verificationState['state'] ?? null) !== 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED'
            || ($verificationState['verified'] ?? false) !== true
            || ($verificationState['activation_authorized'] ?? true) !== false) {
            return null;
        }

        $activeRaw = $this->readPrivateFile($this->activeEnvironmentPath(), self::MAX_ENVIRONMENT_BYTES);
        $receiptRaw = $this->readPrivateFile($this->executionReceiptPath(), self::MAX_JSON_BYTES);
        $verificationRaw = $this->readPrivateFile($this->verificationPath(), self::MAX_JSON_BYTES);

        if ($activeRaw === null || $receiptRaw === null || $verificationRaw === null) {
            return null;
        }

        $receipt = $this->decodeJson($receiptRaw);
        $verification = $this->decodeJson($verificationRaw);
        if ($receipt === null || $verification === null) {
            return null;
        }

        $activeSha = hash('sha256', $activeRaw);
        $receiptSha = hash('sha256', $receiptRaw);
        $verificationSha = hash('sha256', $verificationRaw);
        $requestId = $verification['request_id'] ?? null;
        $authorityId = $verification['authority_id'] ?? null;

        if (($verification['verification_state'] ?? null) !== 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED'
            || ($verification['release_id'] ?? null) !== $this->releaseId
            || ($verification['active_environment_sha256'] ?? null) !== $activeSha
            || ($verification['execution_receipt_sha256'] ?? null) !== $receiptSha
            || ! is_string($requestId)
            || ! is_string($authorityId)
            || ($receipt['request_id'] ?? null) !== $requestId
            || ($receipt['authority_id'] ?? null) !== $authorityId
            || ($receipt['active_environment_sha256'] ?? null) !== $activeSha
            || ($receipt['execution_state'] ?? null) !== 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED') {
            return null;
        }

        return [
            'active_sha256' => $activeSha,
            'receipt_sha256' => $receiptSha,
            'verification_sha256' => $verificationSha,
            'request_id' => $requestId,
            'authority_id' => $authorityId,
        ];
    }

    /** @param array<string,mixed> $completion
     *  @param array{active_sha256:string,receipt_sha256:string,verification_sha256:string,request_id:string,authority_id:string} $context
     */
    private function completionMatchesContext(array $completion, array $context): bool
    {
        return ($completion['schema_version'] ?? null) === 1
            && ($completion['product'] ?? null) === 'oneQay'
            && ($completion['completion_state'] ?? null) === 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED'
            && ($completion['release_id'] ?? null) === $this->releaseId
            && ($completion['request_id'] ?? null) === $context['request_id']
            && ($completion['authority_id'] ?? null) === $context['authority_id']
            && ($completion['execution_receipt_sha256'] ?? null) === $context['receipt_sha256']
            && ($completion['post_promotion_verification_sha256'] ?? null) === $context['verification_sha256']
            && ($completion['active_environment_sha256'] ?? null) === $context['active_sha256']
            && is_int($completion['completed_at_unix'] ?? null)
            && (int) $completion['completed_at_unix'] > 0
            && ($completion['installer_reentry_state'] ?? null) === 'READ_ONLY_COMPLETION'
            && ($completion['migration_execution_authorized'] ?? null) === false
            && ($completion['technical_preview_authorized'] ?? null) === false
            && ($completion['persistence_authorized'] ?? null) === false
            && ($completion['production_authorized'] ?? null) === false
            && ($completion['updater_authorized'] ?? null) === false
            && ($completion['deployment_authorized'] ?? null) === false
            && ($completion['attribution'] ?? null) === 'Lab | zefry';
    }

    private function writeJsonAtomic(string $path, array $payload): void
    {
        $directory = $this->installDirectory();
        if (! is_dir($directory) || is_link($directory)) {
            throw new RuntimeException('installation_completion_boundary_unavailable');
        }

        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
        $temporary = $directory.DIRECTORY_SEPARATOR.'installation-completion.tmp.'.bin2hex(random_bytes(12));
        $handle = @fopen($temporary, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('installation_completion_temp_unavailable');
        }

        try {
            @chmod($temporary, 0600);
            if (fwrite($handle, $json) !== strlen($json) || ! fflush($handle)) {
                throw new RuntimeException('installation_completion_write_failed');
            }
            if (function_exists('fsync')) {
                @fsync($handle);
            }
        } catch (Throwable $exception) {
            fclose($handle);
            @unlink($temporary);
            throw $exception;
        }

        fclose($handle);

        if (file_exists($path) || is_link($path) || ! @rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('installation_completion_commit_failed');
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
            $this->executionReceiptPath(),
            $this->verificationPath(),
            $this->completionPath(),
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

    /** @return array{state:string,complete:bool,request_id:string,authority_id:string,active_environment_sha256:string,activation_authorized:false} */
    private function state(string $state, bool $complete): array
    {
        return [
            'state' => $state,
            'complete' => $complete,
            'request_id' => '',
            'authority_id' => '',
            'active_environment_sha256' => '',
            'activation_authorized' => false,
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

    private function executionReceiptPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'runtime-configuration-promotion-execution.json';
    }

    private function verificationPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'runtime-configuration-post-promotion-verification.json';
    }

    private function completionPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'installation-completion.json';
    }
}
