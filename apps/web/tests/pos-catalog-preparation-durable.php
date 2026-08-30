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
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
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
        throw new RuntimeException('Sprint47 JRN-004 durable catalog regression failed: '.$case);
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
$app['config']->set('oneqay.session_control.idle_ttl_seconds', 7200);
$app['config']->set('oneqay.session_control.absolute_ttl_seconds', 43200);
$app['config']->set('oneqay.pos_sale_completion.enabled', true);
$app['config']->set('oneqay.pos_catalog_preparation.enabled', true);

$manager = $app->make('db');
$manager->purge('s47_catalog');
$manager->setDefaultConnection('s47_catalog');
$connection = $manager->connection('s47_catalog');
$connection->getPdo();

$migrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($migrations);
$assert(count($migrations) === 17, 'migration set count');
for ($index = 1; $index <= 17; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $assert(count(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix))) === 1, 'migration #'.$index.' exact');
}
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
foreach ([
    ['tenant-alpha','manager-alpha'],
    ['tenant-alpha','no-permission-alpha'],
    ['tenant-beta','manager-beta'],
] as [$tenant,$identity]) {
    $connection->table('oneqay_identities')->insert(['tenant_id'=>$tenant,'id'=>$identity]);
}
foreach ([['tenant-alpha','organization-alpha'],['tenant-beta','organization-beta']] as [$tenant,$org]) {
    $connection->table('oneqay_organizations')->insert(['tenant_id'=>$tenant,'id'=>$org]);
}
foreach ([
    ['tenant-alpha','manager-alpha','organization-alpha'],
    ['tenant-alpha','no-permission-alpha','organization-alpha'],
    ['tenant-beta','manager-beta','organization-beta'],
] as [$tenant,$identity,$org]) {
    $connection->table('oneqay_identity_organizations')->insert(['tenant_id'=>$tenant,'identity_id'=>$identity,'organization_id'=>$org]);
}
foreach ([['tenant-alpha','outlet-alpha','organization-alpha'],['tenant-beta','outlet-beta','organization-beta']] as [$tenant,$outlet,$org]) {
    $connection->table('oneqay_outlets')->insert(['tenant_id'=>$tenant,'id'=>$outlet,'organization_id'=>$org]);
}
foreach ([['tenant-alpha','device-alpha','organization-alpha','outlet-alpha'],['tenant-beta','device-beta','organization-beta','outlet-beta']] as [$tenant,$device,$org,$outlet]) {
    $connection->table('oneqay_devices')->insert(['tenant_id'=>$tenant,'id'=>$device,'organization_id'=>$org,'outlet_id'=>$outlet]);
}

$connection->table('oneqay_roles')->insert([
    ['tenant_id'=>'tenant-alpha','id'=>'catalog-role'],
    ['tenant_id'=>'tenant-beta','id'=>'catalog-role'],
]);
$connection->table('oneqay_role_permissions')->insert([
    ['tenant_id'=>'tenant-alpha','role_id'=>'catalog-role','permission_id'=>PosPermission::PREPARE_CATALOG],
    ['tenant_id'=>'tenant-alpha','role_id'=>'catalog-role','permission_id'=>PosPermission::COMPLETE_SALE],
    ['tenant_id'=>'tenant-beta','role_id'=>'catalog-role','permission_id'=>PosPermission::PREPARE_CATALOG],
]);
$connection->table('oneqay_outlet_role_assignments')->insert([
    ['tenant_id'=>'tenant-alpha','identity_id'=>'manager-alpha','organization_id'=>'organization-alpha','outlet_id'=>'outlet-alpha','role_id'=>'catalog-role'],
    ['tenant_id'=>'tenant-beta','identity_id'=>'manager-beta','organization_id'=>'organization-beta','outlet_id'=>'outlet-beta','role_id'=>'catalog-role'],
]);

$connection->table('oneqay_pos_sale_catalog_items')->insert([
    ['tenant_id'=>'tenant-alpha','outlet_id'=>'outlet-alpha','product_id'=>'product-a','display_name'=>'Product A','unit_price_atomic'=>2500,'currency'=>'IDR','currency_scale'=>0,'available_quantity'=>10,'active'=>true],
    ['tenant_id'=>'tenant-beta','outlet_id'=>'outlet-beta','product_id'=>'product-shared','display_name'=>'Beta Shared','unit_price_atomic'=>9000,'currency'=>'IDR','currency_scale'=>0,'available_quantity'=>8,'active'=>true],
]);

$app->forgetScopedInstances();
$contexts = $app->make(OrganizationalContextStore::class);
$contexts->setVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('manager-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha'),
));
$catalog = $app->make(PrepareCatalogItem::class);

$first = $catalog->prepare(new CatalogPreparationCommand(
    'catalog-operation-alpha-0001',
    ProductId::fromString('product-a'),
    'Product A Prepared',
    Money::fromAtomicUnits(3000, 'IDR', 0),
    false,
    'correlation-catalog-alpha-0001',
));
$assert($first->productId()->value() === 'product-a', 'existing product result');
$assert($first->unitPrice()->atomicUnits() === 3000, 'prepared current price');
$assert($first->sellable() === false, 'prepared sellability');
$row = $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id','tenant-alpha')->where('outlet_id','outlet-alpha')->where('product_id','product-a')->first();
$assert($row !== null && (int) $row->available_quantity === 10, 'existing stock preserved');
$assert((int) $row->unit_price_atomic === 3000 && (bool) $row->active === false, 'existing catalog state updated');
$assert($connection->table('oneqay_pos_catalog_preparation_journal')->where('tenant_id','tenant-alpha')->count() === 1, 'journal persisted once');

$replay = $catalog->prepare(new CatalogPreparationCommand(
    'catalog-operation-alpha-0001',
    ProductId::fromString('product-a'),
    'Product A Prepared',
    Money::fromAtomicUnits(3000, 'IDR', 0),
    false,
    'correlation-catalog-alpha-replay',
));
$assert($replay->mutationId() === $first->mutationId(), 'exact replay mutation identity');
$assert($replay->correlationId() === 'correlation-catalog-alpha-0001', 'replay returns original evidence');
$assert($connection->table('oneqay_pos_catalog_preparation_journal')->where('tenant_id','tenant-alpha')->count() === 1, 'replay no second journal');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('tenant_id','tenant-alpha')->where('product_id','product-a')->value('available_quantity') === 10, 'replay stock unchanged');

try {
    $catalog->prepare(new CatalogPreparationCommand(
        'catalog-operation-alpha-0001',
        ProductId::fromString('product-a'),
        'Conflicting Name',
        Money::fromAtomicUnits(3100, 'IDR', 0),
        true,
        'correlation-catalog-alpha-conflict',
    ));
    $assert(false, 'conflicting replay accepted');
} catch (PosTransactionViolation) {}

$created = $catalog->prepare(new CatalogPreparationCommand(
    'catalog-operation-alpha-0002',
    ProductId::fromString('product-new'),
    'Product New',
    Money::fromAtomicUnits(1500, 'IDR', 0),
    true,
    'correlation-catalog-alpha-0002',
));
$newRow = $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id','tenant-alpha')->where('outlet_id','outlet-alpha')->where('product_id','product-new')->first();
$assert($created->sellable() && $newRow !== null, 'new product created');
$assert((int) $newRow->available_quantity === 0, 'new product server-owned zero stock');

$catalog->prepare(new CatalogPreparationCommand(
    'catalog-operation-alpha-0003',
    ProductId::fromString('product-shared'),
    'Alpha Shared',
    Money::fromAtomicUnits(1200, 'IDR', 0),
    true,
    'correlation-catalog-alpha-0003',
));
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id','tenant-beta')->where('outlet_id','outlet-beta')->where('product_id','product-shared')
    ->value('unit_price_atomic') === 9000, 'other tenant catalog unchanged');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id','tenant-alpha')->where('outlet_id','outlet-alpha')->where('product_id','product-shared')
    ->value('unit_price_atomic') === 1200, 'same product reference remains tenant scoped');

$catalog->prepare(new CatalogPreparationCommand(
    'catalog-operation-alpha-0004',
    ProductId::fromString('product-a'),
    'Product A Sellable',
    Money::fromAtomicUnits(3200, 'IDR', 0),
    true,
    'correlation-catalog-alpha-0004',
));
$sales = $app->make(CompleteSale::class);
$receipt = $sales->complete(new SaleCommand(
    'sale-operation-alpha-after-catalog',
    Cart::fromLines([new CartLine(ProductId::fromString('product-a'), 1)]),
    TenderCategory::MANUAL_EXTERNAL,
    Money::fromAtomicUnits(3200, 'IDR', 0),
    'correlation-sale-alpha-after-catalog',
));
$assert($receipt->total()->atomicUnits() === 3200, 'sale uses prepared server-owned price');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('tenant_id','tenant-alpha')->where('product_id','product-a')->value('available_quantity') === 9, 'sale owns stock decrement');

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
        'correlation-catalog-alpha-denied',
    ));
    $assert(false, 'no-permission actor prepared catalog');
} catch (DurableAuthorizationViolation) {}

$assert(Route::has('pos.catalog.prepare'), 'armed catalog route absent');
$route = Route::getRoutes()->getByName('pos.catalog.prepare');
$middleware = $route?->gatherMiddleware() ?? [];
$assert(in_array('session.active', $middleware, true), 'active-session middleware missing');
$assert(in_array(RequirePosSessionContextMiddleware::class, $middleware, true), 'POS context middleware missing');

$manager->disconnect('s47_catalog');
$manager->purge('s47_catalog');
@unlink($db);
@rmdir($workspace);

echo "Sprint47 JRN-004 durable catalog preparation regression passed.\n";
