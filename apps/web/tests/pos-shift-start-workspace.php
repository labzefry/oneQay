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
use App\Infrastructure\Pos\LaravelPosShiftStartWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('c', 32));
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

$databasePath = sys_get_temp_dir().'/oneqay-s158-shift-start-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint158 disposable shift-start database could not be created.');

$app['config']->set('database.default', 's158_shift_start');
$app['config']->set('database.connections.s158_shift_start', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => true,
]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s158_shift_start');
$connection = $databaseManager->connection('s158_shift_start');

$connection->statement('CREATE TABLE oneqay_pos_shifts (tenant_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NOT NULL, active_slot INTEGER NULL, opened_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_shift_opening_cash_evidence (tenant_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NOT NULL, evidence_id TEXT NOT NULL, opening_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL)');

$connection->table('oneqay_pos_shifts')->insert([
    ['tenant_id' => 'tenant-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'device-a', 'shift_id' => 'shift-active-a', 'active_slot' => 1, 'opened_at_unix' => 1789369000],
    ['tenant_id' => 'tenant-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'device-b', 'shift_id' => 'shift-device-b', 'active_slot' => 1, 'opened_at_unix' => 1789369010],
    ['tenant_id' => 'tenant-b', 'organization_id' => 'org-b', 'outlet_id' => 'outlet-a', 'device_id' => 'device-a', 'shift_id' => 'shift-tenant-b', 'active_slot' => 1, 'opened_at_unix' => 1789369020],
]);

$connection->table('oneqay_pos_shift_opening_cash_evidence')->insert([
    ['tenant_id' => 'tenant-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a', 'device_id' => 'device-b', 'shift_id' => 'shift-device-b', 'evidence_id' => 'cashopen-device-b', 'opening_cash_atomic' => 999999, 'currency' => 'IDR', 'currency_scale' => 0, 'evidence_mode' => 'OPERATOR_OBSERVED_OPENING_CASH', 'recorded_at_unix' => 1789369030],
    ['tenant_id' => 'tenant-b', 'organization_id' => 'org-b', 'outlet_id' => 'outlet-a', 'device_id' => 'device-a', 'shift_id' => 'shift-tenant-b', 'evidence_id' => 'cashopen-tenant-b', 'opening_cash_atomic' => 888888, 'currency' => 'IDR', 'currency_scale' => 0, 'evidence_mode' => 'OPERATOR_OBSERVED_OPENING_CASH', 'recorded_at_unix' => 1789369040],
]);

$context = PosExecutionContext::fromVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('device-a'),
));
$repository = new LaravelPosShiftStartWorkspaceRepository($connection, true, 'ci', true, true, true);
$snapshot = $repository->snapshot($context);

$assert($snapshot->tenantId() === 'tenant-a', 'Sprint158 shift-start tenant scope changed.');
$assert($snapshot->organizationId() === 'org-a', 'Sprint158 shift-start organization scope changed.');
$assert($snapshot->outletId() === 'outlet-a', 'Sprint158 shift-start outlet scope changed.');
$assert($snapshot->deviceId() === 'device-a', 'Sprint158 shift-start device scope changed.');
$assert($snapshot->activeShiftId() === 'shift-active-a', 'Sprint158 shift-start must bind active shift to the exact device.');
$assert($snapshot->openedAtUnix() === 1789369000, 'Sprint158 active-shift timestamp changed.');
$assert($snapshot->openingCashEvidenceId() === null, 'Sprint158 shift-start borrowed opening-cash evidence from another scope.');
$assert(! $snapshot->readyForCashier(), 'Sprint158 shift-start must remain resumable until opening cash exists.');

$withoutShift = PosExecutionContext::fromVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('device-c'),
));
$empty = $repository->snapshot($withoutShift);
$assert($empty->activeShiftId() === null, 'Sprint158 shift-start must not borrow another device active shift.');
$assert($empty->openingCashEvidenceId() === null && ! $empty->readyForCashier(), 'Sprint158 no-shift state must remain fail-closed.');

$connection->table('oneqay_pos_shift_opening_cash_evidence')->insert([
    'tenant_id' => 'tenant-a',
    'organization_id' => 'org-a',
    'outlet_id' => 'outlet-a',
    'device_id' => 'device-a',
    'shift_id' => 'shift-active-a',
    'evidence_id' => 'cashopen-exact-a',
    'opening_cash_atomic' => 125050,
    'currency' => 'USD',
    'currency_scale' => 2,
    'evidence_mode' => 'OPERATOR_OBSERVED_OPENING_CASH',
    'recorded_at_unix' => 1789369050,
]);

$ready = $repository->snapshot($context);
$assert($ready->openingCashEvidenceId() === 'cashopen-exact-a', 'Sprint158 exact opening-cash evidence identity changed.');
$assert($ready->openingCash()?->atomicUnits() === 125050, 'Sprint158 opening-cash atomic amount changed.');
$assert($ready->openingCash()?->currency() === 'USD' && $ready->openingCash()?->scale() === 2, 'Sprint158 opening-cash currency/scale boundary changed.');
$assert($ready->openingCashEvidenceMode() === 'OPERATOR_OBSERVED_OPENING_CASH', 'Sprint158 opening-cash evidence mode changed.');
$assert($ready->openingCashRecordedAtUnix() === 1789369050, 'Sprint158 opening-cash evidence timestamp changed.');
$assert($ready->readyForCashier(), 'Sprint158 exact active shift plus opening cash must be cashier-ready.');

foreach ([
    [false, 'ci', true, true, true],
    [true, 'production', true, true, true],
    [true, 'ci', false, true, true],
    [true, 'ci', true, false, true],
    [true, 'ci', true, true, false],
] as [$persistence, $runtime, $workspace, $shiftOpening, $openingCash]) {
    try {
        (new LaravelPosShiftStartWorkspaceRepository(
            $connection,
            $persistence,
            $runtime,
            $workspace,
            $shiftOpening,
            $openingCash,
        ))->snapshot($context);
        $assert(false, 'Sprint158 shift-start accepted a disallowed persistence/runtime/capability state.');
    } catch (PosTransactionViolation) {
        // Expected fail-closed boundary.
    }
}

$databaseManager->disconnect('s158_shift_start');
$databaseManager->purge('s158_shift_start');
@unlink($databasePath);

fwrite(STDOUT, "Sprint158 POS shift-start workspace regression passed.\n");
