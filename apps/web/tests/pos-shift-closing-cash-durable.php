<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\OpenShift;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\RecordShiftOpeningCash;
use App\Application\Pos\RecordShiftClosingCash;
use App\Application\Pos\ShiftOpeningCashCommand;
use App\Application\Pos\ShiftClosingCashCommand;
use App\Application\Pos\ShiftOpeningCommand;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosShiftOpeningCashController;
use App\Delivery\Http\Pos\PosShiftClosingCashController;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Pos\Money;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Pos\LaravelShiftOpeningCashRepository;
use App\Infrastructure\Pos\LaravelShiftClosingCashRepository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('q', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_OPENING_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_OPENING_CASH_EVIDENCE_ENABLED' => 'true',
    'ONEQAY_POS_SHIFT_CLOSING_CASH_EVIDENCE_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint54 shift closing cash regression failed: '.$case);
    }
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
    .DIRECTORY_SEPARATOR.'oneqay-s54-closing-cash-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'closing-cash.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 's54_closing_cash');
$app['config']->set('database.connections.s53_opening_cash', [
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
$app['config']->set('oneqay.pos_shift_opening_cash_evidence.enabled', true);
$app['config']->set('oneqay.pos_shift_closing_cash_evidence.enabled', true);

$manager = $app->make('db');
$manager->purge('s53_opening_cash');
$manager->setDefaultConnection('s53_opening_cash');
$connection = $manager->connection('s53_opening_cash');
$connection->getPdo();

$migrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($migrations);
$assert(count($migrations) === 23, 'migration set count');
for ($index = 1; $index <= 23; $index++) {
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
    ['tenant-alpha', 'cash-admin-alpha'],
    ['tenant-alpha', 'no-permission-alpha'],
    ['tenant-beta', 'cash-admin-beta'],
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
    ['tenant-alpha', 'cash-admin-alpha', 'organization-alpha'],
    ['tenant-alpha', 'no-permission-alpha', 'organization-alpha'],
    ['tenant-beta', 'cash-admin-beta', 'organization-beta'],
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
        'identity_id' => 'cash-admin-alpha',
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
        'identity_id' => 'cash-admin-beta',
        'organization_id' => 'organization-beta',
        'outlet_id' => 'outlet-beta',
    ],
]);

$connection->table('oneqay_roles')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'cash-role'],
    ['tenant_id' => 'tenant-beta', 'id' => 'cash-role'],
]);
foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_role_permissions')->insert([
        [
            'tenant_id' => $tenant,
            'role_id' => 'cash-role',
            'permission_id' => PosPermission::OPEN_SHIFT,
        ],
        [
            'tenant_id' => $tenant,
            'role_id' => 'cash-role',
            'permission_id' => PosPermission::RECORD_SHIFT_OPENING_CASH,
        ],
        [
            'tenant_id' => $tenant,
            'role_id' => 'cash-role',
            'permission_id' => PosPermission::RECORD_SHIFT_CLOSING_CASH,
        ],
    ]);
}
$connection->table('oneqay_outlet_role_assignments')->insert([
    [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => 'cash-admin-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => 'outlet-alpha',
        'role_id' => 'cash-role',
    ],
    [
        'tenant_id' => 'tenant-beta',
        'identity_id' => 'cash-admin-beta',
        'organization_id' => 'organization-beta',
        'outlet_id' => 'outlet-beta',
        'role_id' => 'cash-role',
    ],
]);

$setContext = static function (
    string $identity,
    string $tenant,
    string $organization,
    string $outlet,
    string $device,
) use ($app): OrganizationalContextStore {
    $app->forgetScopedInstances();
    $contexts = $app->make(OrganizationalContextStore::class);
    $contexts->setVerified(new VerifiedOrganizationalContext(
        PlatformIdentityId::fromString($identity),
        TenantId::fromString($tenant),
        OrganizationId::fromString($organization),
        OutletId::fromString($outlet),
        DeviceId::fromString($device),
    ));

    return $contexts;
};

$setContext(
    'cash-admin-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    'device-alpha',
);
$shifts = $app->make(OpenShift::class);
$firstShift = $shifts->open(
    new ShiftOpeningCommand('shift-close-alpha-0001'),
    'correlation-shift-close-alpha-0001',
);

$shiftBefore = (array) $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('shift_id', $firstShift->shiftId())
    ->first();

$setContext(
    'no-permission-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    'device-alpha',
);
try {
    $app->make(RecordShiftClosingCash::class)->record(
        new ShiftClosingCashCommand(
            'closing-cash-denied-0001',
            Money::fromAtomicUnits(1000, 'IDR', 0),
        ),
        'correlation-closing-cash-denied',
    );
    $assert(false, 'permission denied actor recorded closing cash');
} catch (DurableAuthorizationViolation) {}
$assert(
    $connection->table('oneqay_pos_shift_closing_cash_evidence')->count() === 0,
    'authorization denial mutated closing evidence',
);

$setContext(
    'cash-admin-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    'device-alpha',
);
try {
    $app->make(RecordShiftClosingCash::class)->record(
        new ShiftClosingCashCommand(
            'closing-cash-no-opening-0001',
            Money::fromAtomicUnits(1000, 'IDR', 0),
        ),
        'correlation-closing-cash-no-opening',
    );
    $assert(false, 'missing opening cash prerequisite accepted');
} catch (PosTransactionViolation) {}

$opening = $app->make(RecordShiftOpeningCash::class)->record(
    new ShiftOpeningCashCommand(
        'opening-for-closing-alpha-0001',
        Money::fromAtomicUnits(250000, 'IDR', 0),
    ),
    'correlation-opening-for-closing-alpha',
);
$assert($opening->shiftId() === $firstShift->shiftId(), 'opening prerequisite shift mismatch');

try {
    $app->make(RecordShiftClosingCash::class)->record(
        new ShiftClosingCashCommand(
            'closing-cash-currency-mismatch-0001',
            Money::fromAtomicUnits(260000, 'USD', 0),
        ),
        'correlation-closing-cash-currency-mismatch',
    );
    $assert(false, 'currency mismatch accepted');
} catch (PosTransactionViolation) {}

try {
    $app->make(RecordShiftClosingCash::class)->record(
        new ShiftClosingCashCommand(
            'closing-cash-scale-mismatch-0001',
            Money::fromAtomicUnits(260000, 'IDR', 2),
        ),
        'correlation-closing-cash-scale-mismatch',
    );
    $assert(false, 'scale mismatch accepted');
} catch (PosTransactionViolation) {}

$closing = $app->make(RecordShiftClosingCash::class);
$command = new ShiftClosingCashCommand(
    'closing-cash-operation-0001',
    Money::fromAtomicUnits(275000, 'IDR', 0),
);
$result = $closing->record($command, 'correlation-closing-cash-0001');

$assert(strlen($result->evidenceId()) === 32, 'deterministic evidence id length');
$assert(str_starts_with($result->evidenceId(), 'cashclose-'), 'evidence id prefix');
$assert($result->openingCashEvidenceId() === $opening->evidenceId(), 'opening evidence binding');
$assert($result->shiftId() === $firstShift->shiftId(), 'server-resolved shift');
$assert($result->closingCash()->atomicUnits() === 275000, 'closing atomic preserved');
$assert($result->closingCash()->currency() === 'IDR', 'closing currency preserved');
$assert($result->closingCash()->scale() === 0, 'closing scale preserved');
$assert($result->evidenceMode() === 'OPERATOR_OBSERVED_CLOSING_CASH', 'evidence mode');
$assert($result->recordedAtUnix() > 0, 'server recorded time');

$row = (array) $connection->table('oneqay_pos_shift_closing_cash_evidence')
    ->where('tenant_id', 'tenant-alpha')
    ->where('operation_id', 'closing-cash-operation-0001')
    ->first();
$assert(($row['shift_id'] ?? null) === $firstShift->shiftId(), 'durable shift binding');
$assert(($row['opening_cash_evidence_id'] ?? null) === $opening->evidenceId(), 'durable opening evidence binding');
$assert(($row['actor_identity_id'] ?? null) === 'cash-admin-alpha', 'durable actor');
$assert(($row['organization_id'] ?? null) === 'organization-alpha', 'durable organization');
$assert(($row['outlet_id'] ?? null) === 'outlet-alpha', 'durable outlet');
$assert(($row['device_id'] ?? null) === 'device-alpha', 'durable device');
$assert((int) ($row['closing_cash_atomic'] ?? -1) === 275000, 'durable closing amount');

$shiftAfter = (array) $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('shift_id', $firstShift->shiftId())
    ->first();
$assert($shiftAfter === $shiftBefore, 'closing cash mutated shift or closed it');

$openingAfter = (array) $connection->table('oneqay_pos_shift_opening_cash_evidence')
    ->where('tenant_id', 'tenant-alpha')
    ->where('evidence_id', $opening->evidenceId())
    ->first();
$assert((int) ($openingAfter['opening_cash_atomic'] ?? -1) === 250000, 'closing cash mutated opening evidence');

$replay = $closing->record($command, 'correlation-closing-cash-replay');
$assert($replay->evidenceId() === $result->evidenceId(), 'exact replay evidence id');
$assert($replay->correlationId() === 'correlation-closing-cash-0001', 'exact replay original correlation');
$assert(
    $connection->table('oneqay_pos_shift_closing_cash_evidence')
        ->where('tenant_id', 'tenant-alpha')
        ->where('shift_id', $firstShift->shiftId())
        ->count() === 1,
    'exact replay duplicated closing evidence',
);

try {
    $closing->record(
        new ShiftClosingCashCommand(
            'closing-cash-operation-0002',
            Money::fromAtomicUnits(275000, 'IDR', 0),
        ),
        'correlation-closing-cash-second-operation',
    );
    $assert(false, 'second closing observation for same shift accepted');
} catch (PosTransactionViolation) {}

try {
    $closing->record(
        new ShiftClosingCashCommand(
            'closing-cash-operation-0001',
            Money::fromAtomicUnits(300000, 'IDR', 0),
        ),
        'correlation-closing-cash-conflict',
    );
    $assert(false, 'conflicting operation reuse accepted');
} catch (PosTransactionViolation) {}

$connection->table('oneqay_pos_shifts')
    ->where('tenant_id', 'tenant-alpha')
    ->where('shift_id', $firstShift->shiftId())
    ->update(['active_slot' => null]);

$replayAfterInactive = $closing->record($command, 'correlation-closing-cash-inactive-replay');
$assert($replayAfterInactive->evidenceId() === $result->evidenceId(), 'replay required shift to remain active');

$setContext(
    'cash-admin-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    'device-alpha-2',
);
$secondShift = $app->make(OpenShift::class)->open(
    new ShiftOpeningCommand('shift-close-alpha-device2-0001'),
    'correlation-shift-close-alpha-device2',
);
$openingZero = $app->make(RecordShiftOpeningCash::class)->record(
    new ShiftOpeningCashCommand(
        'opening-zero-for-closing-0001',
        Money::fromAtomicUnits(0, 'IDR', 0),
    ),
    'correlation-opening-zero-for-closing',
);
$zero = $app->make(RecordShiftClosingCash::class)->record(
    new ShiftClosingCashCommand(
        'closing-cash-zero-0001',
        Money::fromAtomicUnits(0, 'IDR', 0),
    ),
    'correlation-closing-cash-zero',
);
$assert($zero->shiftId() === $secondShift->shiftId(), 'zero observation shift');
$assert($zero->openingCashEvidenceId() === $openingZero->evidenceId(), 'zero opening binding');
$assert($zero->closingCash()->atomicUnits() === 0, 'explicit zero rejected or changed');

$setContext(
    'cash-admin-beta',
    'tenant-beta',
    'organization-beta',
    'outlet-beta',
    'device-beta',
);
$betaShift = $app->make(OpenShift::class)->open(
    new ShiftOpeningCommand('shift-close-beta-0001'),
    'correlation-shift-close-beta',
);
$app->make(RecordShiftOpeningCash::class)->record(
    new ShiftOpeningCashCommand(
        'opening-for-closing-beta-0001',
        Money::fromAtomicUnits(500, 'USD', 2),
    ),
    'correlation-opening-for-closing-beta',
);
$beta = $app->make(RecordShiftClosingCash::class)->record(
    new ShiftClosingCashCommand(
        'closing-cash-operation-0001',
        Money::fromAtomicUnits(650, 'USD', 2),
    ),
    'correlation-closing-cash-beta',
);
$assert($beta->shiftId() === $betaShift->shiftId(), 'beta server-resolved shift');
$assert($beta->tenantId() === 'tenant-beta', 'tenant isolation result');
$assert($beta->closingCash()->currency() === 'USD', 'beta currency');
$assert($beta->closingCash()->scale() === 2, 'beta scale');
$assert(
    $connection->table('oneqay_pos_shift_closing_cash_evidence')
        ->where('tenant_id', 'tenant-alpha')
        ->where('operation_id', 'closing-cash-operation-0001')
        ->count() === 1,
    'beta operation collided with alpha',
);
$assert(
    $connection->table('oneqay_pos_shift_closing_cash_evidence')
        ->where('tenant_id', 'tenant-beta')
        ->where('operation_id', 'closing-cash-operation-0001')
        ->count() === 1,
    'beta closing evidence missing',
);

$setContext(
    'cash-admin-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    'device-alpha-2',
);
$contexts = $app->make(OrganizationalContextStore::class);
$context = PosExecutionContext::fromVerified($contexts->current());

try {
    (new LaravelShiftClosingCashRepository($connection, true, 'ci', false))->record(
        $context,
        new ShiftClosingCashCommand(
            'closing-cash-disabled-0001',
            Money::fromAtomicUnits(0, 'IDR', 0),
        ),
        'correlation-closing-cash-disabled',
        time(),
    );
    $assert(false, 'disabled feature accepted closing cash');
} catch (PosTransactionViolation) {}

try {
    (new LaravelShiftClosingCashRepository($connection, true, 'production', true))->record(
        $context,
        new ShiftClosingCashCommand(
            'closing-cash-production-0001',
            Money::fromAtomicUnits(0, 'IDR', 0),
        ),
        'correlation-closing-cash-production',
        time(),
    );
    $assert(false, 'production runtime accepted closing cash');
} catch (PosTransactionViolation) {}

$controller = $app->make(PosShiftClosingCashController::class);

$unknown = Request::create('/pos/shifts/closing-cash', 'POST', [
    'operation_id' => 'closing-cash-http-unknown',
    'closing_cash_atomic' => 0,
    'currency' => 'IDR',
    'currency_scale' => 0,
    'shift_id' => $secondShift->shiftId(),
]);
$unknown->attributes->set('oneqay.correlation_id', 'correlation-closing-cash-http-unknown');
$unknownResponse = $controller($unknown);
$assert($unknownResponse->getStatusCode() === 422, 'caller shift id accepted');

$negative = Request::create('/pos/shifts/closing-cash', 'POST', [
    'operation_id' => 'closing-cash-http-negative',
    'closing_cash_atomic' => -1,
    'currency' => 'IDR',
    'currency_scale' => 0,
]);
$negative->attributes->set('oneqay.correlation_id', 'correlation-closing-cash-http-negative');
$negativeResponse = $controller($negative);
$assert($negativeResponse->getStatusCode() === 422, 'negative closing cash accepted');

$assert(Route::has('pos.shifts.closing-cash'), 'armed closing cash route absent');
$route = Route::getRoutes()->getByName('pos.shifts.closing-cash');
$assert($route !== null, 'closing cash route unresolved');
$middleware = $route->gatherMiddleware();
$assert(in_array('session.active', $middleware, true), 'active-session middleware missing');
$assert(in_array(RequirePosSessionContextMiddleware::class, $middleware, true), 'verified POS context middleware missing');

$assert($connection->table('oneqay_pos_sales')->count() === 0, 'closing cash created sale');
$assert($connection->table('oneqay_pos_sale_cash_refunds')->count() === 0, 'closing cash created refund');
$assert($connection->table('oneqay_pos_sale_catalog_items')->count() === 0, 'closing cash mutated catalog');

$migration23 = require __DIR__.'/../database/migrations/0000_00_00_000023_create_pos_shift_closing_cash_evidence_foundation.php';
try {
    $migration23->down();
    $assert(false, 'migration #23 rollback executed');
} catch (LogicException) {}

$manager->disconnect('s54_closing_cash');
$manager->purge('s54_closing_cash');
@unlink($db);
@rmdir($workspace);

echo "Sprint54 JRN-010 prerequisite shift closing cash evidence regression passed.\n";
