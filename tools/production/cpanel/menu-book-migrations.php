<?php
// Author by Lab | zefry. Additive upgrade only; never restore/drop production data.
declare(strict_types=1);

function mbMigrationFiles(): array
{
    return [
        'business/2026_10_29_000001_create_menu_book_catalog.php',
        'business/2026_10_29_000002_add_menu_book_cover_media.php',
        'business/2026_10_30_000003_create_menu_guest_orders.php',
    ];
}

function mbNewTables(): array
{
    $tables = ['oneqay_menu_book_categories','oneqay_menu_book_items','oneqay_menu_book_media','oneqay_menu_book_publication','oneqay_menu_guest_orders','oneqay_menu_guest_order_lines'];
    sort($tables);
    return $tables;
}

function mbVerifySchema(array $before, array $after): void
{
    nNeed(count($before['ddl']) === 108 && count($before['receipts']) === 41, 'EXACT_PREDECESSOR_SCHEMA_REQUIRED');
    nNeed(count($after['ddl']) === 114 && count($after['receipts']) === 44, 'ADDITIVE_SCHEMA_INCOMPLETE');
    foreach ($before['ddl'] as $name => $hash) {
        nNeed(($after['ddl'][$name] ?? null) === $hash, 'HISTORICAL_SCHEMA_CHANGED');
    }
    nNeed(array_slice($after['receipts'], 0, 41) === $before['receipts'], 'HISTORICAL_MIGRATION_RECEIPTS_CHANGED');
    $new = array_keys(array_diff_key($after['ddl'], $before['ddl']));
    sort($new);
    nNeed($new === mbNewTables(), 'UNEXPECTED_ADDITIVE_TABLES');
    $names = array_column(array_slice($after['receipts'], 41), 'migration');
    $expected = array_map(static fn ($file) => basename($file, '.php'), mbMigrationFiles());
    nNeed($names === $expected, 'UNEXPECTED_ADDITIVE_MIGRATIONS');
}

/** Only the pinned three files may be added; all existing migration bytes stay identical. */
function mbVerifyMigrationSources(string $oldApp, string $candidateApp): void
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
            foreach (mbMigrationFiles() as $file) {
                $expected[] = $candidateApp.'/database/module-migrations/'.$file;
            }
        }
        sort($expected);
        sort($new);
        nNeed($new === $expected, 'SCHEMA_SOURCE_SET_CHANGED');
    }
}

/** A partial MySQL DDL attempt is quarantined for forward repair, never retried blindly. */
function mbApplyMigrations($app, $db, string $candidateApp, array $before): array
{
    $current = nSchema($db);
    if ($current !== $before) {
        mbVerifySchema($before, $current);
        return $current;
    }
    nNeed(count($before['ddl']) === 108 && count($before['receipts']) === 41, 'EXACT_PREDECESSOR_SCHEMA_REQUIRED');
    foreach (mbNewTables() as $table) {
        nNeed(!isset($before['ddl'][$table]), 'ADDITIVE_TABLE_ALREADY_EXISTS');
    }
    $migrator = $app->make('migrator');
    $migrator->setConnection($db->getName());
    foreach (mbMigrationFiles() as $file) {
        $path = $candidateApp.'/database/module-migrations/'.$file;
        nNeed(is_file($path) && !is_link($path), 'ADDITIVE_MIGRATION_SOURCE_MISSING');
        $migrator->run([$path], ['step' => true]);
    }
    $after = nSchema($db);
    mbVerifySchema($before, $after);
    return $after;
}
