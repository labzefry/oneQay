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
use App\Infrastructure\Pos\LaravelPosOperationalSalesSummaryRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('r', 32));
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

$databasePath = sys_get_temp_dir().'/oneqay-s156-reporting-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint156 disposable reporting database could not be created.');

$app['config']->set('database.default', 's156_reporting');
$app['config']->set('database.connections.s156_reporting', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => true,
]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s156_reporting');
$connection = $databaseManager->connection('s156_reporting');

$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, completed_at_unix INTEGER NOT NULL, total_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, refund_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL)');

$connection->table('oneqay_pos_sales')->insert([
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-a1', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'completed_at_unix' => 100, 'total_atomic' => 10000, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'CASH'],
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-a2', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'completed_at_unix' => 200, 'total_atomic' => 5000, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'MANUAL_EXTERNAL'],
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-a3', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'completed_at_unix' => 300, 'total_atomic' => 1234, 'currency' => 'USD', 'currency_scale' => 2, 'tender_category' => 'CASH'],
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-other-outlet', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-b', 'completed_at_unix' => 400, 'total_atomic' => 999999, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'CASH'],
    ['tenant_id' => 'tenant-b', 'sale_id' => 'sale-other-tenant', 'organization_id' => 'org-b', 'outlet_id' => 'outlet-a', 'completed_at_unix' => 500, 'total_atomic' => 888888, 'currency' => 'IDR', 'currency_scale' => 0, 'tender_category' => 'CASH'],
]);
$connection->table('oneqay_pos_sale_voids')->insert([
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-a1', 'void_id' => 'void-a1', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a'],
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-a2', 'void_id' => 'void-a2', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a'],
    ['tenant_id' => 'tenant-b', 'sale_id' => 'sale-other-tenant', 'void_id' => 'void-b1', 'organization_id' => 'org-b', 'outlet_id' => 'outlet-a'],
]);
$connection->table('oneqay_pos_sale_cash_refunds')->insert([
    ['tenant_id' => 'tenant-a', 'sale_id' => 'sale-a1', 'refund_id' => 'refund-a1', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a'],
]);

$verified = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('device-a'),
);
$context = PosExecutionContext::fromVerified($verified);
$repository = new LaravelPosOperationalSalesSummaryRepository($connection, true, 'ci', true);
$summary = $repository->summarize($context);

$assert($summary->tenantId() === 'tenant-a', 'Sprint156 summary tenant scope changed.');
$assert($summary->outletId() === 'outlet-a', 'Sprint156 summary outlet scope changed.');
$assert($summary->completedSales() === 3, 'Sprint156 reporting leaked another tenant or outlet into completed count.');
$assert($summary->voidedSales() === 2, 'Sprint156 void counter is not tenant/outlet scoped.');
$assert($summary->cashRefundedSales() === 1, 'Sprint156 refund counter is not tenant/outlet scoped.');
$assert($summary->grossTotals() === [
    ['currency' => 'IDR', 'scale' => 0, 'gross_atomic' => 15000],
    ['currency' => 'USD', 'scale' => 2, 'gross_atomic' => 1234],
], 'Sprint156 gross reporting must preserve currency boundaries and exclude foreign scope.');

$recent = $summary->recentSales();
$assert(count($recent) === 3, 'Sprint156 recent sales must remain scoped to the selected outlet.');
$assert($recent[0]['sale_id'] === 'sale-a3' && $recent[0]['state'] === 'COMPLETED', 'Sprint156 latest completed state changed.');
$assert($recent[1]['sale_id'] === 'sale-a2' && $recent[1]['state'] === 'VOIDED', 'Sprint156 voided state changed.');
$assert($recent[2]['sale_id'] === 'sale-a1' && $recent[2]['state'] === 'REFUNDED', 'Sprint156 refunded state must take precedence over voided state.');

foreach ([[true, 'production', true], [true, 'ci', false], [false, 'ci', true]] as [$persistence, $runtime, $feature]) {
    try {
        (new LaravelPosOperationalSalesSummaryRepository($connection, $persistence, $runtime, $feature))->summarize($context);
        $assert(false, 'Sprint156 reporting accepted a disallowed runtime/feature/persistence state.');
    } catch (PosTransactionViolation) {
        // Expected fail-closed boundary.
    }
}

$databaseManager->disconnect('s156_reporting');
$databaseManager->purge('s156_reporting');
@unlink($databasePath);

fwrite(STDOUT, "Sprint156 POS operational reporting regression passed.\n");