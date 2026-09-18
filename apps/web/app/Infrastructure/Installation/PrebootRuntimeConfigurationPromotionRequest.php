<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootRuntimeConfigurationPromotionRequest
{
    public const REQUEST_SCHEMA_VERSION = 1;

    private const MAX_PENDING_BYTES = 65536;
    private const MAX_HANDOFF_BYTES = 16384;
    private const MAX_REQUEST_BYTES = 16384;

    public function __construct(
        private readonly string $sharedRoot,
        private readonly string $releaseId,
        private readonly int $nowUnix,
    ) {
        if (! preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId)) {
            throw new RuntimeException('invalid_release_identity');
        }
    }

    /**
     * Materialize a private approval request bound to the exact sealed handoff.
     * This method does not grant promotion authority and does not create .env.
     *
     * @return array{
     *   state: string,
     *   request_id: string,
     *   promotion_authorized: false,
     *   technical_preview_authorized: false,
     *   production_authorized: false
     * }
     */
    public function create(): array
    {
        $this->assertPrivateBoundaryShape();

        if (is_file($this->activeEnvironmentPath())) {
            throw new RuntimeException('active_environment_already_present');
        }

        $pending = $this->readPrivateFile($this->pendingEnvironmentPath(), self::MAX_PENDING_BYTES);
        $handoffRaw = $this->readPrivateFile($this->activationReadinessPath(), self::MAX_HANDOFF_BYTES);
        if ($pending === null || $handoffRaw === null) {
            throw new RuntimeException('activation_handoff_not_ready');
        }

        $handoff = $this->decodeJson($handoffRaw);
        if ($handoff === null || ! $this->handoffIsEligible($handoff, $pending)) {
            throw new RuntimeException('activation_handoff_not_ready');
        }

        $pendingSha256 = hash('sha256', $pending);
        $handoffSha256 = hash('sha256', $handoffRaw);
        $requestId = 'promotion-request-'.substr(
            hash('sha256', $this->releaseId.'|'.$pendingSha256.'|'.$handoffSha256),
            0,
            24,
        );

        $request = [
            'schema_version' => self::REQUEST_SCHEMA_VERSION,
            'product' => 'oneQay',
            'request_id' => $requestId,
            'request_state' => 'PENDING_APPROVAL',
            'release_id' => $this->releaseId,
            'requested_operation' => 'PROMOTE_VERIFIED_PENDING_ENVIRONMENT_TO_ACTIVE_RUNTIME_CONFIGURATION',
            'pending_environment_sha256' => $pendingSha256,
            'activation_readiness_sha256' => $handoffSha256,
            'created_at_unix' => $this->nowUnix,
            'requested_effect' => [
                'source_relative_path' => 'oneqay-preview/shared/runtime/.env.pending',
                'target_relative_path' => 'oneqay-preview/shared/runtime/.env',
                'preserve_configuration_bytes_exactly' => true,
                'technical_preview_flag_change_requested' => false,
                'persistence_flag_change_requested' => false,
                'updater_flag_change_requested' => false,
                'migration_execution_requested' => false,
            ],
            'required_approval' => [
                'authority_state' => 'NOT_GRANTED',
                'authority_relative_path' => 'oneqay-preview/shared/install/runtime-configuration-promotion-authority.json',
                'exact_release_required' => true,
                'exact_pending_environment_sha256_required' => true,
                'exact_activation_readiness_sha256_required' => true,
                'single_use_required' => true,
                'separate_operational_authority_required' => true,
            ],
            'promotion_authorized' => false,
            'migration_execution_authorized' => false,
            'technical_preview_authorized' => false,
            'production_authorized' => false,
            'updater_authorized' => false,
            'deployment_authorized' => false,
            'attribution' => 'Lab | zefry',
        ];

        $this->writeRequest($request);

        return [
            'state' => 'PROMOTION_REQUEST_PENDING_APPROVAL',
            'request_id' => $requestId,
            'promotion_authorized' => false,
            'technical_preview_authorized' => false,
            'production_authorized' => false,
        ];
    }

    /**
     * @return array{
     *   state: string,
     *   request_ready: bool,
     *   request_id: string,
     *   promotion_authorized: false
     * }
     */
    public function inspect(): array
    {
        try {
            $this->assertPrivateBoundaryShape();

            if (is_file($this->activeEnvironmentPath())) {
                return $this->state('ACTIVE_ENV_PRESENT', false);
            }

            $pending = $this->readPrivateFile($this->pendingEnvironmentPath(), self::MAX_PENDING_BYTES);
            $handoffRaw = $this->readPrivateFile($this->activationReadinessPath(), self::MAX_HANDOFF_BYTES);
            $requestRaw = $this->readPrivateFile($this->requestPath(), self::MAX_REQUEST_BYTES);

            if ($pending === null || $handoffRaw === null) {
                return $this->state('ACTIVATION_HANDOFF_NOT_READY', false);
            }

            if ($requestRaw === null) {
                return $this->state('PROMOTION_REQUEST_MISSING', false);
            }

            $handoff = $this->decodeJson($handoffRaw);
            $request = $this->decodeJson($requestRaw);
            if ($handoff === null
                || $request === null
                || ! $this->handoffIsEligible($handoff, $pending)
                || ! $this->requestIsValid($request, $pending, $handoffRaw)) {
                return $this->state('PROMOTION_REQUEST_INVALID', false);
            }

            return [
                'state' => 'PROMOTION_REQUEST_PENDING_APPROVAL',
                'request_ready' => true,
                'request_id' => (string) $request['request_id'],
                'promotion_authorized' => false,
            ];
        } catch (Throwable) {
            return $this->state('PROMOTION_REQUEST_INVALID', false);
        }
    }

    /** @param array<string, mixed> $handoff */
    private function handoffIsEligible(array $handoff, string $pending): bool
    {
        return ($handoff['schema_version'] ?? null) === PrebootInstallationActivationReadiness::ATTESTATION_SCHEMA_VERSION
            && ($handoff['product'] ?? null) === 'oneQay'
            && ($handoff['release_id'] ?? null) === $this->releaseId
            && ($handoff['pending_environment_sha256'] ?? null) === hash('sha256', $pending)
            && ($handoff['pending_environment_bytes'] ?? null) === strlen($pending)
            && ($handoff['activation_authorized'] ?? null) === false
            && ($handoff['migration_execution_authorized'] ?? null) === false
            && ($handoff['technical_preview_authorized'] ?? null) === false
            && ($handoff['production_authorized'] ?? null) === false
            && ($handoff['updater_authorized'] ?? null) === false
            && ($handoff['attribution'] ?? null) === 'Lab | zefry';
    }

    /** @param array<string, mixed> $request */
    private function requestIsValid(array $request, string $pending, string $handoffRaw): bool
    {
        $requestedEffect = is_array($request['requested_effect'] ?? null)
            ? $request['requested_effect']
            : [];
        $requiredApproval = is_array($request['required_approval'] ?? null)
            ? $request['required_approval']
            : [];

        $expectedPendingSha256 = hash('sha256', $pending);
        $expectedHandoffSha256 = hash('sha256', $handoffRaw);
        $expectedRequestId = 'promotion-request-'.substr(
            hash('sha256', $this->releaseId.'|'.$expectedPendingSha256.'|'.$expectedHandoffSha256),
            0,
            24,
        );

        return ($request['schema_version'] ?? null) === self::REQUEST_SCHEMA_VERSION
            && ($request['product'] ?? null) === 'oneQay'
            && ($request['request_id'] ?? null) === $expectedRequestId
            && ($request['request_state'] ?? null) === 'PENDING_APPROVAL'
            && ($request['release_id'] ?? null) === $this->releaseId
            && ($request['requested_operation'] ?? null) === 'PROMOTE_VERIFIED_PENDING_ENVIRONMENT_TO_ACTIVE_RUNTIME_CONFIGURATION'
            && ($request['pending_environment_sha256'] ?? null) === $expectedPendingSha256
            && ($request['activation_readiness_sha256'] ?? null) === $expectedHandoffSha256
            && is_int($request['created_at_unix'] ?? null)
            && (int) $request['created_at_unix'] > 0
            && ($requestedEffect['source_relative_path'] ?? null) === 'oneqay-preview/shared/runtime/.env.pending'
            && ($requestedEffect['target_relative_path'] ?? null) === 'oneqay-preview/shared/runtime/.env'
            && ($requestedEffect['preserve_configuration_bytes_exactly'] ?? null) === true
            && ($requestedEffect['technical_preview_flag_change_requested'] ?? null) === false
            && ($requestedEffect['persistence_flag_change_requested'] ?? null) === false
            && ($requestedEffect['updater_flag_change_requested'] ?? null) === false
            && ($requestedEffect['migration_execution_requested'] ?? null) === false
            && ($requiredApproval['authority_state'] ?? null) === 'NOT_GRANTED'
            && ($requiredApproval['authority_relative_path'] ?? null) === 'oneqay-preview/shared/install/runtime-configuration-promotion-authority.json'
            && ($requiredApproval['exact_release_required'] ?? null) === true
            && ($requiredApproval['exact_pending_environment_sha256_required'] ?? null) === true
            && ($requiredApproval['exact_activation_readiness_sha256_required'] ?? null) === true
            && ($requiredApproval['single_use_required'] ?? null) === true
            && ($requiredApproval['separate_operational_authority_required'] ?? null) === true
            && ($request['promotion_authorized'] ?? null) === false
            && ($request['migration_execution_authorized'] ?? null) === false
            && ($request['technical_preview_authorized'] ?? null) === false
            && ($request['production_authorized'] ?? null) === false
            && ($request['updater_authorized'] ?? null) === false
            && ($request['deployment_authorized'] ?? null) === false
            && ($request['attribution'] ?? null) === 'Lab | zefry';
    }

    /** @param array<string, mixed> $request */
    private function writeRequest(array $request): void
    {
        $installDirectory = $this->installDirectory();
        if (! is_dir($installDirectory) || is_link($installDirectory)) {
            throw new RuntimeException('installation_request_boundary_missing');
        }

        $path = $this->requestPath();
        if (file_exists($path) || is_link($path)) {
            $existingRaw = $this->readPrivateFile($path, self::MAX_REQUEST_BYTES);
            $existing = $existingRaw === null ? null : $this->decodeJson($existingRaw);
            $pending = $this->readPrivateFile($this->pendingEnvironmentPath(), self::MAX_PENDING_BYTES);
            $handoffRaw = $this->readPrivateFile($this->activationReadinessPath(), self::MAX_HANDOFF_BYTES);

            if ($existing !== null
                && $pending !== null
                && $handoffRaw !== null
                && $this->requestIsValid($existing, $pending, $handoffRaw)
                && ($existing['request_id'] ?? null) === ($request['request_id'] ?? null)) {
                return;
            }

            throw new RuntimeException('promotion_request_already_present');
        }

        $json = json_encode(
            $request,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        ).PHP_EOL;

        $temporaryPath = $installDirectory.DIRECTORY_SEPARATOR.'promotion-request.tmp.'.bin2hex(random_bytes(12));
        $handle = @fopen($temporaryPath, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('promotion_request_temp_unavailable');
        }

        try {
            @chmod($temporaryPath, 0600);
            $length = strlen($json);
            $written = 0;
            while ($written < $length) {
                $result = fwrite($handle, substr($json, $written));
                if ($result === false || $result === 0) {
                    throw new RuntimeException('promotion_request_write_failed');
                }
                $written += $result;
            }

            if (! fflush($handle)) {
                throw new RuntimeException('promotion_request_flush_failed');
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
            throw new RuntimeException('promotion_request_commit_failed');
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

    /** @return array<string, mixed>|null */
    private function decodeJson(string $json): ?array
    {
        try {
            $decoded = json_decode($json, true, 32, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array{state: string, request_ready: bool, request_id: string, promotion_authorized: false} */
    private function state(string $state, bool $ready): array
    {
        return [
            'state' => $state,
            'request_ready' => $ready,
            'request_id' => '',
            'promotion_authorized' => false,
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
}
