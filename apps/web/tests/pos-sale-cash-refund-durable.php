<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\CompleteSale;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\RecordCashRefund;
use App\Application\Pos\SaleCashRefundCommand;
use App\Application\Pos\SaleCommand;
use App\Application\Pos\SaleVoidCommand;
use App\Application\Pos\VoidSale;
use App\Delivery\Http\Pos\PosSaleCashRefundController;
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
use App\Infrastructure\Pos\LaravelSaleCashRefundRepository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('r', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_POS_SALE_COMPLETION_ENABLED' => 'true',
    'ONEQAY_POS_SALE_VOID_ENABLED' => 'true',
    'ONEQAY_POS_SALE_CASH_REFUND_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint52 JRN-007 full CASH refund regression failed: '.$case);
    }
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s52-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'pos.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 's52_pos');
$app['config']->set('database.connections.s52_pos', [
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
$app['config']->set('oneqay.pos_sale_cash_refund.enabled', true);

$manager = $app->make('db');
$manager->purge('s52_pos');
$manager->setDefaultConnection('s52_pos');
$connection = $manager->connection('s52_pos');
$connection->getPdo();

$migrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($migrations);
$assert(count($migrations) === 21, 'migration set count');
for ($index = 1; $index <= 21; $index++) {
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
    ['tenant-alpha', 'void-manager-alpha'],
    ['tenant-alpha', 'refunder-alpha'],
    ['tenant-alpha', 'no-permission-alpha'],
    ['tenant-beta', 'refunder-beta'],
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
    ['tenant-alpha', 'void-manager-alpha', 'organization-alpha'],
    ['tenant-alpha', 'refunder-alpha', 'organization-alpha'],
    ['tenant-alpha', 'refunder-alpha', 'organization-alt'],
    ['tenant-alpha', 'no-permission-alpha', 'organization-alpha'],
    ['tenant-beta', 'refunder-beta', 'organization-beta'],
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
    ['tenant-alpha', 'device-cashier', 'organization-alpha', 'outlet-alpha'],
    ['tenant-alpha', 'device-void', 'organization-alpha', 'outlet-alpha'],
    ['tenant-alpha', 'device-refund', 'organization-alpha', 'outlet-alpha'],
    ['tenant-alpha', 'device-no-permission', 'organization-alpha', 'outlet-alpha'],
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
    ['tenant_id' => 'tenant-alpha', 'id' => 'refund-role'],
    ['tenant_id' => 'tenant-beta', 'id' => 'refund-role'],
]);
$connection->table('oneqay_role_permissions')->insert([
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'cashier-role', 'permission_id' => PosPermission::COMPLETE_SALE],
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'void-role', 'permission_id' => PosPermission::VOID_SALE],
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'refund-role', 'permission_id' => PosPermission::REFUND_SALE],
    ['tenant_id' => 'tenant-beta', 'role_id' => 'refund-role', 'permission_id' => PosPermission::REFUND_SALE],
]);
$connection->table('oneqay_outlet_role_assignments')->insert([
    ['tenant_id'=>'tenant-alpha','identity_id'=>'cashier-alpha','organization_id'=>'organization-alpha','outlet_id'=>'outlet-alpha','role_id'=>'cashier-role'],
    ['tenant_id'=>'tenant-alpha','identity_id'=>'void-manager-alpha','organization_id'=>'organization-alpha','outlet_id'=>'outlet-alpha','role_id'=>'void-role'],
    ['tenant_id'=>'tenant-alpha','identity_id'=>'refunder-alpha','organization_id'=>'organization-alpha','outlet_id'=>'outlet-alpha','role_id'=>'refund-role'],
    ['tenant_id'=>'tenant-alpha','identity_id'=>'refunder-alpha','organization_id'=>'organization-alpha','outlet_id'=>'outlet-other','role_id'=>'refund-role'],
    ['tenant_id'=>'tenant-alpha','identity_id'=>'refunder-alpha','organization_id'=>'organization-alt','outlet_id'=>'outlet-org-alt','role_id'=>'refund-role'],
    ['tenant_id'=>'tenant-beta','identity_id'=>'refunder-beta','organization_id'=>'organization-beta','outlet_id'=>'outlet-beta','role_id'=>'refund-role'],
]);

foreach ([
    ['product-success', 2500],
    ['product-other', 1500],
    ['product-nonvoid', 3000],
    ['product-external', 1000],
    ['product-inconsistent', 2000],
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
    'shift_id' => str_repeat('5', 32),
    'operation_id' => 'shift-source-refund',
    'payload_fingerprint' => str_repeat('6', 64),
    'actor_identity_id' => 'cashier-alpha',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-cashier',
    'active_slot' => 1,
    'correlation_id' => 'correlation-shift-refund',
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

$setContext('cashier-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-cashier');
$sales = $app->make(CompleteSale::class);

$successReceipt = $sales->complete(new SaleCommand(
    'sale-refund-success',
    Cart::fromLines([new CartLine(ProductId::fromString('product-success'), 2)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(6000, 'IDR', 0),
    'correlation-sale-refund-success',
));
$otherReceipt = $sales->complete(new SaleCommand(
    'sale-refund-other',
    Cart::fromLines([new CartLine(ProductId::fromString('product-other'), 1)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(1500, 'IDR', 0),
    'correlation-sale-refund-other',
));
$nonvoidReceipt = $sales->complete(new SaleCommand(
    'sale-refund-nonvoid',
    Cart::fromLines([new CartLine(ProductId::fromString('product-nonvoid'), 1)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(3000, 'IDR', 0),
    'correlation-sale-refund-nonvoid',
));
$externalReceipt = $sales->complete(new SaleCommand(
    'sale-refund-external',
    Cart::fromLines([new CartLine(ProductId::fromString('product-external'), 1)]),
    TenderCategory::MANUAL_EXTERNAL,
    Money::fromAtomicUnits(1000, 'IDR', 0),
    'correlation-sale-refund-external',
));
$inconsistentReceipt = $sales->complete(new SaleCommand(
    'sale-refund-inconsistent',
    Cart::fromLines([new CartLine(ProductId::fromString('product-inconsistent'), 1)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(2000, 'IDR', 0),
    'correlation-sale-refund-inconsistent',
));

$successSale = (array) $connection->table('oneqay_pos_sales')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $successReceipt->saleId())
    ->first();
$assert(($successSale['applied_atomic'] ?? null) === 5000 || ($successSale['applied_atomic'] ?? null) === '5000', 'cash applied amount');
$assert(($successSale['change_atomic'] ?? null) === 1000 || ($successSale['change_atomic'] ?? null) === '1000', 'cash change evidence');

$setContext('void-manager-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-void');
$voids = $app->make(VoidSale::class);
$successVoid = $voids->execute(
    new SaleVoidCommand('void-refund-success', $successReceipt->saleId()),
    'correlation-void-refund-success',
);
$externalVoid = $voids->execute(
    new SaleVoidCommand('void-refund-external', $externalReceipt->saleId()),
    'correlation-void-refund-external',
);
$inconsistentVoid = $voids->execute(
    new SaleVoidCommand('void-refund-inconsistent', $inconsistentReceipt->saleId()),
    'correlation-void-refund-inconsistent',
);

$stockAfterVoid = (int) $connection->table('oneqay_pos_sale_catalog_items')
    ->where('tenant_id', 'tenant-alpha')
    ->where('outlet_id', 'outlet-alpha')
    ->where('product_id', 'product-success')
    ->value('available_quantity');
$assert($stockAfterVoid === 20, 'JRN-007 exact stock restoration before refund');

$connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-refund')
    ->update(['active_slot' => null]);
$shiftBeforeRefund = (array) $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-refund')
    ->first();

$originalSaleBeforeRefund = (array) $connection->table('oneqay_pos_sales')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $successReceipt->saleId())
    ->first();
$originalVoidBeforeRefund = (array) $connection->table('oneqay_pos_sale_voids')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $successReceipt->saleId())
    ->first();

$refunds = $app->make(RecordCashRefund::class);

$setContext('no-permission-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-no-permission');
try {
    $refunds->record(
        new SaleCashRefundCommand('refund-denied-operation', $successReceipt->saleId()),
        'correlation-refund-denied',
    );
    $assert(false, 'permission denied actor refunded sale');
} catch (DurableAuthorizationViolation) {}
$assert($connection->table('oneqay_pos_sale_cash_refunds')->count() === 0, 'authorization denial mutated refund state');

$setContext('refunder-beta', 'tenant-beta', 'organization-beta', 'outlet-beta', 'device-beta');
try {
    $refunds->record(
        new SaleCashRefundCommand('refund-cross-tenant', $successReceipt->saleId()),
        'correlation-refund-cross-tenant',
    );
    $assert(false, 'cross-tenant refund target accepted');
} catch (PosTransactionViolation) {}

$setContext('refunder-alpha', 'tenant-alpha', 'organization-alt', 'outlet-org-alt', 'device-org-alt');
try {
    $refunds->record(
        new SaleCashRefundCommand('refund-cross-organization', $successReceipt->saleId()),
        'correlation-refund-cross-organization',
    );
    $assert(false, 'cross-organization refund target accepted');
} catch (PosTransactionViolation) {}

$setContext('refunder-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-other', 'device-outlet-other');
try {
    $refunds->record(
        new SaleCashRefundCommand('refund-cross-outlet', $successReceipt->saleId()),
        'correlation-refund-cross-outlet',
    );
    $assert(false, 'cross-outlet refund target accepted');
} catch (PosTransactionViolation) {}

$setContext('refunder-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-refund');
try {
    $refunds->record(
        new SaleCashRefundCommand('refund-nonvoid-operation', $nonvoidReceipt->saleId()),
        'correlation-refund-nonvoid',
    );
    $assert(false, 'non-voided sale refunded');
} catch (PosTransactionViolation) {}

try {
    $refunds->record(
        new SaleCashRefundCommand('refund-manual-external', $externalReceipt->saleId()),
        'correlation-refund-manual-external',
    );
    $assert(false, 'MANUAL_EXTERNAL sale refunded as CASH');
} catch (PosTransactionViolation) {}
$assert($externalVoid->tenderCategory() === TenderCategory::MANUAL_EXTERNAL, 'external void evidence changed');

$connection->table('oneqay_pos_sale_voids')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $inconsistentReceipt->saleId())
    ->update(['reversed_atomic' => 2001]);
try {
    $refunds->record(
        new SaleCashRefundCommand('refund-inconsistent-void', $inconsistentReceipt->saleId()),
        'correlation-refund-inconsistent',
    );
    $assert(false, 'inconsistent void amount accepted');
} catch (PosTransactionViolation) {}
$assert($connection->table('oneqay_pos_sale_cash_refunds')->where('sale_id', $inconsistentReceipt->saleId())->count() === 0, 'inconsistent void persisted refund');

$refundCommand = new SaleCashRefundCommand('refund-operation-success', $successReceipt->saleId());
$refundResult = $refunds->record($refundCommand, 'correlation-refund-success');

$assert($refundResult->saleId() === $successReceipt->saleId(), 'refund target result');
$assert($refundResult->voidId() === $successVoid->voidId(), 'refund bound exact void');
$assert($refundResult->refundedAmount()->atomicUnits() === 5000, 'refund equals original applied amount');
$assert($refundResult->refundedAmount()->atomicUnits() !== 6000, 'refund incorrectly included tendered cash');
$assert($refundResult->tenderCategory() === TenderCategory::CASH, 'refund tender classification');
$assert($refundResult->evidenceMode() === 'FULL_CASH_REFUND', 'refund evidence mode');
$assert($connection->table('oneqay_pos_sale_cash_refunds')->where('sale_id', $successReceipt->saleId())->count() === 1, 'single refund row');
$assert($connection->table('oneqay_pos_sale_events')->where('sale_id', $successReceipt->saleId())->where('event_type', 'REFUNDED')->count() === 1, 'single REFUNDED event');

$refundRow = (array) $connection->table('oneqay_pos_sale_cash_refunds')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $successReceipt->saleId())
    ->first();
$assert(($refundRow['device_id'] ?? null) === 'device-refund', 'current refund device audited');
$assert((int) ($refundRow['refunded_atomic'] ?? -1) === 5000, 'durable refunded amount');

$afterSale = (array) $connection->table('oneqay_pos_sales')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $successReceipt->saleId())
    ->first();
$afterVoid = (array) $connection->table('oneqay_pos_sale_voids')
    ->where('tenant_id', 'tenant-alpha')
    ->where('sale_id', $successReceipt->saleId())
    ->first();
$shiftAfterRefund = (array) $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-source-refund')
    ->first();

$assert($afterSale === $originalSaleBeforeRefund, 'refund mutated original sale');
$assert($afterVoid === $originalVoidBeforeRefund, 'refund mutated original void');
$assert($shiftAfterRefund === $shiftBeforeRefund, 'refund mutated shift');
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('outlet_id', 'outlet-alpha')
        ->where('product_id', 'product-success')
        ->value('available_quantity') === $stockAfterVoid,
    'refund changed stock after JRN-007 restoration',
);

$replay = $refunds->record($refundCommand, 'correlation-refund-replay');
$assert($replay->refundId() === $refundResult->refundId(), 'exact replay returned different refund');
$assert($replay->correlationId() === 'correlation-refund-success', 'replay did not preserve original correlation');
$assert($connection->table('oneqay_pos_sale_cash_refunds')->where('sale_id', $successReceipt->saleId())->count() === 1, 'replay duplicated refund row');
$assert($connection->table('oneqay_pos_sale_events')->where('sale_id', $successReceipt->saleId())->where('event_type', 'REFUNDED')->count() === 1, 'replay duplicated REFUNDED event');
$assert(
    (int) $connection->table('oneqay_pos_sale_catalog_items')
        ->where('tenant_id', 'tenant-alpha')
        ->where('outlet_id', 'outlet-alpha')
        ->where('product_id', 'product-success')
        ->value('available_quantity') === $stockAfterVoid,
    'replay changed stock',
);

try {
    $refunds->record(
        new SaleCashRefundCommand('refund-operation-success', $otherReceipt->saleId()),
        'correlation-refund-conflicting-operation',
    );
    $assert(false, 'conflicting operation reuse accepted');
} catch (PosTransactionViolation) {}

try {
    $refunds->record(
        new SaleCashRefundCommand('refund-second-operation', $successReceipt->saleId()),
        'correlation-refund-second-operation',
    );
    $assert(false, 'second refund operation accepted');
} catch (PosTransactionViolation) {}

$context = PosExecutionContext::fromVerified($contexts->current());
try {
    (new LaravelSaleCashRefundRepository($connection, true, 'production', true))->record(
        $context,
        new SaleCashRefundCommand('refund-production-denied', $successReceipt->saleId()),
        'correlation-refund-production-denied',
        time(),
    );
    $assert(false, 'production runtime accepted refund');
} catch (PosTransactionViolation) {}

try {
    (new LaravelSaleCashRefundRepository($connection, true, 'ci', false))->record(
        $context,
        new SaleCashRefundCommand('refund-feature-disabled', $successReceipt->saleId()),
        'correlation-refund-feature-disabled',
        time(),
    );
    $assert(false, 'disabled feature accepted refund');
} catch (PosTransactionViolation) {}

$controller = $app->make(PosSaleCashRefundController::class);
$request = Request::create('/pos/sales/cash-refund', 'POST', [
    'operation_id' => 'refund-http-unknown-field',
    'sale_id' => $successReceipt->saleId(),
    'amount' => 5000,
]);
$request->attributes->set('oneqay.correlation_id', 'correlation-refund-http-unknown');
$response = $controller($request);
$assert($response->getStatusCode() === 422, 'unknown HTTP field accepted');
$assert($connection->table('oneqay_pos_sale_cash_refunds')->where('operation_id', 'refund-http-unknown-field')->count() === 0, 'unknown HTTP field persisted');

$assert(Route::has('pos.sales.cash-refund'), 'armed cash refund route absent');
$route = Route::getRoutes()->getByName('pos.sales.cash-refund');
$assert($route !== null, 'cash refund route unresolved');
$middleware = $route->gatherMiddleware();
$assert(in_array('session.active', $middleware, true), 'refund active-session middleware missing');
$assert(
    in_array(App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware::class, $middleware, true),
    'refund verified POS context middleware missing',
);

$migration21 = require __DIR__.'/../database/migrations/0000_00_00_000021_create_pos_sale_cash_refund_foundation.php';
try {
    $migration21->down();
    $assert(false, 'migration #21 rollback executed');
} catch (LogicException) {}

$manager->disconnect('s52_pos');
$manager->purge('s52_pos');
@unlink($db);
@rmdir($workspace);

echo "Sprint52 JRN-007 bounded full CASH refund evidence regression passed.\n";
