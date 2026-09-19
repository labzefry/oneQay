<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootTechnicalPreviewActivationAuthorityReadiness
{
    public const AUTHORITY_SCHEMA_VERSION = 1;
    public const READINESS_SCHEMA_VERSION = 1;
    public const MAX_AUTHORITY_LIFETIME_SECONDS = 900;

    private const MAX_ENVIRONMENT_BYTES = 65536;
    private const MAX_JSON_BYTES = 16384;
    private const MAX_APPROVAL_TOKEN_BYTES = 256;

    public function __construct(
        private readonly string $sharedRoot,
        private readonly string $releaseId,
        private readonly int $nowUnix,
    ) {
        if (! preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId)) {
            throw new RuntimeException('invalid_release_identity');
        }

        if ($nowUnix <= 0) {
            throw new RuntimeException('invalid_activation_authority_clock');
        }
    }

    /**
     * @return array{
     *   state:string,
     *   authority_present:bool,
     *   token_required:bool,
     *   execution_ready:bool,
     *   request_id:string,
     *   authority_id:string,
     *   activation_executed:false
     * }
     */
    public function inspect(): array
    {
        try {
            $this->assertPrivateBoundaryShape();

            $context = $this->loadContext();
            if ($context === null) {
                return $this->state('TECHNICAL_PREVIEW_ACTIVATION_REQUEST_NOT_READY', false, false, false);
            }

            $readinessRaw = $this->readPrivateFile($this->readinessPath(), self::MAX_JSON_BYTES);
            if ($readinessRaw !== null) {
                $readiness = $this->decodeJson($readinessRaw);
                if ($readiness === null || ! $this->readinessIsValid($readiness, $context)) {
                    return $this->state('TECHNICAL_PREVIEW_ACTIVATION_READINESS_INVALID', false, false, false);
                }

                if ($this->nowUnix >= (int) $readiness['authority_expires_at_unix']) {
                    return $this->state('TECHNICAL_PREVIEW_ACTIVATION_READINESS_EXPIRED', true, false, false);
                }

                return [
                    'state' => 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED',
                    'authority_present' => true,
                    'token_required' => false,
                    'execution_ready' => true,
                    'request_id' => $context['request_id'],
                    'authority_id' => $context['authority_id'],
                    'activation_executed' => false,
                ];
            }

            $authorityRaw = $this->readPrivateFile($this->authorityPath(), self::MAX_JSON_BYTES);
            if ($authorityRaw === null) {
                return $this->state('TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_MISSING', false, false, false);
            }

            $authority = $this->decodeJson($authorityRaw);
            if ($authority === null || ! $this->authorityIsValid($authority, $context)) {
                return $this->state('TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_INVALID', true, false, false);
            }

            if (! $this->authorityIsFresh($authority)) {
                return $this->state('TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_EXPIRED', true, false, false);
            }

            return [
                'state' => 'TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_TOKEN_REQUIRED',
                'authority_present' => true,
                'token_required' => true,
                'execution_ready' => false,
                'request_id' => $context['request_id'],
                'authority_id' => (string) $authority['authority_id'],
                'activation_executed' => false,
            ];
        } catch (Throwable) {
            return $this->state('TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_INVALID', false, false, false);
        }
    }

    /**
     * Qualify separately provisioned operational authority and persist durable
     * execution-readiness evidence. This method never flips runtime flags.
     *
     * @return array{
     *   state:'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED',
     *   authority_id:string,
     *   request_id:string,
     *   qualification_fingerprint:string,
     *   execution_ready:true,
     *   activation_executed:false
     * }
     */
    public function qualifyAndAttest(string $approvalToken): array
    {
        $this->assertPrivateBoundaryShape();

        if (
            strlen($approvalToken) < 32
            || strlen($approvalToken) > self::MAX_APPROVAL_TOKEN_BYTES
            || preg_match('/\A[A-Za-z0-9._~-]{32,256}\z/', $approvalToken) !== 1
        ) {
            throw new RuntimeException('technical_preview_activation_authority_denied');
        }

        $context = $this->loadContext();
        if ($context === null) {
            throw new RuntimeException('technical_preview_activation_request_not_ready');
        }

        $authorityRaw = $this->readPrivateFile($this->authorityPath(), self::MAX_JSON_BYTES);
        if ($authorityRaw === null) {
            throw new RuntimeException('technical_preview_activation_authority_missing');
        }

        $authority = $this->decodeJson($authorityRaw);
        if ($authority === null
            || ! $this->authorityIsValid($authority, $context)
            || ! $this->authorityIsFresh($authority)
            || ! hash_equals((string) $authority['approval_token_sha256'], hash('sha256', $approvalToken))) {
            throw new RuntimeException('technical_preview_activation_authority_denied');
        }

        $context['authority_id'] = (string) $authority['authority_id'];
        $context['authority_sha256'] = hash('sha256', $authorityRaw);
        $context['authority_expires_at_unix'] = (int) $authority['expires_at_unix'];

        $fingerprint = hash('sha256', implode('|', [
            (string) $authority['authority_id'],
            $context['request_id'],
            $this->releaseId,
            $context['active_sha256'],
            $context['completion_sha256'],
            $context['request_sha256'],
            $context['authority_sha256'],
        ]));

        $payload = [
            'schema_version' => self::READINESS_SCHEMA_VERSION,
            'product' => 'oneQay',
            'readiness_state' => 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED',
            'release_id' => $this->releaseId,
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
            'qualification_fingerprint' => $fingerprint,
            'active_environment_sha256' => $context['active_sha256'],
            'installation_completion_sha256' => $context['completion_sha256'],
            'activation_request_sha256' => $context['request_sha256'],
            'activation_authority_sha256' => $context['authority_sha256'],
            'qualified_at_unix' => $this->nowUnix,
            'authority_expires_at_unix' => $context['authority_expires_at_unix'],
            'execution_contract' => [
                'target_environment_preflight_required' => true,
                'https_required' => true,
                'single_instance_required' => true,
                'private_file_session_directory_required' => true,
                'runtime_envelope_revalidation_required' => true,
                'preview_off_switch_verification_required' => true,
                'post_activation_health_check_required' => true,
                'rollback_recovery_required' => true,
                'migration_execution_required' => false,
                'synthetic_data_only' => true,
                'production_data_forbidden' => true,
                'consume_authority_on_success' => true,
                'consume_readiness_on_success' => true,
            ],
            'authority_qualified' => true,
            'activation_executed' => false,
            'persistence_authorized' => false,
            'migration_execution_authorized' => false,
            'production_authorized' => false,
            'updater_authorized' => false,
            'deployment_authorized' => false,
            'attribution' => 'Lab | zefry',
        ];

        $this->writeReadiness($payload);

        $writtenRaw = $this->readPrivateFile($this->readinessPath(), self::MAX_JSON_BYTES);
        $written = $writtenRaw === null ? null : $this->decodeJson($writtenRaw);
        $freshContext = $this->loadContext();

        if ($writtenRaw === null
            || $written === null
            || $freshContext === null
            || ! $this->readinessIsValid($written, $freshContext)) {
            throw new RuntimeException('technical_preview_activation_readiness_commit_failed');
        }

        return [
            'state' => 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED',
            'authority_id' => $context['authority_id'],
            'request_id' => $context['request_id'],
            'qualification_fingerprint' => $fingerprint,
            'execution_ready' => true,
            'activation_executed' => false,
        ];
    }

    /**
     * @return array{
     *   active_sha256:string,
     *   completion_sha256:string,
     *   request_sha256:string,
     *   request_id:string,
     *   authority_id:string,
     *   authority_sha256:string,
     *   authority_expires_at_unix:int
     * }|null
     */
    private function loadContext(): ?array
    {
        $completion = (new PrebootInstallationCompletionHandoff(
            $this->sharedRoot,
            $this->releaseId,
            $this->nowUnix,
        ))->inspect();

        $request = (new PrebootTechnicalPreviewActivationRequest(
            $this->sharedRoot,
            $this->releaseId,
            $this->nowUnix,
        ))->inspect();

        if (($completion['state'] ?? null) !== 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED'
            || ($completion['complete'] ?? false) !== true
            || ($completion['activation_authorized'] ?? true) !== false
            || ($request['state'] ?? null) !== 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_PENDING_APPROVAL'
            || ($request['request_ready'] ?? false) !== true
            || ($request['technical_preview_authorized'] ?? true) !== false) {
            return null;
        }

        $activeRaw = $this->readPrivateFile($this->activeEnvironmentPath(), self::MAX_ENVIRONMENT_BYTES);
        $completionRaw = $this->readPrivateFile($this->completionPath(), self::MAX_JSON_BYTES);
        $requestRaw = $this->readPrivateFile($this->requestPath(), self::MAX_JSON_BYTES);

        if ($activeRaw === null || $completionRaw === null || $requestRaw === null) {
            return null;
        }

        $requestPayload = $this->decodeJson($requestRaw);
        if ($requestPayload === null
            || ! is_string($requestPayload['request_id'] ?? null)
            || ($requestPayload['release_id'] ?? null) !== $this->releaseId
            || ($requestPayload['active_environment_sha256'] ?? null) !== hash('sha256', $activeRaw)
            || ($requestPayload['installation_completion_sha256'] ?? null) !== hash('sha256', $completionRaw)
            || ($requestPayload['technical_preview_authorized'] ?? null) !== false) {
            return null;
        }

        $authorityId = '';
        $authoritySha256 = '';
        $authorityExpires = 0;
        $authorityRaw = $this->readPrivateFile($this->authorityPath(), self::MAX_JSON_BYTES);
        if ($authorityRaw !== null) {
            $authority = $this->decodeJson($authorityRaw);
            if (is_array($authority)) {
                $authorityId = is_string($authority['authority_id'] ?? null)
                    ? (string) $authority['authority_id']
                    : '';
                $authoritySha256 = hash('sha256', $authorityRaw);
                $authorityExpires = is_int($authority['expires_at_unix'] ?? null)
                    ? (int) $authority['expires_at_unix']
                    : 0;
            }
        }

        return [
            'active_sha256' => hash('sha256', $activeRaw),
            'completion_sha256' => hash('sha256', $completionRaw),
            'request_sha256' => hash('sha256', $requestRaw),
            'request_id' => (string) $requestPayload['request_id'],
            'authority_id' => $authorityId,
            'authority_sha256' => $authoritySha256,
            'authority_expires_at_unix' => $authorityExpires,
        ];
    }

    /**
     * @param array<string,mixed> $authority
     * @param array<string,mixed> $context
     */
    private function authorityIsValid(array $authority, array $context): bool
    {
        return ($authority['schema_version'] ?? null) === self::AUTHORITY_SCHEMA_VERSION
            && ($authority['product'] ?? null) === 'oneQay'
            && is_string($authority['authority_id'] ?? null)
            && preg_match('/\Atechnical-preview-authority-[0-9a-f]{24}\z/', (string) $authority['authority_id']) === 1
            && ($authority['authority_state'] ?? null) === 'GRANTED'
            && ($authority['scope'] ?? null) === 'ENABLE_SYNTHETIC_TECHNICAL_PREVIEW_RUNTIME'
            && ($authority['request_id'] ?? null) === $context['request_id']
            && ($authority['release_id'] ?? null) === $this->releaseId
            && ($authority['active_environment_sha256'] ?? null) === $context['active_sha256']
            && ($authority['installation_completion_sha256'] ?? null) === $context['completion_sha256']
            && ($authority['activation_request_sha256'] ?? null) === $context['request_sha256']
            && is_string($authority['approval_token_sha256'] ?? null)
            && preg_match('/\A[0-9a-f]{64}\z/', (string) $authority['approval_token_sha256']) === 1
            && is_int($authority['authorized_at_unix'] ?? null)
            && is_int($authority['expires_at_unix'] ?? null)
            && (int) $authority['authorized_at_unix'] > 0
            && (int) $authority['expires_at_unix'] > (int) $authority['authorized_at_unix']
            && ((int) $authority['expires_at_unix'] - (int) $authority['authorized_at_unix']) <= self::MAX_AUTHORITY_LIFETIME_SECONDS
            && ($authority['single_use'] ?? null) === true
            && ($authority['technical_preview_authorized'] ?? null) === true
            && ($authority['persistence_authorized'] ?? null) === false
            && ($authority['migration_execution_authorized'] ?? null) === false
            && ($authority['production_authorized'] ?? null) === false
            && ($authority['updater_authorized'] ?? null) === false
            && ($authority['deployment_authorized'] ?? null) === false
            && ($authority['target_environment_preflight_required'] ?? null) === true
            && ($authority['attribution'] ?? null) === 'Lab | zefry';
    }

    /** @param array<string,mixed> $authority */
    private function authorityIsFresh(array $authority): bool
    {
        return $this->nowUnix >= (int) $authority['authorized_at_unix']
            && $this->nowUnix < (int) $authority['expires_at_unix'];
    }

    /**
     * @param array<string,mixed> $readiness
     * @param array<string,mixed> $context
     */
    private function readinessIsValid(array $readiness, array $context): bool
    {
        $contract = is_array($readiness['execution_contract'] ?? null)
            ? $readiness['execution_contract']
            : [];

        return ($readiness['schema_version'] ?? null) === self::READINESS_SCHEMA_VERSION
            && ($readiness['product'] ?? null) === 'oneQay'
            && ($readiness['readiness_state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED'
            && ($readiness['release_id'] ?? null) === $this->releaseId
            && ($readiness['request_id'] ?? null) === $context['request_id']
            && ($readiness['authority_id'] ?? null) === $context['authority_id']
            && is_string($readiness['qualification_fingerprint'] ?? null)
            && preg_match('/\A[0-9a-f]{64}\z/', (string) $readiness['qualification_fingerprint']) === 1
            && ($readiness['active_environment_sha256'] ?? null) === $context['active_sha256']
            && ($readiness['installation_completion_sha256'] ?? null) === $context['completion_sha256']
            && ($readiness['activation_request_sha256'] ?? null) === $context['request_sha256']
            && ($readiness['activation_authority_sha256'] ?? null) === $context['authority_sha256']
            && is_int($readiness['qualified_at_unix'] ?? null)
            && (int) $readiness['qualified_at_unix'] > 0
            && (int) $readiness['qualified_at_unix'] <= $this->nowUnix
            && ($readiness['authority_expires_at_unix'] ?? null) === $context['authority_expires_at_unix']
            && ($contract['target_environment_preflight_required'] ?? null) === true
            && ($contract['https_required'] ?? null) === true
            && ($contract['single_instance_required'] ?? null) === true
            && ($contract['private_file_session_directory_required'] ?? null) === true
            && ($contract['runtime_envelope_revalidation_required'] ?? null) === true
            && ($contract['preview_off_switch_verification_required'] ?? null) === true
            && ($contract['post_activation_health_check_required'] ?? null) === true
            && ($contract['rollback_recovery_required'] ?? null) === true
            && ($contract['migration_execution_required'] ?? null) === false
            && ($contract['synthetic_data_only'] ?? null) === true
            && ($contract['production_data_forbidden'] ?? null) === true
            && ($contract['consume_authority_on_success'] ?? null) === true
            && ($contract['consume_readiness_on_success'] ?? null) === true
            && ($readiness['authority_qualified'] ?? null) === true
            && ($readiness['activation_executed'] ?? null) === false
            && ($readiness['persistence_authorized'] ?? null) === false
            && ($readiness['migration_execution_authorized'] ?? null) === false
            && ($readiness['production_authorized'] ?? null) === false
            && ($readiness['updater_authorized'] ?? null) === false
            && ($readiness['deployment_authorized'] ?? null) === false
            && ($readiness['attribution'] ?? null) === 'Lab | zefry';
    }

    /** @param array<string,mixed> $payload */
    private function writeReadiness(array $payload): void
    {
        $directory = $this->installDirectory();
        if (! is_dir($directory) || is_link($directory)) {
            throw new RuntimeException('technical_preview_activation_readiness_boundary_unavailable');
        }

        $path = $this->readinessPath();
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

        if (file_exists($path) || is_link($path)) {
            $existingRaw = $this->readPrivateFile($path, self::MAX_JSON_BYTES);
            $existing = $existingRaw === null ? null : $this->decodeJson($existingRaw);
            $context = $this->loadContext();

            if ($existing !== null
                && $context !== null
                && $this->readinessIsValid($existing, $context)
                && hash_equals(
                    (string) ($existing['qualification_fingerprint'] ?? ''),
                    (string) ($payload['qualification_fingerprint'] ?? ''),
                )) {
                return;
            }

            throw new RuntimeException('technical_preview_activation_readiness_already_present');
        }

        $temporary = $directory.DIRECTORY_SEPARATOR.'technical-preview-activation-readiness.tmp.'.bin2hex(random_bytes(12));
        $handle = @fopen($temporary, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('technical_preview_activation_readiness_temp_unavailable');
        }

        try {
            @chmod($temporary, 0600);
            $length = strlen($json);
            $written = 0;
            while ($written < $length) {
                $result = fwrite($handle, substr($json, $written));
                if ($result === false || $result === 0) {
                    throw new RuntimeException('technical_preview_activation_readiness_write_failed');
                }
                $written += $result;
            }

            if (! fflush($handle)) {
                throw new RuntimeException('technical_preview_activation_readiness_flush_failed');
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

        if (! @rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('technical_preview_activation_readiness_commit_failed');
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
            $this->completionPath(),
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

    /** @return array{state:string,authority_present:bool,token_required:bool,execution_ready:bool,request_id:string,authority_id:string,activation_executed:false} */
    private function state(string $state, bool $authorityPresent, bool $tokenRequired, bool $ready): array
    {
        return [
            'state' => $state,
            'authority_present' => $authorityPresent,
            'token_required' => $tokenRequired,
            'execution_ready' => $ready,
            'request_id' => '',
            'authority_id' => '',
            'activation_executed' => false,
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

    private function completionPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'installation-completion.json';
    }

    private function requestPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'technical-preview-activation-request.json';
    }

    private function authorityPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'technical-preview-activation-authority.json';
    }

    private function readinessPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'technical-preview-activation-readiness.json';
    }
}
