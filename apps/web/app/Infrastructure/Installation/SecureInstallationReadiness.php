<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

/**
 * Deterministic, read-only installation readiness assessment.
 *
 * Author by Lab | zefry
 */
final class SecureInstallationReadiness
{
    /** @var list<string> */
    private const REQUIRED_ENVIRONMENT_KEYS = [
        'APP_ENV',
        'APP_KEY',
        'APP_URL',
        'ONEQAY_DB_DRIVER',
        'ONEQAY_DB_HOST',
        'ONEQAY_DB_DATABASE',
        'ONEQAY_DB_USERNAME',
    ];

    /** @var list<string> */
    private const REQUIRED_DATABASE_STATE_KEYS = [
        'connected',
        'engine',
        'server_version',
        'charset',
        'timezone',
        'schema_state',
        'least_privilege',
    ];

    /** @var list<string> */
    private const REQUIRED_WRITABLE_PATHS = [
        'bootstrap/cache',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
    ];

    /** @var list<string> */
    private const REQUIRED_RELEASE_MANIFEST_KEYS = [
        'schema_version',
        'product',
        'repository',
        'release_id',
        'release_channel',
        'source_commit',
        'artifact_filename',
        'artifact_type',
        'artifact_size',
        'artifact_sha256',
        'runtime_requirements',
        'host_requirements',
        'compatibility_policy',
        'migration_classification',
        'attribution',
    ];

    /** @var list<string> */
    private const REQUIRED_HOST_REQUIREMENT_KEYS = [
        'os_families',
        'web_server_interfaces',
        'memory_bytes_min',
        'execution_time_seconds_min',
        'disk_bytes_min',
        'required_capabilities',
    ];

    /** @var list<string> */
    private const REQUIRED_HOST_STATE_KEYS = [
        'os_family',
        'web_server_interface',
        'memory_bytes',
        'execution_time_seconds',
        'disk_free_bytes',
        'capabilities',
    ];

    /** @var list<string> */
    private const REQUIRED_HOST_CAPABILITIES = [
        'https',
        'dns',
        'time_sync',
        'outbound_allowlist',
        'scheduler',
        'archive',
        'temp_directory',
        'required_tools',
    ];

    /** @var list<string> */
    private const REQUIRED_COMPATIBILITY_POLICY_KEYS = [
        'release_version',
        'build_provenance_ref',
        'supported_current_version_range',
        'deployment_compatibility',
        'rollback_compatibility',
        'public_bootstrap_layout_compatibility',
        'release_notes_reference',
    ];

    /** @var list<string> */
    private const REQUIRED_ARTIFACT_STATE_KEYS = ['filename', 'size', 'sha256'];

    /**
     * @param array<string, mixed> $environment
     * @param list<string>|null $loadedExtensions
     * @param array<string, bool>|null $writablePaths Deterministic override keyed by canonical relative path.
     * @param array<string, mixed>|null $releaseManifest Installer-facing governed release manifest projection.
     * @param array<string, mixed>|null $artifactState Deterministic observed artifact identity and digest facts.
     * @param array<string, mixed>|null $databaseState Deterministic observed database connectivity and compatibility facts.
     * @param array<string, mixed>|null $hostPlatformState Deterministic observed host/platform facts; no probing is performed here.
     * @return array{ready: bool, checks: array<string, array{ready: bool, reason: string}>}
     */
    public function assess(
        array $environment,
        ?array $loadedExtensions = null,
        ?string $phpVersion = null,
        ?array $writablePaths = null,
        ?array $releaseManifest = null,
        ?array $artifactState = null,
        ?array $databaseState = null,
        ?array $hostPlatformState = null
    ): array {
        $manifest = $releaseManifest ?? [];
        $runtimeRequirements = is_array($manifest['runtime_requirements'] ?? null)
            ? $manifest['runtime_requirements']
            : [];
        $runtimeRequirementsReady = $this->isValidRuntimeRequirements($runtimeRequirements);
        $hostRequirements = is_array($manifest['host_requirements'] ?? null)
            ? $manifest['host_requirements']
            : [];
        $hostRequirementsReady = $this->isValidHostRequirements($hostRequirements);
        $extensions = array_values(array_unique(array_map(
            static fn (string $extension): string => strtolower($extension),
            $loadedExtensions ?? get_loaded_extensions()
        )));
        $version = $phpVersion ?? PHP_VERSION;
        $checks = [];

        $minimumPhpVersion = $runtimeRequirementsReady
            ? (string) $runtimeRequirements['php_min']
            : null;
        $checks['php_version'] = $this->check(
            $minimumPhpVersion !== null && version_compare($version, $minimumPhpVersion, '>='),
            'PHP runtime satisfies the governed release requirement.',
            $minimumPhpVersion === null
                ? 'Governed PHP runtime requirements are unavailable or invalid.'
                : 'PHP runtime is below the governed release minimum.'
        );

        $requiredExtensions = $runtimeRequirementsReady
            ? array_map(
                static fn (string $extension): string => strtolower($extension),
                $runtimeRequirements['php_extensions']
            )
            : [];
        $missingExtensions = array_values(array_filter(
            $requiredExtensions,
            static fn (string $extension): bool => ! in_array($extension, $extensions, true)
        ));
        $checks['php_extensions'] = $this->check(
            $runtimeRequirementsReady && $missingExtensions === [],
            'Loaded PHP extensions satisfy the governed release requirements.',
            $runtimeRequirementsReady
                ? 'Loaded PHP extensions do not satisfy the governed release requirements.'
                : 'Governed PHP extension requirements are unavailable or invalid.'
        );

        $observedHost = $hostPlatformState ?? [];
        $missingHostStateKeys = $this->missingKeys($observedHost, self::REQUIRED_HOST_STATE_KEYS);
        $hostReady = $hostRequirementsReady
            && $missingHostStateKeys === []
            && $this->hostMatchesRequirements($hostRequirements, $observedHost);
        $checks['host_platform'] = $this->check(
            $hostReady,
            'Observed host/platform capabilities satisfy the governed release requirements.',
            ! $hostRequirementsReady
                ? 'Governed host/platform requirements are unavailable or invalid.'
                : ($missingHostStateKeys === []
                    ? 'Observed host/platform capabilities do not satisfy the governed release requirements.'
                    : 'Observed host/platform facts are incomplete: '.implode(', ', $missingHostStateKeys).'.')
        );

        $missingEnvironment = array_values(array_filter(
            self::REQUIRED_ENVIRONMENT_KEYS,
            static fn (string $key): bool => ! isset($environment[$key]) || trim((string) $environment[$key]) === ''
        ));
        $checks['environment'] = $this->check(
            $missingEnvironment === [],
            'Required installation configuration is present.',
            'Required installation configuration is incomplete: '.implode(', ', $missingEnvironment).'.'
        );

        $appKey = trim((string) ($environment['APP_KEY'] ?? ''));
        $checks['application_key'] = $this->check(
            $appKey !== '' && ! in_array(strtolower($appKey), ['changeme', 'base64:changeme'], true),
            'Application key is configured.',
            'Application key is absent or uses a prohibited placeholder.'
        );

        $appEnvironment = strtolower(trim((string) ($environment['APP_ENV'] ?? '')));
        $appDebug = filter_var($environment['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
        $checks['production_debug'] = $this->check(
            $appEnvironment !== 'production' || $appDebug === false,
            'Debug posture is acceptable for the selected environment.',
            'Production installation cannot proceed while debug mode is enabled.'
        );

        $url = trim((string) ($environment['APP_URL'] ?? ''));
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $checks['production_https'] = $this->check(
            $appEnvironment !== 'production' || $scheme === 'https',
            'Application URL transport posture is acceptable.',
            'Production installation requires an HTTPS application URL.'
        );

        $observedDatabase = $databaseState ?? [];
        $missingDatabaseKeys = $this->missingKeys($observedDatabase, self::REQUIRED_DATABASE_STATE_KEYS);
        $databaseReady = $missingDatabaseKeys === []
            && $this->isValidDatabaseState($environment, $observedDatabase);
        $checks['database_compatibility'] = $this->check(
            $databaseReady,
            'Observed database connectivity and compatibility satisfy the installation contract.',
            $missingDatabaseKeys === []
                ? 'Observed database connectivity or compatibility does not satisfy the installation contract.'
                : 'Observed database compatibility facts are incomplete: '.implode(', ', $missingDatabaseKeys).'.'
        );

        $pathStates = $writablePaths ?? $this->inspectWritablePaths();
        $unwritablePaths = array_values(array_filter(
            self::REQUIRED_WRITABLE_PATHS,
            static fn (string $path): bool => ($pathStates[$path] ?? false) !== true
        ));
        $checks['filesystem_write'] = $this->check(
            $unwritablePaths === [],
            'Only required runtime paths are writable.',
            'Required runtime paths are missing or not writable: '.implode(', ', $unwritablePaths).'.'
        );

        $missingManifestKeys = $this->missingKeys($manifest, self::REQUIRED_RELEASE_MANIFEST_KEYS);
        $manifestReady = $missingManifestKeys === [] && $this->isValidReleaseManifest($manifest);
        $checks['release_manifest'] = $this->check(
            $manifestReady,
            'Governed release manifest identity, runtime requirements, host requirements, compatibility policy, and release policy are valid.',
            $missingManifestKeys === []
                ? 'Governed release manifest identity, runtime requirements, host requirements, compatibility policy, or release policy are invalid.'
                : 'Governed release manifest is incomplete: '.implode(', ', $missingManifestKeys).'.'
        );

        $observedArtifact = $artifactState ?? [];
        $missingArtifactKeys = $this->missingKeys($observedArtifact, self::REQUIRED_ARTIFACT_STATE_KEYS);
        $artifactReady = $manifestReady
            && $missingArtifactKeys === []
            && $this->artifactMatchesManifest($manifest, $observedArtifact);
        $checks['artifact_integrity'] = $this->check(
            $artifactReady,
            'Observed release artifact identity and SHA-256 match the governed manifest.',
            $missingArtifactKeys === []
                ? 'Observed release artifact identity or SHA-256 does not match the governed manifest.'
                : 'Observed release artifact facts are incomplete: '.implode(', ', $missingArtifactKeys).'.'
        );

        return [
            'ready' => ! in_array(false, array_column($checks, 'ready'), true),
            'checks' => $checks,
        ];
    }

    /** @return array<string, bool> */
    private function inspectWritablePaths(): array
    {
        $applicationRoot = dirname(__DIR__, 3);
        $states = [];

        foreach (self::REQUIRED_WRITABLE_PATHS as $relativePath) {
            $absolutePath = $applicationRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $states[$relativePath] = is_dir($absolutePath) && is_writable($absolutePath);
        }

        return $states;
    }

    /**
     * @param array<string, mixed> $values
     * @param list<string> $requiredKeys
     * @return list<string>
     */
    private function missingKeys(array $values, array $requiredKeys): array
    {
        return array_values(array_filter(
            $requiredKeys,
            static function (string $key) use ($values): bool {
                if (! array_key_exists($key, $values) || $values[$key] === null) {
                    return true;
                }

                return is_string($values[$key]) && trim($values[$key]) === '';
            }
        ));
    }

    /** @param array<string, mixed> $requirements */
    private function isValidRuntimeRequirements(array $requirements): bool
    {
        if (! isset($requirements['php_min'], $requirements['php_extensions'])
            || ! is_string($requirements['php_min'])
            || ! is_array($requirements['php_extensions'])
            || preg_match('/\A\d+\.\d+(?:\.\d+)?\z/', trim($requirements['php_min'])) !== 1
            || $requirements['php_extensions'] === []
            || count($requirements['php_extensions']) > 64) {
            return false;
        }

        $normalized = [];
        foreach ($requirements['php_extensions'] as $extension) {
            if (! is_string($extension)) {
                return false;
            }

            $name = strtolower(trim($extension));
            if (preg_match('/\A[a-z][a-z0-9_]{0,63}\z/', $name) !== 1 || isset($normalized[$name])) {
                return false;
            }

            $normalized[$name] = true;
        }

        return true;
    }

    /** @param array<string, mixed> $requirements */
    private function isValidHostRequirements(array $requirements): bool
    {
        if ($this->missingKeys($requirements, self::REQUIRED_HOST_REQUIREMENT_KEYS) !== []
            || ! is_array($requirements['os_families'])
            || ! is_array($requirements['web_server_interfaces'])
            || ! is_int($requirements['memory_bytes_min'])
            || ! is_int($requirements['execution_time_seconds_min'])
            || ! is_int($requirements['disk_bytes_min'])
            || ! is_array($requirements['required_capabilities'])
            || $requirements['memory_bytes_min'] <= 0
            || $requirements['execution_time_seconds_min'] <= 0
            || $requirements['execution_time_seconds_min'] > 3600
            || $requirements['disk_bytes_min'] <= 0
            || ! $this->isValidTokenList($requirements['os_families'], 16)
            || ! $this->isValidTokenList($requirements['web_server_interfaces'], 16)
            || ! $this->isValidTokenList($requirements['required_capabilities'], 16)) {
            return false;
        }

        $capabilities = array_map(
            static fn (string $capability): string => strtolower(trim($capability)),
            $requirements['required_capabilities']
        );
        sort($capabilities);
        $requiredCapabilities = self::REQUIRED_HOST_CAPABILITIES;
        sort($requiredCapabilities);

        return $capabilities === $requiredCapabilities;
    }

    /**
     * @param array<string, mixed> $requirements
     * @param array<string, mixed> $observed
     */
    private function hostMatchesRequirements(array $requirements, array $observed): bool
    {
        if (! is_string($observed['os_family'])
            || ! is_string($observed['web_server_interface'])
            || ! is_int($observed['memory_bytes'])
            || ! is_int($observed['execution_time_seconds'])
            || ! is_int($observed['disk_free_bytes'])
            || ! is_array($observed['capabilities'])
            || $observed['memory_bytes'] < 0
            || $observed['execution_time_seconds'] < 0
            || $observed['disk_free_bytes'] < 0) {
            return false;
        }

        $osFamily = strtolower(trim($observed['os_family']));
        $webServerInterface = strtolower(trim($observed['web_server_interface']));
        $allowedOsFamilies = array_map(
            static fn (string $value): string => strtolower(trim($value)),
            $requirements['os_families']
        );
        $allowedInterfaces = array_map(
            static fn (string $value): string => strtolower(trim($value)),
            $requirements['web_server_interfaces']
        );

        if (! in_array($osFamily, $allowedOsFamilies, true)
            || ! in_array($webServerInterface, $allowedInterfaces, true)
            || $observed['memory_bytes'] < $requirements['memory_bytes_min']
            || ($observed['execution_time_seconds'] !== 0
                && $observed['execution_time_seconds'] < $requirements['execution_time_seconds_min'])
            || $observed['disk_free_bytes'] < $requirements['disk_bytes_min']) {
            return false;
        }

        foreach ($requirements['required_capabilities'] as $capability) {
            $name = strtolower(trim($capability));
            if (($observed['capabilities'][$name] ?? false) !== true) {
                return false;
            }
        }

        return true;
    }

    /** @param list<mixed> $values */
    private function isValidTokenList(array $values, int $maximum): bool
    {
        if ($values === [] || count($values) > $maximum) {
            return false;
        }

        $normalized = [];
        foreach ($values as $value) {
            if (! is_string($value)) {
                return false;
            }

            $token = strtolower(trim($value));
            if (preg_match('/\A[a-z][a-z0-9._-]{0,63}\z/', $token) !== 1 || isset($normalized[$token])) {
                return false;
            }

            $normalized[$token] = true;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $environment
     * @param array<string, mixed> $database
     */
    private function isValidDatabaseState(array $environment, array $database): bool
    {
        $configuredDriver = strtolower(trim((string) ($environment['ONEQAY_DB_DRIVER'] ?? '')));
        $engine = strtolower(trim((string) $database['engine']));
        $serverVersion = trim((string) $database['server_version']);
        $charset = strtolower(trim((string) $database['charset']));
        $timezone = strtoupper(trim((string) $database['timezone']));
        $schemaState = strtolower(trim((string) $database['schema_state']));

        return $database['connected'] === true
            && $configuredDriver === 'mysql'
            && in_array($engine, ['mysql', 'mariadb'], true)
            && preg_match('/\A\d+\.\d+(?:\.\d+)?(?:[-+._A-Za-z0-9]*)?\z/', $serverVersion) === 1
            && $charset === 'utf8mb4'
            && in_array($timezone, ['UTC', '+00:00'], true)
            && in_array($schemaState, ['empty', 'recognized'], true)
            && $database['least_privilege'] === true;
    }

    /** @param array<string, mixed> $manifest */
    private function isValidReleaseManifest(array $manifest): bool
    {
        $releaseId = (string) $manifest['release_id'];
        $channel = strtolower((string) $manifest['release_channel']);
        $sourceCommit = (string) $manifest['source_commit'];
        $artifactFilename = (string) $manifest['artifact_filename'];
        $artifactType = (string) $manifest['artifact_type'];
        $artifactSha256 = (string) $manifest['artifact_sha256'];
        $runtimeRequirements = is_array($manifest['runtime_requirements'])
            ? $manifest['runtime_requirements']
            : [];
        $hostRequirements = is_array($manifest['host_requirements'])
            ? $manifest['host_requirements']
            : [];
        $compatibilityPolicy = is_array($manifest['compatibility_policy'])
            ? $manifest['compatibility_policy']
            : [];

        return $manifest['schema_version'] === 1
            && $manifest['product'] === 'oneQay'
            && $manifest['repository'] === 'labzefry/oneQay'
            && preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]{0,127}\z/', $releaseId) === 1
            && in_array($channel, ['internal', 'preview', 'stable'], true)
            && preg_match('/\A[0-9a-f]{40}\z/i', $sourceCommit) === 1
            && $this->isSafeArtifactFilename($artifactFilename)
            && preg_match('/\A[A-Za-z0-9][A-Za-z0-9._+-]{0,31}\z/', $artifactType) === 1
            && is_int($manifest['artifact_size'])
            && $manifest['artifact_size'] > 0
            && preg_match('/\A[0-9a-f]{64}\z/i', $artifactSha256) === 1
            && $this->isValidRuntimeRequirements($runtimeRequirements)
            && $this->isValidHostRequirements($hostRequirements)
            && $this->isValidCompatibilityPolicy($compatibilityPolicy)
            && $manifest['migration_classification'] === 'NO_SCHEMA_CHANGE'
            && $manifest['attribution'] === 'Lab | zefry';
    }

    /** @param array<string, mixed> $policy */
    private function isValidCompatibilityPolicy(array $policy): bool
    {
        if ($this->missingKeys($policy, self::REQUIRED_COMPATIBILITY_POLICY_KEYS) !== []) {
            return false;
        }

        $range = $policy['supported_current_version_range'];
        if (! is_array($range)
            || ! isset($range['min'], $range['max'])
            || ! is_string($range['min'])
            || ! is_string($range['max'])) {
            return false;
        }

        $releaseVersion = trim((string) $policy['release_version']);
        $minimumCurrentVersion = trim($range['min']);
        $maximumCurrentVersion = trim($range['max']);

        return $this->isValidSemanticVersion($releaseVersion)
            && $this->isSafeReference($policy['build_provenance_ref'])
            && $this->isValidSemanticVersion($minimumCurrentVersion)
            && $this->isValidSemanticVersion($maximumCurrentVersion)
            && version_compare($minimumCurrentVersion, $maximumCurrentVersion, '<=')
            && $this->isPolicyToken($policy['deployment_compatibility'])
            && $policy['rollback_compatibility'] === 'NO_SCHEMA_CHANGE_ROLLBACK_SAFE'
            && $this->isPolicyToken($policy['public_bootstrap_layout_compatibility'])
            && $this->isSafeReference($policy['release_notes_reference']);
    }

    private function isValidSemanticVersion(string $version): bool
    {
        return preg_match('/\A\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?(?:\+[0-9A-Za-z.-]+)?\z/', $version) === 1;
    }

    private function isPolicyToken(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/\A[A-Z][A-Z0-9_]{0,63}\z/', trim($value)) === 1;
    }

    private function isSafeReference(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        $reference = trim($value);

        return $reference !== ''
            && strlen($reference) <= 255
            && ! str_contains($reference, '..')
            && preg_match('/\A[A-Za-z0-9][A-Za-z0-9._:\/@+#=-]{0,254}\z/', $reference) === 1;
    }

    private function isSafeArtifactFilename(string $filename): bool
    {
        return $filename !== ''
            && strlen($filename) <= 255
            && ! str_contains($filename, '/')
            && ! str_contains($filename, '\\')
            && ! str_contains($filename, "\0")
            && $filename !== '.'
            && $filename !== '..';
    }

    /**
     * @param array<string, mixed> $manifest
     * @param array<string, mixed> $artifact
     */
    private function artifactMatchesManifest(array $manifest, array $artifact): bool
    {
        return is_string($artifact['filename'])
            && is_int($artifact['size'])
            && is_string($artifact['sha256'])
            && $artifact['filename'] === $manifest['artifact_filename']
            && $artifact['size'] === $manifest['artifact_size']
            && preg_match('/\A[0-9a-f]{64}\z/i', $artifact['sha256']) === 1
            && hash_equals(strtolower((string) $manifest['artifact_sha256']), strtolower($artifact['sha256']));
    }

    /** @return array{ready: bool, reason: string} */
    private function check(bool $ready, string $success, string $failure): array
    {
        return ['ready' => $ready, 'reason' => $ready ? $success : $failure];
    }
}
