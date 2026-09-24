<?php

declare(strict_types=1);

// Author by Lab | zefry

final class FinalShiftCloseRuntimeBindingRepairException extends RuntimeException
{
}

function fscBindingRepairFail(string $code): never
{
    throw new FinalShiftCloseRuntimeBindingRepairException($code);
}

function fscBindingRepairPrivateFile(string $path, string $code): string
{
    if ($path === '' || !str_starts_with($path, '/') || str_contains($path, "\0") || !is_file($path) || is_link($path) || !is_readable($path)) {
        fscBindingRepairFail($code.'_unavailable');
    }
    $mode = fileperms($path);
    if (!is_int($mode) || (($mode & 0077) !== 0)) {
        fscBindingRepairFail($code.'_permissions_invalid');
    }
    $real = realpath($path);
    if (!is_string($real) || $real !== $path) {
        fscBindingRepairFail($code.'_realpath_invalid');
    }
    return $real;
}

function fscBindingRepairRead(string $path, string $code): string
{
    $path = fscBindingRepairPrivateFile($path, $code);
    $size = filesize($path);
    if (!is_int($size) || $size < 2 || $size > 131072) {
        fscBindingRepairFail($code.'_size_invalid');
    }
    $raw = file_get_contents($path);
    if (!is_string($raw)) {
        fscBindingRepairFail($code.'_read_failed');
    }
    return $raw;
}

function fscBindingRepairEnvValue(string $raw, string $key): string
{
    $quoted = preg_quote($key, '/');
    if (preg_match('/^'.$quoted.'\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\r\n#]*))/m', $raw, $match) !== 1) {
        fscBindingRepairFail('runtime_env_key_missing:'.$key);
    }
    foreach ([1, 2, 3] as $index) {
        if (isset($match[$index]) && $match[$index] !== '') {
            return trim((string) $match[$index]);
        }
    }
    return '';
}

function fscBindingRepairWriteEvidence(string $path, array $evidence): void
{
    if ($path === '' || !str_starts_with($path, '/') || str_contains($path, "\0") || file_exists($path) || is_link($path)) {
        fscBindingRepairFail('evidence_path_invalid');
    }
    $dir = dirname($path);
    if (!is_dir($dir) || is_link($dir) || !is_writable($dir)) {
        fscBindingRepairFail('evidence_directory_invalid');
    }
    $json = json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
    $temp = $dir.'/.fsc-binding-repair-evidence-'.bin2hex(random_bytes(12)).'.tmp';
    try {
        $handle = @fopen($temp, 'x+b');
        if (!is_resource($handle)) {
            fscBindingRepairFail('evidence_temp_create_failed');
        }
        @chmod($temp, 0600);
        $written = 0;
        while ($written < strlen($json)) {
            $count = fwrite($handle, substr($json, $written));
            if ($count === false || $count === 0) {
                fclose($handle);
                fscBindingRepairFail('evidence_write_failed');
            }
            $written += $count;
        }
        if (!fflush($handle)) {
            fclose($handle);
            fscBindingRepairFail('evidence_flush_failed');
        }
        if (function_exists('fsync')) {
            @fsync($handle);
        }
        fclose($handle);
        if (!@rename($temp, $path)) {
            fscBindingRepairFail('evidence_commit_failed');
        }
        @chmod($path, 0600);
    } finally {
        if (is_file($temp) || is_link($temp)) {
            @unlink($temp);
        }
    }
}

/** @return array<string,mixed> */
function fscRepairVersionedRuntimeEnvBinding(string $runtimeEnvPath, string $expectedSourceCommit, string $evidencePath): array
{
    if (preg_match('/\A[0-9a-f]{40}\z/D', $expectedSourceCommit) !== 1) {
        fscBindingRepairFail('source_commit_invalid');
    }

    $runtimeEnvPath = fscBindingRepairPrivateFile($runtimeEnvPath, 'runtime_env');
    if (basename($runtimeEnvPath) !== 'runtime.env' || basename(dirname($runtimeEnvPath)) !== 'shared') {
        fscBindingRepairFail('runtime_env_shape_invalid');
    }

    $sourcePrefix = substr($expectedSourceCommit, 0, 12);
    $deploymentRoot = dirname(dirname($runtimeEnvPath));
    $releaseEnvPath = $deploymentRoot.'/releases/durable-staging-'.$sourcePrefix.'/apps/web/.env';
    $versionedEnvPath = dirname($runtimeEnvPath).'/runtime-'.$sourcePrefix.'.env';

    $runtimeRaw = fscBindingRepairRead($runtimeEnvPath, 'runtime_env');
    $releaseRaw = fscBindingRepairRead($releaseEnvPath, 'release_env');
    $versionedRaw = fscBindingRepairRead($versionedEnvPath, 'versioned_env');

    if (fscBindingRepairEnvValue($runtimeRaw, 'ONEQAY_RUNNING_SOURCE_COMMIT') !== $expectedSourceCommit) {
        fscBindingRepairFail('runtime_env_source_mismatch');
    }
    foreach ([$runtimeRaw, $releaseRaw, $versionedRaw] as $raw) {
        if (strtolower(fscBindingRepairEnvValue($raw, 'ONEQAY_POS_SHIFT_CLOSE_ENABLED')) !== 'false') {
            fscBindingRepairFail('feature_flag_must_be_false');
        }
    }

    $expectedHash = hash('sha256', $runtimeRaw);
    if (!hash_equals($expectedHash, hash('sha256', $releaseRaw)) || !hash_equals($expectedHash, hash('sha256', $versionedRaw))) {
        fscBindingRepairFail('runtime_env_content_mismatch');
    }

    $runtimeStat = stat($runtimeEnvPath);
    $releaseStat = stat($releaseEnvPath);
    $versionedStat = stat($versionedEnvPath);
    if (!is_array($runtimeStat) || !is_array($releaseStat) || !is_array($versionedStat)) {
        fscBindingRepairFail('binding_stat_failed');
    }

    $before = [
        'runtime_inode' => $runtimeStat['ino'] ?? null,
        'runtime_nlink' => $runtimeStat['nlink'] ?? null,
        'release_inode' => $releaseStat['ino'] ?? null,
        'release_nlink' => $releaseStat['nlink'] ?? null,
        'versioned_inode' => $versionedStat['ino'] ?? null,
        'versioned_nlink' => $versionedStat['nlink'] ?? null,
    ];

    $alreadyRepaired = ($runtimeStat['dev'] ?? null) === ($releaseStat['dev'] ?? null)
        && ($runtimeStat['ino'] ?? null) === ($releaseStat['ino'] ?? null)
        && ($runtimeStat['nlink'] ?? null) === 2
        && ($releaseStat['nlink'] ?? null) === 2
        && ($versionedStat['nlink'] ?? null) === 1
        && ($versionedStat['ino'] ?? null) !== ($runtimeStat['ino'] ?? null);

    $state = 'ALREADY_REPAIRED';
    if (!$alreadyRepaired) {
        $qualified = ($runtimeStat['nlink'] ?? null) === 1
            && ($releaseStat['nlink'] ?? null) === 2
            && ($versionedStat['nlink'] ?? null) === 2
            && ($runtimeStat['dev'] ?? null) === ($releaseStat['dev'] ?? null)
            && ($releaseStat['dev'] ?? null) === ($versionedStat['dev'] ?? null)
            && ($releaseStat['ino'] ?? null) === ($versionedStat['ino'] ?? null)
            && ($runtimeStat['ino'] ?? null) !== ($releaseStat['ino'] ?? null)
            && function_exists('link');
        if (!$qualified) {
            fscBindingRepairFail('versioned_hardlink_topology_not_qualified');
        }

        $releaseDir = dirname($releaseEnvPath);
        if (!is_dir($releaseDir) || is_link($releaseDir) || !is_writable($releaseDir)) {
            fscBindingRepairFail('release_directory_invalid');
        }

        $temp = $releaseDir.'/.fsc-versioned-rebind-'.bin2hex(random_bytes(12)).'.tmp';
        $rollback = $releaseDir.'/.fsc-versioned-rollback-'.bin2hex(random_bytes(12)).'.tmp';
        try {
            if (!@link($runtimeEnvPath, $temp)) {
                fscBindingRepairFail('rebind_stage_failed');
            }
            $tempStat = stat($temp);
            if (!is_array($tempStat)
                || ($tempStat['dev'] ?? null) !== ($runtimeStat['dev'] ?? null)
                || ($tempStat['ino'] ?? null) !== ($runtimeStat['ino'] ?? null)
            ) {
                fscBindingRepairFail('rebind_stage_verification_failed');
            }
            if (!@rename($temp, $releaseEnvPath)) {
                fscBindingRepairFail('rebind_commit_failed');
            }

            clearstatcache(true, $runtimeEnvPath);
            clearstatcache(true, $releaseEnvPath);
            clearstatcache(true, $versionedEnvPath);
            $runtimeAfter = stat($runtimeEnvPath);
            $releaseAfter = stat($releaseEnvPath);
            $versionedAfter = stat($versionedEnvPath);
            if (!is_array($runtimeAfter)
                || !is_array($releaseAfter)
                || !is_array($versionedAfter)
                || ($runtimeAfter['dev'] ?? null) !== ($releaseAfter['dev'] ?? null)
                || ($runtimeAfter['ino'] ?? null) !== ($releaseAfter['ino'] ?? null)
                || ($runtimeAfter['nlink'] ?? null) !== 2
                || ($releaseAfter['nlink'] ?? null) !== 2
                || ($versionedAfter['nlink'] ?? null) !== 1
                || ($versionedAfter['ino'] ?? null) === ($runtimeAfter['ino'] ?? null)
            ) {
                if (!@link($versionedEnvPath, $rollback) || !@rename($rollback, $releaseEnvPath)) {
                    fscBindingRepairFail('rebind_rollback_failed');
                }
                fscBindingRepairFail('rebind_verification_failed');
            }
            $state = 'REPAIRED';
        } finally {
            foreach ([$temp, $rollback] as $candidate) {
                if (is_file($candidate) || is_link($candidate)) {
                    @unlink($candidate);
                }
            }
        }
    }

    $runtimeAfterRaw = fscBindingRepairRead($runtimeEnvPath, 'runtime_env');
    $releaseAfterRaw = fscBindingRepairRead($releaseEnvPath, 'release_env');
    $versionedAfterRaw = fscBindingRepairRead($versionedEnvPath, 'versioned_env');
    foreach ([$runtimeAfterRaw, $releaseAfterRaw, $versionedAfterRaw] as $raw) {
        if (!hash_equals($expectedHash, hash('sha256', $raw))) {
            fscBindingRepairFail('post_repair_content_drift');
        }
        if (strtolower(fscBindingRepairEnvValue($raw, 'ONEQAY_POS_SHIFT_CLOSE_ENABLED')) !== 'false') {
            fscBindingRepairFail('post_repair_feature_flag_drift');
        }
    }

    $runtimeAfterStat = stat($runtimeEnvPath);
    $releaseAfterStat = stat($releaseEnvPath);
    $versionedAfterStat = stat($versionedEnvPath);
    if (!is_array($runtimeAfterStat) || !is_array($releaseAfterStat) || !is_array($versionedAfterStat)) {
        fscBindingRepairFail('post_repair_stat_failed');
    }

    $evidence = [
        'schema_version' => 1,
        'operation' => 'FINAL_SHIFT_CLOSE_VERSIONED_RUNTIME_ENV_BINDING_REPAIR',
        'state' => $state,
        'source_commit' => $expectedSourceCommit,
        'source_prefix' => $sourcePrefix,
        'runtime_env_path' => $runtimeEnvPath,
        'release_env_path' => $releaseEnvPath,
        'versioned_env_path' => $versionedEnvPath,
        'runtime_env_sha256' => $expectedHash,
        'feature_flag_before' => false,
        'feature_flag_after' => false,
        'before_topology' => $before,
        'after_topology' => [
            'runtime_inode' => $runtimeAfterStat['ino'] ?? null,
            'runtime_nlink' => $runtimeAfterStat['nlink'] ?? null,
            'release_inode' => $releaseAfterStat['ino'] ?? null,
            'release_nlink' => $releaseAfterStat['nlink'] ?? null,
            'versioned_inode' => $versionedAfterStat['ino'] ?? null,
            'versioned_nlink' => $versionedAfterStat['nlink'] ?? null,
        ],
        'migration_execution_performed' => false,
        'permission_provisioning_performed' => false,
        'deployment_performed' => false,
        'technical_preview_activated' => false,
        'production_activated' => false,
        'updater_activated' => false,
        'runtime_allowlist_change' => 'NOT_PERFORMED',
        'attribution' => 'Lab | zefry',
    ];
    $evidence['evidence_sha256'] = hash('sha256', json_encode($evidence, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    fscBindingRepairWriteEvidence($evidencePath, $evidence);

    return $evidence;
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 4) {
        fwrite(STDERR, "Usage: php repair-final-shift-close-versioned-runtime-env-binding.php <runtime.env> <source-commit> <evidence.json>\n");
        exit(64);
    }
    try {
        $evidence = fscRepairVersionedRuntimeEnvBinding($argv[1], $argv[2], $argv[3]);
        fwrite(STDOUT, 'RESULT=SUCCESS'.PHP_EOL);
        fwrite(STDOUT, 'STATE='.(string) $evidence['state'].PHP_EOL);
        fwrite(STDOUT, 'EVIDENCE_SHA256='.(string) $evidence['evidence_sha256'].PHP_EOL);
        exit(0);
    } catch (Throwable $exception) {
        fwrite(STDERR, 'RESULT=FAILED'.PHP_EOL);
        fwrite(STDERR, 'ERROR_CODE='.$exception->getMessage().PHP_EOL);
        exit(1);
    }
}
