<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootRuntimeConfigurationPromotionQualification
{
    public const AUTHORITY_SCHEMA_VERSION = 1;
    public const MAX_AUTHORITY_LIFETIME_SECONDS = 900;

    private const MAX_PENDING_BYTES = 65536;
    private const MAX_HANDOFF_BYTES = 16384;
    private const MAX_REQUEST_BYTES = 16384;
    private const MAX_AUTHORITY_BYTES = 16384;
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
            throw new RuntimeException('invalid_qualification_clock');
        }
    }

    /**
     * Inspect whether a separately provisioned authority is structurally
     * eligible for token qualification. This method never grants promotion.
     *
     * @return array{
     *   state:string,
     *   authority_present:bool,
     *   token_required:bool,
     *   promotion_qualified:false,
     *   promotion_executed:false
     * }
     */
    public function inspect(): array
    {
        try {
            $this->assertPrivateBoundaryShape();

            if (is_file($this->activeEnvironmentPath())) {
                return $this->state('ACTIVE_ENV_PRESENT', false, false);
            }

            $context = $this->loadBoundContext();
            if ($context === null) {
                return $this->state('PROMOTION_REQUEST_NOT_READY', false, false);
            }

            $authorityRaw = $this->readPrivateFile($this->authorityPath(), self::MAX_AUTHORITY_BYTES);
            if ($authorityRaw === null) {
                return $this->state('PROMOTION_AUTHORITY_MISSING', false, false);
            }

            $authority = $this->decodeJson($authorityRaw);
            if ($authority === null || ! $this->authorityIsBoundAndStructurallyValid($authority, $context)) {
                return $this->state('PROMOTION_AUTHORITY_INVALID', true, false);
            }

            if (! $this->authorityIsFresh($authority)) {
                return $this->state('PROMOTION_AUTHORITY_EXPIRED', true, false);
            }

            return $this->state('PROMOTION_AUTHORITY_TOKEN_REQUIRED', true, true);
        } catch (Throwable) {
            return $this->state('PROMOTION_AUTHORITY_INVALID', false, false);
        }
    }

    /**
     * Qualify exact-bound external authority with its out-of-band approval token.
     * No file is written and .env.pending is never promoted here.
     *
     * @return array{
     *   state:'PROMOTION_QUALIFIED_NOT_EXECUTED',
     *   authority_id:string,
     *   request_id:string,
     *   qualification_fingerprint:string,
     *   promotion_qualified:true,
     *   promotion_executed:false,
     *   technical_preview_authorized:false,
     *   production_authorized:false
     * }
     */
    public function qualify(string $approvalToken): array
    {
        $this->assertPrivateBoundaryShape();

        if (is_file($this->activeEnvironmentPath())) {
            throw new RuntimeException('active_environment_already_present');
        }

        if (
            strlen($approvalToken) < 32
            || strlen($approvalToken) > self::MAX_APPROVAL_TOKEN_BYTES
            || preg_match('/\A[A-Za-z0-9._~-]{32,256}\z/', $approvalToken) !== 1
        ) {
            throw new RuntimeException('promotion_authority_denied');
        }

        $context = $this->loadBoundContext();
        if ($context === null) {
            throw new RuntimeException('promotion_request_not_ready');
        }

        $authorityRaw = $this->readPrivateFile($this->authorityPath(), self::MAX_AUTHORITY_BYTES);
        if ($authorityRaw === null) {
            throw new RuntimeException('promotion_authority_missing');
        }

        $authority = $this->decodeJson($authorityRaw);
        if ($authority === null
            || ! $this->authorityIsBoundAndStructurallyValid($authority, $context)
            || ! $this->authorityIsFresh($authority)
            || ! hash_equals((string) $authority['approval_token_sha256'], hash('sha256', $approvalToken))) {
            throw new RuntimeException('promotion_authority_denied');
        }

        $fingerprint = hash('sha256', implode('|', [
            (string) $authority['authority_id'],
            (string) $authority['request_id'],
            $this->releaseId,
            $context['pending_sha256'],
            $context['handoff_sha256'],
            $context['request_sha256'],
        ]));

        return [
            'state' => 'PROMOTION_QUALIFIED_NOT_EXECUTED',
            'authority_id' => (string) $authority['authority_id'],
            'request_id' => (string) $authority['request_id'],
            'qualification_fingerprint' => $fingerprint,
            'promotion_qualified' => true,
            'promotion_executed' => false,
            'technical_preview_authorized' => false,
            'production_authorized' => false,
        ];
    }

    /**
     * @return array{
     *   pending_sha256:string,
     *   handoff_sha256:string,
     *   request_sha256:string,
     *   request_id:string
     * }|null
     */
    private function loadBoundContext(): ?array
    {
        $requestInspection = (new PrebootRuntimeConfigurationPromotionRequest(
            $this->sharedRoot,
            $this->releaseId,
            $this->nowUnix,
        ))->inspect();

        if (($requestInspection['request_ready'] ?? false) !== true) {
            return null;
        }

        $pending = $this->readPrivateFile($this->pendingEnvironmentPath(), self::MAX_PENDING_BYTES);
        $handoffRaw = $this->readPrivateFile($this->activationReadinessPath(), self::MAX_HANDOFF_BYTES);
        $requestRaw = $this->readPrivateFile($this->requestPath(), self::MAX_REQUEST_BYTES);
        if ($pending === null || $handoffRaw === null || $requestRaw === null) {
            return null;
        }

        $request = $this->decodeJson($requestRaw);
        if ($request === null || ! is_string($request['request_id'] ?? null)) {
            return null;
        }

        return [
            'pending_sha256' => hash('sha256', $pending),
            'handoff_sha256' => hash('sha256', $handoffRaw),
            'request_sha256' => hash('sha256', $requestRaw),
            'request_id' => (string) $request['request_id'],
        ];
    }

    /**
     * @param array<string,mixed> $authority
     * @param array{
     *   pending_sha256:string,
     *   handoff_sha256:string,
     *   request_sha256:string,
     *   request_id:string
     * } $context
     */
    private function authorityIsBoundAndStructurallyValid(array $authority, array $context): bool
    {
        return ($authority['schema_version'] ?? null) === self::AUTHORITY_SCHEMA_VERSION
            && ($authority['product'] ?? null) === 'oneQay'
            && is_string($authority['authority_id'] ?? null)
            && preg_match('/\Apromotion-authority-[0-9a-f]{24}\z/', (string) $authority['authority_id']) === 1
            && ($authority['authority_state'] ?? null) === 'GRANTED'
            && ($authority['scope'] ?? null) === 'PROMOTE_VERIFIED_PENDING_ENVIRONMENT_TO_ACTIVE_RUNTIME_CONFIGURATION'
            && ($authority['request_id'] ?? null) === $context['request_id']
            && ($authority['release_id'] ?? null) === $this->releaseId
            && ($authority['pending_environment_sha256'] ?? null) === $context['pending_sha256']
            && ($authority['activation_readiness_sha256'] ?? null) === $context['handoff_sha256']
            && ($authority['promotion_request_sha256'] ?? null) === $context['request_sha256']
            && is_string($authority['approval_token_sha256'] ?? null)
            && preg_match('/\A[0-9a-f]{64}\z/', (string) $authority['approval_token_sha256']) === 1
            && is_int($authority['authorized_at_unix'] ?? null)
            && is_int($authority['expires_at_unix'] ?? null)
            && (int) $authority['authorized_at_unix'] > 0
            && (int) $authority['expires_at_unix'] > (int) $authority['authorized_at_unix']
            && ((int) $authority['expires_at_unix'] - (int) $authority['authorized_at_unix']) <= self::MAX_AUTHORITY_LIFETIME_SECONDS
            && ($authority['single_use'] ?? null) === true
            && ($authority['promotion_authorized'] ?? null) === true
            && ($authority['migration_execution_authorized'] ?? null) === false
            && ($authority['technical_preview_authorized'] ?? null) === false
            && ($authority['production_authorized'] ?? null) === false
            && ($authority['updater_authorized'] ?? null) === false
            && ($authority['deployment_authorized'] ?? null) === false
            && ($authority['attribution'] ?? null) === 'Lab | zefry';
    }

    /** @param array<string,mixed> $authority */
    private function authorityIsFresh(array $authority): bool
    {
        return $this->nowUnix >= (int) $authority['authorized_at_unix']
            && $this->nowUnix < (int) $authority['expires_at_unix'];
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

    /**
     * @return array{
     *   state:string,
     *   authority_present:bool,
     *   token_required:bool,
     *   promotion_qualified:false,
     *   promotion_executed:false
     * }
     */
    private function state(string $state, bool $authorityPresent, bool $tokenRequired): array
    {
        return [
            'state' => $state,
            'authority_present' => $authorityPresent,
            'token_required' => $tokenRequired,
            'promotion_qualified' => false,
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
}
