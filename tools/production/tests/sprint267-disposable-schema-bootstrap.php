<?php

declare(strict_types=1);

// Author by Lab | zefry
// This executable has NO Production mode, NO cPanel mode and NO caller-supplied DSN.
require_once dirname(__DIR__).'/cpanel/governed-production-schema-bootstrap-foundation.php';

function s267CiEnvironment(): string
{
    s267Require(PHP_SAPI === 'cli' && getenv('GITHUB_ACTIONS') === 'true' && getenv('CI') === 'true'
        && getenv('ONEQAY_SPRINT267_DISPOSABLE') === 'true', 'isolated_ci_attestation_required');
    $driver = getenv('ONEQAY_SPRINT267_CI_DRIVER');
    s267Require(in_array($driver, ['sqlite', 'mysql'], true), 'isolated_ci_driver_invalid');
    $base = getenv('RUNNER_TEMP');
    s267Require(is_string($base) && is_dir($base) && is_writable($base) && realpath($base) !== false
        && !str_contains($base, 'public_html'), 'ci_temporary_root_invalid');
    return $driver;
}

function s267SetEnv(string $name, string $value): void
{
    putenv($name.'='.$value);
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}

function s267CiCleanup(string $root): void
{
    if (!is_dir($root)) return;
    foreach (scandir($root) ?: [] as $item) {
        if (in_array($item, ['.','..'], true)) continue;
        $file = $root.'/'.$item;
        if (is_link($file) || is_file($file)) {
            unlink($file);
        } elseif (is_dir($file)) {
            s267CiCleanup($file);
        }
    }
    rmdir($root);
}

function s267CiMain(): void
{
    $driver = s267CiEnvironment();
    $repo = dirname(__DIR__, 3);
    $appRoot = $repo.'/apps/web';
    $migrationDir = $appRoot.'/database/migrations';
    $lock = s267JsonFile($repo.'/ops/final-shift-close/PRODUCTION_SCHEMA_BOOTSTRAP_SOURCE_LOCK.json');
    $expected = s267VerifyLock($lock, $migrationDir);
    s267Require(is_file($appRoot.'/vendor/autoload.php'), 'ci_composer_dependencies_unavailable');
    s267Require(extension_loaded('pdo_'.$driver), 'ci_pdo_driver_unavailable');

    $root = rtrim((string) realpath((string)getenv('RUNNER_TEMP')), '/').'/oneqay-s267-'.bin2hex(random_bytes(8));
    s267Require(mkdir($root, 0700), 'ci_fixture_creation_failed');
    try {
        // Negative tests MUST run before any database connection or migration.
        $copied = $root.'/migrations';
        s267Require(mkdir($copied, 0700), 'ci_copy_directory_failed');
        foreach ($expected as $entry) {
            $name = $entry['filename'];
            s267Require(copy($migrationDir.'/'.$name, $copied.'/'.$name), 'ci_copy_failed');
        }
        s267VerifyLock($lock, $copied);
        $first = $copied.'/'.$expected[0]['filename'];
        file_put_contents($first, "\n// tampered", FILE_APPEND | LOCK_EX);
        try {
            s267VerifyLock($lock, $copied);
            s267Fail('tamper_was_not_rejected');
        } catch (Sprint267SchemaBootstrapException $ex) {
            if ($ex->getMessage() !== 'migration_blob_mismatch') throw $ex;
        }
        s267Require(copy($migrationDir.'/'.$expected[0]['filename'], $first), 'ci_restore_failed');
        file_put_contents($copied.'/unexpected.php', '<?php');
        try {
            s267VerifyLock($lock, $copied);
            s267Fail('unexpected_file_was_not_rejected');
        } catch (Sprint267SchemaBootstrapException $ex) {
            if ($ex->getMessage() !== 'unexpected_migration_directory_entry') throw $ex;
        }
        unlink($copied.'/unexpected.php');
        s267VerifyLock($lock, $copied);
        echo "SPRINT267_SOURCE_LOCK_TAMPER_REJECTION=PASS\n";

        // Only a disposable CI database is reachable from this controlled fixture.
        if ($driver === 'sqlite') {
            $db = $root.'/disposable.sqlite';
            s267Require(file_put_contents($db, '') === 0, 'ci_sqlite_creation_failed');
            $dbConfig = ['ONEQAY_DB_DRIVER'=>'sqlite', 'ONEQAY_DB_DATABASE'=>$db,
                'ONEQAY_DB_HOST'=>'127.0.0.1','ONEQAY_DB_PORT'=>'3306',
                'ONEQAY_DB_USERNAME'=>'','ONEQAY_DB_PASSWORD'=>''];
        } else {
            $dbConfig = ['ONEQAY_DB_DRIVER'=>'mysql','ONEQAY_DB_HOST'=>'127.0.0.1',
                'ONEQAY_DB_PORT'=>'3306','ONEQAY_DB_DATABASE'=>'oneqay_s267_ci',
                'ONEQAY_DB_USERNAME'=>'oneqay_s267_ci',
                'ONEQAY_DB_PASSWORD'=>(string)getenv('ONEQAY_SPRINT267_CI_MYSQL_PASSWORD')];
            s267Require($dbConfig['ONEQAY_DB_PASSWORD'] === 'oneqay-s267-disposable-ci-only', 'ci_mysql_credentials_not_disposable');
        }
        $environment = [
            'APP_NAME'=>'oneQay', 'APP_ENV'=>'testing', 'APP_DEBUG'=>'false',
            'APP_KEY'=>'base64:'.base64_encode(str_repeat('7', 32)),
            'APP_URL'=>'http://localhost', 'ONEQAY_RUNTIME_CLASS'=>'ci',
            'ONEQAY_PERSISTENCE_ENABLED'=>'true', 'ONEQAY_PRODUCTION_DATA_ALLOWED'=>'false',
            'ONEQAY_TECHNICAL_PREVIEW_ENABLED'=>'false', 'ONEQAY_POS_SHIFT_CLOSE_ENABLED'=>'false',
            'SESSION_DRIVER'=>'array', 'CACHE_STORE'=>'array', 'LOG_CHANNEL'=>'stderr',
        ] + $dbConfig;
        foreach ($environment as $key=>$value) s267SetEnv($key, $value);
        s267Require(getenv('APP_ENV') === 'testing' && getenv('ONEQAY_RUNTIME_CLASS') === 'ci'
            && getenv('ONEQAY_PRODUCTION_DATA_ALLOWED') === 'false', 'ci_runtime_guard_failed');
        require $appRoot.'/vendor/autoload.php';
        $app = require $appRoot.'/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        $connection = $app->make('db')->connection('oneqay');
        s267Require($connection->getDriverName() === $driver, 'ci_database_driver_mismatch');
        if ($driver === 'mysql') {
            $identity = $connection->selectOne('SELECT DATABASE() AS database_name');
            s267Require(($identity->database_name ?? null) === 'oneqay_s267_ci', 'ci_database_identity_mismatch');
        }
        $beforeTables = $connection->getSchemaBuilder()->getTableListing();
        s267Require($beforeTables === [], 'ci_database_not_empty');
        $status = $kernel->call('migrate', [
            '--database'=>'oneqay', '--path'=>$migrationDir, '--realpath'=>true,
            '--force'=>true, '--no-interaction'=>true,
        ]);
        s267Require($status === 0, 'ci_migration_command_failed');
        $rows = $connection->table('migrations')->pluck('migration')->all();
        $expectedNames = array_map(static fn(array $entry): string => substr($entry['filename'], 0, -4), $expected);
        sort($rows, SORT_STRING);
        s267Require($rows === $expectedNames, 'ci_migration_ledger_mismatch');
        $tables = $connection->getSchemaBuilder()->getTableListing();
        s267Require(in_array('oneqay_pos_shift_close_evidence', $tables, true), 'ci_final_shift_close_table_missing');
        foreach ($tables as $table) {
            if (!str_starts_with($table, 'oneqay_')) continue;
            $count = (int)$connection->table($table)->count();
            s267Require($count === 0, 'ci_unexpected_business_rows');
        }
        if ($driver === 'sqlite') {
            $integrity = $connection->selectOne('PRAGMA integrity_check');
            s267Require(($integrity->integrity_check ?? null) === 'ok', 'ci_sqlite_integrity_failed');
        }
        $status = $kernel->call('migrate', [
            '--database'=>'oneqay', '--path'=>$migrationDir, '--realpath'=>true,
            '--force'=>true, '--no-interaction'=>true,
        ]);
        s267Require($status === 0 && $connection->table('migrations')->count() === 27, 'ci_idempotency_failed');
        echo "SPRINT267_".strtoupper($driver)."_DISPOSABLE_27_MIGRATIONS=PASS\n";
        echo "SPRINT267_LEDGER_AND_REPLAY_GUARDS=PASS\n";
        echo "PRODUCTION_DATABASE_EXECUTION=NOT_PERFORMED\n";
        echo "STAGING_MUTATION=NOT_PERFORMED\n";
    } finally {
        s267CiCleanup($root);
    }
}

try {
    s267CiMain();
} catch (Throwable $e) {
    $reason = $e instanceof Sprint267SchemaBootstrapException ? $e->getMessage() : 'isolated_ci_test_failed';
    fwrite(STDERR, "SPRINT267_DISPOSABLE_TEST=FAILED:".$reason."\n");
    exit(1);
}
