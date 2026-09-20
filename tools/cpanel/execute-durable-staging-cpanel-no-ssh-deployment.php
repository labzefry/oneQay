<?php

declare(strict_types=1);

require_once __DIR__.'/fixed-public-document-root-bridge.php';

// Author by Lab | zefry

final class CpanelNoSshDeploymentExecutionException extends RuntimeException
{
}

function cpanelExecFail(string $code): never
{
    throw new CpanelNoSshDeploymentExecutionException($code);
}

/** @return array<string,mixed> */
function cpanelExecLoadJson(string $path, bool $private = false): array
{
    if ($path === '' || str_contains($path, "\0") || ! is_file($path) || is_link($path) || ! is_readable($path)) {
        cpanelExecFail('input_unavailable');
    }

    $size = filesize($path);
    if (! is_int($size) || $size < 2 || $size > 524288) {
        cpanelExecFail('input_size_invalid');
    }

    if ($private) {
        $mode = fileperms($path);
        if (! is_int($mode) || (($mode & 0077) !== 0)) {
            cpanelExecFail('private_file_permissions_invalid');
        }
    }

    $raw = file_get_contents($path);
    if (! is_string($raw)) {
        cpanelExecFail('input_read_failed');
    }

    try {
        $decoded = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        cpanelExecFail('input_json_invalid');
    }

    if (! is_array($decoded) || array_is_list($decoded)) {
        cpanelExecFail('input_json_shape_invalid');
    }

    return $decoded;
}

function cpanelExecLiteral(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) {
        cpanelExecFail($code);
    }
}

function cpanelExecBool(mixed $actual, bool $expected, string $code): void
{
    if (! is_bool($actual) || $actual !== $expected) {
        cpanelExecFail($code);
    }
}

function cpanelExecPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) {
        cpanelExecFail($code);
    }

    return $value;
}

function cpanelExecSafeAbsolutePath(mixed $value, string $code): string
{
    if (! is_string($value)
        || $value === ''
        || strlen($value) > 4096
        || ! str_starts_with($value, '/')
        || $value === '/'
        || str_contains($value, "\0")
        || str_contains($value, '\\')
        || preg_match('#(?:^|/)\\.{1,2}(?:/|$)#', $value) === 1
        || preg_match('#//+#', $value) === 1
    ) {
        cpanelExecFail($code);
    }

    return rtrim($value, '/');
}

/** @return array<string,mixed>|list<mixed> */
function cpanelExecCanonicalize(array $value): array
{
    if (array_is_list($value)) {
        return array_map(
            static fn (mixed $item): mixed => is_array($item) ? cpanelExecCanonicalize($item) : $item,
            $value,
        );
    }

    ksort($value, SORT_STRING);
    foreach ($value as $key => $item) {
        if (is_array($item)) {
            $value[$key] = cpanelExecCanonicalize($item);
        }
    }

    return $value;
}

function cpanelExecCanonicalJson(array $value): string
{
    $encoded = json_encode(
        cpanelExecCanonicalize($value),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );

    if (! is_string($encoded)) {
        cpanelExecFail('canonical_json_failed');
    }

    return $encoded;
}

function cpanelExecNested(string $child, string $parent, string $code): void
{
    if (! str_starts_with($child.'/', $parent.'/')) {
        cpanelExecFail($code);
    }
}

function cpanelExecPrivateFile(string $path, string $sharedRoot, string $code): string
{
    if ($path === '' || str_contains($path, "\0") || ! is_file($path) || is_link($path) || ! is_readable($path)) {
        cpanelExecFail($code.'_unavailable');
    }

    $mode = fileperms($path);
    if (! is_int($mode) || (($mode & 0077) !== 0)) {
        cpanelExecFail($code.'_permissions_invalid');
    }

    $real = realpath($path);
    $sharedReal = realpath($sharedRoot);
    if (! is_string($real) || ! is_string($sharedReal) || ! str_starts_with($real.'/', rtrim($sharedReal, '/').'/')) {
        cpanelExecFail($code.'_outside_shared_runtime_root');
    }

    return $real;
}

/** @return list<string> */
function cpanelExecDisabledFunctions(): array
{
    $raw = (string) ini_get('disable_functions');
    if ($raw === '') {
        return [];
    }

    return array_values(array_filter(
        array_map('trim', explode(',', $raw)),
        static fn (string $value): bool => $value !== '',
    ));
}

function cpanelExecFunctionAvailable(string $name): bool
{
    return function_exists($name) && ! in_array($name, cpanelExecDisabledFunctions(), true);
}

/** @return array<string,mixed> */
function cpanelExecValidatePlan(array $plan): array
{
    cpanelExecLiteral($plan['schema_version'] ?? null, 1, 'plan_schema_version_invalid');
    cpanelExecLiteral(
        $plan['plan_state'] ?? null,
        'QUALIFIED_FOR_EXTERNAL_OPERATOR_EXECUTION_NOT_EXECUTED',
        'plan_state_invalid',
    );

    $fingerprint = cpanelExecPattern(
        $plan['plan_fingerprint'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'plan_fingerprint_invalid',
    );

    $core = $plan;
    unset($core['plan_fingerprint']);
    cpanelExecLiteral(
        hash('sha256', cpanelExecCanonicalJson($core)),
        $fingerprint,
        'plan_fingerprint_mismatch',
    );

    cpanelExecLiteral($plan['product']['name'] ?? null, 'oneQay', 'plan_product_invalid');
    cpanelExecLiteral($plan['product']['repository'] ?? null, 'labzefry/oneQay', 'plan_repository_invalid');

    $releaseId = cpanelExecPattern(
        $plan['artifact']['release_id'] ?? null,
        '/\\Adurable-staging-[0-9a-f]{12}\\z/',
        'plan_release_id_invalid',
    );
    $sourceCommit = cpanelExecPattern(
        $plan['artifact']['source_commit'] ?? null,
        '/\\A[0-9a-f]{40}\\z/',
        'plan_source_commit_invalid',
    );
    $artifactSha256 = cpanelExecPattern(
        $plan['artifact']['artifact_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'plan_artifact_sha256_invalid',
    );
    cpanelExecPattern(
        $plan['artifact']['manifest_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'plan_manifest_sha256_invalid',
    );
    cpanelExecLiteral($releaseId, 'durable-staging-'.substr($sourceCommit, 0, 12), 'plan_release_source_mismatch');

    $environmentId = cpanelExecPattern(
        $plan['target']['environment_id'] ?? null,
        '/\\A[a-z0-9][a-z0-9-]{2,62}\\z/',
        'plan_environment_id_invalid',
    );
    cpanelExecLiteral($plan['target']['runtime_class'] ?? null, 'durable-staging', 'plan_runtime_class_invalid');
    cpanelExecBool($plan['target']['production'] ?? null, false, 'plan_production_forbidden');
    cpanelExecBool($plan['target']['production_data_allowed'] ?? null, false, 'plan_production_data_forbidden');
    cpanelExecBool($plan['target']['synthetic_fixture_runtime'] ?? null, false, 'plan_synthetic_runtime_forbidden');

    $deploymentRoot = cpanelExecSafeAbsolutePath(
        $plan['target']['deployment_root'] ?? null,
        'plan_deployment_root_invalid',
    );
    $releaseDirectory = cpanelExecSafeAbsolutePath(
        $plan['target']['release_directory'] ?? null,
        'plan_release_directory_invalid',
    );
    $sharedRoot = cpanelExecSafeAbsolutePath(
        $plan['target']['shared_runtime_root'] ?? null,
        'plan_shared_root_invalid',
    );
    $activePointer = cpanelExecSafeAbsolutePath(
        $plan['target']['active_release_pointer'] ?? null,
        'plan_active_pointer_invalid',
    );

    $releaseRoot = dirname($releaseDirectory);
    cpanelExecNested($releaseRoot, $deploymentRoot, 'plan_release_root_escape');
    cpanelExecNested($sharedRoot, $deploymentRoot, 'plan_shared_root_escape');
    cpanelExecNested($activePointer, $deploymentRoot, 'plan_active_pointer_escape');
    cpanelExecLiteral($releaseDirectory, $releaseRoot.'/'.$releaseId, 'plan_release_directory_mismatch');

    $presentation = $plan['target']['presentation'] ?? null;
    if ($presentation === null) {
        $presentationMode = 'ACTIVE_RELEASE_PUBLIC';
        $documentRoot = $activePointer.'/apps/web/public';
    } else {
        if (! is_array($presentation) || array_is_list($presentation) || count($presentation) !== 2) {
            cpanelExecFail('plan_presentation_invalid');
        }
        $presentationMode = cpanelExecPattern(
            $presentation['mode'] ?? null,
            '/\\A(?:ACTIVE_RELEASE_PUBLIC|FIXED_PUBLIC_BRIDGE)\\z/',
            'plan_presentation_mode_invalid',
        );
        $documentRoot = cpanelExecSafeAbsolutePath(
            $presentation['document_root'] ?? null,
            'plan_presentation_document_root_invalid',
        );
        if ($presentationMode === 'ACTIVE_RELEASE_PUBLIC') {
            cpanelExecLiteral(
                $documentRoot,
                $activePointer.'/apps/web/public',
                'plan_presentation_document_root_mismatch',
            );
        } elseif (str_starts_with($documentRoot.'/', $deploymentRoot.'/')
            || str_starts_with($deploymentRoot.'/', $documentRoot.'/')
        ) {
            cpanelExecFail('plan_fixed_public_document_root_not_disjoint');
        }
    }

    cpanelExecLiteral(
        $plan['authority_binding']['state'] ?? null,
        'EXTERNAL_AUTHORITY_BOUND_TO_EXACT_TARGET',
        'plan_authority_state_invalid',
    );
    $authorityId = cpanelExecPattern(
        $plan['authority_binding']['authority_id'] ?? null,
        '/\\Adurable-staging-deployment-authority-[0-9a-f]{24}\\z/',
        'plan_authority_id_invalid',
    );
    $authoritySha256 = cpanelExecPattern(
        $plan['authority_binding']['authority_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'plan_authority_sha256_invalid',
    );
    $requestId = cpanelExecPattern(
        $plan['authority_binding']['request_id'] ?? null,
        '/\\Adurable-staging-deployment-request-[0-9a-f]{24}\\z/',
        'plan_request_id_invalid',
    );
    $requestSha256 = cpanelExecPattern(
        $plan['authority_binding']['request_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'plan_request_sha256_invalid',
    );
    cpanelExecPattern(
        $plan['authority_binding']['target_descriptor_sha256'] ?? null,
        '/\\A[0-9a-f]{64}\\z/',
        'plan_target_descriptor_sha256_invalid',
    );
    cpanelExecBool($plan['authority_binding']['deployment_allowed'] ?? null, true, 'plan_deployment_authority_missing');
    cpanelExecBool($plan['authority_binding']['migration_execution_allowed'] ?? null, false, 'plan_migration_authority_forbidden');
    cpanelExecBool($plan['authority_binding']['production_allowed'] ?? null, false, 'plan_production_authority_forbidden');

    $authorizedAt = $plan['authority_binding']['authorized_at_unix'] ?? null;
    $expiresAt = $plan['authority_binding']['expires_at_unix'] ?? null;
    if (! is_int($authorizedAt)
        || ! is_int($expiresAt)
        || $authorizedAt <= 0
        || $expiresAt <= $authorizedAt
        || ($expiresAt - $authorizedAt) > 900
    ) {
        cpanelExecFail('plan_authority_window_invalid');
    }

    $now = time();
    if ($now < $authorizedAt || $now >= $expiresAt) {
        cpanelExecFail('plan_authority_not_current');
    }

    cpanelExecLiteral($plan['readback_expectations']['environment_id'] ?? null, $environmentId, 'plan_readback_environment_mismatch');
    cpanelExecLiteral($plan['readback_expectations']['runtime_class'] ?? null, 'durable-staging', 'plan_readback_runtime_mismatch');
    cpanelExecLiteral($plan['readback_expectations']['running_source_commit'] ?? null, $sourceCommit, 'plan_readback_source_mismatch');
    cpanelExecLiteral($plan['readback_expectations']['running_artifact_sha256'] ?? null, $artifactSha256, 'plan_readback_artifact_mismatch');
    cpanelExecBool($plan['readback_expectations']['durable_staging_runtime_enabled'] ?? null, true, 'plan_readback_runtime_enabled_invalid');
    cpanelExecBool($plan['readback_expectations']['production_data_allowed'] ?? null, false, 'plan_readback_production_data_invalid');

    cpanelExecBool($plan['rollback']['required'] ?? null, true, 'plan_rollback_required');
    cpanelExecBool($plan['rollback']['previous_active_release_must_be_preserved'] ?? null, true, 'plan_previous_active_preservation_required');
    cpanelExecBool($plan['rollback']['rollback_must_be_verified_before_acceptance'] ?? null, true, 'plan_rollback_verification_required');
    cpanelExecBool($plan['rollback']['database_rollback_implied'] ?? null, false, 'plan_database_rollback_forbidden');
    cpanelExecBool($plan['rollback']['migration_rollback_implied'] ?? null, false, 'plan_migration_rollback_forbidden');

    cpanelExecLiteral($plan['migration']['expected_count'] ?? null, 27, 'plan_migration_count_invalid');
    cpanelExecLiteral($plan['migration']['execution_state'] ?? null, 'NOT_PERFORMED', 'plan_migration_state_invalid');
    cpanelExecBool($plan['migration']['execution_authorized'] ?? null, false, 'plan_migration_authority_invalid');

    return [
        'plan_fingerprint' => $fingerprint,
        'release_id' => $releaseId,
        'source_commit' => $sourceCommit,
        'artifact_sha256' => $artifactSha256,
        'environment_id' => $environmentId,
        'deployment_root' => $deploymentRoot,
        'release_root' => $releaseRoot,
        'release_directory' => $releaseDirectory,
        'shared_root' => $sharedRoot,
        'active_pointer' => $activePointer,
        'presentation_mode' => $presentationMode,
        'document_root' => $documentRoot,
        'authority_authorized_at' => $authorizedAt,
        'authority_expires_at' => $expiresAt,
        'authority_id' => $authorityId,
        'authority_sha256' => $authoritySha256,
        'request_id' => $requestId,
        'request_sha256' => $requestSha256,
    ];
}

function cpanelExecRequireAuthorityCurrent(array $identity): void
{
    $from = $identity['authority_authorized_at'] ?? null;
    $to = $identity['authority_expires_at'] ?? null;
    $now = time();
    if (! is_int($from) || ! is_int($to) || $now < $from || $now >= $to) {
        cpanelExecFail('authority_not_current_at_mutation');
    }
}

/** @return array<string,mixed> */
function cpanelExecValidateProfile(array $profile, array $identity): array
{
    cpanelExecLiteral($profile['schema_version'] ?? null, 1, 'profile_schema_version_invalid');
    cpanelExecLiteral($profile['profile_state'] ?? null, 'CPANEL_NO_SSH_TARGET_PROFILE_OBSERVED', 'profile_state_invalid');
    cpanelExecLiteral($profile['environment_id'] ?? null, $identity['environment_id'], 'profile_environment_mismatch');
    cpanelExecLiteral($profile['runtime_class'] ?? null, 'durable-staging', 'profile_runtime_invalid');
    cpanelExecBool($profile['production'] ?? null, false, 'profile_production_forbidden');
    cpanelExecBool($profile['production_data_allowed'] ?? null, false, 'profile_production_data_forbidden');
    cpanelExecBool($profile['synthetic_fixture_runtime'] ?? null, false, 'profile_synthetic_runtime_forbidden');
    cpanelExecLiteral($profile['execution_channel'] ?? null, 'CPANEL_CRON_PHP_CLI_NO_SSH', 'profile_execution_channel_invalid');
    cpanelExecLiteral($profile['release_binding']['source_commit'] ?? null, $identity['source_commit'], 'profile_source_mismatch');
    cpanelExecLiteral($profile['release_binding']['artifact_sha256'] ?? null, $identity['artifact_sha256'], 'profile_artifact_mismatch');

    foreach ([
        'deployment_root' => $identity['deployment_root'],
        'release_root' => $identity['release_root'],
        'shared_runtime_root' => $identity['shared_root'],
        'active_release_pointer' => $identity['active_pointer'],
    ] as $field => $expected) {
        cpanelExecLiteral($profile['filesystem'][$field] ?? null, $expected, 'profile_'.$field.'_mismatch');
    }

    $documentRoot = cpanelExecSafeAbsolutePath(
        $profile['filesystem']['document_root'] ?? null,
        'profile_document_root_invalid',
    );
    $presentationMode = cpanelExecPattern(
        $profile['presentation']['mode'] ?? null,
        '/\\A(?:ACTIVE_RELEASE_PUBLIC|FIXED_PUBLIC_BRIDGE)\\z/',
        'profile_presentation_mode_invalid',
    );
    $presentationDocumentRoot = cpanelExecSafeAbsolutePath(
        $profile['presentation']['document_root'] ?? null,
        'profile_presentation_document_root_invalid',
    );
    cpanelExecLiteral($presentationMode, $identity['presentation_mode'], 'profile_presentation_mode_mismatch');
    cpanelExecLiteral($presentationDocumentRoot, $identity['document_root'], 'profile_presentation_document_root_mismatch');
    cpanelExecLiteral($documentRoot, $identity['document_root'], 'profile_document_root_mismatch');

    foreach ([
        'deployment_root_writable',
        'release_root_writable',
        'shared_runtime_root_writable',
        'active_pointer_parent_writable',
        'atomic_rename_supported',
        'symlink_supported',
        'document_root_shape_valid',
    ] as $field) {
        cpanelExecBool($profile['filesystem'][$field] ?? null, true, 'profile_filesystem_'.$field.'_invalid');
    }

    cpanelExecBool($profile['runtime']['php_version_supported'] ?? null, true, 'profile_php_version_unsupported');
    cpanelExecBool($profile['runtime']['required_extensions_present'] ?? null, true, 'profile_required_extensions_missing');
    cpanelExecBool($profile['runtime']['pdo_mysql_available'] ?? null, true, 'profile_pdo_mysql_unavailable');

    foreach ([
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
    ] as $capability) {
        cpanelExecBool(
            $profile['operator_assertions'][$capability] ?? null,
            true,
            'profile_operator_capability_'.$capability.'_invalid',
        );
    }

    cpanelExecBool($profile['configuration']['binding_file_private'] ?? null, true, 'profile_binding_file_private_invalid');
    cpanelExecBool($profile['configuration']['required_binding_identity_matches'] ?? null, true, 'profile_binding_identity_invalid');
    cpanelExecBool($profile['configuration']['secret_values_embedded'] ?? null, false, 'profile_secret_forbidden');
    cpanelExecBool($profile['secrets_embedded'] ?? null, false, 'profile_top_secret_forbidden');
    cpanelExecLiteral($profile['attribution'] ?? null, 'Lab | zefry', 'profile_attribution_invalid');

    return [
        'document_root' => $documentRoot,
        'presentation_mode' => $presentationMode,
    ];
}

/** @return array<string,string> */
function cpanelExecValidateBindings(array $bindings, array $identity): array
{
    $expectedNames = [
        'ONEQAY_RUNTIME_CLASS',
        'ONEQAY_RUNNING_SOURCE_COMMIT',
        'ONEQAY_RUNNING_ARTIFACT_SHA256',
        'ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID',
        'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN',
        'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED',
    ];

    $actualNames = array_keys($bindings);
    sort($actualNames, SORT_STRING);
    $sortedExpected = $expectedNames;
    sort($sortedExpected, SORT_STRING);
    if ($actualNames !== $sortedExpected) {
        cpanelExecFail('binding_set_invalid');
    }

    foreach ($expectedNames as $name) {
        if (! is_string($bindings[$name] ?? null) || $bindings[$name] === '' || str_contains($bindings[$name], "\0")) {
            cpanelExecFail('binding_value_invalid');
        }
    }

    cpanelExecLiteral($bindings['ONEQAY_RUNTIME_CLASS'], 'durable-staging', 'binding_runtime_class_mismatch');
    cpanelExecLiteral($bindings['ONEQAY_RUNNING_SOURCE_COMMIT'], $identity['source_commit'], 'binding_source_mismatch');
    cpanelExecLiteral($bindings['ONEQAY_RUNNING_ARTIFACT_SHA256'], $identity['artifact_sha256'], 'binding_artifact_mismatch');
    cpanelExecLiteral($bindings['ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID'], $identity['environment_id'], 'binding_environment_mismatch');

    if (! in_array(strtolower($bindings['ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED']), ['1', 'true'], true)) {
        cpanelExecFail('binding_runtime_enabled_invalid');
    }

    $token = $bindings['ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN'];
    if (strlen($token) < 32 || strlen($token) > 256) {
        cpanelExecFail('binding_attestation_token_invalid');
    }

    return $bindings;
}

/** @return array{state:string,target:?string} */
function cpanelExecReadPreviousState(string $activePointer, string $releaseRoot, string $newRelease): array
{
    if (! file_exists($activePointer) && ! is_link($activePointer)) {
        return ['state' => 'ABSENT', 'target' => null];
    }

    if (! is_link($activePointer)) {
        cpanelExecFail('active_pointer_must_be_symlink_or_absent');
    }

    $target = realpath($activePointer);
    $releaseRootReal = realpath($releaseRoot);
    if (! is_string($target) || ! is_string($releaseRootReal) || ! is_dir($target)) {
        cpanelExecFail('previous_active_release_unavailable');
    }
    if (! str_starts_with($target.'/', rtrim($releaseRootReal, '/').'/')) {
        cpanelExecFail('previous_active_release_outside_release_root');
    }
    if ($target === $newRelease) {
        cpanelExecFail('new_release_already_active');
    }

    return ['state' => 'SYMLINK', 'target' => $target];
}

function cpanelExecAtomicPoint(string $activePointer, string $target): void
{
    if (! cpanelExecFunctionAvailable('symlink')) {
        cpanelExecFail('symlink_unavailable');
    }

    $parent = dirname($activePointer);
    if (! is_dir($parent) || ! is_writable($parent)) {
        cpanelExecFail('active_pointer_parent_not_writable');
    }

    $temp = $parent.'/.oneqay-active-'.bin2hex(random_bytes(8));
    try {
        if (! symlink($target, $temp)) {
            cpanelExecFail('active_pointer_temp_symlink_failed');
        }
        if (! rename($temp, $activePointer)) {
            cpanelExecFail('active_pointer_atomic_replace_failed');
        }
    } finally {
        if (is_link($temp)) {
            @unlink($temp);
        }
    }

    if (! is_link($activePointer) || realpath($activePointer) !== realpath($target)) {
        cpanelExecFail('active_pointer_verification_failed');
    }
}

/** @param array{state:string,target:?string} $previous */
function cpanelExecRestorePrevious(string $activePointer, array $previous): void
{
    if ($previous['state'] === 'ABSENT') {
        if (is_link($activePointer) && ! @unlink($activePointer)) {
            cpanelExecFail('rollback_to_absent_failed');
        }
        if (file_exists($activePointer) || is_link($activePointer)) {
            cpanelExecFail('rollback_to_absent_verification_failed');
        }
        return;
    }

    if ($previous['state'] !== 'SYMLINK' || ! is_string($previous['target']) || ! is_dir($previous['target'])) {
        cpanelExecFail('previous_state_invalid');
    }

    cpanelExecAtomicPoint($activePointer, $previous['target']);
}

/** @param array{state:string,target:?string} $previous */
function cpanelExecRollbackBestEffort(string $activePointer, array $previous): void
{
    try {
        cpanelExecRestorePrevious($activePointer, $previous);
    } catch (Throwable) {
        // Preserve original failure. Operator evidence will show no success artifact.
    }
}

function cpanelExecRemoveTree(string $path): void
{
    if (! file_exists($path) && ! is_link($path)) {
        return;
    }

    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $item) {
        $itemPath = $item->getPathname();
        if ($item->isLink() || $item->isFile()) {
            @unlink($itemPath);
        } else {
            @rmdir($itemPath);
        }
    }

    @rmdir($path);
}

/** @return array<string,mixed> */
function cpanelExecExtractRelease(string $archivePath, array $identity): array
{
    if (! is_file($archivePath) || is_link($archivePath) || ! is_readable($archivePath)) {
        cpanelExecFail('archive_unavailable');
    }

    $size = filesize($archivePath);
    if (! is_int($size) || $size < 1024 || $size > 134217728) {
        cpanelExecFail('archive_size_invalid');
    }

    cpanelExecLiteral(hash_file('sha256', $archivePath), $identity['artifact_sha256'], 'archive_sha256_mismatch');

    if (file_exists($identity['release_directory']) || is_link($identity['release_directory'])) {
        cpanelExecFail('release_directory_already_exists');
    }

    if (! is_dir($identity['release_root']) || ! is_writable($identity['release_root'])) {
        cpanelExecFail('release_root_not_writable');
    }

    $stageParent = $identity['release_root'].'/.oneqay-extract-'.bin2hex(random_bytes(8));
    if (! mkdir($stageParent, 0700)) {
        cpanelExecFail('extract_stage_create_failed');
    }

    try {
        try {
            $archive = new PharData($archivePath);
            $archive->extractTo($stageParent, null, false);
        } catch (Throwable) {
            cpanelExecFail('archive_extraction_failed');
        }

        $extracted = $stageParent.'/'.$identity['release_id'];
        if (! is_dir($extracted)) {
            cpanelExecFail('archive_release_root_missing');
        }

        foreach ([
            'RELEASE.json',
            'apps/web/public/index.php',
            'apps/web/public/.htaccess',
            'apps/web/vendor/autoload.php',
            'apps/web/bootstrap/app.php',
        ] as $required) {
            if (! is_file($extracted.'/'.$required)) {
                cpanelExecFail('extracted_required_file_missing');
            }
        }

        if (file_exists($extracted.'/apps/web/.env') || is_link($extracted.'/apps/web/.env')) {
            cpanelExecFail('embedded_runtime_env_forbidden');
        }

        $release = cpanelExecLoadJson($extracted.'/RELEASE.json');
        cpanelExecLiteral($release['product'] ?? null, 'oneQay', 'release_product_invalid');
        cpanelExecLiteral($release['release_id'] ?? null, $identity['release_id'], 'release_id_mismatch');
        cpanelExecLiteral($release['environment'] ?? null, 'DURABLE_STAGING', 'release_environment_invalid');
        cpanelExecLiteral($release['required_runtime_class'] ?? null, 'durable-staging', 'release_runtime_invalid');
        cpanelExecLiteral($release['source_commit'] ?? null, $identity['source_commit'], 'release_source_mismatch');
        cpanelExecLiteral($release['migration_count'] ?? null, 27, 'release_migration_count_invalid');
        cpanelExecLiteral(
            $release['latest_migration'] ?? null,
            '0000_00_00_000027_create_pos_shift_close_evidence_foundation.php',
            'release_migration_tail_invalid',
        );
        cpanelExecLiteral($release['migration_execution_state'] ?? null, 'NOT_EXECUTED_BY_ARTIFACT_BUILD', 'release_migration_state_invalid');
        cpanelExecBool($release['migration_execution_authorized'] ?? null, false, 'release_migration_authority_forbidden');

        $migrationFiles = glob($extracted.'/apps/web/database/migrations/*.php');
        if (! is_array($migrationFiles) || count($migrationFiles) !== 27) {
            cpanelExecFail('extracted_migration_count_invalid');
        }

        if (! rename($extracted, $identity['release_directory'])) {
            cpanelExecFail('release_atomic_commit_failed');
        }

        return ['release_directory' => $identity['release_directory'], 'stage_parent' => $stageParent];
    } finally {
        cpanelExecRemoveTree($stageParent);
    }
}

function cpanelExecBindRuntimeEnv(string $runtimeEnvPath, array $identity): string
{
    $runtimeEnvReal = cpanelExecPrivateFile(
        $runtimeEnvPath,
        $identity['shared_root'],
        'runtime_env',
    );

    $size = filesize($runtimeEnvReal);
    if (! is_int($size) || $size < 2 || $size > 1048576) {
        cpanelExecFail('runtime_env_size_invalid');
    }

    $envHashBefore = hash_file('sha256', $runtimeEnvReal);
    if (! is_string($envHashBefore)) {
        cpanelExecFail('runtime_env_hash_failed');
    }

    $envLink = $identity['release_directory'].'/apps/web/.env';
    if (file_exists($envLink) || is_link($envLink)) {
        cpanelExecFail('release_runtime_env_path_occupied');
    }

    if (! cpanelExecFunctionAvailable('symlink') || ! symlink($runtimeEnvReal, $envLink)) {
        cpanelExecFail('runtime_env_symlink_failed');
    }

    if (! is_link($envLink) || realpath($envLink) !== $runtimeEnvReal) {
        cpanelExecFail('runtime_env_symlink_verification_failed');
    }

    $envHashAfter = hash_file('sha256', $envLink);
    if (! is_string($envHashAfter) || ! hash_equals($envHashBefore, $envHashAfter)) {
        cpanelExecFail('runtime_env_read_after_mismatch');
    }

    return $envHashBefore;
}

/** @return array<string,mixed> */
function cpanelExecFetchReadiness(string $url, string $token): array
{
    $parts = parse_url($url);
    if (! is_array($parts)
        || ($parts['scheme'] ?? null) !== 'https'
        || ! is_string($parts['host'] ?? null)
        || $parts['host'] === ''
        || array_key_exists('user', $parts)
        || array_key_exists('pass', $parts)
        || array_key_exists('fragment', $parts)
    ) {
        cpanelExecFail('readiness_url_invalid');
    }

    $body = null;

    if (extension_loaded('curl') && function_exists('curl_init')) {
        $handle = curl_init($url);
        if ($handle === false) {
            cpanelExecFail('readiness_curl_init_failed');
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer '.$token,
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        ];

        $caFile = getenv('SSL_CERT_FILE');
        if (is_string($caFile) && $caFile !== '') {
            $options[CURLOPT_CAINFO] = $caFile;
        }

        curl_setopt_array($handle, $options);
        $result = curl_exec($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);

        if (! is_string($result) || $status !== 200) {
            cpanelExecFail($error === '' ? 'readiness_http_failed' : 'readiness_transport_failed');
        }
        $body = $result;
    } else {
        if (! filter_var((string) ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
            cpanelExecFail('https_client_unavailable');
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\nAuthorization: Bearer ".$token."\r\n",
                'timeout' => 20,
                'ignore_errors' => false,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);
        if (! is_string($result)) {
            cpanelExecFail('readiness_https_failed');
        }
        $body = $result;
    }

    if (strlen($body) < 2 || strlen($body) > 32768) {
        cpanelExecFail('readiness_response_size_invalid');
    }

    try {
        $decoded = json_decode($body, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        cpanelExecFail('readiness_json_invalid');
    }

    if (! is_array($decoded) || array_is_list($decoded)) {
        cpanelExecFail('readiness_shape_invalid');
    }

    return $decoded;
}

/** @param array<string,mixed> $attestation */
function cpanelExecValidateReadiness(array $attestation, array $identity): void
{
    $required = [
        'schema_version',
        'environment_id',
        'runtime_class',
        'runtime_model',
        'environment_isolation',
        'serving_application_runtime',
        'synthetic_fixture_runtime',
        'production_traffic_served',
        'durable_persistence_enabled',
        'durable_session_control_enabled',
        'durable_authorization_enabled',
        'durable_transaction_boundary_enabled',
        'durable_pos_persistence_enabled',
        'exact_running_source_commit',
        'exact_running_artifact_sha256',
        'authenticated_configuration_mutation_channel',
        'read_before_write_read_after_supported',
        'non_mutating_health_attestation_supported',
        'verified_flag_rollback_supported',
        'activation_authority_binding',
        'feature_activation_state',
        'secrets_embedded',
    ];

    $actual = array_keys($attestation);
    sort($actual, SORT_STRING);
    $expected = $required;
    sort($expected, SORT_STRING);
    if ($actual !== $expected) {
        cpanelExecFail('readiness_field_set_invalid');
    }

    cpanelExecLiteral($attestation['schema_version'], 1, 'readiness_schema_invalid');
    cpanelExecLiteral($attestation['environment_id'], $identity['environment_id'], 'readiness_environment_mismatch');
    cpanelExecLiteral($attestation['runtime_class'], 'durable-staging', 'readiness_runtime_mismatch');
    cpanelExecLiteral($attestation['runtime_model'], 'NON_SYNTHETIC_DURABLE_RUNTIME', 'readiness_model_invalid');
    cpanelExecLiteral($attestation['environment_isolation'], 'ISOLATED_NON_PRODUCTION', 'readiness_isolation_invalid');
    cpanelExecBool($attestation['serving_application_runtime'], true, 'readiness_serving_runtime_required');
    cpanelExecBool($attestation['synthetic_fixture_runtime'], false, 'readiness_synthetic_forbidden');
    cpanelExecBool($attestation['production_traffic_served'], false, 'readiness_production_traffic_forbidden');

    foreach ([
        'durable_persistence_enabled',
        'durable_session_control_enabled',
        'durable_authorization_enabled',
        'durable_transaction_boundary_enabled',
        'durable_pos_persistence_enabled',
        'authenticated_configuration_mutation_channel',
        'read_before_write_read_after_supported',
        'non_mutating_health_attestation_supported',
        'verified_flag_rollback_supported',
    ] as $field) {
        cpanelExecBool($attestation[$field], true, 'readiness_capability_'.$field.'_required');
    }

    cpanelExecLiteral($attestation['exact_running_source_commit'], $identity['source_commit'], 'readiness_source_mismatch');
    cpanelExecLiteral($attestation['exact_running_artifact_sha256'], $identity['artifact_sha256'], 'readiness_artifact_mismatch');
    cpanelExecLiteral(
        $attestation['activation_authority_binding'],
        'SEPARATE_EXPLICIT_ACTIVATION_AUTHORITY',
        'readiness_activation_authority_binding_invalid',
    );
    cpanelExecLiteral($attestation['feature_activation_state'], 'INACTIVE', 'readiness_feature_state_invalid');
    cpanelExecBool($attestation['secrets_embedded'], false, 'readiness_secret_forbidden');
}

/** @param array<string,mixed> $payload */
function cpanelExecWriteJson(string $path, array $payload): void
{
    if ($path === '' || is_link($path) || is_dir($path)) {
        cpanelExecFail('output_path_invalid');
    }

    $directory = dirname($path);
    if (! is_dir($directory) || ! is_writable($directory)) {
        cpanelExecFail('output_directory_invalid');
    }

    $json = json_encode(
        $payload,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ).PHP_EOL;

    $temp = tempnam($directory, '.oneqay-cpanel-evidence-');
    if ($temp === false) {
        cpanelExecFail('output_temp_create_failed');
    }

    try {
        if (file_put_contents($temp, $json, LOCK_EX) !== strlen($json)) {
            cpanelExecFail('output_write_failed');
        }
        @chmod($temp, 0600);
        if (! rename($temp, $path)) {
            cpanelExecFail('output_commit_failed');
        }
        @chmod($path, 0600);
    } finally {
        if (is_file($temp)) {
            @unlink($temp);
        }
    }
}

/**
 * @param callable(string,string):array<string,mixed> $readinessFetcher
 * @return array<string,mixed>
 */
function cpanelExecExecute(
    string $planPath,
    string $profilePath,
    string $archivePath,
    string $bindingsPath,
    string $runtimeEnvPath,
    string $readinessUrl,
    string $evidencePath,
    callable $readinessFetcher,
): array {
    $activePointer = null;
    $previousState = ['state' => 'ABSENT', 'target' => null];
    $pointerMutated = false;
    $bridgeState = null;
    $bridgeMutated = false;

    try {
        $plan = cpanelExecLoadJson($planPath);
        $profile = cpanelExecLoadJson($profilePath);
        $identity = cpanelExecValidatePlan($plan);
        $profileIdentity = cpanelExecValidateProfile($profile, $identity);

        foreach ([
            $identity['deployment_root'],
            $identity['release_root'],
            $identity['shared_root'],
            dirname($identity['active_pointer']),
        ] as $directory) {
            if (! is_dir($directory) || ! is_writable($directory)) {
                cpanelExecFail('required_target_directory_not_writable');
            }
        }

        $privateBindingsPath = cpanelExecPrivateFile($bindingsPath, $identity['shared_root'], 'private_bindings');
        $bindings = cpanelExecValidateBindings(cpanelExecLoadJson($privateBindingsPath, true), $identity);

        $activePointer = $identity['active_pointer'];
        $previousState = cpanelExecReadPreviousState(
            $activePointer,
            $identity['release_root'],
            $identity['release_directory'],
        );

        cpanelExecExtractRelease($archivePath, $identity);
        $runtimeEnvSha256 = cpanelExecBindRuntimeEnv($runtimeEnvPath, $identity);

        $documentRoot = $profileIdentity['document_root'];
        if ($identity['presentation_mode'] === 'FIXED_PUBLIC_BRIDGE') {
            $bridgeState = cpanelBridgeInstall(
                $documentRoot,
                $activePointer,
                $identity['release_directory'],
                $identity['shared_root'],
            );
            $bridgeMutated = true;
        }

        cpanelExecRequireAuthorityCurrent($identity);
        cpanelExecAtomicPoint($activePointer, $identity['release_directory']);
        $pointerMutated = true;

        if ($identity['presentation_mode'] === 'ACTIVE_RELEASE_PUBLIC') {
            $resolvedDocumentRoot = realpath($documentRoot);
            $expectedDocumentRoot = realpath($identity['release_directory'].'/apps/web/public');
            if (! is_string($resolvedDocumentRoot)
                || ! is_string($expectedDocumentRoot)
                || $resolvedDocumentRoot !== $expectedDocumentRoot
            ) {
                cpanelExecFail('public_document_root_verification_failed');
            }
        } else {
            if (! is_file($documentRoot.'/index.php')
                || is_link($documentRoot.'/index.php')
                || ! is_file($documentRoot.'/build/manifest.json')
            ) {
                cpanelExecFail('fixed_public_bridge_verification_failed');
            }
        }

        $firstAttestation = $readinessFetcher(
            $readinessUrl,
            $bindings['ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN'],
        );
        if (! is_array($firstAttestation) || array_is_list($firstAttestation)) {
            cpanelExecFail('readiness_fetcher_shape_invalid');
        }
        cpanelExecValidateReadiness($firstAttestation, $identity);

        cpanelExecRestorePrevious($activePointer, $previousState);
        if ($identity['presentation_mode'] === 'FIXED_PUBLIC_BRIDGE') {
            if (! is_array($bridgeState)) {
                cpanelExecFail('fixed_public_bridge_state_missing');
            }
            cpanelBridgeRestore($bridgeState);
            cpanelBridgeFinalize($bridgeState);
            $bridgeMutated = false;

            $bridgeState = cpanelBridgeInstall(
                $documentRoot,
                $activePointer,
                $identity['release_directory'],
                $identity['shared_root'],
            );
            $bridgeMutated = true;
        }

        cpanelExecRequireAuthorityCurrent($identity);
        cpanelExecAtomicPoint($activePointer, $identity['release_directory']);

        $secondAttestation = $readinessFetcher(
            $readinessUrl,
            $bindings['ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN'],
        );
        if (! is_array($secondAttestation) || array_is_list($secondAttestation)) {
            cpanelExecFail('readiness_fetcher_shape_invalid');
        }
        cpanelExecValidateReadiness($secondAttestation, $identity);

        $runtimeEnvSha256After = hash_file('sha256', $identity['release_directory'].'/apps/web/.env');
        if (! is_string($runtimeEnvSha256After) || ! hash_equals($runtimeEnvSha256, $runtimeEnvSha256After)) {
            cpanelExecFail('runtime_env_post_reactivation_readback_failed');
        }

        $evidence = [
            'schema_version' => 1,
            'product' => 'oneQay',
            'evidence_state' => 'DEPLOYED_VERIFIED_NOT_SELECTED',
            'environment_id' => $identity['environment_id'],
            'runtime_class' => 'durable-staging',
            'release_id' => $identity['release_id'],
            'source_commit' => $identity['source_commit'],
            'artifact_sha256' => $identity['artifact_sha256'],
            'deployment_plan_fingerprint' => $identity['plan_fingerprint'],
            'deployment_authority_id' => $identity['authority_id'],
            'deployment_authority_sha256' => $identity['authority_sha256'],
            'deployment_request_id' => $identity['request_id'],
            'deployment_request_sha256' => $identity['request_sha256'],
            'verification' => [
                'preflight_passed' => true,
                'previous_active_release_preserved' => true,
                'immutable_release_extracted' => true,
                'public_document_root_verified' => true,
                'external_runtime_configuration_bound' => true,
                'provenance_readback_verified' => true,
                'configuration_read_before_write_verified' => true,
                'configuration_read_after_write_verified' => true,
                'non_mutating_health_attestation_verified' => true,
                'rollback_path_verified' => true,
            ],
            'runtime_readback' => [
                'environment_id' => $secondAttestation['environment_id'],
                'runtime_class' => $secondAttestation['runtime_class'],
                'exact_running_source_commit' => $secondAttestation['exact_running_source_commit'],
                'exact_running_artifact_sha256' => $secondAttestation['exact_running_artifact_sha256'],
                'durable_staging_runtime_enabled' => true,
                'production_data_allowed' => false,
            ],
            'operational_boundary' => [
                'migration27_execution' => 'NOT_PERFORMED',
                'permission_provisioning' => 'NONE',
                'feature_activation' => 'INACTIVE',
                'technical_preview_activation' => 'NOT_AUTHORIZED',
                'production_activation' => 'NOT_AUTHORIZED',
                'updater_activation' => 'INACTIVE',
                'target_selection' => 'NOT_PERFORMED',
                'selected_target' => null,
                'producer_dispatch' => 'NOT_PERFORMED',
            ],
            'secrets_embedded' => false,
            'attribution' => 'Lab | zefry',
        ];

        cpanelExecWriteJson($evidencePath, $evidence);
        if ($bridgeMutated && is_array($bridgeState)) {
            cpanelBridgeFinalize($bridgeState);
            $bridgeMutated = false;
        }
        $pointerMutated = false;

        return $evidence;
    } catch (Throwable $failure) {
        if ($pointerMutated && is_string($activePointer)) {
            cpanelExecRollbackBestEffort($activePointer, $previousState);
        }
        if ($bridgeMutated && is_array($bridgeState)) {
            try {
                cpanelBridgeRestore($bridgeState);
                cpanelBridgeFinalize($bridgeState);
            } catch (Throwable) {
            }
        }

        if (is_file($evidencePath)) {
            @unlink($evidencePath);
        }

        throw $failure;
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 8) {
        fwrite(
            STDERR,
            "Usage: php tools/cpanel/execute-durable-staging-cpanel-no-ssh-deployment.php <deployment-plan.json> <target-profile.json> <artifact.tar.gz> <private-bindings.json> <private-runtime-env> <readiness-url> <deployment-evidence.json>\n",
        );
        exit(64);
    }

    try {
        cpanelExecExecute(
            $argv[1],
            $argv[2],
            $argv[3],
            $argv[4],
            $argv[5],
            $argv[6],
            $argv[7],
            static fn (string $url, string $token): array => cpanelExecFetchReadiness($url, $token),
        );

        fwrite(STDOUT, "cpanel_no_ssh_durable_staging_deployed_verified_not_selected\n");
        exit(0);
    } catch (Throwable $failure) {
        $code = $failure instanceof CpanelNoSshDeploymentExecutionException
            ? $failure->getMessage()
            : 'unexpected_failure';
        fwrite(STDERR, "cpanel_no_ssh_deployment_execution_failed:".$code."\n");
        exit(1);
    }
}
