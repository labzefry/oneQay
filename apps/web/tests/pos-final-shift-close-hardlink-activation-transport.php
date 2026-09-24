<?php

declare(strict_types=1);

// Author by Lab | zefry

$repositoryRoot = dirname(__DIR__, 3);
require_once $repositoryRoot.'/tools/cpanel/execute-final-shift-close-feature-activation-readiness-successor.php';

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException($message);
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

$sourceCommit = str_repeat('a', 40);
$identity = ['source_commit' => $sourceCommit];
$original = "APP_NAME=oneQay\nONEQAY_RUNTIME_CLASS=durable-staging\nONEQAY_POS_SHIFT_CLOSE_ENABLED=\"false\"\n";
$active = fscActivationWriteFlagValue($original, true);
$releaseId = 'durable-staging-'.substr($sourceCommit, 0, 12);

$fixture = static function (string $suffix) use ($releaseId, $original): array {
    $root = sys_get_temp_dir().'/oneqay-fsc249-'.$suffix.'-'.bin2hex(random_bytes(6));
    $shared = $root.'/shared';
    $releaseApp = $root.'/releases/'.$releaseId.'/apps/web';
    if (! mkdir($shared, 0700, true) || ! mkdir($releaseApp, 0700, true)) {
        throw new RuntimeException('fixture_directory_create_failed');
    }
    $runtime = $shared.'/runtime.env';
    $bound = $releaseApp.'/.env';
    if (file_put_contents($runtime, $original, LOCK_EX) !== strlen($original)) {
        throw new RuntimeException('fixture_runtime_write_failed');
    }
    chmod($runtime, 0600);
    return [$root, $runtime, $bound];
};

$roots = [];
try {
    // Intact deployment hardlink: mutation and rollback must preserve the inode.
    [$root, $runtime, $bound] = $fixture('intact');
    $roots[] = $root;
    $assert(link($runtime, $bound), 'fixture_hardlink_create_failed');
    chmod($bound, 0600);
    $beforeStat = stat($runtime);
    $boundBeforeStat = stat($bound);
    $assert(is_array($beforeStat) && is_array($boundBeforeStat), 'fixture_hardlink_stat_failed');
    $assert(($beforeStat['ino'] ?? null) === ($boundBeforeStat['ino'] ?? null), 'fixture_hardlink_inode_invalid');
    $assert(($beforeStat['nlink'] ?? null) === 2, 'fixture_hardlink_count_invalid');

    $mode = fscActivationSuccessorEnsureRuntimeEnvBinding($runtime, $identity, $original);
    $assert($mode === 'HARDLINK', 'intact_hardlink_not_recognized');

    fscActivationSuccessorWriteRuntimeEnvBound($runtime, $active);
    $runtimeActive = file_get_contents($runtime);
    $boundActive = file_get_contents($bound);
    $activeStat = stat($runtime);
    $boundActiveStat = stat($bound);
    $assert($runtimeActive === $active && $boundActive === $active, 'hardlink_activation_not_visible_through_binding');
    $assert(is_array($activeStat) && is_array($boundActiveStat), 'hardlink_active_stat_failed');
    $assert(($activeStat['ino'] ?? null) === ($beforeStat['ino'] ?? null), 'hardlink_runtime_inode_replaced');
    $assert(($boundActiveStat['ino'] ?? null) === ($beforeStat['ino'] ?? null), 'hardlink_bound_inode_replaced');
    $assert(($activeStat['nlink'] ?? null) === 2, 'hardlink_count_not_preserved');

    fscActivationSuccessorWriteRuntimeEnvBound($runtime, $original);
    $assert(file_get_contents($runtime) === $original, 'hardlink_rollback_runtime_bytes_mismatch');
    $assert(file_get_contents($bound) === $original, 'hardlink_rollback_bound_bytes_mismatch');

    // Legacy atomic replacement can sever the hardlink while leaving equal bytes.
    // The bounded preflight must repair only this exact same-filesystem shape.
    [$repairRoot, $repairRuntime, $repairBound] = $fixture('repair');
    $roots[] = $repairRoot;
    $assert(link($repairRuntime, $repairBound), 'repair_fixture_hardlink_create_failed');
    chmod($repairBound, 0600);
    $replacement = dirname($repairRuntime).'/.replacement';
    $assert(file_put_contents($replacement, $original, LOCK_EX) === strlen($original), 'repair_fixture_replacement_write_failed');
    chmod($replacement, 0600);
    $assert(rename($replacement, $repairRuntime), 'repair_fixture_atomic_replace_failed');

    $severedRuntimeStat = stat($repairRuntime);
    $severedBoundStat = stat($repairBound);
    $assert(is_array($severedRuntimeStat) && is_array($severedBoundStat), 'repair_fixture_severed_stat_failed');
    $assert(($severedRuntimeStat['ino'] ?? null) !== ($severedBoundStat['ino'] ?? null), 'repair_fixture_not_severed');
    $assert(($severedRuntimeStat['nlink'] ?? null) === 1 && ($severedBoundStat['nlink'] ?? null) === 1, 'repair_fixture_link_count_invalid');

    $repairMode = fscActivationSuccessorEnsureRuntimeEnvBinding($repairRuntime, $identity, $original);
    $assert($repairMode === 'HARDLINK_REPAIRED', 'legacy_hardlink_not_repaired');
    $repairedRuntimeStat = stat($repairRuntime);
    $repairedBoundStat = stat($repairBound);
    $assert(is_array($repairedRuntimeStat) && is_array($repairedBoundStat), 'repaired_hardlink_stat_failed');
    $assert(($repairedRuntimeStat['ino'] ?? null) === ($repairedBoundStat['ino'] ?? null), 'repaired_hardlink_inode_mismatch');
    $assert(($repairedRuntimeStat['nlink'] ?? null) === 2, 'repaired_hardlink_count_invalid');

    fscActivationSuccessorWriteRuntimeEnvBound($repairRuntime, $active);
    $assert(file_get_contents($repairBound) === $active, 'repaired_hardlink_activation_not_visible');
    fscActivationSuccessorWriteRuntimeEnvBound($repairRuntime, $original);
    $assert(file_get_contents($repairBound) === $original, 'repaired_hardlink_rollback_not_visible');

    // A severed alias with divergent bytes must never be repaired automatically.
    [$divergentRoot, $divergentRuntime, $divergentBound] = $fixture('divergent');
    $roots[] = $divergentRoot;
    $assert(link($divergentRuntime, $divergentBound), 'divergent_fixture_hardlink_create_failed');
    chmod($divergentBound, 0600);
    $replacement = dirname($divergentRuntime).'/.replacement';
    $assert(file_put_contents($replacement, $original, LOCK_EX) === strlen($original), 'divergent_fixture_replacement_write_failed');
    chmod($replacement, 0600);
    $assert(rename($replacement, $divergentRuntime), 'divergent_fixture_atomic_replace_failed');
    $divergent = str_replace('APP_NAME=oneQay', 'APP_NAME=drifted', $original);
    $assert(file_put_contents($divergentBound, $divergent, LOCK_EX) === strlen($divergent), 'divergent_fixture_bound_write_failed');
    chmod($divergentBound, 0600);

    $denied = false;
    try {
        fscActivationSuccessorEnsureRuntimeEnvBinding($divergentRuntime, $identity, $original);
    } catch (FinalShiftCloseActivationTransportException $exception) {
        $denied = $exception->getMessage() === 'runtime_env_release_binding_content_mismatch';
    }
    $assert($denied, 'divergent_hardlink_repair_did_not_fail_closed');

    // Symlink-bound deployment remains on the atomic-replace path.
    if (function_exists('symlink')) {
        [$symlinkRoot, $symlinkRuntime, $symlinkBound] = $fixture('symlink');
        $roots[] = $symlinkRoot;
        $assert(symlink($symlinkRuntime, $symlinkBound), 'fixture_symlink_create_failed');
        $symlinkMode = fscActivationSuccessorEnsureRuntimeEnvBinding($symlinkRuntime, $identity, $original);
        $assert($symlinkMode === 'SYMLINK', 'symlink_binding_not_recognized');
        fscActivationSuccessorWriteRuntimeEnvBound($symlinkRuntime, $active);
        $assert(is_link($symlinkBound), 'symlink_binding_replaced');
        $assert(file_get_contents($symlinkBound) === $active, 'symlink_activation_not_visible');
        fscActivationSuccessorWriteRuntimeEnvBound($symlinkRuntime, $original);
        $assert(file_get_contents($symlinkBound) === $original, 'symlink_rollback_not_visible');
    }

    fwrite(STDOUT, "SPRINT249_FINAL_SHIFT_CLOSE_HARDLINK_ACTIVATION_TRANSPORT=PASS\n");
} finally {
    foreach ($roots as $root) {
        $removeTree($root);
    }
}
