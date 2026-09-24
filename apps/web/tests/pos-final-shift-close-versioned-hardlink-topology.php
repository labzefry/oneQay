<?php

declare(strict_types=1);

// Author by Lab | zefry

$repositoryRoot = dirname(__DIR__, 3);
require_once $repositoryRoot.'/tools/cpanel/execute-final-shift-close-feature-activation-readiness-successor.php';

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint251 regression failed: '.$message);
    }
};

$removeTree = static function (string $path) use (&$removeTree): void {
    if (! file_exists($path) && ! is_link($path)) {
        return;
    }
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    $items = scandir($path);
    if (! is_array($items)) {
        return;
    }
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $removeTree($path.'/'.$item);
    }
    @rmdir($path);
};

$sourceCommit = str_repeat('b', 40);
$identity = ['source_commit' => $sourceCommit];
$original = "APP_NAME=oneQay\nONEQAY_RUNTIME_CLASS=durable-staging\nONEQAY_POS_SHIFT_CLOSE_ENABLED=\"false\"\n";
$active = fscActivationWriteFlagValue($original, true);
$releaseId = 'durable-staging-'.substr($sourceCommit, 0, 12);
$root = sys_get_temp_dir().'/oneqay-fsc251-'.bin2hex(random_bytes(6));
$shared = $root.'/shared';
$releaseApp = $root.'/releases/'.$releaseId.'/apps/web';
$runtime = $shared.'/runtime.env';
$versioned = $shared.'/runtime-'.substr($sourceCommit, 0, 12).'.env';
$release = $releaseApp.'/.env';

try {
    $assert(mkdir($shared, 0700, true), 'shared directory create failed');
    $assert(mkdir($releaseApp, 0700, true), 'release directory create failed');
    $assert(file_put_contents($runtime, $original, LOCK_EX) === strlen($original), 'runtime write failed');
    $assert(file_put_contents($versioned, $original, LOCK_EX) === strlen($original), 'versioned write failed');
    chmod($runtime, 0600);
    chmod($versioned, 0600);
    $assert(link($versioned, $release), 'release hardlink create failed');
    chmod($release, 0600);

    $runtimeBefore = stat($runtime);
    $versionedBefore = stat($versioned);
    $releaseBefore = stat($release);
    $assert(is_array($runtimeBefore) && is_array($versionedBefore) && is_array($releaseBefore), 'preflight stat failed');
    $assert(($runtimeBefore['nlink'] ?? null) === 1, 'runtime preflight link count invalid');
    $assert(($versionedBefore['nlink'] ?? null) === 2 && ($releaseBefore['nlink'] ?? null) === 2, 'versioned bridge link count invalid');
    $assert(($versionedBefore['ino'] ?? null) === ($releaseBefore['ino'] ?? null), 'versioned release inode mismatch');
    $assert(($runtimeBefore['ino'] ?? null) !== ($releaseBefore['ino'] ?? null), 'fixture topology not severed');

    $mode = fscActivationSuccessorEnsureRuntimeEnvBinding($runtime, $identity, $original);
    $assert($mode === 'VERSIONED_HARDLINK_REPAIRED', 'versioned bridge was not repaired');

    $runtimeAfter = stat($runtime);
    $versionedAfter = stat($versioned);
    $releaseAfter = stat($release);
    $assert(is_array($runtimeAfter) && is_array($versionedAfter) && is_array($releaseAfter), 'post-repair stat failed');
    $inode = $runtimeAfter['ino'] ?? null;
    $assert($inode === ($versionedAfter['ino'] ?? null) && $inode === ($releaseAfter['ino'] ?? null), 'three-path inode repair mismatch');
    $assert(($runtimeAfter['nlink'] ?? null) === 3 && ($versionedAfter['nlink'] ?? null) === 3 && ($releaseAfter['nlink'] ?? null) === 3, 'three-path link count invalid');

    $idempotent = fscActivationSuccessorEnsureRuntimeEnvBinding($runtime, $identity, $original);
    $assert($idempotent === 'VERSIONED_HARDLINK', 'repaired topology is not idempotently recognized');

    fscActivationSuccessorWriteRuntimeEnvBound($runtime, $active);
    $assert(file_get_contents($runtime) === $active, 'runtime activation bytes mismatch');
    $assert(file_get_contents($versioned) === $active, 'versioned activation bytes mismatch');
    $assert(file_get_contents($release) === $active, 'release activation bytes mismatch');

    fscActivationSuccessorWriteRuntimeEnvBound($runtime, $original);
    $assert(file_get_contents($runtime) === $original, 'runtime rollback bytes mismatch');
    $assert(file_get_contents($versioned) === $original, 'versioned rollback bytes mismatch');
    $assert(file_get_contents($release) === $original, 'release rollback bytes mismatch');

    $state = json_decode((string) file_get_contents($repositoryRoot.'/ops/final-shift-close/STATE.json'), true, 64, JSON_THROW_ON_ERROR);
    $assert(($state['migration27']['state'] ?? null) === 'EXECUTED', 'migration #27 state drifted');
    $assert(($state['permission_provisioning']['state'] ?? null) === 'PROVISIONED', 'permission provisioning state drifted');
    $assert(($state['permission_provisioning']['default_grant'] ?? null) === 'NONE', 'permission default grant drifted');
    $assert(($state['feature_activation']['state'] ?? null) === 'INACTIVE', 'engineering correction activated Final Shift Close');
    $assert(($state['deployment_authority'] ?? null) === 'NOT_GRANTED', 'deployment authority drifted');
    $assert(($state['technical_preview_activation'] ?? null) === 'NOT_AUTHORIZED', 'Technical Preview authority drifted');
    $assert(($state['production_activation'] ?? null) === 'NOT_AUTHORIZED', 'Production authority drifted');
    $assert(($state['updater_activation'] ?? null) === 'INACTIVE', 'updater state drifted');

    fwrite(STDOUT, "SPRINT251_FINAL_SHIFT_CLOSE_VERSIONED_HARDLINK_TOPOLOGY=PASS\n");
} finally {
    $removeTree($root);
}
