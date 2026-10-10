<?php
declare(strict_types=1);

/**
 * oneQayDev Production BUSINESS preactivation INSPECTOR (read-only).
 * Author by Lab | zefry.
 *
 * Deliberately NO activate/approve/provision/migrate commands. This code can
 * examine existing private host state and run non-mutating SELECT queries.
 * It never enables routes, writes runtime configuration, issues authority,
 * handles credentials, or changes the document root.
 */
final class OneQayDevBusinessPreflight
{
    public const ROOT = '/home/pekd7254/oneqay-production-test';
    public const PUBLIC_ROOT = '/home/pekd7254/public_html/oneqaydev.n07.my.id';
    public const STAGING_ROOT = '/home/pekd7254/public_html/oneqay.n07.my.id';
    public const RELEASE_ID = 'production-409ac6b2bdb8';
    public const SOURCE = '409ac6b2bdb80d31fbfb9425ddbb413a278b97e6';
    public const ARTIFACT_SHA256 = '8ad336c2396fb5d6e95bcea80c574a305040c6ba4f570c9a2edea911186f3f3e';
    public const HEALTH_BRIDGE_SHA256 = 'fba4792b0e8ef6f8d69bd13d71ae7d91bbcc120c4326d4d47ae3cee73c740529';

    // Explicit minimums for authenticated CASH POS and privileged operations.
    private const REQUIRED_ON = [
        'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED',
        'ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED',
        'ONEQAY_PRIVILEGED_STEP_UP_ENABLED',
        'ONEQAY_POS_SALE_COMPLETION_ENABLED',
        'ONEQAY_POS_CATALOG_PREPARATION_ENABLED',
        'ONEQAY_POS_INVENTORY_BASELINE_ENABLED',
        'ONEQAY_POS_SHIFT_OPENING_ENABLED',
        'ONEQAY_POS_SHIFT_OPENING_CASH_EVIDENCE_ENABLED',
        'ONEQAY_POS_SHIFT_CLOSING_CASH_EVIDENCE_ENABLED',
    ];
    private const REQUIRED_OFF = [
        'ONEQAY_PRODUCTION_TRANSACTION_ACTIVATION_ENABLED',
        'ONEQAY_PRODUCTION_BUSINESS_TRAFFIC_AUTHORIZED',
    ];

    public static function readEnv(string $raw): array
    {
        $found = [];
        foreach (preg_split('/\r\n|\n|\r/', $raw) as $line) {
            if (!preg_match('/^([A-Z][A-Z0-9_]*)=(.*)$/D', (string) $line, $match)) {
                continue;
            }
            if (array_key_exists($match[1], $found)) {
                throw new RuntimeException('DUPLICATE_ENV_KEY');
            }
            $value = trim($match[2]);
            if (strlen($value) >= 2 && (
                ($value[0] === '"' && str_ends_with($value, '"'))
                || ($value[0] === "'" && str_ends_with($value, "'"))
            )) {
                $value = substr($value, 1, -1);
            }
            $found[$match[1]] = $value;
        }
        return $found;
    }

    public static function truthy(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
    }

    /** @return array{missing_features: list<string>, blocked: bool} */
    public static function featureReadiness(array $env): array
    {
        $missing = [];
        foreach (self::REQUIRED_ON as $key) {
            if (!self::truthy((string) ($env[$key] ?? ''))) {
                $missing[] = $key;
            }
        }
        return ['missing_features' => $missing, 'blocked' => count($missing) > 0];
    }

    private static function ensure(bool $condition, string $code): void
    {
        if (!$condition) {
            throw new RuntimeException($code);
        }
    }

    private static function safeFile(string $path, int $maxBytes = 262144): string
    {
        self::ensure(is_file($path) && !is_link($path) && is_readable($path), 'REQUIRED_FILE_MISSING');
        $size = filesize($path);
        self::ensure(is_int($size) && $size > 0 && $size <= $maxBytes, 'REQUIRED_FILE_SIZE_INVALID');
        $contents = file_get_contents($path);
        self::ensure(is_string($contents) && strlen($contents) === $size, 'REQUIRED_FILE_READ_FAILED');
        return $contents;
    }

    private static function safeJson(string $path): array
    {
        $parsed = json_decode(self::safeFile($path), true, 24, JSON_THROW_ON_ERROR);
        self::ensure(is_array($parsed) && !array_is_list($parsed), 'REQUIRED_JSON_INVALID');
        return $parsed;
    }

    private static function ownerOnly(string $path): void
    {
        self::safeFile($path);
        $permissions = fileperms($path);
        self::ensure(is_int($permissions) && ($permissions & 0077) === 0, 'PRIVATE_PERMISSIONS_INVALID');
    }

    private static function https(string $uri): array
    {
        self::ensure(extension_loaded('curl'), 'CURL_REQUIRED');
        $request = curl_init('https://oneqaydev.n07.my.id'.$uri);
        self::ensure($request !== false, 'HTTPS_INIT_FAILED');
        curl_setopt_array($request, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Cache-Control: no-cache'],
        ]);
        $body = curl_exec($request);
        $status = (int) curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        self::ensure(is_string($body) && strlen($body) < 4096, 'HTTPS_RESPONSE_FAILED');
        return ['status' => $status, 'body' => $body];
    }

    private static function schemaProbe(array $env): array
    {
        self::ensure(extension_loaded('pdo_mysql'), 'PDO_MYSQL_REQUIRED');
        foreach (['ONEQAY_DB_HOST', 'ONEQAY_DB_DATABASE', 'ONEQAY_DB_USERNAME', 'ONEQAY_DB_PASSWORD'] as $key) {
            self::ensure(isset($env[$key]) && $env[$key] !== '', 'DATABASE_BINDING_INCOMPLETE');
        }
        $db = $env['ONEQAY_DB_DATABASE'];
        $host = $env['ONEQAY_DB_HOST'];
        $port = (string) ($env['ONEQAY_DB_PORT'] ?? '3306');
        $socket = (string) ($env['ONEQAY_DB_SOCKET'] ?? '');
        self::ensure(preg_match('/\A[a-zA-Z0-9_-]{1,64}\z/D', $db) === 1
            && preg_match('/\A[0-9]{2,5}\z/D', $port) === 1
            && $host !== '', 'DATABASE_BINDING_INVALID');
        $dsn = $socket !== ''
            ? 'mysql:unix_socket='.$socket.';dbname='.$db.';charset=utf8mb4'
            : 'mysql:host='.$host.';port='.$port.';dbname='.$db.';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, $env['ONEQAY_DB_USERNAME'], $env['ONEQAY_DB_PASSWORD'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            // Only SELECT; never migrate, insert, update, delete, lock or alter schema.
            self::ensure((int) $pdo->query('SELECT 1')->fetchColumn() === 1, 'DB_READ_PROBE_FAILED');
            $count = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_type = ?');
            $count->execute([$db, 'BASE TABLE']);
            $tableCount = (int) $count->fetchColumn();
            self::ensure($tableCount >= 43, 'SCHEMA_TABLE_COUNT_BELOW_REPORTED_BASELINE');
            $migrationCount = (int) $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn();
            self::ensure($migrationCount >= 27, 'MIGRATION_RECEIPT_COUNT_BELOW_BASELINE');
            return ['database_connection' => 'READ_ONLY_SELECT_PASS',
                'table_count' => $tableCount, 'core_migration_receipts_at_least_27' => true];
        } catch (Throwable) {
            throw new RuntimeException('READ_ONLY_DATABASE_ATTESTATION_FAILED');
        }
    }

    public static function inspect(): array
    {
        self::ensure(PHP_SAPI === 'cli' && PHP_VERSION_ID >= 80200, 'PHP_CLI_REQUIRED');
        self::ensure(realpath(self::ROOT) === self::ROOT
            && realpath(self::PUBLIC_ROOT) === self::PUBLIC_ROOT
            && realpath(self::STAGING_ROOT) !== realpath(self::PUBLIC_ROOT),
            'ISOLATED_HOST_PATH_MISMATCH');
        $release = self::ROOT.'/releases/'.self::RELEASE_ID;
        $app = $release.'/apps/web';
        self::ensure(realpath($release) === $release && realpath($app) === $app
            && !is_link($release), 'CERTIFIED_RELEASE_NOT_FOUND');
        $releaseJson = self::safeJson($release.'/RELEASE.json');
        self::ensure(($releaseJson['release_id'] ?? '') === self::RELEASE_ID
            && ($releaseJson['source_commit'] ?? '') === self::SOURCE
            && ($releaseJson['business_runtime_activation_ready'] ?? false) === true
            && ($releaseJson['source_ci_certified'] ?? false) === true
            && ($releaseJson['production_activation'] ?? '') === 'NOT_AUTHORIZED'
            && ($releaseJson['production_traffic_activation'] ?? '') === 'NOT_AUTHORIZED',
            'CERTIFIED_RELEASE_MISMATCH');
        self::ensure(is_file($app.'/artisan') && is_file($app.'/vendor/autoload.php')
            && !file_exists($app.'/bootstrap/cache/config.php'), 'LARAVEL_RUNTIME_NOT_QUALIFIED');
        $bridge = self::PUBLIC_ROOT.'/index.php';
        self::ensure(is_file($bridge) && !is_link($bridge)
            && hash_equals(self::HEALTH_BRIDGE_SHA256, (string) hash_file('sha256', $bridge)),
            'EXPECTED_HEALTH_ONLY_BRIDGE_CHANGED');
        self::ensure(is_file(self::PUBLIC_ROOT.'/index.html')
            && is_dir(self::PUBLIC_ROOT.'/build') && !is_link(self::PUBLIC_ROOT.'/build'),
            'PUBLIC_PRESENTATION_UNEXPECTED');

        $upgrade = self::safeJson(self::ROOT.'/oneqaydev-upgrade-409ac6b2bdb8/upgrade-result.json');
        self::ensure(($upgrade['state'] ?? '') === 'HEALTH_READY_BUSINESS_OFF'
            && ($upgrade['source_commit'] ?? '') === self::SOURCE
            && ($upgrade['source_integrity_files'] ?? 0) === 6252
            && ($upgrade['migrations'] ?? '') === 'NOT_TOUCHED'
            && ($upgrade['staging'] ?? '') === 'NOT_TOUCHED',
            'UPGRADE_EVIDENCE_INVALID');

        $credential = self::ROOT.'/shared/oneqaydev-first-merchant-credential.json';
        self::ownerOnly($credential);
        $merchant = self::safeJson($credential);
        self::ensure(($merchant['state'] ?? '') === 'APPLIED'
            && ($merchant['source_commit'] ?? '') === self::SOURCE
            && ($merchant['domain'] ?? '') === 'oneqaydev.n07.my.id'
            && is_array($merchant['merchant'] ?? null)
            && isset($merchant['password']) && is_string($merchant['password'])
            && strlen($merchant['password']) >= 32, 'FIRST_MERCHANT_EVIDENCE_INVALID');
        foreach (['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id', 'provisioning_id'] as $field) {
            self::ensure(isset($merchant['merchant'][$field])
                && is_string($merchant['merchant'][$field])
                && strlen($merchant['merchant'][$field]) > 8, 'MERCHANT_SCOPE_INCOMPLETE');
        }

        $envPath = self::ROOT.'/shared/production-runtime-409ac6b2bdb8.env';
        self::ownerOnly($envPath);
        $envRaw = self::safeFile($envPath);
        $activeEnv = $app.'/.env';
        self::ensure((is_file($activeEnv) || is_link($activeEnv))
            && hash_equals(hash('sha256', $envRaw), (string) hash_file('sha256', $activeEnv)),
            'APP_ENV_BINDING_MISMATCH');
        $env = self::readEnv($envRaw);
        self::ensure(($env['APP_ENV'] ?? '') === 'production'
            && in_array(strtolower((string) ($env['APP_DEBUG'] ?? '')), ['0', 'false'], true)
            && ($env['ONEQAY_RUNTIME_CLASS'] ?? '') === 'production'
            && ($env['APP_URL'] ?? '') === 'https://oneqaydev.n07.my.id'
            && self::truthy((string) ($env['ONEQAY_PERSISTENCE_ENABLED'] ?? ''))
            && ($env['ONEQAY_RUNNING_SOURCE_COMMIT'] ?? '') === self::SOURCE
            && ($env['ONEQAY_RUNNING_ARTIFACT_SHA256'] ?? '') === self::ARTIFACT_SHA256
            && preg_match('/\A[a-z0-9][a-z0-9-]{2,62}\z/D',
                (string) ($env['ONEQAY_PRODUCTION_ENVIRONMENT_ID'] ?? '')) === 1,
            'PRODUCTION_ENVIRONMENT_BINDING_INVALID');
        foreach (self::REQUIRED_OFF as $key) {
            self::ensure(!self::truthy((string) ($env[$key] ?? '')), 'BUSINESS_FLAG_PREMATURELY_ENABLED');
        }

        self::ensure(self::https('/health/live')['status'] === 200, 'HEALTH_LIVE_NOT_READY');
        $ready = self::https('/health/ready');
        $data = json_decode($ready['body'], true);
        self::ensure($ready['status'] === 200
            && is_array($data) && ($data['status'] ?? '') === 'ready'
            && ($data['service'] ?? '') === 'oneqay-web', 'HEALTH_READY_NOT_READY');
        $dark = self::https('/');
        self::ensure($dark['status'] === 503
            && str_contains($dark['body'], 'business activation not authorized'),
            'UNAUTHORIZED_PUBLIC_BUSINESS_PRESENTATION');

        // Bindings file must stay protected; do not expose its values in the report.
        self::ownerOnly(self::ROOT.'/shared/private-bindings.json');
        $database = self::schemaProbe($env);
        $features = self::featureReadiness($env);
        $session = strtolower((string) ($env['SESSION_DRIVER'] ?? ''));
        $sessionReady = in_array($session, ['database', 'file'], true)
            && self::truthy((string) ($env['SESSION_SECURE_COOKIE'] ?? ''));
        // Activation still requires a separately proven durable session policy.
        $blocked = $features['blocked'] || !$sessionReady;
        return [
            'state' => $blocked ? 'PREACTIVATION_BLOCKED' : 'PREACTIVATION_HOST_INSPECTED',
            'source' => self::SOURCE, 'release_id' => self::RELEASE_ID,
            'host' => 'oneqaydev.n07.my.id', 'upgrade' => 'HEALTH_READY_BUSINESS_OFF',
            'first_merchant' => 'APPLIED_PRIVATE_EVIDENCE',
            'https_live_ready' => 'PASS', 'business_public_surface' => 'DENIED_503',
            'business_traffic' => 'OFF', 'transaction_activation' => 'OFF',
            'database' => $database, 'missing_required_flags' => $features['missing_features'],
            'session_configuration_verified' => $sessionReady,
            'migration' => 'READ_ONLY_NOT_EXECUTED', 'staging' => 'NOT_TOUCHED',
            'authority' => 'NOT_GRANTED', 'activation' => 'NOT_EXECUTED',
            'attribution' => 'Lab | zefry',
        ];
    }

    public static function run(array $argv): int
    {
        if (count($argv) !== 2 || $argv[1] !== 'inspect') {
            fwrite(STDERR, "USAGE: php inspect-oneqaydev-business-activation.php inspect\n");
            return 64;
        }
        try {
            $report = self::inspect();
            echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            return $report['state'] === 'PREACTIVATION_HOST_INSPECTED' ? 0 : 2;
        } catch (Throwable $exception) {
            // No exception details, env contents, database names or passwords in output.
            $code = $exception instanceof RuntimeException ? $exception->getMessage() : 'INSPECTION_INTERNAL_ERROR';
            $known = [
                'DUPLICATE_ENV_KEY', 'PHP_CLI_REQUIRED', 'ISOLATED_HOST_PATH_MISMATCH',
                'CERTIFIED_RELEASE_NOT_FOUND', 'CERTIFIED_RELEASE_MISMATCH',
                'LARAVEL_RUNTIME_NOT_QUALIFIED', 'EXPECTED_HEALTH_ONLY_BRIDGE_CHANGED',
                'PUBLIC_PRESENTATION_UNEXPECTED', 'UPGRADE_EVIDENCE_INVALID',
                'PRIVATE_PERMISSIONS_INVALID', 'FIRST_MERCHANT_EVIDENCE_INVALID',
                'MERCHANT_SCOPE_INCOMPLETE', 'APP_ENV_BINDING_MISMATCH',
                'PRODUCTION_ENVIRONMENT_BINDING_INVALID', 'BUSINESS_FLAG_PREMATURELY_ENABLED',
                'HEALTH_LIVE_NOT_READY', 'HEALTH_READY_NOT_READY', 'UNAUTHORIZED_PUBLIC_BUSINESS_PRESENTATION',
                'CURL_REQUIRED', 'PDO_MYSQL_REQUIRED', 'DATABASE_BINDING_INCOMPLETE',
                'DATABASE_BINDING_INVALID', 'READ_ONLY_DATABASE_ATTESTATION_FAILED',
                'REQUIRED_FILE_MISSING', 'REQUIRED_FILE_SIZE_INVALID', 'REQUIRED_FILE_READ_FAILED',
                'REQUIRED_JSON_INVALID', 'HTTPS_INIT_FAILED', 'HTTPS_RESPONSE_FAILED',
            ];
            if (!in_array($code, $known, true)) {
                $code = 'INSPECTION_FAILED';
            }
            echo json_encode([
                'state' => 'STOPPED_FAIL_CLOSED', 'error_code' => $code,
                'authority' => 'NOT_GRANTED', 'activation' => 'NOT_EXECUTED',
                'business_traffic' => 'OFF_NOT_MODIFIED',
                'migrations' => 'NOT_TOUCHED', 'staging' => 'NOT_TOUCHED',
                'attribution' => 'Lab | zefry',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
            return 1;
        }
    }
}

if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(OneQayDevBusinessPreflight::run($argv));
}
