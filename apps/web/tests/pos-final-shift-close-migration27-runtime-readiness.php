<?php

declare(strict_types=1);

use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\CloseShift;
use App\Delivery\Http\Pos\PosShiftCloseController;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('r', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_CLOSE_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint100 Final Shift Close migration27 readiness failed: '.$case);
    }
};

$dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s100-final-close-'.getmypid();
@mkdir($dir, 0700, true);
$db = $dir.DIRECTORY_SEPARATOR.'pos.sqlite';
$assert(touch($db), 'sqlite create');

$app['config']->set('database.default', 's100-final-close');
$app['config']->set('database.connections.s100-final-close', [
    'driver' => 'sqlite',
    'database' => $db,
    'prefix' => '',
    'foreign_key_constraints' => false,
]);
$app['config']->set('database.oneqay_persistence_enabled', true);
$app['config']->set('oneqay.runtime_class', 'ci');

$manager = $app->make('db');
$manager->purge('s100-final-close');
$manager->setDefaultConnection('s100-final-close');
$connection = $manager->connection('s100-final-close');
$connection->getPdo();
$connection->statement('PRAGMA foreign_keys = OFF');

foreach ([
    'CREATE TABLE oneqay_identities (tenant_id TEXT NOT NULL, id TEXT NOT NULL, PRIMARY KEY (tenant_id, id))',
    'CREATE TABLE oneqay_organizations (tenant_id TEXT NOT NULL, id TEXT NOT NULL, PRIMARY KEY (tenant_id, id))',
    'CREATE TABLE oneqay_outlets (tenant_id TEXT NOT NULL, id TEXT NOT NULL, PRIMARY KEY (tenant_id, id))',
    'CREATE TABLE oneqay_devices (tenant_id TEXT NOT NULL, id TEXT NOT NULL, PRIMARY KEY (tenant_id, id))',
    'CREATE TABLE oneqay_tenant_role_assignments (tenant_id TEXT NOT NULL, identity_id TEXT NOT NULL, role_id TEXT NOT NULL)',
    'CREATE TABLE oneqay_organization_role_assignments (tenant_id TEXT NOT NULL, identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, role_id TEXT NOT NULL)',
    'CREATE TABLE oneqay_outlet_role_assignments (tenant_id TEXT NOT NULL, identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, role_id TEXT NOT NULL)',
    'CREATE TABLE oneqay_device_role_assignments (tenant_id TEXT NOT NULL, identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, role_id TEXT NOT NULL)',
    'CREATE TABLE oneqay_role_permissions (tenant_id TEXT NOT NULL, role_id TEXT NOT NULL, permission_id TEXT NOT NULL)',
    'CREATE TABLE oneqay_pos_shifts (tenant_id TEXT NOT NULL, shift_id TEXT NOT NULL, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, active_slot INTEGER NULL, PRIMARY KEY (tenant_id, shift_id))',
    'CREATE TABLE oneqay_pos_shift_opening_cash_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, opening_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL, PRIMARY KEY (tenant_id, evidence_id))',
    'CREATE TABLE oneqay_pos_shift_closing_cash_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, operation_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, closing_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, correlation_id TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL, PRIMARY KEY (tenant_id, evidence_id))',
    'CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NULL, total_atomic INTEGER NOT NULL, applied_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, completed_at_unix INTEGER NOT NULL)',
    'CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, void_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, reversed_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL)',
    'CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, refund_id TEXT NOT NULL, sale_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, refunded_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, refunded_at_unix INTEGER NOT NULL)',
    'CREATE TABLE oneqay_pos_cash_variance_review_decision_evidence (tenant_id TEXT NOT NULL, review_evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, closing_cash_evidence_id TEXT NOT NULL, explanation_actor_identity_id TEXT NOT NULL, reviewer_actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, cutoff_at_unix INTEGER NOT NULL, expected_cash_atomic INTEGER NOT NULL, observed_closing_cash_atomic INTEGER NOT NULL, variance_atomic INTEGER NOT NULL, variance_direction TEXT NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, review_outcome TEXT NOT NULL, PRIMARY KEY (tenant_id, review_evidence_id))',
] as $statement) {
    $connection->statement($statement);
}

$migrationPath = $root = dirname(__DIR__);
$migrationPath .= '/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php';
$assert(is_file($migrationPath), 'canonical migration27 source exists');
$migration = require $migrationPath;
$assert(is_object($migration) && method_exists($migration, 'up'), 'migration27 object load');
$migration->up();

$assert(Schema::hasTable('oneqay_pos_shift_close_evidence'), 'migration27 creates close evidence table');

$indexRows = $connection->select("PRAGMA index_list('oneqay_pos_shift_close_evidence')");
$indexNames = array_map(static fn (object $row): string => (string) ($row->name ?? ''), $indexRows);
$assert(in_array('uq_pos_shift_close_operation', $indexNames, true), 'operation unique index exists');
$assert(in_array('uq_pos_shift_close_shift', $indexNames, true), 'shift unique index exists');

$triggerRows = $connection->select("SELECT name FROM sqlite_master WHERE type = 'trigger' AND tbl_name = 'oneqay_pos_shift_close_evidence'");
$triggerNames = array_map(static fn (object $row): string => (string) ($row->name ?? ''), $triggerRows);
$assert(in_array('chk_pos_shift_close_variance_review_insert', $triggerNames, true), 'insert variance/review trigger exists');
$assert(in_array('chk_pos_shift_close_variance_review_update', $triggerNames, true), 'update variance/review trigger exists');

$invalidRejected = false;
try {
    $connection->table('oneqay_pos_shift_close_evidence')->insert([
        'tenant_id' => 'tenant-invalid',
        'evidence_id' => 'shiftclose-invalid',
        'operation_id' => 'operation-invalid',
        'payload_fingerprint' => str_repeat('a', 64),
        'shift_id' => 'shift-invalid',
        'opening_cash_evidence_id' => 'opening-invalid',
        'closing_cash_evidence_id' => 'closing-invalid',
        'closer_actor_identity_id' => 'closer-invalid',
        'organization_id' => 'organization-invalid',
        'outlet_id' => 'outlet-invalid',
        'device_id' => 'device-invalid',
        'cutoff_at_unix' => 100,
        'expected_cash_atomic' => 1000,
        'observed_closing_cash_atomic' => 1000,
        'variance_atomic' => 0,
        'variance_direction' => 'MATCH',
        'currency' => 'IDR',
        'currency_scale' => 0,
        'review_evidence_id' => 'review-invalid',
        'review_outcome' => 'REVIEW_ACCEPTED',
        'correlation_id' => 'correlation-invalid',
        'closed_at_unix' => 101,
    ]);
} catch (Throwable) {
    $invalidRejected = true;
}
$assert($invalidRejected, 'migration27 rejects invalid MATCH review state');
$assert($connection->table('oneqay_pos_shift_close_evidence')->count() === 0, 'invalid insert writes no row');

$tenant = 'tenant-alpha';
$identity = 'identity-closer';
$organization = 'organization-alpha';
$outlet = 'outlet-alpha';
$device = 'device-alpha';
$role = 'role-shift-closer';
$shift = 'shift-alpha';
$opening = 'opening-alpha';
$closing = 'closing-alpha';

foreach ([
    ['table' => 'oneqay_identities', 'row' => ['tenant_id' => $tenant, 'id' => $identity]],
    ['table' => 'oneqay_identities', 'row' => ['tenant_id' => $tenant, 'id' => 'identity-opener']],
    ['table' => 'oneqay_organizations', 'row' => ['tenant_id' => $tenant, 'id' => $organization]],
    ['table' => 'oneqay_outlets', 'row' => ['tenant_id' => $tenant, 'id' => $outlet]],
    ['table' => 'oneqay_devices', 'row' => ['tenant_id' => $tenant, 'id' => $device]],
] as $fixture) {
    $connection->table($fixture['table'])->insert($fixture['row']);
}

$connection->table('oneqay_device_role_assignments')->insert([
    'tenant_id' => $tenant,
    'identity_id' => $identity,
    'organization_id' => $organization,
    'outlet_id' => $outlet,
    'device_id' => $device,
    'role_id' => $role,
]);
$connection->table('oneqay_role_permissions')->insert([
    'tenant_id' => $tenant,
    'role_id' => $role,
    'permission_id' => 'pos.shift.close',
]);
$connection->table('oneqay_pos_shifts')->insert([
    'tenant_id' => $tenant,
    'shift_id' => $shift,
    'actor_identity_id' => 'identity-opener',
    'organization_id' => $organization,
    'outlet_id' => $outlet,
    'device_id' => $device,
    'active_slot' => 1,
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

$verified = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString($identity),
    TenantId::fromString($tenant),
    OrganizationId::fromString($organization),
    OutletId::fromString($outlet),
    DeviceId::fromString($device),
);
$contexts = $app->make(OrganizationalContextStore::class);
$controller = new PosShiftCloseController($app->make(CloseShift::class));
$contexts->setVerified($verified);
$request = Request::create('/pos/shifts/close', 'POST', ['operation_id' => 'close-operation-alpha']);
$request->attributes->set('oneqay.correlation_id', 'correlation-close-alpha');
try {
    $response = $controller($request);
} finally {
    $contexts->clear();
}

$body = $response->getData(true);
$assert($response->getStatusCode() === 200, 'current runtime closes against migration27 schema');
$assert(($body['status'] ?? null) === 'closed', 'runtime returns closed status');
$assert(($body['reconciliation']['variance_direction'] ?? null) === 'MATCH', 'migration27 runtime MATCH result');
$assert($connection->table('oneqay_pos_shift_close_evidence')->count() === 1, 'migration27 stores one durable close row');
$assert($connection->table('oneqay_pos_shifts')->where('shift_id', $shift)->value('active_slot') === null, 'runtime atomically releases active slot');

$stored = $connection->table('oneqay_pos_shift_close_evidence')->first();
$assert(is_object($stored), 'durable close row readable');
$assert(($stored->operation_id ?? null) === 'close-operation-alpha', 'operation id persisted');
$assert(($stored->variance_direction ?? null) === 'MATCH', 'variance direction persisted');
$assert(($stored->review_evidence_id ?? 'sentinel') === null, 'MATCH stores no review evidence');
$assert(($stored->review_outcome ?? 'sentinel') === null, 'MATCH stores no review outcome');

$downDenied = false;
try {
    $migration->down();
} catch (LogicException) {
    $downDenied = true;
}
$assert($downDenied, 'migration27 rollback remains unauthorized');
$assert(Schema::hasTable('oneqay_pos_shift_close_evidence'), 'denied rollback preserves table');

@unlink($db);
@rmdir($dir);

echo "Sprint100 Final Shift Close migration27 runtime readiness regression passed.\n";
