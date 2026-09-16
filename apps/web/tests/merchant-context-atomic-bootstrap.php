<?php

declare(strict_types=1);

use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Bootstrap\MerchantContextBootstrapService;
use App\Application\Bootstrap\MerchantContextBootstrapViolation;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapService;
use App\Application\Persistence\DurableContextGraph;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Authorization\LaravelInitialTenantAdministratorProvisioningRepository;
use App\Infrastructure\Bootstrap\LaravelMerchantContextBootstrapStateRepository;
use App\Infrastructure\Bootstrap\PreauthorizedMerchantContextBootstrapAuthority;
use App\Infrastructure\Identity\LaravelFirstControlPrincipalCredentialBootstrapRepository;
use App\Infrastructure\Persistence\LaravelDurableContextGraphRepository;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;

// Author by Lab | zefry
require __DIR__.'/../vendor/autoload.php';

foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('b', 32)),
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
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException($message);
    }
};

$assert(extension_loaded('pdo_sqlite'), 'Sprint176 merchant bootstrap regression requires pdo_sqlite.');

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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s176-merchant-bootstrap-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'Sprint176 workspace creation failed.');
$databasePath = $workspace.DIRECTORY_SEPARATOR.'merchant-bootstrap.sqlite';
$assert(touch($databasePath), 'Sprint176 SQLite database creation failed.');

$app['config']->set('database.connections.s176_merchant_bootstrap', [
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
$manager->purge('s176_merchant_bootstrap');
$manager->setDefaultConnection('s176_merchant_bootstrap');
$connection = $manager->connection('s176_merchant_bootstrap');
$connection->getPdo();

$migrations = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
    '0000_00_00_000007_create_identity_password_credentials.php',
    '0000_00_00_000008_create_initial_password_enrollments.php',
    '0000_00_00_000009_create_identity_totp_factors.php',
];
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$clock = new class implements PolicyAdministrationClock {
    public function nowUnix(): int
    {
        return 1789520400;
    }
};

$graph = static function (
    string $tenant,
    string $identity,
    string $organization,
    string $outlet,
    string $device,
): DurableContextGraph {
    return new DurableContextGraph(
        TenantId::fromString($tenant),
        PlatformIdentityId::fromString($identity),
        OrganizationId::fromString($organization),
        OutletId::fromString($outlet),
        DeviceId::fromString($device),
    );
};

$grant = static function (DurableContextGraph $context, string $provisioningId): array {
    return [
        'tenant_id' => $context->tenantId->value(),
        'identity_id' => $context->identityId->value(),
        'organization_id' => $context->organizationId->value(),
        'outlet_id' => $context->outletId->value(),
        'device_id' => $context->deviceId->value(),
        'provisioning_id' => $provisioningId,
    ];
};

$service = static function (
    DurableContextGraph $context,
    string $provisioningId,
    bool $bootstrapEnabled = true,
    string $runtimeClass = 'ci',
) use ($connection, $clock, $grant): MerchantContextBootstrapService {
    $transaction = new LaravelPersistenceTransaction($connection, true, $runtimeClass);
    $authority = new PreauthorizedMerchantContextBootstrapAuthority([$grant($context, $provisioningId)]);
    $credentialRepository = new LaravelFirstControlPrincipalCredentialBootstrapRepository(
        $connection,
        true,
        $runtimeClass,
        $bootstrapEnabled,
    );

    return new MerchantContextBootstrapService(
        $authority,
        new LaravelMerchantContextBootstrapStateRepository($connection, true, $runtimeClass),
        new LaravelDurableContextGraphRepository($connection, true, $runtimeClass),
        new LaravelInitialTenantAdministratorProvisioningRepository($connection, true, $runtimeClass),
        new FirstControlPrincipalCredentialBootstrapService($credentialRepository, $transaction),
        $transaction,
        $clock,
    );
};

$alpha = $graph(
    'merchant-alpha',
    'merchant-alpha-owner',
    'merchant-alpha-organization',
    'merchant-alpha-outlet',
    'merchant-alpha-device',
);
$alphaProvisioning = InitialTenantAdministratorProvisioningId::fromString('merchant-alpha-initial-admin');
$alphaPassword = 'merchant-alpha-secure-password';
$alphaService = $service($alpha, $alphaProvisioning->value());
$assert(
    $alphaService->bootstrap($alpha, $alphaProvisioning, $alphaPassword) === MerchantContextBootstrapService::OUTCOME_APPLIED,
    'Sprint176 merchant bootstrap did not apply.',
);

foreach ([
    ['oneqay_tenants', ['id' => $alpha->tenantId->value()]],
    ['oneqay_identities', ['tenant_id' => $alpha->tenantId->value(), 'id' => $alpha->identityId->value()]],
    ['oneqay_organizations', ['tenant_id' => $alpha->tenantId->value(), 'id' => $alpha->organizationId->value()]],
    ['oneqay_identity_organizations', ['tenant_id' => $alpha->tenantId->value(), 'identity_id' => $alpha->identityId->value(), 'organization_id' => $alpha->organizationId->value()]],
    ['oneqay_outlets', ['tenant_id' => $alpha->tenantId->value(), 'id' => $alpha->outletId->value(), 'organization_id' => $alpha->organizationId->value()]],
    ['oneqay_devices', ['tenant_id' => $alpha->tenantId->value(), 'id' => $alpha->deviceId->value(), 'organization_id' => $alpha->organizationId->value(), 'outlet_id' => $alpha->outletId->value()]],
] as [$table, $where]) {
    $query = $connection->table($table);
    foreach ($where as $column => $value) {
        $query->where($column, $value);
    }
    $assert($query->count() === 1, 'Sprint176 bootstrap materialized an invalid context graph row: '.$table);
}

$assert(
    $connection->table('oneqay_initial_tenant_admin_provisionings')
        ->where('tenant_id', $alpha->tenantId->value())
        ->where('identity_id', $alpha->identityId->value())
        ->where('outcome', 'applied')
        ->count() === 1,
    'Sprint176 bootstrap did not journal the initial administrator.',
);
$assert(
    $connection->table('oneqay_tenant_role_assignments')
        ->where('tenant_id', $alpha->tenantId->value())
        ->where('identity_id', $alpha->identityId->value())
        ->where('role_id', 'authorization-policy-administrator')
        ->count() === 1,
    'Sprint176 bootstrap did not assign the protected tenant administrator role.',
);
$credential = $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', $alpha->tenantId->value())
    ->where('identity_id', $alpha->identityId->value())
    ->first();
$assert($credential !== null, 'Sprint176 bootstrap did not create the first control credential.');
$assert(
    is_string($credential->password_hash ?? null) && password_verify($alphaPassword, $credential->password_hash),
    'Sprint176 bootstrap credential hash does not verify the supplied password.',
);
$assert(
    ! hash_equals($alphaPassword, (string) ($credential->password_hash ?? '')),
    'Sprint176 bootstrap stored plaintext password material.',
);

$altered = $graph(
    'merchant-alpha',
    'merchant-alpha-owner',
    'merchant-alpha-other-organization',
    'merchant-alpha-outlet',
    'merchant-alpha-device',
);
try {
    $alphaService->bootstrap($altered, $alphaProvisioning, 'another-secure-password');
    $assert(false, 'Sprint176 accepted a bootstrap tuple outside the exact preauthorized graph.');
} catch (MerchantContextBootstrapViolation $exception) {
    $assert(
        $exception->errorCode === MerchantContextBootstrapViolation::AUTHORIZATION_DENIED,
        'Sprint176 unauthorized graph returned an unexpected error code.',
    );
}

$existing = $graph(
    'merchant-existing',
    'merchant-existing-owner',
    'merchant-existing-organization',
    'merchant-existing-outlet',
    'merchant-existing-device',
);
$connection->table('oneqay_tenants')->insert(['id' => $existing->tenantId->value()]);
$existingProvisioning = InitialTenantAdministratorProvisioningId::fromString('merchant-existing-initial-admin');
try {
    $service($existing, $existingProvisioning->value())->bootstrap(
        $existing,
        $existingProvisioning,
        'merchant-existing-password',
    );
    $assert(false, 'Sprint176 accepted bootstrap into a pre-existing tenant.');
} catch (MerchantContextBootstrapViolation $exception) {
    $assert(
        $exception->errorCode === MerchantContextBootstrapViolation::TENANT_ALREADY_EXISTS,
        'Sprint176 pre-existing tenant returned an unexpected error code.',
    );
}
$assert(
    $connection->table('oneqay_identities')->where('tenant_id', $existing->tenantId->value())->doesntExist(),
    'Sprint176 pre-existing tenant denial left child context state.',
);

$shortPasswordGraph = $graph(
    'merchant-short-password',
    'merchant-short-password-owner',
    'merchant-short-password-organization',
    'merchant-short-password-outlet',
    'merchant-short-password-device',
);
$shortProvisioning = InitialTenantAdministratorProvisioningId::fromString('merchant-short-password-initial-admin');
try {
    $service($shortPasswordGraph, $shortProvisioning->value())->bootstrap(
        $shortPasswordGraph,
        $shortProvisioning,
        'too-short',
    );
    $assert(false, 'Sprint176 accepted an invalid bootstrap password.');
} catch (MerchantContextBootstrapViolation $exception) {
    $assert(
        $exception->errorCode === MerchantContextBootstrapViolation::INVALID_PASSWORD,
        'Sprint176 invalid password returned an unexpected error code.',
    );
}
$assert(
    $connection->table('oneqay_tenants')->where('id', $shortPasswordGraph->tenantId->value())->doesntExist(),
    'Sprint176 invalid password denial occurred after tenant mutation.',
);

$rollback = $graph(
    'merchant-rollback',
    'merchant-rollback-owner',
    'merchant-rollback-organization',
    'merchant-rollback-outlet',
    'merchant-rollback-device',
);
$rollbackProvisioning = InitialTenantAdministratorProvisioningId::fromString('merchant-rollback-initial-admin');
try {
    $service($rollback, $rollbackProvisioning->value(), false)->bootstrap(
        $rollback,
        $rollbackProvisioning,
        'merchant-rollback-secure-password',
    );
    $assert(false, 'Sprint176 accepted a bootstrap when credential bootstrap was disabled.');
} catch (MerchantContextBootstrapViolation $exception) {
    $assert(
        $exception->errorCode === MerchantContextBootstrapViolation::TRANSACTION_FAILURE,
        'Sprint176 downstream bootstrap failure returned an unexpected error code.',
    );
}
$assert(
    $connection->table('oneqay_tenants')->where('id', $rollback->tenantId->value())->doesntExist(),
    'Sprint176 failed atomic rollback left durable tenant state.',
);
foreach ([
    'oneqay_identities',
    'oneqay_organizations',
    'oneqay_identity_organizations',
    'oneqay_outlets',
    'oneqay_devices',
    'oneqay_initial_tenant_admin_provisionings',
    'oneqay_tenant_role_assignments',
    'oneqay_identity_password_credentials',
] as $table) {
    $assert(
        $connection->table($table)->where('tenant_id', $rollback->tenantId->value())->doesntExist(),
        'Sprint176 failed atomic rollback left durable state in '.$table.'.',
    );
}

$preview = $graph(
    'merchant-preview-denied',
    'merchant-preview-denied-owner',
    'merchant-preview-denied-organization',
    'merchant-preview-denied-outlet',
    'merchant-preview-denied-device',
);
$previewProvisioning = InitialTenantAdministratorProvisioningId::fromString('merchant-preview-denied-initial-admin');
try {
    $service($preview, $previewProvisioning->value(), true, 'preview')->bootstrap(
        $preview,
        $previewProvisioning,
        'merchant-preview-denied-password',
    );
    $assert(false, 'Sprint176 Preview runtime reached merchant bootstrap storage.');
} catch (MerchantContextBootstrapViolation $exception) {
    $assert(
        $exception->errorCode === MerchantContextBootstrapViolation::RUNTIME_DENIED,
        'Sprint176 Preview denial returned an unexpected error code.',
    );
}
$assert(
    $connection->table('oneqay_tenants')->where('id', $preview->tenantId->value())->doesntExist(),
    'Sprint176 Preview denial occurred after tenant mutation.',
);

$manager->disconnect('s176_merchant_bootstrap');
$manager->purge('s176_merchant_bootstrap');
@unlink($databasePath);
$removeTree($workspace);
$assert(! file_exists($workspace), 'Sprint176 disposable bootstrap workspace cleanup failed.');

fwrite(STDOUT, "Sprint176 merchant context atomic bootstrap regression passed.\n");
