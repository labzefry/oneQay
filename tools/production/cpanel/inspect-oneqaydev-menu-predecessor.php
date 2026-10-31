<?php
// Author by Lab | zefry. Read-only oneQayDev predecessor fingerprint; no secrets or database access.
declare(strict_types=1);
const ROOT = '/home/pekd7254/oneqay-production-test';
const WEB = '/home/pekd7254/public_html/oneqaydev.n07.my.id';
const SOURCE = '95d9286f4698b168f7c419b67ac3c6583f844910';
const RELEASE = 'production-95d9286f4698';
function need(bool $ok, string $code): void { if (!$ok) { throw new RuntimeException($code); } }
function fingerprint(string $path): string {
    need(is_file($path) && !is_link($path) && is_readable($path), 'EXPECTED_FILE_NOT_FOUND');
    $sha = hash_file('sha256', $path);
    need(is_string($sha) && preg_match('/\A[a-f0-9]{64}\z/D', $sha) === 1, 'FINGERPRINT_FAILED');
    return $sha;
}
try {
    need(PHP_SAPI === 'cli', 'CLI_ONLY');
    need(realpath(ROOT) === ROOT && realpath(WEB) === WEB, 'CANONICAL_HOST_DIRECTORY_REQUIRED');
    $app = ROOT.'/releases/'.RELEASE.'/apps/web';
    need(realpath($app) === $app, 'PINNED_PREDECESSOR_RELEASE_NOT_FOUND');
    $shared = ROOT.'/shared/production-business-runtime-95d9286f4698-pos.env';
    $env = fingerprint($shared);
    need(hash_equals($env, fingerprint($app.'/.env')), 'PREDECESSOR_ENV_BINDING_CHANGED');
    $content = file_get_contents($shared);
    need(is_string($content) && strlen($content) < 200000, 'ENV_READ_DENIED');
    $sourcePattern = '/^ONEQAY_RUNNING_SOURCE_COMMIT=([a-f0-9]{40})\r?$/m';
    $artifactPattern = '/^ONEQAY_RUNNING_ARTIFACT_SHA256=([a-f0-9]{64})\r?$/m';
    need(preg_match_all($sourcePattern, $content, $sm) === 1 && ($sm[1][0] ?? null) === SOURCE, 'PREDECESSOR_SOURCE_CHANGED');
    need(preg_match_all($artifactPattern, $content, $am) === 1, 'PREDECESSOR_ARTIFACT_CHANGED');
    $inventoryPath = ROOT.'/oneqaydev-workspace-successor-95d9286f4698/release-files.sha256';
    $inventory = fingerprint($inventoryPath);
    $lines = file($inventoryPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    need(is_array($lines) && count($lines) >= 6300 && count($lines) <= 7500, 'PREDECESSOR_FILE_COUNT_INVALID');
    $entry = WEB.'/oneqay-business-95d9286f4698-pos.php';
    $binding = [
        'source' => SOURCE,
        'file_count' => count($lines),
        'artifact' => $am[1][0],
        'env' => $env,
        'entry' => fingerprint($entry),
        'routing' => fingerprint(WEB.'/.htaccess'),
        'inventory' => $inventory,
        'index' => fingerprint(WEB.'/index.php'),
    ];
    $out = ROOT.'/oneqaydev-menu-predecessor-binding.json';
    need(!is_link($out) && !is_dir($out), 'PRIVATE_OUTPUT_PATH_INVALID');
    $tmp = $out.'.tmp-'.bin2hex(random_bytes(8));
    $mask = umask(0077);
    try { $written = file_put_contents($tmp, json_encode($binding, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n", LOCK_EX); }
    finally { umask($mask); }
    need(is_int($written) && $written > 0 && chmod($tmp, 0600) && rename($tmp, $out), 'PRIVATE_BINDING_WRITE_FAILED');
    fwrite(STDOUT, "ONEQAYDEV_PREDECESSOR_BINDING_READY\nPRIVATE_PATH=".$out."\nFILE_COUNT=".$binding['file_count']."\nBINDING_SHA256=".hash_file('sha256', $out)."\n");
} catch (Throwable $e) {
    $code = $e instanceof RuntimeException ? $e->getMessage() : 'READ_ONLY_PREFLIGHT_ERROR';
    fwrite(STDERR, "ONEQAY|".$code."\n");
    exit(1);
}
