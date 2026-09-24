<?php

declare(strict_types=1);

// Author by Lab | zefry

require_once __DIR__.'/execute-final-shift-close-feature-activation.php';

/** @return array<string,mixed> */
function fscActivationMapCanonicalDurableStagingReadiness(array $readiness): array
{
    fscActivationLiteral($readiness['schema_version'] ?? null, 1, 'readiness_schema_invalid');
    fscActivationLiteral($readiness['runtime_model'] ?? null, 'NON_SYNTHETIC_DURABLE_RUNTIME', 'readiness_runtime_model_invalid');
    fscActivationLiteral($readiness['environment_isolation'] ?? null, 'ISOLATED_NON_PRODUCTION', 'readiness_environment_isolation_invalid');
    fscActivationBool($readiness['serving_application_runtime'] ?? null, true, 'readiness_serving_runtime_invalid');
    fscActivationBool($readiness['synthetic_fixture_runtime'] ?? null, false, 'readiness_synthetic_runtime_invalid');
    fscActivationBool($readiness['production_traffic_served'] ?? null, false, 'readiness_production_traffic_invalid');
    fscActivationBool($readiness['durable_persistence_enabled'] ?? null, true, 'readiness_persistence_invalid');
    fscActivationBool($readiness['durable_session_control_enabled'] ?? null, true, 'readiness_session_control_invalid');
    fscActivationBool($readiness['durable_authorization_enabled'] ?? null, true, 'readiness_authorization_invalid');
    fscActivationBool($readiness['durable_transaction_boundary_enabled'] ?? null, true, 'readiness_transaction_boundary_invalid');
    fscActivationBool($readiness['durable_pos_persistence_enabled'] ?? null, true, 'readiness_pos_persistence_invalid');
    fscActivationLiteral(
        $readiness['activation_authority_binding'] ?? null,
        'SEPARATE_EXPLICIT_ACTIVATION_AUTHORITY',
        'readiness_activation_authority_binding_invalid',
    );
    fscActivationLiteral($readiness['feature_activation_state'] ?? null, 'ACTIVE', 'readiness_feature_activation_state_invalid');
    fscActivationBool($readiness['secrets_embedded'] ?? null, false, 'readiness_secrets_boundary_invalid');

    $source = fscActivationHex(
        $readiness['exact_running_source_commit'] ?? null,
        40,
        'readiness_exact_source_invalid',
    );
    $artifact = fscActivationHex(
        $readiness['exact_running_artifact_sha256'] ?? null,
        64,
        'readiness_exact_artifact_invalid',
    );
    $environment = fscActivationString(
        $readiness['environment_id'] ?? null,
        '/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D',
        'readiness_environment_invalid',
    );
    $runtime = fscActivationString(
        $readiness['runtime_class'] ?? null,
        '/\Adurable-staging\z/D',
        'readiness_runtime_class_invalid',
    );

    return [
        'environment_id' => $environment,
        'runtime_class' => $runtime,
        'running_source_commit' => $source,
        'running_artifact_sha256' => $artifact,
        'durable_staging_runtime_enabled' => true,
        'production_data_allowed' => false,
    ];
}

/** @return array<string,mixed> */
function fscActivationFetchCanonicalDurableStagingReadiness(string $url, string $token): array
{
    return fscActivationMapCanonicalDurableStagingReadiness(
        fscActivationFetchReadiness($url, $token),
    );
}

function fscActivationSuccessorEnsureRuntimeEnvBinding(string $runtimeEnvPath, array $identity, string $expectedRaw): string
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
    $deploymentRoot = dirname(dirname($runtimeEnvPath));
    $releaseEnvPath = $deploymentRoot
        .'/releases/durable-staging-'.substr($sourceCommit, 0, 12)
        .'/apps/web/.env';

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

    // The pre-Sprint249 atomic writer can sever a deployment-created hardlink
    // while leaving both private files byte-identical. Repair only that exact,
    // same-filesystem, one-link-per-file shape before any feature mutation.
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
}

function fscActivationSuccessorWriteRuntimeEnvBound(string $path, string $contents): void
{
    $path = fscActivationSafeAbsoluteFile($path, true, 'runtime_env');
    $stat = stat($path);
    if (!is_array($stat) || !is_int($stat['nlink'] ?? null)) {
        fscActivationFail('runtime_env_write_stat_failed');
    }

    $linkCount = (int) $stat['nlink'];
    if ($linkCount === 1) {
        // Symlink-bound releases follow this stable shared path; atomic replace
        // therefore remains valid and does not sever the release binding.
        fscActivationWritePrivateAtomic($path, $contents);
        return;
    }
    if ($linkCount !== 2) {
        fscActivationFail('runtime_env_write_link_count_invalid');
    }

    $originalInode = $stat['ino'] ?? null;
    $originalDevice = $stat['dev'] ?? null;
    if (!is_int($originalInode) || !is_int($originalDevice)) {
        fscActivationFail('runtime_env_write_identity_invalid');
    }

    $handle = @fopen($path, 'r+b');
    if (!is_resource($handle)) {
        fscActivationFail('runtime_env_write_open_failed');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new FinalShiftCloseActivationTransportException('runtime_env_write_lock_failed');
        }
        $openStat = fstat($handle);
        if (!is_array($openStat)
            || ($openStat['ino'] ?? null) !== $originalInode
            || ($openStat['dev'] ?? null) !== $originalDevice
            || ($openStat['nlink'] ?? null) !== 2
            || (((int) ($openStat['mode'] ?? 0)) & 0077) !== 0
        ) {
            throw new FinalShiftCloseActivationTransportException('runtime_env_write_identity_drifted');
        }

        if (!rewind($handle) || !ftruncate($handle, 0)) {
            throw new FinalShiftCloseActivationTransportException('runtime_env_write_truncate_failed');
        }

        $length = strlen($contents);
        $written = 0;
        while ($written < $length) {
            $count = fwrite($handle, substr($contents, $written));
            if ($count === false || $count === 0) {
                throw new FinalShiftCloseActivationTransportException('runtime_env_write_failed');
            }
            $written += $count;
        }
        if (!fflush($handle)) {
            throw new FinalShiftCloseActivationTransportException('runtime_env_write_flush_failed');
        }
        if (function_exists('fsync') && !@fsync($handle)) {
            throw new FinalShiftCloseActivationTransportException('runtime_env_write_fsync_failed');
        }
    } finally {
        @flock($handle, LOCK_UN);
        fclose($handle);
    }

    clearstatcache(true, $path);
    $after = stat($path);
    $readback = file_get_contents($path);
    if (!is_array($after)
        || ($after['ino'] ?? null) !== $originalInode
        || ($after['dev'] ?? null) !== $originalDevice
        || ($after['nlink'] ?? null) !== 2
        || !is_string($readback)
        || !hash_equals(hash('sha256', $contents), hash('sha256', $readback))
    ) {
        fscActivationFail('runtime_env_write_readback_failed');
    }
}

/** @return array<string,mixed> */
function fscActivationExecuteReadinessSuccessor(
    string $transportEnvelopePath,
    string $authorityPath,
    string $runtimeManifestPath,
    string $runtimeEnvPath,
    string $approvalTokenPath,
    string $readinessUrl,
    string $outputPath,
): array {
    $transport = fscActivationLoadJson($transportEnvelopePath);
    $authorityRaw = fscActivationReadPrivate($authorityPath, 65536, 'authority_file');
    try {
        $authority = json_decode($authorityRaw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        fscActivationFail('authority_json_invalid');
    }
    if (!is_array($authority) || array_is_list($authority)) {
        fscActivationFail('authority_json_shape_invalid');
    }
    $runtimeManifest = fscActivationLoadJson($runtimeManifestPath);
    $approvalToken = trim(fscActivationReadPrivate($approvalTokenPath, 4096, 'approval_token_file'));
    if (strlen($approvalToken) < 32 || strlen($approvalToken) > 512) {
        fscActivationFail('approval_token_length_invalid');
    }

    $runtimeEnvPath = fscActivationSafeAbsoluteFile($runtimeEnvPath, true, 'runtime_env');
    $original = fscActivationReadPrivate($runtimeEnvPath, 131072, 'runtime_env');
    $identity = fscActivationValidateEnvelope($transport);
    fscActivationValidateRuntimeManifest($runtimeManifest, $identity);
    $authorityIdentity = fscActivationValidateAuthority($authority, $identity, $approvalToken);

    $env = fscActivationDotenv($original);
    foreach ([
        'ONEQAY_RUNTIME_CLASS' => $identity['runtime_class'],
        'ONEQAY_RUNNING_SOURCE_COMMIT' => $identity['source_commit'],
        'ONEQAY_RUNNING_ARTIFACT_SHA256' => $identity['artifact_sha256'],
        'ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID' => $identity['environment_id'],
        'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED' => 'true',
    ] as $key => $expected) {
        fscActivationLiteral($env[$key] ?? null, $expected, 'runtime_env_binding_mismatch:'.$key);
    }
    $attestationToken = $env['ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN'] ?? '';
    if (!is_string($attestationToken) || strlen($attestationToken) < 24 || strlen($attestationToken) > 4096) {
        fscActivationFail('runtime_attestation_token_invalid');
    }

    fscActivationSuccessorEnsureRuntimeEnvBinding($runtimeEnvPath, $identity, $original);

    $before = fscActivationReadFlag($original);
    if ($before['value'] !== false) {
        fscActivationFail('pre_activation_flag_must_be_false');
    }

    $afterRaw = fscActivationWriteFlagValue($original, true);
    $mutated = false;

    try {
        // Set before the hardlink-preserving in-place write so any partial I/O
        // failure still enters the exact-byte rollback path.
        $mutated = true;
        fscActivationSuccessorWriteRuntimeEnvBound($runtimeEnvPath, $afterRaw);

        $readbackRaw = fscActivationReadPrivate($runtimeEnvPath, 131072, 'runtime_env');
        $after = fscActivationReadFlag($readbackRaw);
        if ($after['value'] !== true) {
            fscActivationFail('post_write_flag_readback_failed');
        }

        $readiness = fscActivationFetchCanonicalDurableStagingReadiness($readinessUrl, $attestationToken);
        if (!is_array($readiness) || array_is_list($readiness)) {
            fscActivationFail('readiness_fetcher_shape_invalid');
        }
        fscActivationValidateReadiness($readiness, $identity);

        $receipt = [
            'schema_version' => 1,
            'feature' => 'final-shift-close',
            'execution_state' => 'FLAG_TRUE_VERIFIED_HEALTHY_AWAITING_RUNTIME_ALLOWLIST',
            'concrete_adapter' => 'CPANEL_PRIVATE_FILE_ONE_SHOT_CRON_V1',
            'environment_id' => $identity['environment_id'],
            'runtime_class' => $identity['runtime_class'],
            'exact_running_source_commit' => $identity['source_commit'],
            'exact_running_artifact_sha256' => $identity['artifact_sha256'],
            'readiness_attestation_sha256' => $identity['readiness_attestation_sha256'],
            'selection_fingerprint_sha256' => $identity['selection_fingerprint_sha256'],
            'target_binding_sha256' => $identity['target_binding_sha256'],
            'dependency_envelope_sha256' => $identity['dependency_envelope_sha256'],
            'activation_plan_sha256' => $identity['activation_plan_sha256'],
            'transport_envelope_sha256' => $identity['transport_envelope_sha256'],
            'operator_authority_sha256' => hash('sha256', $authorityRaw),
            'executor_source_commit' => $authorityIdentity['executor_source_commit'],
            'state_transition_pr_number' => $authorityIdentity['state_transition_pr_number'],
            'state_transition_head_sha' => $authorityIdentity['state_transition_head_sha'],
            'runtime_flag' => 'ONEQAY_POS_SHIFT_CLOSE_ENABLED',
            'flag_before' => false,
            'flag_before_explicit' => $before['present'],
            'flag_after' => true,
            'runtime_env_before_sha256' => hash('sha256', $original),
            'runtime_env_after_sha256' => hash('sha256', $readbackRaw),
            'non_mutating_health_attestation_sha256' => hash('sha256', fscActivationCanonicalJson($readiness)),
            'rollback_policy' => 'RESTORE_EXACT_PREWRITE_BYTES_ON_ANY_POST_WRITE_FAILURE',
            'runtime_allowlist_change' => 'NOT_PERFORMED',
            'feature_delivery_state' => 'BLOCKED_BY_RUNTIME_ALLOWLIST',
            'migration_execution_performed' => false,
            'permission_provisioning_performed' => false,
            'deployment_performed' => false,
            'technical_preview_activated' => false,
            'production_activated' => false,
            'updater_activated' => false,
            'secrets_embedded' => false,
            'attribution' => 'Lab | zefry',
        ];
        $receipt['receipt_sha256'] = hash('sha256', fscActivationCanonicalJson($receipt));
        $json = json_encode($receipt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
        fscActivationWritePrivateAtomic($outputPath, $json);

        return $receipt;
    } catch (Throwable $exception) {
        if ($mutated) {
            try {
                fscActivationSuccessorWriteRuntimeEnvBound($runtimeEnvPath, $original);
                $rollbackRaw = fscActivationReadPrivate($runtimeEnvPath, 131072, 'runtime_env');
                $rollback = fscActivationReadFlag($rollbackRaw);
                fscActivationSuccessorEnsureRuntimeEnvBinding($runtimeEnvPath, $identity, $original);
                if (!hash_equals(hash('sha256', $original), hash('sha256', $rollbackRaw)) || $rollback['value'] !== false) {
                    throw new RuntimeException('rollback_verification_failed');
                }
            } catch (Throwable $rollbackFailure) {
                throw new FinalShiftCloseActivationTransportException('activation_transport_rollback_failed', 0, $rollbackFailure);
            }
        }
        throw $exception;
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 8) {
        fwrite(
            STDERR,
            "Usage: php tools/cpanel/execute-final-shift-close-feature-activation-readiness-successor.php <transport-envelope.json> <private-authority.json> <runtime-manifest.json> <private-runtime-env> <private-approval-token-file> <https-readiness-url> <execution-evidence.json>\n",
        );
        exit(64);
    }

    try {
        $receipt = fscActivationExecuteReadinessSuccessor(
            $argv[1],
            $argv[2],
            $argv[3],
            $argv[4],
            $argv[5],
            $argv[6],
            $argv[7],
        );
        fwrite(STDOUT, 'RESULT=SUCCESS'.PHP_EOL);
        fwrite(STDOUT, 'EXECUTION_STATE='.(string) $receipt['execution_state'].PHP_EOL);
        fwrite(STDOUT, 'RECEIPT_SHA256='.(string) $receipt['receipt_sha256'].PHP_EOL);
        exit(0);
    } catch (Throwable $exception) {
        fwrite(STDERR, 'RESULT=FAILED'.PHP_EOL);
        fwrite(STDERR, 'ERROR_CODE='.$exception->getMessage().PHP_EOL);
        exit(1);
    }
}
