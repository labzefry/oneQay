<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\RoleIdentifier;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Authorization\LaravelDurableRolePermissionRepository;

// Author by Lab | zefry
if (! isset($app, $assert) || ! is_callable($assert)) {
    throw new RuntimeException('Sprint 21 authorization persistence regression requires the M7.1 application harness.');
}

$assert(extension_loaded('pdo_sqlite'), 'Sprint 21 authorization persistence regression requires pdo_sqlite in CI.');

$expectInvalid = static function (callable $callback, string $message) use ($assert): void {
    try {
        $callback();
        $assert(false, $message);
    } catch (InvalidArgumentException) {
        // Expected.
    }
};

$removeTree = null;
$removeTree = static function (string $path) use (&$removeTree): void {
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (! is_dir($path)) {
        return;
    }
    $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $item) {
        $removeTree($item->getPathname());
    }
    @rmdir($path);
};

$parent = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s21-authz-'.getmypid();
$removeTree($parent);
$assert(@mkdir($parent, 0700, false), 'Sprint 21 authorization workspace could not be created.');
$databasePath = $parent.DIRECTORY_SEPARATOR.'authorization.sqlite';
$assert(touch($databasePath), 'Sprint 21 disposable authorization SQLite file could not be created.');

$app['config']->set('database.default', 's21_sqlite');
$app['config']->set('database.connections.s21_sqlite', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);
$app['config']->set('oneqay.runtime_class', 'ci');
$app['config']->set('database.oneqay_persistence_enabled', false);

/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s21_sqlite');
$databaseManager->setDefaultConnection('s21_sqlite');
$connection = $databaseManager->connection('s21_sqlite');
$connection->getPdo();

$alphaIdentity = PlatformIdentityId::fromString('synthetic-principal-alpha');
$alphaTenant = TenantId::fromString('tenant-alpha');
$sharedOrganization = OrganizationId::fromString('organization-shared');
$sharedOutlet = OutletId::fromString('outlet-shared');
$sharedDevice = DeviceId::fromString('device-shared');
$alphaDeviceContext = new VerifiedOrganizationalContext(
    $alphaIdentity,
    $alphaTenant,
    $sharedOrganization,
    $sharedOutlet,
    $sharedDevice,
);
$readPermission = PermissionIdentifier::fromString('synthetic.resource.read');

foreach ([
    [false, 'ci', DurableAuthorizationViolation::PERSISTENCE_DISABLED],
    [true, 'preview', DurableAuthorizationViolation::RUNTIME_DENIED],
    [true, 'production', DurableAuthorizationViolation::RUNTIME_DENIED],
    [true, '', DurableAuthorizationViolation::RUNTIME_DENIED],
] as [$enabled, $runtime, $expectedCode]) {
    try {
        (new LaravelDurableRolePermissionRepository($connection, $enabled, $runtime))
            ->allows($alphaDeviceContext, $readPermission);
        $assert(false, 'Sprint 21 unauthorized durable authorization runtime was accepted.');
    } catch (DurableAuthorizationViolation $exception) {
        $assert($exception->errorCode === $expectedCode, 'Sprint 21 runtime guard returned an unexpected error code.');
        $assert(! str_contains($exception->getMessage(), $databasePath), 'Sprint 21 runtime failure leaked the database path.');
    }
}

$role = RoleIdentifier::fromString('  Synthetic-Reader  ');
$assert($role->value() === 'synthetic-reader', 'Sprint 21 role identifier did not canonicalize deterministically.');
$permission = PermissionIdentifier::fromString('  Synthetic.Resource_Read  ');
$assert($permission->value() === 'synthetic.resource_read', 'Sprint 21 permission identifier did not canonicalize deterministically.');

foreach (['', '1reader', 'role space', str_repeat('a', 65), 'platform-superadmin', 'platform-admin', 'platform_admin'] as $invalidRole) {
    $expectInvalid(static fn () => RoleIdentifier::fromString($invalidRole), 'Sprint 21 accepted invalid/reserved role: '.$invalidRole);
}
foreach (['', 'single', '1.bad', 'synthetic.*', 'synthetic.resource.*', 'tenant_alpha.read', 'synthetic.user_alpha', 'platform.system-update.install'] as $invalidPermission) {
    $expectInvalid(static fn () => PermissionIdentifier::fromString($invalidPermission), 'Sprint 21 accepted invalid/reserved permission: '.$invalidPermission);
}

$migrationDirectory = __DIR__.'/../database/migrations';
$migrations = array_values(array_filter(scandir($migrationDirectory) ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($migrations);
$expectedMigrations = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
];
$assert($migrations === $expectedMigrations, 'Sprint 21 migration directory is not the exact governed three-migration set.');

foreach ($expectedMigrations as $migration) {
    (require $migrationDirectory.'/'.$migration)->up();
}

$schema = $connection->getSchemaBuilder();
$policyTables = [
    'oneqay_roles',
    'oneqay_role_permissions',
    'oneqay_tenant_role_assignments',
    'oneqay_organization_role_assignments',
    'oneqay_outlet_role_assignments',
    'oneqay_device_role_assignments',
];
foreach ($policyTables as $table) {
    $assert($schema->hasTable($table), 'Sprint 21 policy table missing: '.$table);
}

$connection->table('oneqay_tenants')->insert([
    ['id' => 'tenant-alpha'],
    ['id' => 'tenant-beta'],
]);
$connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-principal-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-principal-beta'],
]);
$connection->table('oneqay_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-shared'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-secondary'],
    ['tenant_id' => 'tenant-beta', 'id' => 'organization-shared'],
]);
$connection->table('oneqay_identity_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-shared'],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-secondary'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-principal-beta', 'organization_id' => 'organization-shared'],
]);
$connection->table('oneqay_outlets')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'outlet-shared', 'organization_id' => 'organization-shared'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'outlet-secondary', 'organization_id' => 'organization-shared'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'outlet-tertiary', 'organization_id' => 'organization-secondary'],
    ['tenant_id' => 'tenant-beta', 'id' => 'outlet-shared', 'organization_id' => 'organization-shared'],
]);
$connection->table('oneqay_devices')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'device-shared', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'device-secondary', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-secondary'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'device-tertiary', 'organization_id' => 'organization-secondary', 'outlet_id' => 'outlet-tertiary'],
    ['tenant_id' => 'tenant-beta', 'id' => 'device-shared', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared'],
]);
$connection->table('oneqay_outlet_access_grants')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared'],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-secondary'],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-secondary', 'outlet_id' => 'outlet-tertiary'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-principal-beta', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared'],
]);
$connection->table('oneqay_device_access_grants')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared', 'device_id' => 'device-shared'],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-secondary', 'device_id' => 'device-secondary'],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-secondary', 'outlet_id' => 'outlet-tertiary', 'device_id' => 'device-tertiary'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-principal-beta', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared', 'device_id' => 'device-shared'],
]);

$connection->table('oneqay_roles')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-tenant-reader'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-org-writer'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-outlet-exporter'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-device-operator'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-unassigned'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-tenant-reader'],
]);
$connection->table('oneqay_role_permissions')->insert([
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'synthetic-tenant-reader', 'permission_id' => 'synthetic.resource.read'],
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'synthetic-org-writer', 'permission_id' => 'synthetic.resource.write'],
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'synthetic-outlet-exporter', 'permission_id' => 'synthetic.resource.export'],
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'synthetic-device-operator', 'permission_id' => 'synthetic.resource.execute'],
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'synthetic-unassigned', 'permission_id' => 'synthetic.resource.unassigned'],
    ['tenant_id' => 'tenant-beta', 'role_id' => 'synthetic-tenant-reader', 'permission_id' => 'synthetic.resource.read'],
    ['tenant_id' => 'tenant-beta', 'role_id' => 'synthetic-tenant-reader', 'permission_id' => 'synthetic.resource.beta-only'],
]);
$connection->table('oneqay_tenant_role_assignments')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'role_id' => 'synthetic-tenant-reader'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-principal-beta', 'role_id' => 'synthetic-tenant-reader'],
]);
$connection->table('oneqay_organization_role_assignments')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-shared', 'role_id' => 'synthetic-org-writer'],
]);
$connection->table('oneqay_outlet_role_assignments')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared', 'role_id' => 'synthetic-outlet-exporter'],
]);
$connection->table('oneqay_device_role_assignments')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared', 'device_id' => 'device-shared', 'role_id' => 'synthetic-device-operator'],
]);

$repository = new LaravelDurableRolePermissionRepository($connection, true, 'ci');
$policy = new DurableScopedAuthorizationPolicy($repository);

$alphaOrgShared = new VerifiedOrganizationalContext($alphaIdentity, $alphaTenant, $sharedOrganization);
$alphaOutletShared = new VerifiedOrganizationalContext($alphaIdentity, $alphaTenant, $sharedOrganization, $sharedOutlet);
$alphaOutletSecondary = new VerifiedOrganizationalContext($alphaIdentity, $alphaTenant, $sharedOrganization, OutletId::fromString('outlet-secondary'));
$alphaDeviceSecondary = new VerifiedOrganizationalContext($alphaIdentity, $alphaTenant, $sharedOrganization, OutletId::fromString('outlet-secondary'), DeviceId::fromString('device-secondary'));
$alphaOrgSecondary = new VerifiedOrganizationalContext($alphaIdentity, $alphaTenant, OrganizationId::fromString('organization-secondary'));
$alphaDeviceTertiary = new VerifiedOrganizationalContext($alphaIdentity, $alphaTenant, OrganizationId::fromString('organization-secondary'), OutletId::fromString('outlet-tertiary'), DeviceId::fromString('device-tertiary'));
$betaContext = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('synthetic-principal-beta'),
    TenantId::fromString('tenant-beta'),
    OrganizationId::fromString('organization-shared'),
    OutletId::fromString('outlet-shared'),
    DeviceId::fromString('device-shared'),
);

$read = PermissionIdentifier::fromString('synthetic.resource.read');
$write = PermissionIdentifier::fromString('synthetic.resource.write');
$export = PermissionIdentifier::fromString('synthetic.resource.export');
$execute = PermissionIdentifier::fromString('synthetic.resource.execute');
$unassigned = PermissionIdentifier::fromString('synthetic.resource.unassigned');
$betaOnly = PermissionIdentifier::fromString('synthetic.resource.beta-only');

foreach ([$alphaOrgShared, $alphaOutletShared, $alphaDeviceContext, $alphaOrgSecondary, $alphaDeviceTertiary] as $context) {
    $assert($policy->allows($context, $read), 'Sprint 21 tenant-scoped role failed to apply within tenant-alpha descendants.');
}
$assert($policy->allows($alphaOrgShared, $write), 'Sprint 21 organization-scoped permission was not allowed at organization scope.');
$assert($policy->allows($alphaDeviceContext, $write), 'Sprint 21 organization-scoped permission did not apply to a verified descendant device.');
$assert($policy->allows($alphaDeviceSecondary, $write), 'Sprint 21 organization-scoped permission did not apply to another verified descendant outlet/device.');
$assert(! $policy->allows($alphaOrgSecondary, $write), 'Sprint 21 organization-scoped permission crossed organization scope.');

$assert($policy->allows($alphaOutletShared, $export), 'Sprint 21 outlet-scoped permission was not allowed at outlet scope.');
$assert($policy->allows($alphaDeviceContext, $export), 'Sprint 21 outlet-scoped permission did not apply to its verified device child.');
$assert(! $policy->allows($alphaOutletSecondary, $export), 'Sprint 21 outlet-scoped permission crossed outlet scope.');
$assert(! $policy->allows($alphaDeviceSecondary, $export), 'Sprint 21 outlet-scoped permission crossed to another device hierarchy.');

$assert($policy->allows($alphaDeviceContext, $execute), 'Sprint 21 device-scoped permission was not allowed on the exact device.');
$assert(! $policy->allows($alphaDeviceSecondary, $execute), 'Sprint 21 device-scoped permission crossed device scope.');
$assert(! $policy->allows($alphaOutletShared, $execute), 'Sprint 21 device-scoped permission leaked upward to outlet scope.');

$assert(! $policy->allows($alphaDeviceContext, $unassigned), 'Sprint 21 unassigned role unexpectedly granted permission.');
$assert(! $policy->allows($alphaDeviceContext, PermissionIdentifier::fromString('synthetic.resource.unknown')), 'Sprint 21 missing exact permission unexpectedly allowed access.');
$assert(! $policy->allows($alphaDeviceContext, $betaOnly), 'Sprint 21 foreign-tenant permission row granted access in tenant-alpha.');
$assert($policy->allows($betaContext, $betaOnly), 'Sprint 21 tenant-beta positive control failed with same textual resource identifiers.');
$assert(! $policy->allows($alphaDeviceContext, PermissionIdentifier::fromString('synthetic.resource.re')), 'Sprint 21 permission evaluation used implicit prefix matching.');
$assert(! $policy->allows(null, $read), 'Sprint 21 missing verified organizational context did not fail closed.');
try {
    $policy->require(null, $read);
    $assert(false, 'Sprint 21 require() accepted missing verified context.');
} catch (DurableAuthorizationViolation $exception) {
    $assert($exception->errorCode === DurableAuthorizationViolation::PERMISSION_DENIED, 'Sprint 21 require() returned an unexpected denial code.');
}

$connection->table('oneqay_roles')->insert(['tenant_id' => 'tenant-alpha', 'id' => 'Bad-Role']);
$connection->table('oneqay_role_permissions')->insert(['tenant_id' => 'tenant-alpha', 'role_id' => 'Bad-Role', 'permission_id' => 'synthetic.corrupt.read']);
$connection->table('oneqay_tenant_role_assignments')->insert(['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-principal-alpha', 'role_id' => 'Bad-Role']);
try {
    $repository->allows($alphaDeviceContext, PermissionIdentifier::fromString('synthetic.corrupt.read'));
    $assert(false, 'Sprint 21 accepted non-canonical durable role data.');
} catch (DurableAuthorizationViolation $exception) {
    $assert($exception->errorCode === DurableAuthorizationViolation::POLICY_DATA_INVALID, 'Sprint 21 corrupt durable role did not fail closed with bounded classification.');
    $assert(! str_contains($exception->getMessage(), 'Bad-Role'), 'Sprint 21 policy-data failure leaked durable role data.');
}
$connection->table('oneqay_tenant_role_assignments')->where('tenant_id', 'tenant-alpha')->where('role_id', 'Bad-Role')->delete();
$connection->table('oneqay_role_permissions')->where('tenant_id', 'tenant-alpha')->where('role_id', 'Bad-Role')->delete();
$connection->table('oneqay_roles')->where('tenant_id', 'tenant-alpha')->where('id', 'Bad-Role')->delete();

$repositorySource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php');
$applicationSources = '';
foreach (glob(__DIR__.'/../app/Application/Authorization/*.php') ?: [] as $sourceFile) {
    $applicationSources .= (string) file_get_contents($sourceFile)."\n";
}
$migrationSource = (string) file_get_contents($migrationDirectory.'/0000_00_00_000003_create_scoped_role_permission_policy.php');

foreach ($policyTables as $table) {
    $assert(str_contains($migrationSource, $table), 'Sprint 21 migration source lost policy table: '.$table);
}
foreach (['fk_organization_role_membership', 'fk_outlet_role_access', 'fk_device_role_access', 'Forward-only generated migration; rollback is not authorized.'] as $boundary) {
    $assert(str_contains($migrationSource, $boundary), 'Sprint 21 migration source lost boundary: '.$boundary);
}
$assert(! preg_match('/\b(insert|insertOrIgnore|update|updateOrInsert|upsert|delete|truncate)\s*\(/', $repositorySource), 'Sprint 21 durable authorization repository contains a policy mutation operation.');
$assert(! str_contains($repositorySource, 'Schema::'), 'Sprint 21 durable authorization repository contains schema mutation coupling.');
$assert(substr_count($repositorySource, "->where('tenant_id',") >= 5, 'Sprint 21 durable authorization repository lost explicit tenant predicates.');
$assert(str_contains($repositorySource, "['local', 'test', 'ci']"), 'Sprint 21 durable authorization runtime allowlist changed unexpectedly.');
foreach (['Illuminate\\', 'Laravel\\', 'Schema::', 'DB::', 'new PDO', 'mysqli_'] as $frameworkNeedle) {
    $assert(! str_contains($applicationSources, $frameworkNeedle), 'Sprint 21 Application authorization boundary became framework/database-coupled: '.$frameworkNeedle);
}
$assert(str_contains((string) file_get_contents(__DIR__.'/../app/Application/Authorization/RoleIdentifier.php'), 'platform-superadmin'), 'Sprint 21 role identifier lost platform-superadmin reservation.');
$assert(str_contains((string) file_get_contents(__DIR__.'/../app/Application/Authorization/PermissionIdentifier.php'), "'platform.'"), 'Sprint 21 permission identifier lost platform namespace reservation.');

$schema->drop('oneqay_tenant_role_assignments');
try {
    $repository->allows($alphaDeviceContext, $read);
    $assert(false, 'Sprint 21 storage failure did not fail closed.');
} catch (DurableAuthorizationViolation $exception) {
    $assert($exception->errorCode === DurableAuthorizationViolation::STORAGE_FAILURE, 'Sprint 21 storage failure returned an unexpected bounded code.');
    foreach ([$databasePath, $parent, 'SQLSTATE', 'sqlite'] as $forbidden) {
        $assert(! str_contains($exception->getMessage(), $forbidden), 'Sprint 21 storage failure leaked private database material.');
    }
}

$databaseManager->disconnect('s21_sqlite');
$databaseManager->purge('s21_sqlite');
$app['config']->set('database.oneqay_persistence_enabled', false);
$app['config']->set('database.connections.s21_sqlite', null);
@unlink($databasePath);
$removeTree($parent);
$assert(! file_exists($parent), 'Sprint 21 authorization workspace cleanup failed.');
