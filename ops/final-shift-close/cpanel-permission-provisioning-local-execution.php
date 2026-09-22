<?php

declare(strict_types=1);

// Author by Lab | zefry

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "RESULT=FAILED\nFAILED_STAGE=CLI_ONLY_REQUIRED\n");
    exit(2);
}

const ONEQAY_PERMISSION_LOCAL_SCHEMA_VERSION = 1;
const ONEQAY_PERMISSION_LOCAL_EVIDENCE_TYPE = 'CPANEL_LOCAL_PERMISSION_PROVISIONING';
const ONEQAY_PERMISSION_LOCAL_TTL_SECONDS = 900;
const ONEQAY_PERMISSION_LOCAL_SIGNING_CONTEXT = 'oneqay-permission-cpanel-local-provisioning-v1';
const ONEQAY_PERMISSION_ID = 'pos.shift.close';
const ONEQAY_PERMISSION_ATTESTATION_MODE = 'LOCAL_PERMISSION_PROVISIONING_AND_POST_VERIFICATION';
const ONEQAY_MIGRATION27_NAME = '0000_00_00_000027_create_pos_shift_close_evidence_foundation';
const ONEQAY_MIGRATION27_TABLE = 'oneqay_pos_shift_close_evidence';

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
        throw new RuntimeException('Runtime env file boundary failed.');
    }
    $raw = file_get_contents($path);
    if ($raw === false || strlen($raw) < 2 || strlen($raw) > 131072) {
        throw new RuntimeException('Runtime env read boundary failed.');
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
        throw new RuntimeException('Required runtime database environment value missing.');
    }
    return $value;
}

/** @return array<string,mixed> */
function jsonObject(string $path): array
{
    if (! str_starts_with($path, DIRECTORY_SEPARATOR) || ! is_file($path) || is_link($path)) {
        throw new RuntimeException('Runtime manifest file boundary failed.');
    }
    $raw = file_get_contents($path);
    if ($raw === false || strlen($raw) < 2 || strlen($raw) > 32768) {
        throw new RuntimeException('Runtime manifest read boundary failed.');
    }
    $value = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    if (! is_array($value)) {
        throw new RuntimeException('Runtime manifest object boundary failed.');
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

function assertHex(string $value, int $length): void
{
    if (preg_match('/\A[0-9a-f]{'.$length.'}\z/D', $value) !== 1) {
        throw new RuntimeException('Hex identity boundary failed.');
    }
}

function positiveIntOption(string $value): int
{
    if (preg_match('/\A[1-9][0-9]*\z/D', $value) !== 1) {
        throw new RuntimeException('Positive integer boundary failed.');
    }

    return (int) $value;
}

function assertId(string $value, string $pattern, string $label): void
{
    if (preg_match($pattern, $value) !== 1) {
        throw new RuntimeException($label.' boundary failed.');
    }
}

$stage = 'BOOT';

try {
    $stage = 'INPUTS';

    $options = cliOptions($argv);
    $appRoot = rtrim(requiredOption($options, 'app-root'), DIRECTORY_SEPARATOR);
    $runtimeEnv = requiredOption($options, 'runtime-env');
    $runtimeManifest = requiredOption($options, 'runtime-manifest');
    $canonicalMain = requiredOption($options, 'canonical-main');
    $targetPr = positiveIntOption(requiredOption($options, 'target-pr'));
    $targetHead = requiredOption($options, 'target-head');
    $bindingRunId = positiveIntOption(requiredOption($options, 'binding-run-id'));
    $bindingRunAttempt = positiveIntOption(requiredOption($options, 'binding-run-attempt'));
    $expectedDbBinding = requiredOption($options, 'expected-db-binding');
    $tenantId = requiredOption($options, 'tenant-id');
    $actorIdentityId = requiredOption($options, 'actor-identity-id');
    $actorOrganizationId = requiredOption($options, 'actor-organization-id');
    $roleId = requiredOption($options, 'role-id');
    $output = requiredOption($options, 'output');

    assertHex($canonicalMain, 40);
    assertHex($targetHead, 40);
    assertHex($expectedDbBinding, 64);
    assertId($tenantId, '/\A[a-z0-9][a-z0-9_-]{0,63}\z/D', 'Tenant ID');
    assertId($actorIdentityId, '/\A[a-z0-9][a-z0-9_-]{0,95}\z/D', 'Actor identity ID');
    assertId($actorOrganizationId, '/\A[a-z0-9][a-z0-9_-]{0,63}\z/D', 'Actor organization ID');
    assertId($roleId, '/\A[a-z][a-z0-9_-]{0,63}\z/D', 'Role ID');

    if (str_starts_with($roleId, 'platform-') || str_starts_with($roleId, 'platform_')) {
        throw new RuntimeException('Platform-prefixed role is not eligible.');
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
    ] as $requiredFile) {
        if (! is_file($requiredFile) || is_link($requiredFile) || ! is_readable($requiredFile)) {
            throw new RuntimeException('Required application file boundary failed.');
        }
    }

    $outputDir = dirname($output);
    if (! is_dir($outputDir) || is_link($outputDir) || ! is_writable($outputDir)) {
        throw new RuntimeException('Output directory boundary failed.');
    }

    $stage = 'RUNTIME_BINDING';
    $manifest = jsonObject($runtimeManifest);
    $expectedManifestKeys = [
        'environment_id',
        'exact_running_artifact_sha256',
        'exact_running_source_commit',
        'feature',
        'readiness_attestation_sha256',
        'runtime_class',
        'schema_version',
        'secrets_embedded',
        'selection_fingerprint_sha256',
        'selection_state',
        'trusted_ingestion',
    ];
    $actualManifestKeys = array_keys($manifest);
    sort($expectedManifestKeys, SORT_STRING);
    sort($actualManifestKeys, SORT_STRING);

    if ($actualManifestKeys !== $expectedManifestKeys
        || ($manifest['schema_version'] ?? null) !== 1
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
    $ingestionRunId = (string) ($manifest['trusted_ingestion']['run_id'] ?? '');
    $ingestionRunAttempt = (string) ($manifest['trusted_ingestion']['run_attempt'] ?? '');
    $ingestionFingerprint = (string) ($manifest['trusted_ingestion']['ingestion_fingerprint_sha256'] ?? '');

    assertId($environmentId, '/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D', 'Environment ID');
    assertId($runtimeClass, '/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D', 'Runtime class');
    if (in_array($runtimeClass, ['local', 'test', 'testing', 'ci', 'preview', 'synthetic-preview', 'production', 'prod'], true)) {
        throw new RuntimeException('Runtime class is not eligible for durable permission provisioning.');
    }

    assertHex($runningSource, 40);
    assertHex($runningArtifact, 64);
    assertHex($readinessAttestation, 64);
    assertHex($selectionFingerprint, 64);
    positiveIntOption($ingestionRunId);
    positiveIntOption($ingestionRunAttempt);
    assertHex($ingestionFingerprint, 64);

    $stage = 'DATABASE_CONFIGURATION';
    $env = dotenv($runtimeEnv);
    $host = envRequired($env, 'ONEQAY_DB_HOST');
    $database = envRequired($env, 'ONEQAY_DB_DATABASE');
    $username = envRequired($env, 'ONEQAY_DB_USERNAME');
    $password = envRequired($env, 'ONEQAY_DB_PASSWORD');
    $portRaw = $env['ONEQAY_DB_PORT'] ?? '3306';

    if (! is_string($portRaw)
        || preg_match('/\A[1-9][0-9]{0,4}\z/D', $portRaw) !== 1
        || (int) $portRaw < 1
        || (int) $portRaw > 65535
    ) {
        throw new RuntimeException('Runtime database port invalid.');
    }
    $port = (int) $portRaw;

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
    if ((string) getenv('APP_ENV') !== 'production') {
        throw new RuntimeException('Operational permission provisioning must retain production runtime classification.');
    }
    if (filter_var(getenv('ONEQAY_PERSISTENCE_ENABLED') ?: false, FILTER_VALIDATE_BOOL)) {
        throw new RuntimeException('Application persistence must remain disabled during isolated permission provisioning.');
    }

    $stage = 'DATABASE_BINDING';
    $identity = $db->selectOne(
        'SELECT DATABASE() AS database_name, @@hostname AS server_hostname, @@port AS server_port'
    );
    if (! is_object($identity)) {
        throw new RuntimeException('Selected-target database identity readback failed.');
    }

    $databaseName = trim((string) ($identity->database_name ?? ''));
    $serverHostname = trim((string) ($identity->server_hostname ?? ''));
    $serverPort = (int) ($identity->server_port ?? 0);

    if ($databaseName === '' || $serverHostname === '' || $serverPort < 1 || $serverPort > 65535) {
        throw new RuntimeException('Selected-target database identity readback is incomplete.');
    }

    $bindingPayload = [
        'database_name' => $databaseName,
        'server_hostname' => $serverHostname,
        'server_port' => $serverPort,
    ];
    $actualDbBinding = hash('sha256', canonicalJson($bindingPayload));
    if (! hash_equals($expectedDbBinding, $actualDbBinding)) {
        throw new RuntimeException('Permission provisioning database binding mismatch.');
    }

    $stage = 'PRE_MUTATION';
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
        ONEQAY_MIGRATION27_TABLE,
    ] as $table) {
        if (! $schema->hasTable($table)) {
            throw new RuntimeException('Required permission provisioning table missing.');
        }
    }

    if ((int) $db->table('migrations')->where('migration', ONEQAY_MIGRATION27_NAME)->count() !== 1) {
        throw new RuntimeException('Canonical migration27 execution record is not exact.');
    }

    $tenant = App\Domain\Tenancy\TenantId::fromString($tenantId);
    $actorId = App\Domain\Identity\PlatformIdentityId::fromString($actorIdentityId);
    $organization = App\Domain\Organization\OrganizationId::fromString($actorOrganizationId);
    $role = App\Application\Authorization\RoleIdentifier::fromString($roleId);
    $permission = App\Application\Pos\FinalShiftClosePermission::identifier();

    if (! hash_equals(ONEQAY_PERMISSION_ID, $permission->value())) {
        throw new RuntimeException('Canonical Final Shift Close permission identifier drifted.');
    }

    $actor = new App\Application\Organization\VerifiedOrganizationalContext(
        $actorId,
        $tenant,
        $organization,
    );
    $mutationId = App\Application\Authorization\PolicyMutationId::fromString(
        'fscperm_'.substr(hash('sha256', $targetHead.'|'.$tenant->value().'|'.$role->value()), 0, 55),
    );
    $mutation = App\Application\Authorization\DurablePolicyMutation::permissionGrant(
        $mutationId,
        $actor,
        $role,
        $permission,
    );
    $fingerprint = $mutation->fingerprint($actor);
    assertHex($fingerprint, 64);

    $assignmentTables = [
        'oneqay_tenant_role_assignments',
        'oneqay_organization_role_assignments',
        'oneqay_outlet_role_assignments',
        'oneqay_device_role_assignments',
    ];

    $assignmentCounts = [];
    foreach ($assignmentTables as $table) {
        $assignmentCounts[$table] = (int) $db->table($table)
            ->where('tenant_id', $tenant->value())
            ->where('role_id', $role->value())
            ->count();
    }

    $stage = 'PROVISIONING_TRANSACTION';
    $occurredAtUnix = time();
    if ($occurredAtUnix <= 0) {
        throw new RuntimeException('Operational provisioning clock returned an invalid timestamp.');
    }

    $db->transaction(function () use (
        $db,
        $tenant,
        $actorId,
        $organization,
        $role,
        $permission,
        $mutationId,
        $fingerprint,
        $assignmentTables,
        $assignmentCounts,
        $occurredAtUnix,
    ): void {
        $tenantRow = $db->table('oneqay_tenants')
            ->where('id', $tenant->value())
            ->lockForUpdate()
            ->first();
        if ($tenantRow === null) {
            throw new RuntimeException('Target tenant does not exist.');
        }

        $identityRow = $db->table('oneqay_identities')
            ->where('tenant_id', $tenant->value())
            ->where('id', $actorId->value())
            ->lockForUpdate()
            ->first();
        if ($identityRow === null) {
            throw new RuntimeException('Provisioning actor is not an existing same-tenant identity.');
        }

        $membership = $db->table('oneqay_identity_organizations')
            ->where('tenant_id', $tenant->value())
            ->where('identity_id', $actorId->value())
            ->where('organization_id', $organization->value())
            ->lockForUpdate()
            ->first();
        if ($membership === null) {
            throw new RuntimeException('Provisioning actor organization membership is not durable.');
        }

        $roleRow = $db->table('oneqay_roles')
            ->where('tenant_id', $tenant->value())
            ->where('id', $role->value())
            ->lockForUpdate()
            ->first();
        if ($roleRow === null) {
            throw new RuntimeException('Target role does not exist.');
        }

        $actorAssignments = $db->table('oneqay_tenant_role_assignments')
            ->where('tenant_id', $tenant->value())
            ->where('identity_id', $actorId->value())
            ->lockForUpdate()
            ->get();

        $tenantControl = false;
        foreach ($actorAssignments as $assignment) {
            $controlRoleId = $assignment->role_id ?? null;
            if (! is_string($controlRoleId) || $controlRoleId === '') {
                continue;
            }

            $controlPermission = $db->table('oneqay_role_permissions')
                ->where('tenant_id', $tenant->value())
                ->where('role_id', $controlRoleId)
                ->where('permission_id', App\Application\Authorization\AdministrationPermission::MANAGE)
                ->lockForUpdate()
                ->first();

            if ($controlPermission !== null) {
                $tenantControl = true;
                break;
            }
        }

        if (! $tenantControl) {
            throw new RuntimeException('Provisioning actor lacks tenant-scoped protected control authority.');
        }

        $protectedTarget = $db->table('oneqay_role_permissions')
            ->where('tenant_id', $tenant->value())
            ->where('role_id', $role->value())
            ->where('permission_id', App\Application\Authorization\AdministrationPermission::MANAGE)
            ->lockForUpdate()
            ->first();
        if ($protectedTarget !== null) {
            throw new RuntimeException('Protected control role cannot receive Final Shift Close permission through this executor.');
        }

        $existingGrant = $db->table('oneqay_role_permissions')
            ->where('tenant_id', $tenant->value())
            ->where('role_id', $role->value())
            ->where('permission_id', $permission->value())
            ->lockForUpdate()
            ->first();
        if ($existingGrant !== null) {
            throw new RuntimeException('Final Shift Close permission already exists on target role; refusing retrospective evidence.');
        }

        $existingJournal = $db->table('oneqay_policy_mutations')
            ->where('tenant_id', $tenant->value())
            ->where('mutation_id', $mutationId->value())
            ->lockForUpdate()
            ->first();
        if ($existingJournal !== null) {
            throw new RuntimeException('Deterministic provisioning mutation journal already exists; refusing ambiguous evidence.');
        }

        if (! $db->table('oneqay_policy_mutations')->insert([
            'tenant_id' => $tenant->value(),
            'mutation_id' => $mutationId->value(),
            'actor_identity_id' => $actorId->value(),
            'operation' => App\Application\Authorization\PolicyMutationOperation::PERMISSION_GRANT,
            'scope_type' => 'tenant',
            'organization_id' => null,
            'outlet_id' => null,
            'device_id' => null,
            'target_identity_id' => null,
            'role_id' => $role->value(),
            'permission_id' => $permission->value(),
            'payload_fingerprint' => $fingerprint,
            'outcome' => 'applied',
            'occurred_at_unix' => $occurredAtUnix,
        ])) {
            throw new RuntimeException('Canonical policy mutation journal insert failed.');
        }

        if (! $db->table('oneqay_role_permissions')->insert([
            'tenant_id' => $tenant->value(),
            'role_id' => $role->value(),
            'permission_id' => $permission->value(),
        ])) {
            throw new RuntimeException('Final Shift Close role permission insert failed.');
        }

        if ((int) $db->table('oneqay_role_permissions')
            ->where('tenant_id', $tenant->value())
            ->where('role_id', $role->value())
            ->where('permission_id', $permission->value())
            ->count() !== 1
        ) {
            throw new RuntimeException('Final Shift Close permission grant is not exact after provisioning.');
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
            throw new RuntimeException('Canonical policy mutation journal evidence is invalid.');
        }

        foreach ($assignmentTables as $table) {
            $after = (int) $db->table($table)
                ->where('tenant_id', $tenant->value())
                ->where('role_id', $role->value())
                ->count();

            if ($after !== $assignmentCounts[$table]) {
                throw new RuntimeException('Permission provisioning changed a role assignment boundary.');
            }
        }
    }, 1);

    $stage = 'POST_VERIFICATION';

    if ((int) $db->table('oneqay_role_permissions')
        ->where('tenant_id', $tenant->value())
        ->where('role_id', $role->value())
        ->where('permission_id', $permission->value())
        ->count() !== 1
    ) {
        throw new RuntimeException('Post-provisioning permission readback failed.');
    }

    $journal = $db->table('oneqay_policy_mutations')
        ->where('tenant_id', $tenant->value())
        ->where('mutation_id', $mutationId->value())
        ->first();

    if ($journal === null
        || ! hash_equals($fingerprint, (string) ($journal->payload_fingerprint ?? ''))
        || ! hash_equals('applied', (string) ($journal->outcome ?? ''))
    ) {
        throw new RuntimeException('Post-provisioning journal readback failed.');
    }

    foreach ($assignmentTables as $table) {
        $after = (int) $db->table($table)
            ->where('tenant_id', $tenant->value())
            ->where('role_id', $role->value())
            ->count();

        if ($after !== $assignmentCounts[$table]) {
            throw new RuntimeException('Role assignment boundary changed after transaction.');
        }
    }

    $stage = 'EVIDENCE_PUBLICATION';
    $issued = time();
    $payload = [
        'schema_version' => ONEQAY_PERMISSION_LOCAL_SCHEMA_VERSION,
        'evidence_type' => ONEQAY_PERMISSION_LOCAL_EVIDENCE_TYPE,
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
        'trusted_ingestion_run_id' => (int) $ingestionRunId,
        'trusted_ingestion_run_attempt' => (int) $ingestionRunAttempt,
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
        'attestation_mode' => ONEQAY_PERMISSION_ATTESTATION_MODE,
        'issued_at_unix' => $issued,
        'expires_at_unix' => $issued + ONEQAY_PERMISSION_LOCAL_TTL_SECONDS,
        'nonce' => bin2hex(random_bytes(16)),
        'secrets_embedded' => false,
    ];

    $signingKey = hash_hmac(
        'sha256',
        ONEQAY_PERMISSION_LOCAL_SIGNING_CONTEXT,
        $password,
        true,
    );
    $hmac = hash_hmac('sha256', canonicalJson($payload), $signingKey);
    $signed = $payload + ['hmac_sha256' => $hmac];
    $encoded = base64_encode(canonicalJson($signed));

    if (strlen($encoded) > 32768) {
        throw new RuntimeException('Local permission provisioning evidence oversized.');
    }

    writePrivate($output, $encoded."\n");

    fwrite(STDOUT, "RESULT=SUCCESS\n");
    fwrite(STDOUT, "MODE=CPANEL_LOCAL_PERMISSION_PROVISIONING\n");
    fwrite(STDOUT, "DATABASE_BINDING_SHA256={$actualDbBinding}\n");
    fwrite(STDOUT, "TENANT_ID={$tenant->value()}\n");
    fwrite(STDOUT, "ROLE_ID={$role->value()}\n");
    fwrite(STDOUT, "PERMISSION_ID={$permission->value()}\n");
    fwrite(STDOUT, "MIGRATION27=EXECUTED\n");
    fwrite(STDOUT, "PERMISSION_PROVISIONING=PROVISIONED\n");
    fwrite(STDOUT, "POLICY_JOURNAL_VERIFICATION=PASS\n");
    fwrite(STDOUT, "ROLE_ASSIGNMENT_BOUNDARY=UNCHANGED\n");
    fwrite(STDOUT, "FINAL_SHIFT_CLOSE_FEATURE=INACTIVE\n");
    fwrite(STDOUT, "OUTPUT={$output}\n");
    fwrite(STDOUT, "EXPIRES_AT_UNIX=".($issued + ONEQAY_PERMISSION_LOCAL_TTL_SECONDS)."\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "RESULT=FAILED\n");
    fwrite(STDERR, "FAILED_STAGE={$stage}\n");
    fwrite(STDERR, "ERROR=LOCAL_PERMISSION_PROVISIONING_FAILED\n");
    exit(1);
}
