<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\DurableRolePermissionRepository;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ReplenishInventory;
use App\Application\Pos\ViewPosInventoryAccountabilityWorkspace;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Pos\LaravelPosInventoryAccountabilityWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('a', 32));
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

$databasePath = sys_get_temp_dir().'/oneqay-s165-inventory-accountability-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint165 disposable database could not be created.');
$app['config']->set('database.default', 's165_inventory_accountability');
$app['config']->set('database.connections.s165_inventory_accountability', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => false,
]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s165_inventory_accountability');
$connection = $databaseManager->connection('s165_inventory_accountability');

$connection->statement('CREATE TABLE oneqay_pos_sale_catalog_items (tenant_id TEXT NOT NULL, outlet_id TEXT NOT NULL, product_id TEXT NOT NULL, display_name TEXT NOT NULL, available_quantity INTEGER NOT NULL, active INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_inventory_baselines (tenant_id TEXT NOT NULL, baseline_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, product_id TEXT NOT NULL, opening_quantity INTEGER NOT NULL, occurred_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_inventory_replenishments (tenant_id TEXT NOT NULL, replenishment_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, product_id TEXT NOT NULL, before_available_quantity INTEGER NOT NULL, replenished_quantity INTEGER NOT NULL, after_available_quantity INTEGER NOT NULL, occurred_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, completed_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_lines (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, line_no INTEGER NOT NULL, product_id TEXT NOT NULL, quantity INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, void_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, voided_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, refund_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, refunded_at_unix INTEGER NOT NULL)');

$connection->table('oneqay_pos_sale_catalog_items')->insert([
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'coffee', 'display_name' => 'Coffee', 'available_quantity' => 12, 'active' => 1],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-a', 'product_id' => 'inactive', 'display_name' => 'Inactive', 'available_quantity' => 2, 'active' => 0],
    ['tenant_id' => 'tenant-a', 'outlet_id' => 'outlet-b', 'product_id' => 'foreign-outlet', 'display_name' => 'Foreign Outlet', 'available_quantity' => 9, 'active' => 1],
    ['tenant_id' => 'tenant-b', 'outlet_id' => 'outlet-a', 'product_id' => 'foreign-tenant', 'display_name' => 'Foreign Tenant', 'available_quantity' => 9, 'active' => 1],
]);
$connection->table('oneqay_pos_inventory_baselines')->insert([
    ['tenant_id' => 'tenant-a', 'baseline_id' => 'baseline-coffee', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'product_id' => 'coffee', 'opening_quantity' => 10, 'occurred_at_unix' => 1789401000],
    ['tenant_id' => 'tenant-a', 'baseline_id' => 'baseline-inactive', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'product_id' => 'inactive', 'opening_quantity' => 2, 'occurred_at_unix' => 1789401001],
    ['tenant_id' => 'tenant-a', 'baseline_id' => 'baseline-foreign-outlet', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-b', 'product_id' => 'foreign-outlet', 'opening_quantity' => 9, 'occurred_at_unix' => 1789401002],
    ['tenant_id' => 'tenant-b', 'baseline_id' => 'baseline-foreign-tenant', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'product_id' => 'foreign-tenant', 'opening_quantity' => 9, 'occurred_at_unix' => 1789401003],
]);
$connection->table('oneqay_pos_inventory_replenishments')->insert([
    ['tenant_id' => 'tenant-a', 'replenishment_id' => 'replenishment-coffee', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'product_id' => 'coffee', 'before_available_quantity' => 10, 'replenished_quantity' => 5, 'after_available_quantity' => 15, 'occurred_at_unix' => 1789402000],
]);
$connection->table('oneqay_pos_sales')->insert([
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-1', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'completed_at_unix' => 1789403000],
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-2', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'completed_at_unix' => 1789405000],
]);
$connection->table('oneqay_pos_sale_lines')->insert([
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-1', 'line_no' => 1, 'product_id' => 'coffee', 'quantity' => 4],
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-2', 'line_no' => 1, 'product_id' => 'coffee', 'quantity' => 3],
]);
$connection->table('oneqay_pos_sale_voids')->insert([
    ['tenant_id' => 'tenant-a', 'void_id' => 'void-1', 'sale_id' => 'sale-1', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'voided_at_unix' => 1789404000],
]);
$connection->table('oneqay_pos_sale_cash_refunds')->insert([
    ['tenant_id' => 'tenant-a', 'refund_id' => 'refund-1', 'sale_id' => 'sale-2', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'refunded_at_unix' => 1789406000],
]);

$verified = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('device-a'),
);
$context = PosExecutionContext::fromVerified($verified);
$repository = new LaravelPosInventoryAccountabilityWorkspaceRepository($connection, true, 'ci', true);
$snapshot = $repository->read($context);

$assert($snapshot->tenantId() === 'tenant-a' && $snapshot->organizationId() === 'org-a', 'Sprint165 changed tenant/organization scope.');
$assert($snapshot->outletId() === 'outlet-a' && $snapshot->deviceId() === 'device-a', 'Sprint165 changed outlet/device scope.');
$assert(count($snapshot->items()) === 2, 'Sprint165 leaked foreign scope or excluded inactive baselined stock.');
$coffee = array_values(array_filter($snapshot->items(), static fn (array $item): bool => $item['product_id'] === 'coffee'))[0] ?? null;
$assert(is_array($coffee), 'Sprint165 coffee accountability row missing.');
$assert($coffee['opening_quantity'] === '10', 'Sprint165 opening quantity changed.');
$assert($coffee['replenished_quantity'] === '5', 'Sprint165 replenishment total changed.');
$assert($coffee['sold_quantity'] === '7', 'Sprint165 sale decrement total changed.');
$assert($coffee['restored_quantity'] === '4', 'Sprint165 void restoration total changed.');
$assert($coffee['expected_available_quantity'] === '12' && $coffee['current_available_quantity'] === '12', 'Sprint165 stock reconciliation equation changed.');

$coffeeMovements = array_values(array_filter(
    $snapshot->recentMovements(),
    static fn (array $movement): bool => $movement['product_id'] === 'coffee',
));
$assert(count($coffeeMovements) === 5, 'Sprint165 movement evidence must contain baseline, replenishment, two sales, and one void only.');
$types = array_column($coffeeMovements, 'movement_type');
sort($types);
$assert($types === ['FULL_SALE_VOID_RESTORATION', 'OPENING_BASELINE', 'REPLENISHMENT', 'SALE_DECREMENT', 'SALE_DECREMENT'], 'Sprint165 movement kinds changed or cash refund was treated as stock movement.');

$store = new class($verified) implements OrganizationalContextStore {
    public function __construct(private ?VerifiedOrganizationalContext $context) {}
    public function current(): ?VerifiedOrganizationalContext { return $this->context; }
    public function setVerified(VerifiedOrganizationalContext $context): void { $this->context = $context; }
    public function clear(): void { $this->context = null; }
};

$viewFor = static function (array $grants) use ($store, $repository): ViewPosInventoryAccountabilityWorkspace {
    $authorizationRepository = new class($grants) implements DurableRolePermissionRepository {
        /** @param list<string> $grants */
        public function __construct(private array $grants) {}
        public function allows(VerifiedOrganizationalContext $context, PermissionIdentifier $permission): bool
        {
            return in_array($permission->value(), $this->grants, true);
        }
    };

    return new ViewPosInventoryAccountabilityWorkspace(
        $repository,
        $store,
        new DurableScopedAuthorizationPolicy($authorizationRepository),
    );
};

$assert($viewFor(['pos.inventory.baseline'])->view()->outletId() === 'outlet-a', 'Sprint165 baseline authority must allow accountability view.');
$assert($viewFor([ReplenishInventory::REPLENISH_PERMISSION])->view()->outletId() === 'outlet-a', 'Sprint165 replenishment authority must allow accountability view.');
try {
    $viewFor([])->view();
    $assert(false, 'Sprint165 accountability workspace allowed a context without an existing inventory authority.');
} catch (DurableAuthorizationViolation) {
    // Expected deny-by-default boundary.
}

foreach ([[false, 'ci', true], [true, 'production', true], [true, 'ci', false]] as [$persistence, $runtime, $feature]) {
    try {
        (new LaravelPosInventoryAccountabilityWorkspaceRepository($connection, $persistence, $runtime, $feature))->read($context);
        $assert(false, 'Sprint165 workspace accepted a disallowed persistence/runtime/feature state.');
    } catch (PosTransactionViolation) {
        // Expected.
    }
}

$connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-a')
    ->where('outlet_id', 'outlet-a')
    ->where('product_id', 'coffee')
    ->update(['available_quantity' => 99]);
try {
    $repository->read($context);
    $assert(false, 'Sprint165 accepted catalog stock that does not reconcile to immutable evidence.');
} catch (PosTransactionViolation) {
    // Expected fail-closed integrity boundary.
}

$databaseManager->disconnect('s165_inventory_accountability');
$databaseManager->purge('s165_inventory_accountability');
@unlink($databasePath);

fwrite(STDOUT, "Sprint165 POS inventory accountability workspace regression passed.\n");
