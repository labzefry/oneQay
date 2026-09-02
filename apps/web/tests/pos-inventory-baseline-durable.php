<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\CatalogPreparationCommand;
use App\Application\Pos\CompleteSale;
use App\Application\Pos\EstablishInventoryBaseline;
use App\Application\Pos\InventoryBaselineCommand;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\PrepareCatalogItem;
use App\Application\Pos\SaleCommand;
use App\Application\Pos\SaleVoidCommand;
use App\Application\Pos\VoidSale;
use App\Delivery\Http\Pos\PosInventoryBaselineController;
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
use App\Infrastructure\Pos\LaravelInventoryBaselineRepository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('i', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_POS_SALE_COMPLETION_ENABLED' => 'true',
    'ONEQAY_POS_SALE_VOID_ENABLED' => 'true',
    'ONEQAY_POS_CATALOG_PREPARATION_ENABLED' => 'true',
    'ONEQAY_POS_INVENTORY_BASELINE_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint51 JRN-008 inventory baseline regression failed: '.$case);
    }
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s51-inventory-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'inventory.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 's51_inventory');
$app['config']->set('database.connections.s51_inventory', [
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
$app['config']->set('oneqay.pos_catalog_preparation.enabled', true);
$app['config']->set('oneqay.pos_inventory_baseline.enabled', true);

$manager = $app->make('db');
$manager->purge('s51_inventory');
$manager->setDefaultConnection('s51_inventory');
$connection = $manager->connection('s51_inventory');
$connection->getPdo();

$migrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($migrations);
$assert(count($migrations) === 20, 'migration set count');
for ($index = 1; $index <= 20; $index++) {
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
    ['tenant-alpha', 'inventory-admin-alpha'],
    ['tenant-alpha', 'no-permission-alpha'],
    ['tenant-beta', 'inventory-admin-beta'],
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
    ['tenant-alpha', 'inventory-admin-alpha', 'organization-alpha'],
    ['tenant-alpha', 'no-permission-alpha', 'organization-alpha'],
    ['tenant-beta', 'inventory-admin-beta', 'organization-beta'],
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
    ['tenant-alpha', 'device-other', 'organization-alpha', 'outlet-other'],
    ['tenant-beta', 'device-beta', 'organization-beta', 'outlet-beta'],
] as [$tenant, $device, $organization, $outlet]) {
    $connection->table('oneqay_devices')->insert([
        'tenant_id' => $tenant,
        'id' => $device,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
    ]);
}

$connection->table('oneqay_outlet_access_grants')->insert([
    [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => 'inventory-admin-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => 'outlet-alpha',
    ],
    [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => 'inventory-admin-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => 'outlet-other',
    ],
    [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => 'no-permission-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => 'outlet-alpha',
    ],
    [
        'tenant_id' => 'tenant-beta',
        'identity_id' => 'inventory-admin-beta',
        'organization_id' => 'organization-beta',
        'outlet_id' => 'outlet-beta',
    ],
]);

$connection->table('oneqay_roles')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'inventory-role'],
    ['tenant_id' => 'tenant-beta', 'id' => 'inventory-role'],
]);

$assert(
    $connection->table('oneqay_role_permissions')
        ->where('permission_id', PosPermission::INVENTORY_BASELINE)
        ->count() === 0,
    'inventory permission granted by default',
);

foreach ([
    ['tenant-alpha', PosPermission::INVENTORY_BASELINE],
    ['tenant-alpha', PosPermission::PREPARE_CATALOG],
    ['tenant-alpha', PosPermission::COMPLETE_SALE],
    ['tenant-alpha', PosPermission::VOID_SALE],
    ['tenant-beta', PosPermission::INVENTORY_BASELINE],
] as [$tenant, $permission]) {
    $connection->table('oneqay_role_permissions')->insert([
        'tenant_id' => $tenant,
        'role_id' => 'inventory-role',
        'permission_id' => $permission,
    ]);
}

$connection->table('oneqay_outlet_role_assignments')->insert([
    [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => 'inventory-admin-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => 'outlet-alpha',
        'role_id' => 'inventory-role',
    ],
    [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => 'inventory-admin-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => 'outlet-other',
        'role_id' => 'inventory-role',
    ],
    [
        'tenant_id' => 'tenant-beta',
        'identity_id' => 'inventory-admin-beta',
        'organization_id' => 'organization-beta',
        'outlet_id' => 'outlet-beta',
        'role_id' => 'inventory-role',
    ],
]);

foreach ([
    ['tenant-alpha', 'outlet-alpha', 'product-positive', 0, true],
    ['tenant-alpha', 'outlet-alpha', 'product-zero', 0, true],
    ['tenant-alpha', 'outlet-alpha', 'product-replay', 0, true],
    ['tenant-alpha', 'outlet-alpha', 'product-inactive', 0, false],
    ['tenant-alpha', 'outlet-alpha', 'product-nonzero', 5, true],
    ['tenant-alpha', 'outlet-alpha', 'product-sale-history', 1, true],
    ['tenant-alpha', 'outlet-alpha', 'product-denied', 0, true],
    ['tenant-alpha', 'outlet-other', 'product-other', 0, true],
    ['tenant-beta', 'outlet-beta', 'product-beta', 0, true],
] as [$tenant, $outlet, $product, $quantity, $active]) {
    $connection->table('oneqay_pos_sale_catalog_items')->insert([
        'tenant_id' => $tenant,
        'outlet_id' => $outlet,
        'product_id' => $product,
        'display_name' => strtoupper($product),
        'unit_price_atomic' => 1000,
        'currency' => 'IDR',
        'currency_scale' => 0,
        'available_quantity' => $quantity,
        'active' => $active,
    ]);
}

$connection->table('oneqay_pos_shifts')->insert([
    'tenant_id' => 'tenant-alpha',
    'shift_id' => str_repeat('5', 32),
    'operation_id' => 'shift-inventory-alpha',
    'payload_fingerprint' => str_repeat('6', 64),
    'actor_identity_id' => 'inventory-admin-alpha',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-alpha',
    'active_slot' => 1,
    'correlation_id' => 'correlation-shift-inventory',
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

$setContext('inventory-admin-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha');

$inventory = $app->make(EstablishInventoryBaseline::class);
$catalog = $app->make(PrepareCatalogItem::class);
$sales = $app->make(CompleteSale::class);
$voids = $app->make(VoidSale::class);

$shiftBeforeBaseline = (array) $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-inventory-alpha')
    ->first();

$positiveCommand = new InventoryBaselineCommand(
    'inventory-baseline-positive-0001',
    ProductId::fromString('product-positive'),
    8,
);
$positive = $inventory->establish($positiveCommand, 'correlation-inventory-positive');
$assert(strlen($positive->baselineId()) === 32, 'positive baseline id length');
$assert($positive->openingQuantity() === 8, 'positive baseline result quantity');
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('outlet_id', 'outlet-alpha')
        ->where('product_id', 'product-positive')
        ->value('available_quantity') === 8,
    'positive baseline current quantity',
);

$positiveRow = (array) $connection->table('oneqay_pos_inventory_baselines')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'inventory-baseline-positive-0001')
    ->first();
$assert(($positiveRow['actor_identity_id'] ?? null) === 'inventory-admin-alpha', 'verified actor evidence');
$assert(($positiveRow['outlet_id'] ?? null) === 'outlet-alpha', 'verified outlet evidence');
$assert((int) ($positiveRow['before_available_quantity'] ?? -1) === 0, 'before quantity evidence');
$assert((int) ($positiveRow['opening_quantity'] ?? -1) === 8, 'opening quantity evidence');
$assert(($positiveRow['correlation_id'] ?? null) === 'correlation-inventory-positive', 'correlation evidence');

$shiftAfterBaseline = (array) $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-inventory-alpha')
    ->first();
$assert($shiftAfterBaseline === $shiftBeforeBaseline, 'baseline mutated shift state');

$positiveReplay = $inventory->establish($positiveCommand, 'correlation-inventory-positive-replay');
$assert($positiveReplay->baselineId() === $positive->baselineId(), 'exact replay baseline identity');
$assert(
    $connection->table('oneqay_pos_inventory_baselines')
        ->where('tenant_id', 'tenant-alpha')
        ->where('operation_id', 'inventory-baseline-positive-0001')
        ->count() === 1,
    'exact replay duplicate baseline row',
);

try {
    $inventory->establish(
        new InventoryBaselineCommand(
            'inventory-baseline-positive-0001',
            ProductId::fromString('product-zero'),
            0,
        ),
        'correlation-inventory-conflict',
    );
    $assert(false, 'conflicting operation reuse accepted');
} catch (PosTransactionViolation) {}

$zeroCommand = new InventoryBaselineCommand(
    'inventory-baseline-zero-0001',
    ProductId::fromString('product-zero'),
    0,
);
$zero = $inventory->establish($zeroCommand, 'correlation-inventory-zero');
$assert($zero->openingQuantity() === 0, 'zero baseline result');
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('outlet_id', 'outlet-alpha')
        ->where('product_id', 'product-zero')
        ->value('available_quantity') === 0,
    'zero baseline changed current quantity',
);
$assert(
    $connection->table('oneqay_pos_inventory_baselines')
        ->where('tenant_id', 'tenant-alpha')
        ->where('outlet_id', 'outlet-alpha')
        ->where('product_id', 'product-zero')
        ->count() === 1,
    'zero baseline not durably established',
);

try {
    $inventory->establish(
        new InventoryBaselineCommand(
            'inventory-baseline-zero-second',
            ProductId::fromString('product-zero'),
            0,
        ),
        'correlation-inventory-zero-second',
    );
    $assert(false, 'second zero baseline accepted');
} catch (PosTransactionViolation) {}

$inactive = $inventory->establish(
    new InventoryBaselineCommand(
        'inventory-baseline-inactive',
        ProductId::fromString('product-inactive'),
        2,
    ),
    'correlation-inventory-inactive',
);
$assert($inactive->openingQuantity() === 2, 'inactive baseline quantity');
$inactiveCatalog = $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-alpha')
    ->where('outlet_id', 'outlet-alpha')
    ->where('product_id', 'product-inactive')
    ->first();
$assert($inactiveCatalog !== null && ! (bool) $inactiveCatalog->active, 'baseline activated inactive catalog');

try {
    $inventory->establish(
        new InventoryBaselineCommand(
            'inventory-baseline-nonzero',
            ProductId::fromString('product-nonzero'),
            3,
        ),
        'correlation-inventory-nonzero',
    );
    $assert(false, 'non-zero pre-baseline state accepted');
} catch (PosTransactionViolation) {}

try {
    $inventory->establish(
        new InventoryBaselineCommand(
            'inventory-baseline-missing',
            ProductId::fromString('product-missing'),
            3,
        ),
        'correlation-inventory-missing',
    );
    $assert(false, 'missing catalog accepted');
} catch (PosTransactionViolation) {}

$setContext('no-permission-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha');
try {
    $inventory->establish(
        new InventoryBaselineCommand(
            'inventory-baseline-denied',
            ProductId::fromString('product-denied'),
            3,
        ),
        'correlation-inventory-denied',
    );
    $assert(false, 'no-permission actor established baseline');
} catch (DurableAuthorizationViolation) {}

$setContext('inventory-admin-beta', 'tenant-beta', 'organization-beta', 'outlet-beta', 'device-beta');
try {
    $inventory->establish(
        new InventoryBaselineCommand(
            'inventory-baseline-cross-tenant',
            ProductId::fromString('product-positive'),
            3,
        ),
        'correlation-inventory-cross-tenant',
    );
    $assert(false, 'cross-tenant product resolved');
} catch (PosTransactionViolation) {}

$setContext('inventory-admin-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-other', 'device-other');
try {
    $inventory->establish(
        new InventoryBaselineCommand(
            'inventory-baseline-cross-outlet',
            ProductId::fromString('product-positive'),
            3,
        ),
        'correlation-inventory-cross-outlet',
    );
    $assert(false, 'cross-outlet product resolved');
} catch (PosTransactionViolation) {}

$setContext('inventory-admin-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha');

$saleHistory = $sales->complete(new SaleCommand(
    'inventory-sale-history-source',
    Cart::fromLines([new CartLine(ProductId::fromString('product-sale-history'), 1)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(1000, 'IDR', 0),
    'correlation-inventory-sale-history',
));
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('product_id', 'product-sale-history')
        ->value('available_quantity') === 0,
    'sale-history precondition did not reach zero',
);
try {
    $inventory->establish(
        new InventoryBaselineCommand(
            'inventory-baseline-after-sale',
            ProductId::fromString('product-sale-history'),
            9,
        ),
        'correlation-inventory-after-sale',
    );
    $assert(false, 'prior-sale-history product re-baselined');
} catch (PosTransactionViolation) {}

$replayCommand = new InventoryBaselineCommand(
    'inventory-baseline-replay-movement',
    ProductId::fromString('product-replay'),
    4,
);
$replayBaseline = $inventory->establish($replayCommand, 'correlation-inventory-replay-movement');
$replayEvidenceBefore = (array) $connection->table('oneqay_pos_inventory_baselines')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'inventory-baseline-replay-movement')
    ->first();

$movementSale = $sales->complete(new SaleCommand(
    'inventory-sale-after-baseline',
    Cart::fromLines([new CartLine(ProductId::fromString('product-replay'), 1)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(1000, 'IDR', 0),
    'correlation-inventory-sale-after-baseline',
));
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('product_id', 'product-replay')
        ->value('available_quantity') === 3,
    'JRN-006 did not decrement established baseline',
);

$movementReplay = $inventory->establish($replayCommand, 'correlation-inventory-replay-after-sale');
$assert($movementReplay->baselineId() === $replayBaseline->baselineId(), 'replay after movement identity changed');
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('product_id', 'product-replay')
        ->value('available_quantity') === 3,
    'baseline replay rewrote post-sale quantity',
);

$voidResult = $voids->execute(
    new SaleVoidCommand('inventory-void-after-baseline', $movementSale->saleId()),
    'correlation-inventory-void-after-baseline',
);
$assert($voidResult->saleId() === $movementSale->saleId(), 'JRN-007 target changed');
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('product_id', 'product-replay')
        ->value('available_quantity') === 4,
    'JRN-007 did not restore exact baseline-consumed quantity',
);
$replayEvidenceAfter = (array) $connection->table('oneqay_pos_inventory_baselines')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'inventory-baseline-replay-movement')
    ->first();
$assert($replayEvidenceAfter === $replayEvidenceBefore, 'sale/void rewrote baseline evidence');

$catalogBaselineBefore = (array) $connection->table('oneqay_pos_inventory_baselines')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'inventory-baseline-positive-0001')
    ->first();
$catalog->prepare(new CatalogPreparationCommand(
    'inventory-catalog-after-baseline',
    ProductId::fromString('product-positive'),
    'Product Positive Repriced',
    Money::fromAtomicUnits(2500, 'IDR', 0),
    false,
), 'correlation-inventory-catalog-after-baseline');
$positiveCatalogAfter = $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-alpha')
    ->where('outlet_id', 'outlet-alpha')
    ->where('product_id', 'product-positive')
    ->first();
$assert($positiveCatalogAfter !== null && (int) $positiveCatalogAfter->available_quantity === 8, 'JRN-004 changed baselined quantity');
$assert($positiveCatalogAfter !== null && (int) $positiveCatalogAfter->unit_price_atomic === 2500, 'JRN-004 repricing unavailable');
$assert($positiveCatalogAfter !== null && ! (bool) $positiveCatalogAfter->active, 'JRN-004 sellability mutation unavailable');
$catalogBaselineAfter = (array) $connection->table('oneqay_pos_inventory_baselines')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'inventory-baseline-positive-0001')
    ->first();
$assert($catalogBaselineAfter === $catalogBaselineBefore, 'JRN-004 rewrote baseline evidence');

try {
    new InventoryBaselineCommand(
        'inventory-baseline-negative',
        ProductId::fromString('product-denied'),
        -1,
    );
    $assert(false, 'negative opening quantity accepted');
} catch (InvalidArgumentException) {}

$constructor = new ReflectionMethod(InventoryBaselineCommand::class, '__construct');
$constructorParameters = array_map(
    static fn (ReflectionParameter $parameter): string => $parameter->getName(),
    $constructor->getParameters(),
);
$assert(
    $constructorParameters === ['operationId', 'productId', 'openingQuantity'],
    'command accepts authority or unrelated business state',
);

$controller = new PosInventoryBaselineController($inventory);
$invalidRequest = Request::create('/pos/inventory/baseline', 'POST', [
    'operation_id' => 'inventory-http-extra-field',
    'product_id' => 'product-denied',
    'opening_quantity' => 1,
    'tenant_id' => 'tenant-alpha',
]);
$invalidRequest->attributes->set('oneqay.correlation_id', 'correlation-inventory-http-extra');
$invalidResponse = $controller($invalidRequest);
$assert($invalidResponse->getStatusCode() === 422, 'unknown HTTP field accepted');

$verified = $contexts->current();
$productionRepository = new LaravelInventoryBaselineRepository($connection, true, 'production', true);
try {
    $productionRepository->establish(
        PosExecutionContext::fromVerified($verified),
        new InventoryBaselineCommand(
            'inventory-production-denied',
            ProductId::fromString('product-denied'),
            1,
        ),
        'correlation-inventory-production-denied',
        time(),
    );
    $assert(false, 'production runtime accepted baseline');
} catch (PosTransactionViolation) {}

$disabledRepository = new LaravelInventoryBaselineRepository($connection, true, 'ci', false);
try {
    $disabledRepository->establish(
        PosExecutionContext::fromVerified($verified),
        new InventoryBaselineCommand(
            'inventory-feature-disabled',
            ProductId::fromString('product-denied'),
            1,
        ),
        'correlation-inventory-feature-disabled',
        time(),
    );
    $assert(false, 'disabled feature accepted baseline');
} catch (PosTransactionViolation) {}

$assert(Route::has('pos.inventory.baseline'), 'armed inventory baseline route absent');
$route = Route::getRoutes()->getByName('pos.inventory.baseline');
$assert($route !== null, 'inventory baseline route unresolved');
$middleware = $route->gatherMiddleware();
$assert(in_array('session.active', $middleware, true), 'inventory active-session middleware missing');
$assert(
    in_array(App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware::class, $middleware, true),
    'inventory verified POS context middleware missing',
);

$migration20 = require __DIR__.'/../database/migrations/0000_00_00_000020_create_pos_inventory_baseline_foundation.php';
try {
    $migration20->down();
    $assert(false, 'migration #20 rollback executed');
} catch (LogicException) {}

$assert($saleHistory->saleId() !== '', 'sale-history evidence missing');

$manager->disconnect('s51_inventory');
$manager->purge('s51_inventory');
@unlink($db);
@rmdir($workspace);

echo "Sprint51 JRN-008 bounded outlet inventory baseline regression passed.\n";
