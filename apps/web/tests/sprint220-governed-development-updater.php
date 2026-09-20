<?php

declare(strict_types=1);

use App\Infrastructure\SystemUpdate\Development\DevelopmentUpdaterViolation;
use App\Infrastructure\SystemUpdate\Development\GovernedDevelopmentUpdateRequest;
use Illuminate\Contracts\Console\Kernel;

require_once __DIR__.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("Sprint220 regression failed: {$case}");
    }
};

$temp = sys_get_temp_dir().'/oneqay-sprint220-'.bin2hex(random_bytes(6));
mkdir($temp, 0700, true);

$remove = static function (string $path) use (&$remove): void {
    if (! file_exists($path) && ! is_link($path)) {
        return;
    }
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $remove($path.'/'.$entry);
    }
    @rmdir($path);
};

$totp = static function (string $secret, int $unix): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $normalized = rtrim(strtoupper($secret), '=');
    $buffer = 0;
    $bits = 0;
    $decoded = '';
    foreach (str_split($normalized) as $character) {
        $value = strpos($alphabet, $character);
        if ($value === false) {
            throw new RuntimeException('bad fixture secret');
        }
        $buffer = ($buffer << 5) | $value;
        $bits += 5;
        while ($bits >= 8) {
            $bits -= 8;
            $decoded .= chr(($buffer >> $bits) & 0xff);
            $buffer &= (1 << $bits) - 1;
        }
    }
    $counter = intdiv($unix, 30);
    $digest = hash_hmac('sha1', pack('N2', intdiv($counter, 4294967296), $counter % 4294967296), $decoded, true);
    $offset = ord($digest[19]) & 0x0f;
    $binary = ((ord($digest[$offset]) & 0x7f) << 24)
        | ((ord($digest[$offset + 1]) & 0xff) << 16)
        | ((ord($digest[$offset + 2]) & 0xff) << 8)
        | (ord($digest[$offset + 3]) & 0xff);

    return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
};

try {
    $now = 1_800_000_000;
    $operatorToken = 'sprint220-private-operator-token-0123456789';
    $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    config([
        'app.env' => 'staging',
        'oneqay.runtime_class' => 'durable-staging',
        'oneqay.development_updater.enabled' => true,
        'oneqay.development_updater.private_root' => $temp,
        'oneqay.development_updater.operator_token_sha256' => hash('sha256', $operatorToken),
        'oneqay.development_updater.totp_secret' => $secret,
        'oneqay.development_updater.request_hmac_key' => str_repeat('h', 64),
        'oneqay.development_updater.running_source_commit' => str_repeat('a', 40),
        'oneqay.development_updater.running_artifact_sha256' => str_repeat('b', 64),
    ]);
    putenv('ONEQAY_PRODUCTION_DATA_ALLOWED=false');
    $_ENV['ONEQAY_PRODUCTION_DATA_ALLOWED'] = 'false';
    $_SERVER['ONEQAY_PRODUCTION_DATA_ALLOWED'] = 'false';

    $service = new GovernedDevelopmentUpdateRequest();

    $created = $service->create($operatorToken, $totp($secret, $now), $now);
    $assert(($created['state'] ?? null) === 'PENDING', 'REQ-001 request accepted');
    $assert(($created['production_allowed'] ?? true) === false, 'REQ-002 production denied');
    $assert(($created['migration_execution_allowed'] ?? true) === false, 'REQ-003 migration denied');

    $raw = (string) file_get_contents($temp.'/requests/pending.json');
    foreach ([$operatorToken, $secret, $totp($secret, $now)] as $forbidden) {
        $assert(! str_contains($raw, $forbidden), 'REQ-004 request contains no operator secret material');
    }

    $required = $service->requireCurrentPending($now + 1);
    $assert(($required['repository'] ?? null) === 'labzefry/oneQay', 'REQ-005 repository fixed');
    $assert(($required['workflow'] ?? null) === 'durable-staging-release-publication.yml', 'REQ-006 workflow fixed');
    $assert(($required['expires_at_unix'] - $required['requested_at_unix']) === 900, 'REQ-007 request TTL exact');

    try {
        $service->create('wrong-token', $totp($secret, $now), $now);
        $assert(false, 'REQ-NEG-001 wrong token must fail');
    } catch (DevelopmentUpdaterViolation $expected) {
        $assert($expected->safeCode() === 'operator_authorization_denied', 'REQ-NEG-001 safe code');
    }

    $tampered = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    $tampered['production_allowed'] = true;
    file_put_contents(
        $temp.'/requests/pending.json',
        json_encode($tampered, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL,
        LOCK_EX,
    );
    chmod($temp.'/requests/pending.json', 0600);

    try {
        $service->requireCurrentPending($now + 1);
        $assert(false, 'REQ-NEG-002 tamper must fail');
    } catch (DevelopmentUpdaterViolation $expected) {
        $assert($expected->safeCode() === 'request_signature_invalid', 'REQ-NEG-002 signature fail closed');
    }

    $processor = (string) file_get_contents(__DIR__.'/../app/Infrastructure/SystemUpdate/Development/GovernedDevelopmentUpdateProcessor.php');
    foreach ([
        'https://api.github.com/repos/labzefry/oneQay/',
        'durable-staging-release-publication.yml',
        'candidate_not_forward_from_running_source',
        'migration_execution_allowed',
        'rollback_path_verified',
        'DEPLOYED_VERIFIED_NOT_SELECTED',
        'NOT_AUTHORIZED',
    ] as $marker) {
        $assert(str_contains($processor, $marker), 'PROC-001 missing marker '.$marker);
    }
    foreach (['php artisan migrate', 'Artisan::call', 'shell_exec(', 'proc_open(', 'system('] as $forbidden) {
        $assert(! str_contains($processor, $forbidden), 'PROC-002 forbidden execution primitive '.$forbidden);
    }

    $attestation = (string) file_get_contents(__DIR__.'/../app/Providers/PosOperationsHubServiceProvider.php');
    foreach ([
        '/internal/oneqay/durable-runtime/readiness',
        'DurableStagingRuntimeReadinessAttestationController',
        'NON_SYNTHETIC_DURABLE_RUNTIME',
        'SEPARATE_EXPLICIT_ACTIVATION_AUTHORITY',
        "'secrets_embedded' => false",
    ] as $marker) {
        $assert(str_contains($attestation, $marker), 'ATT-001 missing canonical Sprint204 marker '.$marker);
    }

    fwrite(STDOUT, "Sprint220 governed development updater regression passed.\n");
} finally {
    $remove($temp);
}
