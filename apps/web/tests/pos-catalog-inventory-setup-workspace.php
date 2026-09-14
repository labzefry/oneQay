<?php

declare(strict_types=1);

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Pos\LaravelPosCatalogInventorySetupWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('u', 32));
foreach ([
    'APP_ENV' => 'testing',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $console */
$console = $app->make(Kernel::class);
$console->bootstrap();

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$databasePath = sys_get_temp_dir().'/oneqay-s161-catalog-inventory-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint161 disposable database could not be created.');
$app['config']->set('database.default', 's161_catalog_inventory');
$app['config']->set('database.connections.s161_catalog_inventory', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => false,
]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s161_catalog_inventory');
$connection = $databaseManager->connection('s161_catalog_inventory');

$connection->statement('CREATE TABLE oneqay_pos_sale_catalog_items (tenant_id TEXT NOT NULL, outlet_id TEXT NOT NULL, product_id TEXT NOT NULL, display_name TEXT NOT NULL, unit_price_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, available_quantity INTEGER NOT NULL, active INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_inventory_baselines (tenant_id TEXT NOT NULL, outlet_id TEXT NOT NULL, product_id TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, outlet_id TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_lines (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, product_id TEXT NOT NULL)');

$connection->table('oneqay_pos_sale_catalog_items')->insert([
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'eligible-product', 'display_name' => 'Eligible Product', 'unit_price_atomic' => 15000, 'currency' => 'IDR', 'currency_scale' => 0, 'available_quantity' => 0, 'active' => 1],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'baseline-product', 'display_name' => 'Baseline Product', 'unit_price_atomic' => 1250, 'currency' => 'USD', 'currency_scale' => 2, 'available_quantity' => 20, 'active' => 1],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'sold-product', 'display_name' => 'Sold Product', 'unit_price_atomic' => 9000, 'currency' => 'IDR', 'currency_scale' => 0, 'available_quantity' => 0, 'active' => 1],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'inactive-product', 'display_name' => 'Inactive Product', 'unit_price_atomic' => 5000, 'currency' => 'IDR', 'currency_scale' => 0, 'available_quantity' => 0, 'active' => 0],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-b', 'product_id' => 'foreign-outlet', 'display_name' => 'Foreign Outlet', 'unit_price_atomic' => 1, 'currency' => 'IDR', 'currency_scale' => 0, 'available_quantity' => 1, 'active' => 1],
    ['tenant_id' => 'tenant-b', 'outlet_id' => 'outlet-a', 'product_id' => 'foreign-tenant', 'display_name' => 'Foreign Tenant', 'unit_price_atomic' => 1, 'currency' => 'IDR', 'currency_scale' => 0, 'available_quantity' => 1, 'active' => 1],
]);
$connection->table('oneqay_pos_inventory_baselines')->insert([
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'baseline-product'],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-b', 'product_id' => 'eligible-product'],
]);
$connection->table('oneqay_pos_sales')->insert([
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-'.str_repeat('a', 24), 'outlet_id' => 'outlet-a'],
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-'.str_repeat('b', 24), 'outlet_id' => 'outlet-b'],
]);
$connection->table('oneqay_pos_sale_lines')->insert([
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-'.str_repeat('a', 24), 'product_id' => 'sold-product'],
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-'.str_repeat('b', 24), 'product_id' => 'eligible-product'],
]);

$context = PosExecutionContext::fromVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('device-a'),
));

$repository = new LaravelPosCatalogInventorySetupWorkspaceRepository($connection, true, 'ci', true, true, true);
$snapshot = $repository->read($context);
$assert($snapshot->tenantId() === 'tenant-a' && $snapshot->organizationId() === 'org-a' && $snapshot->outletId() === 'outlet-a', 'Sprint161 scope snapshot changed.');
$assert(count($snapshot->items()) === 4, 'Sprint161 leaked foreign tenant/outlet catalog items.');
$items = [];
foreach ($snapshot->items() as $item) { $items[$item['product_id']] = $item; }
$assert($items['eligible-product']['baseline_eligible'] === true, 'Sprint161 eligible zero-stock unsold product must be baseline eligible.');
$assert($items['baseline-product']['baseline_established'] === true && $items['baseline-product']['baseline_eligible'] === false, 'Sprint161 established baseline must block a second opening baseline.');
$assert($items['baseline-product']['unit_price_atomic'] === '1250' && $items['baseline-product']['currency'] === 'USD' && $items['baseline-product']['scale'] === 2, 'Sprint161 price atomic/currency/scale boundary changed.');
$assert($items['sold-product']['sale_history_exists'] === true && $items['sold-product']['baseline_eligible'] === false, 'Sprint161 sale history must block opening baseline exactly like canonical mutation authority.');
$assert($items['inactive-product']['sellable'] === false && $items['inactive-product']['baseline_eligible'] === true, 'Sprint161 inactive catalog state must remain visible without inventing a baseline restriction.');

foreach ([
    [false, 'ci', true, true, true],
    [true, 'production', true, true, true],
    [true, 'ci', false, true, true],
    [true, 'ci', true, false, true],
    [true, 'ci', true, true, false],
] as [$persistence, $runtime, $workspace, $catalog, $baseline]) {
    try {
        (new LaravelPosCatalogInventorySetupWorkspaceRepository($connection, $persistence, $runtime, $workspace, $catalog, $baseline))->read($context);
        $assert(false, 'Sprint161 accepted a disallowed persistence/runtime/capability state.');
    } catch (PosTransactionViolation) {
        // Expected fail-closed boundary.
    }
}

$connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-a')
    ->where('outlet_id', 'outlet-a')
    ->where('product_id', 'eligible-product')
    ->update(['currency' => 'bad']);
try {
    $repository->read($context);
    $assert(false, 'Sprint161 accepted malformed catalog currency evidence.');
} catch (PosTransactionViolation) {
    // Expected fail-closed integrity boundary.
}

$databaseManager->disconnect('s161_catalog_inventory');
$databaseManager->purge('s161_catalog_inventory');
@unlink($databasePath);

fwrite(STDOUT, "Sprint161 POS catalog inventory setup workspace regression passed.\n");
