<?php

declare(strict_types=1);

// Author by Lab | zefry

final class DurableStagingDeploymentAuthorityRequestException extends RuntimeException
{
}

function dsAuthorityRequestFail(string $code): never
{
    throw new DurableStagingDeploymentAuthorityRequestException($code);
}

/** @return array<string,mixed> */
function dsAuthorityRequestLoadJson(string $path): array
{
    if ($path === '' || ! is_file($path) || is_link($path)) {
        dsAuthorityRequestFail('input_unavailable');
    }
    $size = filesize($path);
    if (! is_int($size) || $size < 2 || $size > 262144) {
        dsAuthorityRequestFail('input_size_invalid');
    }
    $raw = file_get_contents($path);
    if (! is_string($raw)) {
        dsAuthorityRequestFail('input_read_failed');
    }
    try {
        $decoded = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        dsAuthorityRequestFail('input_json_invalid');
    }
    if (! is_array($decoded) || array_is_list($decoded)) {
        dsAuthorityRequestFail('input_json_shape_invalid');
    }
    return $decoded;
}

function dsAuthorityRequestAssertLiteral(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) {
        dsAuthorityRequestFail($code);
    }
}

function dsAuthorityRequestAssertBool(mixed $value, bool $expected, string $code): void
{
    if (! is_bool($value) || $value !== $expected) {
        dsAuthorityRequestFail($code);
    }
}

function dsAuthorityRequestAssertPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) {
        dsAuthorityRequestFail($code);
    }
    return $value;
}

function dsAuthorityRequestSafePath(mixed $value, string $code): string
{
    if (! is_string($value)
        || $value === ''
        || strlen($value) > 4096
        || ! str_starts_with($value, '/')
        || $value === '/'
        || str_ends_with($value, '/')
        || str_contains($value, "\0")
        || str_contains($value, '\\')
        || preg_match('#(?:^|/)\\.{1,2}(?:/|$)#', $value) === 1
        || preg_match('#//+#', $value) === 1
    ) {
        dsAuthorityRequestFail($code);
    }
    return rtrim($value, '/');
}

/** @return array<string,mixed>|list<mixed> */
function dsAuthorityRequestCanonicalize(array $value): array
{
    if (array_is_list($value)) {
        return array_map(
            static fn (mixed $item): mixed => is_array($item) ? dsAuthorityRequestCanonicalize($item) : $item,
            $value,
        );
    }
    ksort($value, SORT_STRING);
    foreach ($value as $key => $item) {
        if (is_array($item)) {
            $value[$key] = dsAuthorityRequestCanonicalize($item);
        }
    }
    return $value;
}

function dsAuthorityRequestCanonicalJson(array $value): string
{
    $encoded = json_encode(
        dsAuthorityRequestCanonicalize($value),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
    if (! is_string($encoded)) {
        dsAuthorityRequestFail('canonical_json_failed');
    }
    return $encoded;
}

/** @return array{release_id:string,source_commit:string,artifact_sha256:string,manifest_sha256:string} */
function dsAuthorityRequestValidateHandoff(array $handoff): array
{
    dsAuthorityRequestAssertLiteral($handoff['schema_version'] ?? null, 1, 'handoff_schema_version_invalid');
    dsAuthorityRequestAssertLiteral($handoff['handoff_state'] ?? null, 'VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED', 'handoff_state_invalid');
    dsAuthorityRequestAssertLiteral($handoff['product']['name'] ?? null, 'oneQay', 'handoff_product_invalid');
    dsAuthorityRequestAssertLiteral($handoff['product']['repository'] ?? null, 'labzefry/oneQay', 'handoff_repository_invalid');

    $releaseId = dsAuthorityRequestAssertPattern($handoff['artifact']['release_id'] ?? null, '/\\Adurable-staging-[0-9a-f]{12}\\z/', 'handoff_release_id_invalid');
    $sourceCommit = dsAuthorityRequestAssertPattern($handoff['artifact']['source_commit'] ?? null, '/\\A[0-9a-f]{40}\\z/', 'handoff_source_commit_invalid');
    $artifactSha = dsAuthorityRequestAssertPattern($handoff['artifact']['artifact_sha256'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'handoff_artifact_sha256_invalid');
    $manifestSha = dsAuthorityRequestAssertPattern($handoff['artifact']['manifest_sha256'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'handoff_manifest_sha256_invalid');

    dsAuthorityRequestAssertLiteral($releaseId, 'durable-staging-'.substr($sourceCommit, 0, 12), 'handoff_release_source_mismatch');
    dsAuthorityRequestAssertLiteral($handoff['runtime']['required_runtime_class'] ?? null, 'durable-staging', 'handoff_runtime_class_invalid');
    dsAuthorityRequestAssertBool($handoff['runtime']['production'] ?? null, false, 'handoff_production_forbidden');
    dsAuthorityRequestAssertBool($handoff['runtime']['production_data_allowed'] ?? null, false, 'handoff_production_data_forbidden');
    dsAuthorityRequestAssertBool($handoff['runtime']['synthetic_fixture_runtime'] ?? null, false, 'handoff_synthetic_runtime_forbidden');
    dsAuthorityRequestAssertLiteral($handoff['migration']['expected_count'] ?? null, 27, 'handoff_migration_count_invalid');
    dsAuthorityRequestAssertLiteral($handoff['migration']['execution_state'] ?? null, 'NOT_PERFORMED', 'handoff_migration_state_invalid');
    dsAuthorityRequestAssertBool($handoff['migration']['execution_authorized'] ?? null, false, 'handoff_migration_authority_forbidden');
    dsAuthorityRequestAssertLiteral($handoff['deployment']['execution_state'] ?? null, 'NOT_PERFORMED', 'handoff_deployment_state_invalid');
    dsAuthorityRequestAssertLiteral($handoff['deployment']['authority_state'] ?? null, 'NOT_GRANTED', 'handoff_embedded_authority_forbidden');

    return [
        'release_id' => $releaseId,
        'source_commit' => $sourceCommit,
        'artifact_sha256' => $artifactSha,
        'manifest_sha256' => $manifestSha,
    ];
}

/** @return array{environment_id:string,target_descriptor_sha256:string} */
function dsAuthorityRequestValidateCandidate(array $candidate): array
{
    dsAuthorityRequestAssertLiteral($candidate['schema_version'] ?? null, 1, 'candidate_schema_version_invalid');
    dsAuthorityRequestAssertLiteral($candidate['target_state'] ?? null, 'OPERATOR_TARGET_CANDIDATE', 'candidate_state_invalid');
    $environmentId = dsAuthorityRequestAssertPattern($candidate['environment_id'] ?? null, '/\\A[a-z0-9][a-z0-9-]{2,62}\\z/', 'candidate_environment_id_invalid');
    dsAuthorityRequestAssertLiteral($candidate['runtime_class'] ?? null, 'durable-staging', 'candidate_runtime_class_invalid');
    dsAuthorityRequestAssertBool($candidate['production'] ?? null, false, 'candidate_production_forbidden');
    dsAuthorityRequestAssertBool($candidate['production_data_allowed'] ?? null, false, 'candidate_production_data_forbidden');
    dsAuthorityRequestAssertBool($candidate['synthetic_fixture_runtime'] ?? null, false, 'candidate_synthetic_runtime_forbidden');
    dsAuthorityRequestAssertLiteral($candidate['attribution'] ?? null, 'Lab | zefry', 'candidate_attribution_invalid');

    $deploymentRoot = dsAuthorityRequestSafePath($candidate['filesystem']['deployment_root'] ?? null, 'candidate_deployment_root_invalid');
    $releaseRoot = dsAuthorityRequestSafePath($candidate['filesystem']['release_root'] ?? null, 'candidate_release_root_invalid');
    $sharedRoot = dsAuthorityRequestSafePath($candidate['filesystem']['shared_runtime_root'] ?? null, 'candidate_shared_root_invalid');
    $activePointer = dsAuthorityRequestSafePath($candidate['filesystem']['active_release_pointer'] ?? null, 'candidate_active_pointer_invalid');

    foreach ([$releaseRoot, $sharedRoot, $activePointer] as $path) {
        if (! str_starts_with($path.'/', $deploymentRoot.'/')) {
            dsAuthorityRequestFail('candidate_filesystem_escape');
        }
    }
    if (count(array_unique([$deploymentRoot, $releaseRoot, $sharedRoot, $activePointer], SORT_STRING)) !== 4) {
        dsAuthorityRequestFail('candidate_filesystem_collision');
    }

    $presentation = $candidate['presentation'] ?? null;
    if ($presentation !== null) {
        if (! is_array($presentation) || array_is_list($presentation) || count($presentation) !== 2) {
            dsAuthorityRequestFail('candidate_presentation_invalid');
        }
        $mode = dsAuthorityRequestAssertPattern(
            $presentation['mode'] ?? null,
            '/\\A(?:ACTIVE_RELEASE_PUBLIC|FIXED_PUBLIC_BRIDGE)\\z/',
            'candidate_presentation_mode_invalid',
        );
        $documentRoot = dsAuthorityRequestSafePath(
            $presentation['document_root'] ?? null,
            'candidate_presentation_document_root_invalid',
        );
        if ($mode === 'ACTIVE_RELEASE_PUBLIC') {
            dsAuthorityRequestAssertLiteral(
                $documentRoot,
                $activePointer.'/apps/web/public',
                'candidate_presentation_document_root_mismatch',
            );
        } elseif (str_starts_with($documentRoot.'/', $deploymentRoot.'/')
            || str_starts_with($deploymentRoot.'/', $documentRoot.'/')
        ) {
            dsAuthorityRequestFail('candidate_fixed_public_document_root_not_disjoint');
        }
    }

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
        dsAuthorityRequestAssertBool($candidate['capabilities'][$capability] ?? null, true, 'candidate_capability_missing');
    }

    dsAuthorityRequestAssertBool($candidate['configuration']['secret_values_embedded'] ?? null, false, 'candidate_embedded_secret_forbidden');
    $presence = $candidate['configuration']['required_binding_presence'] ?? null;
    if (! is_array($presence) || array_is_list($presence)) {
        dsAuthorityRequestFail('candidate_binding_presence_invalid');
    }

    $requiredBindings = [
        'ONEQAY_RUNTIME_CLASS',
        'ONEQAY_RUNNING_SOURCE_COMMIT',
        'ONEQAY_RUNNING_ARTIFACT_SHA256',
        'ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID',
        'ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN',
        'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED',
    ];
    foreach ($requiredBindings as $binding) {
        dsAuthorityRequestAssertBool($presence[$binding] ?? null, true, 'candidate_required_binding_missing');
    }
    if (count($presence) !== count($requiredBindings)) {
        dsAuthorityRequestFail('candidate_binding_presence_extra');
    }

    return [
        'environment_id' => $environmentId,
        'target_descriptor_sha256' => hash('sha256', dsAuthorityRequestCanonicalJson($candidate)),
    ];
}

/** @return array<string,mixed> */
function dsAuthorityRequestPrepare(string $handoffPath, string $candidatePath): array
{
    $handoff = dsAuthorityRequestLoadJson($handoffPath);
    $candidate = dsAuthorityRequestLoadJson($candidatePath);
    $artifact = dsAuthorityRequestValidateHandoff($handoff);
    $target = dsAuthorityRequestValidateCandidate($candidate);

    $requestId = 'durable-staging-deployment-request-'.substr(
        hash('sha256', implode('|', [
            $artifact['release_id'],
            $artifact['source_commit'],
            $artifact['artifact_sha256'],
            $target['environment_id'],
            $target['target_descriptor_sha256'],
        ])),
        0,
        24,
    );

    return [
        'schema_version' => 1,
        'product' => 'oneQay',
        'request_state' => 'DURABLE_STAGING_DEPLOYMENT_AUTHORITY_REQUEST_PENDING_APPROVAL',
        'request_id' => $requestId,
        'requested_scope' => 'DEPLOY_EXACT_DURABLE_STAGING_ARTIFACT_TO_EXACT_NON_PRODUCTION_TARGET',
        'release_id' => $artifact['release_id'],
        'source_commit' => $artifact['source_commit'],
        'artifact_sha256' => $artifact['artifact_sha256'],
        'manifest_sha256' => $artifact['manifest_sha256'],
        'environment_id' => $target['environment_id'],
        'target_descriptor_sha256' => $target['target_descriptor_sha256'],
        'required_authority' => [
            'state' => 'NOT_GRANTED',
            'separate_operational_authority_required' => true,
            'exact_request_required' => true,
            'exact_target_descriptor_required' => true,
            'exact_environment_required' => true,
            'exact_release_required' => true,
            'exact_artifact_required' => true,
            'approval_token_required' => true,
            'maximum_lifetime_seconds' => 900,
        ],
        'migration_execution_authorized' => false,
        'production_authorized' => false,
        'technical_preview_activation_authorized' => false,
        'updater_activation_authorized' => false,
        'target_selection_authorized' => false,
        'producer_dispatch_authorized' => false,
        'attribution' => 'Lab | zefry',
    ];
}

function dsAuthorityRequestWrite(string $outputPath, array $payload): void
{
    if ($outputPath === '' || is_link($outputPath) || is_dir($outputPath)) {
        dsAuthorityRequestFail('output_path_invalid');
    }
    $directory = dirname($outputPath);
    if (! is_dir($directory) || ! is_writable($directory)) {
        dsAuthorityRequestFail('output_directory_invalid');
    }
    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    $temp = tempnam($directory, '.oneqay-authority-request-');
    if ($temp === false) {
        dsAuthorityRequestFail('output_temp_create_failed');
    }
    try {
        if (file_put_contents($temp, $encoded, LOCK_EX) !== strlen($encoded)) {
            dsAuthorityRequestFail('output_write_failed');
        }
        @chmod($temp, 0600);
        if (! rename($temp, $outputPath)) {
            dsAuthorityRequestFail('output_commit_failed');
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
        fwrite(STDERR, "Usage: php tools/prepare-durable-staging-deployment-authority-request.php <handoff.json> <target-candidate.json> <request.json>\n");
        exit(64);
    }

    try {
        $payload = dsAuthorityRequestPrepare($argv[1], $argv[2]);
        dsAuthorityRequestWrite($argv[3], $payload);
        fwrite(STDOUT, "durable_staging_deployment_authority_request_prepared\n");
        exit(0);
    } catch (DurableStagingDeploymentAuthorityRequestException $failure) {
        fwrite(STDERR, "durable_staging_deployment_authority_request_failed:".$failure->getMessage()."\n");
        exit(1);
    } catch (Throwable) {
        fwrite(STDERR, "durable_staging_deployment_authority_request_failed:unexpected_failure\n");
        exit(1);
    }
}
