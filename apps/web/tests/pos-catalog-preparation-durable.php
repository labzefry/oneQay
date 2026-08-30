<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\CatalogPreparationCommand;
use App\Application\Pos\CompleteSale;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\PrepareCatalogItem;
use App\Application\Pos\SaleCommand;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Pos\Cart;
use App\Domain\Pos\CartLine;
use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use App\Domain\Pos\TenderCategory;
use App\Domain\Tenancy\TenantId;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('z', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_POS_SALE_COMPLETION_ENABLED' => 'true',
    'ONEQAY_POS_CATALOG_PREPARATION_ENABLED' => 'true',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
$app->instance('request', Request::create('/'));
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Sprint47 JRN-004 catalog preparation regression failed: '.$case);
    }
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s47-catalog-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'catalog.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 's47_catalog');
$app['config']->set('database.connections.s47_catalog', [
    'driver' => 'sqlite', 'url' => null, 'database' => $db, 'prefix' => '',
    'foreign_key_constraints' => true, 'busy_timeout' => null, 'journal_mode' => null, 'synchronous' => null,
]);
$app['config']->set('database.oneqay_persistence_enabled', true);
$app['config']->set('oneqay.runtime_class', 'ci');
$app['config']->set('oneqay.session_control.enabled', true);
$app['config']->set('oneqay.pos_sale_completion.enabled', true);
$app['config']->set('oneqay.pos_catalog_preparation.enabled', true);

$manager = $app->make('db');
$manager->purge('s47_catalog');
$manager->setDefaultConnection('s47_catalog');
$connection = $manager->connection('s47_catalog');
$connection->getPdo();

$migrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($migrations);
$assert(count($migrations) === 17, 'migration set count');
for ($index = 1; $index <= 17; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $assert(
        count(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix))) === 1,
        'migration #'.$index.' exact',
    );
}
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
foreach ([
    ['tenant-alpha', 'catalog-admin-alpha'],
    ['tenant-alpha', 'no-permission-alpha'],
    ['tenant-beta', 'catalog-admin-beta'],
] as [$tenant, $identity]) {
    $connection->table('oneqay_identities')->insert(['tenant_id' => $tenant, 'id' => $identity]);
}
foreach ([
    ['tenant-alpha', 'organization-alpha'],
    ['tenant-beta', 'organization-beta'],
] as [$tenant, $organization]) {
    $connection->table('oneqay_organizations')->insert(['tenant_id' => $tenant, 'id' => $organization]);
}
foreach ([
    ['tenant-alpha', 'catalog-admin-alpha', 'organization-alpha'],
    ['tenant-alpha', 'no-permission-alpha', 'organization-alpha'],
    ['tenant-beta', 'catalog-admin-beta', 'organization-beta'],
] as [$tenant, $identity, $organization]) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => $tenant,
        'identity_id' => $identity,
        'organization_id' => $organization,
    ]);
}
foreach ([
    ['tenant-alpha', 'outlet-alpha', 'organization-alpha'],
    ['tenant-beta', 'outlet-beta', 'organization-beta'],
] as [$tenant, $outlet, $organization]) {
    $connection->table('oneqay_outlets')->insert([
        'tenant_id' => $tenant,
        'id' => $outlet,
        'organization_id' => $organization,
    ]);
}
foreach ([
    ['tenant-alpha', 'device-alpha', 'organization-alpha', 'outlet-alpha'],
    ['tenant-beta', 'device-beta', 'organization-beta', 'outlet-beta'],
] as [$tenant, $device, $organization, $outlet]) {
    $connection->table('oneqay_devices')->insert([
        'tenant_id' => $tenant,
        'id' => $device,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
    ]);
}

$connection->table('oneqay_roles')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'catalog-role'],
    ['tenant_id' => 'tenant-beta', 'id' => 'catalog-role'],
]);
$connection->table('oneqay_role_permissions')->insert([
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'catalog-role', 'permission_id' => PosPermission::COMPLETE_SALE],
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'catalog-role', 'permission_id' => PosPermission::PREPARE_CATALOG],
    ['tenant_id' => 'tenant-beta', 'role_id' => 'catalog-role', 'permission_id' => PosPermission::PREPARE_CATALOG],
]);
$connection->table('oneqay_outlet_role_assignments')->insert([
    [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => 'catalog-admin-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => 'outlet-alpha',
        'role_id' => 'catalog-role',
    ],
    [
        'tenant_id' => 'tenant-beta',
        'identity_id' => 'catalog-admin-beta',
        'organization_id' => 'organization-beta',
        'outlet_id' => 'outlet-beta',
        'role_id' => 'catalog-role',
    ],
]);

$connection->table('oneqay_pos_sale_catalog_items')->insert([
    [
        'tenant_id' => 'tenant-alpha',
        'outlet_id' => 'outlet-alpha',
        'product_id' => 'product-a',
        'display_name' => 'Product A',
        'unit_price_atomic' => 2500,
        'currency' => 'IDR',
        'currency_scale' => 0,
        'available_quantity' => 10,
        'active' => true,
    ],
    [
        'tenant_id' => 'tenant-beta',
        'outlet_id' => 'outlet-beta',
        'product_id' => 'product-b',
        'display_name' => 'Product B',
        'unit_price_atomic' => 9000,
        'currency' => 'IDR',
        'currency_scale' => 0,
        'available_quantity' => 7,
        'active' => true,
    ],
]);

$app->forgetScopedInstances();
$contexts = $app->make(OrganizationalContextStore::class);
$contexts->setVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('catalog-admin-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha'),
));

$sales = $app->make(CompleteSale::class);
$sale = $sales->complete(new SaleCommand(
    'sale-before-catalog-0001',
    Cart::fromLines([new CartLine(ProductId::fromString('product-a'), 1)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(2500, 'IDR', 0),
    'correlation-sale-before-0001',
));
$assert($sale->total()->atomicUnits() === 2500, 'baseline sale total');
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('outlet_id', 'outlet-alpha')
        ->where('product_id', 'product-a')
        ->value('available_quantity') === 9,
    'baseline sale stock decrement',
);

$catalog = $app->make(PrepareCatalogItem::class);
$firstCommand = new CatalogPreparationCommand(
    'catalog-operation-alpha-0001',
    ProductId::fromString('product-a'),
    'Product A Updated',
    Money::fromAtomicUnits(3000, 'IDR', 0),
    true,
);
$first = $catalog->prepare($firstCommand, 'correlation-catalog-alpha-0001');
$assert(strlen($first->mutationId()) === 32, 'mutation id length');
$assert($first->unitPrice()->atomicUnits() === 3000, 'first after-state price');
$assert($first->sellable(), 'first after-state sellability');
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('outlet_id', 'outlet-alpha')
        ->where('product_id', 'product-a')
        ->value('available_quantity') === 9,
    'existing stock quantity preserved',
);
$journal = $connection->table('oneqay_pos_catalog_preparation_journal')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'catalog-operation-alpha-0001')
    ->first();
$assert($journal !== null, 'journal persisted');
$assert((bool) $journal->before_exists, 'before existence recorded');
$assert((int) $journal->before_unit_price_atomic === 2500, 'before price recorded');
$assert((int) $journal->after_unit_price_atomic === 3000, 'after price recorded');
$assert((string) $journal->actor_identity_id === 'catalog-admin-alpha', 'verified actor recorded');
$assert((string) $journal->outlet_id === 'outlet-alpha', 'verified outlet recorded');

$replay = $catalog->prepare($firstCommand, 'correlation-catalog-alpha-replay');
$assert($replay->mutationId() === $first->mutationId(), 'exact replay mutation identity');
$assert(
    $connection->table('oneqay_pos_catalog_preparation_journal')
        ->where('tenant_id', 'tenant-alpha')
        ->where('operation_id', 'catalog-operation-alpha-0001')
        ->count() === 1,
    'exact replay no second journal row',
);

$second = $catalog->prepare(new CatalogPreparationCommand(
    'catalog-operation-alpha-0002',
    ProductId::fromString('product-a'),
    'Product A Later',
    Money::fromAtomicUnits(3500, 'IDR', 0),
    false,
), 'correlation-catalog-alpha-0002');
$assert($second->unitPrice()->atomicUnits() === 3500 && ! $second->sellable(), 'later state applied');
$current = $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-alpha')
    ->where('outlet_id', 'outlet-alpha')
    ->where('product_id', 'product-a')
    ->first();
$assert($current !== null && (int) $current->unit_price_atomic === 3500 && ! (bool) $current->active, 'later current state');
$assert((int) $current->available_quantity === 9, 'later mutation still preserves stock');

$oldReplay = $catalog->prepare($firstCommand, 'correlation-catalog-alpha-old-replay');
$assert($oldReplay->unitPrice()->atomicUnits() === 3000 && $oldReplay->sellable(), 'old replay returns original after-state');
$currentAfterReplay = $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-alpha')
    ->where('outlet_id', 'outlet-alpha')
    ->where('product_id', 'product-a')
    ->first();
$assert(
    $currentAfterReplay !== null
        && (int) $currentAfterReplay->unit_price_atomic === 3500
        && ! (bool) $currentAfterReplay->active,
    'old replay does not restore stale state',
);

try {
    $catalog->prepare(new CatalogPreparationCommand(
        'catalog-operation-alpha-0001',
        ProductId::fromString('product-a'),
        'Conflicting Product A',
        Money::fromAtomicUnits(4000, 'IDR', 0),
        true,
    ), 'correlation-catalog-alpha-conflict');
    $assert(false, 'conflicting replay accepted');
} catch (PosTransactionViolation) {}

$created = $catalog->prepare(new CatalogPreparationCommand(
    'catalog-operation-alpha-0003',
    ProductId::fromString('product-new'),
    'Product New',
    Money::fromAtomicUnits(1200, 'IDR', 0),
    true,
), 'correlation-catalog-alpha-0003');
$assert($created->productId() === 'product-new', 'new product prepared');
$newRow = $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-alpha')
    ->where('outlet_id', 'outlet-alpha')
    ->where('product_id', 'product-new')
    ->first();
$assert($newRow !== null && (int) $newRow->available_quantity === 0, 'new row server-owned zero stock');

$catalog->prepare(new CatalogPreparationCommand(
    'catalog-operation-alpha-0004',
    ProductId::fromString('product-b'),
    'Alpha Product B',
    Money::fromAtomicUnits(1000, 'IDR', 0),
    true,
), 'correlation-catalog-alpha-0004');
$betaRow = $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-beta')
    ->where('outlet_id', 'outlet-beta')
    ->where('product_id', 'product-b')
    ->first();
$assert(
    $betaRow !== null
        && (int) $betaRow->unit_price_atomic === 9000
        && (int) $betaRow->available_quantity === 7,
    'cross-tenant current state untouched',
);

$saleLine = $connection->table('oneqay_pos_sale_lines')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $sale->saleId())
    ->first();
$assert($saleLine !== null && (int) $saleLine->unit_price_atomic === 2500, 'historical sale price snapshot preserved');

$contexts->setVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('no-permission-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha'),
));
try {
    $catalog->prepare(new CatalogPreparationCommand(
        'catalog-operation-alpha-0005',
        ProductId::fromString('product-denied'),
        'Denied Product',
        Money::fromAtomicUnits(1000, 'IDR', 0),
        true,
    ), 'correlation-catalog-alpha-denied');
    $assert(false, 'no-permission actor prepared catalog');
} catch (DurableAuthorizationViolation) {}

$constructor = new ReflectionMethod(CatalogPreparationCommand::class, '__construct');
$constructorParameters = array_map(
    static fn (ReflectionParameter $parameter): string => $parameter->getName(),
    $constructor->getParameters(),
);
$assert(
    $constructorParameters === ['operationId', 'productId', 'displayName', 'unitPrice', 'sellable'],
    'command accepts only bounded business mutation state',
);

$assert(Route::has('pos.catalog.prepare'), 'armed catalog route absent');
$route = Route::getRoutes()->getByName('pos.catalog.prepare');
$assert($route !== null, 'catalog route unresolved');
$middleware = $route->gatherMiddleware();
$assert(in_array('session.active', $middleware, true), 'catalog active-session middleware missing');
$assert(
    in_array(App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware::class, $middleware, true),
    'catalog verified POS context middleware missing',
);

$manager->disconnect('s47_catalog');
$manager->purge('s47_catalog');
@unlink($db);
@rmdir($workspace);

echo "Sprint47 JRN-004 catalog preparation regression passed.\n";
