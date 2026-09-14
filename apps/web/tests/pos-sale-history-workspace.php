<?php

declare(strict_types=1);

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Pos\LaravelPosSaleHistoryWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('h', 32));
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

$databasePath = sys_get_temp_dir().'/oneqay-s160-history-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint160 disposable sale history database could not be created.');
$app['config']->set('database.default', 's160_history');
$app['config']->set('database.connections.s160_history', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => false,
]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s160_history');
$connection = $databaseManager->connection('s160_history');

$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NULL, total_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, applied_atomic INTEGER NOT NULL, change_atomic INTEGER NOT NULL, completed_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_lines (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, line_no INTEGER NOT NULL, product_id TEXT NOT NULL, quantity INTEGER NOT NULL, unit_price_atomic INTEGER NOT NULL, line_total_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, reversed_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, voided_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, refund_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, refunded_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, refunded_at_unix INTEGER NOT NULL)');

$saleCompleted = 'sale-'.str_repeat('a', 24);
$saleVoided = 'sale-'.str_repeat('b', 24);
$saleRefunded = 'sale-'.str_repeat('c', 24);
$saleLegacy = 'sale-'.str_repeat('d', 24);
$saleForeignOrg = 'sale-'.str_repeat('e', 24);
$saleForeignTenant = 'sale-'.str_repeat('f', 24);

$connection->table('oneqay_pos_sales')->insert([
    ['tenant_id'=>'tenant-a','sale_id'=>$saleCompleted,'organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','shift_id'=>'shift-a','total_atomic'=>18000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'SYNTHETIC_CASH','applied_atomic'=>18000,'change_atomic'=>2000,'completed_at_unix'=>1700000600],
    ['tenant_id'=>'tenant-a','sale_id'=>$saleVoided,'organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-b','shift_id'=>'shift-b','total_atomic'=>1250,'currency'=>'USD','currency_scale'=>2,'tender_category'=>'MANUAL_EXTERNAL','evidence_mode'=>'MANUAL_EXTERNAL_REFERENCE','applied_atomic'=>1250,'change_atomic'=>0,'completed_at_unix'=>1700000500],
    ['tenant_id'=>'tenant-a','sale_id'=>$saleRefunded,'organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-c','shift_id'=>'shift-c','total_atomic'=>9900,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'SYNTHETIC_CASH','applied_atomic'=>9900,'change_atomic'=>0,'completed_at_unix'=>1700000400],
    ['tenant_id'=>'tenant-a','sale_id'=>$saleLegacy,'organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-d','shift_id'=>null,'total_atomic'=>5000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'SYNTHETIC_CASH','applied_atomic'=>5000,'change_atomic'=>0,'completed_at_unix'=>1700000300],
    ['tenant_id'=>'tenant-a','sale_id'=>$saleForeignOrg,'organization_id'=>'org-b','outlet_id'=>'outlet-a','device_id'=>'device-e','shift_id'=>'shift-e','total_atomic'=>1,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'SYNTHETIC_CASH','applied_atomic'=>1,'change_atomic'=>0,'completed_at_unix'=>1700000700],
    ['tenant_id'=>'tenant-b','sale_id'=>$saleForeignTenant,'organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-f','shift_id'=>'shift-f','total_atomic'=>1,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'SYNTHETIC_CASH','applied_atomic'=>1,'change_atomic'=>0,'completed_at_unix'=>1700000800],
]);

$connection->table('oneqay_pos_sale_lines')->insert([
    ['tenant_id'=>'tenant-a','sale_id'=>$saleCompleted,'line_no'=>1,'product_id'=>'product-alpha','quantity'=>2,'unit_price_atomic'=>5000,'line_total_atomic'=>10000,'currency'=>'IDR','currency_scale'=>0],
    ['tenant_id'=>'tenant-a','sale_id'=>$saleCompleted,'line_no'=>2,'product_id'=>'product-beta','quantity'=>1,'unit_price_atomic'=>8000,'line_total_atomic'=>8000,'currency'=>'IDR','currency_scale'=>0],
    ['tenant_id'=>'tenant-a','sale_id'=>$saleVoided,'line_no'=>1,'product_id'=>'product-usd','quantity'=>1,'unit_price_atomic'=>1250,'line_total_atomic'=>1250,'currency'=>'USD','currency_scale'=>2],
    ['tenant_id'=>'tenant-a','sale_id'=>$saleRefunded,'line_no'=>1,'product_id'=>'product-refund','quantity'=>3,'unit_price_atomic'=>3300,'line_total_atomic'=>9900,'currency'=>'IDR','currency_scale'=>0],
    ['tenant_id'=>'tenant-a','sale_id'=>$saleLegacy,'line_no'=>1,'product_id'=>'product-legacy','quantity'=>1,'unit_price_atomic'=>5000,'line_total_atomic'=>5000,'currency'=>'IDR','currency_scale'=>0],
]);

$voidB = 'void-'.str_repeat('b', 24);
$voidC = 'void-'.str_repeat('c', 24);
$connection->table('oneqay_pos_sale_voids')->insert([
    ['tenant_id'=>'tenant-a','sale_id'=>$saleVoided,'void_id'=>$voidB,'organization_id'=>'org-a','outlet_id'=>'outlet-a','reversed_atomic'=>1250,'currency'=>'USD','currency_scale'=>2,'tender_category'=>'MANUAL_EXTERNAL','evidence_mode'=>'FULL_SALE_VOID','voided_at_unix'=>1700000510],
    ['tenant_id'=>'tenant-a','sale_id'=>$saleRefunded,'void_id'=>$voidC,'organization_id'=>'org-a','outlet_id'=>'outlet-a','reversed_atomic'=>9900,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'FULL_SALE_VOID','voided_at_unix'=>1700000410],
]);
$connection->table('oneqay_pos_sale_cash_refunds')->insert([
    ['tenant_id'=>'tenant-a','sale_id'=>$saleRefunded,'refund_id'=>'refund-'.str_repeat('c',24),'void_id'=>$voidC,'organization_id'=>'org-a','outlet_id'=>'outlet-a','refunded_atomic'=>9900,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'FULL_CASH_REFUND','refunded_at_unix'=>1700000420],
]);

$context = PosExecutionContext::fromVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('operator-device'),
));

$repository = new LaravelPosSaleHistoryWorkspaceRepository($connection, true, 'ci', true, true);
$snapshot = $repository->read($context, null);
$assert(count($snapshot->recentSales()) === 4, 'Sprint160 sale history leaked foreign tenant or organization sales.');
$byId = [];
foreach ($snapshot->recentSales() as $sale) { $byId[$sale['sale_id']] = $sale; }
$assert($byId[$saleCompleted]['state'] === 'COMPLETED', 'Sprint160 completed state changed.');
$assert($byId[$saleVoided]['state'] === 'VOIDED', 'Sprint160 void state changed.');
$assert($byId[$saleRefunded]['state'] === 'REFUNDED', 'Sprint160 refund state changed.');
$assert($byId[$saleVoided]['total_atomic'] === '1250' && $byId[$saleVoided]['currency'] === 'USD' && $byId[$saleVoided]['scale'] === 2, 'Sprint160 history lost currency or scale fidelity.');
$assert($byId[$saleLegacy]['shift_id'] === null, 'Sprint160 must preserve legitimate legacy unbound shift evidence.');

$selected = $repository->read($context, $saleCompleted);
$receipt = $selected->selectedReceipt();
$assert($receipt !== null && $receipt['sale_id'] === $saleCompleted, 'Sprint160 exact sale lookup did not return its scoped receipt.');
$assert($receipt['total_atomic'] === '18000' && $receipt['change_atomic'] === '2000', 'Sprint160 receipt lost server-authoritative atomic values.');
$assert(count($receipt['lines']) === 2, 'Sprint160 receipt line cardinality changed.');
$assert($receipt['lines'][0]['product_id'] === 'product-alpha' && $receipt['lines'][0]['line_total_atomic'] === '10000', 'Sprint160 receipt line identity or total changed.');
$assert($receipt['lines'][1]['line_no'] === 2 && $receipt['lines'][1]['line_total_atomic'] === '8000', 'Sprint160 receipt line order changed.');

$foreign = $repository->read($context, $saleForeignOrg);
$assert($foreign->selectedReceipt() === null, 'Sprint160 exact lookup leaked a foreign organization receipt.');

try {
    $repository->read($context, 'sale-invalid');
    $assert(false, 'Sprint160 accepted a malformed canonical sale identifier.');
} catch (PosTransactionViolation) {
    // Expected closed identifier boundary.
}

foreach ([
    [false, 'ci', true, true],
    [true, 'production', true, true],
    [true, 'ci', false, true],
    [true, 'ci', true, false],
] as [$persistence, $runtime, $reporting, $history]) {
    try {
        (new LaravelPosSaleHistoryWorkspaceRepository($connection, $persistence, $runtime, $reporting, $history))->read($context, null);
        $assert(false, 'Sprint160 accepted a disallowed persistence/runtime/reporting/history state.');
    } catch (PosTransactionViolation) {
        // Expected fail-closed feature/runtime boundary.
    }
}

$connection->table('oneqay_pos_sale_lines')
    ->where('tenant_id', 'tenant-a')->where('sale_id', $saleCompleted)->where('line_no', 2)
    ->update(['line_total_atomic' => 7999]);
try {
    $repository->read($context, $saleCompleted);
    $assert(false, 'Sprint160 accepted receipt lines whose sum no longer equals the canonical sale total.');
} catch (PosTransactionViolation) {
    // Expected receipt-integrity boundary.
}
$connection->table('oneqay_pos_sale_lines')
    ->where('tenant_id', 'tenant-a')->where('sale_id', $saleCompleted)->where('line_no', 2)
    ->update(['line_total_atomic' => 8000]);

$connection->table('oneqay_pos_sale_cash_refunds')->insert([
    'tenant_id'=>'tenant-a','sale_id'=>$saleCompleted,'refund_id'=>'refund-'.str_repeat('a',24),'void_id'=>'void-'.str_repeat('a',24),'organization_id'=>'org-a','outlet_id'=>'outlet-a','refunded_atomic'=>18000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'FULL_CASH_REFUND','refunded_at_unix'=>1700000610,
]);
try {
    $repository->read($context, null);
    $assert(false, 'Sprint160 accepted refund evidence without canonical void evidence.');
} catch (PosTransactionViolation) {
    // Expected correction evidence-integrity boundary.
}
$connection->table('oneqay_pos_sale_cash_refunds')->where('sale_id', $saleCompleted)->delete();

$connection->table('oneqay_pos_sale_voids')->where('sale_id', $saleVoided)->update(['organization_id' => 'org-corrupt']);
try {
    $repository->read($context, null);
    $assert(false, 'Sprint160 accepted correction evidence outside the verified organization scope.');
} catch (PosTransactionViolation) {
    // Expected correction scope-integrity boundary.
}

$databaseManager->disconnect('s160_history');
$databaseManager->purge('s160_history');
@unlink($databasePath);

fwrite(STDOUT, "Sprint160 POS sale history workspace regression passed.\n");
