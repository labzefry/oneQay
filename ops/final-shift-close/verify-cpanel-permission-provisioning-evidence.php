<?php

declare(strict_types=1);

// Author by Lab | zefry
// Verifies signed short-lived cPanel-local permission provisioning evidence.
// Supports the original execution envelope and bounded successor-head reattestation.

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "RESULT=FAILED\nERROR=CLI_ONLY_REQUIRED\n");
    exit(2);
}

function options(array $argv): array
{
    $out = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (! str_starts_with($arg, '--') || ! str_contains($arg, '=')) {
            throw new InvalidArgumentException('Invalid argument shape.');
        }
        [$key, $value] = explode('=', substr($arg, 2), 2);
        if ($key === '' || $value === '' || isset($out[$key])) {
            throw new InvalidArgumentException('Invalid argument set.');
        }
        $out[$key] = $value;
    }

    return $out;
}

function required(array $options, string $key): string
{
    $value = $options[$key] ?? '';
    if (! is_string($value) || $value === '') {
        throw new InvalidArgumentException('Missing required option.');
    }

    return $value;
}

function canonical(array $payload): string
{
    ksort($payload, SORT_STRING);

    return json_encode(
        $payload,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
}

function assertHex(string $value, int $length): void
{
    if (preg_match('/\A[0-9a-f]{'.$length.'}\z/D', $value) !== 1) {
        throw new RuntimeException('Hex identity boundary failed.');
    }
}

try {
    $o = options($argv);

    $evidenceEnv = required($o, 'evidence-env');
    $canonicalMain = required($o, 'canonical-main');
    $targetPr = (int) required($o, 'target-pr');
    $targetHead = required($o, 'target-head');
    $bindingRunId = (int) required($o, 'binding-run-id');
    $bindingRunAttempt = (int) required($o, 'binding-run-attempt');
    $environmentId = required($o, 'environment-id');
    $runtimeClass = required($o, 'runtime-class');
    $runningSource = required($o, 'running-source');
    $runningArtifact = required($o, 'running-artifact');
    $readinessAttestation = required($o, 'readiness-attestation');
    $selectionFingerprint = required($o, 'selection-fingerprint');
    $ingestionRunId = (int) required($o, 'ingestion-run-id');
    $ingestionRunAttempt = (int) required($o, 'ingestion-run-attempt');
    $ingestionFingerprint = required($o, 'ingestion-fingerprint');
    $databaseBinding = required($o, 'database-binding');
    $tenantId = required($o, 'tenant-id');
    $actorIdentityId = required($o, 'actor-identity-id');
    $actorOrganizationId = required($o, 'actor-organization-id');
    $roleId = required($o, 'role-id');

    $encoded = getenv($evidenceEnv);
    if (! is_string($encoded) || $encoded === '' || strlen($encoded) > 65536) {
        throw new RuntimeException('Local evidence environment payload missing or oversized.');
    }

    $decoded = base64_decode($encoded, true);
    if ($decoded === false || strlen($decoded) < 2 || strlen($decoded) > 32768) {
        throw new RuntimeException('Local evidence base64 payload invalid.');
    }

    $evidence = json_decode($decoded, true, 32, JSON_THROW_ON_ERROR);
    if (! is_array($evidence)) {
        throw new RuntimeException('Local evidence is not an object.');
    }

    $evidenceType = $evidence['evidence_type'] ?? null;
    if (! is_string($evidenceType)) {
        throw new RuntimeException('Local evidence type missing.');
    }

    $baseKeys = [
        'schema_version',
        'evidence_type',
        'canonical_main_sha',
        'target_pr_number',
        'target_head_sha',
        'binding_run_id',
        'binding_run_attempt',
        'environment_id',
        'runtime_class',
        'exact_running_source_commit',
        'exact_running_artifact_sha256',
        'readiness_attestation_sha256',
        'selection_fingerprint_sha256',
        'trusted_ingestion_run_id',
        'trusted_ingestion_run_attempt',
        'trusted_ingestion_fingerprint_sha256',
        'database_binding_sha256',
        'tenant_id',
        'actor_identity_id',
        'actor_organization_id',
        'role_id',
        'permission_id',
        'mutation_id',
        'mutation_fingerprint',
        'migration27_state',
        'permission_provisioning_state',
        'policy_journal_verified',
        'permission_grant_verified',
        'role_assignment_boundaries_unchanged',
        'attestation_mode',
        'issued_at_unix',
        'expires_at_unix',
        'nonce',
        'secrets_embedded',
        'hmac_sha256',
    ];

    $successor = $evidenceType === 'CPANEL_LOCAL_PERMISSION_PROVISIONING_SUCCESSOR_REATTESTATION';
    if ($successor) {
        $baseKeys[] = 'mutation_origin_head_sha';
    } elseif ($evidenceType !== 'CPANEL_LOCAL_PERMISSION_PROVISIONING') {
        throw new RuntimeException('Unsupported local permission evidence type.');
    }

    $actualKeys = array_keys($evidence);
    sort($baseKeys, SORT_STRING);
    sort($actualKeys, SORT_STRING);
    if ($actualKeys !== $baseKeys) {
        throw new RuntimeException('Local permission evidence key-set mismatch.');
    }

    $hmac = $evidence['hmac_sha256'];
    if (! is_string($hmac) || preg_match('/\A[0-9a-f]{64}\z/D', $hmac) !== 1) {
        throw new RuntimeException('Local permission evidence HMAC invalid.');
    }

    $dbPassword = getenv('ONEQAY_DB_PASSWORD');
    if (! is_string($dbPassword) || $dbPassword === '') {
        throw new RuntimeException('Permission DB password is unavailable for HMAC verification.');
    }

    $payload = $evidence;
    unset($payload['hmac_sha256']);

    $signingKey = hash_hmac(
        'sha256',
        'oneqay-permission-cpanel-local-provisioning-v1',
        $dbPassword,
        true,
    );
    $expectedHmac = hash_hmac('sha256', canonical($payload), $signingKey);
    if (! hash_equals($expectedHmac, $hmac)) {
        throw new RuntimeException('Local permission evidence HMAC mismatch.');
    }

    $mutationOriginHead = $targetHead;
    $attestationMode = 'LOCAL_PERMISSION_PROVISIONING_AND_POST_VERIFICATION';
    if ($successor) {
        $mutationOriginHead = $evidence['mutation_origin_head_sha'] ?? '';
        if (! is_string($mutationOriginHead)) {
            throw new RuntimeException('Mutation origin head missing.');
        }
        assertHex($mutationOriginHead, 40);
        $attestationMode = 'LOCAL_PERMISSION_PROVISIONING_SUCCESSOR_REATTESTATION';
    }

    $expectedMutationId = 'fscperm_'.substr(
        hash('sha256', $mutationOriginHead.'|'.$tenantId.'|'.$roleId),
        0,
        55,
    );

    $expected = [
        'schema_version' => 1,
        'evidence_type' => $evidenceType,
        'canonical_main_sha' => $canonicalMain,
        'target_pr_number' => $targetPr,
        'target_head_sha' => $targetHead,
        'binding_run_id' => $bindingRunId,
        'binding_run_attempt' => $bindingRunAttempt,
        'environment_id' => $environmentId,
        'runtime_class' => $runtimeClass,
        'exact_running_source_commit' => $runningSource,
        'exact_running_artifact_sha256' => $runningArtifact,
        'readiness_attestation_sha256' => $readinessAttestation,
        'selection_fingerprint_sha256' => $selectionFingerprint,
        'trusted_ingestion_run_id' => $ingestionRunId,
        'trusted_ingestion_run_attempt' => $ingestionRunAttempt,
        'trusted_ingestion_fingerprint_sha256' => $ingestionFingerprint,
        'database_binding_sha256' => $databaseBinding,
        'tenant_id' => $tenantId,
        'actor_identity_id' => $actorIdentityId,
        'actor_organization_id' => $actorOrganizationId,
        'role_id' => $roleId,
        'permission_id' => 'pos.shift.close',
        'mutation_id' => $expectedMutationId,
        'migration27_state' => 'EXECUTED',
        'permission_provisioning_state' => 'PROVISIONED',
        'policy_journal_verified' => true,
        'permission_grant_verified' => true,
        'role_assignment_boundaries_unchanged' => true,
        'attestation_mode' => $attestationMode,
        'secrets_embedded' => false,
    ];

    foreach ($expected as $key => $value) {
        if (($evidence[$key] ?? null) !== $value) {
            throw new RuntimeException('Local permission evidence exact field mismatch.');
        }
    }

    if (! is_string($evidence['mutation_fingerprint'])
        || preg_match('/\A[0-9a-f]{64}\z/D', $evidence['mutation_fingerprint']) !== 1
        || ! is_string($evidence['nonce'])
        || preg_match('/\A[0-9a-f]{32}\z/D', $evidence['nonce']) !== 1
    ) {
        throw new RuntimeException('Local permission evidence fingerprint or nonce invalid.');
    }

    $issued = $evidence['issued_at_unix'];
    $expires = $evidence['expires_at_unix'];
    if (! is_int($issued) || ! is_int($expires) || $issued <= 0 || $expires <= $issued) {
        throw new RuntimeException('Local permission evidence timestamps invalid.');
    }
    if (($expires - $issued) > 900) {
        throw new RuntimeException('Local permission evidence TTL exceeds 900 seconds.');
    }

    $now = time();
    if ($issued > ($now + 60) || $expires < $now) {
        throw new RuntimeException('Local permission evidence is not currently fresh.');
    }

    fwrite(STDOUT, "RESULT=SUCCESS\n");
    fwrite(STDOUT, "MODE=".($successor
        ? "CPANEL_LOCAL_PERMISSION_SUCCESSOR_EVIDENCE_VERIFICATION"
        : "CPANEL_LOCAL_PERMISSION_EVIDENCE_VERIFICATION")."\n");
    fwrite(STDOUT, "LOCAL_EXECUTION_VERIFIED=YES\n");
    if ($successor) {
        fwrite(STDOUT, "MUTATION_ORIGIN_HEAD={$mutationOriginHead}\n");
    }
    exit(0);
} catch (Throwable) {
    fwrite(STDERR, "RESULT=FAILED\n");
    fwrite(STDERR, "ERROR=LOCAL_PERMISSION_EVIDENCE_VERIFICATION_FAILED\n");
    exit(1);
}
