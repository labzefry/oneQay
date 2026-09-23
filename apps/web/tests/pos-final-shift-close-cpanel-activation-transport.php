<?php

declare(strict_types=1);

require_once __DIR__.'/../../../tools/cpanel/execute-final-shift-close-feature-activation.php';

// Author by Lab | zefry

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('Sprint239 regression failed: '.$message);
    }
};

$root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s239-'.bin2hex(random_bytes(8));
$remove = null;
$remove = static function (string $path) use (&$remove): void {
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (!is_dir($path)) {
        return;
    }
    foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $item) {
        $remove($item->getPathname());
    }
    @rmdir($path);
};

$writePrivate = static function (string $path, string $contents): void {
    if (file_put_contents($path, $contents, LOCK_EX) === false) {
        throw new RuntimeException('fixture write failed');
    }
    chmod($path, 0600);
};
$writeJson = static function (string $path, array $value, bool $private = false) use ($writePrivate): void {
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
    if ($private) {
        $writePrivate($path, $json);
        return;
    }
    if (file_put_contents($path, $json, LOCK_EX) === false) {
        throw new RuntimeException('fixture json write failed');
    }
};

try {
    $assert(mkdir($root, 0700, true), 'fixture root unavailable');

    $environment = 'oneqay-durable-staging-01';
    $runtime = 'durable-staging';
    $source = str_repeat('5', 40);
    $artifact = str_repeat('6', 64);
    $attestation = str_repeat('3', 64);
    $selection = str_repeat('8', 64);
    $targetBinding = str_repeat('b', 64);
    $dependency = str_repeat('d', 64);
    $activationAuthority = str_repeat('a', 64);
    $activationPlan = str_repeat('c', 64);
    $capabilityBundle = str_repeat('e', 64);
    $executorSource = str_repeat('f', 40);
    $targetHead = str_repeat('1', 40);
    $approvalToken = 's239-approval-'.str_repeat('T', 48);
    $readinessToken = 's239-readiness-'.str_repeat('R', 48);

    $transport = [
        'schema_version' => 1,
        'feature' => 'final-shift-close',
        'transport_state' => 'TARGET_BOUND_FEATURE_ACTIVATION_TRANSPORT_ENVELOPE_SOURCE_ONLY',
        'environment_id' => $environment,
        'runtime_class' => $runtime,
        'exact_running_source_commit' => $source,
        'exact_running_artifact_sha256' => $artifact,
        'readiness_attestation_sha256' => $attestation,
        'selection_fingerprint_sha256' => $selection,
        'target_binding_sha256' => $targetBinding,
        'dependency_envelope_sha256' => $dependency,
        'activation_authority_sha256' => $activationAuthority,
        'activation_plan_sha256' => $activationPlan,
        'capability_evidence_bundle_sha256' => $capabilityBundle,
        'authenticated_configuration_channel_evidence_sha256' => str_repeat('2', 64),
        'read_before_write_read_after_evidence_sha256' => str_repeat('4', 64),
        'non_mutating_health_attestation_evidence_sha256' => str_repeat('7', 64),
        'verified_flag_rollback_evidence_sha256' => str_repeat('9', 64),
        'runtime_flag' => 'ONEQAY_POS_SHIFT_CLOSE_ENABLED',
        'operations' => [
            ['sequence' => 1, 'operation' => 'READ_FLAG', 'expected_value' => false],
            ['sequence' => 2, 'operation' => 'WRITE_FLAG', 'value' => true],
            ['sequence' => 3, 'operation' => 'READ_FLAG', 'expected_value' => true],
            ['sequence' => 4, 'operation' => 'NON_MUTATING_HEALTH_ATTESTATION'],
            ['sequence' => 5, 'operation' => 'ROLLBACK_WRITE_FLAG_ON_POST_WRITE_FAILURE', 'value' => false],
            ['sequence' => 6, 'operation' => 'VERIFY_ROLLBACK_READBACK', 'expected_value' => false],
        ],
        'concrete_adapter' => 'NOT_IMPLEMENTED',
        'network_dispatch' => 'NOT_PERFORMED',
        'runtime_allowlist_change' => 'NOT_IMPLEMENTED',
        'feature_activation_state' => 'INACTIVE',
        'secrets_embedded' => false,
    ];
    $transport['transport_envelope_sha256'] = hash('sha256', fscActivationCanonicalJson($transport));

    $manifest = [
        'schema_version' => 1,
        'feature' => 'final-shift-close',
        'selection_state' => 'SELECTED_NOT_AUTHORIZED',
        'environment_id' => $environment,
        'runtime_class' => $runtime,
        'exact_running_source_commit' => $source,
        'exact_running_artifact_sha256' => $artifact,
        'readiness_attestation_sha256' => $attestation,
        'selection_fingerprint_sha256' => $selection,
        'trusted_ingestion' => [
            'run_id' => 35596214711,
            'run_attempt' => 1,
            'ingestion_fingerprint_sha256' => str_repeat('1', 64),
        ],
        'secrets_embedded' => false,
    ];

    $authority = [
        'schema_version' => 1,
        'feature' => 'final-shift-close',
        'authority_state' => 'GRANTED',
        'authority_context' => 'final-shift-close-feature-activation-authority',
        'concrete_adapter' => 'CPANEL_PRIVATE_FILE_ONE_SHOT_CRON_V1',
        'environment_id' => $environment,
        'runtime_class' => $runtime,
        'exact_running_source_commit' => $source,
        'exact_running_artifact_sha256' => $artifact,
        'readiness_attestation_sha256' => $attestation,
        'selection_fingerprint_sha256' => $selection,
        'target_binding_sha256' => $targetBinding,
        'dependency_envelope_sha256' => $dependency,
        'activation_plan_sha256' => $activationPlan,
        'transport_envelope_sha256' => $transport['transport_envelope_sha256'],
        'executor_source_commit' => $executorSource,
        'state_transition_pr_number' => 999,
        'state_transition_head_sha' => $targetHead,
        'approval_token_sha256' => hash('sha256', $approvalToken),
        'authorized_at_unix' => time() - 10,
        'expires_at_unix' => time() + 600,
        'feature_activation_allowed' => true,
        'migration_execution_allowed' => false,
        'permission_provisioning_allowed' => false,
        'deployment_allowed' => false,
        'technical_preview_allowed' => false,
        'production_allowed' => false,
        'updater_allowed' => false,
        'runtime_allowlist_change_allowed' => false,
        'secrets_embedded' => false,
        'attribution' => 'Lab | zefry',
    ];

    $transportPath = $root.'/transport-envelope.json';
    $manifestPath = $root.'/runtime-manifest.json';
    $authorityPath = $root.'/activation-authority.json';
    $tokenPath = $root.'/approval-token.txt';
    $envPath = $root.'/runtime.env';
    $outputPath = $root.'/execution-evidence.json';

    $writeJson($transportPath, $transport);
    $writeJson($manifestPath, $manifest);
    $writeJson($authorityPath, $authority, true);
    $writePrivate($tokenPath, $approvalToken."\n");

    $originalEnv = implode("\n", [
        'APP_ENV="production"',
        'ONEQAY_RUNTIME_CLASS="'.$runtime.'"',
        'ONEQAY_RUNNING_SOURCE_COMMIT="'.$source.'"',
        'ONEQAY_RUNNING_ARTIFACT_SHA256="'.$artifact.'"',
        'ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID="'.$environment.'"',
        'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN="'.$readinessToken.'"',
        'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED="true"',
        'ONEQAY_POS_SHIFT_CLOSE_ENABLED="false"',
        'ONEQAY_UNRELATED_SECRET="must-stay-byte-identical"',
        '',
    ]);
    $writePrivate($envPath, $originalEnv);

    $healthy = static function (string $url, string $token) use ($assert, $readinessToken, $environment, $runtime, $source, $artifact): array {
        $assert($url === 'https://fixture.invalid/readiness', 'readiness URL drifted');
        $assert(hash_equals($readinessToken, $token), 'readiness token drifted');
        return [
            'environment_id' => $environment,
            'runtime_class' => $runtime,
            'running_source_commit' => $source,
            'running_artifact_sha256' => $artifact,
            'durable_staging_runtime_enabled' => true,
            'production_data_allowed' => false,
        ];
    };

    $receipt = fscActivationExecute(
        $transportPath,
        $authorityPath,
        $manifestPath,
        $envPath,
        $tokenPath,
        'https://fixture.invalid/readiness',
        $outputPath,
        $healthy,
    );

    $assert(($receipt['execution_state'] ?? null) === 'FLAG_TRUE_VERIFIED_HEALTHY_AWAITING_RUNTIME_ALLOWLIST', 'execution state invalid');
    $assert(($receipt['feature_delivery_state'] ?? null) === 'BLOCKED_BY_RUNTIME_ALLOWLIST', 'runtime allowlist boundary not preserved');
    $assert(($receipt['runtime_allowlist_change'] ?? null) === 'NOT_PERFORMED', 'runtime allowlist was mutated');
    $assert(($receipt['migration_execution_performed'] ?? true) === false, 'migration boundary crossed');
    $assert(($receipt['permission_provisioning_performed'] ?? true) === false, 'permission boundary crossed');
    $assert(($receipt['technical_preview_activated'] ?? true) === false, 'Technical Preview boundary crossed');
    $assert(($receipt['production_activated'] ?? true) === false, 'Production boundary crossed');
    $assert(($receipt['updater_activated'] ?? true) === false, 'updater boundary crossed');

    $afterEnv = (string) file_get_contents($envPath);
    $assert(fscActivationReadFlag($afterEnv)['value'] === true, 'runtime flag was not true after success');
    $expectedAfter = str_replace('ONEQAY_POS_SHIFT_CLOSE_ENABLED="false"', 'ONEQAY_POS_SHIFT_CLOSE_ENABLED="true"', $originalEnv);
    $assert($afterEnv === $expectedAfter, 'successful mutation changed unrelated runtime bytes');
    $assert(is_file($outputPath), 'execution evidence missing');
    $evidenceRaw = (string) file_get_contents($outputPath);
    foreach ([$approvalToken, $readinessToken, 'must-stay-byte-identical'] as $secret) {
        $assert(!str_contains($evidenceRaw, $secret), 'secret leaked into evidence');
    }

    $writePrivate($envPath, $originalEnv);
    @unlink($outputPath);
    $badTokenPath = $root.'/bad-token.txt';
    $writePrivate($badTokenPath, 'wrong-'.str_repeat('X', 48)."\n");
    $denied = false;
    try {
        fscActivationExecute(
            $transportPath,
            $authorityPath,
            $manifestPath,
            $envPath,
            $badTokenPath,
            'https://fixture.invalid/readiness',
            $outputPath,
            $healthy,
        );
    } catch (FinalShiftCloseActivationTransportException $exception) {
        $denied = $exception->getMessage() === 'authority_token_invalid';
    }
    $assert($denied, 'wrong approval token did not fail closed');
    $assert((string) file_get_contents($envPath) === $originalEnv, 'wrong token mutated runtime environment');

    $writePrivate($envPath, $originalEnv);
    $failedHealth = false;
    try {
        fscActivationExecute(
            $transportPath,
            $authorityPath,
            $manifestPath,
            $envPath,
            $tokenPath,
            'https://fixture.invalid/readiness',
            $outputPath,
            static function (): array {
                throw new FinalShiftCloseActivationTransportException('fixture_health_failure');
            },
        );
    } catch (FinalShiftCloseActivationTransportException $exception) {
        $failedHealth = $exception->getMessage() === 'fixture_health_failure';
    }
    $assert($failedHealth, 'post-write health failure was not surfaced');
    $assert((string) file_get_contents($envPath) === $originalEnv, 'health failure did not restore exact pre-write bytes');
    $assert(fscActivationReadFlag((string) file_get_contents($envPath))['value'] === false, 'rollback flag readback failed');

    $writePrivate($envPath, str_replace('ONEQAY_POS_SHIFT_CLOSE_ENABLED="false"', 'ONEQAY_POS_SHIFT_CLOSE_ENABLED="true"', $originalEnv));
    $replayDenied = false;
    try {
        fscActivationExecute(
            $transportPath,
            $authorityPath,
            $manifestPath,
            $envPath,
            $tokenPath,
            'https://fixture.invalid/readiness',
            $outputPath,
            $healthy,
        );
    } catch (FinalShiftCloseActivationTransportException $exception) {
        $replayDenied = $exception->getMessage() === 'pre_activation_flag_must_be_false';
    }
    $assert($replayDenied, 'already-true flag did not fail closed');

    $sourceText = (string) file_get_contents(__DIR__.'/../../../tools/cpanel/execute-final-shift-close-feature-activation.php');
    foreach ([
        'CPANEL_PRIVATE_FILE_ONE_SHOT_CRON_V1',
        'ONEQAY_POS_SHIFT_CLOSE_ENABLED',
        'RESTORE_EXACT_PREWRITE_BYTES_ON_ANY_POST_WRITE_FAILURE',
        'CURLOPT_PROTOCOLS => CURLPROTO_HTTPS',
        'runtime_allowlist_change_allowed',
        'feature_delivery_state',
    ] as $required) {
        $assert(str_contains($sourceText, $required), 'executor source missing '.$required);
    }
    foreach (['Artisan::call', 'migrate', 'production_activation = true'] as $forbidden) {
        $assert(!str_contains($sourceText, $forbidden), 'executor source contains forbidden primitive '.$forbidden);
    }

    fwrite(STDOUT, "Sprint239 Final Shift Close cPanel activation transport regression passed.\n");
} finally {
    $remove($root);
}
