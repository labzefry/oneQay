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
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_DATABASE' => 'oneqay',
    'DB_USERNAME' => 'oneqay',
    'DB_PASSWORD' => 'must-never-appear-in-output',
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
    'migration_classification' => 'NO_SCHEMA_CHANGE',
    'attribution' => 'Lab | zefry',
    'operator_token' => 'manifest-secret-must-never-appear',
];
$artifactState = [
    'filename' => 'oneqay-preview-0.1.0.tar.gz',
    'size' => 1048576,
    'sha256' => str_repeat('b', 64),
];

$ready = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState);
assert($ready['ready'] === true);
assert($ready['checks']['filesystem_write']['ready'] === true);
assert($ready['checks']['release_manifest']['ready'] === true);
assert($ready['checks']['artifact_integrity']['ready'] === true);
$encodedReady = json_encode($ready, JSON_THROW_ON_ERROR);
assert(! str_contains($encodedReady, 'must-never-appear-in-output'));
assert(! str_contains($encodedReady, 'manifest-secret-must-never-appear'));
assert(! str_contains($encodedReady, str_repeat('b', 64)));

$missingDatabase = $subject->assess([
    'APP_ENV' => 'production',
    'APP_KEY' => 'base64:secure-installation-key',
    'APP_URL' => 'https://oneqay.example.test',
    'APP_DEBUG' => 'false',
], $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState);
assert($missingDatabase['ready'] === false);
assert($missingDatabase['checks']['environment']['ready'] === false);

$unsafeProduction = $subject->assess([
    'APP_ENV' => 'production',
    'APP_KEY' => 'changeme',
    'APP_URL' => 'http://oneqay.example.test',
    'APP_DEBUG' => 'true',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_DATABASE' => 'oneqay',
    'DB_USERNAME' => 'oneqay',
], $extensions, '8.3.0', $writablePaths, $releaseManifest, $artifactState);
assert($unsafeProduction['ready'] === false);
assert($unsafeProduction['checks']['application_key']['ready'] === false);
assert($unsafeProduction['checks']['production_debug']['ready'] === false);
assert($unsafeProduction['checks']['production_https']['ready'] === false);

$missingExtension = $subject->assess([
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:secure-installation-key',
    'APP_URL' => 'http://localhost',
    'APP_DEBUG' => 'false',
    'DB_CONNECTION' => 'sqlite',
    'DB_HOST' => 'localhost',
    'DB_DATABASE' => ':memory:',
    'DB_USERNAME' => 'test',
], array_values(array_filter($extensions, static fn (string $extension): bool => $extension !== 'openssl')), '8.3.0', $writablePaths, $releaseManifest, $artifactState);
assert($missingExtension['ready'] === false);
assert($missingExtension['checks']['php_extensions']['ready'] === false);

$oldPhp = $subject->assess([
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:secure-installation-key',
    'APP_URL' => 'http://localhost',
    'APP_DEBUG' => 'false',
    'DB_CONNECTION' => 'sqlite',
    'DB_HOST' => 'localhost',
    'DB_DATABASE' => ':memory:',
    'DB_USERNAME' => 'test',
], $extensions, '8.1.0', $writablePaths, $releaseManifest, $artifactState);
assert($oldPhp['ready'] === false);
assert($oldPhp['checks']['php_version']['ready'] === false);

$unwritable = $writablePaths;
$unwritable['storage/framework/views'] = false;
$filesystemFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $unwritable, $releaseManifest, $artifactState);
assert($filesystemFailure['ready'] === false);
assert($filesystemFailure['checks']['filesystem_write']['ready'] === false);
assert(str_contains($filesystemFailure['checks']['filesystem_write']['reason'], 'storage/framework/views'));
assert(! str_contains($filesystemFailure['checks']['filesystem_write']['reason'], '/home/'));

$missingPathState = $writablePaths;
unset($missingPathState['storage/logs']);
$missingPath = $subject->assess($productionEnvironment, $extensions, '8.3.0', $missingPathState, $releaseManifest, $artifactState);
assert($missingPath['ready'] === false);
assert(str_contains($missingPath['checks']['filesystem_write']['reason'], 'storage/logs'));

$missingManifest = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, null, null);
assert($missingManifest['ready'] === false);
assert($missingManifest['checks']['release_manifest']['ready'] === false);
assert($missingManifest['checks']['artifact_integrity']['ready'] === false);

$unsupportedSchema = $releaseManifest;
$unsupportedSchema['schema_version'] = 2;
$schemaFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $unsupportedSchema, $artifactState);
assert($schemaFailure['ready'] === false);
assert($schemaFailure['checks']['release_manifest']['ready'] === false);

$foreignRepository = $releaseManifest;
$foreignRepository['repository'] = 'someone/else';
$repositoryFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $foreignRepository, $artifactState);
assert($repositoryFailure['ready'] === false);
assert($repositoryFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($repositoryFailure, JSON_THROW_ON_ERROR), 'someone/else'));

$schemaChangingRelease = $releaseManifest;
$schemaChangingRelease['migration_classification'] = 'REQUIRES_MIGRATION';
$migrationFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $schemaChangingRelease, $artifactState);
assert($migrationFailure['ready'] === false);
assert($migrationFailure['checks']['release_manifest']['ready'] === false);

$unsafeFilename = $releaseManifest;
$unsafeFilename['artifact_filename'] = '../oneqay.tar.gz';
$filenameFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $unsafeFilename, $artifactState);
assert($filenameFailure['ready'] === false);
assert($filenameFailure['checks']['release_manifest']['ready'] === false);
assert(! str_contains(json_encode($filenameFailure, JSON_THROW_ON_ERROR), '../oneqay.tar.gz'));

$digestMismatch = $artifactState;
$digestMismatch['sha256'] = str_repeat('c', 64);
$digestFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $digestMismatch);
assert($digestFailure['ready'] === false);
assert($digestFailure['checks']['artifact_integrity']['ready'] === false);
assert(! str_contains(json_encode($digestFailure, JSON_THROW_ON_ERROR), str_repeat('c', 64)));

$sizeMismatch = $artifactState;
$sizeMismatch['size'] = 1048575;
$sizeFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths, $releaseManifest, $sizeMismatch);
assert($sizeFailure['ready'] === false);
assert($sizeFailure['checks']['artifact_integrity']['ready'] === false);

echo "secure installation readiness regression passed\n";
