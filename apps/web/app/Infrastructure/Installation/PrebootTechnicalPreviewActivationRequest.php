<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootTechnicalPreviewActivationRequest
{
    private const MAX_ENVIRONMENT_BYTES = 65536;
    private const MAX_JSON_BYTES = 16384;

    public function __construct(
        private readonly string $sharedRoot,
        private readonly string $releaseId,
    ) {
        if (! preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId)) {
            throw new RuntimeException('invalid_release_identity');
        }
    }

    /**
     * @return array{
     *   state:string,
     *   request_ready:bool,
     *   request_id:string,
     *   technical_preview_authorized:false
     * }
     */
    public function inspect(): array
    {
        $this->assertPrivateBoundaryShape();

        $context = $this->loadContext();
        if ($context === null) {
            return $this->state('TECHNICAL_PREVIEW_ACTIVATION_REQUEST_NOT_READY', false);
        }

        if (! is_file($this->requestPath())) {
            return [
                'state' => 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_READY_TO_CREATE',
                'request_ready' => false,
                'request_id' => $this->requestId($context['active_sha256'], $context['completion_sha256']),
                'technical_preview_authorized' => false,
            ];
        }

        $requestRaw = $this->readPrivateFile($this->requestPath(), self::MAX_JSON_BYTES);
        $request = $requestRaw === null ? null : $this->decodeJson($requestRaw);

        if ($request === null || ! $this->requestMatchesContext($request, $context)) {
            return $this->state('TECHNICAL_PREVIEW_ACTIVATION_REQUEST_INVALID', false);
        }

        return [
            'state' => 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_PENDING_APPROVAL',
            'request_ready' => true,
            'request_id' => (string) $request['request_id'],
            'technical_preview_authorized' => false,
        ];
    }

    /**
     * @return array{
     *   state:'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_PENDING_APPROVAL',
     *   request_ready:true,
     *   request_id:string,
     *   request_sha256:string,
     *   technical_preview_authorized:false
     * }
     */
    public function create(): array
    {
        $this->assertPrivateBoundaryShape();

        $context = $this->loadContext();
        if ($context === null) {
            throw new RuntimeException('technical_preview_activation_request_not_ready');
        }

        $requestId = $this->requestId($context['active_sha256'], $context['completion_sha256']);

        if (is_file($this->requestPath())) {
            $existingRaw = $this->readPrivateFile($this->requestPath(), self::MAX_JSON_BYTES);
            $existing = $existingRaw === null ? null : $this->decodeJson($existingRaw);

            if ($existingRaw === null || $existing === null || ! $this->requestMatchesContext($existing, $context)) {
                throw new RuntimeException('technical_preview_activation_request_invalid');
            }

            return [
                'state' => 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_PENDING_APPROVAL',
                'request_ready' => true,
                'request_id' => $requestId,
                'request_sha256' => hash('sha256', $existingRaw),
                'technical_preview_authorized' => false,
            ];
        }

        $payload = [
            'schema_version' => 1,
            'product' => 'oneQay',
            'request_state' => 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_PENDING_APPROVAL',
            'request_id' => $requestId,
            'release_id' => $this->releaseId,
            'active_environment_sha256' => $context['active_sha256'],
            'installation_completion_sha256' => $context['completion_sha256'],
            'requested_operation' => 'ENABLE_SYNTHETIC_TECHNICAL_PREVIEW_RUNTIME',
            'requested_runtime_envelope' => [
                'runtime_class' => 'preview',
                'technical_preview_enabled' => true,
                'session_driver' => 'file',
                'session_lifetime_minutes' => 60,
                'session_encrypt' => true,
                'session_secure_cookie' => true,
                'session_cookie' => 'oneqay-preview-session',
                'persistence_enabled' => false,
                'system_update_control_plane_enabled' => false,
            ],
            'required_authority' => [
                'state' => 'NOT_GRANTED',
                'relative_path' => 'oneqay-preview/shared/install/technical-preview-activation-authority.json',
                'exact_release_required' => true,
                'exact_active_environment_required' => true,
                'exact_completion_required' => true,
                'single_use_required' => true,
                'separate_operational_authority_required' => true,
            ],
            'migration_execution_authorized' => false,
            'technical_preview_authorized' => false,
            'persistence_authorized' => false,
            'production_authorized' => false,
            'updater_authorized' => false,
            'deployment_authorized' => false,
            'attribution' => 'Lab | zefry',
        ];

        $this->writeJsonAtomic($this->requestPath(), $payload);

        $writtenRaw = $this->readPrivateFile($this->requestPath(), self::MAX_JSON_BYTES);
        $written = $writtenRaw === null ? null : $this->decodeJson($writtenRaw);

        if ($writtenRaw === null || $written === null || ! $this->requestMatchesContext($written, $context)) {
            @unlink($this->requestPath());
            throw new RuntimeException('technical_preview_activation_request_commit_failed');
        }

        return [
            'state' => 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_PENDING_APPROVAL',
            'request_ready' => true,
            'request_id' => $requestId,
            'request_sha256' => hash('sha256', $writtenRaw),
            'technical_preview_authorized' => false,
        ];
    }

    /**
     * @return array{active_sha256:string,completion_sha256:string}|null
     */
    private function loadContext(): ?array
    {
        $completionState = (new PrebootInstallationCompletionHandoff(
            $this->sharedRoot,
            $this->releaseId,
            time(),
        ))->inspect();

        if (($completionState['state'] ?? null) !== 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED'
            || ($completionState['complete'] ?? false) !== true
            || ($completionState['activation_authorized'] ?? true) !== false) {
            return null;
        }

        $activeRaw = $this->readPrivateFile($this->activeEnvironmentPath(), self::MAX_ENVIRONMENT_BYTES);
        $completionRaw = $this->readPrivateFile($this->completionPath(), self::MAX_JSON_BYTES);

        if ($activeRaw === null || $completionRaw === null || ! $this->activeEnvironmentIsRequestEligible($activeRaw)) {
            return null;
        }

        $completion = $this->decodeJson($completionRaw);
        if ($completion === null
            || ($completion['completion_state'] ?? null) !== 'INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED'
            || ($completion['release_id'] ?? null) !== $this->releaseId
            || ($completion['active_environment_sha256'] ?? null) !== hash('sha256', $activeRaw)
            || ($completion['technical_preview_authorized'] ?? null) !== false
            || ($completion['persistence_authorized'] ?? null) !== false
            || ($completion['production_authorized'] ?? null) !== false
            || ($completion['deployment_authorized'] ?? null) !== false) {
            return null;
        }

        return [
            'active_sha256' => hash('sha256', $activeRaw),
            'completion_sha256' => hash('sha256', $completionRaw),
        ];
    }

    /**
     * @param array<string,mixed> $request
     * @param array{active_sha256:string,completion_sha256:string} $context
     */
    private function requestMatchesContext(array $request, array $context): bool
    {
        $envelope = is_array($request['requested_runtime_envelope'] ?? null)
            ? $request['requested_runtime_envelope']
            : [];
        $authority = is_array($request['required_authority'] ?? null)
            ? $request['required_authority']
            : [];

        return ($request['schema_version'] ?? null) === 1
            && ($request['product'] ?? null) === 'oneQay'
            && ($request['request_state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_REQUEST_PENDING_APPROVAL'
            && ($request['request_id'] ?? null) === $this->requestId($context['active_sha256'], $context['completion_sha256'])
            && ($request['release_id'] ?? null) === $this->releaseId
            && ($request['active_environment_sha256'] ?? null) === $context['active_sha256']
            && ($request['installation_completion_sha256'] ?? null) === $context['completion_sha256']
            && ($request['requested_operation'] ?? null) === 'ENABLE_SYNTHETIC_TECHNICAL_PREVIEW_RUNTIME'
            && ($envelope['runtime_class'] ?? null) === 'preview'
            && ($envelope['technical_preview_enabled'] ?? null) === true
            && ($envelope['session_driver'] ?? null) === 'file'
            && ($envelope['session_lifetime_minutes'] ?? null) === 60
            && ($envelope['session_encrypt'] ?? null) === true
            && ($envelope['session_secure_cookie'] ?? null) === true
            && ($envelope['session_cookie'] ?? null) === 'oneqay-preview-session'
            && ($envelope['persistence_enabled'] ?? null) === false
            && ($envelope['system_update_control_plane_enabled'] ?? null) === false
            && ($authority['state'] ?? null) === 'NOT_GRANTED'
            && ($authority['relative_path'] ?? null) === 'oneqay-preview/shared/install/technical-preview-activation-authority.json'
            && ($authority['exact_release_required'] ?? null) === true
            && ($authority['exact_active_environment_required'] ?? null) === true
            && ($authority['exact_completion_required'] ?? null) === true
            && ($authority['single_use_required'] ?? null) === true
            && ($authority['separate_operational_authority_required'] ?? null) === true
            && ($request['migration_execution_authorized'] ?? null) === false
            && ($request['technical_preview_authorized'] ?? null) === false
            && ($request['persistence_authorized'] ?? null) === false
            && ($request['production_authorized'] ?? null) === false
            && ($request['updater_authorized'] ?? null) === false
            && ($request['deployment_authorized'] ?? null) === false
            && ($request['attribution'] ?? null) === 'Lab | zefry';
    }

    private function activeEnvironmentIsRequestEligible(string $content): bool
    {
        foreach ([
            'ONEQAY_RUNTIME_CLASS="preview"',
            'ONEQAY_TECHNICAL_PREVIEW_ENABLED="false"',
            'ONEQAY_PERSISTENCE_ENABLED="false"',
            'ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED="false"',
            'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="false"',
            'SESSION_DRIVER="file"',
        ] as $required) {
            if (! str_contains($content, $required)) {
                return false;
            }
        }

        return ! str_contains($content, 'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"')
            && ! str_contains($content, 'ONEQAY_PERSISTENCE_ENABLED="true"')
            && ! str_contains($content, 'ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED="true"')
            && ! str_contains($content, 'ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED="true"');
    }

    private function requestId(string $activeSha256, string $completionSha256): string
    {
        return 'technical-preview-activation-request-'.substr(
            hash('sha256', $this->releaseId.'|'.$activeSha256.'|'.$completionSha256),
            0,
            24,
        );
    }

    private function writeJsonAtomic(string $path, array $payload): void
    {
        $directory = $this->installDirectory();
        if (! is_dir($directory) || is_link($directory)) {
            throw new RuntimeException('technical_preview_activation_request_boundary_unavailable');
        }

        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
        $temporary = $directory.DIRECTORY_SEPARATOR.'technical-preview-activation-request.tmp.'.bin2hex(random_bytes(12));
        $handle = @fopen($temporary, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('technical_preview_activation_request_temp_unavailable');
        }

        try {
            @chmod($temporary, 0600);
            if (fwrite($handle, $json) !== strlen($json) || ! fflush($handle)) {
                throw new RuntimeException('technical_preview_activation_request_write_failed');
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
            throw new RuntimeException('technical_preview_activation_request_commit_failed');
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

    /** @return array{state:string,request_ready:bool,request_id:string,technical_preview_authorized:false} */
    private function state(string $state, bool $ready): array
    {
        return [
            'state' => $state,
            'request_ready' => $ready,
            'request_id' => '',
            'technical_preview_authorized' => false,
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
}
