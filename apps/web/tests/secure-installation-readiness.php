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

$ready = $subject->assess($productionEnvironment, $extensions, '8.3.0', $writablePaths);
assert($ready['ready'] === true);
assert($ready['checks']['filesystem_write']['ready'] === true);
assert(strpos(json_encode($ready, JSON_THROW_ON_ERROR), 'must-never-appear-in-output') === false);

$missingDatabase = $subject->assess([
    'APP_ENV' => 'production',
    'APP_KEY' => 'base64:secure-installation-key',
    'APP_URL' => 'https://oneqay.example.test',
    'APP_DEBUG' => 'false',
], $extensions, '8.3.0', $writablePaths);
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
], $extensions, '8.3.0', $writablePaths);
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
], array_values(array_filter($extensions, static fn (string $extension): bool => $extension !== 'openssl')), '8.3.0', $writablePaths);
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
], $extensions, '8.1.0', $writablePaths);
assert($oldPhp['ready'] === false);
assert($oldPhp['checks']['php_version']['ready'] === false);

$unwritable = $writablePaths;
$unwritable['storage/framework/views'] = false;
$filesystemFailure = $subject->assess($productionEnvironment, $extensions, '8.3.0', $unwritable);
assert($filesystemFailure['ready'] === false);
assert($filesystemFailure['checks']['filesystem_write']['ready'] === false);
assert(str_contains($filesystemFailure['checks']['filesystem_write']['reason'], 'storage/framework/views'));
assert(! str_contains($filesystemFailure['checks']['filesystem_write']['reason'], '/home/'));

$missingPathState = $writablePaths;
unset($missingPathState['storage/logs']);
$missingPath = $subject->assess($productionEnvironment, $extensions, '8.3.0', $missingPathState);
assert($missingPath['ready'] === false);
assert(str_contains($missingPath['checks']['filesystem_write']['reason'], 'storage/logs'));

echo "secure installation readiness regression passed\n";
