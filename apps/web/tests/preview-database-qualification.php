<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\PreviewDatabaseQualification;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$valid = [
    'enabled' => true,
    'profile' => 'mariadb',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'preview_database',
    'username' => 'preview_user',
    'password' => 'synthetic-secret-not-for-runtime',
    'charset' => 'utf8mb4',
];

$assert(
    PreviewDatabaseQualification::configurationViolations($valid) === [],
    'valid MariaDB Preview qualification configuration must pass static validation',
);

$invalid = $valid;
$invalid['enabled'] = false;
$invalid['profile'] = 'production';
$invalid['password'] = '';
$invalid['charset'] = 'latin1';
$violations = PreviewDatabaseQualification::configurationViolations($invalid);

foreach (['enabled', 'profile', 'password', 'charset'] as $expected) {
    $assert(in_array($expected, $violations, true), "invalid qualification config must report {$expected}");
}

$secretBearingInvalid = $valid;
$secretBearingInvalid['host'] = '';
$result = (new PreviewDatabaseQualification())->qualify($secretBearingInvalid);
$serialized = json_encode($result, JSON_THROW_ON_ERROR);

$assert(($result['status'] ?? null) === 'blocked', 'invalid qualification config must fail closed');
$assert(($result['failed_check'] ?? null) === 'configuration', 'configuration must be the failed check');
$assert(! str_contains($serialized, (string) $valid['password']), 'qualification result must never expose DB password');
$assert(! array_key_exists('database', $result), 'qualification result must not expose DB name');
$assert(! array_key_exists('username', $result), 'qualification result must not expose DB username');
$assert(($result['production_ready'] ?? true) === false, 'qualification result must never claim Production readiness');
$assert(($result['persistent_schema_created'] ?? true) === false, 'qualification result must never claim persistent schema creation');

fwrite(STDOUT, "M7.5 Preview database qualification regression passed.\n");
