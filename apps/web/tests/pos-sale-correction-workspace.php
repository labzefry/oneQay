<?php

declare(strict_types=1);

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosSaleCorrectionWorkspaceSnapshot;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Pos\LaravelPosSaleCorrectionWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('s', 32));
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

$databasePath = sys_get_temp_dir().'/oneqay-s159-corrections-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint159 disposable correction database could not be created.');

$app['config']->set('database.default', 's159_corrections');
$app['config']->set('database.connections.s159_corrections', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => true,
]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s159_corrections');
$connection = $databaseManager->connection('s159_corrections');

$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NOT NULL, applied_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, completed_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, reversed_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, voided_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, refund_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, refunded_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, refunded_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_shifts (tenant_id TEXT NOT NULL, shift_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, active_slot INTEGER NULL)');

$saleCompletedCash = 'sale-'.str_repeat('a', 24);
$saleVoidedCash = 'sale-'.str_repeat('b', 24);
$saleRefundedCash = 'sale-'.str_repeat('c', 24);
$saleCompletedExternal = 'sale-'.str_repeat('d', 24);
$saleClosedShift = 'sale-'.str_repeat('e', 24);

$connection->table('oneqay_pos_sales')->insert([
    ['tenant_id' => 'tenant-a', 'sale_id' => $saleCompletedCash, 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'device-sale-a', 'shift_id' => 'shift-a', 'applied_atomic' => 18000, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'CASH', 'completed_at_unix' => 1700000500],
    ['tenant_id' => 'tenant-a', 'sale_id' => $saleVoidedCash, 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'device-sale-b', 'shift_id' => 'shift-b', 'applied_atomic' => 1250, 'currency' => 'USD', 'currency_scale' => 2, 'tender_category' => 'CASH', 'completed_at_unix' => 1700000400],
    ['tenant_id' => 'tenant-a', 'sale_id' => $saleRefundedCash, 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'device-sale-c', 'shift_id' => 'shift-c', 'applied_atomic' => 9900, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'CASH', 'completed_at_unix' => 1700000300],
    ['tenant_id' => 'tenant-a', 'sale_id' => $saleCompletedExternal, 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'device-sale-d', 'shift_id' => 'shift-d', 'applied_atomic' => 5000, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'MANUAL_EXTERNAL', 'completed_at_unix' => 1700000200],
    ['tenant_id' => 'tenant-a', 'sale_id' => $saleClosedShift, 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'device-sale-e', 'shift_id' => 'shift-e', 'applied_atomic' => 7500, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'CASH', 'completed_at_unix' => 1700000100],
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-'.str_repeat('f', 24), 'organization_id' => 'org-other', 'outlet_id' => 'outlet-a', 'device_id' => 'foreign-org', 'shift_id' => 'shift-f', 'applied_atomic' => 1, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'CASH', 'completed_at_unix' => 1700000600],
    ['tenant_id' => 'tenant-b', 'sale_id' => 'sale-'.str_repeat('1', 24), 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'foreign-tenant', 'shift_id' => 'shift-g', 'applied_atomic' => 1, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'CASH', 'completed_at_unix' => 1700000700],
]);

$connection->table('oneqay_pos_shifts')->insert([
    ['tenant_id' => 'tenant-a', 'shift_id' => 'shift-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'active_slot' => 1],
    ['tenant_id' => 'tenant-a', 'shift_id' => 'shift-b', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'active_slot' => 1],
    ['tenant_id' => 'tenant-a', 'shift_id' => 'shift-c', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'active_slot' => 1],
    ['tenant_id' => 'tenant-a', 'shift_id' => 'shift-d', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'active_slot' => 1],
    ['tenant_id' => 'tenant-a', 'shift_id' => 'shift-e', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'active_slot' => null],
]);

$voidB = 'void-'.str_repeat('b', 24);
$voidC = 'void-'.str_repeat('c', 24);
$connection->table('oneqay_pos_sale_voids')->insert([
    ['tenant_id' => 'tenant-a', 'sale_id' => $saleVoidedCash, 'void_id' => $voidB, 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'reversed_atomic' => 1250, 'currency' => 'USD', 'currency_scale' => 2, 'tender_category' => 'CASH', 'evidence_mode' => 'FULL_SALE_VOID', 'voided_at_unix' => 1700000410],
    ['tenant_id' => 'tenant-a', 'sale_id' => $saleRefundedCash, 'void_id' => $voidC, 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'reversed_atomic' => 9900, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'CASH', 'evidence_mode' => 'FULL_SALE_VOID', 'voided_at_unix' => 1700000310],
]);
$connection->table('oneqay_pos_sale_cash_refunds')->insert([
    ['tenant_id' => 'tenant-a', 'sale_id' => $saleRefundedCash, 'refund_id' => 'refund-'.str_repeat('c', 24), 'void_id' => $voidC, 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'refunded_atomic' => 9900, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'CASH', 'evidence_mode' => 'FULL_CASH_REFUND', 'refunded_at_unix' => 1700000320],
]);

$context = PosExecutionContext::fromVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('operator-device'),
));

$repository = new LaravelPosSaleCorrectionWorkspaceRepository($connection, true, 'ci', true, true, true, true);
$rows = $repository->recent($context);
$assert(count($rows) === 5, 'Sprint159 correction workspace leaked foreign tenant or organization sales.');
$byId = [];
foreach ($rows as $row) {
    $byId[$row['sale_id']] = $row;
}
$assert($byId[$saleCompletedCash]['state'] === 'COMPLETED' && $byId[$saleCompletedCash]['shift_active'] === true, 'Sprint159 completed CASH state or active shift changed.');
$assert($byId[$saleCompletedCash]['original_device_id'] === 'device-sale-a', 'Sprint159 must preserve original sale device without restricting current operator device.');
$assert($byId[$saleVoidedCash]['state'] === 'VOIDED' && $byId[$saleVoidedCash]['void_id'] === $voidB, 'Sprint159 void evidence state changed.');
$assert($byId[$saleVoidedCash]['amount_atomic'] === 1250 && $byId[$saleVoidedCash]['currency'] === 'USD' && $byId[$saleVoidedCash]['scale'] === 2, 'Sprint159 correction workspace must preserve amount currency and scale.');
$assert($byId[$saleRefundedCash]['state'] === 'REFUNDED' && $byId[$saleRefundedCash]['refund_id'] === 'refund-'.str_repeat('c', 24), 'Sprint159 refund evidence state changed.');
$assert($byId[$saleClosedShift]['shift_active'] === false, 'Sprint159 must not mark a closed original shift active.');

$snapshot = new PosSaleCorrectionWorkspaceSnapshot('tenant-a', 'org-a', 'outlet-a', 'operator-device', true, true, $rows);
$snapshotRows = [];
foreach ($snapshot->sales() as $row) {
    $snapshotRows[$row['sale_id']] = $row;
}
$assert($snapshotRows[$saleCompletedCash]['void_eligible'] === true, 'Sprint159 completed active sale must be void eligible with permission.');
$assert($snapshotRows[$saleVoidedCash]['cash_refund_eligible'] === true, 'Sprint159 voided CASH sale must be refund eligible with permission.');
$assert($snapshotRows[$saleRefundedCash]['void_eligible'] === false && $snapshotRows[$saleRefundedCash]['cash_refund_eligible'] === false, 'Sprint159 refunded sale must expose no correction action.');
$assert($snapshotRows[$saleCompletedExternal]['void_eligible'] === true, 'Sprint159 MANUAL_EXTERNAL sale may still be void eligible.');
$assert($snapshotRows[$saleClosedShift]['void_eligible'] === false, 'Sprint159 closed-shift sale must not be void eligible.');

$voidOnly = new PosSaleCorrectionWorkspaceSnapshot('tenant-a', 'org-a', 'outlet-a', 'operator-device', true, false, $rows);
$voidOnlyRows = [];
foreach ($voidOnly->sales() as $row) { $voidOnlyRows[$row['sale_id']] = $row; }
$assert($voidOnlyRows[$saleVoidedCash]['cash_refund_eligible'] === false, 'Sprint159 refund action must honor deny-by-default permission narrowing.');

$refundOnly = new PosSaleCorrectionWorkspaceSnapshot('tenant-a', 'org-a', 'outlet-a', 'operator-device', false, true, $rows);
$refundOnlyRows = [];
foreach ($refundOnly->sales() as $row) { $refundOnlyRows[$row['sale_id']] = $row; }
$assert($refundOnlyRows[$saleCompletedCash]['void_eligible'] === false, 'Sprint159 void action must honor deny-by-default permission narrowing.');
$assert($refundOnlyRows[$saleVoidedCash]['cash_refund_eligible'] === true, 'Sprint159 independently granted refund permission must remain usable.');

foreach ([
    [false, 'ci', true, true, true, true],
    [true, 'production', true, true, true, true],
    [true, 'ci', false, true, true, true],
    [true, 'ci', true, false, true, true],
    [true, 'ci', true, true, false, true],
    [true, 'ci', true, true, true, false],
] as [$persistence, $runtime, $workspace, $saleCompletion, $void, $refund]) {
    try {
        (new LaravelPosSaleCorrectionWorkspaceRepository($connection, $persistence, $runtime, $workspace, $saleCompletion, $void, $refund))->recent($context);
        $assert(false, 'Sprint159 correction workspace accepted a disallowed persistence/runtime/capability state.');
    } catch (PosTransactionViolation) {
        // Expected fail-closed boundary.
    }
}

$connection->table('oneqay_pos_sale_cash_refunds')->insert([
    'tenant_id' => 'tenant-a',
    'sale_id' => $saleCompletedCash,
    'refund_id' => 'refund-'.str_repeat('a', 24),
    'void_id' => 'void-'.str_repeat('a', 24),
    'organization_id' => 'org-a',
    'outlet_id' => 'outlet-a',
    'refunded_atomic' => 18000,
    'currency' => 'IDR',
    'currency_scale' => 0,
    'tender_category' => 'CASH',
    'evidence_mode' => 'FULL_CASH_REFUND',
    'refunded_at_unix' => 1700000510,
]);
try {
    $repository->recent($context);
    $assert(false, 'Sprint159 correction workspace accepted refund evidence without canonical void evidence.');
} catch (PosTransactionViolation) {
    // Expected fail-closed integrity boundary.
}

$databaseManager->disconnect('s159_corrections');
$databaseManager->purge('s159_corrections');
@unlink($databasePath);

fwrite(STDOUT, "Sprint159 POS sale correction workspace regression passed.\n");
