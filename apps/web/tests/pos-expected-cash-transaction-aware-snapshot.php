<?php

declare(strict_types=1);

use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftClosingCashResult;
use App\Domain\Pos\Money;
use App\Infrastructure\Pos\LaravelExpectedCashRepository;
use App\Infrastructure\Pos\LaravelExpectedCashSnapshotReader;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('q', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
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
        throw new RuntimeException('Sprint91 transaction-aware snapshot regression failed: '.$case);
    }
};
$expectViolation = static function (callable $callback, string $case) use ($assert): void {
    try {
        $callback();
        $assert(false, $case.' accepted');
    } catch (PosTransactionViolation) {
    }
};

$dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s91-expected-snapshot-'.getmypid();
@mkdir($dir, 0700, true);
$db = $dir.DIRECTORY_SEPARATOR.'pos.sqlite';
$assert(touch($db), 'sqlite create');

$app['config']->set('database.default', 's91-expected-snapshot');
$app['config']->set('database.connections.s91-expected-snapshot', [
    'driver' => 'sqlite',
    'database' => $db,
    'prefix' => '',
    'foreign_key_constraints' => true,
]);
$manager = $app->make('db');
$manager->purge('s91-expected-snapshot');
$manager->setDefaultConnection('s91-expected-snapshot');
$connection = $manager->connection('s91-expected-snapshot');
$connection->getPdo();

$connection->statement('CREATE TABLE oneqay_pos_shifts (tenant_id TEXT NOT NULL, shift_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_shift_opening_cash_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, opening_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_shift_closing_cash_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, operation_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, closing_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, correlation_id TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NULL, total_atomic INTEGER NOT NULL, applied_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, completed_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, void_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, reversed_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, refund_id TEXT NOT NULL, sale_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, refunded_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, refunded_at_unix INTEGER NOT NULL)');

$tenant = 'tenant-alpha';
$organization = 'organization-alpha';
$outlet = 'outlet-alpha';
$device = 'device-alpha';
$shift = 'shift-alpha';
$opening = 'opening-alpha';
$closing = 'closing-alpha';

$connection->table('oneqay_pos_shifts')->insert([
    'tenant_id' => $tenant,
    'shift_id' => $shift,
    'organization_id' => $organization,
    'outlet_id' => $outlet,
    'device_id' => $device,
]);
$connection->table('oneqay_pos_shift_opening_cash_evidence')->insert([
    'tenant_id' => $tenant,
    'evidence_id' => $opening,
    'shift_id' => $shift,
    'organization_id' => $organization,
    'outlet_id' => $outlet,
    'device_id' => $device,
    'opening_cash_atomic' => 1000,
    'currency' => 'IDR',
    'currency_scale' => 0,
    'evidence_mode' => 'OPERATOR_OBSERVED_OPENING_CASH',
    'recorded_at_unix' => 100,
]);
$connection->table('oneqay_pos_shift_closing_cash_evidence')->insert([
    'tenant_id' => $tenant,
    'evidence_id' => $closing,
    'opening_cash_evidence_id' => $opening,
    'shift_id' => $shift,
    'operation_id' => 'closing-operation-alpha',
    'organization_id' => $organization,
    'outlet_id' => $outlet,
    'device_id' => $device,
    'closing_cash_atomic' => 1000,
    'currency' => 'IDR',
    'currency_scale' => 0,
    'evidence_mode' => 'OPERATOR_OBSERVED_CLOSING_CASH',
    'correlation_id' => 'closing-correlation-alpha',
    'recorded_at_unix' => 200,
]);

$closingEvidence = new ShiftClosingCashResult(
    $closing,
    $opening,
    $shift,
    'closing-operation-alpha',
    $tenant,
    $outlet,
    $device,
    Money::fromAtomicUnits(1000, 'IDR', 0),
    'OPERATOR_OBSERVED_CLOSING_CASH',
    'closing-correlation-alpha',
    200,
);

$reader = new LaravelExpectedCashSnapshotReader($connection);
$repository = new LaravelExpectedCashRepository($connection);

$expectViolation(
    fn () => $reader->deriveFrom($closingEvidence),
    'snapshot reader outside transaction',
);

$inside = $connection->transaction(fn () => $reader->deriveFrom($closingEvidence), 1);
$assert($inside->expectedCash()->atomicUnits() === 1000, 'reader opening-only result');
$assert($connection->transactionLevel() === 0, 'outer transaction returned to zero');

$standalone = $repository->deriveFrom($closingEvidence);
$assert($standalone->expectedCash()->canonicalFingerprintPart() === $inside->expectedCash()->canonicalFingerprintPart(), 'standalone wrapper parity');

$connection->transaction(function () use ($repository, $closingEvidence, $expectViolation): void {
    $expectViolation(
        fn () => $repository->deriveFrom($closingEvidence),
        'standalone repository nested transaction',
    );
}, 1);

$connection->table('oneqay_pos_sales')->insert([
    'tenant_id' => $tenant,
    'sale_id' => 'sale-alpha',
    'organization_id' => $organization,
    'outlet_id' => $outlet,
    'device_id' => $device,
    'shift_id' => $shift,
    'total_atomic' => 250,
    'applied_atomic' => 250,
    'currency' => 'IDR',
    'currency_scale' => 0,
    'tender_category' => 'CASH',
    'evidence_mode' => 'CASH_COUNTED',
    'completed_at_unix' => 150,
]);
$saleResult = $connection->transaction(fn () => $reader->deriveFrom($closingEvidence), 1);
$assert($saleResult->expectedCash()->atomicUnits() === 1250, 'reader cash sale arithmetic preserved');

$connection->table('oneqay_pos_sale_voids')->insert([
    'tenant_id' => $tenant,
    'void_id' => 'void-alpha',
    'sale_id' => 'sale-alpha',
    'organization_id' => $organization,
    'outlet_id' => $outlet,
    'reversed_atomic' => 250,
    'currency' => 'IDR',
    'currency_scale' => 0,
    'tender_category' => 'CASH',
    'evidence_mode' => 'FULL_SALE_VOID',
]);
$voidOnly = $connection->transaction(fn () => $reader->deriveFrom($closingEvidence), 1);
$assert($voidOnly->expectedCash()->atomicUnits() === 1250, 'void-only arithmetic preserved');

$connection->table('oneqay_pos_sale_cash_refunds')->insert([
    'tenant_id' => $tenant,
    'refund_id' => 'refund-alpha',
    'sale_id' => 'sale-alpha',
    'void_id' => 'void-alpha',
    'organization_id' => $organization,
    'outlet_id' => $outlet,
    'refunded_atomic' => 250,
    'currency' => 'IDR',
    'currency_scale' => 0,
    'tender_category' => 'CASH',
    'evidence_mode' => 'FULL_CASH_REFUND',
    'refunded_at_unix' => 170,
]);
$refunded = $connection->transaction(fn () => $reader->deriveFrom($closingEvidence), 1);
$assert($refunded->expectedCash()->atomicUnits() === 1000, 'full refund arithmetic preserved');

$repeat = $connection->transaction(fn () => $reader->deriveFrom($closingEvidence), 1);
$assert($repeat->expectedCash()->canonicalFingerprintPart() === $refunded->expectedCash()->canonicalFingerprintPart(), 'transaction-aware deterministic repeat');

@unlink($db);
@rmdir($dir);

echo "Sprint91 transaction-aware expected-cash snapshot regression passed.\n";
