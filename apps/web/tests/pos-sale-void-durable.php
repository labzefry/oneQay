<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\CompleteSale;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleCommand;
use App\Application\Pos\SaleVoidCommand;
use App\Application\Pos\VoidSale;
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
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('v', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_POS_SALE_COMPLETION_ENABLED' => 'true',
    'ONEQAY_POS_SALE_VOID_ENABLED' => 'true',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
$app->instance('request', Request::create('/'));
$app->make(Kernel::class)->bootstrap();

$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Sprint50 JRN-007 completed-sale void regression failed: '.$case);
    }
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s50-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'pos.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 's50_pos');
$app['config']->set('database.connections.s50_pos', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $db,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);
$app['config']->set('database.oneqay_persistence_enabled', true);
$app['config']->set('oneqay.runtime_class', 'ci');
$app['config']->set('oneqay.session_control.enabled', true);
$app['config']->set('oneqay.pos_sale_completion.enabled', true);
$app['config']->set('oneqay.pos_sale_void.enabled', true);

$manager = $app->make('db');
$manager->purge('s50_pos');
$manager->setDefaultConnection('s50_pos');
$connection = $manager->connection('s50_pos');
$connection->getPdo();

$migrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($migrations);
$assert(count($migrations) === 19, 'migration set count');
for ($index = 1; $index <= 19; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $assert(
        count(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix))) === 1,
        'migration #'.$index.' exact',
    );
}
foreach ($migrations as $migrationFile) {
    (require __DIR__.'/../database/migrations/'.$migrationFile)->up();
}

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
foreach ([
    ['tenant-alpha', 'cashier-alpha'],
    ['tenant-alpha', 'manager-alpha'],
    ['tenant-alpha', 'no-permission-alpha'],
    ['tenant-beta', 'manager-beta'],
] as [$tenant, $identity]) {
    $connection->table('oneqay_identities')->insert(['tenant_id' => $tenant, 'id' => $identity]);
}
foreach ([
    ['tenant-alpha', 'organization-alpha'],
    ['tenant-alpha', 'organization-alt'],
    ['tenant-beta', 'organization-beta'],
] as [$tenant, $organization]) {
    $connection->table('oneqay_organizations')->insert(['tenant_id' => $tenant, 'id' => $organization]);
}
foreach ([
    ['tenant-alpha', 'cashier-alpha', 'organization-alpha'],
    ['tenant-alpha', 'manager-alpha', 'organization-alpha'],
    ['tenant-alpha', 'manager-alpha', 'organization-alt'],
    ['tenant-alpha', 'no-permission-alpha', 'organization-alpha'],
    ['tenant-beta', 'manager-beta', 'organization-beta'],
] as [$tenant, $identity, $organization]) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => $tenant,
        'identity_id' => $identity,
        'organization_id' => $organization,
    ]);
}
foreach ([
    ['tenant-alpha', 'outlet-alpha', 'organization-alpha'],
    ['tenant-alpha', 'outlet-other', 'organization-alpha'],
    ['tenant-alpha', 'outlet-org-alt', 'organization-alt'],
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
    ['tenant-alpha', 'device-other', 'organization-alpha', 'outlet-alpha'],
    ['tenant-alpha', 'device-outlet-other', 'organization-alpha', 'outlet-other'],
    ['tenant-alpha', 'device-org-alt', 'organization-alt', 'outlet-org-alt'],
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
    ['tenant_id' => 'tenant-alpha', 'id' => 'cashier-role'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'void-role'],
    ['tenant_id' => 'tenant-beta', 'id' => 'void-role'],
]);
$connection->table('oneqay_role_permissions')->insert([
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'cashier-role', 'permission_id' => PosPermission::COMPLETE_SALE],
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'void-role', 'permission_id' => PosPermission::VOID_SALE],
    ['tenant_id' => 'tenant-beta', 'role_id' => 'void-role', 'permission_id' => PosPermission::VOID_SALE],
]);
$connection->table('oneqay_outlet_role_assignments')->insert([
    ['tenant_id'=>'tenant-alpha','identity_id'=>'cashier-alpha','organization_id'=>'organization-alpha','outlet_id'=>'outlet-alpha','role_id'=>'cashier-role'],
    ['tenant_id'=>'tenant-alpha','identity_id'=>'manager-alpha','organization_id'=>'organization-alpha','outlet_id'=>'outlet-alpha','role_id'=>'void-role'],
    ['tenant_id'=>'tenant-alpha','identity_id'=>'manager-alpha','organization_id'=>'organization-alpha','outlet_id'=>'outlet-other','role_id'=>'void-role'],
    ['tenant_id'=>'tenant-alpha','identity_id'=>'manager-alpha','organization_id'=>'organization-alt','outlet_id'=>'outlet-org-alt','role_id'=>'void-role'],
    ['tenant_id'=>'tenant-beta','identity_id'=>'manager-beta','organization_id'=>'organization-beta','outlet_id'=>'outlet-beta','role_id'=>'void-role'],
]);

foreach ([
    ['product-a', 2500],
    ['product-b', 1500],
    ['product-c', 3000],
    ['product-d', 1000],
] as [$product, $price]) {
    $connection->table('oneqay_pos_sale_catalog_items')->insert([
        'tenant_id' => 'tenant-alpha',
        'outlet_id' => 'outlet-alpha',
        'product_id' => $product,
        'display_name' => strtoupper($product),
        'unit_price_atomic' => $price,
        'currency' => 'IDR',
        'currency_scale' => 0,
        'available_quantity' => 20,
        'active' => true,
    ]);
}

$connection->table('oneqay_pos_shifts')->insert([
    'tenant_id' => 'tenant-alpha',
    'shift_id' => str_repeat('a', 32),
    'operation_id' => 'shift-source-alpha',
    'payload_fingerprint' => str_repeat('b', 64),
    'actor_identity_id' => 'cashier-alpha',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-alpha',
    'active_slot' => 1,
    'correlation_id' => 'correlation-shift-alpha',
    'opened_at_unix' => 1000,
]);

$app->forgetScopedInstances();
$contexts = $app->make(OrganizationalContextStore::class);
$setContext = static function (
    string $identity,
    string $tenant,
    string $organization,
    string $outlet,
    string $device,
) use ($contexts): void {
    $contexts->setVerified(new VerifiedOrganizationalContext(
        PlatformIdentityId::fromString($identity),
        TenantId::fromString($tenant),
        OrganizationId::fromString($organization),
        OutletId::fromString($outlet),
        DeviceId::fromString($device),
    ));
};

$setContext('cashier-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha');
$sales = $app->make(CompleteSale::class);
$voids = $app->make(VoidSale::class);

$successCommand = new SaleCommand(
    'sale-source-success',
    Cart::fromLines([new CartLine(ProductId::fromString('product-a'), 2)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(6000, 'IDR', 0),
    'correlation-sale-success',
);
$otherCommand = new SaleCommand(
    'sale-source-other',
    Cart::fromLines([new CartLine(ProductId::fromString('product-c'), 1)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(3000, 'IDR', 0),
    'correlation-sale-other',
);
$missingCatalogCommand = new SaleCommand(
    'sale-source-missing-catalog',
    Cart::fromLines([
        new CartLine(ProductId::fromString('product-a'), 1),
        new CartLine(ProductId::fromString('product-b'), 1),
    ]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(5000, 'IDR', 0),
    'correlation-sale-missing-catalog',
);
$overflowCommand = new SaleCommand(
    'sale-source-overflow',
    Cart::fromLines([new CartLine(ProductId::fromString('product-d'), 1)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(1000, 'IDR', 0),
    'correlation-sale-overflow',
);

$successReceipt = $sales->complete($successCommand);
$otherReceipt = $sales->complete($otherCommand);
$missingCatalogReceipt = $sales->complete($missingCatalogCommand);
$overflowReceipt = $sales->complete($overflowCommand);

$assert($successReceipt->total()->atomicUnits() === 5000, 'success sale total');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id','product-a')->value('available_quantity') === 17, 'pre-void product-a quantity');

$originalSale = (array) $connection->table('oneqay_pos_sales')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $successReceipt->saleId())
    ->first();
$originalLines = array_map(
    static fn (object $row): array => (array) $row,
    $connection->table('oneqay_pos_sale_lines')
        ->where('tenant_id', 'tenant-alpha')
        ->where('sale_id', $successReceipt->saleId())
        ->orderBy('line_no')
        ->get()
        ->all(),
);

$connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-alpha')
    ->update(['active_slot' => null]);
$shiftBeforeVoid = (array) $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-alpha')
    ->first();

$setContext('no-permission-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-other');
try {
    $voids->execute(
        new SaleVoidCommand('void-denied-operation', $successReceipt->saleId()),
        'correlation-void-denied',
    );
    $assert(false, 'no-permission actor voided sale');
} catch (DurableAuthorizationViolation) {}
$assert($connection->table('oneqay_pos_sale_voids')->count() === 0, 'authorization denial mutated void state');

$setContext('manager-beta', 'tenant-beta', 'organization-beta', 'outlet-beta', 'device-beta');
try {
    $voids->execute(
        new SaleVoidCommand('void-cross-tenant', $successReceipt->saleId()),
        'correlation-cross-tenant',
    );
    $assert(false, 'cross-tenant target accepted');
} catch (PosTransactionViolation) {}

$setContext('manager-alpha', 'tenant-alpha', 'organization-alt', 'outlet-org-alt', 'device-org-alt');
try {
    $voids->execute(
        new SaleVoidCommand('void-cross-organization', $successReceipt->saleId()),
        'correlation-cross-organization',
    );
    $assert(false, 'cross-organization target accepted');
} catch (PosTransactionViolation) {}

$setContext('manager-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-other', 'device-outlet-other');
try {
    $voids->execute(
        new SaleVoidCommand('void-cross-outlet', $successReceipt->saleId()),
        'correlation-cross-outlet',
    );
    $assert(false, 'cross-outlet target accepted');
} catch (PosTransactionViolation) {}

$setContext('manager-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-other');
try {
    $voids->execute(
        new SaleVoidCommand('void-missing-sale', 'sale-'.str_repeat('f', 24)),
        'correlation-missing-sale',
    );
    $assert(false, 'missing sale accepted');
} catch (PosTransactionViolation) {}

$connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-alpha')
    ->where('outlet_id', 'outlet-alpha')
    ->where('product_id', 'product-a')
    ->update(['active' => false]);

$voidCommand = new SaleVoidCommand('void-operation-success', $successReceipt->saleId());
$voidResult = $voids->execute($voidCommand, 'correlation-void-success');

$assert($voidResult->saleId() === $successReceipt->saleId(), 'void target result');
$assert($voidResult->reversedAmount()->atomicUnits() === 5000, 'reversed amount equals original applied');
$assert($voidResult->tenderCategory() === TenderCategory::CASH, 'original tender category preserved');
$assert($voidResult->evidenceMode() === 'FULL_SALE_VOID', 'bounded evidence mode');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id','product-a')->value('available_quantity') === 19, 'inactive catalog exact stock restoration');
$assert($connection->table('oneqay_pos_sale_voids')->where('sale_id',$successReceipt->saleId())->count() === 1, 'single durable void row');
$assert($connection->table('oneqay_pos_sale_events')->where('sale_id',$successReceipt->saleId())->where('event_type','VOIDED')->count() === 1, 'single VOIDED event');

$voidRow = (array) $connection->table('oneqay_pos_sale_voids')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $successReceipt->saleId())
    ->first();
$assert(($voidRow['device_id'] ?? null) === 'device-other', 'current correction device audited');
$assert(($originalSale['device_id'] ?? null) === 'device-alpha', 'original sale device preserved');

$afterSale = (array) $connection->table('oneqay_pos_sales')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $successReceipt->saleId())
    ->first();
$afterLines = array_map(
    static fn (object $row): array => (array) $row,
    $connection->table('oneqay_pos_sale_lines')
        ->where('tenant_id', 'tenant-alpha')
        ->where('sale_id', $successReceipt->saleId())
        ->orderBy('line_no')
        ->get()
        ->all(),
);
$assert($afterSale === $originalSale, 'original sale mutated');
$assert($afterLines === $originalLines, 'original sale lines mutated');
$shiftAfterVoid = (array) $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-alpha')
    ->first();
$assert($shiftAfterVoid === $shiftBeforeVoid, 'void mutated shift evidence');

$replay = $voids->execute($voidCommand, 'correlation-void-replay');
$assert($replay->voidId() === $voidResult->voidId(), 'exact replay returned original void');
$assert($replay->correlationId() === 'correlation-void-success', 'replay preserved original correlation');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id','product-a')->value('available_quantity') === 19, 'replay duplicated stock restoration');
$assert($connection->table('oneqay_pos_sale_voids')->where('sale_id',$successReceipt->saleId())->count() === 1, 'replay duplicated void row');
$assert($connection->table('oneqay_pos_sale_events')->where('sale_id',$successReceipt->saleId())->where('event_type','VOIDED')->count() === 1, 'replay duplicated VOIDED event');

try {
    $voids->execute(
        new SaleVoidCommand('void-operation-success', $otherReceipt->saleId()),
        'correlation-conflicting-operation',
    );
    $assert(false, 'conflicting operation reuse accepted');
} catch (PosTransactionViolation) {}

try {
    $voids->execute(
        new SaleVoidCommand('void-second-operation', $successReceipt->saleId()),
        'correlation-second-operation',
    );
    $assert(false, 'second operation voided already-void sale');
} catch (PosTransactionViolation) {}

$connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id','tenant-alpha')
    ->where('outlet_id','outlet-alpha')
    ->where('product_id','product-b')
    ->delete();
$productABeforeFailedRestore = (int) $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id','tenant-alpha')
    ->where('outlet_id','outlet-alpha')
    ->where('product_id','product-a')
    ->value('available_quantity');
try {
    $voids->execute(
        new SaleVoidCommand('void-missing-catalog-operation', $missingCatalogReceipt->saleId()),
        'correlation-missing-catalog-void',
    );
    $assert(false, 'missing catalog correction accepted');
} catch (PosTransactionViolation) {}
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id','tenant-alpha')
        ->where('outlet_id','outlet-alpha')
        ->where('product_id','product-a')
        ->value('available_quantity') === $productABeforeFailedRestore,
    'missing catalog caused partial stock restoration',
);
$assert($connection->table('oneqay_pos_sale_voids')->where('sale_id',$missingCatalogReceipt->saleId())->count() === 0, 'missing catalog persisted correction');

$connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id','tenant-alpha')
    ->where('outlet_id','outlet-alpha')
    ->where('product_id','product-d')
    ->update(['available_quantity' => PHP_INT_MAX]);
try {
    $voids->execute(
        new SaleVoidCommand('void-overflow-operation', $overflowReceipt->saleId()),
        'correlation-overflow-void',
    );
    $assert(false, 'overflow correction accepted');
} catch (PosTransactionViolation) {}
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id','tenant-alpha')
        ->where('outlet_id','outlet-alpha')
        ->where('product_id','product-d')
        ->value('available_quantity') === PHP_INT_MAX,
    'overflow mutated stock',
);
$assert($connection->table('oneqay_pos_sale_voids')->where('sale_id',$overflowReceipt->saleId())->count() === 0, 'overflow persisted correction');

$setContext('cashier-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha');
$saleReplayAfterVoid = $sales->complete($successCommand);
$assert($saleReplayAfterVoid->saleId() === $successReceipt->saleId(), 'JRN-006 replay changed after void');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id','product-a')->value('available_quantity') === 19, 'JRN-006 replay mutated restored stock');

try {
    $sales->complete(new SaleCommand(
        'sale-fresh-after-shift-close',
        Cart::fromLines([new CartLine(ProductId::fromString('product-c'), 1)]),
        TenderCategory::CASH,
        Money::fromAtomicUnits(3000, 'IDR', 0),
        'correlation-fresh-after-shift-close',
    ));
    $assert(false, 'fresh JRN-006 sale bypassed inactive shift');
} catch (PosTransactionViolation) {}

$assert(Route::has('pos.sales.void'), 'armed void route absent');
$route = Route::getRoutes()->getByName('pos.sales.void');
$assert($route !== null && in_array('session.active', $route->gatherMiddleware(), true), 'void active-session middleware missing');
$assert($route !== null && in_array(App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware::class, $route->gatherMiddleware(), true), 'void verified-context middleware missing');

$manager->disconnect('s50_pos');
$manager->purge('s50_pos');
@unlink($db);
@rmdir($workspace);

echo "Sprint50 JRN-007 completed-sale void regression passed.\n";
