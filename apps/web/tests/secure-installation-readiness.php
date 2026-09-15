<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use App\Infrastructure\Installation\SecureInstallationReadiness;

// Author by Lab | zefry

$subject = new SecureInstallationReadiness();
$extensions = ['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pdo', 'session', 'tokenizer', 'xml'];
$writablePaths = [
    'bootstrap/cache' => true,
    'storage/framework/cache' => true,
    'storage/framework/sessions' => true,
    'storage/framework/views' => true,
    'storage/logs' => true,
];
$productionEnvironment = [
    'APP_ENV' => 'production',
    'APP_KEY' => 'base64:secure-installation-key',
    'APP_URL' => 'https://oneqay.example.test',
    'APP_DEBUG' => 'false',
    'ONEQAY_DB_DRIVER' => 'mysql',
    'ONEQAY_DB_HOST' => '127.0.0.1',
    'ONEQAY_DB_DATABASE' => 'oneqay',
    'ONEQAY_DB_USERNAME' => 'oneqay',
    'ONEQAY_DB_PASSWORD' => 'must-never-appear-in-output',
];
$databaseState = [
    'connected' => true,
    'engine' => 'mysql',
    'server_version' => '8.0.36',
    'charset' => 'utf8mb4',
    'timezone' => '+00:00',
    'schema_state' => 'empty',
    'least_privilege' => true,
    'connection_detail' => 'database-secret-must-never-appear',
];
$hostPlatformState = [
    'os_family' => 'linux',
    'web_server_interface' => 'fpm-fcgi',
    'memory_bytes' => 536870912,
    'execution_time_seconds' => 120,
    'disk_free_bytes' => 5368709120,
    'capabilities' => [
        'https' => true,
        'dns' => true,
        'time_sync' => true,
        'outbound_allowlist' => true,
        'scheduler' => true,
        'archive' => true,
        'temp_directory' => true,
        'required_tools' => true,
    ],
    'diagnostic_detail' => 'host-secret-must-never-appear',
];
$releaseManifest = [
    'schema_version' => 1,
    'product' => 'oneQay',
    'repository' => 'labzefry/oneQay',
    'release_id' => 'oneqay-preview-0.1.0',
    'release_channel' => 'preview',
    'source_commit' => str_repeat('a', 40),
    'artifact_filename' => 'oneqay-preview-0.1.0.tar.gz',
    'artifact_type' => 'tar.gz',
    'artifact_size' => 1048576,
    'artifact_sha256' => str_repeat('b', 64),
    'runtime_requirements' => [
        'php_min' => '8.2.0',
        'php_extensions' => $extensions,
    ],
    'host_requirements' => [
        'os_families' => ['linux'],
        'web_server_interfaces' => ['fpm-fcgi', 'cgi-fcgi', 'apache2handler'],
        'memory_bytes_min' => 268435456,
        'execution_time_seconds_min' => 60,
        'disk_bytes_min' => 1073741824,
        'required_capabilities' => [
            'https',
            'dns',
            'time_sync',
            'outbound_allowlist',
            'scheduler',
            'archive',
            'temp_directory',
            'required_tools',
        ],
    ],
    'compatibility_policy' => [
        'release_version' => '0.1.0',
        'build_provenance_ref' => 'github-actions:34991613421',
        'supported_current_version_range' => [
            'min' => '0.0.0',
            'max' => '0.1.0',
        ],
        'deployment_compatibility' => 'WEB_RUNTIME_V1',
        'rollback_compatibility' => 'NO_SCHEMA_CHANGE_ROLLBACK_SAFE',
        'public_bootstrap_layout_compatibility' => 'PUBLIC_BOOTSTRAP_V1',
        'release_notes_reference' => 'CHANGELOG.md#sprint174',
    ],
    'migration_classification' => 'NO_SCHEMA_CHANGE',
    'attribution' => 'Lab | zefry',
    'operator_token' => 'manifest-secret-must-never-appear',
];
$artifactState = [
    'filename' => 'oneqay-preview-0.1.0.tar.gz',
    'size' => 1048576,
    'sha256' => str_repeat('b', 64),
];

$assess = static function (
    array $environment,
    ?array $loadedExtensions = null,
    ?string $phpVersion = null,
    ?array $paths = null,
    ?array $manifest = null,
    ?array $artifact = null,
    ?array $database = null,
    ?array $hostOverride = null
) use ($subject, $hostPlatformState): array {
    return $subject->assess(
        $environment,
        $loadedExtensions,
        $phpVersion,
        $paths,
        $manifest,
        $artifact,
        $database,
        $hostOverride ?? $hostPlatformState
    );
};

$ready = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($ready['ready'] === true);
assert($ready['checks']['php_version']['ready'] === true);
assert($ready['checks']['php_extensions']['ready'] === true);
assert($ready['checks']['host_platform']['ready'] === true);
assert($ready['checks']['database_compatibility']['ready'] === true);
assert($ready['checks']['filesystem_write']['ready'] === true);
assert($ready['checks']['release_manifest']['ready'] === true);
assert($ready['checks']['artifact_integrity']['ready'] === true);
$encodedReady = json_encode($ready, JSON_THROW_ON_ERROR);
assert(! str_contains($encodedReady, 'must-never-appear-in-output'));
assert(! str_contains($encodedReady, 'database-secret-must-never-appear'));
assert(! str_contains($encodedReady, 'host-secret-must-never-appear'));
assert(! str_contains($encodedReady, 'manifest-secret-must-never-appear'));
assert(! str_contains($encodedReady, str_repeat('b', 64)));

$missingDatabase = $assess([
    'APP_ENV' => 'production',
    'APP_KEY' => 'base64:secure-installation-key',
    'APP_URL' => 'https://oneqay.example.test',
    'APP_DEBUG' => 'false',
], $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($missingDatabase['ready'] === false);
assert($missingDatabase['checks']['environment']['ready'] === false);
assert($missingDatabase['checks']['database_compatibility']['ready'] === false);

$legacyDatabaseKeys = [
    'APP_ENV' => 'production',
    'APP_KEY' => 'base64:secure-installation-key',
    'APP_URL' => 'https://oneqay.example.test',
    'APP_DEBUG' => 'false',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_DATABASE' => 'oneqay',
    'DB_USERNAME' => 'oneqay',
];
$legacyDatabaseFailure = $assess($legacyDatabaseKeys, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($legacyDatabaseFailure['ready'] === false);
assert($legacyDatabaseFailure['checks']['environment']['ready'] === false);
assert($legacyDatabaseFailure['checks']['database_compatibility']['ready'] === false);

$unsafeProduction = $productionEnvironment;
$unsafeProduction['APP_KEY'] = 'changeme';
$unsafeProduction['APP_URL'] = 'http://oneqay.example.test';
$unsafeProduction['APP_DEBUG'] = 'true';
$unsafeProductionFailure = $assess($unsafeProduction, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($unsafeProductionFailure['ready'] === false);
assert($unsafeProductionFailure['checks']['application_key']['ready'] === false);
assert($unsafeProductionFailure['checks']['production_debug']['ready'] === false);
assert($unsafeProductionFailure['checks']['production_https']['ready'] === false);

$missingExtension = $assess($productionEnvironment, array_values(array_filter($extensions, static fn (string $extension): bool => $extension !== 'openssl')), '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($missingExtension['ready'] === false);
assert($missingExtension['checks']['php_extensions']['ready'] === false);

$oldPhp = $assess($productionEnvironment, $extensions, '8.1.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($oldPhp['ready'] === false);
assert($oldPhp['checks']['php_version']['ready'] === false);

$raisedRuntimeManifest = $releaseManifest;
$raisedRuntimeManifest['runtime_requirements']['php_min'] = '8.4.0';
$raisedRuntimeFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $raisedRuntimeManifest, $artifactState, $databaseState);
assert($raisedRuntimeFailure['ready'] === false);
assert($raisedRuntimeFailure['checks']['php_version']['ready'] === false);
assert($raisedRuntimeFailure['checks']['release_manifest']['ready'] === true);

$governedSubsetManifest = $releaseManifest;
$governedSubsetManifest['runtime_requirements']['php_extensions'] = ['openssl', 'pdo'];
$governedSubsetReady = $assess($productionEnvironment, ['openssl', 'pdo'], '8.3.0', $writablePaths, $governedSubsetManifest, $artifactState, $databaseState);
assert($governedSubsetReady['ready'] === true);
assert($governedSubsetReady['checks']['php_extensions']['ready'] === true);
assert($governedSubsetReady['checks']['release_manifest']['ready'] === true);

$missingRuntimeManifest = $releaseManifest;
unset($missingRuntimeManifest['runtime_requirements']);
$missingRuntimeFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $missingRuntimeManifest, $artifactState, $databaseState);
assert($missingRuntimeFailure['ready'] === false);
assert($missingRuntimeFailure['checks']['php_version']['ready'] === false);
assert($missingRuntimeFailure['checks']['php_extensions']['ready'] === false);
assert($missingRuntimeFailure['checks']['release_manifest']['ready'] === false);

$invalidRuntimeManifest = $releaseManifest;
$invalidRuntimeManifest['runtime_requirements']['php_extensions'][] = 'bad/extension-secret';
$invalidRuntimeFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $invalidRuntimeManifest, $artifactState, $databaseState);
assert($invalidRuntimeFailure['ready'] === false);
assert($invalidRuntimeFailure['checks']['php_extensions']['ready'] === false);
assert($invalidRuntimeFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($invalidRuntimeFailure, JSON_THROW_ON_ERROR), 'bad/extension-secret'));

$duplicateRuntimeManifest = $releaseManifest;
$duplicateRuntimeManifest['runtime_requirements']['php_extensions'] = ['openssl', 'OpenSSL'];
$duplicateRuntimeFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $duplicateRuntimeManifest, $artifactState, $databaseState);
assert($duplicateRuntimeFailure['ready'] === false);
assert($duplicateRuntimeFailure['checks']['release_manifest']['ready'] === false);

$disconnectedDatabase = $databaseState;
$disconnectedDatabase['connected'] = false;
$disconnectedFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $disconnectedDatabase);
assert($disconnectedFailure['ready'] === false);
assert($disconnectedFailure['checks']['database_compatibility']['ready'] === false);

$foreignEngine = $databaseState;
$foreignEngine['engine'] = 'postgresql';
$engineFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $foreignEngine);
assert($engineFailure['ready'] === false);
assert($engineFailure['checks']['database_compatibility']['ready'] === false);
assert(! str_contains(json_encode($engineFailure, JSON_THROW_ON_ERROR), 'postgresql'));

$invalidVersion = $databaseState;
$invalidVersion['server_version'] = 'unknown-version-secret';
$versionFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $invalidVersion);
assert($versionFailure['ready'] === false);
assert($versionFailure['checks']['database_compatibility']['ready'] === false);
assert(! str_contains(json_encode($versionFailure, JSON_THROW_ON_ERROR), 'unknown-version-secret'));

$wrongCharset = $databaseState;
$wrongCharset['charset'] = 'latin1';
$charsetFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $wrongCharset);
assert($charsetFailure['ready'] === false);
assert($charsetFailure['checks']['database_compatibility']['ready'] === false);

$wrongTimezone = $databaseState;
$wrongTimezone['timezone'] = '+07:00';
$timezoneFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $wrongTimezone);
assert($timezoneFailure['ready'] === false);
assert($timezoneFailure['checks']['database_compatibility']['ready'] === false);

$unknownSchema = $databaseState;
$unknownSchema['schema_state'] = 'foreign';
$schemaStateFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $unknownSchema);
assert($schemaStateFailure['ready'] === false);
assert($schemaStateFailure['checks']['database_compatibility']['ready'] === false);

$overPrivileged = $databaseState;
$overPrivileged['least_privilege'] = false;
$privilegeFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $overPrivileged);
assert($privilegeFailure['ready'] === false);
assert($privilegeFailure['checks']['database_compatibility']['ready'] === false);

$missingDatabaseState = $databaseState;
unset($missingDatabaseState['schema_state']);
$missingDatabaseStateFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $missingDatabaseState);
assert($missingDatabaseStateFailure['ready'] === false);
assert($missingDatabaseStateFailure['checks']['database_compatibility']['ready'] === false);
assert(str_contains($missingDatabaseStateFailure['checks']['database_compatibility']['reason'], 'schema_state'));

$mariaDbState = $databaseState;
$mariaDbState['engine'] = 'mariadb';
$mariaDbState['server_version'] = '10.11.6-MariaDB';
$mariaDbState['schema_state'] = 'recognized';
$mariaDbReady = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $mariaDbState);
assert($mariaDbReady['ready'] === true);
assert($mariaDbReady['checks']['database_compatibility']['ready'] === true);

$unsupportedConfiguredDriver = $productionEnvironment;
$unsupportedConfiguredDriver['ONEQAY_DB_DRIVER'] = 'pgsql';
$driverFailure = $assess($unsupportedConfiguredDriver, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($driverFailure['ready'] === false);
assert($driverFailure['checks']['database_compatibility']['ready'] === false);

$unwritable = $writablePaths;
$unwritable['storage/framework/views'] = false;
$filesystemFailure = $assess($productionEnvironment, $extensions, '8.3.0', $unwritable, $releaseManifest, $artifactState, $databaseState);
assert($filesystemFailure['ready'] === false);
assert($filesystemFailure['checks']['filesystem_write']['ready'] === false);
assert(str_contains($filesystemFailure['checks']['filesystem_write']['reason'], 'storage/framework/views'));
assert(! str_contains($filesystemFailure['checks']['filesystem_write']['reason'], '/home/'));

$missingPathState = $writablePaths;
unset($missingPathState['storage/logs']);
$missingPath = $assess($productionEnvironment, $extensions, '8.3.0', $missingPathState, $releaseManifest, $artifactState, $databaseState);
assert($missingPath['ready'] === false);
assert(str_contains($missingPath['checks']['filesystem_write']['reason'], 'storage/logs'));

$missingManifest = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, null, null, $databaseState);
assert($missingManifest['ready'] === false);
assert($missingManifest['checks']['php_version']['ready'] === false);
assert($missingManifest['checks']['php_extensions']['ready'] === false);
assert($missingManifest['checks']['host_platform']['ready'] === false);
assert($missingManifest['checks']['release_manifest']['ready'] === false);
assert($missingManifest['checks']['artifact_integrity']['ready'] === false);

$unsupportedSchema = $releaseManifest;
$unsupportedSchema['schema_version'] = 2;
$schemaFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $unsupportedSchema, $artifactState, $databaseState);
assert($schemaFailure['ready'] === false);
assert($schemaFailure['checks']['release_manifest']['ready'] === false);

$foreignRepository = $releaseManifest;
$foreignRepository['repository'] = 'someone/else';
$repositoryFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $foreignRepository, $artifactState, $databaseState);
assert($repositoryFailure['ready'] === false);
assert($repositoryFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($repositoryFailure, JSON_THROW_ON_ERROR), 'someone/else'));

$schemaChangingRelease = $releaseManifest;
$schemaChangingRelease['migration_classification'] = 'REQUIRES_MIGRATION';
$migrationFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $schemaChangingRelease, $artifactState, $databaseState);
assert($migrationFailure['ready'] === false);
assert($migrationFailure['checks']['release_manifest']['ready'] === false);

$unsafeFilename = $releaseManifest;
$unsafeFilename['artifact_filename'] = '../oneqay.tar.gz';
$filenameFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $unsafeFilename, $artifactState, $databaseState);
assert($filenameFailure['ready'] === false);
assert($filenameFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($filenameFailure, JSON_THROW_ON_ERROR), '../oneqay.tar.gz'));

$digestMismatch = $artifactState;
$digestMismatch['sha256'] = str_repeat('c', 64);
$digestFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $digestMismatch, $databaseState);
assert($digestFailure['ready'] === false);
assert($digestFailure['checks']['artifact_integrity']['ready'] === false);
assert(! str_contains(json_encode($digestFailure, JSON_THROW_ON_ERROR), str_repeat('c', 64)));

$sizeMismatch = $artifactState;
$sizeMismatch['size'] = 1048575;
$sizeFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $sizeMismatch, $databaseState);
assert($sizeFailure['ready'] === false);
assert($sizeFailure['checks']['artifact_integrity']['ready'] === false);

$missingCompatibilityPolicy = $releaseManifest;
unset($missingCompatibilityPolicy['compatibility_policy']);
$missingCompatibilityPolicyFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $missingCompatibilityPolicy, $artifactState, $databaseState);
assert($missingCompatibilityPolicyFailure['ready'] === false);
assert($missingCompatibilityPolicyFailure['checks']['release_manifest']['ready'] === false);
assert($missingCompatibilityPolicyFailure['checks']['artifact_integrity']['ready'] === false);

$missingProvenance = $releaseManifest;
unset($missingProvenance['compatibility_policy']['build_provenance_ref']);
$missingProvenanceFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $missingProvenance, $artifactState, $databaseState);
assert($missingProvenanceFailure['ready'] === false);
assert($missingProvenanceFailure['checks']['release_manifest']['ready'] === false);

$invalidReleaseVersion = $releaseManifest;
$invalidReleaseVersion['compatibility_policy']['release_version'] = 'v0.1';
$invalidReleaseVersionFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $invalidReleaseVersion, $artifactState, $databaseState);
assert($invalidReleaseVersionFailure['ready'] === false);
assert($invalidReleaseVersionFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($invalidReleaseVersionFailure, JSON_THROW_ON_ERROR), 'v0.1'));

$invertedVersionRange = $releaseManifest;
$invertedVersionRange['compatibility_policy']['supported_current_version_range'] = [
    'min' => '0.2.0',
    'max' => '0.1.0',
];
$invertedVersionRangeFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $invertedVersionRange, $artifactState, $databaseState);
assert($invertedVersionRangeFailure['ready'] === false);
assert($invertedVersionRangeFailure['checks']['release_manifest']['ready'] === false);

$unsafeProvenance = $releaseManifest;
$unsafeProvenance['compatibility_policy']['build_provenance_ref'] = 'https://example.test/../secret-provenance';
$unsafeProvenanceFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $unsafeProvenance, $artifactState, $databaseState);
assert($unsafeProvenanceFailure['ready'] === false);
assert($unsafeProvenanceFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($unsafeProvenanceFailure, JSON_THROW_ON_ERROR), 'secret-provenance'));

$unsafeRollbackPolicy = $releaseManifest;
$unsafeRollbackPolicy['compatibility_policy']['rollback_compatibility'] = 'SCHEMA_CHANGE_ROLLBACK_REQUIRED';
$unsafeRollbackPolicyFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $unsafeRollbackPolicy, $artifactState, $databaseState);
assert($unsafeRollbackPolicyFailure['ready'] === false);
assert($unsafeRollbackPolicyFailure['checks']['release_manifest']['ready'] === false);

$unsafeDeploymentPolicy = $releaseManifest;
$unsafeDeploymentPolicy['compatibility_policy']['deployment_compatibility'] = 'web runtime v1';
$unsafeDeploymentPolicyFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $unsafeDeploymentPolicy, $artifactState, $databaseState);
assert($unsafeDeploymentPolicyFailure['ready'] === false);
assert($unsafeDeploymentPolicyFailure['checks']['release_manifest']['ready'] === false);

$missingHostRequirements = $releaseManifest;
unset($missingHostRequirements['host_requirements']);
$missingHostRequirementsFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $missingHostRequirements, $artifactState, $databaseState);
assert($missingHostRequirementsFailure['ready'] === false);
assert($missingHostRequirementsFailure['checks']['host_platform']['ready'] === false);
assert($missingHostRequirementsFailure['checks']['release_manifest']['ready'] === false);
assert($missingHostRequirementsFailure['checks']['artifact_integrity']['ready'] === false);

$missingHostStateFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState, []);
assert($missingHostStateFailure['ready'] === false);
assert($missingHostStateFailure['checks']['host_platform']['ready'] === false);
assert(str_contains($missingHostStateFailure['checks']['host_platform']['reason'], 'os_family'));
assert(! str_contains($missingHostStateFailure['checks']['host_platform']['reason'], '/home/'));

$lowMemoryHost = $hostPlatformState;
$lowMemoryHost['memory_bytes'] = 134217728;
$lowMemoryFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState, $lowMemoryHost);
assert($lowMemoryFailure['ready'] === false);
assert($lowMemoryFailure['checks']['host_platform']['ready'] === false);

$lowDiskHost = $hostPlatformState;
$lowDiskHost['disk_free_bytes'] = 536870912;
$lowDiskFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState, $lowDiskHost);
assert($lowDiskFailure['ready'] === false);
assert($lowDiskFailure['checks']['host_platform']['ready'] === false);

$shortExecutionHost = $hostPlatformState;
$shortExecutionHost['execution_time_seconds'] = 30;
$shortExecutionFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState, $shortExecutionHost);
assert($shortExecutionFailure['ready'] === false);
assert($shortExecutionFailure['checks']['host_platform']['ready'] === false);

$unlimitedExecutionHost = $hostPlatformState;
$unlimitedExecutionHost['execution_time_seconds'] = 0;
$unlimitedExecutionReady = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState, $unlimitedExecutionHost);
assert($unlimitedExecutionReady['ready'] === true);
assert($unlimitedExecutionReady['checks']['host_platform']['ready'] === true);

$unsupportedOsHost = $hostPlatformState;
$unsupportedOsHost['os_family'] = 'unsupported-host-secret';
$unsupportedOsFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState, $unsupportedOsHost);
assert($unsupportedOsFailure['ready'] === false);
assert($unsupportedOsFailure['checks']['host_platform']['ready'] === false);
assert(! str_contains(json_encode($unsupportedOsFailure, JSON_THROW_ON_ERROR), 'unsupported-host-secret'));

$unsupportedInterfaceHost = $hostPlatformState;
$unsupportedInterfaceHost['web_server_interface'] = 'unsupported-interface-secret';
$unsupportedInterfaceFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState, $unsupportedInterfaceHost);
assert($unsupportedInterfaceFailure['ready'] === false);
assert($unsupportedInterfaceFailure['checks']['host_platform']['ready'] === false);
assert(! str_contains(json_encode($unsupportedInterfaceFailure, JSON_THROW_ON_ERROR), 'unsupported-interface-secret'));

$missingSchedulerHost = $hostPlatformState;
$missingSchedulerHost['capabilities']['scheduler'] = false;
$missingSchedulerFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState, $missingSchedulerHost);
assert($missingSchedulerFailure['ready'] === false);
assert($missingSchedulerFailure['checks']['host_platform']['ready'] === false);

$missingCapabilityHost = $hostPlatformState;
unset($missingCapabilityHost['capabilities']['archive']);
$missingCapabilityFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState, $missingCapabilityHost);
assert($missingCapabilityFailure['ready'] === false);
assert($missingCapabilityFailure['checks']['host_platform']['ready'] === false);

$invalidHostRequirements = $releaseManifest;
$invalidHostRequirements['host_requirements']['required_capabilities'][] = 'shell_exec';
$invalidHostRequirementsFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $invalidHostRequirements, $artifactState, $databaseState);
assert($invalidHostRequirementsFailure['ready'] === false);
assert($invalidHostRequirementsFailure['checks']['host_platform']['ready'] === false);
assert($invalidHostRequirementsFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($invalidHostRequirementsFailure, JSON_THROW_ON_ERROR), 'shell_exec'));

$duplicateHostTokenManifest = $releaseManifest;
$duplicateHostTokenManifest['host_requirements']['os_families'] = ['linux', 'Linux'];
$duplicateHostTokenFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $duplicateHostTokenManifest, $artifactState, $databaseState);
assert($duplicateHostTokenFailure['ready'] === false);
assert($duplicateHostTokenFailure['checks']['release_manifest']['ready'] === false);

$invalidHostThresholdManifest = $releaseManifest;
$invalidHostThresholdManifest['host_requirements']['execution_time_seconds_min'] = 7200;
$invalidHostThresholdFailure = $assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $invalidHostThresholdManifest, $artifactState, $databaseState);
assert($invalidHostThresholdFailure['ready'] === false);
assert($invalidHostThresholdFailure['checks']['release_manifest']['ready'] === false);

echo "secure installation readiness regression passed\n";
