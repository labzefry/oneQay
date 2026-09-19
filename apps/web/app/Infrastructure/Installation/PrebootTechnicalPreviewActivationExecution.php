<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use Closure;
use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootTechnicalPreviewActivationExecution
{
    public const RECEIPT_SCHEMA_VERSION = 1;
    public const RECOVERY_SCHEMA_VERSION = 1;

    private const MAX_ENVIRONMENT_BYTES = 65536;
    private const MAX_JSON_BYTES = 32768;
    private const MAX_APPROVAL_TOKEN_BYTES = 256;

    public function __construct(
        private readonly string $sharedRoot,
        private readonly string $releaseId,
        private readonly int $nowUnix,
        private readonly Closure $healthProbe,
    ) {
        if (! preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId)) {
            throw new RuntimeException('invalid_release_identity');
        }

        if ($nowUnix <= 0) {
            throw new RuntimeException('invalid_activation_execution_clock');
        }
    }

    /**
     * @return array{
     *   state:string,
     *   execution_ready:bool,
     *   active_healthy:bool,
     *   activation_executed:bool,
     *   request_id:string,
     *   authority_id:string
     * }
     */
    public function inspect(): array
    {
        try {
            $this->assertBoundaryShape();

            $receiptRaw = $this->readPrivateFile($this->receiptPath(), self::MAX_JSON_BYTES);
            if ($receiptRaw !== null) {
                $receipt = $this->decodeJson($receiptRaw);
                if ($receipt !== null && $this->receiptIsValid($receipt)) {
                    return [
                        'state' => 'TECHNICAL_PREVIEW_ACTIVE_HEALTHY',
                        'execution_ready' => false,
                        'active_healthy' => true,
                        'activation_executed' => true,
                        'request_id' => (string) $receipt['request_id'],
                        'authority_id' => (string) $receipt['authority_id'],
                    ];
                }

                return $this->state('TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_INVALID', false, false, false);
            }

            $preflight = $this->targetPreflight()->inspect();
            if (($preflight['state'] ?? null) === 'TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT_PASSED_NOT_ACTIVATED'
                && ($preflight['preflight_passed'] ?? false) === true
                && ($preflight['activation_executed'] ?? true) === false) {
                return [
                    'state' => 'TECHNICAL_PREVIEW_ACTIVATION_READY_TO_EXECUTE',
                    'execution_ready' => true,
                    'active_healthy' => false,
                    'activation_executed' => false,
                    'request_id' => (string) ($preflight['request_id'] ?? ''),
                    'authority_id' => (string) ($preflight['authority_id'] ?? ''),
                ];
            }

            if (($preflight['state'] ?? null) === 'TECHNICAL_PREVIEW_TARGET_PREFLIGHT_AUTHORITY_EXPIRED') {
                return $this->state('TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_EXPIRED', false, false, false);
            }

            return $this->state('TECHNICAL_PREVIEW_ACTIVATION_NOT_READY', false, false, false);
        } catch (Throwable) {
            return $this->state('TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_INVALID', false, false, false);
        }
    }

    /**
     * @return array{
     *   state:'TECHNICAL_PREVIEW_ACTIVE_HEALTHY',
     *   attempt_id:string,
     *   activation_fingerprint:string,
     *   active_healthy:true,
     *   activation_executed:true,
     *   rollback_performed:false
     * }
     */
    public function execute(string $approvalToken): array
    {
        $this->assertBoundaryShape();

        if (
            strlen($approvalToken) < 32
            || strlen($approvalToken) > self::MAX_APPROVAL_TOKEN_BYTES
            || preg_match('/\A[A-Za-z0-9._~-]{32,256}\z/', $approvalToken) !== 1
        ) {
            throw new RuntimeException('technical_preview_activation_execution_denied');
        }

        if (is_file($this->receiptPath()) || is_link($this->receiptPath())) {
            throw new RuntimeException('technical_preview_activation_already_executed');
        }

        $preflightState = $this->targetPreflight()->inspect();
        if (($preflightState['state'] ?? null) !== 'TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT_PASSED_NOT_ACTIVATED'
            || ($preflightState['preflight_passed'] ?? false) !== true
            || ($preflightState['activation_executed'] ?? true) !== false) {
            throw new RuntimeException('technical_preview_activation_preflight_not_ready');
        }

        $activeRaw = $this->readPrivateFile($this->activeEnvironmentPath(), self::MAX_ENVIRONMENT_BYTES);
        $readinessRaw = $this->readPrivateFile($this->readinessPath(), self::MAX_JSON_BYTES);
        $preflightRaw = $this->readPrivateFile($this->preflightPath(), self::MAX_JSON_BYTES);
        $authorityRaw = $this->readPrivateFile($this->authorityPath(), self::MAX_JSON_BYTES);

        if ($activeRaw === null || $readinessRaw === null || $preflightRaw === null || $authorityRaw === null) {
            throw new RuntimeException('technical_preview_activation_execution_denied');
        }

        $environment = $this->parseEnvironment($activeRaw);
        $readiness = $this->decodeJson($readinessRaw);
        $preflight = $this->decodeJson($preflightRaw);
        $authority = $this->decodeJson($authorityRaw);

        if ($readiness === null
            || $preflight === null
            || $authority === null
            || ! $this->executionInputsAreValid(
                $environment,
                $activeRaw,
                $readiness,
                $readinessRaw,
                $preflight,
                $preflightRaw,
                $authority,
                $authorityRaw,
                $approvalToken,
            )) {
            throw new RuntimeException('technical_preview_activation_execution_denied');
        }

        $expectedHost = $this->httpsHost((string) ($environment['APP_URL'] ?? ''));
        $sessionDirectory = (string) ($environment['SESSION_FILES'] ?? '');
        if ($expectedHost === null || $sessionDirectory === '') {
            throw new RuntimeException('technical_preview_activation_execution_denied');
        }

        $attemptId = 'technical-preview-activation-attempt-'.bin2hex(random_bytes(12));
        $activatedRaw = $this->activatedEnvironment($activeRaw);
        $originalSha256 = hash('sha256', $activeRaw);
        $activatedSha256 = hash('sha256', $activatedRaw);
        $readinessSha256 = hash('sha256', $readinessRaw);
        $preflightSha256 = hash('sha256', $preflightRaw);
        $authoritySha256 = hash('sha256', $authorityRaw);

        $activationFingerprint = hash('sha256', implode('|', [
            $attemptId,
            $this->releaseId,
            (string) $readiness['request_id'],
            (string) $readiness['authority_id'],
            $originalSha256,
            $activatedSha256,
            $readinessSha256,
            $preflightSha256,
            $authoritySha256,
        ]));

        $mutated = false;

        try {
            $this->replaceActiveEnvironment($activatedRaw);
            $mutated = true;

            $writtenRaw = $this->readPrivateFile($this->activeEnvironmentPath(), self::MAX_ENVIRONMENT_BYTES);
            if ($writtenRaw === null || ! hash_equals($activatedSha256, hash('sha256', $writtenRaw))) {
                throw new RuntimeException('activation_environment_commit_failed');
            }

            $health = ($this->healthProbe)($expectedHost, $sessionDirectory, $this->releaseId);
            if (! is_array($health) || ! $this->healthResultIsHealthy($health)) {
                throw new RuntimeException('post_activation_health_failed');
            }

            $receipt = [
                'schema_version' => self::RECEIPT_SCHEMA_VERSION,
                'product' => 'oneQay',
                'execution_state' => 'TECHNICAL_PREVIEW_ACTIVE_HEALTHY',
                'attempt_id' => $attemptId,
                'release_id' => $this->releaseId,
                'request_id' => (string) $readiness['request_id'],
                'authority_id' => (string) $readiness['authority_id'],
                'activation_fingerprint' => $activationFingerprint,
                'previous_environment_sha256' => $originalSha256,
                'active_environment_sha256' => $activatedSha256,
                'activation_readiness_sha256' => $readinessSha256,
                'target_preflight_sha256' => $preflightSha256,
                'activation_authority_sha256' => $authoritySha256,
                'activated_at_unix' => $this->nowUnix,
                'authority_expires_at_unix' => (int) $readiness['authority_expires_at_unix'],
                'health' => [
                    'liveness_passed' => true,
                    'readiness_passed' => true,
                    'preview_surface_passed' => true,
                    'runtime_policy_passed' => true,
                    'session_contract_passed' => true,
                ],
                'post_activation_health_passed' => true,
                'authority_consumed' => true,
                'readiness_consumed' => true,
                'preflight_consumed' => true,
                'activation_executed' => true,
                'rollback_performed' => false,
                'migration_execution_authorized' => false,
                'persistence_authorized' => false,
                'production_authorized' => false,
                'updater_authorized' => false,
                'deployment_authorized' => false,
                'attribution' => 'Lab | zefry',
            ];

            $this->writePrivateJsonOnce($this->receiptPath(), $receipt);

            $receiptRaw = $this->readPrivateFile($this->receiptPath(), self::MAX_JSON_BYTES);
            $writtenReceipt = $receiptRaw === null ? null : $this->decodeJson($receiptRaw);
            if ($writtenReceipt === null || ! $this->receiptIsValid($writtenReceipt)) {
                throw new RuntimeException('activation_receipt_commit_failed');
            }

            return [
                'state' => 'TECHNICAL_PREVIEW_ACTIVE_HEALTHY',
                'attempt_id' => $attemptId,
                'activation_fingerprint' => $activationFingerprint,
                'active_healthy' => true,
                'activation_executed' => true,
                'rollback_performed' => false,
            ];
        } catch (Throwable $failure) {
            if (! $mutated) {
                throw new RuntimeException('technical_preview_activation_execution_failed');
            }

            $this->replaceActiveEnvironment($activeRaw);
            $restored = $this->readPrivateFile($this->activeEnvironmentPath(), self::MAX_ENVIRONMENT_BYTES);
            if ($restored === null
                || ! hash_equals($originalSha256, hash('sha256', $restored))
                || str_contains($restored, 'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"')) {
                throw new RuntimeException('technical_preview_activation_rollback_failed');
            }

            if (is_file($this->receiptPath())) {
                @unlink($this->receiptPath());
            }

            $safeCode = $failure->getMessage() === 'post_activation_health_failed'
                ? 'post_activation_health_failed'
                : 'activation_commit_failed';

            $recovery = [
                'schema_version' => self::RECOVERY_SCHEMA_VERSION,
                'product' => 'oneQay',
                'recovery_state' => 'TECHNICAL_PREVIEW_ACTIVATION_ROLLED_BACK',
                'attempt_id' => $attemptId,
                'release_id' => $this->releaseId,
                'request_id' => (string) $readiness['request_id'],
                'authority_id' => (string) $readiness['authority_id'],
                'safe_code' => $safeCode,
                'previous_environment_sha256' => $originalSha256,
                'attempted_environment_sha256' => $activatedSha256,
                'activation_readiness_sha256' => $readinessSha256,
                'target_preflight_sha256' => $preflightSha256,
                'activation_authority_sha256' => $authoritySha256,
                'attempted_at_unix' => $this->nowUnix,
                'rollback_verified' => true,
                'activation_executed' => false,
                'post_activation_health_passed' => false,
                'authority_consumed' => false,
                'readiness_consumed' => false,
                'preflight_consumed' => false,
                'migration_execution_authorized' => false,
                'persistence_authorized' => false,
                'production_authorized' => false,
                'updater_authorized' => false,
                'deployment_authorized' => false,
                'attribution' => 'Lab | zefry',
            ];

            $this->writePrivateJsonOnce($this->recoveryPath($attemptId), $recovery);

            throw new RuntimeException('technical_preview_activation_failed_rolled_back');
        }
    }

    /**
     * @param array<string,string> $environment
     * @param array<string,mixed> $readiness
     * @param array<string,mixed> $preflight
     * @param array<string,mixed> $authority
     */
    private function executionInputsAreValid(
        array $environment,
        string $activeRaw,
        array $readiness,
        string $readinessRaw,
        array $preflight,
        string $preflightRaw,
        array $authority,
        string $authorityRaw,
        string $approvalToken,
    ): bool {
        $activeSha256 = hash('sha256', $activeRaw);
        $readinessSha256 = hash('sha256', $readinessRaw);
        $authoritySha256 = hash('sha256', $authorityRaw);

        return ($environment['ONEQAY_RUNTIME_CLASS'] ?? null) === 'preview'
            && ($environment['ONEQAY_TECHNICAL_PREVIEW_ENABLED'] ?? null) === 'false'
            && ($environment['ONEQAY_PREVIEW_INSTANCE_MODE'] ?? null) === 'single'
            && ($environment['ONEQAY_PREVIEW_DATA_CLASS'] ?? null) === 'synthetic'
            && ($environment['ONEQAY_PRODUCTION_DATA_ALLOWED'] ?? null) === 'false'
            && ($environment['ONEQAY_PERSISTENCE_ENABLED'] ?? null) === 'false'
            && ($environment['ONEQAY_INSTALLATION_RELEASE_ID'] ?? null) === $this->releaseId
            && ($readiness['readiness_state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED'
            && ($readiness['release_id'] ?? null) === $this->releaseId
            && ($readiness['active_environment_sha256'] ?? null) === $activeSha256
            && ($readiness['activation_authority_sha256'] ?? null) === $authoritySha256
            && ($readiness['activation_executed'] ?? null) === false
            && is_string($readiness['request_id'] ?? null)
            && is_string($readiness['authority_id'] ?? null)
            && is_int($readiness['authority_expires_at_unix'] ?? null)
            && $this->nowUnix < (int) $readiness['authority_expires_at_unix']
            && ($preflight['preflight_state'] ?? null) === 'TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT_PASSED_NOT_ACTIVATED'
            && ($preflight['release_id'] ?? null) === $this->releaseId
            && ($preflight['request_id'] ?? null) === $readiness['request_id']
            && ($preflight['authority_id'] ?? null) === $readiness['authority_id']
            && ($preflight['activation_readiness_sha256'] ?? null) === $readinessSha256
            && ($preflight['active_environment_sha256'] ?? null) === $activeSha256
            && ($preflight['authority_expires_at_unix'] ?? null) === $readiness['authority_expires_at_unix']
            && ($preflight['post_activation_health_check_required'] ?? null) === true
            && ($preflight['post_activation_health_check_executed'] ?? null) === false
            && ($preflight['activation_executed'] ?? null) === false
            && ($authority['authority_state'] ?? null) === 'GRANTED'
            && ($authority['scope'] ?? null) === 'ENABLE_SYNTHETIC_TECHNICAL_PREVIEW_RUNTIME'
            && ($authority['release_id'] ?? null) === $this->releaseId
            && ($authority['request_id'] ?? null) === $readiness['request_id']
            && ($authority['authority_id'] ?? null) === $readiness['authority_id']
            && ($authority['active_environment_sha256'] ?? null) === $activeSha256
            && is_int($authority['authorized_at_unix'] ?? null)
            && is_int($authority['expires_at_unix'] ?? null)
            && $this->nowUnix >= (int) $authority['authorized_at_unix']
            && $this->nowUnix < (int) $authority['expires_at_unix']
            && ($authority['expires_at_unix'] ?? null) === $readiness['authority_expires_at_unix']
            && ($authority['single_use'] ?? null) === true
            && ($authority['technical_preview_authorized'] ?? null) === true
            && ($authority['production_authorized'] ?? null) === false
            && ($authority['persistence_authorized'] ?? null) === false
            && ($authority['migration_execution_authorized'] ?? null) === false
            && is_string($authority['approval_token_sha256'] ?? null)
            && hash_equals((string) $authority['approval_token_sha256'], hash('sha256', $approvalToken))
            && $this->isSha256($preflight['target_fingerprint'] ?? null);
    }

    /** @param array<string,mixed> $health */
    private function healthResultIsHealthy(array $health): bool
    {
        foreach ([
            'liveness_passed',
            'readiness_passed',
            'preview_surface_passed',
            'runtime_policy_passed',
            'session_contract_passed',
        ] as $key) {
            if (($health[$key] ?? null) !== true) {
                return false;
            }
        }

        return true;
    }

    /** @param array<string,mixed> $receipt */
    private function receiptIsValid(array $receipt): bool
    {
        $activeRaw = $this->readPrivateFile($this->activeEnvironmentPath(), self::MAX_ENVIRONMENT_BYTES);
        $readinessRaw = $this->readPrivateFile($this->readinessPath(), self::MAX_JSON_BYTES);
        $preflightRaw = $this->readPrivateFile($this->preflightPath(), self::MAX_JSON_BYTES);
        $authorityRaw = $this->readPrivateFile($this->authorityPath(), self::MAX_JSON_BYTES);
        if ($activeRaw === null || $readinessRaw === null || $preflightRaw === null || $authorityRaw === null) {
            return false;
        }

        $health = is_array($receipt['health'] ?? null) ? $receipt['health'] : [];

        return ($receipt['schema_version'] ?? null) === self::RECEIPT_SCHEMA_VERSION
            && ($receipt['product'] ?? null) === 'oneQay'
            && ($receipt['execution_state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVE_HEALTHY'
            && ($receipt['release_id'] ?? null) === $this->releaseId
            && is_string($receipt['attempt_id'] ?? null)
            && preg_match('/\Atechnical-preview-activation-attempt-[0-9a-f]{24}\z/', (string) $receipt['attempt_id']) === 1
            && $this->isSha256($receipt['activation_fingerprint'] ?? null)
            && ($receipt['active_environment_sha256'] ?? null) === hash('sha256', $activeRaw)
            && ($receipt['activation_readiness_sha256'] ?? null) === hash('sha256', $readinessRaw)
            && ($receipt['target_preflight_sha256'] ?? null) === hash('sha256', $preflightRaw)
            && ($receipt['activation_authority_sha256'] ?? null) === hash('sha256', $authorityRaw)
            && str_contains($activeRaw, 'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"')
            && ($receipt['post_activation_health_passed'] ?? null) === true
            && $this->healthResultIsHealthy($health)
            && ($receipt['authority_consumed'] ?? null) === true
            && ($receipt['readiness_consumed'] ?? null) === true
            && ($receipt['preflight_consumed'] ?? null) === true
            && ($receipt['activation_executed'] ?? null) === true
            && ($receipt['rollback_performed'] ?? null) === false
            && ($receipt['migration_execution_authorized'] ?? null) === false
            && ($receipt['persistence_authorized'] ?? null) === false
            && ($receipt['production_authorized'] ?? null) === false
            && ($receipt['updater_authorized'] ?? null) === false
            && ($receipt['deployment_authorized'] ?? null) === false
            && ($receipt['attribution'] ?? null) === 'Lab | zefry';
    }

    private function activatedEnvironment(string $raw): string
    {
        $needle = 'ONEQAY_TECHNICAL_PREVIEW_ENABLED="false"';
        if (substr_count($raw, $needle) !== 1
            || str_contains($raw, 'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"')) {
            throw new RuntimeException('technical_preview_activation_environment_invalid');
        }

        return str_replace($needle, 'ONEQAY_TECHNICAL_PREVIEW_ENABLED="true"', $raw);
    }

    private function replaceActiveEnvironment(string $content): void
    {
        $path = $this->activeEnvironmentPath();
        if (! is_file($path) || is_link($path)) {
            throw new RuntimeException('technical_preview_activation_environment_unavailable');
        }

        $directory = dirname($path);
        if (! is_dir($directory) || is_link($directory)) {
            throw new RuntimeException('technical_preview_activation_environment_boundary_invalid');
        }

        $temporary = $directory.DIRECTORY_SEPARATOR.'.env.activation.tmp.'.bin2hex(random_bytes(12));
        $handle = @fopen($temporary, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('technical_preview_activation_environment_temp_unavailable');
        }

        try {
            @chmod($temporary, 0600);
            $length = strlen($content);
            $written = 0;
            while ($written < $length) {
                $result = fwrite($handle, substr($content, $written));
                if ($result === false || $result === 0) {
                    throw new RuntimeException('technical_preview_activation_environment_write_failed');
                }
                $written += $result;
            }

            if (! fflush($handle)) {
                throw new RuntimeException('technical_preview_activation_environment_flush_failed');
            }
            if (function_exists('fsync')) {
                @fsync($handle);
            }
        } catch (Throwable $failure) {
            fclose($handle);
            @unlink($temporary);
            throw $failure;
        }

        fclose($handle);

        if (! @rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('technical_preview_activation_environment_commit_failed');
        }

        @chmod($path, 0600);
    }

    /** @param array<string,mixed> $payload */
    private function writePrivateJsonOnce(string $path, array $payload): void
    {
        $directory = dirname($path);
        if (! is_dir($directory)
            && ! @mkdir($directory, 0700, true)
            && ! is_dir($directory)) {
            throw new RuntimeException('technical_preview_activation_evidence_directory_unavailable');
        }
        if (is_link($directory) || file_exists($path) || is_link($path)) {
            throw new RuntimeException('technical_preview_activation_evidence_boundary_invalid');
        }

        @chmod($directory, 0700);
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
        $temporary = $directory.DIRECTORY_SEPARATOR.'.activation-evidence.tmp.'.bin2hex(random_bytes(12));
        $handle = @fopen($temporary, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('technical_preview_activation_evidence_temp_unavailable');
        }

        try {
            @chmod($temporary, 0600);
            if (fwrite($handle, $json) !== strlen($json) || ! fflush($handle)) {
                throw new RuntimeException('technical_preview_activation_evidence_write_failed');
            }
            if (function_exists('fsync')) {
                @fsync($handle);
            }
        } catch (Throwable $failure) {
            fclose($handle);
            @unlink($temporary);
            throw $failure;
        }

        fclose($handle);
        if (! @rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('technical_preview_activation_evidence_commit_failed');
        }
        @chmod($path, 0600);
    }

    /** @return array<string,string> */
    private function parseEnvironment(string $raw): array
    {
        $values = [];
        foreach (preg_split('/\r\n|\n|\r/', $raw) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            if (preg_match('/\A[A-Z][A-Z0-9_]*\z/', $key) !== 1) {
                continue;
            }

            $value = trim($value);
            if (strlen($value) >= 2 && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
                $value = substr($value, 1, -1);
                $value = strtr($value, ['\\\\' => '\\', '\\"' => '"', '\\$' => '$']);
            }

            $values[$key] = $value;
        }

        return $values;
    }

    private function httpsHost(string $url): ?string
    {
        $parts = parse_url($url);
        if (! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || ! is_string($parts['host'] ?? null)
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
            || (($parts['port'] ?? 443) !== 443)) {
            return null;
        }

        $host = strtolower(rtrim(trim((string) $parts['host']), '.'));

        return $host !== '' && str_contains($host, '.') ? $host : null;
    }

    private function targetPreflight(): PrebootTechnicalPreviewTargetEnvironmentPreflight
    {
        return new PrebootTechnicalPreviewTargetEnvironmentPreflight(
            $this->sharedRoot,
            $this->releaseId,
            $this->nowUnix,
            dirname($this->sharedRoot).DIRECTORY_SEPARATOR.'releases'.DIRECTORY_SEPARATOR.$this->releaseId.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.'web',
            dirname(dirname($this->sharedRoot)).DIRECTORY_SEPARATOR.'public_html',
        );
    }

    private function assertBoundaryShape(): void
    {
        if ($this->sharedRoot === ''
            || str_contains($this->sharedRoot, "\0")
            || (file_exists($this->sharedRoot) && is_link($this->sharedRoot))) {
            throw new RuntimeException('unsafe_activation_execution_boundary');
        }

        foreach ([
            $this->activeEnvironmentPath(),
            $this->authorityPath(),
            $this->readinessPath(),
            $this->preflightPath(),
            $this->receiptPath(),
        ] as $path) {
            if (is_link($path)) {
                throw new RuntimeException('activation_execution_symlink_boundary_rejected');
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
            $decoded = json_decode($json, true, 64, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function isSha256(mixed $value): bool
    {
        return is_string($value) && preg_match('/\A[0-9a-f]{64}\z/', $value) === 1;
    }

    /** @return array{state:string,execution_ready:bool,active_healthy:bool,activation_executed:bool,request_id:string,authority_id:string} */
    private function state(string $state, bool $ready, bool $healthy, bool $executed): array
    {
        return [
            'state' => $state,
            'execution_ready' => $ready,
            'active_healthy' => $healthy,
            'activation_executed' => $executed,
            'request_id' => '',
            'authority_id' => '',
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

    private function authorityPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'technical-preview-activation-authority.json';
    }

    private function readinessPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'technical-preview-activation-readiness.json';
    }

    private function preflightPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'technical-preview-target-environment-preflight.json';
    }

    private function receiptPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'technical-preview-activation-execution.json';
    }

    private function recoveryPath(string $attemptId): string
    {
        return $this->installDirectory()
            .DIRECTORY_SEPARATOR.'technical-preview-activation-attempts'
            .DIRECTORY_SEPARATOR.$attemptId.'.json';
    }
}
