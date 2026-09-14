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
use App\Infrastructure\Pos\LaravelPosCashierWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('c', 32));
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

$databasePath = sys_get_temp_dir().'/oneqay-s157-cashier-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint157 disposable cashier database could not be created.');

$app['config']->set('database.default', 's157_cashier');
$app['config']->set('database.connections.s157_cashier', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => true,
]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s157_cashier');
$connection = $databaseManager->connection('s157_cashier');

$connection->statement('CREATE TABLE oneqay_pos_shifts (tenant_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NOT NULL, active_slot INTEGER NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_catalog_items (tenant_id TEXT NOT NULL, outlet_id TEXT NOT NULL, product_id TEXT NOT NULL, display_name TEXT NOT NULL, unit_price_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, available_quantity INTEGER NOT NULL, active INTEGER NOT NULL)');

$connection->table('oneqay_pos_shifts')->insert([
    ['tenant_id' => 'tenant-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'device-a', 'shift_id' => 'shift-active-a', 'active_slot' => 1],
    ['tenant_id' => 'tenant-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'device-b', 'shift_id' => 'shift-device-b', 'active_slot' => 1],
    ['tenant_id' => 'tenant-b', 'organization_id' => 'org-b', 'outlet_id' => 'outlet-a', 'device_id' => 'device-a', 'shift_id' => 'shift-tenant-b', 'active_slot' => 1],
]);

$connection->table('oneqay_pos_sale_catalog_items')->insert([
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'product-aaa-001', 'display_name' => 'Americano', 'unit_price_atomic' => 18000, 'currency' => 'IDR', 'currency_scale' => 0, 'available_quantity' => 12, 'active' => 1],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'product-bbb-002', 'display_name' => 'Brownie', 'unit_price_atomic' => 250, 'currency' => 'USD', 'currency_scale' => 2, 'available_quantity' => 4, 'active' => 1],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'product-zero-003', 'display_name' => 'Zero stock', 'unit_price_atomic' => 9000, 'currency' => 'IDR', 'currency_scale' => 0, 'available_quantity' => 0, 'active' => 1],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'product-off-004', 'display_name' => 'Inactive', 'unit_price_atomic' => 9000, 'currency' => 'IDR', 'currency_scale' => 0, 'available_quantity' => 9, 'active' => 0],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-b', 'product_id' => 'product-other-005', 'display_name' => 'Other outlet', 'unit_price_atomic' => 999999, 'currency' => 'IDR', 'currency_scale' => 0, 'available_quantity' => 99, 'active' => 1],
    ['tenant_id' => 'tenant-b', 'outlet_id' => 'outlet-a', 'product_id' => 'product-other-006', 'display_name' => 'Other tenant', 'unit_price_atomic' => 888888, 'currency' => 'IDR', 'currency_scale' => 0, 'available_quantity' => 88, 'active' => 1],
]);

$context = PosExecutionContext::fromVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('device-a'),
));
$repository = new LaravelPosCashierWorkspaceRepository($connection, true, 'ci', true, true);
$snapshot = $repository->snapshot($context);

$assert($snapshot->tenantId() === 'tenant-a', 'Sprint157 cashier tenant scope changed.');
$assert($snapshot->organizationId() === 'org-a', 'Sprint157 cashier organization scope changed.');
$assert($snapshot->outletId() === 'outlet-a', 'Sprint157 cashier outlet scope changed.');
$assert($snapshot->deviceId() === 'device-a', 'Sprint157 cashier device scope changed.');
$assert($snapshot->activeShiftId() === 'shift-active-a', 'Sprint157 cashier must bind active shift to the exact device.');

$items = $snapshot->catalogItems();
$assert(count($items) === 2, 'Sprint157 cashier leaked inactive, zero-stock, foreign outlet, or foreign tenant catalog rows.');
$assert($items[0]->productId() === 'product-aaa-001' && $items[0]->displayName() === 'Americano', 'Sprint157 cashier catalog ordering or product identity changed.');
$assert($items[0]->availableQuantity() === 12, 'Sprint157 cashier available quantity changed.');
$assert($items[0]->unitPrice()->atomicUnits() === 18000 && $items[0]->unitPrice()->currency() === 'IDR' && $items[0]->unitPrice()->scale() === 0, 'Sprint157 cashier IDR money boundary changed.');
$assert($items[1]->productId() === 'product-bbb-002', 'Sprint157 cashier second catalog item changed.');
$assert($items[1]->unitPrice()->atomicUnits() === 250 && $items[1]->unitPrice()->currency() === 'USD' && $items[1]->unitPrice()->scale() === 2, 'Sprint157 cashier must preserve currency and scale independently.');

$withoutShift = PosExecutionContext::fromVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('device-c'),
));
$assert($repository->snapshot($withoutShift)->activeShiftId() === null, 'Sprint157 cashier must not borrow another device active shift.');

foreach ([
    [false, 'ci', true, true],
    [true, 'production', true, true],
    [true, 'ci', false, true],
    [true, 'ci', true, false],
] as [$persistence, $runtime, $workspace, $saleCompletion]) {
    try {
        (new LaravelPosCashierWorkspaceRepository($connection, $persistence, $runtime, $workspace, $saleCompletion))->snapshot($context);
        $assert(false, 'Sprint157 cashier accepted a disallowed persistence/runtime/feature state.');
    } catch (PosTransactionViolation) {
        // Expected fail-closed boundary.
    }
}

$databaseManager->disconnect('s157_cashier');
$databaseManager->purge('s157_cashier');
@unlink($databasePath);

fwrite(STDOUT, "Sprint157 POS cashier workspace regression passed.\n");
