<?php

declare(strict_types=1);

use App\Application\Access\DurableOrganizationalAccessService;
use App\Application\Authorization\DurablePolicyAdministrationService;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Authorization\PosPermission;
use App\Application\Bootstrap\MerchantContextBootstrapService;
use App\Application\Bootstrap\MerchantContextBootstrapViolation;
use App\Application\Bootstrap\MerchantPosReadyBootstrapService;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapService;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurableContextGraph;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Access\LaravelDurableOrganizationalAccessRepository;
use App\Infrastructure\Authorization\LaravelDurablePolicyAdministrationRepository;
use App\Infrastructure\Authorization\LaravelDurableRolePermissionRepository;
use App\Infrastructure\Authorization\LaravelInitialTenantAdministratorProvisioningRepository;
use App\Infrastructure\Bootstrap\LaravelMerchantContextBootstrapStateRepository;
use App\Infrastructure\Bootstrap\PreauthorizedMerchantContextBootstrapAuthority;
use App\Infrastructure\Identity\LaravelFirstControlPrincipalCredentialBootstrapRepository;
use App\Infrastructure\Persistence\LaravelDurableContextGraphRepository;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use Illuminate\Contracts\Http\Kernel;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('p', 32)),
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
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint179 merchant POS-ready bootstrap regression failed: '.$message);
    }
};

$assert(extension_loaded('pdo_sqlite'), 'pdo_sqlite is required.');

$removeTree = null;
$removeTree = static function (string $path) use (&$removeTree): void {
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (! is_dir($path)) {
        return;
    }
    foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $item) {
        $removeTree($item->getPathname());
    }
    @rmdir($path);
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s179-pos-ready-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace creation failed.');
$databasePath = $workspace.DIRECTORY_SEPARATOR.'pos-ready.sqlite';
$assert(touch($databasePath), 'SQLite database creation failed.');

$app['config']->set('database.connections.s179_pos_ready', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);

/** @var \Illuminate\Database\DatabaseManager $manager */
$manager = $app->make('db');
$manager->purge('s179_pos_ready');
$manager->setDefaultConnection('s179_pos_ready');
$connection = $manager->connection('s179_pos_ready');
$connection->getPdo();

foreach ([
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
    '0000_00_00_000007_create_identity_password_credentials.php',
    '0000_00_00_000008_create_initial_password_enrollments.php',
    '0000_00_00_000009_create_identity_totp_factors.php',
] as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$goodClock = new class implements PolicyAdministrationClock {
    public function nowUnix(): int
    {
        return 1790000000;
    }
};
$badClock = new class implements PolicyAdministrationClock {
    public function nowUnix(): int
    {
        return 0;
    }
};

$graph = static fn (
    string $tenant,
    string $identity,
    string $organization,
    string $outlet,
    string $device,
): DurableContextGraph => new DurableContextGraph(
    TenantId::fromString($tenant),
    PlatformIdentityId::fromString($identity),
    OrganizationId::fromString($organization),
    OutletId::fromString($outlet),
    DeviceId::fromString($device),
);

$grant = static fn (DurableContextGraph $context, string $provisioningId): array => [
    'tenant_id' => $context->tenantId->value(),
    'identity_id' => $context->identityId->value(),
    'organization_id' => $context->organizationId->value(),
    'outlet_id' => $context->outletId->value(),
    'device_id' => $context->deviceId->value(),
    'provisioning_id' => $provisioningId,
];

$make = static function (
    DurableContextGraph $context,
    string $provisioningId,
    PolicyAdministrationClock $policyClock,
) use ($connection, $goodClock, $grant): array {
    $transaction = new LaravelPersistenceTransaction($connection, true, 'ci');
    $core = new MerchantContextBootstrapService(
        new PreauthorizedMerchantContextBootstrapAuthority([$grant($context, $provisioningId)]),
        new LaravelMerchantContextBootstrapStateRepository($connection, true, 'ci'),
        new LaravelDurableContextGraphRepository($connection, true, 'ci'),
        new LaravelInitialTenantAdministratorProvisioningRepository($connection, true, 'ci'),
        new FirstControlPrincipalCredentialBootstrapService(
            new LaravelFirstControlPrincipalCredentialBootstrapRepository($connection, true, 'ci', true),
            $transaction,
        ),
        $transaction,
        $goodClock,
    );

    $access = new DurableOrganizationalAccessService(
        new LaravelDurableOrganizationalAccessRepository($connection, true, 'ci'),
        $transaction,
    );

    $authorization = new DurableScopedAuthorizationPolicy(
        new LaravelDurableRolePermissionRepository($connection, true, 'ci'),
    );

    $policy = new DurablePolicyAdministrationService(
        $authorization,
        new LaravelDurablePolicyAdministrationRepository($connection, true, 'ci'),
        $transaction,
        $policyClock,
    );

    return [
        new MerchantPosReadyBootstrapService($core, $access, $policy, $transaction),
        $authorization,
    ];
};

$merchant = $graph(
    'merchant-pos-ready',
    'merchant-pos-ready-owner',
    'merchant-pos-ready-organization',
    'merchant-pos-ready-outlet',
    'merchant-pos-ready-device',
);
$provisioning = InitialTenantAdministratorProvisioningId::fromString('merchant-pos-ready-initial-admin');
[$service, $authorization] = $make($merchant, $provisioning->value(), $goodClock);

$assert(
    $service->bootstrap($merchant, $provisioning, 'merchant-pos-ready-secure-password') === MerchantContextBootstrapService::OUTCOME_APPLIED,
    'POS-ready bootstrap did not apply.',
);

foreach ([
    'oneqay_outlet_access_grants' => [
        'tenant_id' => $merchant->tenantId->value(),
        'identity_id' => $merchant->identityId->value(),
        'organization_id' => $merchant->organizationId->value(),
        'outlet_id' => $merchant->outletId->value(),
    ],
    'oneqay_device_access_grants' => [
        'tenant_id' => $merchant->tenantId->value(),
        'identity_id' => $merchant->identityId->value(),
        'organization_id' => $merchant->organizationId->value(),
        'outlet_id' => $merchant->outletId->value(),
        'device_id' => $merchant->deviceId->value(),
    ],
] as $table => $where) {
    $query = $connection->table($table);
    foreach ($where as $column => $value) {
        $query->where($column, $value);
    }
    $assert($query->count() === 1, 'missing exact '.$table.' row.');
}

$role = MerchantPosReadyBootstrapService::ROLE;
$expectedPermissions = [
    PosPermission::PREPARE_CATALOG,
    PosPermission::INVENTORY_BASELINE,
    PosPermission::OPEN_SHIFT,
    PosPermission::RECORD_SHIFT_OPENING_CASH,
    PosPermission::COMPLETE_SALE,
];
$actualPermissions = $connection->table('oneqay_role_permissions')
    ->where('tenant_id', $merchant->tenantId->value())
    ->where('role_id', $role)
    ->orderBy('permission_id')
    ->pluck('permission_id')
    ->all();
sort($expectedPermissions);
$assert($actualPermissions === $expectedPermissions, 'initial POS role permission set is not exact.');

$assert(
    $connection->table('oneqay_device_role_assignments')
        ->where('tenant_id', $merchant->tenantId->value())
        ->where('identity_id', $merchant->identityId->value())
        ->where('organization_id', $merchant->organizationId->value())
        ->where('outlet_id', $merchant->outletId->value())
        ->where('device_id', $merchant->deviceId->value())
        ->where('role_id', $role)
        ->count() === 1,
    'initial POS role is not exact-device scoped.',
);

foreach (['oneqay_tenant_role_assignments', 'oneqay_organization_role_assignments', 'oneqay_outlet_role_assignments'] as $table) {
    $assert(
        $connection->table($table)
            ->where('tenant_id', $merchant->tenantId->value())
            ->where('identity_id', $merchant->identityId->value())
            ->where('role_id', $role)
            ->doesntExist(),
        'initial POS role leaked to broader scope in '.$table.'.',
    );
}

$controlPermissions = $connection->table('oneqay_role_permissions')
    ->where('tenant_id', $merchant->tenantId->value())
    ->where('role_id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)
    ->pluck('permission_id')
    ->all();
$assert(
    $controlPermissions === [InitialTenantAdministratorProvisioningRepository::CONTROL_PERMISSION],
    'protected control role was widened.',
);

$verified = new VerifiedOrganizationalContext(
    $merchant->identityId,
    $merchant->tenantId,
    $merchant->organizationId,
    $merchant->outletId,
    $merchant->deviceId,
);
foreach ($expectedPermissions as $permission) {
    $assert(
        $authorization->allows($verified, PermissionIdentifier::fromString($permission)),
        'expected POS permission was not effective: '.$permission,
    );
}
$assert(
    ! $authorization->allows($verified, PosPermission::voidSale()),
    'initial POS role unexpectedly grants void permission.',
);
$assert(
    ! $authorization->allows($verified, PosPermission::refundSale()),
    'initial POS role unexpectedly grants refund permission.',
);

$rollback = $graph(
    'merchant-pos-rollback',
    'merchant-pos-rollback-owner',
    'merchant-pos-rollback-organization',
    'merchant-pos-rollback-outlet',
    'merchant-pos-rollback-device',
);
$rollbackProvisioning = InitialTenantAdministratorProvisioningId::fromString('merchant-pos-rollback-initial-admin');
[$rollbackService] = $make($rollback, $rollbackProvisioning->value(), $badClock);

try {
    $rollbackService->bootstrap($rollback, $rollbackProvisioning, 'merchant-pos-rollback-secure-password');
    $assert(false, 'policy-stage failure unexpectedly succeeded.');
} catch (MerchantContextBootstrapViolation $exception) {
    $assert(
        $exception->errorCode === MerchantContextBootstrapViolation::TRANSACTION_FAILURE,
        'policy-stage failure returned an unexpected error code.',
    );
}

foreach ([
    'oneqay_tenants',
    'oneqay_identities',
    'oneqay_organizations',
    'oneqay_identity_organizations',
    'oneqay_outlets',
    'oneqay_devices',
    'oneqay_outlet_access_grants',
    'oneqay_device_access_grants',
    'oneqay_roles',
    'oneqay_role_permissions',
    'oneqay_tenant_role_assignments',
    'oneqay_device_role_assignments',
    'oneqay_policy_mutations',
    'oneqay_initial_tenant_admin_provisionings',
    'oneqay_identity_password_credentials',
] as $table) {
    $assert(
        $connection->table($table)->where('tenant_id', $rollback->tenantId->value())->doesntExist(),
        'outer rollback left durable state in '.$table.'.',
    );
}

$manager->disconnect('s179_pos_ready');
$manager->purge('s179_pos_ready');
$removeTree($workspace);
$assert(! file_exists($workspace), 'workspace cleanup failed.');

fwrite(STDOUT, "Sprint179 merchant POS-ready bootstrap regression passed.\n");
