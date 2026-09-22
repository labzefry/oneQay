<?php

declare(strict_types=1);

// Author by Lab | zefry
// Read-only successor-head reattestation of an already-provisioned permission.
// Verifies prior signed evidence and the durable journal/grant, then emits fresh
// evidence bound to a synchronized successor PR head. No DDL/DML.

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "RESULT=FAILED\nFAILED_STAGE=CLI_ONLY_REQUIRED\n");
    exit(2);
}

const ONEQAY_SUCCESSOR_SCHEMA_VERSION = 1;
const ONEQAY_SUCCESSOR_EVIDENCE_TYPE = 'CPANEL_LOCAL_PERMISSION_PROVISIONING_SUCCESSOR_REATTESTATION';
const ONEQAY_SUCCESSOR_TTL_SECONDS = 900;
const ONEQAY_SUCCESSOR_SIGNING_CONTEXT = 'oneqay-permission-cpanel-local-provisioning-v1';
const ONEQAY_SUCCESSOR_PERMISSION = 'pos.shift.close';
const ONEQAY_SUCCESSOR_ATTESTATION = 'LOCAL_PERMISSION_PROVISIONING_SUCCESSOR_REATTESTATION';
const ONEQAY_MIGRATION27_NAME = '0000_00_00_000027_create_pos_shift_close_evidence_foundation';

/** @return array<string,string> */
function cliOptions(array $argv): array
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

function requiredOption(array $options, string $name): string
{
    $value = $options[$name] ?? '';
    if (! is_string($value) || $value === '') {
        throw new InvalidArgumentException('Missing required option.');
    }

    return $value;
}

/** @return array<string,string> */
function dotenv(string $path): array
{
    if (! str_starts_with($path, DIRECTORY_SEPARATOR) || ! is_file($path) || is_link($path)) {
        throw new RuntimeException('Runtime env boundary failed.');
    }

    $raw = file_get_contents($path);
    if ($raw === false || strlen($raw) < 2 || strlen($raw) > 131072) {
        throw new RuntimeException('Runtime env read failed.');
    }

    $result = [];
    foreach (preg_split('/\R/', $raw) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_starts_with($line, 'export ')) {
            $line = substr($line, 7);
        }
        if (! preg_match('/\A([A-Z][A-Z0-9_]*)=(.*)\z/D', $line, $m)) {
            continue;
        }

        $key = $m[1];
        $value = trim($m[2]);
        if (strlen($value) >= 2 && $value[0] === "'" && $value[strlen($value) - 1] === "'") {
            $value = substr($value, 1, -1);
        } elseif (strlen($value) >= 2 && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
            $value = stripcslashes(substr($value, 1, -1));
        } else {
            $hash = strpos($value, ' #');
            if ($hash !== false) {
                $value = rtrim(substr($value, 0, $hash));
            }
        }
        $result[$key] = $value;
    }

    return $result;
}

function envRequired(array $env, string $key): string
{
    $value = $env[$key] ?? '';
    if (! is_string($value) || $value === '') {
        throw new RuntimeException('Required runtime database value missing.');
    }

    return $value;
}

/** @return array<string,mixed> */
function jsonObject(string $path): array
{
    if (! str_starts_with($path, DIRECTORY_SEPARATOR) || ! is_file($path) || is_link($path)) {
        throw new RuntimeException('Runtime manifest boundary failed.');
    }

    $raw = file_get_contents($path);
    if ($raw === false || strlen($raw) < 2 || strlen($raw) > 32768) {
        throw new RuntimeException('Runtime manifest read failed.');
    }

    $value = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    if (! is_array($value)) {
        throw new RuntimeException('Runtime manifest is not an object.');
    }

    return $value;
}

function canonicalJson(array $payload): string
{
    ksort($payload, SORT_STRING);

    return json_encode(
        $payload,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
}

function writePrivate(string $path, string $contents): void
{
    if (! str_starts_with($path, DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Output path must be absolute.');
    }

    $dir = dirname($path);
    if (! is_dir($dir) || is_link($dir) || ! is_writable($dir)) {
        throw new RuntimeException('Output directory boundary failed.');
    }
    if (file_exists($path) && (! is_file($path) || is_link($path))) {
        throw new RuntimeException('Output target boundary failed.');
    }

    $tmp = $path.'.tmp.'.bin2hex(random_bytes(8));
    if (file_put_contents($tmp, $contents, LOCK_EX) === false) {
        throw new RuntimeException('Temporary evidence write failed.');
    }
    chmod($tmp, 0600);

    if (! rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException('Atomic evidence publication failed.');
    }
    chmod($path, 0600);
}

function positiveInt(string $value): int
{
    if (preg_match('/\A[1-9][0-9]*\z/D', $value) !== 1) {
        throw new RuntimeException('Positive integer boundary failed.');
    }

    return (int) $value;
}

function nonNegativeInt(string $value): int
{
    if (preg_match('/\A(?:0|[1-9][0-9]*)\z/D', $value) !== 1) {
        throw new RuntimeException('Non-negative integer boundary failed.');
    }

    return (int) $value;
}

function assertHex(string $value, int $length): void
{
    if (preg_match('/\A[0-9a-f]{'.$length.'}\z/D', $value) !== 1) {
        throw new RuntimeException('Hex identity boundary failed.');
    }
}

function assertId(string $value, string $pattern): void
{
    if (preg_match($pattern, $value) !== 1) {
        throw new RuntimeException('Identifier boundary failed.');
    }
}

$stage = 'BOOT';

try {
    $stage = 'INPUTS';
    $options = cliOptions($argv);

    $appRoot = rtrim(requiredOption($options, 'app-root'), DIRECTORY_SEPARATOR);
    $runtimeEnv = requiredOption($options, 'runtime-env');
    $runtimeManifest = requiredOption($options, 'runtime-manifest');
    $priorEvidence = requiredOption($options, 'prior-evidence');
    $priorCanonicalMain = requiredOption($options, 'prior-canonical-main');
    $canonicalMain = requiredOption($options, 'canonical-main');
    $targetPr = positiveInt(requiredOption($options, 'target-pr'));
    $mutationOriginHead = requiredOption($options, 'mutation-origin-head');
    $targetHead = requiredOption($options, 'target-head');
    $bindingRunId = positiveInt(requiredOption($options, 'binding-run-id'));
    $bindingRunAttempt = positiveInt(requiredOption($options, 'binding-run-attempt'));
    $expectedDbBinding = requiredOption($options, 'expected-db-binding');
    $tenantId = requiredOption($options, 'tenant-id');
    $actorIdentityId = requiredOption($options, 'actor-identity-id');
    $actorOrganizationId = requiredOption($options, 'actor-organization-id');
    $roleId = requiredOption($options, 'role-id');
    $expectedTenantAssignments = nonNegativeInt(requiredOption($options, 'expected-tenant-assignments'));
    $expectedOrganizationAssignments = nonNegativeInt(requiredOption($options, 'expected-organization-assignments'));
    $expectedOutletAssignments = nonNegativeInt(requiredOption($options, 'expected-outlet-assignments'));
    $expectedDeviceAssignments = nonNegativeInt(requiredOption($options, 'expected-device-assignments'));
    $output = requiredOption($options, 'output');

    assertHex($priorCanonicalMain, 40);
    assertHex($canonicalMain, 40);
    assertHex($mutationOriginHead, 40);
    assertHex($targetHead, 40);
    assertHex($expectedDbBinding, 64);
    assertId($tenantId, '/\A[a-z0-9][a-z0-9_-]{0,63}\z/D');
    assertId($actorIdentityId, '/\A[a-z0-9][a-z0-9_-]{0,95}\z/D');
    assertId($actorOrganizationId, '/\A[a-z0-9][a-z0-9_-]{0,63}\z/D');
    assertId($roleId, '/\A[a-z][a-z0-9_-]{0,63}\z/D');

    if (hash_equals($mutationOriginHead, $targetHead)) {
        throw new RuntimeException('Successor target head must differ from mutation origin head.');
    }

    if (! str_starts_with($appRoot, DIRECTORY_SEPARATOR)
        || ! is_dir($appRoot)
        || is_link($appRoot)
        || realpath($appRoot) !== $appRoot
    ) {
        throw new RuntimeException('Application root boundary failed.');
    }

    foreach ([
        $appRoot.'/vendor/autoload.php',
        $appRoot.'/bootstrap/app.php',
        $runtimeEnv,
        $runtimeManifest,
        $priorEvidence,
    ] as $requiredFile) {
        if (! is_file($requiredFile) || is_link($requiredFile) || ! is_readable($requiredFile)) {
            throw new RuntimeException('Required file boundary failed.');
        }
    }

    $stage = 'RUNTIME_BINDING';
    $manifest = jsonObject($runtimeManifest);
    if (($manifest['schema_version'] ?? null) !== 1
        || ($manifest['feature'] ?? null) !== 'final-shift-close'
        || ($manifest['selection_state'] ?? null) !== 'SELECTED_NOT_AUTHORIZED'
        || ($manifest['secrets_embedded'] ?? null) !== false
        || ! is_array($manifest['trusted_ingestion'] ?? null)
    ) {
        throw new RuntimeException('Runtime binding manifest contract mismatch.');
    }

    $environmentId = (string) ($manifest['environment_id'] ?? '');
    $runtimeClass = (string) ($manifest['runtime_class'] ?? '');
    $runningSource = (string) ($manifest['exact_running_source_commit'] ?? '');
    $runningArtifact = (string) ($manifest['exact_running_artifact_sha256'] ?? '');
    $readinessAttestation = (string) ($manifest['readiness_attestation_sha256'] ?? '');
    $selectionFingerprint = (string) ($manifest['selection_fingerprint_sha256'] ?? '');
    $ingestionRunId = (int) ($manifest['trusted_ingestion']['run_id'] ?? 0);
    $ingestionRunAttempt = (int) ($manifest['trusted_ingestion']['run_attempt'] ?? 0);
    $ingestionFingerprint = (string) ($manifest['trusted_ingestion']['ingestion_fingerprint_sha256'] ?? '');

    assertHex($runningSource, 40);
    assertHex($runningArtifact, 64);
    assertHex($readinessAttestation, 64);
    assertHex($selectionFingerprint, 64);
    assertHex($ingestionFingerprint, 64);

    if ($environmentId === '' || $runtimeClass === '' || $ingestionRunId <= 0 || $ingestionRunAttempt <= 0) {
        throw new RuntimeException('Runtime binding identity incomplete.');
    }

    $stage = 'DATABASE_CONFIGURATION';
    $env = dotenv($runtimeEnv);
    $host = envRequired($env, 'ONEQAY_DB_HOST');
    $database = envRequired($env, 'ONEQAY_DB_DATABASE');
    $username = envRequired($env, 'ONEQAY_DB_USERNAME');
    $password = envRequired($env, 'ONEQAY_DB_PASSWORD');
    $port = (int) ($env['ONEQAY_DB_PORT'] ?? '3306');
    if ($port < 1 || $port > 65535) {
        throw new RuntimeException('Database port invalid.');
    }

    foreach ([
        'ONEQAY_DB_HOST' => $host,
        'ONEQAY_DB_PORT' => (string) $port,
        'ONEQAY_DB_DATABASE' => $database,
        'ONEQAY_DB_USERNAME' => $username,
        'ONEQAY_DB_PASSWORD' => $password,
        'ONEQAY_PERSISTENCE_ENABLED' => 'false',
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
    ] as $key => $value) {
        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    $stage = 'PRIOR_EVIDENCE';
    $priorEncoded = trim((string) file_get_contents($priorEvidence));
    if ($priorEncoded === '' || strlen($priorEncoded) > 65536) {
        throw new RuntimeException('Prior evidence payload missing or oversized.');
    }

    $priorDecoded = base64_decode($priorEncoded, true);
    if ($priorDecoded === false || strlen($priorDecoded) < 2 || strlen($priorDecoded) > 32768) {
        throw new RuntimeException('Prior evidence Base64 invalid.');
    }

    $prior = json_decode($priorDecoded, true, 32, JSON_THROW_ON_ERROR);
    if (! is_array($prior)) {
        throw new RuntimeException('Prior evidence is not an object.');
    }

    $priorHmac = $prior['hmac_sha256'] ?? null;
    if (! is_string($priorHmac) || preg_match('/\A[0-9a-f]{64}\z/D', $priorHmac) !== 1) {
        throw new RuntimeException('Prior evidence HMAC invalid.');
    }

    $priorPayload = $prior;
    unset($priorPayload['hmac_sha256']);
    $signingKey = hash_hmac('sha256', ONEQAY_SUCCESSOR_SIGNING_CONTEXT, $password, true);
    $expectedPriorHmac = hash_hmac('sha256', canonicalJson($priorPayload), $signingKey);
    if (! hash_equals($expectedPriorHmac, $priorHmac)) {
        throw new RuntimeException('Prior evidence HMAC mismatch.');
    }

    $expectedMutationId = 'fscperm_'.substr(
        hash('sha256', $mutationOriginHead.'|'.$tenantId.'|'.$roleId),
        0,
        55,
    );

    $priorExpected = [
        'schema_version' => 1,
        'evidence_type' => 'CPANEL_LOCAL_PERMISSION_PROVISIONING',
        'canonical_main_sha' => $priorCanonicalMain,
        'target_pr_number' => $targetPr,
        'target_head_sha' => $mutationOriginHead,
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
        'database_binding_sha256' => $expectedDbBinding,
        'tenant_id' => $tenantId,
        'actor_identity_id' => $actorIdentityId,
        'actor_organization_id' => $actorOrganizationId,
        'role_id' => $roleId,
        'permission_id' => ONEQAY_SUCCESSOR_PERMISSION,
        'mutation_id' => $expectedMutationId,
        'migration27_state' => 'EXECUTED',
        'permission_provisioning_state' => 'PROVISIONED',
        'policy_journal_verified' => true,
        'permission_grant_verified' => true,
        'role_assignment_boundaries_unchanged' => true,
        'attestation_mode' => 'LOCAL_PERMISSION_PROVISIONING_AND_POST_VERIFICATION',
        'secrets_embedded' => false,
    ];

    foreach ($priorExpected as $key => $expected) {
        if (($prior[$key] ?? null) !== $expected) {
            throw new RuntimeException('Prior evidence exact field mismatch.');
        }
    }

    $priorIssued = $prior['issued_at_unix'] ?? null;
    $priorExpires = $prior['expires_at_unix'] ?? null;
    if (! is_int($priorIssued)
        || ! is_int($priorExpires)
        || $priorIssued <= 0
        || $priorExpires <= $priorIssued
        || ($priorExpires - $priorIssued) > ONEQAY_SUCCESSOR_TTL_SECONDS
    ) {
        throw new RuntimeException('Prior evidence timestamp contract invalid.');
    }

    $priorFingerprint = $prior['mutation_fingerprint'] ?? null;
    if (! is_string($priorFingerprint) || preg_match('/\A[0-9a-f]{64}\z/D', $priorFingerprint) !== 1) {
        throw new RuntimeException('Prior mutation fingerprint invalid.');
    }

    $stage = 'APPLICATION_BOOT';
    if (! chdir($appRoot)) {
        throw new RuntimeException('Application root activation failed.');
    }

    require $appRoot.'/vendor/autoload.php';
    $app = require $appRoot.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $db = $app->make('db')->connection('oneqay');
    $schema = $db->getSchemaBuilder();
    if (strtolower($db->getDriverName()) !== 'mysql') {
        throw new RuntimeException('Canonical MySQL-compatible target required.');
    }

    $stage = 'DATABASE_BINDING';
    $identity = $db->selectOne(
        'SELECT DATABASE() AS database_name, @@hostname AS server_hostname, @@port AS server_port'
    );
    if (! is_object($identity)) {
        throw new RuntimeException('Database identity readback failed.');
    }

    $bindingPayload = [
        'database_name' => trim((string) ($identity->database_name ?? '')),
        'server_hostname' => trim((string) ($identity->server_hostname ?? '')),
        'server_port' => (int) ($identity->server_port ?? 0),
    ];
    $actualDbBinding = hash('sha256', canonicalJson($bindingPayload));
    if (! hash_equals($expectedDbBinding, $actualDbBinding)) {
        throw new RuntimeException('Database binding mismatch.');
    }

    $stage = 'READ_ONLY_REATTESTATION';
    foreach ([
        'migrations',
        'oneqay_tenants',
        'oneqay_identities',
        'oneqay_identity_organizations',
        'oneqay_roles',
        'oneqay_role_permissions',
        'oneqay_tenant_role_assignments',
        'oneqay_organization_role_assignments',
        'oneqay_outlet_role_assignments',
        'oneqay_device_role_assignments',
        'oneqay_policy_mutations',
        'oneqay_pos_shift_close_evidence',
    ] as $table) {
        if (! $schema->hasTable($table)) {
            throw new RuntimeException('Required table missing.');
        }
    }

    if ((int) $db->table('migrations')->where('migration', ONEQAY_MIGRATION27_NAME)->count() !== 1) {
        throw new RuntimeException('Migration27 execution record is not exact.');
    }

    $tenant = App\Domain\Tenancy\TenantId::fromString($tenantId);
    $actorId = App\Domain\Identity\PlatformIdentityId::fromString($actorIdentityId);
    $organization = App\Domain\Organization\OrganizationId::fromString($actorOrganizationId);
    $role = App\Application\Authorization\RoleIdentifier::fromString($roleId);
    $permission = App\Application\Pos\FinalShiftClosePermission::identifier();

    if (! hash_equals(ONEQAY_SUCCESSOR_PERMISSION, $permission->value())) {
        throw new RuntimeException('Permission identifier drifted.');
    }

    if ((int) $db->table('oneqay_tenants')->where('id', $tenant->value())->count() !== 1
        || (int) $db->table('oneqay_identities')
            ->where('tenant_id', $tenant->value())
            ->where('id', $actorId->value())
            ->count() !== 1
        || (int) $db->table('oneqay_identity_organizations')
            ->where('tenant_id', $tenant->value())
            ->where('identity_id', $actorId->value())
            ->where('organization_id', $organization->value())
            ->count() !== 1
    ) {
        throw new RuntimeException('Control actor context is not exact.');
    }

    $actorAssignments = $db->table('oneqay_tenant_role_assignments')
        ->where('tenant_id', $tenant->value())
        ->where('identity_id', $actorId->value())
        ->get();

    $tenantControl = false;
    foreach ($actorAssignments as $assignment) {
        $controlRoleId = $assignment->role_id ?? null;
        if (! is_string($controlRoleId) || $controlRoleId === '') {
            continue;
        }

        if ((int) $db->table('oneqay_role_permissions')
            ->where('tenant_id', $tenant->value())
            ->where('role_id', $controlRoleId)
            ->where('permission_id', App\Application\Authorization\AdministrationPermission::MANAGE)
            ->count() === 1
        ) {
            $tenantControl = true;
            break;
        }
    }
    if (! $tenantControl) {
        throw new RuntimeException('Control actor no longer has protected authority.');
    }

    if ((int) $db->table('oneqay_roles')
        ->where('tenant_id', $tenant->value())
        ->where('id', $role->value())
        ->count() !== 1
    ) {
        throw new RuntimeException('Target role missing.');
    }

    if ((int) $db->table('oneqay_role_permissions')
        ->where('tenant_id', $tenant->value())
        ->where('role_id', $role->value())
        ->where('permission_id', App\Application\Authorization\AdministrationPermission::MANAGE)
        ->count() !== 0
    ) {
        throw new RuntimeException('Target role became protected.');
    }

    if ((int) $db->table('oneqay_role_permissions')
        ->where('tenant_id', $tenant->value())
        ->where('role_id', $role->value())
        ->where('permission_id', $permission->value())
        ->count() !== 1
    ) {
        throw new RuntimeException('Provisioned permission is not exact.');
    }

    $actor = new App\Application\Organization\VerifiedOrganizationalContext(
        $actorId,
        $tenant,
        $organization,
    );
    $mutationId = App\Application\Authorization\PolicyMutationId::fromString($expectedMutationId);
    $mutation = App\Application\Authorization\DurablePolicyMutation::permissionGrant(
        $mutationId,
        $actor,
        $role,
        $permission,
    );
    $fingerprint = $mutation->fingerprint($actor);
    if (! hash_equals($priorFingerprint, $fingerprint)) {
        throw new RuntimeException('Deterministic mutation fingerprint drifted.');
    }

    $journal = $db->table('oneqay_policy_mutations')
        ->where('tenant_id', $tenant->value())
        ->where('mutation_id', $mutationId->value())
        ->first();
    if ($journal === null
        || ! hash_equals($actorId->value(), (string) ($journal->actor_identity_id ?? ''))
        || ! hash_equals(App\Application\Authorization\PolicyMutationOperation::PERMISSION_GRANT, (string) ($journal->operation ?? ''))
        || ! hash_equals('tenant', (string) ($journal->scope_type ?? ''))
        || ! hash_equals($role->value(), (string) ($journal->role_id ?? ''))
        || ! hash_equals($permission->value(), (string) ($journal->permission_id ?? ''))
        || ! hash_equals($fingerprint, (string) ($journal->payload_fingerprint ?? ''))
        || ! hash_equals('applied', (string) ($journal->outcome ?? ''))
    ) {
        throw new RuntimeException('Policy mutation journal reattestation failed.');
    }

    $assignmentExpected = [
        'oneqay_tenant_role_assignments' => $expectedTenantAssignments,
        'oneqay_organization_role_assignments' => $expectedOrganizationAssignments,
        'oneqay_outlet_role_assignments' => $expectedOutletAssignments,
        'oneqay_device_role_assignments' => $expectedDeviceAssignments,
    ];
    foreach ($assignmentExpected as $table => $expectedCount) {
        $actualCount = (int) $db->table($table)
            ->where('tenant_id', $tenant->value())
            ->where('role_id', $role->value())
            ->count();
        if ($actualCount !== $expectedCount) {
            throw new RuntimeException('Role assignment boundary reattestation failed.');
        }
    }

    if (filter_var($env['ONEQAY_POS_SHIFT_CLOSE_ENABLED'] ?? false, FILTER_VALIDATE_BOOL)) {
        throw new RuntimeException('Final Shift Close feature is unexpectedly active.');
    }

    $stage = 'EVIDENCE_PUBLICATION';
    $issued = time();
    $payload = [
        'schema_version' => ONEQAY_SUCCESSOR_SCHEMA_VERSION,
        'evidence_type' => ONEQAY_SUCCESSOR_EVIDENCE_TYPE,
        'canonical_main_sha' => $canonicalMain,
        'target_pr_number' => $targetPr,
        'target_head_sha' => $targetHead,
        'mutation_origin_head_sha' => $mutationOriginHead,
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
        'database_binding_sha256' => $actualDbBinding,
        'tenant_id' => $tenant->value(),
        'actor_identity_id' => $actorId->value(),
        'actor_organization_id' => $organization->value(),
        'role_id' => $role->value(),
        'permission_id' => $permission->value(),
        'mutation_id' => $mutationId->value(),
        'mutation_fingerprint' => $fingerprint,
        'migration27_state' => 'EXECUTED',
        'permission_provisioning_state' => 'PROVISIONED',
        'policy_journal_verified' => true,
        'permission_grant_verified' => true,
        'role_assignment_boundaries_unchanged' => true,
        'attestation_mode' => ONEQAY_SUCCESSOR_ATTESTATION,
        'issued_at_unix' => $issued,
        'expires_at_unix' => $issued + ONEQAY_SUCCESSOR_TTL_SECONDS,
        'nonce' => bin2hex(random_bytes(16)),
        'secrets_embedded' => false,
    ];

    $hmac = hash_hmac('sha256', canonicalJson($payload), $signingKey);
    $signed = $payload + ['hmac_sha256' => $hmac];
    $encoded = base64_encode(canonicalJson($signed));
    if (strlen($encoded) > 32768) {
        throw new RuntimeException('Successor evidence oversized.');
    }

    writePrivate($output, $encoded."\n");

    fwrite(STDOUT, "RESULT=SUCCESS\n");
    fwrite(STDOUT, "MODE=CPANEL_LOCAL_PERMISSION_SUCCESSOR_REATTESTATION\n");
    fwrite(STDOUT, "MUTATION_ORIGIN_HEAD={$mutationOriginHead}\n");
    fwrite(STDOUT, "TARGET_HEAD={$targetHead}\n");
    fwrite(STDOUT, "PERMISSION_PROVISIONING=PROVISIONED\n");
    fwrite(STDOUT, "PRIOR_EVIDENCE_HMAC=PASS\n");
    fwrite(STDOUT, "POLICY_JOURNAL_REATTESTATION=PASS\n");
    fwrite(STDOUT, "ROLE_ASSIGNMENT_BOUNDARY_REATTESTATION=PASS\n");
    fwrite(STDOUT, "FINAL_SHIFT_CLOSE_FEATURE=INACTIVE\n");
    fwrite(STDOUT, "DATABASE_MUTATION=NONE\n");
    fwrite(STDOUT, "OUTPUT={$output}\n");
    fwrite(STDOUT, "EXPIRES_AT_UNIX=".($issued + ONEQAY_SUCCESSOR_TTL_SECONDS)."\n");
    exit(0);
} catch (Throwable) {
    fwrite(STDERR, "RESULT=FAILED\n");
    fwrite(STDERR, "FAILED_STAGE={$stage}\n");
    fwrite(STDERR, "ERROR=LOCAL_PERMISSION_SUCCESSOR_REATTESTATION_FAILED\n");
    exit(1);
}
