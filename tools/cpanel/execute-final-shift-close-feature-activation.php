<?php

declare(strict_types=1);

// Author by Lab | zefry

final class FinalShiftCloseActivationTransportException extends RuntimeException
{
}

function fscActivationFail(string $code): never
{
    throw new FinalShiftCloseActivationTransportException($code);
}

/** @return array<string,mixed> */
function fscActivationLoadJson(string $path, bool $private = false): array
{
    if ($path === '' || str_contains($path, "\0") || !is_file($path) || is_link($path) || !is_readable($path)) {
        fscActivationFail('input_unavailable');
    }

    $size = filesize($path);
    if (!is_int($size) || $size < 2 || $size > 262144) {
        fscActivationFail('input_size_invalid');
    }

    if ($private) {
        $mode = fileperms($path);
        if (!is_int($mode) || (($mode & 0077) !== 0)) {
            fscActivationFail('private_file_permissions_invalid');
        }
    }

    $raw = file_get_contents($path);
    if (!is_string($raw)) {
        fscActivationFail('input_read_failed');
    }

    try {
        $decoded = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        fscActivationFail('input_json_invalid');
    }

    if (!is_array($decoded) || array_is_list($decoded)) {
        fscActivationFail('input_json_shape_invalid');
    }

    return $decoded;
}

/** @return array<string,mixed>|list<mixed> */
function fscActivationCanonicalize(array $value): array
{
    if (array_is_list($value)) {
        return array_map(
            static fn (mixed $item): mixed => is_array($item) ? fscActivationCanonicalize($item) : $item,
            $value,
        );
    }

    ksort($value, SORT_STRING);
    foreach ($value as $key => $item) {
        if (is_array($item)) {
            $value[$key] = fscActivationCanonicalize($item);
        }
    }

    return $value;
}

function fscActivationCanonicalJson(array $value): string
{
    $json = json_encode(
        fscActivationCanonicalize($value),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
    if (!is_string($json)) {
        fscActivationFail('canonical_json_failed');
    }

    return $json;
}

function fscActivationHex(mixed $value, int $length, string $code): string
{
    if (!is_string($value) || preg_match('/\A[0-9a-f]{'.$length.'}\z/D', $value) !== 1) {
        fscActivationFail($code);
    }
    return $value;
}

function fscActivationString(mixed $value, string $pattern, string $code): string
{
    if (!is_string($value) || preg_match($pattern, $value) !== 1) {
        fscActivationFail($code);
    }
    return $value;
}

function fscActivationBool(mixed $value, bool $expected, string $code): void
{
    if (!is_bool($value) || $value !== $expected) {
        fscActivationFail($code);
    }
}

function fscActivationLiteral(mixed $actual, mixed $expected, string $code): void
{
    if ($actual !== $expected) {
        fscActivationFail($code);
    }
}

function fscActivationSafeAbsoluteFile(string $path, bool $private, string $code): string
{
    if ($path === ''
        || strlen($path) > 4096
        || str_contains($path, "\0")
        || str_contains($path, '\\')
        || !str_starts_with($path, '/')
        || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $path) === 1
        || !is_file($path)
        || is_link($path)
        || !is_readable($path)
    ) {
        fscActivationFail($code.'_boundary_invalid');
    }

    if ($private) {
        $mode = fileperms($path);
        if (!is_int($mode) || (($mode & 0077) !== 0)) {
            fscActivationFail($code.'_permissions_invalid');
        }
    }

    $real = realpath($path);
    if (!is_string($real) || $real !== $path) {
        fscActivationFail($code.'_realpath_invalid');
    }

    return $real;
}

function fscActivationReadPrivate(string $path, int $maxBytes, string $code): string
{
    $path = fscActivationSafeAbsoluteFile($path, true, $code);
    $size = filesize($path);
    if (!is_int($size) || $size < 1 || $size > $maxBytes) {
        fscActivationFail($code.'_size_invalid');
    }
    $raw = file_get_contents($path);
    if (!is_string($raw)) {
        fscActivationFail($code.'_read_failed');
    }
    return $raw;
}

function fscActivationWritePrivateAtomic(string $path, string $contents): void
{
    if ($path === ''
        || strlen($path) > 4096
        || !str_starts_with($path, '/')
        || str_contains($path, "\0")
        || str_contains($path, '\\')
        || preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $path) === 1
    ) {
        fscActivationFail('output_path_invalid');
    }

    $dir = dirname($path);
    if (!is_dir($dir) || is_link($dir) || !is_writable($dir)) {
        fscActivationFail('output_directory_invalid');
    }
    if (file_exists($path) && (!is_file($path) || is_link($path))) {
        fscActivationFail('output_target_invalid');
    }

    $temp = $dir.'/.fsc-activation-'.bin2hex(random_bytes(12)).'.tmp';
    $handle = @fopen($temp, 'x+b');
    if (!is_resource($handle)) {
        fscActivationFail('output_temp_create_failed');
    }

    try {
        @chmod($temp, 0600);
        $length = strlen($contents);
        $written = 0;
        while ($written < $length) {
            $count = fwrite($handle, substr($contents, $written));
            if ($count === false || $count === 0) {
                throw new FinalShiftCloseActivationTransportException('output_temp_write_failed');
            }
            $written += $count;
        }
        if (!fflush($handle)) {
            throw new FinalShiftCloseActivationTransportException('output_temp_flush_failed');
        }
        if (function_exists('fsync')) {
            @fsync($handle);
        }
    } catch (Throwable $exception) {
        fclose($handle);
        @unlink($temp);
        throw $exception;
    }
    fclose($handle);

    if (!@rename($temp, $path)) {
        @unlink($temp);
        fscActivationFail('output_atomic_replace_failed');
    }
    @chmod($path, 0600);
}

/** @return array{present:bool,value:bool} */
function fscActivationReadFlag(string $raw): array
{
    $matches = [];
    $count = preg_match_all(
        '/^(?:[ \t]*export[ \t]+)?[ \t]*ONEQAY_POS_SHIFT_CLOSE_ENABLED[ \t]*=[ \t]*(.*)$/m',
        $raw,
        $matches,
    );
    if ($count === false || $count > 1) {
        fscActivationFail('runtime_flag_ambiguous');
    }
    if ($count === 0) {
        return ['present' => false, 'value' => false];
    }

    $value = trim((string) $matches[1][0]);
    if (strlen($value) >= 2 && (($value[0] === '"' && $value[strlen($value) - 1] === '"') || ($value[0] === "'" && $value[strlen($value) - 1] === "'"))) {
        $value = substr($value, 1, -1);
    }
    $normalized = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    if (!is_bool($normalized)) {
        fscActivationFail('runtime_flag_value_invalid');
    }

    return ['present' => true, 'value' => $normalized];
}

function fscActivationWriteFlagValue(string $raw, bool $value): string
{
    fscActivationReadFlag($raw);
    $replacement = 'ONEQAY_POS_SHIFT_CLOSE_ENABLED="'.($value ? 'true' : 'false').'"';
    $pattern = '/^(?:[ \t]*export[ \t]+)?[ \t]*ONEQAY_POS_SHIFT_CLOSE_ENABLED[ \t]*=.*$/m';
    $count = preg_match_all($pattern, $raw);
    if ($count === false || $count > 1) {
        fscActivationFail('runtime_flag_ambiguous');
    }
    if ($count === 1) {
        $updated = preg_replace($pattern, $replacement, $raw, 1, $replacements);
        if (!is_string($updated) || $replacements !== 1) {
            fscActivationFail('runtime_flag_rewrite_failed');
        }
        return $updated;
    }

    $separator = ($raw === '' || str_ends_with($raw, "\n")) ? '' : "\n";
    return $raw.$separator.$replacement."\n";
}

/** @return array<string,string> */
function fscActivationDotenv(string $raw): array
{
    $result = [];
    foreach (preg_split('/\R/', $raw) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_starts_with($line, 'export ')) {
            $line = substr($line, 7);
        }
        if (!preg_match('/\A([A-Z][A-Z0-9_]*)=(.*)\z/D', $line, $match)) {
            continue;
        }
        $key = $match[1];
        if (array_key_exists($key, $result)) {
            fscActivationFail('runtime_env_duplicate_key');
        }
        $value = trim($match[2]);
        if (strlen($value) >= 2 && $value[0] === "'" && $value[strlen($value) - 1] === "'") {
            $value = substr($value, 1, -1);
        } elseif (strlen($value) >= 2 && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
            $value = stripcslashes(substr($value, 1, -1));
        } else {
            $comment = strpos($value, ' #');
            if ($comment !== false) {
                $value = rtrim(substr($value, 0, $comment));
            }
        }
        $result[$key] = $value;
    }
    return $result;
}

/** @return array<string,mixed> */
function fscActivationValidateEnvelope(array $envelope): array
{
    fscActivationLiteral($envelope['schema_version'] ?? null, 1, 'transport_schema_invalid');
    fscActivationLiteral($envelope['feature'] ?? null, 'final-shift-close', 'transport_feature_invalid');
    fscActivationLiteral(
        $envelope['transport_state'] ?? null,
        'TARGET_BOUND_FEATURE_ACTIVATION_TRANSPORT_ENVELOPE_SOURCE_ONLY',
        'transport_state_invalid',
    );
    fscActivationLiteral($envelope['runtime_class'] ?? null, 'durable-staging', 'transport_runtime_class_invalid');
    $environmentId = fscActivationString(
        $envelope['environment_id'] ?? null,
        '/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D',
        'transport_environment_invalid',
    );
    $source = fscActivationHex($envelope['exact_running_source_commit'] ?? null, 40, 'transport_source_invalid');
    $artifact = fscActivationHex($envelope['exact_running_artifact_sha256'] ?? null, 64, 'transport_artifact_invalid');
    $attestation = fscActivationHex($envelope['readiness_attestation_sha256'] ?? null, 64, 'transport_attestation_invalid');
    $selection = fscActivationHex($envelope['selection_fingerprint_sha256'] ?? null, 64, 'transport_selection_invalid');
    $targetBinding = fscActivationHex($envelope['target_binding_sha256'] ?? null, 64, 'transport_target_binding_invalid');
    $dependency = fscActivationHex($envelope['dependency_envelope_sha256'] ?? null, 64, 'transport_dependency_invalid');
    $authority = fscActivationHex($envelope['activation_authority_sha256'] ?? null, 64, 'transport_authority_digest_invalid');
    $plan = fscActivationHex($envelope['activation_plan_sha256'] ?? null, 64, 'transport_plan_digest_invalid');
    $bundle = fscActivationHex($envelope['capability_evidence_bundle_sha256'] ?? null, 64, 'transport_capability_bundle_invalid');
    fscActivationHex($envelope['transport_envelope_sha256'] ?? null, 64, 'transport_envelope_digest_invalid');

    $core = $envelope;
    $declaredEnvelopeSha = (string) $core['transport_envelope_sha256'];
    unset($core['transport_envelope_sha256']);
    fscActivationLiteral(hash('sha256', fscActivationCanonicalJson($core)), $declaredEnvelopeSha, 'transport_envelope_digest_mismatch');

    fscActivationLiteral($envelope['runtime_flag'] ?? null, 'ONEQAY_POS_SHIFT_CLOSE_ENABLED', 'transport_runtime_flag_invalid');
    fscActivationLiteral($envelope['concrete_adapter'] ?? null, 'NOT_IMPLEMENTED', 'transport_source_adapter_boundary_invalid');
    fscActivationLiteral($envelope['network_dispatch'] ?? null, 'NOT_PERFORMED', 'transport_source_dispatch_boundary_invalid');
    fscActivationLiteral($envelope['runtime_allowlist_change'] ?? null, 'NOT_IMPLEMENTED', 'transport_source_allowlist_boundary_invalid');
    fscActivationLiteral($envelope['feature_activation_state'] ?? null, 'INACTIVE', 'transport_source_feature_state_invalid');
    fscActivationBool($envelope['secrets_embedded'] ?? null, false, 'transport_secrets_boundary_invalid');

    $expectedOperations = [
        ['sequence' => 1, 'operation' => 'READ_FLAG', 'expected_value' => false],
        ['sequence' => 2, 'operation' => 'WRITE_FLAG', 'value' => true],
        ['sequence' => 3, 'operation' => 'READ_FLAG', 'expected_value' => true],
        ['sequence' => 4, 'operation' => 'NON_MUTATING_HEALTH_ATTESTATION'],
        ['sequence' => 5, 'operation' => 'ROLLBACK_WRITE_FLAG_ON_POST_WRITE_FAILURE', 'value' => false],
        ['sequence' => 6, 'operation' => 'VERIFY_ROLLBACK_READBACK', 'expected_value' => false],
    ];
    fscActivationLiteral($envelope['operations'] ?? null, $expectedOperations, 'transport_operation_order_invalid');

    return [
        'environment_id' => $environmentId,
        'runtime_class' => 'durable-staging',
        'source_commit' => $source,
        'artifact_sha256' => $artifact,
        'readiness_attestation_sha256' => $attestation,
        'selection_fingerprint_sha256' => $selection,
        'target_binding_sha256' => $targetBinding,
        'dependency_envelope_sha256' => $dependency,
        'activation_authority_sha256' => $authority,
        'activation_plan_sha256' => $plan,
        'capability_evidence_bundle_sha256' => $bundle,
        'transport_envelope_sha256' => $declaredEnvelopeSha,
    ];
}

/** @param array<string,mixed> $manifest */
function fscActivationValidateRuntimeManifest(array $manifest, array $identity): void
{
    fscActivationLiteral($manifest['schema_version'] ?? null, 1, 'runtime_manifest_schema_invalid');
    fscActivationLiteral($manifest['feature'] ?? null, 'final-shift-close', 'runtime_manifest_feature_invalid');
    fscActivationLiteral($manifest['selection_state'] ?? null, 'SELECTED_NOT_AUTHORIZED', 'runtime_manifest_selection_state_invalid');
    fscActivationBool($manifest['secrets_embedded'] ?? null, false, 'runtime_manifest_secrets_invalid');

    foreach ([
        'environment_id' => 'environment_id',
        'runtime_class' => 'runtime_class',
        'exact_running_source_commit' => 'source_commit',
        'exact_running_artifact_sha256' => 'artifact_sha256',
        'readiness_attestation_sha256' => 'readiness_attestation_sha256',
        'selection_fingerprint_sha256' => 'selection_fingerprint_sha256',
    ] as $manifestField => $identityField) {
        fscActivationLiteral(
            $manifest[$manifestField] ?? null,
            $identity[$identityField] ?? null,
            'runtime_manifest_binding_mismatch:'.$manifestField,
        );
    }
}

/** @return array<string,mixed> */
function fscActivationValidateAuthority(array $authority, array $identity, string $approvalToken): array
{
    fscActivationLiteral($authority['schema_version'] ?? null, 1, 'authority_schema_invalid');
    fscActivationLiteral($authority['feature'] ?? null, 'final-shift-close', 'authority_feature_invalid');
    fscActivationLiteral($authority['authority_state'] ?? null, 'GRANTED', 'authority_state_invalid');
    fscActivationLiteral($authority['authority_context'] ?? null, 'final-shift-close-feature-activation-authority', 'authority_context_invalid');
    fscActivationLiteral($authority['concrete_adapter'] ?? null, 'CPANEL_PRIVATE_FILE_ONE_SHOT_CRON_V1', 'authority_adapter_invalid');

    foreach ([
        'environment_id' => 'environment_id',
        'runtime_class' => 'runtime_class',
        'exact_running_source_commit' => 'source_commit',
        'exact_running_artifact_sha256' => 'artifact_sha256',
        'readiness_attestation_sha256' => 'readiness_attestation_sha256',
        'selection_fingerprint_sha256' => 'selection_fingerprint_sha256',
        'target_binding_sha256' => 'target_binding_sha256',
        'dependency_envelope_sha256' => 'dependency_envelope_sha256',
        'activation_plan_sha256' => 'activation_plan_sha256',
        'transport_envelope_sha256' => 'transport_envelope_sha256',
    ] as $authorityField => $identityField) {
        fscActivationLiteral(
            $authority[$authorityField] ?? null,
            $identity[$identityField] ?? null,
            'authority_binding_mismatch:'.$authorityField,
        );
    }

    fscActivationHex($authority['executor_source_commit'] ?? null, 40, 'authority_executor_source_invalid');
    $targetHead = fscActivationHex($authority['state_transition_head_sha'] ?? null, 40, 'authority_state_transition_head_invalid');
    $targetPr = $authority['state_transition_pr_number'] ?? null;
    if (!is_int($targetPr) || $targetPr < 1) {
        fscActivationFail('authority_state_transition_pr_invalid');
    }

    $tokenHash = fscActivationHex($authority['approval_token_sha256'] ?? null, 64, 'authority_token_hash_invalid');
    if (!hash_equals($tokenHash, hash('sha256', $approvalToken))) {
        fscActivationFail('authority_token_invalid');
    }

    $authorizedAt = $authority['authorized_at_unix'] ?? null;
    $expiresAt = $authority['expires_at_unix'] ?? null;
    if (!is_int($authorizedAt)
        || !is_int($expiresAt)
        || $authorizedAt <= 0
        || $expiresAt <= $authorizedAt
        || ($expiresAt - $authorizedAt) > 900
    ) {
        fscActivationFail('authority_window_invalid');
    }
    $now = time();
    if ($now < $authorizedAt || $now >= $expiresAt) {
        fscActivationFail('authority_not_current');
    }

    fscActivationBool($authority['feature_activation_allowed'] ?? null, true, 'authority_feature_activation_missing');
    foreach ([
        'migration_execution_allowed',
        'permission_provisioning_allowed',
        'deployment_allowed',
        'technical_preview_allowed',
        'production_allowed',
        'updater_allowed',
        'runtime_allowlist_change_allowed',
    ] as $field) {
        fscActivationBool($authority[$field] ?? null, false, 'authority_boundary_invalid:'.$field);
    }
    fscActivationBool($authority['secrets_embedded'] ?? null, false, 'authority_secrets_invalid');

    return [
        'state_transition_pr_number' => $targetPr,
        'state_transition_head_sha' => $targetHead,
        'executor_source_commit' => (string) $authority['executor_source_commit'],
        'authorized_at_unix' => $authorizedAt,
        'expires_at_unix' => $expiresAt,
    ];
}

/** @return array<string,mixed> */
function fscActivationFetchReadiness(string $url, string $token): array
{
    $parts = parse_url($url);
    if (!is_array($parts)
        || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
        || !is_string($parts['host'] ?? null)
        || trim((string) $parts['host']) === ''
        || isset($parts['user'])
        || isset($parts['pass'])
        || isset($parts['fragment'])
    ) {
        fscActivationFail('readiness_url_invalid');
    }
    if ($token === '' || strlen($token) > 4096) {
        fscActivationFail('readiness_token_invalid');
    }
    if (!extension_loaded('curl')) {
        fscActivationFail('curl_extension_required');
    }

    $handle = curl_init($url);
    if ($handle === false) {
        fscActivationFail('readiness_client_init_failed');
    }
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: Bearer '.$token,
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_USERAGENT => 'oneQay-final-shift-close-activation/1',
    ]);
    $body = curl_exec($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);

    if (!is_string($body) || $status !== 200 || strlen($body) < 2 || strlen($body) > 65536) {
        fscActivationFail('readiness_request_failed');
    }
    try {
        $decoded = json_decode($body, true, 32, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        fscActivationFail('readiness_json_invalid');
    }
    if (!is_array($decoded) || array_is_list($decoded)) {
        fscActivationFail('readiness_shape_invalid');
    }
    return $decoded;
}

/** @param array<string,mixed> $readiness */
function fscActivationValidateReadiness(array $readiness, array $identity): void
{
    fscActivationLiteral($readiness['environment_id'] ?? null, $identity['environment_id'], 'readiness_environment_mismatch');
    fscActivationLiteral($readiness['runtime_class'] ?? null, $identity['runtime_class'], 'readiness_runtime_class_mismatch');
    fscActivationLiteral($readiness['running_source_commit'] ?? null, $identity['source_commit'], 'readiness_source_mismatch');
    fscActivationLiteral($readiness['running_artifact_sha256'] ?? null, $identity['artifact_sha256'], 'readiness_artifact_mismatch');
    fscActivationBool($readiness['durable_staging_runtime_enabled'] ?? null, true, 'readiness_runtime_not_enabled');
    fscActivationBool($readiness['production_data_allowed'] ?? null, false, 'readiness_production_data_forbidden');
}

/**
 * @param callable(string,string):array<string,mixed>|null $readinessFetcher
 * @return array<string,mixed>
 */
function fscActivationExecute(
    string $transportEnvelopePath,
    string $authorityPath,
    string $runtimeManifestPath,
    string $runtimeEnvPath,
    string $approvalTokenPath,
    string $readinessUrl,
    string $outputPath,
    ?callable $readinessFetcher = null,
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

    $before = fscActivationReadFlag($original);
    if ($before['value'] !== false) {
        fscActivationFail('pre_activation_flag_must_be_false');
    }

    $afterRaw = fscActivationWriteFlagValue($original, true);
    $mutated = false;

    try {
        fscActivationWritePrivateAtomic($runtimeEnvPath, $afterRaw);
        $mutated = true;

        $readbackRaw = fscActivationReadPrivate($runtimeEnvPath, 131072, 'runtime_env');
        $after = fscActivationReadFlag($readbackRaw);
        if ($after['value'] !== true) {
            fscActivationFail('post_write_flag_readback_failed');
        }

        $fetcher = $readinessFetcher ?? 'fscActivationFetchReadiness';
        $readiness = $fetcher($readinessUrl, $attestationToken);
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
                fscActivationWritePrivateAtomic($runtimeEnvPath, $original);
                $rollbackRaw = fscActivationReadPrivate($runtimeEnvPath, 131072, 'runtime_env');
                $rollback = fscActivationReadFlag($rollbackRaw);
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
            "Usage: php tools/cpanel/execute-final-shift-close-feature-activation.php <transport-envelope.json> <private-authority.json> <runtime-manifest.json> <private-runtime-env> <private-approval-token-file> <https-readiness-url> <execution-evidence.json>\n",
        );
        exit(64);
    }

    try {
        $receipt = fscActivationExecute($argv[1], $argv[2], $argv[3], $argv[4], $argv[5], $argv[6], $argv[7]);
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
