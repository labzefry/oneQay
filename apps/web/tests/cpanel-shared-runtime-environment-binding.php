<?php

declare(strict_types=1);

use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedRuntimeEnvironmentStatus;
use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use App\Infrastructure\SystemUpdate\SharedConfiguration\FilesystemSystemUpdateSharedRuntimeEnvironmentGuard;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("cPanel shared runtime environment binding regression failed: {$case}");
    }
};

$expectDenied = static function (callable $attempt, string $expectedCode, string $case) use ($assert): void {
    try {
        $attempt();
        $assert(false, $case);
    } catch (SystemUpdateControlPlaneViolation $violation) {
        $assert($violation->safeCode() === $expectedCode, $case.' safe code');
        $assert($violation->getMessage() === 'System update control plane request denied.', $case.' generic message');
    }
};

$removeTree = static function (string $path) use (&$removeTree): void {
    if (! file_exists($path) && ! is_link($path)) {
        return;
    }

    if (is_link($path) || is_file($path)) {
        @chmod($path, 0600);
        @unlink($path);
        return;
    }

    @chmod($path, 0700);
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry !== '.' && $entry !== '..') {
            $removeTree($path.DIRECTORY_SEPARATOR.$entry);
        }
    }
    @rmdir($path);
};

$root = sys_get_temp_dir().'/oneqay-shared-runtime-env-'.bin2hex(random_bytes(8));
mkdir($root, 0700, true);
chmod($root, 0700);

$reset = static function () use ($root, $removeTree): void {
    foreach (['shared'] as $entry) {
        $removeTree($root.'/'.$entry);
    }
};

$prepareDirectories = static function (int $sharedMode = 0700, int $runtimeMode = 0700) use ($root): void {
    mkdir($root.'/shared', $sharedMode, true);
    mkdir($root.'/shared/runtime', $runtimeMode, true);
    chmod($root.'/shared', $sharedMode);
    chmod($root.'/shared/runtime', $runtimeMode);
};

$writeEnvironment = static function (string $contents, int $mode = 0600) use ($root): void {
    file_put_contents($root.'/shared/runtime/.env', $contents);
    chmod($root.'/shared/runtime/.env', $mode);
    clearstatcache(true, $root.'/shared/runtime/.env');
};

try {
    $guard = new FilesystemSystemUpdateSharedRuntimeEnvironmentGuard($root);

    $expectDenied(
        static fn () => $guard->assertReady(1_786_930_000),
        'shared_runtime_environment_missing',
        'ENV-001 missing shared runtime environment fails closed',
    );

    $symlinkTarget = $root.'/shared-target';
    mkdir($symlinkTarget, 0700, true);
    chmod($symlinkTarget, 0700);
    symlink($symlinkTarget, $root.'/shared');
    $expectDenied(
        static fn () => $guard->assertReady(1_786_930_010),
        'shared_runtime_environment_symlink_forbidden',
        'ENV-002 shared root symlink denied',
    );
    @unlink($root.'/shared');
    @rmdir($symlinkTarget);

    $prepareDirectories(0755, 0700);
    $writeEnvironment("APP_KEY=synthetic-app-key\n", 0600);
    $expectDenied(
        static fn () => $guard->assertReady(1_786_930_020),
        'shared_runtime_environment_permissions_invalid',
        'ENV-003 group-readable shared directory denied',
    );
    $reset();

    $prepareDirectories();
    $target = $root.'/shared/runtime/env-target';
    file_put_contents($target, "APP_KEY=synthetic-app-key\n");
    chmod($target, 0600);
    symlink($target, $root.'/shared/runtime/.env');
    $expectDenied(
        static fn () => $guard->assertReady(1_786_930_030),
        'shared_runtime_environment_symlink_forbidden',
        'ENV-004 environment symlink denied',
    );
    $reset();

    $prepareDirectories();
    $writeEnvironment("APP_KEY=synthetic-app-key\n", 0644);
    $expectDenied(
        static fn () => $guard->assertReady(1_786_930_040),
        'shared_runtime_environment_permissions_invalid',
        'ENV-005 group/world-readable environment denied',
    );
    $reset();

    $prepareDirectories();
    $writeEnvironment("APP_ENV=preview\nAPP_DEBUG=false\n", 0600);
    $expectDenied(
        static fn () => $guard->assertReady(1_786_930_050),
        'shared_runtime_app_key_missing',
        'ENV-006 APP_KEY absence fails closed',
    );
    $reset();

    $prepareDirectories();
    $writeEnvironment("APP_KEY=REPLACE_WITH_REAL_KEY\n", 0600);
    $expectDenied(
        static fn () => $guard->assertReady(1_786_930_060),
        'shared_runtime_app_key_missing',
        'ENV-007 placeholder APP_KEY fails closed',
    );
    $reset();

    $prepareDirectories();
    $writeEnvironment(str_repeat('A', 65537), 0600);
    $expectDenied(
        static fn () => $guard->assertReady(1_786_930_070),
        'shared_runtime_environment_size_invalid',
        'ENV-008 oversized environment fails closed',
    );
    $reset();

    $prepareDirectories();
    $writeEnvironment(
        "APP_NAME=oneQay\n"
        ."APP_ENV=preview\n"
        ."APP_KEY=base64:c3ludGhldGljLXNoYXJlZC1ydW50aW1lLWtleQ==\n"
        ."APP_DEBUG=false\n"
        ."DB_PASSWORD=synthetic-db-password-must-not-leak\n"
        ."MAIL_PASSWORD=synthetic-mail-password-must-not-leak\n",
        0600,
    );

    $status = $guard->assertReady(1_786_930_080);
    $safe = $status->toSafeArray();
    $assert($status->isReady(), 'ENV-009 private shared environment accepted');
    $assert(($safe['profile'] ?? null) === SystemUpdateSharedRuntimeEnvironmentStatus::PROFILE, 'ENV-010 profile reported');
    $assert(($safe['required_secrets']['APP_KEY'] ?? null) === 'PRESENT', 'ENV-011 APP_KEY presence only');
    $assert(($safe['code'] ?? null) === 'shared_runtime_environment_ready', 'ENV-012 safe status code');

    $serialized = strtolower(json_encode($safe, JSON_THROW_ON_ERROR));
    foreach ([
        'c3ludghldgljlxno',
        'synthetic-db-password',
        'synthetic-mail-password',
        'db_password',
        'mail_password',
        '.env',
        '/home/',
        'public_html',
        strtolower($root),
    ] as $forbidden) {
        $assert(! str_contains($serialized, $forbidden), 'SAFE-001 safe status excludes '.$forbidden);
    }

    $repositoryRoot = dirname(__DIR__, 3);
    $builder = file_get_contents($repositoryRoot.'/tools/build-m7-5-preview-release.sh');
    $assert(is_string($builder) && $builder !== '', 'BUILD-001 release builder readable');
    $assert(str_contains($builder, "oneqay-preview/shared/runtime"), 'BUILD-002 stable private shared runtime path');
    $assert(str_contains($builder, 'useEnvironmentPath'), 'BUILD-003 public bootstrap selects shared environment path');
    $assert(str_contains($builder, 'useEnvironmentFile'), 'BUILD-004 public bootstrap selects environment file');
    $assert(str_contains($builder, 'PRIVATE_SHARED_DOTENV_V1'), 'BUILD-005 release metadata advertises shared environment profile');
    $assert(str_contains($builder, 'Tracked or generated .env is forbidden in the release input.'), 'BUILD-006 release payload still forbids embedded env');
    $assert(str_contains($builder, 'bootstrap/cache/config.php'), 'BUILD-007 cached configuration is rejected at build time');

    fwrite(STDOUT, "cPanel shared runtime environment binding regression passed.\n");
} finally {
    $removeTree($root);
}
