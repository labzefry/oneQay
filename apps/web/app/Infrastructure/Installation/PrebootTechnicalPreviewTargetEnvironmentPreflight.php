<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

use RuntimeException;
use Throwable;

// Author by Lab | zefry
final class PrebootTechnicalPreviewTargetEnvironmentPreflight
{
    public const SCHEMA_VERSION = 1;

    private const MAX_ENVIRONMENT_BYTES = 65536;
    private const MAX_JSON_BYTES = 32768;

    public function __construct(
        private readonly string $sharedRoot,
        private readonly string $releaseId,
        private readonly int $nowUnix,
        private readonly string $appRoot,
        private readonly string $publicRoot,
    ) {
        if (! preg_match('/\Am75-preview-[0-9a-f]{12}\z/i', $releaseId)) {
            throw new RuntimeException('invalid_release_identity');
        }

        if ($nowUnix <= 0) {
            throw new RuntimeException('invalid_target_preflight_clock');
        }
    }

    /**
     * @return array{
     *   state:string,
     *   preflight_ready:bool,
     *   preflight_passed:bool,
     *   activation_executed:false,
     *   request_id:string,
     *   authority_id:string
     * }
     */
    public function inspect(): array
    {
        try {
            $this->assertBoundaryShape();

            $readiness = $this->activationReadinessState();
            if (($readiness['state'] ?? null) === 'TECHNICAL_PREVIEW_ACTIVATION_READINESS_EXPIRED') {
                return $this->state('TECHNICAL_PREVIEW_TARGET_PREFLIGHT_AUTHORITY_EXPIRED', false, false);
            }

            if (($readiness['state'] ?? null) !== 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED'
                || ($readiness['execution_ready'] ?? false) !== true
                || ($readiness['activation_executed'] ?? true) !== false) {
                return $this->state('TECHNICAL_PREVIEW_TARGET_PREFLIGHT_NOT_READY', false, false);
            }

            $raw = $this->readPrivateFile($this->preflightPath(), self::MAX_JSON_BYTES);
            if ($raw === null) {
                return [
                    'state' => 'TECHNICAL_PREVIEW_TARGET_PREFLIGHT_READY_TO_RUN',
                    'preflight_ready' => true,
                    'preflight_passed' => false,
                    'activation_executed' => false,
                    'request_id' => (string) ($readiness['request_id'] ?? ''),
                    'authority_id' => (string) ($readiness['authority_id'] ?? ''),
                ];
            }

            $payload = $this->decodeJson($raw);
            $context = $this->readinessContext();
            if ($payload === null || $context === null || ! $this->preflightEvidenceIsValid($payload, $context)) {
                return $this->state('TECHNICAL_PREVIEW_TARGET_PREFLIGHT_INVALID', false, false);
            }

            if ($this->nowUnix >= (int) $payload['authority_expires_at_unix']) {
                return $this->state('TECHNICAL_PREVIEW_TARGET_PREFLIGHT_AUTHORITY_EXPIRED', false, false);
            }

            return [
                'state' => 'TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT_PASSED_NOT_ACTIVATED',
                'preflight_ready' => true,
                'preflight_passed' => true,
                'activation_executed' => false,
                'request_id' => (string) $payload['request_id'],
                'authority_id' => (string) $payload['authority_id'],
            ];
        } catch (Throwable) {
            return $this->state('TECHNICAL_PREVIEW_TARGET_PREFLIGHT_INVALID', false, false);
        }
    }

    /**
     * Execute a read-only target-environment preflight and persist only private
     * non-secret evidence. No runtime flag, deployment pointer, schema, or
     * application data is mutated.
     *
     * @param array<string,mixed> $server
     * @return array{
     *   state:'TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT_PASSED_NOT_ACTIVATED',
     *   target_fingerprint:string,
     *   preflight_passed:true,
     *   activation_executed:false,
     *   request_id:string,
     *   authority_id:string
     * }
     */
    public function run(array $server): array
    {
        $this->assertBoundaryShape();

        $context = $this->readinessContext();
        if ($context === null) {
            throw new RuntimeException('technical_preview_target_preflight_not_ready');
        }

        if ($this->nowUnix >= $context['authority_expires_at_unix']) {
            throw new RuntimeException('technical_preview_target_preflight_authority_expired');
        }

        $environmentRaw = $this->readPrivateFile($this->activeEnvironmentPath(), self::MAX_ENVIRONMENT_BYTES);
        if ($environmentRaw === null) {
            throw new RuntimeException('technical_preview_target_preflight_failed');
        }

        $environment = $this->parseEnvironment($environmentRaw);
        $releaseRaw = $this->readReleaseFile();
        $release = $releaseRaw === null ? null : $this->decodeJson($releaseRaw);
        if ($releaseRaw === null || $release === null) {
            throw new RuntimeException('technical_preview_target_preflight_failed');
        }

        $appUrl = $this->parseHttpsApplicationUrl((string) ($environment['APP_URL'] ?? ''));
        $requestHost = $this->requestHost($server);
        $https = $this->httpsActive($server);

        $sessionDirectory = (string) ($environment['SESSION_FILES'] ?? '');
        $expectedSessionDirectory = $this->sessionDirectory();
        $sessionReal = $sessionDirectory !== '' ? realpath($sessionDirectory) : false;
        $publicReal = realpath($this->publicRoot);

        $checks = [
            'https_tls_active' => $https,
            'dedicated_host_binding' => $appUrl !== null
                && $requestHost !== null
                && hash_equals($appUrl['host'], $requestHost)
                && $appUrl['root_path'],
            'single_instance_target' => ($environment['ONEQAY_PREVIEW_INSTANCE_MODE'] ?? null) === 'single',
            'private_persistent_session_directory' => $sessionDirectory === $expectedSessionDirectory
                && is_string($sessionReal)
                && is_string($publicReal)
                && is_dir($sessionDirectory)
                && ! is_link($sessionDirectory)
                && is_writable($sessionDirectory)
                && $this->permissionsArePrivate($sessionDirectory)
                && ! $this->pathIsInside($sessionReal, $publicReal),
            'runtime_envelope_exact' => $this->runtimeEnvelopeIsExact($environment),
            'preview_off_switch_verified' => ($environment['ONEQAY_TECHNICAL_PREVIEW_ENABLED'] ?? null) === 'false',
            'release_synthetic_no_schema_change' => $this->releaseContractIsExact($release),
            'stale_config_cache_absent' => ! file_exists($this->appRoot.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'config.php')
                && ! is_link($this->appRoot.DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'config.php'),
            'preview_route_off_switch_contract_available' => $this->previewSourceContractAvailable(),
            'post_activation_health_contract_available' => $this->postActivationHealthContractAvailable(),
            'rollback_recovery_contract_available' => $this->rollbackRecoveryContractAvailable(),
            'production_data_forbidden' => ($environment['ONEQAY_PREVIEW_DATA_CLASS'] ?? null) === 'synthetic'
                && ($environment['ONEQAY_PRODUCTION_DATA_ALLOWED'] ?? null) === 'false'
                && ($environment['ONEQAY_PERSISTENCE_ENABLED'] ?? null) === 'false',
        ];

        foreach ($checks as $passed) {
            if ($passed !== true) {
                throw new RuntimeException('technical_preview_target_preflight_failed');
            }
        }

        $hostHash = hash('sha256', $appUrl['host']);
        $sessionPathHash = hash('sha256', $sessionReal);
        $releaseSha256 = hash('sha256', $releaseRaw);
        $activeSha256 = hash('sha256', $environmentRaw);

        if (! hash_equals($context['active_environment_sha256'], $activeSha256)) {
            throw new RuntimeException('technical_preview_target_preflight_failed');
        }

        $targetFingerprint = hash('sha256', implode('|', [
            $this->releaseId,
            $context['request_id'],
            $context['authority_id'],
            $context['readiness_sha256'],
            $activeSha256,
            $releaseSha256,
            $hostHash,
            $sessionPathHash,
            json_encode($checks, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
        ]));

        $payload = [
            'schema_version' => self::SCHEMA_VERSION,
            'product' => 'oneQay',
            'preflight_state' => 'TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT_PASSED_NOT_ACTIVATED',
            'release_id' => $this->releaseId,
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
            'target_fingerprint' => $targetFingerprint,
            'activation_readiness_sha256' => $context['readiness_sha256'],
            'active_environment_sha256' => $activeSha256,
            'release_manifest_sha256' => $releaseSha256,
            'host_identity_sha256' => $hostHash,
            'session_directory_identity_sha256' => $sessionPathHash,
            'checked_at_unix' => $this->nowUnix,
            'authority_expires_at_unix' => $context['authority_expires_at_unix'],
            'checks' => $checks,
            'post_activation_health_check_required' => true,
            'post_activation_health_check_executed' => false,
            'authority_consumed' => false,
            'readiness_consumed' => false,
            'activation_executed' => false,
            'migration_execution_authorized' => false,
            'persistence_authorized' => false,
            'production_authorized' => false,
            'updater_authorized' => false,
            'deployment_authorized' => false,
            'attribution' => 'Lab | zefry',
        ];

        $this->writeEvidence($payload);

        $writtenRaw = $this->readPrivateFile($this->preflightPath(), self::MAX_JSON_BYTES);
        $written = $writtenRaw === null ? null : $this->decodeJson($writtenRaw);
        $freshContext = $this->readinessContext();
        if ($writtenRaw === null
            || $written === null
            || $freshContext === null
            || ! $this->preflightEvidenceIsValid($written, $freshContext)
            || ! hash_equals((string) $written['target_fingerprint'], $targetFingerprint)) {
            throw new RuntimeException('technical_preview_target_preflight_commit_failed');
        }

        return [
            'state' => 'TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT_PASSED_NOT_ACTIVATED',
            'target_fingerprint' => $targetFingerprint,
            'preflight_passed' => true,
            'activation_executed' => false,
            'request_id' => $context['request_id'],
            'authority_id' => $context['authority_id'],
        ];
    }

    /**
     * @return array{
     *   request_id:string,
     *   authority_id:string,
     *   readiness_sha256:string,
     *   active_environment_sha256:string,
     *   authority_expires_at_unix:int
     * }|null
     */
    private function readinessContext(): ?array
    {
        $state = $this->activationReadinessState();
        if (($state['state'] ?? null) !== 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED'
            || ($state['execution_ready'] ?? false) !== true
            || ($state['activation_executed'] ?? true) !== false) {
            return null;
        }

        $readinessRaw = $this->readPrivateFile($this->activationReadinessPath(), self::MAX_JSON_BYTES);
        if ($readinessRaw === null) {
            return null;
        }

        $readiness = $this->decodeJson($readinessRaw);
        if ($readiness === null
            || ($readiness['readiness_state'] ?? null) !== 'TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED'
            || ($readiness['release_id'] ?? null) !== $this->releaseId
            || ! is_string($readiness['request_id'] ?? null)
            || ! is_string($readiness['authority_id'] ?? null)
            || ! is_string($readiness['active_environment_sha256'] ?? null)
            || ! is_int($readiness['authority_expires_at_unix'] ?? null)
            || ($readiness['activation_executed'] ?? null) !== false) {
            return null;
        }

        return [
            'request_id' => (string) $readiness['request_id'],
            'authority_id' => (string) $readiness['authority_id'],
            'readiness_sha256' => hash('sha256', $readinessRaw),
            'active_environment_sha256' => (string) $readiness['active_environment_sha256'],
            'authority_expires_at_unix' => (int) $readiness['authority_expires_at_unix'],
        ];
    }

    /** @return array<string,mixed> */
    private function activationReadinessState(): array
    {
        return (new PrebootTechnicalPreviewActivationAuthorityReadiness(
            $this->sharedRoot,
            $this->releaseId,
            $this->nowUnix,
        ))->inspect();
    }

    /** @param array<string,mixed> $payload @param array<string,mixed> $context */
    private function preflightEvidenceIsValid(array $payload, array $context): bool
    {
        $checks = is_array($payload['checks'] ?? null) ? $payload['checks'] : [];
        $requiredChecks = [
            'https_tls_active',
            'dedicated_host_binding',
            'single_instance_target',
            'private_persistent_session_directory',
            'runtime_envelope_exact',
            'preview_off_switch_verified',
            'release_synthetic_no_schema_change',
            'stale_config_cache_absent',
            'preview_route_off_switch_contract_available',
            'post_activation_health_contract_available',
            'rollback_recovery_contract_available',
            'production_data_forbidden',
        ];

        foreach ($requiredChecks as $check) {
            if (($checks[$check] ?? null) !== true) {
                return false;
            }
        }

        return ($payload['schema_version'] ?? null) === self::SCHEMA_VERSION
            && ($payload['product'] ?? null) === 'oneQay'
            && ($payload['preflight_state'] ?? null) === 'TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT_PASSED_NOT_ACTIVATED'
            && ($payload['release_id'] ?? null) === $this->releaseId
            && ($payload['request_id'] ?? null) === $context['request_id']
            && ($payload['authority_id'] ?? null) === $context['authority_id']
            && ($payload['activation_readiness_sha256'] ?? null) === $context['readiness_sha256']
            && ($payload['active_environment_sha256'] ?? null) === $context['active_environment_sha256']
            && $this->isSha256($payload['target_fingerprint'] ?? null)
            && $this->isSha256($payload['release_manifest_sha256'] ?? null)
            && $this->isSha256($payload['host_identity_sha256'] ?? null)
            && $this->isSha256($payload['session_directory_identity_sha256'] ?? null)
            && is_int($payload['checked_at_unix'] ?? null)
            && (int) $payload['checked_at_unix'] > 0
            && (int) $payload['checked_at_unix'] <= $this->nowUnix
            && ($payload['authority_expires_at_unix'] ?? null) === $context['authority_expires_at_unix']
            && ($payload['post_activation_health_check_required'] ?? null) === true
            && ($payload['post_activation_health_check_executed'] ?? null) === false
            && ($payload['authority_consumed'] ?? null) === false
            && ($payload['readiness_consumed'] ?? null) === false
            && ($payload['activation_executed'] ?? null) === false
            && ($payload['migration_execution_authorized'] ?? null) === false
            && ($payload['persistence_authorized'] ?? null) === false
            && ($payload['production_authorized'] ?? null) === false
            && ($payload['updater_authorized'] ?? null) === false
            && ($payload['deployment_authorized'] ?? null) === false
            && ($payload['attribution'] ?? null) === 'Lab | zefry';
    }

    /** @param array<string,string> $environment */
    private function runtimeEnvelopeIsExact(array $environment): bool
    {
        $lifetime = filter_var(
            $environment['SESSION_LIFETIME'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 60]],
        );

        return ($environment['APP_ENV'] ?? null) === 'production'
            && ($environment['ONEQAY_RUNTIME_CLASS'] ?? null) === 'preview'
            && ($environment['ONEQAY_INSTALLATION_RELEASE_ID'] ?? null) === $this->releaseId
            && ($environment['ONEQAY_INSTALLATION_DATABASE_VERIFIED'] ?? null) === 'true'
            && ($environment['ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED'] ?? null) === 'false'
            && ($environment['ONEQAY_TECHNICAL_PREVIEW_ENABLED'] ?? null) === 'false'
            && ($environment['ONEQAY_PREVIEW_INSTANCE_MODE'] ?? null) === 'single'
            && ($environment['ONEQAY_PREVIEW_DATA_CLASS'] ?? null) === 'synthetic'
            && ($environment['ONEQAY_PRODUCTION_DATA_ALLOWED'] ?? null) === 'false'
            && ($environment['ONEQAY_PERSISTENCE_ENABLED'] ?? null) === 'false'
            && ($environment['ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED'] ?? null) === 'false'
            && ($environment['SESSION_DRIVER'] ?? null) === 'file'
            && ($environment['SESSION_FILES'] ?? null) === $this->sessionDirectory()
            && $lifetime !== false
            && ($environment['SESSION_ENCRYPT'] ?? null) === 'true'
            && ($environment['SESSION_SECURE_COOKIE'] ?? null) === 'true'
            && ($environment['SESSION_COOKIE'] ?? null) === 'oneqay-preview-session';
    }

    /** @param array<string,mixed> $release */
    private function releaseContractIsExact(array $release): bool
    {
        $activation = is_array($release['preboot_installation'] ?? null)
            ? $release['preboot_installation']
            : [];

        return ($release['product'] ?? null) === 'oneQay'
            && ($release['environment'] ?? null) === 'TECHNICAL_PREVIEW'
            && ($release['production'] ?? null) === false
            && ($release['synthetic_data_only'] ?? null) === true
            && ($release['release_id'] ?? null) === $this->releaseId
            && ($release['migration_classification'] ?? null) === 'NO_SCHEMA_CHANGE'
            && ($release['updater_activation'] ?? null) === 'DISABLED'
            && ($activation['technical_preview_authorized'] ?? null) === false
            && ($activation['activation_authorized'] ?? null) === false;
    }

    private function previewSourceContractAvailable(): bool
    {
        $provider = $this->appRoot.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'TechnicalPreviewServiceProvider.php';
        $policy = $this->appRoot.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'Preview'.DIRECTORY_SEPARATOR.'TechnicalPreviewRuntimePolicy.php';
        $config = $this->appRoot.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'technical-preview.php';

        foreach ([$provider, $policy, $config] as $path) {
            if (! is_file($path) || is_link($path) || ! is_readable($path)) {
                return false;
            }
        }

        $providerSource = (string) file_get_contents($provider);
        $policySource = (string) file_get_contents($policy);
        $configSource = (string) file_get_contents($config);

        return str_contains($providerSource, 'removeDeniedPreviewRoutesAfterApplicationBoot')
            && str_contains($policySource, 'DEPLOYED_SESSION_DRIVER')
            && str_contains($configSource, "'domain' => null")
            && str_contains($configSource, "'path' => '/'")
            && str_contains($configSource, "'same_site' => 'lax'")
            && str_contains($configSource, "'http_only' => true");
    }

    private function postActivationHealthContractAvailable(): bool
    {
        $path = $this->appRoot.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'SystemUpdate'.DIRECTORY_SEPARATOR.'SystemUpdateHealthVerifier.php';

        return is_file($path) && ! is_link($path) && is_readable($path);
    }

    private function rollbackRecoveryContractAvailable(): bool
    {
        $paths = [
            $this->appRoot.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Application'.DIRECTORY_SEPARATOR.'SystemUpdate'.DIRECTORY_SEPARATOR.'Rehearsal'.DIRECTORY_SEPARATOR.'SystemUpdatePreviewRehearsalDriver.php',
            $this->appRoot.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Infrastructure'.DIRECTORY_SEPARATOR.'SystemUpdate'.DIRECTORY_SEPARATOR.'Activation'.DIRECTORY_SEPARATOR.'FilesystemSystemUpdateDeploymentLockManager.php',
        ];

        foreach ($paths as $path) {
            if (! is_file($path) || is_link($path) || ! is_readable($path)) {
                return false;
            }
        }

        return true;
    }

    /** @return array{host:string,root_path:bool}|null */
    private function parseHttpsApplicationUrl(string $url): ?array
    {
        $parts = parse_url($url);
        if (! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || ! is_string($parts['host'] ?? null)
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])) {
            return null;
        }

        $host = $this->normalizeDnsHost((string) $parts['host']);
        if ($host === null) {
            return null;
        }

        $port = $parts['port'] ?? 443;
        if ($port !== 443) {
            return null;
        }

        $path = (string) ($parts['path'] ?? '');

        return [
            'host' => $host,
            'root_path' => $path === '' || $path === '/',
        ];
    }

    /** @param array<string,mixed> $server */
    private function requestHost(array $server): ?string
    {
        $raw = trim((string) ($server['HTTP_HOST'] ?? ''));
        if ($raw === '' || str_contains($raw, ',') || str_contains($raw, '/')) {
            return null;
        }

        $parts = parse_url('https://'.$raw);
        if (! is_array($parts) || ! is_string($parts['host'] ?? null)) {
            return null;
        }

        if (isset($parts['port']) && (int) $parts['port'] !== 443) {
            return null;
        }

        return $this->normalizeDnsHost((string) $parts['host']);
    }

    /** @param array<string,mixed> $server */
    private function httpsActive(array $server): bool
    {
        $https = strtolower(trim((string) ($server['HTTPS'] ?? '')));
        if ($https !== '' && ! in_array($https, ['off', '0', 'false'], true)) {
            return true;
        }

        return (int) ($server['SERVER_PORT'] ?? 0) === 443;
    }

    private function normalizeDnsHost(string $host): ?string
    {
        $host = strtolower(rtrim(trim($host), '.'));
        if ($host === ''
            || $host === 'localhost'
            || filter_var($host, FILTER_VALIDATE_IP) !== false
            || strlen($host) > 253
            || ! str_contains($host, '.')
            || preg_match('/\A(?=.{1,253}\z)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\z/', $host) !== 1) {
            return null;
        }

        return $host;
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
                $value = strtr($value, [
                    '\\\\' => '\\',
                    '\\"' => '"',
                    '\\$' => '$',
                ]);
            }

            $values[$key] = $value;
        }

        return $values;
    }

    private function assertBoundaryShape(): void
    {
        foreach ([$this->sharedRoot, $this->appRoot, $this->publicRoot] as $path) {
            if ($path === '' || str_contains($path, "\0") || (file_exists($path) && is_link($path))) {
                throw new RuntimeException('unsafe_target_preflight_boundary');
            }
        }

        foreach ([
            $this->activeEnvironmentPath(),
            $this->activationReadinessPath(),
            $this->preflightPath(),
            $this->releasePath(),
            $this->sessionDirectory(),
        ] as $path) {
            if (is_link($path)) {
                throw new RuntimeException('target_preflight_symlink_boundary_rejected');
            }
        }
    }

    private function readReleaseFile(): ?string
    {
        $path = $this->releasePath();
        if (! is_file($path) || is_link($path) || ! is_readable($path)) {
            return null;
        }

        $size = filesize($path);
        if (! is_int($size) || $size <= 0 || $size > self::MAX_JSON_BYTES) {
            return null;
        }

        $content = file_get_contents($path);

        return is_string($content) ? $content : null;
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

    /** @param array<string,mixed> $payload */
    private function writeEvidence(array $payload): void
    {
        $directory = $this->installDirectory();
        if (! is_dir($directory) || is_link($directory)) {
            throw new RuntimeException('technical_preview_target_preflight_boundary_unavailable');
        }

        $path = $this->preflightPath();
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

        if (file_exists($path) || is_link($path)) {
            $existingRaw = $this->readPrivateFile($path, self::MAX_JSON_BYTES);
            $existing = $existingRaw === null ? null : $this->decodeJson($existingRaw);
            $context = $this->readinessContext();

            if ($existing !== null
                && $context !== null
                && $this->preflightEvidenceIsValid($existing, $context)
                && hash_equals(
                    (string) ($existing['target_fingerprint'] ?? ''),
                    (string) ($payload['target_fingerprint'] ?? ''),
                )) {
                return;
            }

            throw new RuntimeException('technical_preview_target_preflight_already_present');
        }

        $temporary = $directory.DIRECTORY_SEPARATOR.'technical-preview-target-environment-preflight.tmp.'.bin2hex(random_bytes(12));
        $handle = @fopen($temporary, 'x+b');
        if (! is_resource($handle)) {
            throw new RuntimeException('technical_preview_target_preflight_temp_unavailable');
        }

        try {
            @chmod($temporary, 0600);
            $length = strlen($json);
            $written = 0;
            while ($written < $length) {
                $result = fwrite($handle, substr($json, $written));
                if ($result === false || $result === 0) {
                    throw new RuntimeException('technical_preview_target_preflight_write_failed');
                }
                $written += $result;
            }

            if (! fflush($handle)) {
                throw new RuntimeException('technical_preview_target_preflight_flush_failed');
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
            throw new RuntimeException('technical_preview_target_preflight_commit_failed');
        }

        @chmod($path, 0600);
    }

    private function permissionsArePrivate(string $path): bool
    {
        $permissions = fileperms($path);

        return is_int($permissions) && ($permissions & 0077) === 0;
    }

    private function pathIsInside(string $path, string $parent): bool
    {
        $path = rtrim(str_replace('\\', '/', $path), '/');
        $parent = rtrim(str_replace('\\', '/', $parent), '/');

        return $path === $parent || str_starts_with($path.'/', $parent.'/');
    }

    private function isSha256(mixed $value): bool
    {
        return is_string($value) && preg_match('/\A[0-9a-f]{64}\z/', $value) === 1;
    }

    /** @return array{state:string,preflight_ready:bool,preflight_passed:bool,activation_executed:false,request_id:string,authority_id:string} */
    private function state(string $state, bool $ready, bool $passed): array
    {
        return [
            'state' => $state,
            'preflight_ready' => $ready,
            'preflight_passed' => $passed,
            'activation_executed' => false,
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

    private function sessionDirectory(): string
    {
        return $this->runtimeDirectory().DIRECTORY_SEPARATOR.'sessions';
    }

    private function activeEnvironmentPath(): string
    {
        return $this->runtimeDirectory().DIRECTORY_SEPARATOR.'.env';
    }

    private function activationReadinessPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'technical-preview-activation-readiness.json';
    }

    private function preflightPath(): string
    {
        return $this->installDirectory().DIRECTORY_SEPARATOR.'technical-preview-target-environment-preflight.json';
    }

    private function releasePath(): string
    {
        return dirname($this->appRoot, 2).DIRECTORY_SEPARATOR.'RELEASE.json';
    }
}
