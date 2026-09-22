<?php

declare(strict_types=1);

// Author by Lab | zefry

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI_ONLY\n");
    exit(2);
}

const ONEQAY_PROBE_SCHEMA_VERSION = 1;
const ONEQAY_PROBE_EVIDENCE_TYPE = 'CPANEL_LOCAL_INDEPENDENT_PDO_READBACK';
const ONEQAY_PROBE_TTL_SECONDS = 900;
const ONEQAY_DATABASE_BINDING_ALGORITHM = 'SHA256_CANONICAL_JSON_DATABASE_HOSTNAME_PORT_V1';
const ONEQAY_MIGRATION27 = '0000_00_00_000027_create_pos_shift_close_evidence_foundation';
const ONEQAY_MIGRATION27_TABLE = 'oneqay_pos_shift_close_evidence';
const ONEQAY_SIGNING_CONTEXT = 'oneqay-migration27-cpanel-local-probe-v1';

/** @return array<string,string> */
function options(array $argv): array
{
    $out = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (! str_starts_with($arg, '--') || ! str_contains($arg, '=')) {
            throw new InvalidArgumentException('Arguments must use --name=value.');
        }
        [$key, $value] = explode('=', substr($arg, 2), 2);
        if ($key === '' || $value === '' || isset($out[$key])) {
            throw new InvalidArgumentException('Argument set is invalid.');
        }
        $out[$key] = $value;
    }
    return $out;
}

function requiredOption(array $opts, string $name): string
{
    $value = $opts[$name] ?? '';
    if (! is_string($value) || $value === '') {
        throw new InvalidArgumentException('Missing required option: '.$name);
    }
    return $value;
}

/** @return array<string,mixed> */
function jsonObject(string $path): array
{
    if (! str_starts_with($path, DIRECTORY_SEPARATOR) || ! is_file($path) || is_link($path)) {
        throw new RuntimeException('Private JSON input must be an absolute regular non-symlink file.');
    }
    $raw = file_get_contents($path);
    if ($raw === false || strlen($raw) < 2 || strlen($raw) > 32768) {
        throw new RuntimeException('Private JSON input is unreadable or oversized.');
    }
    $value = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    if (! is_array($value)) {
        throw new RuntimeException('Private JSON input must decode to an object.');
    }
    return $value;
}

/** @return array<string,string> */
function dotenv(string $path): array
{
    if (! str_starts_with($path, DIRECTORY_SEPARATOR) || ! is_file($path) || is_link($path)) {
        throw new RuntimeException('Runtime env must be an absolute regular non-symlink file.');
    }
    $raw = file_get_contents($path);
    if ($raw === false || strlen($raw) < 2 || strlen($raw) > 131072) {
        throw new RuntimeException('Runtime env is unreadable or oversized.');
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
        throw new RuntimeException('Required runtime database environment value is missing.');
    }
    return $value;
}

function canonicalJson(array $payload): string
{
    ksort($payload, SORT_STRING);
    return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

function sha256Json(array $payload): string
{
    return hash('sha256', canonicalJson($payload));
}

function assertHex(string $value, int $length): void
{
    if (preg_match('/\A[0-9a-f]{'.$length.'}\z/D', $value) !== 1) {
        throw new RuntimeException('Required hexadecimal identity is invalid.');
    }
}

function writePrivate(string $path, string $contents): void
{
    if (! str_starts_with($path, DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Output path must be absolute.');
    }
    $dir = dirname($path);
    if (! is_dir($dir) || is_link($dir)) {
        throw new RuntimeException('Output parent must be an existing non-symlink directory.');
    }
    $temp = $path.'.tmp.'.bin2hex(random_bytes(8));
    if (file_put_contents($temp, $contents, LOCK_EX) === false) {
        throw new RuntimeException('Cannot write temporary probe evidence.');
    }
    chmod($temp, 0600);
    if (! rename($temp, $path)) {
        @unlink($temp);
        throw new RuntimeException('Cannot atomically publish probe evidence.');
    }
    chmod($path, 0600);
}

try {
    if (! extension_loaded('pdo_mysql')) {
        throw new RuntimeException('pdo_mysql is required.');
    }

    $opts = options($argv);
    $envPath = requiredOption($opts, 'runtime-env');
    $manifestPath = requiredOption($opts, 'runtime-manifest');
    $canonicalMain = requiredOption($opts, 'canonical-main');
    $output = requiredOption($opts, 'output');
    assertHex($canonicalMain, 40);

    $manifest = jsonObject($manifestPath);
    $manifestKeys = array_keys($manifest);
    sort($manifestKeys, SORT_STRING);
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
    sort($expectedManifestKeys, SORT_STRING);
    if ($manifestKeys !== $expectedManifestKeys
        || ($manifest['schema_version'] ?? null) !== 1
        || ($manifest['feature'] ?? null) !== 'final-shift-close'
        || ($manifest['selection_state'] ?? null) !== 'SELECTED_NOT_AUTHORIZED'
        || ($manifest['secrets_embedded'] ?? null) !== false
    ) {
        throw new RuntimeException('Runtime binding manifest contract mismatch.');
    }

    $environmentId = (string) ($manifest['environment_id'] ?? '');
    $runtimeClass = (string) ($manifest['runtime_class'] ?? '');
    $runningSource = (string) ($manifest['exact_running_source_commit'] ?? '');
    $runningArtifact = (string) ($manifest['exact_running_artifact_sha256'] ?? '');
    $readinessAttestation = (string) ($manifest['readiness_attestation_sha256'] ?? '');
    $selectionFingerprint = (string) ($manifest['selection_fingerprint_sha256'] ?? '');
    if (preg_match('/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D', $environmentId) !== 1
        || preg_match('/\A[a-z0-9][a-z0-9._:-]{7,127}\z/D', $runtimeClass) !== 1
        || in_array($runtimeClass, ['local','test','testing','ci','preview','synthetic-preview','production','prod'], true)
    ) {
        throw new RuntimeException('Runtime binding identity is not eligible.');
    }
    assertHex($runningSource, 40);
    assertHex($runningArtifact, 64);
    assertHex($readinessAttestation, 64);
    assertHex($selectionFingerprint, 64);

    $env = dotenv($envPath);
    $host = envRequired($env, 'ONEQAY_DB_HOST');
    $database = envRequired($env, 'ONEQAY_DB_DATABASE');
    $username = envRequired($env, 'ONEQAY_DB_USERNAME');
    $password = envRequired($env, 'ONEQAY_DB_PASSWORD');
    $portRaw = $env['ONEQAY_DB_PORT'] ?? '3306';
    if (! is_string($portRaw) || preg_match('/\A[1-9][0-9]{0,4}\z/D', $portRaw) !== 1) {
        throw new RuntimeException('Runtime ONEQAY_DB_PORT is invalid.');
    }
    $port = (int) $portRaw;
    if ($port < 1 || $port > 65535 || str_contains($host, ';') || preg_match('/\s/', $host) === 1 || str_contains($database, ';') || preg_match('/\s/', $database) === 1) {
        throw new RuntimeException('Runtime database endpoint is invalid.');
    }

    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database),
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5, PDO::ATTR_EMULATE_PREPARES => false],
    );

    $identity = $pdo->query('SELECT DATABASE() AS database_name, @@hostname AS server_hostname, @@port AS server_port')->fetch(PDO::FETCH_ASSOC);
    if (! is_array($identity)) {
        throw new RuntimeException('Selected-target database identity readback failed.');
    }
    $databaseName = trim((string) ($identity['database_name'] ?? ''));
    $serverHostname = trim((string) ($identity['server_hostname'] ?? ''));
    $serverPort = (int) ($identity['server_port'] ?? 0);
    if ($databaseName === '' || $serverHostname === '' || $serverPort < 1 || $serverPort > 65535) {
        throw new RuntimeException('Selected-target database identity readback is incomplete.');
    }

    $tableCheck = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $tableCheck->execute(['migrations']);
    if ((int) $tableCheck->fetchColumn() !== 1) {
        throw new RuntimeException('Canonical migrations table is missing on selected-target database.');
    }
    $tableCheck->execute([ONEQAY_MIGRATION27_TABLE]);
    if ((int) $tableCheck->fetchColumn() !== 0) {
        throw new RuntimeException('Migration27 table already exists; refusing retrospective evidence.');
    }
    $migrationCheck = $pdo->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
    $migrationCheck->execute([ONEQAY_MIGRATION27]);
    if ((int) $migrationCheck->fetchColumn() !== 0) {
        throw new RuntimeException('Migration27 is already recorded; refusing retrospective evidence.');
    }

    $databaseBinding = sha256Json([
        'database_name' => $databaseName,
        'server_hostname' => $serverHostname,
        'server_port' => $serverPort,
    ]);
    $credentialBinding = sha256Json([
        'database' => $database,
        'host' => $host,
        'port' => $port,
        'username' => $username,
    ]);

    $issued = time();
    $payload = [
        'schema_version' => ONEQAY_PROBE_SCHEMA_VERSION,
        'evidence_type' => ONEQAY_PROBE_EVIDENCE_TYPE,
        'canonical_main_sha' => $canonicalMain,
        'environment_id' => $environmentId,
        'runtime_class' => $runtimeClass,
        'exact_running_source_commit' => $runningSource,
        'exact_running_artifact_sha256' => $runningArtifact,
        'readiness_attestation_sha256' => $readinessAttestation,
        'selection_fingerprint_sha256' => $selectionFingerprint,
        'database_binding_algorithm' => ONEQAY_DATABASE_BINDING_ALGORITHM,
        'database_binding_sha256' => $databaseBinding,
        'credential_binding_sha256' => $credentialBinding,
        'migration27_state' => 'NOT_EXECUTED',
        'attestation_mode' => 'READ_ONLY',
        'issued_at_unix' => $issued,
        'expires_at_unix' => $issued + ONEQAY_PROBE_TTL_SECONDS,
        'nonce' => bin2hex(random_bytes(16)),
        'secrets_embedded' => false,
    ];

    $signingKey = hash_hmac('sha256', ONEQAY_SIGNING_CONTEXT, $password, true);
    $hmac = hash_hmac('sha256', canonicalJson($payload), $signingKey);
    $signed = $payload + ['hmac_sha256' => $hmac];
    $encoded = base64_encode(canonicalJson($signed));
    if (strlen($encoded) > 32768) {
        throw new RuntimeException('Signed probe evidence is oversized.');
    }
    writePrivate($output, $encoded."\n");

    fwrite(STDOUT, "RESULT=SUCCESS\n");
    fwrite(STDOUT, "MODE=CPANEL_LOCAL_INDEPENDENT_PDO_READBACK\n");
    fwrite(STDOUT, "OUTPUT={$output}\n");
    fwrite(STDOUT, "EXPIRES_AT_UNIX=".($issued + ONEQAY_PROBE_TTL_SECONDS)."\n");
    fwrite(STDOUT, "DATABASE_BINDING_SHA256={$databaseBinding}\n");
    fwrite(STDOUT, "MIGRATION27=NOT_EXECUTED\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "RESULT=FAILED\n");
    fwrite(STDERR, "ERROR=LOCAL_DB_BINDING_PROBE_FAILED\n");
    exit(1);
}
