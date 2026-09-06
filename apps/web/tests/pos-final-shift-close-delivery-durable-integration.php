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

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('d', 32)),
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
        throw new RuntimeException('Sprint98 Final Shift Close delivery integration failed: '.$case);
    }
};

$dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s98-final-close-'.getmypid();
@mkdir($dir, 0700, true);
$db = $dir.DIRECTORY_SEPARATOR.'pos.sqlite';
$assert(touch($db), 'sqlite create');

$app['config']->set('database.default', 's98-final-close');
$app['config']->set('database.connections.s98-final-close', [
    'driver' => 'sqlite',
    'database' => $db,
    'prefix' => '',
    'foreign_key_constraints' => false,
]);
$app['config']->set('database.oneqay_persistence_enabled', true);
$app['config']->set('oneqay.runtime_class', 'ci');

$manager = $app->make('db');
$manager->purge('s98-final-close');
$manager->setDefaultConnection('s98-final-close');
$connection = $manager->connection('s98-final-close');
$connection->getPdo();

foreach ([
    'CREATE TABLE oneqay_tenant_role_assignments (tenant_id TEXT NOT NULL, identity_id TEXT NOT NULL, role_id TEXT NOT NULL)',
    'CREATE TABLE oneqay_organization_role_assignments (tenant_id TEXT NOT NULL, identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, role_id TEXT NOT NULL)',
    'CREATE TABLE oneqay_outlet_role_assignments (tenant_id TEXT NOT NULL, identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, role_id TEXT NOT NULL)',
    'CREATE TABLE oneqay_device_role_assignments (tenant_id TEXT NOT NULL, identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, role_id TEXT NOT NULL)',
    'CREATE TABLE oneqay_role_permissions (tenant_id TEXT NOT NULL, role_id TEXT NOT NULL, permission_id TEXT NOT NULL)',
    'CREATE TABLE oneqay_pos_shifts (tenant_id TEXT NOT NULL, shift_id TEXT NOT NULL, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, active_slot INTEGER NULL)',
    'CREATE TABLE oneqay_pos_shift_opening_cash_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, opening_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL)',
    'CREATE TABLE oneqay_pos_shift_closing_cash_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, operation_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, closing_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, correlation_id TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL)',
    'CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NULL, total_atomic INTEGER NOT NULL, applied_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, completed_at_unix INTEGER NOT NULL)',
    'CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, void_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, reversed_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL)',
    'CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, refund_id TEXT NOT NULL, sale_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, refunded_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, refunded_at_unix INTEGER NOT NULL)',
    'CREATE TABLE oneqay_pos_cash_variance_review_decision_evidence (tenant_id TEXT NOT NULL, review_evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, closing_cash_evidence_id TEXT NOT NULL, explanation_actor_identity_id TEXT NOT NULL, reviewer_actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, cutoff_at_unix INTEGER NOT NULL, expected_cash_atomic INTEGER NOT NULL, observed_closing_cash_atomic INTEGER NOT NULL, variance_atomic INTEGER NOT NULL, variance_direction TEXT NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, review_outcome TEXT NOT NULL)',
    'CREATE TABLE oneqay_pos_shift_close_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, operation_id TEXT NOT NULL, payload_fingerprint TEXT NOT NULL, shift_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, closing_cash_evidence_id TEXT NOT NULL, closer_actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, cutoff_at_unix INTEGER NOT NULL, expected_cash_atomic INTEGER NOT NULL, observed_closing_cash_atomic INTEGER NOT NULL, variance_atomic INTEGER NOT NULL, variance_direction TEXT NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, review_evidence_id TEXT NULL, review_outcome TEXT NULL, correlation_id TEXT NOT NULL, closed_at_unix INTEGER NOT NULL)',
] as $statement) {
    $connection->statement($statement);
}

$tenant = 'tenant-alpha';
$identity = 'identity-closer';
$organization = 'organization-alpha';
$outlet = 'outlet-alpha';
$device = 'device-alpha';
$role = 'role-shift-closer';
$shift = 'shift-alpha';
$opening = 'opening-alpha';
$closing = 'closing-alpha';

$connection->table('oneqay_device_role_assignments')->insert([
    'tenant_id' => $tenant,
    'identity_id' => $identity,
    'organization_id' => $organization,
    'outlet_id' => $outlet,
    'device_id' => $device,
    'role_id' => $role,
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

$invoke = static function (array $payload, string $correlationId) use ($controller, $contexts, $verified) {
    $contexts->setVerified($verified);
    $request = Request::create('/pos/shifts/close', 'POST', $payload);
    $request->attributes->set('oneqay.correlation_id', $correlationId);

    try {
        return $controller($request);
    } finally {
        $contexts->clear();
    }
};

$denied = $invoke(['operation_id' => 'close-operation-alpha'], 'correlation-denied-alpha');
$deniedBody = $denied->getData(true);
$assert($denied->getStatusCode() === 403, 'permission absent must deny');
$assert(($deniedBody['error']['code'] ?? null) === 'POS_SHIFT_CLOSE_AUTHORIZATION_DENIED', 'permission denial safe envelope');
$assert((int) $connection->table('oneqay_pos_shifts')->where('shift_id', $shift)->value('active_slot') === 1, 'permission denial preserves active shift');
$assert($connection->table('oneqay_pos_shift_close_evidence')->count() === 0, 'permission denial writes no close evidence');

$connection->table('oneqay_role_permissions')->insert([
    'tenant_id' => $tenant,
    'role_id' => $role,
    'permission_id' => 'pos.shift.close',
]);

$success = $invoke(['operation_id' => 'close-operation-alpha'], 'correlation-close-alpha');
$successBody = $success->getData(true);
$assert($success->getStatusCode() === 200, 'explicit durable grant permits close');
$assert(($successBody['status'] ?? null) === 'closed', 'success status');
$assert(($successBody['operation_id'] ?? null) === 'close-operation-alpha', 'operation id preserved');
$assert(($successBody['shift_id'] ?? null) === $shift, 'durable shift id returned');
$assert(($successBody['reconciliation']['variance_direction'] ?? null) === 'MATCH', 'MATCH variance returned');
$assert(($successBody['reconciliation']['variance_atomic'] ?? null) === 0, 'zero variance returned');
$assert(($successBody['correlation_id'] ?? null) === 'correlation-close-alpha', 'correlation id persisted');
$assert($success->headers->get('Cache-Control') === 'no-store, private', 'success cache boundary');
$encodedSuccess = json_encode($successBody, JSON_THROW_ON_ERROR);
foreach (['closer_actor_identity_id', 'opener_actor_identity_id', 'reviewer_actor_identity_id', 'explanation_actor_identity_id'] as $forbidden) {
    $assert(! str_contains($encodedSuccess, $forbidden), 'response hides '.$forbidden);
}
$assert($connection->table('oneqay_pos_shift_close_evidence')->count() === 1, 'exactly one close evidence row');
$assert($connection->table('oneqay_pos_shifts')->where('shift_id', $shift)->value('active_slot') === null, 'successful close releases active slot');

$replay = $invoke(['operation_id' => 'close-operation-alpha'], 'correlation-replay-alpha');
$replayBody = $replay->getData(true);
$assert($replay->getStatusCode() === 200, 'exact operation replay succeeds');
$assert(($replayBody['evidence_id'] ?? null) === ($successBody['evidence_id'] ?? null), 'replay returns original evidence');
$assert(($replayBody['closed_at_unix'] ?? null) === ($successBody['closed_at_unix'] ?? null), 'replay preserves original close timestamp');
$assert(($replayBody['correlation_id'] ?? null) === 'correlation-close-alpha', 'replay preserves original durable correlation');
$assert($connection->table('oneqay_pos_shift_close_evidence')->count() === 1, 'replay does not duplicate evidence');

$second = $invoke(['operation_id' => 'close-operation-second'], 'correlation-close-second');
$secondBody = $second->getData(true);
$assert($second->getStatusCode() === 422, 'different operation after close is safely rejected');
$assert(($secondBody['error']['code'] ?? null) === 'POS_SHIFT_CLOSE_REJECTED', 'transaction rejection safe envelope');
$assert($second->headers->get('Cache-Control') === 'no-store, private', 'rejection cache boundary');
$assert($connection->table('oneqay_pos_shift_close_evidence')->count() === 1, 'second operation writes no evidence');

$extra = $invoke([
    'operation_id' => 'close-operation-extra',
    'shift_id' => $shift,
], 'correlation-close-extra');
$extraBody = $extra->getData(true);
$assert($extra->getStatusCode() === 422, 'authoritative extra field is rejected');
$assert(($extraBody['error']['code'] ?? null) === 'POS_SHIFT_CLOSE_REJECTED', 'extra field safe envelope');
$assert($connection->table('oneqay_pos_shift_close_evidence')->count() === 1, 'extra field writes no evidence');

@unlink($db);
@rmdir($dir);

echo "Sprint98 Final Shift Close durable delivery integration regression passed.\n";
