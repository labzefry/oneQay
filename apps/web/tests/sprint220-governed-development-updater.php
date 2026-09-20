<?php

declare(strict_types=1);

use App\Infrastructure\SystemUpdate\Development\DevelopmentUpdaterViolation;
use App\Infrastructure\SystemUpdate\Development\GovernedDevelopmentUpdateProcessor;
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
        'oneqay.development_updater.github_token' => str_repeat('g', 32),
        'oneqay.development_updater.environment_id' => 'oneqay-staging-01',
        'oneqay.development_updater.release_root' => $temp.'/releases',
        'oneqay.development_updater.active_release_pointer' => $temp.'/active',
        'oneqay.development_updater.runtime_env_path' => $temp.'/runtime.env',
        'oneqay.development_updater.document_root' => $temp.'/public',
        'oneqay.development_updater.attestation_url' => 'https://staging.example.invalid/internal/oneqay/durable-runtime/readiness',
    ]);
    putenv('ONEQAY_PRODUCTION_DATA_ALLOWED=false');
    $_ENV['ONEQAY_PRODUCTION_DATA_ALLOWED'] = 'false';
    $_SERVER['ONEQAY_PRODUCTION_DATA_ALLOWED'] = 'false';

    $service = new GovernedDevelopmentUpdateRequest();

    $processorService = new GovernedDevelopmentUpdateProcessor($service);
    $discovery = $processorService->discover(
        static function (string $url, string $token): array {
            if ($token !== str_repeat('g', 32)) {
                throw new RuntimeException('unexpected GitHub token fixture');
            }
            if (str_contains($url, '/actions/workflows/durable-staging-release-publication.yml/runs?')) {
                return [
                    'workflow_runs' => [[
                        'id' => 123456,
                        'conclusion' => 'success',
                        'head_branch' => 'main',
                        'event' => 'push',
                        'head_sha' => str_repeat('c', 40),
                    ]],
                ];
            }
            if (str_contains($url, '/compare/'.str_repeat('a', 40).'...'.str_repeat('c', 40))) {
                return [
                    'status' => 'ahead',
                    'base_commit' => ['sha' => str_repeat('a', 40)],
                    'merge_base_commit' => ['sha' => str_repeat('a', 40)],
                ];
            }
            if (str_contains($url, '/actions/runs/123456/artifacts')) {
                return [
                    'artifacts' => [[
                        'id' => 654321,
                        'name' => 'oneqay-durable-staging-'.str_repeat('c', 12).'-operator-bundle',
                        'expired' => false,
                        'digest' => 'sha256:'.str_repeat('d', 64),
                    ]],
                ];
            }

            throw new RuntimeException('unexpected discovery URL: '.$url);
        },
    );
    $assert(($discovery['state'] ?? null) === 'AVAILABLE', 'DISC-001 forward candidate discovered');
    $assert(($discovery['release_id'] ?? null) === 'durable-staging-'.str_repeat('c', 12), 'DISC-002 exact release discovered');
    $assert(preg_match('/\A[0-9a-f]{64}\z/', (string) ($discovery['candidate_fingerprint'] ?? '')) === 1, 'DISC-003 signed fingerprint materialized');

    $candidate = $service->storeCandidate([
        'release_id' => 'durable-staging-'.str_repeat('c', 12),
        'source_commit' => str_repeat('c', 40),
        'current_source_commit' => str_repeat('a', 40),
        'github_run_id' => 123456,
        'github_artifact_id' => 654321,
        'github_outer_sha256' => str_repeat('d', 64),
    ], $now);
    $assert(($candidate['candidate_state'] ?? null) === 'AVAILABLE', 'CAND-001 update candidate available');
    $assert(($candidate['expires_at_unix'] - $candidate['discovered_at_unix']) === 3600, 'CAND-002 discovery TTL exact');
    $assert(preg_match('/\A[0-9a-f]{64}\z/', (string) ($candidate['candidate_fingerprint'] ?? '')) === 1, 'CAND-003 fingerprint exact');

    $candidateRaw = (string) file_get_contents($temp.'/candidate.json');
    foreach ([$operatorToken, $secret, $totp($secret, $now)] as $forbidden) {
        $assert(! str_contains($candidateRaw, $forbidden), 'CAND-004 candidate contains no operator secret material');
    }

    try {
        $service->create($operatorToken, $totp($secret, $now), str_repeat('f', 64), $now);
        $assert(false, 'CAND-NEG-001 wrong candidate fingerprint must fail');
    } catch (DevelopmentUpdaterViolation $expected) {
        $assert($expected->safeCode() === 'candidate_fingerprint_mismatch', 'CAND-NEG-001 exact candidate binding');
    }

    $created = $service->create(
        $operatorToken,
        $totp($secret, $now),
        $candidate['candidate_fingerprint'],
        $now,
    );
    $assert(($created['state'] ?? null) === 'PENDING', 'REQ-001 request accepted');
    $assert(($created['production_allowed'] ?? true) === false, 'REQ-002 production denied');
    $assert(($created['migration_execution_allowed'] ?? true) === false, 'REQ-003 migration denied');
    $assert(preg_match('/\Adurable-staging-deployment-authority-[0-9a-f]{24}\z/', (string) ($created['deployment_authority_id'] ?? '')) === 1, 'REQ-004 authority id materialized');
    $assert(preg_match('/\A[0-9a-f]{64}\z/', (string) ($created['deployment_authority_sha256'] ?? '')) === 1, 'REQ-005 authority hash materialized');
    $assert(preg_match('/\A[0-9a-f]{64}\z/', (string) ($created['deployment_request_sha256'] ?? '')) === 1, 'REQ-006 request hash materialized');

    $authorityPath = $temp.'/requests/'.$created['request_id'].'.authority.json';
    $raw = (string) file_get_contents($temp.'/requests/pending.json');
    $authorityRaw = (string) file_get_contents($authorityPath);
    $assert(hash_file('sha256', $temp.'/requests/pending.json') === $created['deployment_request_sha256'], 'REQ-007 raw request hash exact');
    $assert(hash_file('sha256', $authorityPath) === $created['deployment_authority_sha256'], 'REQ-008 raw authority hash exact');
    $authorityPayload = json_decode($authorityRaw, true, 64, JSON_THROW_ON_ERROR);
    $assert(($authorityPayload['request_sha256'] ?? null) === $created['deployment_request_sha256'], 'REQ-009 authority binds raw request hash');
    $assert(($authorityPayload['candidate_fingerprint'] ?? null) === $candidate['candidate_fingerprint'], 'REQ-010 authority binds exact candidate');
    $assert(($authorityPayload['expires_at_unix'] - $authorityPayload['authorized_at_unix']) === 900, 'REQ-011 authority TTL exact');
    foreach ([$operatorToken, $secret, $totp($secret, $now)] as $forbidden) {
        $assert(! str_contains($raw, $forbidden), 'REQ-012 request contains no operator secret material');
        $assert(! str_contains($authorityRaw, $forbidden), 'REQ-013 authority contains no operator secret material');
    }

    $required = $service->requireCurrentPending($now + 1);
    $assert(($required['repository'] ?? null) === 'labzefry/oneQay', 'REQ-014 repository fixed');
    $assert(($required['workflow'] ?? null) === 'durable-staging-release-publication.yml', 'REQ-015 workflow fixed');
    $assert(($required['expires_at_unix'] - $required['requested_at_unix']) === 900, 'REQ-016 request TTL exact');
    $assert(($required['scope'] ?? null) === 'INSTALL_EXACT_GOVERNED_DURABLE_STAGING_RELEASE', 'REQ-017 exact scope');
    $assert(($required['candidate_fingerprint'] ?? null) === $candidate['candidate_fingerprint'], 'REQ-018 fingerprint bound');
    $assert(($required['candidate_source_commit'] ?? null) === str_repeat('c', 40), 'REQ-019 source bound');
    $assert(($required['github_artifact_id'] ?? null) === 654321, 'REQ-020 artifact bound');
    $assert(($required['deployment_authority_id'] ?? null) === $created['deployment_authority_id'], 'REQ-021 validated authority id enriched');
    $assert(($required['deployment_authority_sha256'] ?? null) === $created['deployment_authority_sha256'], 'REQ-022 validated authority hash enriched');
    $assert(($required['deployment_request_sha256'] ?? null) === $created['deployment_request_sha256'], 'REQ-023 validated request hash enriched');

    $tamperedAuthority = $authorityPayload;
    $tamperedAuthority['production_allowed'] = true;
    file_put_contents(
        $authorityPath,
        json_encode($tamperedAuthority, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL,
        LOCK_EX,
    );
    chmod($authorityPath, 0600);
    try {
        $service->requireCurrentPending($now + 1);
        $assert(false, 'REQ-NEG-002 authority tamper must fail');
    } catch (DevelopmentUpdaterViolation $expected) {
        $assert($expected->safeCode() === 'deployment_authority_binding_invalid', 'REQ-NEG-002 authority fail closed');
    }
    file_put_contents($authorityPath, $authorityRaw, LOCK_EX);
    chmod($authorityPath, 0600);

    try {
        $service->create('wrong-token', $totp($secret, $now), $candidate['candidate_fingerprint'], $now);
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
        $assert(false, 'REQ-NEG-003 tamper must fail');
    } catch (DevelopmentUpdaterViolation $expected) {
        $assert($expected->safeCode() === 'request_signature_invalid', 'REQ-NEG-003 signature fail closed');
    }

    $processor = (string) file_get_contents(__DIR__.'/../app/Infrastructure/SystemUpdate/Development/GovernedDevelopmentUpdateProcessor.php');
    foreach ([
        'https://api.github.com/repos/labzefry/oneQay/',
        'durable-staging-release-publication.yml',
        'candidate_not_forward_from_running_source',
        'authorized_candidate_drift',
        'trustedPublicationRunById',
        'candidate_fingerprint',
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

    $archiveGuard = strpos($processor, '$this->assertArchiveEntriesSafe($archive, $archivePath)');
    $extractCall = strpos($processor, '$archive->extractTo($destination, null, false)');
    $assert(is_int($archiveGuard) && is_int($extractCall) && $archiveGuard < $extractCall, 'PROC-003 archive guard precedes extraction');
    $assert(str_contains($processor, 'CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS'), 'PROC-004 HTTPS redirect policy required');
    $assert(! str_contains($processor, 'stream_context_create'), 'PROC-005 no stream fallback for privileged HTTPS');

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
