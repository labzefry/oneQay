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
    private const REQUIRED_EXTENSIONS = ['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pdo', 'session', 'tokenizer', 'xml'];

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
        'migration_classification',
        'attribution',
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
     * @return array{ready: bool, checks: array<string, array{ready: bool, reason: string}>}
     */
    public function assess(
        array $environment,
        ?array $loadedExtensions = null,
        ?string $phpVersion = null,
        ?array $writablePaths = null,
        ?array $releaseManifest = null,
        ?array $artifactState = null,
        ?array $databaseState = null
    ): array {
        $extensions = array_map('strtolower', $loadedExtensions ?? get_loaded_extensions());
        $version = $phpVersion ?? PHP_VERSION;
        $checks = [];

        $checks['php_version'] = $this->check(
            version_compare($version, '8.2.0', '>='),
            'PHP runtime satisfies the minimum supported version.',
            'PHP runtime is below the minimum supported version.'
        );

        $missingExtensions = array_values(array_filter(
            self::REQUIRED_EXTENSIONS,
            static fn (string $extension): bool => ! in_array($extension, $extensions, true)
        ));
        $checks['php_extensions'] = $this->check(
            $missingExtensions === [],
            'Required PHP extensions are available.',
            'Required PHP extensions are missing: '.implode(', ', $missingExtensions).'.'
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

        $manifest = $releaseManifest ?? [];
        $missingManifestKeys = $this->missingKeys($manifest, self::REQUIRED_RELEASE_MANIFEST_KEYS);
        $manifestReady = $missingManifestKeys === [] && $this->isValidReleaseManifest($manifest);
        $checks['release_manifest'] = $this->check(
            $manifestReady,
            'Governed release manifest identity and policy are valid.',
            $missingManifestKeys === []
                ? 'Governed release manifest identity or policy is invalid.'
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
            && $manifest['migration_classification'] === 'NO_SCHEMA_CHANGE'
            && $manifest['attribution'] === 'Lab | zefry';
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
