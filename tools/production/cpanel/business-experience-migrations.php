<?php
// Author by Lab | zefry. Additive upgrade only; never restore/drop production data.
declare(strict_types=1);

function beMigrationFiles(): array
{
    return [
        'platform/2026_10_23_000001_create_business_experience.php',
        'business/2026_10_23_000002_create_isolated_service_pos.php',
    ];
}

function beNewTables(): array
{
    $tables = ['oneqay_business_profiles', 'oneqay_business_domains', 'oneqay_business_handoffs', 'oneqay_platform_branding'];
    foreach (['laundry', 'boarding', 'homestay'] as $module) {
        foreach (['resources', 'orders', 'invoices', 'shifts', 'payments'] as $part) {
            $tables[] = 'oneqay_'.$module.'_'.$part;
        }
    }
    sort($tables);
    return $tables;
}

function beVerifySchema(array $before, array $after): void
{
    nNeed(count($before['ddl']) === 89 && count($before['receipts']) === 39, 'EXACT_PREDECESSOR_SCHEMA_REQUIRED');
    nNeed(count($after['ddl']) === 108 && count($after['receipts']) === 41, 'ADDITIVE_SCHEMA_INCOMPLETE');
    foreach ($before['ddl'] as $name => $hash) {
        nNeed(($after['ddl'][$name] ?? null) === $hash, 'HISTORICAL_SCHEMA_CHANGED');
    }
    nNeed(array_slice($after['receipts'], 0, 39) === $before['receipts'], 'HISTORICAL_MIGRATION_RECEIPTS_CHANGED');
    $new = array_keys(array_diff_key($after['ddl'], $before['ddl']));
    sort($new);
    nNeed($new === beNewTables(), 'UNEXPECTED_ADDITIVE_TABLES');
    $names = array_column(array_slice($after['receipts'], 39), 'migration');
    $expected = array_map(static fn ($file) => basename($file, '.php'), beMigrationFiles());
    nNeed($names === $expected, 'UNEXPECTED_ADDITIVE_MIGRATIONS');
}

/** Only the pinned two files may be added; all existing migration bytes stay identical. */
function beVerifyMigrationSources(string $oldApp, string $candidateApp): void
{
    foreach (['database/migrations/*.php', 'database/module-migrations/*/*.php'] as $pattern) {
        $old = glob($oldApp.'/'.$pattern);
        $new = glob($candidateApp.'/'.$pattern);
        $expected = [];
        foreach ($old as $file) {
            $relative = substr($file, strlen($oldApp) + 1);
            nHash($candidateApp.'/'.$relative, hash_file('sha256', $file));
            $expected[] = $candidateApp.'/'.$relative;
        }
        if (str_contains($pattern, 'module-migrations')) {
            foreach (beMigrationFiles() as $file) {
                $expected[] = $candidateApp.'/database/module-migrations/'.$file;
            }
        }
        sort($expected);
        sort($new);
        nNeed($new === $expected, 'SCHEMA_SOURCE_SET_CHANGED');
    }
}

/** A partial MySQL DDL attempt is quarantined for forward repair, never retried blindly. */
function beApplyMigrations($app, $db, string $candidateApp, array $before): array
{
    $current = nSchema($db);
    if ($current !== $before) {
        beVerifySchema($before, $current);
        return $current;
    }
    nNeed(count($before['ddl']) === 89 && count($before['receipts']) === 39, 'EXACT_PREDECESSOR_SCHEMA_REQUIRED');
    foreach (beNewTables() as $table) {
        nNeed(!isset($before['ddl'][$table]), 'ADDITIVE_TABLE_ALREADY_EXISTS');
    }
    $migrator = $app->make('migrator');
    $migrator->setConnection($db->getName());
    foreach (beMigrationFiles() as $file) {
        $path = $candidateApp.'/database/module-migrations/'.$file;
        nNeed(is_file($path) && !is_link($path), 'ADDITIVE_MIGRATION_SOURCE_MISSING');
        $migrator->run([$path], ['step' => true]);
    }
    $after = nSchema($db);
    beVerifySchema($before, $after);
    return $after;
}
