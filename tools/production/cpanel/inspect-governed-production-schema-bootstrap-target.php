<?php

declare(strict_types=1);

// Author by Lab | zefry
// Read-only inventory and plan. NEVER executes migrations or grants Production authority.
require_once __DIR__.'/governed-production-schema-bootstrap-foundation.php';

final class OneQayProductionSchemaInventoryError extends RuntimeException {}

function psInventoryRequire(bool $ok, string $code): void
{
    if (!$ok) throw new OneQayProductionSchemaInventoryError($code);
}

function psInventoryPrivatePath(string $path, string $root, bool $existing): void
{
    psInventoryRequire(str_starts_with($path, '/') && !str_contains($path, "\0")
        && preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $path) !== 1
        && !str_contains($path, '//'), 'private_path_invalid');
    $parent = realpath(dirname($path));
    psInventoryRequire(is_string($parent) && ($parent === $root || str_starts_with($parent, $root.'/'))
        && !is_link(dirname($path)) && !is_link($path), 'private_path_escape');
    if ($existing) {
        psInventoryRequire(is_file($path) && is_readable($path) && realpath($path) === $path,
            'private_input_unavailable');
    } else {
        psInventoryRequire(!file_exists($path) && is_writable($parent), 'private_output_unavailable');
    }
}

/** @param array<string,mixed> $target */
function psInventoryValidateTarget(array $target): array
{
    $expected = [
        'schema_version','product','environment_id','runtime_class',
        'expected_database_name','private_operator_root','public_document_root',
        'migration_source_commit','schema_provisioning_authorized',
        'production_deployment_authorized','production_traffic_authorized','attribution',
    ];
    $actual = array_keys($target);
    sort($actual);
    sort($expected);
    psInventoryRequire($actual === $expected, 'target_field_envelope_invalid');
    psInventoryRequire(($target['schema_version'] ?? null) === 1
        && ($target['product'] ?? null) === 'oneQay'
        && ($target['environment_id'] ?? null) === 'oneqay-production-01'
        && ($target['runtime_class'] ?? null) === 'production'
        && ($target['migration_source_commit'] ?? null) === '505518f79e8a70f789b94f5074a040eae785aeb0'
        && ($target['schema_provisioning_authorized'] ?? null) === false
        && ($target['production_deployment_authorized'] ?? null) === false
        && ($target['production_traffic_authorized'] ?? null) === false
        && ($target['attribution'] ?? null) === 'Lab | zefry', 'target_contract_invalid');
    psInventoryRequire(is_string($target['expected_database_name'])
        && preg_match('/\A[a-zA-Z0-9_]{3,64}\z/D', $target['expected_database_name']) === 1,
        'target_database_name_invalid');
    $root = $target['private_operator_root'];
    $public = $target['public_document_root'];
    psInventoryRequire(is_string($root) && is_string($public)
        && str_starts_with($root, '/') && str_starts_with($public, '/')
        && !is_link($root) && is_dir($root) && is_writable($root)
        && realpath($root) === $root && realpath($public) === $public
        && !str_contains($root, 'public_html') && !str_contains($root, '/www/')
        && $root !== $public
        && !str_starts_with($root.'/', $public.'/')
        && !str_starts_with($public.'/', $root.'/'), 'private_public_isolation_invalid');
    return $target;
}

/** @return array<string,mixed> */
function psInventoryPrivateCredentials(string $path, string $root): array
{
    psInventoryPrivatePath($path, $root, true);
    $mode = fileperms($path);
    psInventoryRequire(is_int($mode) && ($mode & 0077) === 0, 'credentials_permissions_invalid');
    $data = s267JsonFile($path);
    $expected = ['host','port','database','username','password'];
    $actual = array_keys($data);
    sort($expected);
    sort($actual);
    psInventoryRequire($actual === $expected, 'credentials_shape_invalid');
    foreach (['host','database','username','password'] as $name) {
        psInventoryRequire(is_string($data[$name]) && $data[$name] !== ''
            && strlen($data[$name]) < 512, 'credentials_value_invalid');
    }
    psInventoryRequire(is_int($data['port']) && $data['port'] >= 1 && $data['port'] <= 65535,
        'credentials_port_invalid');
    psInventoryRequire(!str_contains($data['host'], ';') && !str_contains($data['host'], "\0"),
        'credentials_host_invalid');
    return $data;
}

/** @return array<string,mixed> */
function psInventoryObserve(PDO $db, string $expectedDatabase): array
{
    psInventoryRequire($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql',
        'mysql_driver_required');
    $started = false;
    try {
        $db->exec('START TRANSACTION READ ONLY');
        $started = true;
        $identity = $db->query(
            'SELECT DATABASE() AS database_name, @@hostname AS server_hostname, @@port AS server_port'
        )->fetch(PDO::FETCH_ASSOC);
        psInventoryRequire(is_array($identity) && $identity['database_name'] === $expectedDatabase
            && is_string($identity['server_hostname']) && $identity['server_hostname'] !== ''
            && is_numeric($identity['server_port']), 'direct_database_identity_mismatch');
        $counts = [];
        foreach ([
            'objects' => 'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()',
            'routines' => 'SELECT COUNT(*) FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE()',
            'triggers' => 'SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE()',
            'events' => 'SELECT COUNT(*) FROM information_schema.EVENTS WHERE EVENT_SCHEMA = DATABASE()',
        ] as $key => $sql) {
            $counts[$key] = (int) $db->query($sql)->fetchColumn();
            psInventoryRequire($counts[$key] === 0, 'nonempty_production_schema_refused');
        }
        $binding = [
            'database_name' => $identity['database_name'],
            'server_hostname' => $identity['server_hostname'],
            'server_port' => (int)$identity['server_port'],
        ];
        return [
            'database_binding_sha256' => hash('sha256', s267CanonicalJson($binding)),
            'observed_object_counts' => $counts,
        ];
    } finally {
        if ($started) $db->exec('ROLLBACK');
    }
}

function psInventoryRun(
    string $migrationDir, string $targetPath, string $credentialsPath,
    string $inventoryOutput, string $planOutput
): void {
    psInventoryRequire(PHP_SAPI === 'cli' && extension_loaded('pdo_mysql'), 'php_cli_pdo_mysql_required');
    $lock = s267JsonFile(dirname(__DIR__, 3).'/ops/final-shift-close/PRODUCTION_SCHEMA_BOOTSTRAP_SOURCE_LOCK.json');
    $migrations = s267VerifyLock($lock, $migrationDir);
    $target = psInventoryValidateTarget(s267JsonFile($targetPath));
    $root = $target['private_operator_root'];
    psInventoryPrivatePath($targetPath, $root, true);
    psInventoryPrivatePath($inventoryOutput, $root, false);
    psInventoryPrivatePath($planOutput, $root, false);
    psInventoryRequire($inventoryOutput !== $planOutput, 'output_collision');
    $credentials = psInventoryPrivateCredentials($credentialsPath, $root);
    psInventoryRequire($credentials['database'] === $target['expected_database_name'],
        'configured_database_name_mismatch');

    $dsn = 'mysql:host='.$credentials['host'].';port='.$credentials['port']
        .';dbname='.$credentials['database'].';charset=utf8mb4';
    $db = new PDO($dsn, $credentials['username'], $credentials['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 8,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $observed = psInventoryObserve($db, $target['expected_database_name']);
    $db = null;
    $inventory = [
        'schema_version'=>1, 'product'=>'oneQay',
        'environment_id'=>$target['environment_id'],
        'release_source'=>$lock['application_source_commit'],
        'state'=>'READ_ONLY_INVENTORY_COMPLETE',
        'code'=>'MYSQL_SCHEMA_REGISTRY_OBSERVED_READ_ONLY',
        'database_inventory_only'=>true,
        'database_mutation_performed'=>false,
        'staging_access_performed'=>false,
        'public_document_root_modified'=>false,
        'production_schema_provisioned_by_this_check'=>false,
        'production_target_qualified'=>false,
        'production_deployment_authorized'=>false,
        'production_traffic_authorized'=>false,
        'observed_at_unix'=>time(),
        'database_binding_sha256'=>$observed['database_binding_sha256'],
        'observed_object_counts'=>$observed['observed_object_counts'],
        'inventory_is_historical_observation'=>true,
        'details'=>[
            'schema_observation'=>'FRESH_EMPTY_PRODUCTION_DATABASE',
            'expected_source_migration_count'=>27,
            'observed_base_table_count'=>0,
            'migration_registry_present'=>false,
            'observed_unique_migration_count'=>0,
            'missing_expected_migrations'=>array_map(
                static fn(array $m): string => substr($m['filename'], 0, -4), $migrations
            ),
            'unexpected_migration_count'=>0,
            'production_schema_provisioning_authorized'=>false,
        ],
    ];
    $plan = s267PreparePlan($lock, $inventory, $migrationDir);
    s267WritePrivatePlan($inventoryOutput, $inventory);
    s267WritePrivatePlan($planOutput, $plan);
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 6) {
        fwrite(STDERR, "Usage: php inspect-governed-production-schema-bootstrap-target.php <migration-dir> <private-target.json> <private-db-credentials.json> <private-inventory.json> <private-plan.json>\n");
        exit(64);
    }
    try {
        psInventoryRun($argv[1], $argv[2], $argv[3], $argv[4], $argv[5]);
        echo "PRODUCTION_SCHEMA_READ_ONLY_PREFLIGHT=PASS\n";
        echo "PRODUCTION_SCHEMA_BOOTSTRAP_PLAN=PREPARED_NOT_AUTHORIZED\n";
        echo "PRODUCTION_DATABASE_MUTATION=NOT_PERFORMED\n";
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, "PRODUCTION_SCHEMA_READ_ONLY_PREFLIGHT=FAILED\n");
        exit(1);
    }
}
