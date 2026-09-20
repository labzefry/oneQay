<?php

declare(strict_types=1);

// Author by Lab | zefry

final class DurableStagingOperatorDeploymentPlanException extends RuntimeException
{
}

function dsPlanFail(string $code): never
{
    throw new DurableStagingOperatorDeploymentPlanException($code);
}

/** @return array<string,mixed> */
function dsPlanLoadJson(string $path): array
{
    if ($path === '' || ! is_file($path) || is_link($path)) {
        dsPlanFail('input_unavailable');
    }

    $size = filesize($path);
    if (! is_int($size) || $size < 2 || $size > 262144) {
        dsPlanFail('input_size_invalid');
    }

    $raw = file_get_contents($path);
    if (! is_string($raw)) {
        dsPlanFail('input_read_failed');
    }

    try {
        $decoded = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        dsPlanFail('input_json_invalid');
    }

    if (! is_array($decoded) || array_is_list($decoded)) {
        dsPlanFail('input_json_shape_invalid');
    }

    return $decoded;
}

function dsPlanAssertLiteral(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) {
        dsPlanFail($code);
    }
}

function dsPlanAssertPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) {
        dsPlanFail($code);
    }

    return $value;
}

function dsPlanAssertBool(mixed $value, bool $expected, string $code): void
{
    if (! is_bool($value) || $value !== $expected) {
        dsPlanFail($code);
    }
}

function dsPlanAssertSafeAbsolutePath(mixed $value, string $code): string
{
    if (! is_string($value)
        || $value === ''
        || strlen($value) > 4096
        || ! str_starts_with($value, '/')
        || $value === '/'
        || str_contains($value, "\0")
        || str_contains($value, '\\')
        || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $value) === 1
        || preg_match('#//+#', $value) === 1
    ) {
        dsPlanFail($code);
    }

    return rtrim($value, '/');
}

/** @return array<string,mixed>|list<mixed> */
function dsPlanCanonicalize(array $value): array
{
    if (array_is_list($value)) {
        return array_map(
            static fn (mixed $item): mixed => is_array($item) ? dsPlanCanonicalize($item) : $item,
            $value,
        );
    }

    ksort($value, SORT_STRING);
    foreach ($value as $key => $item) {
        if (is_array($item)) {
            $value[$key] = dsPlanCanonicalize($item);
        }
    }

    return $value;
}

function dsPlanCanonicalJson(array $value): string
{
    $json = json_encode(
        dsPlanCanonicalize($value),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );

    if (! is_string($json)) {
        dsPlanFail('canonical_json_failed');
    }

    return $json;
}

/** @param array<string,mixed> $handoff */
function dsPlanValidateHandoff(array $handoff): array
{
    dsPlanAssertLiteral($handoff['schema_version'] ?? null, 1, 'handoff_schema_version_invalid');
    dsPlanAssertLiteral(
        $handoff['handoff_state'] ?? null,
        'VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED',
        'handoff_state_invalid',
    );
    dsPlanAssertLiteral($handoff['product']['name'] ?? null, 'oneQay', 'handoff_product_invalid');
    dsPlanAssertLiteral($handoff['product']['repository'] ?? null, 'labzefry/oneQay', 'handoff_repository_invalid');

    $releaseId = dsPlanAssertPattern(
        $handoff['artifact']['release_id'] ?? null,
        '/\\Adurable-staging-[0-9a-f]{12}\\z/',
        'handoff_release_id_invalid',
    );
    $sourceCommit = dsPlanAssertPattern(
        $handoff['artifact']['source_commit'] ?? null,
        '/\\A[0-9a-f]{40}\\z/',
        'handoff_source_commit_invalid',
    );
    $artifactSha256 = dsPlanAssertPattern(
        $handoff['artifact']['artifact_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'handoff_artifact_sha256_invalid',
    );
    $manifestSha256 = dsPlanAssertPattern(
        $handoff['artifact']['manifest_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'handoff_manifest_sha256_invalid',
    );

    dsPlanAssertLiteral($releaseId, 'durable-staging-'.substr($sourceCommit, 0, 12), 'handoff_release_source_mismatch');
    dsPlanAssertLiteral($handoff['runtime']['required_runtime_class'] ?? null, 'durable-staging', 'handoff_runtime_class_invalid');
    dsPlanAssertBool($handoff['runtime']['production'] ?? null, false, 'handoff_production_forbidden');
    dsPlanAssertBool($handoff['runtime']['production_data_allowed'] ?? null, false, 'handoff_production_data_forbidden');
    dsPlanAssertBool($handoff['runtime']['synthetic_fixture_runtime'] ?? null, false, 'handoff_synthetic_runtime_forbidden');

    dsPlanAssertLiteral($handoff['migration']['expected_count'] ?? null, 27, 'handoff_migration_count_invalid');
    dsPlanAssertLiteral($handoff['migration']['execution_state'] ?? null, 'NOT_PERFORMED', 'handoff_migration_state_invalid');
    dsPlanAssertBool($handoff['migration']['execution_authorized'] ?? null, false, 'handoff_migration_authority_forbidden');

    dsPlanAssertLiteral($handoff['deployment']['execution_state'] ?? null, 'NOT_PERFORMED', 'handoff_deployment_state_invalid');
    dsPlanAssertLiteral($handoff['deployment']['authority_state'] ?? null, 'NOT_GRANTED', 'handoff_embedded_authority_forbidden');

    $boundary = $handoff['operational_boundary'] ?? null;
    if (! is_array($boundary) || array_is_list($boundary)) {
        dsPlanFail('handoff_boundary_invalid');
    }
    dsPlanAssertLiteral($boundary['environment_deployment'] ?? null, 'NOT_PERFORMED', 'handoff_environment_deployment_invalid');
    dsPlanAssertLiteral($boundary['migration27_execution'] ?? null, 'NOT_PERFORMED', 'handoff_migration27_invalid');
    dsPlanAssertLiteral($boundary['permission_provisioning'] ?? null, 'NONE', 'handoff_permission_invalid');
    dsPlanAssertLiteral($boundary['feature_activation'] ?? null, 'INACTIVE', 'handoff_feature_activation_invalid');
    dsPlanAssertLiteral($boundary['technical_preview_activation'] ?? null, 'NOT_AUTHORIZED', 'handoff_preview_authority_invalid');
    dsPlanAssertLiteral($boundary['production_activation'] ?? null, 'NOT_AUTHORIZED', 'handoff_production_authority_invalid');
    dsPlanAssertLiteral($boundary['updater_activation'] ?? null, 'INACTIVE', 'handoff_updater_invalid');
    if (! array_key_exists('selected_target', $boundary) || $boundary['selected_target'] !== null) {
        dsPlanFail('handoff_selected_target_forbidden');
    }
    dsPlanAssertLiteral($boundary['producer_dispatch'] ?? null, 'NOT_PERFORMED', 'handoff_producer_dispatch_invalid');

    $requiredBindings = $handoff['required_external_bindings'] ?? null;
    $expectedBindings = [
        'ONEQAY_RUNTIME_CLASS',
        'ONEQAY_RUNNING_SOURCE_COMMIT',
        'ONEQAY_RUNNING_ARTIFACT_SHA256',
        'ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID',
        'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN',
        'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED',
    ];
    if (! is_array($requiredBindings) || ! array_is_list($requiredBindings)) {
        dsPlanFail('handoff_required_bindings_invalid');
    }
    $actualBindings = $requiredBindings;
    sort($actualBindings, SORT_STRING);
    $sortedExpected = $expectedBindings;
    sort($sortedExpected, SORT_STRING);
    if ($actualBindings !== $sortedExpected) {
        dsPlanFail('handoff_required_bindings_mismatch');
    }

    return [
        'release_id' => $releaseId,
        'source_commit' => $sourceCommit,
        'artifact_sha256' => $artifactSha256,
        'manifest_sha256' => $manifestSha256,
        'artifact_filename' => (string) ($handoff['artifact']['artifact_filename'] ?? ''),
        'required_bindings' => $expectedBindings,
    ];
}

/** @param array<string,mixed> $target @param array<string,mixed> $identity */
function dsPlanValidateTarget(array $target, array $identity): array
{
    dsPlanAssertLiteral($target['schema_version'] ?? null, 1, 'target_schema_version_invalid');
    dsPlanAssertLiteral($target['target_state'] ?? null, 'OPERATOR_TARGET_DECLARED', 'target_state_invalid');

    $environmentId = dsPlanAssertPattern(
        $target['environment_id'] ?? null,
        '/\\A[a-z0-9][a-z0-9-]{2,62}\\z/',
        'target_environment_id_invalid',
    );
    dsPlanAssertLiteral($target['runtime_class'] ?? null, 'durable-staging', 'target_runtime_class_invalid');
    dsPlanAssertBool($target['production'] ?? null, false, 'target_production_forbidden');
    dsPlanAssertBool($target['production_data_allowed'] ?? null, false, 'target_production_data_forbidden');
    dsPlanAssertBool($target['synthetic_fixture_runtime'] ?? null, false, 'target_synthetic_runtime_forbidden');

    $deploymentRoot = dsPlanAssertSafeAbsolutePath($target['filesystem']['deployment_root'] ?? null, 'target_deployment_root_invalid');
    $releaseRoot = dsPlanAssertSafeAbsolutePath($target['filesystem']['release_root'] ?? null, 'target_release_root_invalid');
    $sharedRuntimeRoot = dsPlanAssertSafeAbsolutePath($target['filesystem']['shared_runtime_root'] ?? null, 'target_shared_runtime_root_invalid');
    $activePointer = dsPlanAssertSafeAbsolutePath($target['filesystem']['active_release_pointer'] ?? null, 'target_active_pointer_invalid');

    if (! str_starts_with($releaseRoot.'/', $deploymentRoot.'/')
        || ! str_starts_with($sharedRuntimeRoot.'/', $deploymentRoot.'/')
        || ! str_starts_with($activePointer.'/', $deploymentRoot.'/')
    ) {
        dsPlanFail('target_filesystem_escape');
    }
    if ($releaseRoot === $sharedRuntimeRoot || $releaseRoot === $activePointer || $sharedRuntimeRoot === $activePointer) {
        dsPlanFail('target_filesystem_collision');
    }

    $presentation = $target['presentation'] ?? null;
    if ($presentation !== null) {
        if (! is_array($presentation) || array_is_list($presentation) || count($presentation) !== 2) {
            dsPlanFail('target_presentation_invalid');
        }
        $presentationMode = dsPlanAssertPattern(
            $presentation['mode'] ?? null,
            '/\\A(?:ACTIVE_RELEASE_PUBLIC|FIXED_PUBLIC_BRIDGE)\\z/',
            'target_presentation_mode_invalid',
        );
        $presentationDocumentRoot = dsPlanAssertSafeAbsolutePath(
            $presentation['document_root'] ?? null,
            'target_presentation_document_root_invalid',
        );
        if ($presentationMode === 'ACTIVE_RELEASE_PUBLIC') {
            dsPlanAssertLiteral(
                $presentationDocumentRoot,
                $activePointer.'/apps/web/public',
                'target_presentation_document_root_mismatch',
            );
        } elseif (str_starts_with($presentationDocumentRoot.'/', $deploymentRoot.'/')
            || str_starts_with($deploymentRoot.'/', $presentationDocumentRoot.'/')
        ) {
            dsPlanFail('target_fixed_public_document_root_not_disjoint');
        }
        $presentation = [
            'mode' => $presentationMode,
            'document_root' => $presentationDocumentRoot,
        ];
    }

    $requiredCaps = [
        'durable_database_persistence',
        'durable_session',
        'authorization',
        'transaction_durability',
        'pos_durability',
        'authenticated_configuration_channel',
        'read_before_write',
        'read_after_write',
        'non_mutating_health_attestation',
        'verified_rollback',
    ];
    foreach ($requiredCaps as $capability) {
        dsPlanAssertBool($target['capabilities'][$capability] ?? null, true, 'target_capability_missing_'.$capability);
    }

    dsPlanAssertBool($target['configuration']['secret_values_embedded'] ?? null, false, 'target_embedded_secret_forbidden');
    $bindingPresence = $target['configuration']['required_binding_presence'] ?? null;
    if (! is_array($bindingPresence) || array_is_list($bindingPresence)) {
        dsPlanFail('target_binding_presence_invalid');
    }
    foreach ($identity['required_bindings'] as $binding) {
        dsPlanAssertBool($bindingPresence[$binding] ?? null, true, 'target_required_binding_missing');
    }
    if (count($bindingPresence) !== count($identity['required_bindings'])) {
        dsPlanFail('target_binding_presence_extra');
    }

    $authority = $target['deployment_authority'] ?? null;
    if (! is_array($authority) || array_is_list($authority)) {
        dsPlanFail('target_authority_invalid');
    }
    dsPlanAssertLiteral($authority['state'] ?? null, 'EXTERNALLY_GRANTED_FOR_EXACT_TARGET', 'target_authority_state_invalid');
    $authorityId = dsPlanAssertPattern(
        $authority['authority_id'] ?? null,
        '/\\Adurable-staging-deployment-authority-[0-9a-f]{24}\\z/',
        'target_authority_id_invalid',
    );
    $authoritySha256 = dsPlanAssertPattern(
        $authority['authority_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'target_authority_sha256_invalid',
    );
    $requestId = dsPlanAssertPattern(
        $authority['request_id'] ?? null,
        '/\\Adurable-staging-deployment-request-[0-9a-f]{24}\\z/',
        'target_authority_request_id_invalid',
    );
    $requestSha256 = dsPlanAssertPattern(
        $authority['request_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'target_authority_request_sha256_invalid',
    );
    $targetDescriptorSha256 = dsPlanAssertPattern(
        $authority['target_descriptor_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'target_authority_target_descriptor_sha256_invalid',
    );

    $authorizedAt = $authority['authorized_at_unix'] ?? null;
    $expiresAt = $authority['expires_at_unix'] ?? null;
    if (! is_int($authorizedAt)
        || ! is_int($expiresAt)
        || $authorizedAt <= 0
        || $expiresAt <= $authorizedAt
        || ($expiresAt - $authorizedAt) > 900
    ) {
        dsPlanFail('target_authority_time_window_invalid');
    }
    $nowUnix = time();
    if ($nowUnix < $authorizedAt || $nowUnix >= $expiresAt) {
        dsPlanFail('target_authority_expired_or_not_current');
    }

    $candidateProjection = [
        'schema_version' => 1,
        'target_state' => 'OPERATOR_TARGET_CANDIDATE',
        'environment_id' => $environmentId,
        'runtime_class' => 'durable-staging',
        'production' => false,
        'production_data_allowed' => false,
        'synthetic_fixture_runtime' => false,
        'filesystem' => [
            'deployment_root' => $deploymentRoot,
            'release_root' => $releaseRoot,
            'shared_runtime_root' => $sharedRuntimeRoot,
            'active_release_pointer' => $activePointer,
        ],
        ...($presentation !== null ? ['presentation' => $presentation] : []),
        'capabilities' => $target['capabilities'],
        'configuration' => $target['configuration'],
        'attribution' => 'Lab | zefry',
    ];
    dsPlanAssertLiteral(
        $targetDescriptorSha256,
        hash('sha256', dsPlanCanonicalJson($candidateProjection)),
        'target_authority_target_descriptor_mismatch',
    );

    dsPlanAssertLiteral($authority['environment_id'] ?? null, $environmentId, 'target_authority_environment_mismatch');
    dsPlanAssertLiteral($authority['release_id'] ?? null, $identity['release_id'], 'target_authority_release_mismatch');
    dsPlanAssertLiteral($authority['artifact_sha256'] ?? null, $identity['artifact_sha256'], 'target_authority_artifact_mismatch');
    dsPlanAssertBool($authority['deployment_allowed'] ?? null, true, 'target_deployment_authority_missing');
    dsPlanAssertBool($authority['migration_execution_allowed'] ?? null, false, 'target_migration_authority_forbidden');
    dsPlanAssertBool($authority['production_allowed'] ?? null, false, 'target_production_authority_forbidden');

    return [
        'environment_id' => $environmentId,
        'deployment_root' => $deploymentRoot,
        'release_root' => $releaseRoot,
        'shared_runtime_root' => $sharedRuntimeRoot,
        'active_release_pointer' => $activePointer,
        'presentation' => $presentation,
        'authority_id' => $authorityId,
        'authority_sha256' => $authoritySha256,
        'request_id' => $requestId,
        'request_sha256' => $requestSha256,
        'target_descriptor_sha256' => $targetDescriptorSha256,
        'authorized_at_unix' => $authorizedAt,
        'expires_at_unix' => $expiresAt,
    ];
}

/** @return array<string,mixed> */
function dsPlanPrepare(string $handoffPath, string $targetPath): array
{
    $handoff = dsPlanLoadJson($handoffPath);
    $target = dsPlanLoadJson($targetPath);
    $identity = dsPlanValidateHandoff($handoff);
    $validatedTarget = dsPlanValidateTarget($target, $identity);

    $releaseDirectory = $validatedTarget['release_root'].'/'.$identity['release_id'];
    $planTarget = [
        'environment_id' => $validatedTarget['environment_id'],
        'runtime_class' => 'durable-staging',
        'production' => false,
        'production_data_allowed' => false,
        'synthetic_fixture_runtime' => false,
        'deployment_root' => $validatedTarget['deployment_root'],
        'release_directory' => $releaseDirectory,
        'shared_runtime_root' => $validatedTarget['shared_runtime_root'],
        'active_release_pointer' => $validatedTarget['active_release_pointer'],
    ];
    if ($validatedTarget['presentation'] !== null) {
        $planTarget['presentation'] = $validatedTarget['presentation'];
    }

    $planCore = [
        'schema_version' => 1,
        'plan_state' => 'QUALIFIED_FOR_EXTERNAL_OPERATOR_EXECUTION_NOT_EXECUTED',
        'product' => [
            'name' => 'oneQay',
            'repository' => 'labzefry/oneQay',
        ],
        'artifact' => [
            'release_id' => $identity['release_id'],
            'source_commit' => $identity['source_commit'],
            'artifact_filename' => $identity['artifact_filename'],
            'artifact_sha256' => $identity['artifact_sha256'],
            'manifest_sha256' => $identity['manifest_sha256'],
        ],
        'target' => $planTarget,
        'authority_binding' => [
            'state' => 'EXTERNAL_AUTHORITY_BOUND_TO_EXACT_TARGET',
            'authority_id' => $validatedTarget['authority_id'],
            'authority_sha256' => $validatedTarget['authority_sha256'],
            'request_id' => $validatedTarget['request_id'],
            'request_sha256' => $validatedTarget['request_sha256'],
            'target_descriptor_sha256' => $validatedTarget['target_descriptor_sha256'],
            'authorized_at_unix' => $validatedTarget['authorized_at_unix'],
            'expires_at_unix' => $validatedTarget['expires_at_unix'],
            'deployment_allowed' => true,
            'migration_execution_allowed' => false,
            'production_allowed' => false,
        ],
        'preflight' => [
            'required_before_mutation' => true,
            'checks' => [
                'artifact_and_handoff_revalidated',
                'target_paths_private_and_writeable',
                'public_document_root_points_to_release_public_only',
                'shared_runtime_root_private',
                'required_external_bindings_present_without_embedding_values',
                'durable_database_session_authorization_transaction_pos_capabilities_confirmed',
                'previous_active_release_identity_read_before_write',
                'health_attestation_contract_reachable',
                'rollback_target_preserved',
            ],
        ],
        'execution_order' => [
            'REVALIDATE_EXACT_ARTIFACT_AND_HANDOFF',
            'READ_CURRENT_ACTIVE_RELEASE_AND_PRESERVE_ROLLBACK_TARGET',
            'PREFLIGHT_PRIVATE_FILESYSTEM_AND_PUBLIC_DOCUMENT_ROOT',
            'EXTRACT_TO_NEW_IMMUTABLE_RELEASE_DIRECTORY',
            'BIND_EXTERNAL_RUNTIME_CONFIGURATION_AND_SECRETS',
            'VERIFY_RUNNING_SOURCE_ARTIFACT_RUNTIME_AND_ENVIRONMENT_IDENTITY',
            'PERFORM_READ_BEFORE_WRITE_READ_AFTER_CONFIGURATION_VERIFICATION',
            'RUN_NON_MUTATING_DURABLE_RUNTIME_HEALTH_ATTESTATION',
            'VERIFY_ROLLBACK_PATH_BEFORE_ACCEPTING_DEPLOYMENT',
            'WRITE_DEPLOYMENT_READBACK_EVIDENCE',
        ],
        'readback_expectations' => [
            'environment_id' => $validatedTarget['environment_id'],
            'runtime_class' => 'durable-staging',
            'running_source_commit' => $identity['source_commit'],
            'running_artifact_sha256' => $identity['artifact_sha256'],
            'durable_staging_runtime_enabled' => true,
            'production_data_allowed' => false,
        ],
        'rollback' => [
            'required' => true,
            'previous_active_release_must_be_preserved' => true,
            'rollback_must_be_verified_before_acceptance' => true,
            'database_rollback_implied' => false,
            'migration_rollback_implied' => false,
        ],
        'migration' => [
            'source_included' => true,
            'expected_count' => 27,
            'execution_state' => 'NOT_PERFORMED',
            'execution_authorized' => false,
        ],
        'required_evidence' => [
            'host_preflight',
            'artifact_provenance_readback',
            'configuration_readback',
            'non_mutating_health_attestation',
            'rollback_verification',
            'deployment_receipt',
        ],
        'operational_boundary' => [
            'plan_preparation' => 'PERFORMED',
            'environment_creation' => 'NOT_PERFORMED',
            'environment_deployment' => 'NOT_PERFORMED',
            'archive_extraction' => 'NOT_PERFORMED',
            'runtime_configuration_mutation' => 'NOT_PERFORMED',
            'active_release_pointer_mutation' => 'NOT_PERFORMED',
            'migration27_execution' => 'NOT_PERFORMED',
            'permission_provisioning' => 'NONE',
            'feature_activation' => 'INACTIVE',
            'technical_preview_activation' => 'NOT_AUTHORIZED',
            'production_activation' => 'NOT_AUTHORIZED',
            'updater_activation' => 'INACTIVE',
            'target_selection' => 'BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET',
            'selected_target' => null,
            'producer_dispatch' => 'NOT_PERFORMED',
        ],
        'attribution' => 'Lab | zefry',
    ];

    $fingerprint = hash('sha256', dsPlanCanonicalJson($planCore));

    return ['plan_fingerprint' => $fingerprint] + $planCore;
}

function dsPlanWrite(string $outputPath, array $plan): void
{
    if ($outputPath === '' || is_link($outputPath) || is_dir($outputPath)) {
        dsPlanFail('output_path_invalid');
    }

    $directory = dirname($outputPath);
    if (! is_dir($directory) || ! is_writable($directory)) {
        dsPlanFail('output_directory_invalid');
    }

    $encoded = json_encode(
        $plan,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    )."\n";

    $temp = tempnam($directory, '.oneqay-plan-');
    if ($temp === false) {
        dsPlanFail('output_temp_create_failed');
    }

    try {
        if (file_put_contents($temp, $encoded, LOCK_EX) !== strlen($encoded)) {
            dsPlanFail('output_write_failed');
        }
        @chmod($temp, 0600);
        if (! rename($temp, $outputPath)) {
            dsPlanFail('output_commit_failed');
        }
        @chmod($outputPath, 0600);
    } finally {
        if (is_file($temp)) {
            @unlink($temp);
        }
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 4) {
        fwrite(STDERR, "Usage: php tools/prepare-durable-staging-operator-deployment-plan.php <handoff.json> <target.json> <output.json>\n");
        exit(64);
    }

    try {
        $plan = dsPlanPrepare($argv[1], $argv[2]);
        dsPlanWrite($argv[3], $plan);
        fwrite(STDOUT, "durable_staging_operator_deployment_plan_prepared\n");
        exit(0);
    } catch (DurableStagingOperatorDeploymentPlanException $failure) {
        fwrite(STDERR, "durable_staging_operator_deployment_plan_failed:".$failure->getMessage()."\n");
        exit(1);
    } catch (Throwable) {
        fwrite(STDERR, "durable_staging_operator_deployment_plan_failed:unexpected_failure\n");
        exit(1);
    }
}
