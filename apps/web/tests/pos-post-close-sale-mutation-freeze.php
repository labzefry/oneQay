<?php

declare(strict_types=1);

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleCashRefundCommand;
use App\Application\Pos\SaleCommand;
use App\Application\Pos\SaleVoidCommand;
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
use App\Infrastructure\Pos\LaravelDurablePosSaleRepository;
use App\Infrastructure\Pos\LaravelSaleCashRefundRepository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('f', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
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
        throw new RuntimeException('Sprint90 post-close sale mutation freeze regression failed: '.$case);
    }
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s90-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'pos.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 's90_pos');
$app['config']->set('database.connections.s90_pos', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $db,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);

$manager = $app->make('db');
$manager->purge('s90_pos');
$manager->setDefaultConnection('s90_pos');
$connection = $manager->connection('s90_pos');
$connection->getPdo();

$migrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($migrations);
$assert(count($migrations) === 27, 'canonical migration count through #27');
for ($index = 1; $index <= 27; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $assert(
        count(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix))) === 1,
        'migration #'.$index.' exact',
    );
}
$assert(
    count(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, '0000_00_00_000028_'))) === 0,
    'migration #28 unexpectedly exists',
);
foreach ($migrations as $migrationFile) {
    (require __DIR__.'/../database/migrations/'.$migrationFile)->up();
}

$connection->table('oneqay_tenants')->insert(['id' => 'tenant-alpha']);
$connection->table('oneqay_identities')->insert([
    'tenant_id' => 'tenant-alpha',
    'id' => 'operator-alpha',
]);
$connection->table('oneqay_organizations')->insert([
    'tenant_id' => 'tenant-alpha',
    'id' => 'organization-alpha',
]);
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'operator-alpha',
    'organization_id' => 'organization-alpha',
]);
$connection->table('oneqay_outlets')->insert([
    'tenant_id' => 'tenant-alpha',
    'id' => 'outlet-alpha',
    'organization_id' => 'organization-alpha',
]);
$connection->table('oneqay_devices')->insert([
    'tenant_id' => 'tenant-alpha',
    'id' => 'device-alpha',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
]);

$shiftId = str_repeat('9', 32);
$connection->table('oneqay_pos_shifts')->insert([
    'tenant_id' => 'tenant-alpha',
    'shift_id' => $shiftId,
    'operation_id' => 'shift-s90-active-operation',
    'payload_fingerprint' => str_repeat('8', 64),
    'actor_identity_id' => 'operator-alpha',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-alpha',
    'active_slot' => 1,
    'correlation_id' => 'correlation-s90-shift-open',
    'opened_at_unix' => 1000,
]);

foreach ([
    ['product-a', 1000],
    ['product-b', 2000],
    ['product-c', 3000],
] as [$product, $price]) {
    $connection->table('oneqay_pos_sale_catalog_items')->insert([
        'tenant_id' => 'tenant-alpha',
        'outlet_id' => 'outlet-alpha',
        'product_id' => $product,
        'display_name' => strtoupper($product),
        'unit_price_atomic' => $price,
        'currency' => 'IDR',
        'currency_scale' => 0,
        'available_quantity' => 10,
        'active' => true,
    ]);
}

$context = PosExecutionContext::fromVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('operator-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha'),
));

$sales = new LaravelDurablePosSaleRepository($connection, true, 'ci', true, true);
$refunds = new LaravelSaleCashRefundRepository($connection, true, 'ci', true);

$complete = static function (
    LaravelDurablePosSaleRepository $repository,
    PosExecutionContext $context,
    string $operationId,
    string $productId,
    int $price,
    int $occurredAt,
) use ($connection) {
    return $connection->transaction(fn () => $repository->complete(
        $context,
        new SaleCommand(
            $operationId,
            Cart::fromLines([new CartLine(ProductId::fromString($productId), 1)]),
            TenderCategory::CASH,
            Money::fromAtomicUnits($price, 'IDR', 0),
            'correlation-'.$operationId,
        ),
        $occurredAt,
    ));
};

$saleA = $complete($sales, $context, 'sale-s90-primary-operation', 'product-a', 1000, 1100);
$saleB = $complete($sales, $context, 'sale-s90-void-denial', 'product-b', 2000, 1200);
$saleC = $complete($sales, $context, 'sale-s90-refund-denial', 'product-c', 3000, 1300);

foreach ([$saleA, $saleB, $saleC] as $receipt) {
    $shiftBinding = $connection->table('oneqay_pos_sales')
        ->where('tenant_id', 'tenant-alpha')
        ->where('sale_id', $receipt->saleId())
        ->value('shift_id');
    $assert($shiftBinding === $shiftId, 'completed sale missing immutable shift binding');
}

$voidACommand = new SaleVoidCommand('void-s90-primary-operation', $saleA->saleId());
$voidA = $connection->transaction(fn () => $sales->voidCompletedSale(
    $context,
    $voidACommand,
    'correlation-void-s90-primary',
    1400,
));
$voidC = $connection->transaction(fn () => $sales->voidCompletedSale(
    $context,
    new SaleVoidCommand('void-s90-refund-denial', $saleC->saleId()),
    'correlation-void-s90-refund-denial',
    1500,
));

$refundACommand = new SaleCashRefundCommand('refund-s90-primary-operation', $saleA->saleId());
$refundA = $connection->transaction(fn () => $refunds->record(
    $context,
    $refundACommand,
    'correlation-refund-s90-primary',
    1600,
));

$assert($voidA->reversedAmount()->atomicUnits() === 1000, 'active-shift void amount');
$assert($voidC->reversedAmount()->atomicUnits() === 3000, 'active-shift alternate void amount');
$assert($refundA->refundedAmount()->atomicUnits() === 1000, 'active-shift refund amount');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id', 'product-a')->value('available_quantity') === 10, 'primary void did not restore stock');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id', 'product-b')->value('available_quantity') === 9, 'void-denial fixture stock');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id', 'product-c')->value('available_quantity') === 10, 'refund-denial fixture void did not restore stock');

$connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('shift_id', $shiftId)
    ->update(['active_slot' => null]);
$assert(
    $connection->table('oneqay_pos_shifts')->where('tenant_id', 'tenant-alpha')->where('shift_id', $shiftId)->value('active_slot') === null,
    'inactive-shift fixture was not established',
);

$voidRowsBeforeDenied = $connection->table('oneqay_pos_sale_voids')->count();
$voidEventsBeforeDenied = $connection->table('oneqay_pos_sale_events')->where('event_type', 'VOIDED')->count();
$productBBeforeDenied = (int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id', 'product-b')->value('available_quantity');
try {
    $connection->transaction(fn () => $sales->voidCompletedSale(
        $context,
        new SaleVoidCommand('void-s90-post-close-new', $saleB->saleId()),
        'correlation-void-s90-post-close',
        1700,
    ));
    $assert(false, 'new void accepted after bound shift became inactive');
} catch (PosTransactionViolation) {}
$assert($connection->table('oneqay_pos_sale_voids')->count() === $voidRowsBeforeDenied, 'denied post-close void persisted evidence');
$assert($connection->table('oneqay_pos_sale_events')->where('event_type', 'VOIDED')->count() === $voidEventsBeforeDenied, 'denied post-close void emitted event');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id', 'product-b')->value('available_quantity') === $productBBeforeDenied, 'denied post-close void mutated inventory');

$refundRowsBeforeDenied = $connection->table('oneqay_pos_sale_cash_refunds')->count();
$refundEventsBeforeDenied = $connection->table('oneqay_pos_sale_events')->where('event_type', 'REFUNDED')->count();
$productCBeforeDenied = (int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id', 'product-c')->value('available_quantity');
try {
    $connection->transaction(fn () => $refunds->record(
        $context,
        new SaleCashRefundCommand('refund-s90-post-close-new', $saleC->saleId()),
        'correlation-refund-s90-post-close',
        1800,
    ));
    $assert(false, 'new refund accepted after bound shift became inactive');
} catch (PosTransactionViolation) {}
$assert($connection->table('oneqay_pos_sale_cash_refunds')->count() === $refundRowsBeforeDenied, 'denied post-close refund persisted evidence');
$assert($connection->table('oneqay_pos_sale_events')->where('event_type', 'REFUNDED')->count() === $refundEventsBeforeDenied, 'denied post-close refund emitted event');
$assert((int) $connection->table('oneqay_pos_sale_catalog_items')->where('product_id', 'product-c')->value('available_quantity') === $productCBeforeDenied, 'denied post-close refund mutated inventory');

$voidReplay = $connection->transaction(fn () => $sales->voidCompletedSale(
    $context,
    $voidACommand,
    'correlation-void-s90-replay',
    1900,
));
$refundReplay = $connection->transaction(fn () => $refunds->record(
    $context,
    $refundACommand,
    'correlation-refund-s90-replay',
    2000,
));

$assert($voidReplay->voidId() === $voidA->voidId(), 'post-close exact void replay changed evidence');
$assert($voidReplay->correlationId() === 'correlation-void-s90-primary', 'post-close void replay changed original correlation');
$assert($refundReplay->refundId() === $refundA->refundId(), 'post-close exact refund replay changed evidence');
$assert($refundReplay->correlationId() === 'correlation-refund-s90-primary', 'post-close refund replay changed original correlation');
$assert($connection->table('oneqay_pos_sale_voids')->where('sale_id', $saleA->saleId())->count() === 1, 'void replay duplicated evidence');
$assert($connection->table('oneqay_pos_sale_cash_refunds')->where('sale_id', $saleA->saleId())->count() === 1, 'refund replay duplicated evidence');
$assert($connection->table('oneqay_pos_sale_events')->where('sale_id', $saleA->saleId())->where('event_type', 'VOIDED')->count() === 1, 'void replay duplicated event');
$assert($connection->table('oneqay_pos_sale_events')->where('sale_id', $saleA->saleId())->where('event_type', 'REFUNDED')->count() === 1, 'refund replay duplicated event');

$manager->disconnect('s90_pos');
$manager->purge('s90_pos');
@unlink($db);
@rmdir($workspace);

echo "Sprint90 post-close sale mutation freeze regression passed.\n";
