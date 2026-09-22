<?php

declare(strict_types=1);

// Author by Lab | zefry

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "RESULT=FAILED\nFAILED_STAGE=CLI_ONLY_REQUIRED\n");
    exit(2);
}

const ONEQAY_MIGRATION27_LOCAL_EXECUTION_SCHEMA_VERSION = 1;
const ONEQAY_MIGRATION27_LOCAL_EXECUTION_EVIDENCE_TYPE = 'CPANEL_LOCAL_MIGRATION27_EXECUTION';
const ONEQAY_MIGRATION27_LOCAL_EXECUTION_TTL_SECONDS = 900;
const ONEQAY_MIGRATION27_LOCAL_EXECUTION_SIGNING_CONTEXT = 'oneqay-migration27-cpanel-local-execution-v1';
const ONEQAY_MIGRATION27_NAME = '0000_00_00_000027_create_pos_shift_close_evidence_foundation';
const ONEQAY_MIGRATION27_RELATIVE_PATH = 'database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php';
const ONEQAY_MIGRATION27_TABLE = 'oneqay_pos_shift_close_evidence';
const ONEQAY_MIGRATION27_GIT_BLOB_SHA1 = 'a412560c2f340f4783a385aef729dbd074389a4c';

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

    $temp = $path.'.tmp.'.bin2hex(random_bytes(8));
    if (file_put_contents($temp, $contents, LOCK_EX) === false) {
        throw new RuntimeException('Temporary evidence write failed.');
    }
    chmod($temp, 0600);
    if (! rename($temp, $path)) {
        @unlink($temp);
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

function assertPositiveIntString(string $value): int
{
    if (preg_match('/\A[1-9][0-9]*\z/D', $value) !== 1) {
        throw new RuntimeException('Positive integer identity boundary failed.');
    }
    return (int) $value;
}

$stage = 'BOOT';

try {
    $stage = 'INPUTS';
    if (! extension_loaded('pdo_mysql')) {
        throw new RuntimeException('pdo_mysql unavailable.');
    }

    $options = cliOptions($argv);
    $appRoot = rtrim(requiredOption($options, 'app-root'), DIRECTORY_SEPARATOR);
    $runtimeEnv = requiredOption($options, 'runtime-env');
    $runtimeManifest = requiredOption($options, 'runtime-manifest');
    $canonicalMain = requiredOption($options, 'canonical-main');
    $targetPr = assertPositiveIntString(requiredOption($options, 'target-pr'));
    $targetHead = requiredOption($options, 'target-head');
    $bindingRunId = assertPositiveIntString(requiredOption($options, 'binding-run-id'));
    $bindingRunAttempt = assertPositiveIntString(requiredOption($options, 'binding-run-attempt'));
    $expectedDbBinding = requiredOption($options, 'expected-db-binding');
    $output = requiredOption($options, 'output');

    assertHex($canonicalMain, 40);
    assertHex($targetHead, 40);
    assertHex($expectedDbBinding, 64);

    if (! str_starts_with($appRoot, DIRECTORY_SEPARATOR)
        || ! is_dir($appRoot)
        || is_link($appRoot)
        || realpath($appRoot) !== $appRoot
    ) {
        throw new RuntimeException('Application root boundary failed.');
    }

    $migrationPath = $appRoot.DIRECTORY_SEPARATOR.ONEQAY_MIGRATION27_RELATIVE_PATH;
    $autoloadPath = $appRoot.DIRECTORY_SEPARATOR.'vendor/autoload.php';
    $bootstrapPath = $appRoot.DIRECTORY_SEPARATOR.'bootstrap/app.php';

    foreach ([$migrationPath, $autoloadPath, $bootstrapPath] as $requiredFile) {
        if (! is_file($requiredFile) || is_link($requiredFile) || ! is_readable($requiredFile)) {
            throw new RuntimeException('Required application file boundary failed.');
        }
    }

    $migrationRaw = file_get_contents($migrationPath);
    if ($migrationRaw === false) {
        throw new RuntimeException('Migration source read failed.');
    }
    $migrationBlob = sha1('blob '.strlen($migrationRaw)."\0".$migrationRaw);
    if (! hash_equals(ONEQAY_MIGRATION27_GIT_BLOB_SHA1, $migrationBlob)) {
        throw new RuntimeException('Migration source identity mismatch.');
    }

    $outputDir = dirname($output);
    if (! is_dir($outputDir) || is_link($outputDir) || ! is_writable($outputDir)) {
        throw new RuntimeException('Output directory preflight failed.');
    }
    $writeProbe = $outputDir.DIRECTORY_SEPARATOR.'.migration27-write-probe-'.bin2hex(random_bytes(8));
    if (file_put_contents($writeProbe, 'ok', LOCK_EX) === false) {
        throw new RuntimeException('Output directory write preflight failed.');
    }
    chmod($writeProbe, 0600);
    @unlink($writeProbe);

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

    if (preg_match('/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D', $environmentId) !== 1
        || preg_match('/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D', $runtimeClass) !== 1
        || in_array($runtimeClass, ['local', 'test', 'testing', 'ci', 'preview', 'synthetic-preview', 'production', 'prod'], true)
    ) {
        throw new RuntimeException('Runtime identity is not eligible.');
    }
    assertHex($runningSource, 40);
    assertHex($runningArtifact, 64);
    assertHex($readinessAttestation, 64);
    assertHex($selectionFingerprint, 64);
    assertPositiveIntString($ingestionRunId);
    assertPositiveIntString($ingestionRunAttempt);
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
    require $autoloadPath;
    $app = require $bootstrapPath;
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $db = $app->make('db')->connection('oneqay');
    $schema = $db->getSchemaBuilder();

    if (strtolower($db->getDriverName()) !== 'mysql') {
        throw new RuntimeException('Canonical MySQL target required.');
    }

    $stage = 'DATABASE_BINDING';
    $identity = $db->selectOne('SELECT DATABASE() AS database_name, @@hostname AS server_hostname, @@port AS server_port');
    if (! is_object($identity)) {
        throw new RuntimeException('Database identity readback failed.');
    }
    $databaseName = trim((string) ($identity->database_name ?? ''));
    $serverHostname = trim((string) ($identity->server_hostname ?? ''));
    $serverPort = (int) ($identity->server_port ?? 0);
    if ($databaseName === '' || $serverHostname === '' || $serverPort < 1 || $serverPort > 65535) {
        throw new RuntimeException('Database identity readback incomplete.');
    }

    $bindingPayload = [
        'database_name' => $databaseName,
        'server_hostname' => $serverHostname,
        'server_port' => $serverPort,
    ];
    $actualDbBinding = hash('sha256', canonicalJson($bindingPayload));
    if (! hash_equals($expectedDbBinding, $actualDbBinding)) {
        throw new RuntimeException('Database binding mismatch.');
    }

    $stage = 'PREDECESSOR_PREFLIGHT';
    if (! $schema->hasTable('migrations')) {
        throw new RuntimeException('Canonical migrations table missing.');
    }
    if ($schema->hasTable(ONEQAY_MIGRATION27_TABLE)) {
        throw new RuntimeException('Migration27 table already exists.');
    }
    if ((int) $db->table('migrations')->where('migration', ONEQAY_MIGRATION27_NAME)->count() !== 0) {
        throw new RuntimeException('Migration27 already recorded.');
    }

    $requiredTables = [
        'oneqay_pos_shifts',
        'oneqay_pos_shift_opening_cash_evidence',
        'oneqay_pos_shift_closing_cash_evidence',
        'oneqay_pos_cash_variance_review_decision_evidence',
        'oneqay_identities',
        'oneqay_organizations',
        'oneqay_outlets',
        'oneqay_devices',
    ];
    foreach ($requiredTables as $table) {
        if (! $schema->hasTable($table)) {
            throw new RuntimeException('Required predecessor table missing.');
        }
    }

    $requiredMigrations = [
        '0000_00_00_000018_create_pos_shift_opening_foundation',
        '0000_00_00_000022_create_pos_shift_opening_cash_evidence_foundation',
        '0000_00_00_000023_create_pos_shift_closing_cash_evidence_foundation',
        '0000_00_00_000026_create_pos_cash_variance_review_decision_evidence_foundation',
    ];
    $recorded = $db->table('migrations')->whereIn('migration', $requiredMigrations)->pluck('migration')->all();
    sort($recorded);
    sort($requiredMigrations);
    if ($recorded !== $requiredMigrations) {
        throw new RuntimeException('Required predecessor migrations missing.');
    }

    $stage = 'MIGRATION_EXECUTION';
    $exit = $kernel->call('migrate', [
        '--path' => ONEQAY_MIGRATION27_RELATIVE_PATH,
        '--force' => true,
    ]);
    if ($exit !== 0) {
        throw new RuntimeException('Migration command failed.');
    }

    $stage = 'POST_SCHEMA_VERIFICATION';
    if (! $schema->hasTable(ONEQAY_MIGRATION27_TABLE)) {
        throw new RuntimeException('Migration27 table missing after execution.');
    }
    $migrationRecord = $db->table('migrations')->where('migration', ONEQAY_MIGRATION27_NAME)->first();
    if (! is_object($migrationRecord)) {
        throw new RuntimeException('Migration27 ledger record missing.');
    }
    if ((int) $db->table(ONEQAY_MIGRATION27_TABLE)->count() !== 0) {
        throw new RuntimeException('Migration27 table not empty after execution.');
    }

    $indexes = $db->select(
        "SELECT DISTINCT INDEX_NAME AS name FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'oneqay_pos_shift_close_evidence'"
    );
    $indexNames = array_map(static fn (object $row): string => (string) $row->name, $indexes);
    foreach (['uq_pos_shift_close_operation', 'uq_pos_shift_close_shift', 'ix_pos_shift_close_outlet_time'] as $requiredIndex) {
        if (! in_array($requiredIndex, $indexNames, true)) {
            throw new RuntimeException('Required migration27 index missing.');
        }
    }

    $constraints = $db->select(
        "SELECT CONSTRAINT_NAME AS name, CONSTRAINT_TYPE AS type FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'oneqay_pos_shift_close_evidence'"
    );
    $checkFound = false;
    foreach ($constraints as $constraint) {
        if ((string) $constraint->name === 'chk_pos_shift_close_variance_review'
            && strtoupper((string) $constraint->type) === 'CHECK'
        ) {
            $checkFound = true;
        }
    }
    if (! $checkFound) {
        throw new RuntimeException('Required migration27 CHECK constraint missing.');
    }

    $columns = $schema->getColumnListing(ONEQAY_MIGRATION27_TABLE);
    foreach ([
        'tenant_id',
        'evidence_id',
        'operation_id',
        'payload_fingerprint',
        'shift_id',
        'variance_atomic',
        'review_evidence_id',
        'review_outcome',
        'closed_at_unix',
    ] as $requiredColumn) {
        if (! in_array($requiredColumn, $columns, true)) {
            throw new RuntimeException('Required migration27 column missing.');
        }
    }

    $stage = 'EVIDENCE_PUBLICATION';
    $issued = time();
    $payload = [
        'schema_version' => ONEQAY_MIGRATION27_LOCAL_EXECUTION_SCHEMA_VERSION,
        'evidence_type' => ONEQAY_MIGRATION27_LOCAL_EXECUTION_EVIDENCE_TYPE,
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
        'migration_blob_sha1' => $migrationBlob,
        'migration_name' => ONEQAY_MIGRATION27_NAME,
        'migration27_state' => 'EXECUTED',
        'migration_batch' => (int) ($migrationRecord->batch ?? 0),
        'table_row_count' => 0,
        'required_indexes_verified' => true,
        'variance_review_check_verified' => true,
        'required_columns_verified' => true,
        'attestation_mode' => 'LOCAL_EXECUTION_AND_POST_SCHEMA_VERIFICATION',
        'issued_at_unix' => $issued,
        'expires_at_unix' => $issued + ONEQAY_MIGRATION27_LOCAL_EXECUTION_TTL_SECONDS,
        'nonce' => bin2hex(random_bytes(16)),
        'secrets_embedded' => false,
    ];

    if ($payload['migration_batch'] < 1) {
        throw new RuntimeException('Migration batch evidence invalid.');
    }

    $signingKey = hash_hmac(
        'sha256',
        ONEQAY_MIGRATION27_LOCAL_EXECUTION_SIGNING_CONTEXT,
        $password,
        true,
    );
    $hmac = hash_hmac('sha256', canonicalJson($payload), $signingKey);
    $signed = $payload + ['hmac_sha256' => $hmac];
    $encoded = base64_encode(canonicalJson($signed));
    if (strlen($encoded) > 32768) {
        throw new RuntimeException('Local execution evidence oversized.');
    }

    writePrivate($output, $encoded."\n");

    fwrite(STDOUT, "RESULT=SUCCESS\n");
    fwrite(STDOUT, "MODE=CPANEL_LOCAL_MIGRATION27_EXECUTION\n");
    fwrite(STDOUT, "OUTPUT={$output}\n");
    fwrite(STDOUT, "EXPIRES_AT_UNIX=".($issued + ONEQAY_MIGRATION27_LOCAL_EXECUTION_TTL_SECONDS)."\n");
    fwrite(STDOUT, "DATABASE_BINDING_SHA256={$actualDbBinding}\n");
    fwrite(STDOUT, "MIGRATION27=EXECUTED\n");
    fwrite(STDOUT, "POST_SCHEMA_VERIFICATION=PASS\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "RESULT=FAILED\n");
    fwrite(STDERR, "FAILED_STAGE={$stage}\n");
    fwrite(STDERR, "ERROR=LOCAL_MIGRATION27_EXECUTION_FAILED\n");
    exit(1);
}
