from pathlib import Path
import re

path = Path('tools/cpanel/execute-final-shift-close-feature-activation-readiness-successor.php')
text = path.read_text()

replacement = r'''function fscActivationSuccessorEnsureRuntimeEnvBinding(string $runtimeEnvPath, array $identity, string $expectedRaw): string
{
    $runtimeEnvPath = fscActivationSafeAbsoluteFile($runtimeEnvPath, true, 'runtime_env');

    if (basename($runtimeEnvPath) !== 'runtime.env' || basename(dirname($runtimeEnvPath)) !== 'shared') {
        fscActivationFail('runtime_env_deployment_shape_invalid');
    }

    $sourceCommit = fscActivationHex(
        $identity['source_commit'] ?? null,
        40,
        'runtime_env_binding_source_invalid',
    );
    $sourcePrefix = substr($sourceCommit, 0, 12);
    $deploymentRoot = dirname(dirname($runtimeEnvPath));
    $releaseEnvPath = $deploymentRoot.'/releases/durable-staging-'.$sourcePrefix.'/apps/web/.env';
    $versionedEnvPath = dirname($runtimeEnvPath).'/runtime-'.$sourcePrefix.'.env';

    if (is_link($releaseEnvPath)) {
        $target = realpath($releaseEnvPath);
        if (!is_string($target) || $target !== $runtimeEnvPath) {
            fscActivationFail('runtime_env_symlink_binding_invalid');
        }
        return 'SYMLINK';
    }

    if (!is_file($releaseEnvPath) || !is_readable($releaseEnvPath)) {
        fscActivationFail('runtime_env_release_binding_unavailable');
    }
    $mode = fileperms($releaseEnvPath);
    if (!is_int($mode) || (($mode & 0077) !== 0)) {
        fscActivationFail('runtime_env_release_binding_permissions_invalid');
    }
    $releaseReal = realpath($releaseEnvPath);
    if (!is_string($releaseReal) || $releaseReal !== $releaseEnvPath) {
        fscActivationFail('runtime_env_release_binding_realpath_invalid');
    }

    $releaseRaw = file_get_contents($releaseEnvPath);
    if (!is_string($releaseRaw) || !hash_equals(hash('sha256', $expectedRaw), hash('sha256', $releaseRaw))) {
        fscActivationFail('runtime_env_release_binding_content_mismatch');
    }

    $runtimeStat = stat($runtimeEnvPath);
    $releaseStat = stat($releaseEnvPath);
    if (!is_array($runtimeStat) || !is_array($releaseStat)) {
        fscActivationFail('runtime_env_binding_stat_failed');
    }

    $sameInode = ($runtimeStat['dev'] ?? null) === ($releaseStat['dev'] ?? null)
        && ($runtimeStat['ino'] ?? null) === ($releaseStat['ino'] ?? null);
    if ($sameInode) {
        if (($runtimeStat['nlink'] ?? null) !== 2 || ($releaseStat['nlink'] ?? null) !== 2) {
            fscActivationFail('runtime_env_hardlink_count_invalid');
        }
        return 'HARDLINK';
    }

    // cPanel hosts with symlink disabled can retain the deployed release .env
    // on the exact source-versioned private snapshot while shared/runtime.env
    // has already been atomically replaced. Repair only this observed,
    // byte-identical, same-filesystem topology by rebinding the serving release
    // to canonical runtime.env while preserving the versioned snapshot intact.
    $versionedTopology = ($runtimeStat['nlink'] ?? null) === 1
        && ($releaseStat['nlink'] ?? null) === 2
        && ($runtimeStat['dev'] ?? null) === ($releaseStat['dev'] ?? null);

    if ($versionedTopology) {
        if (!is_file($versionedEnvPath) || is_link($versionedEnvPath) || !is_readable($versionedEnvPath)) {
            fscActivationFail('runtime_env_versioned_binding_unavailable');
        }
        $versionedMode = fileperms($versionedEnvPath);
        $versionedReal = realpath($versionedEnvPath);
        $versionedRaw = file_get_contents($versionedEnvPath);
        $versionedStat = stat($versionedEnvPath);
        if (!is_int($versionedMode) || (($versionedMode & 0077) !== 0)) {
            fscActivationFail('runtime_env_versioned_binding_permissions_invalid');
        }
        if (!is_string($versionedReal) || $versionedReal !== $versionedEnvPath) {
            fscActivationFail('runtime_env_versioned_binding_realpath_invalid');
        }
        if (!is_string($versionedRaw) || !hash_equals(hash('sha256', $expectedRaw), hash('sha256', $versionedRaw))) {
            fscActivationFail('runtime_env_versioned_binding_content_mismatch');
        }
        if (!is_array($versionedStat)
            || ($versionedStat['dev'] ?? null) !== ($releaseStat['dev'] ?? null)
            || ($versionedStat['ino'] ?? null) !== ($releaseStat['ino'] ?? null)
            || ($versionedStat['nlink'] ?? null) !== 2
            || !function_exists('link')
        ) {
            fscActivationFail('runtime_env_versioned_hardlink_repair_not_qualified');
        }

        $releaseDir = dirname($releaseEnvPath);
        if (!is_dir($releaseDir) || is_link($releaseDir) || !is_writable($releaseDir)) {
            fscActivationFail('runtime_env_versioned_hardlink_repair_directory_invalid');
        }

        $temp = $releaseDir.'/.fsc-runtime-env-versioned-rebind-'.bin2hex(random_bytes(12)).'.tmp';
        $rollbackTemp = $releaseDir.'/.fsc-runtime-env-versioned-rollback-'.bin2hex(random_bytes(12)).'.tmp';
        try {
            if (!@link($runtimeEnvPath, $temp)) {
                fscActivationFail('runtime_env_versioned_hardlink_repair_stage_failed');
            }
            $tempStat = stat($temp);
            if (!is_array($tempStat)
                || ($tempStat['dev'] ?? null) !== ($runtimeStat['dev'] ?? null)
                || ($tempStat['ino'] ?? null) !== ($runtimeStat['ino'] ?? null)
            ) {
                fscActivationFail('runtime_env_versioned_hardlink_repair_stage_verification_failed');
            }
            if (!@rename($temp, $releaseEnvPath)) {
                fscActivationFail('runtime_env_versioned_hardlink_repair_commit_failed');
            }

            clearstatcache(true, $runtimeEnvPath);
            clearstatcache(true, $releaseEnvPath);
            clearstatcache(true, $versionedEnvPath);
            $runtimeAfter = stat($runtimeEnvPath);
            $releaseAfter = stat($releaseEnvPath);
            $versionedAfter = stat($versionedEnvPath);
            $versionedAfterRaw = file_get_contents($versionedEnvPath);
            if (!is_array($runtimeAfter)
                || !is_array($releaseAfter)
                || !is_array($versionedAfter)
                || ($runtimeAfter['dev'] ?? null) !== ($releaseAfter['dev'] ?? null)
                || ($runtimeAfter['ino'] ?? null) !== ($releaseAfter['ino'] ?? null)
                || ($runtimeAfter['nlink'] ?? null) !== 2
                || ($releaseAfter['nlink'] ?? null) !== 2
                || ($versionedAfter['nlink'] ?? null) !== 1
                || ($versionedAfter['ino'] ?? null) === ($runtimeAfter['ino'] ?? null)
                || !is_string($versionedAfterRaw)
                || !hash_equals(hash('sha256', $expectedRaw), hash('sha256', $versionedAfterRaw))
            ) {
                if (!@link($versionedEnvPath, $rollbackTemp) || !@rename($rollbackTemp, $releaseEnvPath)) {
                    fscActivationFail('runtime_env_versioned_hardlink_repair_rollback_failed');
                }
                fscActivationFail('runtime_env_versioned_hardlink_repair_verification_failed');
            }
        } finally {
            foreach ([$temp, $rollbackTemp] as $candidate) {
                if (is_file($candidate) || is_link($candidate)) {
                    @unlink($candidate);
                }
            }
        }

        return 'VERSIONED_HARDLINK_REPAIRED';
    }

    // The pre-Sprint249 atomic writer can sever a deployment-created two-path
    // hardlink while leaving both private files byte-identical.
    if (($runtimeStat['nlink'] ?? null) !== 1
        || ($releaseStat['nlink'] ?? null) !== 1
        || ($runtimeStat['dev'] ?? null) !== ($releaseStat['dev'] ?? null)
        || !function_exists('link')
    ) {
        fscActivationFail('runtime_env_hardlink_repair_not_qualified');
    }

    $releaseDir = dirname($releaseEnvPath);
    if (!is_dir($releaseDir) || is_link($releaseDir) || !is_writable($releaseDir)) {
        fscActivationFail('runtime_env_hardlink_repair_directory_invalid');
    }

    $temp = $releaseDir.'/.fsc-runtime-env-link-'.bin2hex(random_bytes(12)).'.tmp';
    try {
        if (!@link($runtimeEnvPath, $temp)) {
            fscActivationFail('runtime_env_hardlink_repair_stage_failed');
        }
        $tempStat = stat($temp);
        if (!is_array($tempStat)
            || ($tempStat['dev'] ?? null) !== ($runtimeStat['dev'] ?? null)
            || ($tempStat['ino'] ?? null) !== ($runtimeStat['ino'] ?? null)
        ) {
            fscActivationFail('runtime_env_hardlink_repair_stage_verification_failed');
        }
        if (!@rename($temp, $releaseEnvPath)) {
            fscActivationFail('runtime_env_hardlink_repair_commit_failed');
        }
    } finally {
        if (is_file($temp) || is_link($temp)) {
            @unlink($temp);
        }
    }

    clearstatcache(true, $runtimeEnvPath);
    clearstatcache(true, $releaseEnvPath);
    $runtimeAfter = stat($runtimeEnvPath);
    $releaseAfter = stat($releaseEnvPath);
    if (!is_array($runtimeAfter)
        || !is_array($releaseAfter)
        || ($runtimeAfter['dev'] ?? null) !== ($releaseAfter['dev'] ?? null)
        || ($runtimeAfter['ino'] ?? null) !== ($releaseAfter['ino'] ?? null)
        || ($runtimeAfter['nlink'] ?? null) !== 2
        || ($releaseAfter['nlink'] ?? null) !== 2
    ) {
        fscActivationFail('runtime_env_hardlink_repair_verification_failed');
    }

    return 'HARDLINK_REPAIRED';
}'''

pattern = re.compile(
    r'function fscActivationSuccessorEnsureRuntimeEnvBinding\(.*?\n\}\n\n(?=function fscActivationSuccessorWriteRuntimeEnvBound)',
    re.S,
)
text, count = pattern.subn(replacement + '\n\n', text, count=1)
if count != 1:
    raise SystemExit('Sprint251 ensure-binding function boundary mismatch')

path.write_text(text)
