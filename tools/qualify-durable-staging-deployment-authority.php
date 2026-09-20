<?php

declare(strict_types=1);

// Author by Lab | zefry

final class DurableStagingDeploymentAuthorityQualificationException extends RuntimeException
{
}

function dsAuthorityQualifyFail(string $code): never
{
    throw new DurableStagingDeploymentAuthorityQualificationException($code);
}

/** @return array{decoded:array<string,mixed>,raw:string} */
function dsAuthorityQualifyLoadJson(string $path): array
{
    if ($path === '' || ! is_file($path) || is_link($path)) {
        dsAuthorityQualifyFail('input_unavailable');
    }
    $size = filesize($path);
    if (! is_int($size) || $size < 2 || $size > 262144) {
        dsAuthorityQualifyFail('input_size_invalid');
    }
    $raw = file_get_contents($path);
    if (! is_string($raw)) {
        dsAuthorityQualifyFail('input_read_failed');
    }
    try {
        $decoded = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        dsAuthorityQualifyFail('input_json_invalid');
    }
    if (! is_array($decoded) || array_is_list($decoded)) {
        dsAuthorityQualifyFail('input_json_shape_invalid');
    }
    return ['decoded' => $decoded, 'raw' => $raw];
}

function dsAuthorityQualifyAssertLiteral(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) {
        dsAuthorityQualifyFail($code);
    }
}

function dsAuthorityQualifyAssertBool(mixed $value, bool $expected, string $code): void
{
    if (! is_bool($value) || $value !== $expected) {
        dsAuthorityQualifyFail($code);
    }
}

function dsAuthorityQualifyPattern(mixed $value, string $pattern, string $code): string
{
    if (! is_string($value) || preg_match($pattern, $value) !== 1) {
        dsAuthorityQualifyFail($code);
    }
    return $value;
}

function dsAuthorityQualifySafePath(mixed $value, string $code): string
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
        dsAuthorityQualifyFail($code);
    }
    return rtrim($value, '/');
}

/** @return array<string,mixed>|list<mixed> */
function dsAuthorityQualifyCanonicalize(array $value): array
{
    if (array_is_list($value)) {
        return array_map(
            static fn (mixed $item): mixed => is_array($item) ? dsAuthorityQualifyCanonicalize($item) : $item,
            $value,
        );
    }
    ksort($value, SORT_STRING);
    foreach ($value as $key => $item) {
        if (is_array($item)) {
            $value[$key] = dsAuthorityQualifyCanonicalize($item);
        }
    }
    return $value;
}

function dsAuthorityQualifyCanonicalJson(array $value): string
{
    $encoded = json_encode(
        dsAuthorityQualifyCanonicalize($value),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
    if (! is_string($encoded)) {
        dsAuthorityQualifyFail('canonical_json_failed');
    }
    return $encoded;
}

/** @return array{release_id:string,source_commit:string,artifact_sha256:string,manifest_sha256:string} */
function dsAuthorityQualifyHandoff(array $handoff): array
{
    dsAuthorityQualifyAssertLiteral($handoff['schema_version'] ?? null, 1, 'handoff_schema_version_invalid');
    dsAuthorityQualifyAssertLiteral($handoff['handoff_state'] ?? null, 'VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED', 'handoff_state_invalid');
    dsAuthorityQualifyAssertLiteral($handoff['product']['name'] ?? null, 'oneQay', 'handoff_product_invalid');
    dsAuthorityQualifyAssertLiteral($handoff['product']['repository'] ?? null, 'labzefry/oneQay', 'handoff_repository_invalid');

    $releaseId = dsAuthorityQualifyPattern($handoff['artifact']['release_id'] ?? null, '/\\Adurable-staging-[0-9a-f]{12}\\z/', 'handoff_release_invalid');
    $source = dsAuthorityQualifyPattern($handoff['artifact']['source_commit'] ?? null, '/\\A[0-9a-f]{40}\\z/', 'handoff_source_invalid');
    $artifact = dsAuthorityQualifyPattern($handoff['artifact']['artifact_sha256'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'handoff_artifact_invalid');
    $manifest = dsAuthorityQualifyPattern($handoff['artifact']['manifest_sha256'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'handoff_manifest_invalid');

    dsAuthorityQualifyAssertLiteral($releaseId, 'durable-staging-'.substr($source, 0, 12), 'handoff_release_source_mismatch');
    dsAuthorityQualifyAssertLiteral($handoff['runtime']['required_runtime_class'] ?? null, 'durable-staging', 'handoff_runtime_invalid');
    dsAuthorityQualifyAssertBool($handoff['runtime']['production'] ?? null, false, 'handoff_production_forbidden');
    dsAuthorityQualifyAssertBool($handoff['runtime']['production_data_allowed'] ?? null, false, 'handoff_production_data_forbidden');
    dsAuthorityQualifyAssertBool($handoff['runtime']['synthetic_fixture_runtime'] ?? null, false, 'handoff_synthetic_forbidden');
    dsAuthorityQualifyAssertLiteral($handoff['migration']['expected_count'] ?? null, 27, 'handoff_migration_count_invalid');
    dsAuthorityQualifyAssertLiteral($handoff['migration']['execution_state'] ?? null, 'NOT_PERFORMED', 'handoff_migration_state_invalid');
    dsAuthorityQualifyAssertBool($handoff['migration']['execution_authorized'] ?? null, false, 'handoff_migration_authority_forbidden');
    dsAuthorityQualifyAssertLiteral($handoff['deployment']['execution_state'] ?? null, 'NOT_PERFORMED', 'handoff_deployment_state_invalid');
    dsAuthorityQualifyAssertLiteral($handoff['deployment']['authority_state'] ?? null, 'NOT_GRANTED', 'handoff_authority_state_invalid');

    return ['release_id'=>$releaseId,'source_commit'=>$source,'artifact_sha256'=>$artifact,'manifest_sha256'=>$manifest];
}

/** @return array{environment_id:string,target_descriptor_sha256:string} */
function dsAuthorityQualifyCandidate(array $candidate): array
{
    dsAuthorityQualifyAssertLiteral($candidate['schema_version'] ?? null, 1, 'candidate_schema_invalid');
    dsAuthorityQualifyAssertLiteral($candidate['target_state'] ?? null, 'OPERATOR_TARGET_CANDIDATE', 'candidate_state_invalid');
    $environment = dsAuthorityQualifyPattern($candidate['environment_id'] ?? null, '/\\A[a-z0-9][a-z0-9-]{2,62}\\z/', 'candidate_environment_invalid');
    dsAuthorityQualifyAssertLiteral($candidate['runtime_class'] ?? null, 'durable-staging', 'candidate_runtime_invalid');
    dsAuthorityQualifyAssertBool($candidate['production'] ?? null, false, 'candidate_production_forbidden');
    dsAuthorityQualifyAssertBool($candidate['production_data_allowed'] ?? null, false, 'candidate_production_data_forbidden');
    dsAuthorityQualifyAssertBool($candidate['synthetic_fixture_runtime'] ?? null, false, 'candidate_synthetic_forbidden');
    dsAuthorityQualifyAssertLiteral($candidate['attribution'] ?? null, 'Lab | zefry', 'candidate_attribution_invalid');

    $deploymentRoot = dsAuthorityQualifySafePath($candidate['filesystem']['deployment_root'] ?? null, 'candidate_deployment_root_invalid');
    $releaseRoot = dsAuthorityQualifySafePath($candidate['filesystem']['release_root'] ?? null, 'candidate_release_root_invalid');
    $sharedRoot = dsAuthorityQualifySafePath($candidate['filesystem']['shared_runtime_root'] ?? null, 'candidate_shared_root_invalid');
    $activePointer = dsAuthorityQualifySafePath($candidate['filesystem']['active_release_pointer'] ?? null, 'candidate_active_pointer_invalid');

    foreach ([$releaseRoot, $sharedRoot, $activePointer] as $path) {
        if (! str_starts_with($path.'/', $deploymentRoot.'/')) {
            dsAuthorityQualifyFail('candidate_filesystem_escape');
        }
    }
    if (count(array_unique([$deploymentRoot, $releaseRoot, $sharedRoot, $activePointer], SORT_STRING)) !== 4) {
        dsAuthorityQualifyFail('candidate_filesystem_collision');
    }

    $presentation = $candidate['presentation'] ?? null;
    if ($presentation !== null) {
        if (! is_array($presentation) || array_is_list($presentation) || count($presentation) !== 2) {
            dsAuthorityQualifyFail('candidate_presentation_invalid');
        }
        $mode = dsAuthorityQualifyPattern(
            $presentation['mode'] ?? null,
            '/\\A(?:ACTIVE_RELEASE_PUBLIC|FIXED_PUBLIC_BRIDGE)\\z/',
            'candidate_presentation_mode_invalid',
        );
        $documentRoot = dsAuthorityQualifySafePath(
            $presentation['document_root'] ?? null,
            'candidate_presentation_document_root_invalid',
        );
        if ($mode === 'ACTIVE_RELEASE_PUBLIC') {
            dsAuthorityQualifyAssertLiteral(
                $documentRoot,
                $activePointer.'/apps/web/public',
                'candidate_presentation_document_root_mismatch',
            );
        } elseif (str_starts_with($documentRoot.'/', $deploymentRoot.'/')
            || str_starts_with($deploymentRoot.'/', $documentRoot.'/')
        ) {
            dsAuthorityQualifyFail('candidate_fixed_public_document_root_not_disjoint');
        }
    }

    foreach ([
        'durable_database_persistence','durable_session','authorization','transaction_durability',
        'pos_durability','authenticated_configuration_channel','read_before_write','read_after_write',
        'non_mutating_health_attestation','verified_rollback',
    ] as $capability) {
        dsAuthorityQualifyAssertBool($candidate['capabilities'][$capability] ?? null, true, 'candidate_capability_missing');
    }

    dsAuthorityQualifyAssertBool($candidate['configuration']['secret_values_embedded'] ?? null, false, 'candidate_secret_embedding_forbidden');
    $presence = $candidate['configuration']['required_binding_presence'] ?? null;
    if (! is_array($presence) || array_is_list($presence)) {
        dsAuthorityQualifyFail('candidate_binding_presence_invalid');
    }

    $requiredBindings = [
        'ONEQAY_RUNTIME_CLASS','ONEQAY_RUNNING_SOURCE_COMMIT','ONEQAY_RUNNING_ARTIFACT_SHA256',
        'ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID','ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN',
        'ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED',
    ];
    foreach ($requiredBindings as $binding) {
        dsAuthorityQualifyAssertBool($presence[$binding] ?? null, true, 'candidate_required_binding_missing');
    }
    if (count($presence) !== count($requiredBindings)) {
        dsAuthorityQualifyFail('candidate_binding_presence_extra');
    }

    return [
        'environment_id' => $environment,
        'target_descriptor_sha256' => hash('sha256', dsAuthorityQualifyCanonicalJson($candidate)),
    ];
}

/** @return array{request_id:string,request_sha256:string} */
function dsAuthorityQualifyRequest(array $request, string $requestRaw, array $artifact, array $target): array
{
    dsAuthorityQualifyAssertLiteral($request['schema_version'] ?? null, 1, 'request_schema_invalid');
    dsAuthorityQualifyAssertLiteral($request['product'] ?? null, 'oneQay', 'request_product_invalid');
    dsAuthorityQualifyAssertLiteral($request['request_state'] ?? null, 'DURABLE_STAGING_DEPLOYMENT_AUTHORITY_REQUEST_PENDING_APPROVAL', 'request_state_invalid');
    $requestId = dsAuthorityQualifyPattern($request['request_id'] ?? null, '/\\Adurable-staging-deployment-request-[0-9a-f]{24}\\z/', 'request_id_invalid');
    dsAuthorityQualifyAssertLiteral($request['requested_scope'] ?? null, 'DEPLOY_EXACT_DURABLE_STAGING_ARTIFACT_TO_EXACT_NON_PRODUCTION_TARGET', 'request_scope_invalid');
    dsAuthorityQualifyAssertLiteral($request['release_id'] ?? null, $artifact['release_id'], 'request_release_mismatch');
    dsAuthorityQualifyAssertLiteral($request['source_commit'] ?? null, $artifact['source_commit'], 'request_source_mismatch');
    dsAuthorityQualifyAssertLiteral($request['artifact_sha256'] ?? null, $artifact['artifact_sha256'], 'request_artifact_mismatch');
    dsAuthorityQualifyAssertLiteral($request['manifest_sha256'] ?? null, $artifact['manifest_sha256'], 'request_manifest_mismatch');
    dsAuthorityQualifyAssertLiteral($request['environment_id'] ?? null, $target['environment_id'], 'request_environment_mismatch');
    dsAuthorityQualifyAssertLiteral($request['target_descriptor_sha256'] ?? null, $target['target_descriptor_sha256'], 'request_target_descriptor_mismatch');

    $required = $request['required_authority'] ?? null;
    if (! is_array($required) || array_is_list($required)) {
        dsAuthorityQualifyFail('request_required_authority_invalid');
    }
    foreach ([
        'separate_operational_authority_required','exact_request_required','exact_target_descriptor_required',
        'exact_environment_required','exact_release_required','exact_artifact_required','approval_token_required',
    ] as $flag) {
        dsAuthorityQualifyAssertBool($required[$flag] ?? null, true, 'request_authority_requirement_missing');
    }
    dsAuthorityQualifyAssertLiteral($required['state'] ?? null, 'NOT_GRANTED', 'request_authority_state_invalid');
    dsAuthorityQualifyAssertLiteral($required['maximum_lifetime_seconds'] ?? null, 900, 'request_authority_lifetime_invalid');

    foreach ([
        'migration_execution_authorized','production_authorized','technical_preview_activation_authorized',
        'updater_activation_authorized','target_selection_authorized','producer_dispatch_authorized',
    ] as $flag) {
        dsAuthorityQualifyAssertBool($request[$flag] ?? null, false, 'request_forbidden_authority');
    }
    dsAuthorityQualifyAssertLiteral($request['attribution'] ?? null, 'Lab | zefry', 'request_attribution_invalid');

    return ['request_id'=>$requestId,'request_sha256'=>hash('sha256', $requestRaw)];
}

/** @return array<string,mixed> */
function dsAuthorityQualifyAuthority(
    array $authority,
    string $authorityRaw,
    string $approvalToken,
    int $nowUnix,
    array $artifact,
    array $target,
    array $request,
): array {
    dsAuthorityQualifyAssertLiteral($authority['schema_version'] ?? null, 1, 'authority_schema_invalid');
    dsAuthorityQualifyAssertLiteral($authority['product'] ?? null, 'oneQay', 'authority_product_invalid');
    $authorityId = dsAuthorityQualifyPattern($authority['authority_id'] ?? null, '/\\Adurable-staging-deployment-authority-[0-9a-f]{24}\\z/', 'authority_id_invalid');
    dsAuthorityQualifyAssertLiteral($authority['authority_state'] ?? null, 'GRANTED', 'authority_state_invalid');
    dsAuthorityQualifyAssertLiteral($authority['scope'] ?? null, 'DEPLOY_EXACT_DURABLE_STAGING_ARTIFACT_TO_EXACT_NON_PRODUCTION_TARGET', 'authority_scope_invalid');
    dsAuthorityQualifyAssertLiteral($authority['request_id'] ?? null, $request['request_id'], 'authority_request_id_mismatch');
    dsAuthorityQualifyAssertLiteral($authority['request_sha256'] ?? null, $request['request_sha256'], 'authority_request_hash_mismatch');
    dsAuthorityQualifyAssertLiteral($authority['release_id'] ?? null, $artifact['release_id'], 'authority_release_mismatch');
    dsAuthorityQualifyAssertLiteral($authority['source_commit'] ?? null, $artifact['source_commit'], 'authority_source_mismatch');
    dsAuthorityQualifyAssertLiteral($authority['artifact_sha256'] ?? null, $artifact['artifact_sha256'], 'authority_artifact_mismatch');
    dsAuthorityQualifyAssertLiteral($authority['environment_id'] ?? null, $target['environment_id'], 'authority_environment_mismatch');
    dsAuthorityQualifyAssertLiteral($authority['target_descriptor_sha256'] ?? null, $target['target_descriptor_sha256'], 'authority_target_descriptor_mismatch');

    $tokenSha = dsAuthorityQualifyPattern($authority['approval_token_sha256'] ?? null, '/\\A[0-9a-f]{64}\\z/', 'authority_token_hash_invalid');
    if (! hash_equals($tokenSha, hash('sha256', $approvalToken))) {
        dsAuthorityQualifyFail('authority_approval_token_invalid');
    }

    $authorizedAt = $authority['authorized_at_unix'] ?? null;
    $expiresAt = $authority['expires_at_unix'] ?? null;
    if (! is_int($authorizedAt) || ! is_int($expiresAt) || $authorizedAt <= 0 || $expiresAt <= $authorizedAt) {
        dsAuthorityQualifyFail('authority_time_window_invalid');
    }
    if (($expiresAt - $authorizedAt) > 900) {
        dsAuthorityQualifyFail('authority_lifetime_exceeds_policy');
    }
    if ($nowUnix < $authorizedAt || $nowUnix >= $expiresAt) {
        dsAuthorityQualifyFail('authority_not_current');
    }

    dsAuthorityQualifyAssertBool($authority['deployment_allowed'] ?? null, true, 'authority_deployment_missing');
    foreach ([
        'migration_execution_allowed','production_allowed','technical_preview_activation_allowed',
        'updater_activation_allowed','target_selection_allowed','producer_dispatch_allowed',
    ] as $flag) {
        dsAuthorityQualifyAssertBool($authority[$flag] ?? null, false, 'authority_forbidden_scope');
    }
    dsAuthorityQualifyAssertLiteral($authority['attribution'] ?? null, 'Lab | zefry', 'authority_attribution_invalid');

    return [
        'authority_id'=>$authorityId,
        'authority_sha256'=>hash('sha256', $authorityRaw),
        'request_id'=>$request['request_id'],
        'request_sha256'=>$request['request_sha256'],
        'target_descriptor_sha256'=>$target['target_descriptor_sha256'],
        'authorized_at_unix'=>$authorizedAt,
        'expires_at_unix'=>$expiresAt,
    ];
}

/** @return array<string,mixed> */
function dsAuthorityQualifyBuildTarget(array $candidate, array $artifact, array $target, array $authority): array
{
    return [
        'schema_version'=>1,
        'target_state'=>'OPERATOR_TARGET_DECLARED',
        'environment_id'=>$target['environment_id'],
        'runtime_class'=>'durable-staging',
        'production'=>false,
        'production_data_allowed'=>false,
        'synthetic_fixture_runtime'=>false,
        'filesystem'=>$candidate['filesystem'],
        ...(array_key_exists('presentation', $candidate) ? ['presentation'=>$candidate['presentation']] : []),
        'capabilities'=>$candidate['capabilities'],
        'configuration'=>$candidate['configuration'],
        'deployment_authority'=>[
            'state'=>'EXTERNALLY_GRANTED_FOR_EXACT_TARGET',
            'authority_id'=>$authority['authority_id'],
            'authority_sha256'=>$authority['authority_sha256'],
            'request_id'=>$authority['request_id'],
            'request_sha256'=>$authority['request_sha256'],
            'target_descriptor_sha256'=>$authority['target_descriptor_sha256'],
            'authorized_at_unix'=>$authority['authorized_at_unix'],
            'expires_at_unix'=>$authority['expires_at_unix'],
            'environment_id'=>$target['environment_id'],
            'release_id'=>$artifact['release_id'],
            'artifact_sha256'=>$artifact['artifact_sha256'],
            'deployment_allowed'=>true,
            'migration_execution_allowed'=>false,
            'production_allowed'=>false,
        ],
        'attribution'=>'Lab | zefry',
    ];
}

function dsAuthorityQualifyReadToken(): string
{
    $raw = stream_get_contents(STDIN, 512);
    if (! is_string($raw)) {
        dsAuthorityQualifyFail('approval_token_read_failed');
    }
    $token = rtrim($raw, "\r\n");
    if (strlen($token) < 32 || strlen($token) > 256 || str_contains($token, "\0")) {
        dsAuthorityQualifyFail('approval_token_shape_invalid');
    }
    return $token;
}

function dsAuthorityQualifyWrite(string $outputPath, array $payload): void
{
    if ($outputPath === '' || is_link($outputPath) || is_dir($outputPath)) {
        dsAuthorityQualifyFail('output_path_invalid');
    }
    $directory = dirname($outputPath);
    if (! is_dir($directory) || ! is_writable($directory)) {
        dsAuthorityQualifyFail('output_directory_invalid');
    }
    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
    $temp = tempnam($directory, '.oneqay-authority-target-');
    if ($temp === false) {
        dsAuthorityQualifyFail('output_temp_create_failed');
    }
    try {
        if (file_put_contents($temp, $encoded, LOCK_EX) !== strlen($encoded)) {
            dsAuthorityQualifyFail('output_write_failed');
        }
        @chmod($temp, 0600);
        if (! rename($temp, $outputPath)) {
            dsAuthorityQualifyFail('output_commit_failed');
        }
        @chmod($outputPath, 0600);
    } finally {
        if (is_file($temp)) {
            @unlink($temp);
        }
    }
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 7) {
        fwrite(STDERR, "Usage: printf '%s' <approval-token> | php tools/qualify-durable-staging-deployment-authority.php <handoff.json> <target-candidate.json> <request.json> <authority.json> <now-unix> <qualified-target.json>\n");
        exit(64);
    }

    try {
        if (preg_match('/\\A[1-9][0-9]{0,10}\\z/', $argv[5]) !== 1) {
            dsAuthorityQualifyFail('qualification_clock_invalid');
        }
        $nowUnix = (int) $argv[5];
        if ($nowUnix <= 0) {
            dsAuthorityQualifyFail('qualification_clock_invalid');
        }

        $handoffInput = dsAuthorityQualifyLoadJson($argv[1]);
        $candidateInput = dsAuthorityQualifyLoadJson($argv[2]);
        $requestInput = dsAuthorityQualifyLoadJson($argv[3]);
        $authorityInput = dsAuthorityQualifyLoadJson($argv[4]);

        $artifact = dsAuthorityQualifyHandoff($handoffInput['decoded']);
        $target = dsAuthorityQualifyCandidate($candidateInput['decoded']);
        $request = dsAuthorityQualifyRequest($requestInput['decoded'], $requestInput['raw'], $artifact, $target);
        $approvalToken = dsAuthorityQualifyReadToken();
        $authority = dsAuthorityQualifyAuthority(
            $authorityInput['decoded'],
            $authorityInput['raw'],
            $approvalToken,
            $nowUnix,
            $artifact,
            $target,
            $request,
        );

        $qualified = dsAuthorityQualifyBuildTarget($candidateInput['decoded'], $artifact, $target, $authority);
        dsAuthorityQualifyWrite($argv[6], $qualified);
        fwrite(STDOUT, "durable_staging_deployment_authority_qualified\n");
        exit(0);
    } catch (DurableStagingDeploymentAuthorityQualificationException $failure) {
        fwrite(STDERR, "durable_staging_deployment_authority_failed:".$failure->getMessage()."\n");
        exit(1);
    } catch (Throwable) {
        fwrite(STDERR, "durable_staging_deployment_authority_failed:unexpected_failure\n");
        exit(1);
    }
}
