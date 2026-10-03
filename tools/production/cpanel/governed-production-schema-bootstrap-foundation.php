<?php

declare(strict_types=1);

// Author by Lab | zefry
// Sprint267 is planning-only outside disposable, isolated CI fixtures.

final class Sprint267SchemaBootstrapException extends RuntimeException {}

function s267Fail(string $code): never
{
    throw new Sprint267SchemaBootstrapException($code);
}

/** @return array<string,mixed> */
function s267JsonFile(string $path): array
{
    if ($path === '' || !is_file($path) || is_link($path) || !is_readable($path)) s267Fail('input_unavailable');
    $length = filesize($path);
    if (!is_int($length) || $length < 2 || $length > 524288) s267Fail('input_size_invalid');
    try {
        $value = json_decode((string)file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        s267Fail('input_json_invalid');
    }
    if (!is_array($value) || array_is_list($value)) s267Fail('input_shape_invalid');
    return $value;
}

function s267Require(bool $condition, string $code): void
{
    if (!$condition) s267Fail($code);
}

/** @return list<array{filename:string,git_blob_sha:string}> */
function s267VerifyLock(array $lock, string $migrationsDirectory): array
{
    s267Require(($lock['schema_version'] ?? null) === 1, 'lock_schema_invalid');
    s267Require(($lock['product'] ?? null) === 'oneQay', 'lock_product_invalid');
    s267Require(($lock['state'] ?? null) === 'EXACT_SOURCE_LOCK_ISOLATED_TEST_ONLY', 'lock_state_invalid');
    s267Require(($lock['canonical_predecessor'] ?? null) === 'c83d5131d7aaab19ddbd001223236aa424d2ea48', 'lock_predecessor_invalid');
    s267Require(($lock['application_source_commit'] ?? null) === '505518f79e8a70f789b94f5074a040eae785aeb0', 'lock_source_invalid');
    s267Require(($lock['production_release_id'] ?? null) === 'production-505518f79e8a', 'lock_release_invalid');
    s267Require(($lock['migration_dir'] ?? null) === 'apps/web/database/migrations', 'lock_directory_invalid');
    s267Require(($lock['migration_count'] ?? null) === 27, 'lock_count_invalid');
    s267Require(($lock['attribution'] ?? null) === 'Lab | zefry', 'lock_attribution_invalid');
    $boundary = $lock['authority'] ?? null;
    s267Require(is_array($boundary) && ($boundary['ci_disposable_database_only'] ?? null) === true, 'lock_ci_boundary_invalid');
    foreach ([
        'production_database_execution_authorized','production_deployment_authorized',
        'production_traffic_authorized','staging_mutation_authorized','target_reselection_authorized',
        'feature_reactivation_authorized','permission_provisioning_authorized',
        'producer_dispatch_authorized','updater_activation_authorized',
    ] as $flag) {
        s267Require(($boundary[$flag] ?? null) === false, 'lock_authority_forbidden');
    }
    s267Require(is_dir($migrationsDirectory) && !is_link($migrationsDirectory), 'migration_directory_invalid');
    $expected = $lock['migrations'] ?? null;
    s267Require(is_array($expected) && array_is_list($expected) && count($expected) === 27, 'lock_set_invalid');
    $names = [];
    foreach ($expected as $index => $entry) {
        s267Require(is_array($entry), 'lock_entry_invalid');
        $filename = $entry['filename'] ?? null;
        $blob = $entry['git_blob_sha'] ?? null;
        $ordinal = str_pad((string)($index + 1), 6, '0', STR_PAD_LEFT);
        s267Require(is_string($filename) && preg_match('/\A0000_00_00_'.$ordinal.'_[a-z0-9_]+\.php\z/D', $filename) === 1, 'lock_order_invalid');
        s267Require(is_string($blob) && preg_match('/\A[0-9a-f]{40}\z/D', $blob) === 1, 'lock_blob_invalid');
        $file = rtrim($migrationsDirectory, '/').'/'.$filename;
        s267Require(is_file($file) && !is_link($file) && is_readable($file), 'migration_file_missing_or_link');
        $raw = file_get_contents($file);
        s267Require(is_string($raw) && strlen($raw) > 100 && strlen($raw) <= 262144, 'migration_bytes_invalid');
        s267Require(hash_equals($blob, sha1('blob '.strlen($raw)."\0".$raw)), 'migration_blob_mismatch');
        $names[] = $filename;
    }
    $actual = [];
    $items = scandir($migrationsDirectory);
    s267Require(is_array($items), 'migration_scan_failed');
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        s267Require(str_ends_with($item, '.php') && !is_link($migrationsDirectory.'/'.$item) && is_file($migrationsDirectory.'/'.$item), 'unexpected_migration_directory_entry');
        $actual[] = $item;
    }
    sort($actual, SORT_STRING);
    s267Require($actual === $names, 'migration_set_mismatch');
    return $expected;
}

/** @return array<string,mixed> */
function s267PreparePlan(array $lock, array $inventory, string $migrationsDirectory): array
{
    $migrations = s267VerifyLock($lock, $migrationsDirectory);
    s267Require(($inventory['schema_version'] ?? null) === 1, 'inventory_schema_invalid');
    s267Require(($inventory['product'] ?? null) === 'oneQay', 'inventory_product_invalid');
    s267Require(($inventory['state'] ?? null) === 'READ_ONLY_INVENTORY_COMPLETE', 'inventory_state_invalid');
    s267Require(($inventory['code'] ?? null) === 'MYSQL_SCHEMA_REGISTRY_OBSERVED_READ_ONLY', 'inventory_code_invalid');
    s267Require(($inventory['release_source'] ?? null) === $lock['application_source_commit'], 'inventory_source_invalid');
    $environment = $inventory['environment_id'] ?? null;
    s267Require(is_string($environment) && preg_match('/\A[a-z0-9][a-z0-9-]{2,62}\z/D', $environment) === 1, 'inventory_environment_invalid');
    foreach ([
        'database_inventory_only' => true, 'database_mutation_performed' => false,
        'staging_access_performed' => false, 'public_document_root_modified' => false,
        'production_schema_provisioned_by_this_check' => false, 'production_target_qualified' => false,
        'production_deployment_authorized' => false, 'production_traffic_authorized' => false,
    ] as $field => $value) {
        s267Require(($inventory[$field] ?? null) === $value, 'inventory_boundary_invalid');
    }
    $details = $inventory['details'] ?? null;
    s267Require(is_array($details), 'inventory_details_invalid');
    s267Require(($details['schema_observation'] ?? null) === 'FRESH_EMPTY_PRODUCTION_DATABASE', 'nonempty_database_refused');
    s267Require(($details['expected_source_migration_count'] ?? null) === 27, 'inventory_expected_invalid');
    s267Require(($details['observed_base_table_count'] ?? null) === 0, 'database_tables_exist');
    s267Require(($details['migration_registry_present'] ?? null) === false, 'migration_registry_exists');
    s267Require(($details['observed_unique_migration_count'] ?? null) === 0, 'migration_already_executed');
    s267Require(($details['unexpected_migration_count'] ?? null) === 0, 'unexpected_migrations_observed');
    s267Require(($details['production_schema_provisioning_authorized'] ?? null) === false, 'unexpected_authority');
    $expectedNames = array_map(static fn(array $row): string => substr($row['filename'], 0, -4), $migrations);
    s267Require(($details['missing_expected_migrations'] ?? null) === $expectedNames, 'missing_migration_set_invalid');

    $plan = [
        'schema_version' => 1,
        'product' => 'oneQay',
        'state' => 'PRODUCTION_SCHEMA_BOOTSTRAP_PLAN_PREPARED_NOT_AUTHORIZED',
        'environment_id' => $environment,
        'application_source_commit' => $lock['application_source_commit'],
        'release_id' => $lock['production_release_id'],
        'migration_count' => 27,
        'ordered_migrations' => $migrations,
        'source_lock_sha256' => hash('sha256', json_encode($lock, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)),
        'inventory_canonical_sha256' => hash('sha256', s267CanonicalJson($inventory)),
        'inventory_is_historical_observation' => true,
        'fresh_direct_db_preflight_required_before_any_future_execution' => true,
        'approved_execution_target' => 'DISPOSABLE_CI_ONLY',
        'production_database_execution_authorized' => false,
        'production_deployment_authorized' => false,
        'production_traffic_authorized' => false,
        'staging_mutation_authorized' => false,
        'migration27_staging_replay_authorized' => false,
        'schema_provisioning_performed' => false,
        'attribution' => 'Lab | zefry',
    ];
    $plan['plan_fingerprint_sha256'] = hash('sha256', s267CanonicalJson($plan));
    return $plan;
}

function s267Canonicalize(array $data): array
{
    if (array_is_list($data)) {
        return array_map(static fn(mixed $v): mixed => is_array($v) ? s267Canonicalize($v) : $v, $data);
    }
    ksort($data, SORT_STRING);
    foreach ($data as $key => $value) if (is_array($value)) $data[$key] = s267Canonicalize($value);
    return $data;
}

function s267CanonicalJson(array $data): string
{
    return json_encode(s267Canonicalize($data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

function s267WritePrivatePlan(string $path, array $plan): void
{
    $dir = dirname($path);
    s267Require(str_starts_with($path, '/') && !str_contains($path, "\0")
        && !preg_match('#(?:^|/)\.{1,2}(?:/|$)#', $path)
        && !str_contains($path, 'public_html') && !is_link($dir)
        && is_dir($dir) && is_writable($dir) && !is_link($path) && !is_dir($path),
        'private_output_invalid');
    $temp = tempnam($dir, '.oneqay-s267-plan-');
    s267Require($temp !== false, 'temp_create_failed');
    try {
        chmod($temp, 0600);
        $json = json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
        s267Require(file_put_contents($temp, $json, LOCK_EX) === strlen($json), 'private_output_write_failed');
        s267Require(rename($temp, $path), 'private_output_commit_failed');
        chmod($path, 0600);
    } finally {
        if (is_file($temp)) unlink($temp);
    }
}
