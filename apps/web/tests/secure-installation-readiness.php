<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use App\Infrastructure\Installation\SecureInstallationReadiness;

// Author by Lab | zefry

$subject = new SecureInstallationReadiness();
$extensions = ['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pdo', 'session', 'tokenizer', 'xml'];

$ready = $subject->assess([
    'APP_ENV' => 'production',
    'APP_KEY' => 'base64:secure-installation-key',
    'APP_URL' => 'https://oneqay.example.test',
    'APP_DEBUG' => 'false',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_DATABASE' => 'oneqay',
    'DB_USERNAME' => 'oneqay',
    'DB_PASSWORD' => 'must-never-appear-in-output',
], $extensions, '8.3.0');

assert($ready['ready'] === true);
assert(strpos(json_encode($ready, JSON_THROW_ON_ERROR), 'must-never-appear-in-output') === false);

$missingDatabase = $subject->assess([
    'APP_ENV' => 'production',
    'APP_KEY' => 'base64:secure-installation-key',
    'APP_URL' => 'https://oneqay.example.test',
    'APP_DEBUG' => 'false',
], $extensions, '8.3.0');
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
], $extensions, '8.3.0');
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
], array_values(array_filter($extensions, static fn (string $extension): bool => $extension !== 'openssl')), '8.3.0');
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
], $extensions, '8.1.0');
assert($oldPhp['ready'] === false);
assert($oldPhp['checks']['php_version']['ready'] === false);

echo "secure installation readiness regression passed\n";
