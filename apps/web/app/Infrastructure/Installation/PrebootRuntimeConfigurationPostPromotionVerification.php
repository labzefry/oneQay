<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootRuntimeConfigurationPostPromotionVerification
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
            throw new RuntimeException('invalid_verification_clock');
        }
    }

    /**
     * @return array{
     *   state:string,
     *   verified:bool,
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
            if (is_file($this->activeEnvironmentPath()) || is_file($this->executionReceiptPath())) {
                return $this->state('RUNTIME_CONFIGURATION_VERIFICATION_INVALID', false);
            }

            return $this->state('POST_PROMOTION_VERIFICATION_NOT_READY', false);
        }

        if (! is_file($this->verificationPath())) {
            return [
                'state' => 'RUNTIME_CONFIGURATION_VERIFICATION_REQUIRED',
                'verified' => false,
                'request_id' => $context['request_id'],
                'authority_id' => $context['authority_id'],
                'active_environment_sha256' => $context['active_sha256'],
                'activation_authorized' => false,
            ];
        }

        $verificationRaw = $this->readPrivateFile($this->verificationPath(), self::MAX_JSON_BYTES);
        $verification = $verificationRaw === null ? null : $this->decodeJson($verificationRaw);

        if ($verification === null || ! $this->verificationMatchesContext($verification, $context)) {
            return $this->state('RUNTIME_CONFIGURATION_VERIFICATION_INVALID', false);
        }

        return [
            'state' => 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED',
            'verified' => true,
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
            'active_environment_sha256' => $context['active_sha256'],
            'activation_authorized' => false,
        ];
    }

    /**
     * @return array{
     *   state:'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED',
     *   verified:true,
     *   request_id:string,
     *   authority_id:string,
     *   active_environment_sha256:string,
     *   verification_sha256:string,
     *   activation_authorized:false
     * }
     */
    public function verify(): array
    {
        $this->assertPrivateBoundaryShape();

        $context = $this->loadContext();
        if ($context === null) {
            throw new RuntimeException('post_promotion_verification_denied');
        }

        if (is_file($this->verificationPath())) {
            $existingRaw = $this->readPrivateFile($this->verificationPath(), self::MAX_JSON_BYTES);
            $existing = $existingRaw === null ? null : $this->decodeJson($existingRaw);

            if ($existingRaw === null || $existing === null || ! $this->verificationMatchesContext($existing, $context)) {
                throw new RuntimeException('post_promotion_verification_invalid');
            }

            return [
                'state' => 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED',
                'verified' => true,
                'request_id' => $context['request_id'],
                'authority_id' => $context['authority_id'],
                'active_environment_sha256' => $context['active_sha256'],
                'verification_sha256' => hash('sha256', $existingRaw),
                'activation_authorized' => false,
            ];
        }

        $payload = [
            'schema_version' => 1,
            'product' => 'oneQay',
            'verification_state' => 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED',
            'release_id' => $this->releaseId,
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
            'execution_receipt_sha256' => $context['receipt_sha256'],
            'active_environment_sha256' => $context['active_sha256'],
            'verified_at_unix' => $this->nowUnix,
            'pending_environment_absent' => true,
            'runtime_configuration_active' => true,
            'migration_execution_authorized' => false,
            'technical_preview_authorized' => false,
            'production_authorized' => false,
            'updater_authorized' => false,
            'deployment_authorized' => false,
            'attribution' => 'Lab | zefry',
        ];

        $this->writeJsonAtomic($this->verificationPath(), $payload);

        $writtenRaw = $this->readPrivateFile($this->verificationPath(), self::MAX_JSON_BYTES);
        $written = $writtenRaw === null ? null : $this->decodeJson($writtenRaw);

        if ($writtenRaw === null || $written === null || ! $this->verificationMatchesContext($written, $context)) {
            @unlink($this->verificationPath());
            throw new RuntimeException('post_promotion_verification_commit_failed');
        }

        return [
            'state' => 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED',
            'verified' => true,
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
            'active_environment_sha256' => $context['active_sha256'],
            'verification_sha256' => hash('sha256', $writtenRaw),
            'activation_authorized' => false,
        ];
    }

    /**
     * @return array{
     *   active_sha256:string,
     *   receipt_sha256:string,
     *   request_id:string,
     *   authority_id:string
     * }|null
     */
    private function loadContext(): ?array
    {
        if (file_exists($this->pendingEnvironmentPath()) || is_link($this->pendingEnvironmentPath())) {
            return null;
        }

        $activeRaw = $this->readPrivateFile($this->activeEnvironmentPath(), self::MAX_ENVIRONMENT_BYTES);
        $receiptRaw = $this->readPrivateFile($this->executionReceiptPath(), self::MAX_JSON_BYTES);

        if ($activeRaw === null || $receiptRaw === null || ! $this->activeEnvironmentIsSafe($activeRaw)) {
            return null;
        }

        $receipt = $this->decodeJson($receiptRaw);
        if ($receipt === null) {
            return null;
        }

        $activeSha = hash('sha256', $activeRaw);
        $requestId = $receipt['request_id'] ?? null;
        $authorityId = $receipt['authority_id'] ?? null;

        if (($receipt['schema_version'] ?? null) !== 1
            || ($receipt['product'] ?? null) !== 'oneQay'
            || ($receipt['execution_state'] ?? null) !== 'RUNTIME_CONFIGURATION_PROMOTED_NOT_ACTIVATED'
            || ($receipt['release_id'] ?? null) !== $this->releaseId
            || ! is_string($requestId)
            || preg_match('/\Apromotion-request-[0-9a-f]{24}\z/', $requestId) !== 1
            || ! is_string($authorityId)
            || preg_match('/\Apromotion-authority-[0-9a-f]{24}\z/', $authorityId) !== 1
            || ($receipt['pending_environment_sha256'] ?? null) !== $activeSha
            || ($receipt['active_environment_sha256'] ?? null) !== $activeSha
            || ($receipt['authority_consumption_state'] ?? null) !== 'CONSUMED_BY_EXECUTION_RECEIPT'
            || ($receipt['readiness_consumption_state'] ?? null) !== 'CONSUMED_BY_EXECUTION_RECEIPT'
            || ($receipt['pending_environment_state'] ?? null) !== 'REMOVED_AFTER_VERIFIED_PROMOTION'
            || ($receipt['migration_execution_authorized'] ?? null) !== false
            || ($receipt['technical_preview_authorized'] ?? null) !== false
            || ($receipt['production_authorized'] ?? null) !== false
            || ($receipt['updater_authorized'] ?? null) !== false
            || ($receipt['deployment_authorized'] ?? null) !== false
            || ($receipt['attribution'] ?? null) !== 'Lab | zefry') {
            return null;
        }

        return [
            'active_sha256' => $activeSha,
            'receipt_sha256' => hash('sha256', $receiptRaw),
            'request_id' => $requestId,
            'authority_id' => $authorityId,
        ];
    }

    /** @param array<string,mixed> $verification
     *  @param array{active_sha256:string,receipt_sha256:string,request_id:string,authority_id:string} $context
     */
    private function verificationMatchesContext(array $verification, array $context): bool
    {
        return ($verification['schema_version'] ?? null) === 1
            && ($verification['product'] ?? null) === 'oneQay'
            && ($verification['verification_state'] ?? null) === 'RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED'
            && ($verification['release_id'] ?? null) === $this->releaseId
            && ($verification['request_id'] ?? null) === $context['request_id']
            && ($verification['authority_id'] ?? null) === $context['authority_id']
            && ($verification['execution_receipt_sha256'] ?? null) === $context['receipt_sha256']
            && ($verification['active_environment_sha256'] ?? null) === $context['active_sha256']
            && is_int($verification['verified_at_unix'] ?? null)
            && (int) $verification['verified_at_unix'] > 0
            && ($verification['pending_environment_absent'] ?? null) === true
            && ($verification['runtime_configuration_active'] ?? null) === true
            && ($verification['migration_execution_authorized'] ?? null) === false
            && ($verification['technical_preview_authorized'] ?? null) === false
            && ($verification['production_authorized'] ?? null) === false
            && ($verification['updater_authorized'] ?? null) === false
            && ($verification['deployment_authorized'] ?? null) === false
            && ($verification['attribution'] ?? null) === 'Lab | zefry';
    }

    private function activeEnvironmentIsSafe(string $content): bool
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

    private function writeJsonAtomic(string $path, array $payload): void
    {
        $directory = $this->installDirectory();
        if (! is_dir($directory) || is_link($directory)) {
            throw new RuntimeException('post_promotion_verification_boundary_unavailable');
        }

        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
        $temporary = $directory.DIRECTORY_SEPARATOR.'post-promotion-verification.tmp.'.bin2hex(random_bytes(12));
        $handle = @fopen($temporary, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('post_promotion_verification_temp_unavailable');
        }

        try {
            @chmod($temporary, 0600);
            $length = strlen($json);
            $written = 0;

            while ($written < $length) {
                $result = fwrite($handle, substr($json, $written));
                if ($result === false || $result === 0) {
                    throw new RuntimeException('post_promotion_verification_write_failed');
                }
                $written += $result;
            }

            if (! fflush($handle)) {
                throw new RuntimeException('post_promotion_verification_flush_failed');
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

        if (file_exists($path) || is_link($path)) {
            @unlink($temporary);
            throw new RuntimeException('post_promotion_verification_already_present');
        }

        if (! @rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('post_promotion_verification_commit_failed');
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

    /** @return array{state:string,verified:bool,request_id:string,authority_id:string,active_environment_sha256:string,activation_authorized:false} */
    private function state(string $state, bool $verified): array
    {
        return [
            'state' => $state,
            'verified' => $verified,
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
}
