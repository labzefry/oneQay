<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\OpenShift;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftOpeningCommand;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('z', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_OPENING_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint48 JRN-005 shift opening regression failed: '.$case);
    }
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
    .DIRECTORY_SEPARATOR.'oneqay-s48-shift-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'shift.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 's48_shift');
$app['config']->set('database.connections.s48_shift', [
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
$app['config']->set('oneqay.pos_shift_opening.enabled', true);

$manager = $app->make('db');
$manager->purge('s48_shift');
$manager->setDefaultConnection('s48_shift');
$connection = $manager->connection('s48_shift');
$connection->getPdo();

$migrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($migrations);
$assert(count($migrations) === 18, 'migration set count');
for ($index = 1; $index <= 18; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $assert(
        count(array_filter(
            $migrations,
            static fn (string $file): bool => str_starts_with($file, $prefix),
        )) === 1,
        'migration #'.$index.' exact',
    );
}
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
foreach ([
    ['tenant-alpha', 'shift-admin-alpha'],
    ['tenant-alpha', 'no-permission-alpha'],
    ['tenant-beta', 'shift-admin-beta'],
] as [$tenant, $identity]) {
    $connection->table('oneqay_identities')->insert([
        'tenant_id' => $tenant,
        'id' => $identity,
    ]);
}
foreach ([
    ['tenant-alpha', 'organization-alpha'],
    ['tenant-beta', 'organization-beta'],
] as [$tenant, $organization]) {
    $connection->table('oneqay_organizations')->insert([
        'tenant_id' => $tenant,
        'id' => $organization,
    ]);
}
foreach ([
    ['tenant-alpha', 'shift-admin-alpha', 'organization-alpha'],
    ['tenant-alpha', 'no-permission-alpha', 'organization-alpha'],
    ['tenant-beta', 'shift-admin-beta', 'organization-beta'],
] as [$tenant, $identity, $organization]) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => $tenant,
        'identity_id' => $identity,
        'organization_id' => $organization,
    ]);
}
foreach ([
    ['tenant-alpha', 'outlet-alpha', 'organization-alpha'],
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
    ['tenant-alpha', 'device-alpha-2', 'organization-alpha', 'outlet-alpha'],
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
        'identity_id' => 'shift-admin-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => 'outlet-alpha',
    ],
    [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => 'no-permission-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => 'outlet-alpha',
    ],
    [
        'tenant_id' => 'tenant-beta',
        'identity_id' => 'shift-admin-beta',
        'organization_id' => 'organization-beta',
        'outlet_id' => 'outlet-beta',
    ],
]);

$connection->table('oneqay_roles')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'shift-role'],
    ['tenant_id' => 'tenant-beta', 'id' => 'shift-role'],
]);
$connection->table('oneqay_role_permissions')->insert([
    [
        'tenant_id' => 'tenant-alpha',
        'role_id' => 'shift-role',
        'permission_id' => PosPermission::OPEN_SHIFT,
    ],
    [
        'tenant_id' => 'tenant-beta',
        'role_id' => 'shift-role',
        'permission_id' => PosPermission::OPEN_SHIFT,
    ],
]);
$connection->table('oneqay_outlet_role_assignments')->insert([
    [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => 'shift-admin-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => 'outlet-alpha',
        'role_id' => 'shift-role',
    ],
    [
        'tenant_id' => 'tenant-beta',
        'identity_id' => 'shift-admin-beta',
        'organization_id' => 'organization-beta',
        'outlet_id' => 'outlet-beta',
        'role_id' => 'shift-role',
    ],
]);

$app->forgetScopedInstances();
$contexts = $app->make(OrganizationalContextStore::class);
$contexts->setVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('shift-admin-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha'),
));

$shifts = $app->make(OpenShift::class);
$firstCommand = new ShiftOpeningCommand('shift-operation-alpha-0001');
$first = $shifts->open($firstCommand, 'correlation-shift-alpha-0001');

$assert(strlen($first->shiftId()) === 32, 'server-owned shift id length');
$assert($first->tenantId() === 'tenant-alpha', 'verified tenant result');
$assert($first->outletId() === 'outlet-alpha', 'verified outlet result');
$assert($first->deviceId() === 'device-alpha', 'verified register context result');
$assert($first->active(), 'first shift active');
$assert($first->openedAtUnix() > 0, 'server-owned open time');

$firstRow = $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'shift-operation-alpha-0001')
    ->first();
$assert($firstRow !== null, 'shift evidence persisted');
$assert((string) $firstRow->actor_identity_id === 'shift-admin-alpha', 'verified actor persisted');
$assert((string) $firstRow->organization_id === 'organization-alpha', 'verified organization persisted');
$assert((string) $firstRow->outlet_id === 'outlet-alpha', 'verified outlet persisted');
$assert((string) $firstRow->device_id === 'device-alpha', 'verified device persisted');
$assert((int) $firstRow->active_slot === 1, 'server-owned active slot');

$replay = $shifts->open($firstCommand, 'correlation-shift-alpha-replay');
$assert($replay->shiftId() === $first->shiftId(), 'exact replay shift identity');
$assert(
    $replay->correlationId() === 'correlation-shift-alpha-0001',
    'replay returns original correlation evidence',
);
$assert(
    $replay->openedAtUnix() === $first->openedAtUnix(),
    'replay returns original opened-at evidence',
);
$assert(
    $connection->table('oneqay_pos_shifts')
        ->where('tenant_id', 'tenant-alpha')
        ->where('operation_id', 'shift-operation-alpha-0001')
        ->count() === 1,
    'exact replay no second shift',
);

try {
    $shifts->open(
        new ShiftOpeningCommand('shift-operation-alpha-0002'),
        'correlation-shift-alpha-0002',
    );
    $assert(false, 'second active shift on same register context accepted');
} catch (PosTransactionViolation) {}

$contexts->setVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('shift-admin-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha-2'),
));

try {
    $shifts->open($firstCommand, 'correlation-shift-alpha-context-conflict');
    $assert(false, 'same operation reused under different device context');
} catch (PosTransactionViolation) {}

$secondDevice = $shifts->open(
    new ShiftOpeningCommand('shift-operation-alpha-device2-0001'),
    'correlation-shift-alpha-device2-0001',
);
$assert($secondDevice->deviceId() === 'device-alpha-2', 'second device independently opened');
$assert(
    $connection->table('oneqay_pos_shifts')
        ->where('tenant_id', 'tenant-alpha')
        ->where('outlet_id', 'outlet-alpha')
        ->where('active_slot', 1)
        ->count() === 2,
    'independent device-backed register contexts',
);

$contexts->setVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('shift-admin-beta'),
    TenantId::fromString('tenant-beta'),
    OrganizationId::fromString('organization-beta'),
    OutletId::fromString('outlet-beta'),
    DeviceId::fromString('device-beta'),
));
$beta = $shifts->open(
    new ShiftOpeningCommand('shift-operation-alpha-0001'),
    'correlation-shift-beta-0001',
);
$assert($beta->tenantId() === 'tenant-beta', 'same operation id isolated by tenant');
$assert($beta->deviceId() === 'device-beta', 'beta device binding');
$assert(
    $connection->table('oneqay_pos_shifts')->where('tenant_id', 'tenant-beta')->count() === 1,
    'beta shift isolated',
);
$assert(
    $connection->table('oneqay_pos_shifts')->where('tenant_id', 'tenant-alpha')->count() === 2,
    'alpha shift evidence untouched by beta',
);

$contexts->setVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('no-permission-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha'),
));
try {
    $shifts->open(
        new ShiftOpeningCommand('shift-operation-alpha-denied-0001'),
        'correlation-shift-alpha-denied',
    );
    $assert(false, 'no-permission actor opened shift');
} catch (DurableAuthorizationViolation) {}

$constructor = new ReflectionMethod(ShiftOpeningCommand::class, '__construct');
$parameters = array_map(
    static fn (ReflectionParameter $parameter): string => $parameter->getName(),
    $constructor->getParameters(),
);
$assert($parameters === ['operationId'], 'command accepts only operation id');

$assert(Route::has('pos.shifts.open'), 'armed shift route absent');
$route = Route::getRoutes()->getByName('pos.shifts.open');
$assert($route !== null, 'shift route unresolved');
$middleware = $route->gatherMiddleware();
$assert(in_array('session.active', $middleware, true), 'active-session middleware missing');
$assert(
    in_array(RequirePosSessionContextMiddleware::class, $middleware, true),
    'verified POS context middleware missing',
);

$manager->disconnect('s48_shift');
$manager->purge('s48_shift');
@unlink($db);
@rmdir($workspace);

echo "Sprint48 JRN-005 shift opening regression passed.\n";
