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
    'migration_classification' => 'NO_SCHEMA_CHANGE',
    'attribution' => 'Lab | zefry',
    'operator_token' => 'manifest-secret-must-never-appear',
];
$artifactState = [
    'filename' => 'oneqay-preview-0.1.0.tar.gz',
    'size' => 1048576,
    'sha256' => str_repeat('b', 64),
];

$ready = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($ready['ready'] === true);
assert($ready['checks']['php_version']['ready'] === true);
assert($ready['checks']['php_extensions']['ready'] === true);
assert($ready['checks']['database_compatibility']['ready'] === true);
assert($ready['checks']['filesystem_write']['ready'] === true);
assert($ready['checks']['release_manifest']['ready'] === true);
assert($ready['checks']['artifact_integrity']['ready'] === true);
$encodedReady = json_encode($ready, JSON_THROW_ON_ERROR);
assert(! str_contains($encodedReady, 'must-never-appear-in-output'));
assert(! str_contains($encodedReady, 'database-secret-must-never-appear'));
assert(! str_contains($encodedReady, 'manifest-secret-must-never-appear'));
assert(! str_contains($encodedReady, str_repeat('b', 64)));

$missingDatabase = $subject->assess([
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
$legacyDatabaseFailure = $subject->assess($legacyDatabaseKeys, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($legacyDatabaseFailure['ready'] === false);
assert($legacyDatabaseFailure['checks']['environment']['ready'] === false);
assert($legacyDatabaseFailure['checks']['database_compatibility']['ready'] === false);

$unsafeProduction = $productionEnvironment;
$unsafeProduction['APP_KEY'] = 'changeme';
$unsafeProduction['APP_URL'] = 'http://oneqay.example.test';
$unsafeProduction['APP_DEBUG'] = 'true';
$unsafeProductionFailure = $subject->assess($unsafeProduction, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($unsafeProductionFailure['ready'] === false);
assert($unsafeProductionFailure['checks']['application_key']['ready'] === false);
assert($unsafeProductionFailure['checks']['production_debug']['ready'] === false);
assert($unsafeProductionFailure['checks']['production_https']['ready'] === false);

$missingExtension = $subject->assess($productionEnvironment, array_values(array_filter($extensions, static fn (string $extension): bool => $extension !== 'openssl')), '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($missingExtension['ready'] === false);
assert($missingExtension['checks']['php_extensions']['ready'] === false);

$oldPhp = $subject->assess($productionEnvironment, $extensions, '8.1.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($oldPhp['ready'] === false);
assert($oldPhp['checks']['php_version']['ready'] === false);

$raisedRuntimeManifest = $releaseManifest;
$raisedRuntimeManifest['runtime_requirements']['php_min'] = '8.4.0';
$raisedRuntimeFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $raisedRuntimeManifest, $artifactState, $databaseState);
assert($raisedRuntimeFailure['ready'] === false);
assert($raisedRuntimeFailure['checks']['php_version']['ready'] === false);
assert($raisedRuntimeFailure['checks']['release_manifest']['ready'] === true);

$governedSubsetManifest = $releaseManifest;
$governedSubsetManifest['runtime_requirements']['php_extensions'] = ['openssl', 'pdo'];
$governedSubsetReady = $subject->assess($productionEnvironment, ['openssl', 'pdo'], '8.3.0', $writablePaths, $governedSubsetManifest, $artifactState, $databaseState);
assert($governedSubsetReady['ready'] === true);
assert($governedSubsetReady['checks']['php_extensions']['ready'] === true);
assert($governedSubsetReady['checks']['release_manifest']['ready'] === true);

$missingRuntimeManifest = $releaseManifest;
unset($missingRuntimeManifest['runtime_requirements']);
$missingRuntimeFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $missingRuntimeManifest, $artifactState, $databaseState);
assert($missingRuntimeFailure['ready'] === false);
assert($missingRuntimeFailure['checks']['php_version']['ready'] === false);
assert($missingRuntimeFailure['checks']['php_extensions']['ready'] === false);
assert($missingRuntimeFailure['checks']['release_manifest']['ready'] === false);

$invalidRuntimeManifest = $releaseManifest;
$invalidRuntimeManifest['runtime_requirements']['php_extensions'][] = 'bad/extension-secret';
$invalidRuntimeFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $invalidRuntimeManifest, $artifactState, $databaseState);
assert($invalidRuntimeFailure['ready'] === false);
assert($invalidRuntimeFailure['checks']['php_extensions']['ready'] === false);
assert($invalidRuntimeFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($invalidRuntimeFailure, JSON_THROW_ON_ERROR), 'bad/extension-secret'));

$duplicateRuntimeManifest = $releaseManifest;
$duplicateRuntimeManifest['runtime_requirements']['php_extensions'] = ['openssl', 'OpenSSL'];
$duplicateRuntimeFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $duplicateRuntimeManifest, $artifactState, $databaseState);
assert($duplicateRuntimeFailure['ready'] === false);
assert($duplicateRuntimeFailure['checks']['release_manifest']['ready'] === false);

$disconnectedDatabase = $databaseState;
$disconnectedDatabase['connected'] = false;
$disconnectedFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $disconnectedDatabase);
assert($disconnectedFailure['ready'] === false);
assert($disconnectedFailure['checks']['database_compatibility']['ready'] === false);

$foreignEngine = $databaseState;
$foreignEngine['engine'] = 'postgresql';
$engineFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $foreignEngine);
assert($engineFailure['ready'] === false);
assert($engineFailure['checks']['database_compatibility']['ready'] === false);
assert(! str_contains(json_encode($engineFailure, JSON_THROW_ON_ERROR), 'postgresql'));

$invalidVersion = $databaseState;
$invalidVersion['server_version'] = 'unknown-version-secret';
$versionFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $invalidVersion);
assert($versionFailure['ready'] === false);
assert($versionFailure['checks']['database_compatibility']['ready'] === false);
assert(! str_contains(json_encode($versionFailure, JSON_THROW_ON_ERROR), 'unknown-version-secret'));

$wrongCharset = $databaseState;
$wrongCharset['charset'] = 'latin1';
$charsetFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $wrongCharset);
assert($charsetFailure['ready'] === false);
assert($charsetFailure['checks']['database_compatibility']['ready'] === false);

$wrongTimezone = $databaseState;
$wrongTimezone['timezone'] = '+07:00';
$timezoneFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $wrongTimezone);
assert($timezoneFailure['ready'] === false);
assert($timezoneFailure['checks']['database_compatibility']['ready'] === false);

$unknownSchema = $databaseState;
$unknownSchema['schema_state'] = 'foreign';
$schemaStateFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $unknownSchema);
assert($schemaStateFailure['ready'] === false);
assert($schemaStateFailure['checks']['database_compatibility']['ready'] === false);

$overPrivileged = $databaseState;
$overPrivileged['least_privilege'] = false;
$privilegeFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $overPrivileged);
assert($privilegeFailure['ready'] === false);
assert($privilegeFailure['checks']['database_compatibility']['ready'] === false);

$missingDatabaseState = $databaseState;
unset($missingDatabaseState['schema_state']);
$missingDatabaseStateFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $missingDatabaseState);
assert($missingDatabaseStateFailure['ready'] === false);
assert($missingDatabaseStateFailure['checks']['database_compatibility']['ready'] === false);
assert(str_contains($missingDatabaseStateFailure['checks']['database_compatibility']['reason'], 'schema_state'));

$mariaDbState = $databaseState;
$mariaDbState['engine'] = 'mariadb';
$mariaDbState['server_version'] = '10.11.6-MariaDB';
$mariaDbState['schema_state'] = 'recognized';
$mariaDbReady = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $mariaDbState);
assert($mariaDbReady['ready'] === true);
assert($mariaDbReady['checks']['database_compatibility']['ready'] === true);

$unsupportedConfiguredDriver = $productionEnvironment;
$unsupportedConfiguredDriver['ONEQAY_DB_DRIVER'] = 'pgsql';
$driverFailure = $subject->assess($unsupportedConfiguredDriver, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState, $databaseState);
assert($driverFailure['ready'] === false);
assert($driverFailure['checks']['database_compatibility']['ready'] === false);

$unwritable = $writablePaths;
$unwritable['storage/framework/views'] = false;
$filesystemFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $unwritable, $releaseManifest, $artifactState, $databaseState);
assert($filesystemFailure['ready'] === false);
assert($filesystemFailure['checks']['filesystem_write']['ready'] === false);
assert(str_contains($filesystemFailure['checks']['filesystem_write']['reason'], 'storage/framework/views'));
assert(! str_contains($filesystemFailure['checks']['filesystem_write']['reason'], '/home/'));

$missingPathState = $writablePaths;
unset($missingPathState['storage/logs']);
$missingPath = $subject->assess($productionEnvironment, $extensions, '8.3.0', $missingPathState, $releaseManifest, $artifactState, $databaseState);
assert($missingPath['ready'] === false);
assert(str_contains($missingPath['checks']['filesystem_write']['reason'], 'storage/logs'));

$missingManifest = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, null, null, $databaseState);
assert($missingManifest['ready'] === false);
assert($missingManifest['checks']['php_version']['ready'] === false);
assert($missingManifest['checks']['php_extensions']['ready'] === false);
assert($missingManifest['checks']['release_manifest']['ready'] === false);
assert($missingManifest['checks']['artifact_integrity']['ready'] === false);

$unsupportedSchema = $releaseManifest;
$unsupportedSchema['schema_version'] = 2;
$schemaFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $unsupportedSchema, $artifactState, $databaseState);
assert($schemaFailure['ready'] === false);
assert($schemaFailure['checks']['release_manifest']['ready'] === false);

$foreignRepository = $releaseManifest;
$foreignRepository['repository'] = 'someone/else';
$repositoryFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $foreignRepository, $artifactState, $databaseState);
assert($repositoryFailure['ready'] === false);
assert($repositoryFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($repositoryFailure, JSON_THROW_ON_ERROR), 'someone/else'));

$schemaChangingRelease = $releaseManifest;
$schemaChangingRelease['migration_classification'] = 'REQUIRES_MIGRATION';
$migrationFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $schemaChangingRelease, $artifactState, $databaseState);
assert($migrationFailure['ready'] === false);
assert($migrationFailure['checks']['release_manifest']['ready'] === false);

$unsafeFilename = $releaseManifest;
$unsafeFilename['artifact_filename'] = '../oneqay.tar.gz';
$filenameFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $unsafeFilename, $artifactState, $databaseState);
assert($filenameFailure['ready'] === false);
assert($filenameFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($filenameFailure, JSON_THROW_ON_ERROR), '../oneqay.tar.gz'));

$digestMismatch = $artifactState;
$digestMismatch['sha256'] = str_repeat('c', 64);
$digestFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $digestMismatch, $databaseState);
assert($digestFailure['ready'] === false);
assert($digestFailure['checks']['artifact_integrity']['ready'] === false);
assert(! str_contains(json_encode($digestFailure, JSON_THROW_ON_ERROR), str_repeat('c', 64)));

$sizeMismatch = $artifactState;
$sizeMismatch['size'] = 1048575;
$sizeFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $sizeMismatch, $databaseState);
assert($sizeFailure['ready'] === false);
assert($sizeFailure['checks']['artifact_integrity']['ready'] === false);

echo "secure installation readiness regression passed\n";
