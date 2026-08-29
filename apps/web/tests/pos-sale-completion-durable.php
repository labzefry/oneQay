<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\CompleteSale;
use App\Application\Pos\PosTransactionViolation;
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
        throw new RuntimeException('Sprint46 JRN-006 durable sale regression failed: '.$case);
    }
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s46-pos-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'pos.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 's46_pos');
$app['config']->set('database.connections.s46_pos', [
    'driver' => 'sqlite', 'url' => null, 'database' => $db, 'prefix' => '',
    'foreign_key_constraints' => true, 'busy_timeout' => null, 'journal_mode' => null, 'synchronous' => null,
]);
$app['config']->set('database.oneqay_persistence_enabled', true);
$app['config']->set('oneqay.runtime_class', 'ci');
$app['config']->set('oneqay.session_control.enabled', true);
$app['config']->set('oneqay.pos_sale_completion.enabled', true);

$manager = $app->make('db');
$manager->purge('s46_pos');
$manager->setDefaultConnection('s46_pos');
$connection = $manager->connection('s46_pos');
$connection->getPdo();

$migrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($migrations);
$assert(count($migrations) === 16, 'migration set count');
for ($index = 1; $index <= 16; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $assert(count(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix))) === 1, 'migration #'.$index.' exact');
}
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
foreach ([['tenant-alpha','cashier-alpha'],['tenant-alpha','no-permission-alpha'],['tenant-beta','cashier-beta']] as [$tenant,$identity]) {
    $connection->table('oneqay_identities')->insert(['tenant_id'=>$tenant,'id'=>$identity]);
}
foreach ([['tenant-alpha','organization-alpha'],['tenant-beta','organization-beta']] as [$tenant,$org]) {
    $connection->table('oneqay_organizations')->insert(['tenant_id'=>$tenant,'id'=>$org]);
}
foreach ([['tenant-alpha','cashier-alpha','organization-alpha'],['tenant-alpha','no-permission-alpha','organization-alpha'],['tenant-beta','cashier-beta','organization-beta']] as [$tenant,$identity,$org]) {
    $connection->table('oneqay_identity_organizations')->insert(['tenant_id'=>$tenant,'identity_id'=>$identity,'organization_id'=>$org]);
}
foreach ([['tenant-alpha','outlet-alpha','organization-alpha'],['tenant-beta','outlet-beta','organization-beta']] as [$tenant,$outlet,$org]) {
    $connection->table('oneqay_outlets')->insert(['tenant_id'=>$tenant,'id'=>$outlet,'organization_id'=>$org]);
}
foreach ([['tenant-alpha','device-alpha','organization-alpha','outlet-alpha'],['tenant-beta','device-beta','organization-beta','outlet-beta']] as [$tenant,$device,$org,$outlet]) {
    $connection->table('oneqay_devices')->insert(['tenant_id'=>$tenant,'id'=>$device,'organization_id'=>$org,'outlet_id'=>$outlet]);
}

$connection->table('oneqay_roles')->insert([
    ['tenant_id'=>'tenant-alpha','id'=>'cashier-role'],
    ['tenant_id'=>'tenant-beta','id'=>'cashier-role'],
]);
$connection->table('oneqay_role_permissions')->insert([
    ['tenant_id'=>'tenant-alpha','role_id'=>'cashier-role','permission_id'=>PosPermission::COMPLETE_SALE],
    ['tenant_id'=>'tenant-beta','role_id'=>'cashier-role','permission_id'=>PosPermission::COMPLETE_SALE],
]);
$connection->table('oneqay_outlet_role_assignments')->insert([
    ['tenant_id'=>'tenant-alpha','identity_id'=>'cashier-alpha','organization_id'=>'organization-alpha','outlet_id'=>'outlet-alpha','role_id'=>'cashier-role'],
    ['tenant_id'=>'tenant-beta','identity_id'=>'cashier-beta','organization_id'=>'organization-beta','outlet_id'=>'outlet-beta','role_id'=>'cashier-role'],
]);

$connection->table('oneqay_pos_sale_catalog_items')->insert([
    ['tenant_id'=>'tenant-alpha','outlet_id'=>'outlet-alpha','product_id'=>'product-a','display_name'=>'Product A','unit_price_atomic'=>2500,'currency'=>'IDR','currency_scale'=>0,'available_quantity'=>10,'active'=>true],
    ['tenant_id'=>'tenant-beta','outlet_id'=>'outlet-beta','product_id'=>'product-b','display_name'=>'Product B','unit_price_atomic'=>9000,'currency'=>'IDR','currency_scale'=>0,'available_quantity'=>8,'active'=>true],
]);

$app->forgetScopedInstances();
$contexts = $app->make(OrganizationalContextStore::class);
$contexts->setVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('cashier-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha'),
));
$sales = $app->make(CompleteSale::class);

$command = new SaleCommand(
    'operation-alpha-0001',
    Cart::fromLines([new CartLine(ProductId::fromString('product-a'), 2)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(6000, 'IDR', 0),
    'correlation-alpha-0001',
);
$receipt = $sales->complete($command);
$assert($receipt->total()->atomicUnits() === 5000, 'server-owned price total');
$assert($receipt->changeAmount()->atomicUnits() === 1000, 'cash change');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('tenant_id','tenant-alpha')->where('product_id','product-a')->value('available_quantity') === 8, 'stock decrement');
$assert($connection->table('oneqay_pos_sales')->where('tenant_id','tenant-alpha')->count() === 1, 'sale persisted once');

$replay = $sales->complete($command);
$assert($replay->saleId() === $receipt->saleId(), 'exact replay');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('tenant_id','tenant-alpha')->where('product_id','product-a')->value('available_quantity') === 8, 'replay no second decrement');

try {
    $sales->complete(new SaleCommand(
        'operation-alpha-0001',
        Cart::fromLines([new CartLine(ProductId::fromString('product-a'), 1)]),
        TenderCategory::CASH,
        Money::fromAtomicUnits(3000, 'IDR', 0),
        'correlation-alpha-conflict',
    ));
    $assert(false, 'conflicting replay accepted');
} catch (PosTransactionViolation) {}

try {
    $sales->complete(new SaleCommand(
        'operation-alpha-0002',
        Cart::fromLines([new CartLine(ProductId::fromString('product-b'), 1)]),
        TenderCategory::MANUAL_EXTERNAL,
        Money::fromAtomicUnits(9000, 'IDR', 0),
        'correlation-alpha-cross-tenant',
    ));
    $assert(false, 'cross-tenant catalog borrowing accepted');
} catch (PosTransactionViolation) {}

$contexts->setVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('no-permission-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha'),
));
try {
    $sales->complete(new SaleCommand(
        'operation-alpha-0003',
        Cart::fromLines([new CartLine(ProductId::fromString('product-a'), 1)]),
        TenderCategory::CASH,
        Money::fromAtomicUnits(2500, 'IDR', 0),
        'correlation-alpha-denied',
    ));
    $assert(false, 'no-permission actor completed sale');
} catch (DurableAuthorizationViolation) {}

$assert(Route::has('pos.sales.complete'), 'armed route absent');
$route = Route::getRoutes()->getByName('pos.sales.complete');
$assert($route !== null && in_array('session.active', $route->gatherMiddleware(), true), 'active-session middleware missing');

$manager->disconnect('s46_pos');
$manager->purge('s46_pos');
@unlink($db);
@rmdir($workspace);

echo "Sprint46 JRN-006 durable sale completion regression passed.\n";
