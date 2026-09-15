<?php

declare(strict_types=1);

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\InventoryReplenishmentCommand;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Pos\ProductId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Pos\LaravelInventoryReplenishmentRepository;
use App\Infrastructure\Pos\LaravelPosInventoryReplenishmentWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('v', 32));
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

$databasePath = sys_get_temp_dir().'/oneqay-s164-inventory-replenishment-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint164 disposable database could not be created.');
$app['config']->set('database.default', 's164_inventory_replenishment');
$app['config']->set('database.connections.s164_inventory_replenishment', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => false,
]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s164_inventory_replenishment');
$connection = $databaseManager->connection('s164_inventory_replenishment');

$connection->statement('CREATE TABLE oneqay_pos_sale_catalog_items (tenant_id TEXT NOT NULL, outlet_id TEXT NOT NULL, product_id TEXT NOT NULL, display_name TEXT NOT NULL, available_quantity INTEGER NOT NULL, active INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_inventory_baselines (tenant_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, product_id TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_inventory_replenishments (tenant_id TEXT NOT NULL, replenishment_id TEXT NOT NULL, operation_id TEXT NOT NULL, payload_fingerprint TEXT NOT NULL, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, product_id TEXT NOT NULL, before_available_quantity INTEGER NOT NULL, replenished_quantity INTEGER NOT NULL, after_available_quantity INTEGER NOT NULL, correlation_id TEXT NOT NULL, occurred_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE UNIQUE INDEX uq_s164_operation ON oneqay_pos_inventory_replenishments (tenant_id, operation_id)');

$connection->table('oneqay_pos_sale_catalog_items')->insert([
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'coffee', 'display_name' => 'Coffee', 'available_quantity' => 5, 'active' => 1],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'inactive', 'display_name' => 'Inactive', 'available_quantity' => 8, 'active' => 0],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'no-baseline', 'display_name' => 'No Baseline', 'available_quantity' => 2, 'active' => 1],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-b', 'product_id' => 'foreign-outlet', 'display_name' => 'Foreign Outlet', 'available_quantity' => 3, 'active' => 1],
    ['tenant_id' => 'tenant-b', 'outlet_id' => 'outlet-a', 'product_id' => 'foreign-tenant', 'display_name' => 'Foreign Tenant', 'available_quantity' => 4, 'active' => 1],
]);
$connection->table('oneqay_pos_inventory_baselines')->insert([
    ['tenant_id' => 'tenant-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'product_id' => 'coffee'],
    ['tenant_id' => 'tenant-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'product_id' => 'inactive'],
    ['tenant_id' => 'tenant-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-b', 'product_id' => 'foreign-outlet'],
    ['tenant_id' => 'tenant-b', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'product_id' => 'foreign-tenant'],
]);

$context = PosExecutionContext::fromVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('device-a'),
));

$repository = new LaravelInventoryReplenishmentRepository($connection, true, 'ci', true);
$command = new InventoryReplenishmentCommand('replenish:operation-0001', ProductId::fromString('coffee'), 7);
$result = $repository->replenish($context, $command, 'correlation-s164-0001', 1789403000);
$assert($result->beforeAvailableQuantity() === 5, 'Sprint164 before stock changed.');
$assert($result->replenishedQuantity() === 7, 'Sprint164 received quantity changed.');
$assert($result->afterAvailableQuantity() === 12, 'Sprint164 after stock arithmetic changed.');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('tenant_id', 'tenant-a')->where('outlet_id', 'outlet-a')->where('product_id', 'coffee')->value('available_quantity') === 12, 'Sprint164 did not persist authoritative catalog stock.');
$assert($connection->table('oneqay_pos_inventory_replenishments')->count() === 1, 'Sprint164 did not persist exactly one replenishment evidence row.');

$replay = $repository->replenish($context, $command, 'correlation-s164-replay', 1789403010);
$assert($replay->replenishmentId() === $result->replenishmentId(), 'Sprint164 idempotent replay identity changed.');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('tenant_id', 'tenant-a')->where('outlet_id', 'outlet-a')->where('product_id', 'coffee')->value('available_quantity') === 12, 'Sprint164 idempotent replay changed stock twice.');

try {
    $repository->replenish(
        $context,
        new InventoryReplenishmentCommand('replenish:operation-0001', ProductId::fromString('coffee'), 8),
        'correlation-s164-conflict',
        1789403020,
    );
    $assert(false, 'Sprint164 accepted operation replay with a different fingerprint.');
} catch (PosTransactionViolation) {
    // Expected.
}

foreach ([['no-baseline', 2], ['inactive', 2]] as [$productId, $quantity]) {
    try {
        $repository->replenish(
            $context,
            new InventoryReplenishmentCommand('replenish:'.str_replace('-', '', $productId).'-0001', ProductId::fromString($productId), $quantity),
            'correlation-s164-rejected',
            1789403030,
        );
        $assert(false, 'Sprint164 accepted replenishment without baseline or on inactive catalog state.');
    } catch (PosTransactionViolation) {
        // Expected.
    }
}

$workspace = new LaravelPosInventoryReplenishmentWorkspaceRepository($connection, true, 'ci', true);
$snapshot = $workspace->read($context);
$assert($snapshot->tenantId() === 'tenant-a' && $snapshot->organizationId() === 'org-a' && $snapshot->outletId() === 'outlet-a', 'Sprint164 workspace scope changed.');
$assert(count($snapshot->items()) === 1 && $snapshot->items()[0]['product_id'] === 'coffee', 'Sprint164 workspace leaked inactive, foreign, or non-baselined products.');
$assert($snapshot->items()[0]['available_quantity'] === '12', 'Sprint164 workspace current stock is not authoritative.');
$assert(count($snapshot->recentReplenishments()) === 1, 'Sprint164 recent replenishment evidence changed.');
$recent = $snapshot->recentReplenishments()[0];
$assert($recent['before_available_quantity'] === '5' && $recent['replenished_quantity'] === '7' && $recent['after_available_quantity'] === '12', 'Sprint164 recent evidence arithmetic changed.');

foreach ([[false, 'ci', true], [true, 'production', true], [true, 'ci', false]] as [$persistence, $runtime, $feature]) {
    try {
        (new LaravelInventoryReplenishmentRepository($connection, $persistence, $runtime, $feature))->replenish(
            $context,
            new InventoryReplenishmentCommand('replenish:gate-'.bin2hex(random_bytes(4)), ProductId::fromString('coffee'), 1),
            'correlation-s164-gate',
            1789403040,
        );
        $assert(false, 'Sprint164 mutation accepted a disallowed persistence/runtime/feature state.');
    } catch (PosTransactionViolation) {
        // Expected fail-closed boundary.
    }

    try {
        (new LaravelPosInventoryReplenishmentWorkspaceRepository($connection, $persistence, $runtime, $feature))->read($context);
        $assert(false, 'Sprint164 workspace accepted a disallowed persistence/runtime/feature state.');
    } catch (PosTransactionViolation) {
        // Expected fail-closed boundary.
    }
}

$connection->table('oneqay_pos_inventory_replenishments')
    ->where('tenant_id', 'tenant-a')
    ->where('operation_id', 'replenish:operation-0001')
    ->update(['after_available_quantity' => 99]);
try {
    $workspace->read($context);
    $assert(false, 'Sprint164 workspace accepted corrupt replenishment arithmetic.');
} catch (PosTransactionViolation) {
    // Expected fail-closed integrity boundary.
}

$databaseManager->disconnect('s164_inventory_replenishment');
$databaseManager->purge('s164_inventory_replenishment');
@unlink($databasePath);

fwrite(STDOUT, "Sprint164 POS inventory replenishment workspace regression passed.\n");
