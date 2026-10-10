<?php
declare(strict_types=1);
// Author by Lab | zefry — Production cookie policy verification.
require_once dirname(__DIR__).'/vendor/autoload.php';
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        return array_key_exists($key, $_ENV) ? $_ENV[$key] : $default;
    }
}
if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string { return dirname(__DIR__).'/storage/'.$path; }
}
function verify(bool $passed, string $message): void {
    if (!$passed) { fwrite(STDERR, 'FAIL:'.$message."\n"); exit(1); }
}
$file = dirname(__DIR__).'/config/session.php';
$_ENV['APP_ENV'] = 'production';
$_ENV['SESSION_SECURE_COOKIE'] = 'false';
$production = require $file;
verify($production['secure'] === true, 'Production secure even when env false');
verify($production['http_only'] === true && $production['same_site'] === 'lax', 'Production cookie isolation');
$_ENV['SESSION_SECURE_COOKIE'] = 'true';
$productionExplicit = require $file;
verify($productionExplicit['secure'] === true, 'Production secure positive');
$_ENV['APP_ENV'] = 'testing';
$_ENV['SESSION_SECURE_COOKIE'] = 'false';
$testing = require $file;
verify($testing['secure'] === false, 'CI/test HTTP preserved');
$_ENV['SESSION_SECURE_COOKIE'] = 'true';
$testingSecure = require $file;
verify($testingSecure['secure'] === true, 'CI can require Secure');
echo "production_session_cookie_policy_pass:5\n";
