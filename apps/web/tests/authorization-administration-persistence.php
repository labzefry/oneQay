<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\DurablePolicyAdministrationRepository;
use App\Application\Authorization\DurablePolicyAdministrationService;
use App\Application\Authorization\DurablePolicyAdministrationViolation;
use App\Application\Authorization\DurablePolicyMutation;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Authorization\PolicyAssignmentScope;
use App\Application\Authorization\PolicyMutationId;
use App\Application\Authorization\PolicyMutationOperation;
use App\Application\Authorization\RoleIdentifier;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Authorization\LaravelDurablePolicyAdministrationRepository;
use App\Infrastructure\Authorization\LaravelDurableRolePermissionRepository;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;

// Author by Lab | zefry
if (! isset($app, $assert) || ! is_callable($assert)) {
    throw new RuntimeException('Sprint 22 policy administration regression requires the M7.1 application harness.');
}
$assert(extension_loaded('pdo_sqlite'), 'Sprint 22 policy administration regression requires pdo_sqlite.');

$s22Remove = static function (string $path): void {
    if (is_file($path) || is_link($path)) { @unlink($path); return; }
    if (! is_dir($path)) { return; }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($path);
};

$s22Parent = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s22-admin-'.getmypid();
$s22Remove($s22Parent);
$assert(@mkdir($s22Parent, 0700, false), 'Sprint 22 workspace create failed.');
$s22DbPath = $s22Parent.DIRECTORY_SEPARATOR.'policy.sqlite';
$assert(touch($s22DbPath), 'Sprint 22 SQLite create failed.');
$app['config']->set('database.connections.s22_admin', [
    'driver' => 'sqlite', 'url' => null, 'database' => $s22DbPath, 'prefix' => '',
    'foreign_key_constraints' => true, 'busy_timeout' => null, 'journal_mode' => null, 'synchronous' => null,
]);
/** @var \Illuminate\Database\DatabaseManager $s22Manager */
$s22Manager = $app->make('db');
$s22Manager->purge('s22_admin');
$s22Manager->setDefaultConnection('s22_admin');
$s22Connection = $s22Manager->connection('s22_admin');
$s22Connection->getPdo();

$s22AlphaActor = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('synthetic-admin-alpha'), TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-shared'), OutletId::fromString('outlet-shared'), DeviceId::fromString('device-shared'),
);
$s22PreSchemaMutation = DurablePolicyMutation::roleCreate(PolicyMutationId::fromString('guard-disabled'), $s22AlphaActor, RoleIdentifier::fromString('synthetic-operator'));
foreach ([[false, 'ci', DurablePolicyAdministrationViolation::PERSISTENCE_DISABLED], [true, 'preview', DurablePolicyAdministrationViolation::RUNTIME_DENIED], [true, 'production', DurablePolicyAdministrationViolation::RUNTIME_DENIED]] as [$enabled, $runtime, $code]) {
    try {
        (new LaravelDurablePolicyAdministrationRepository($s22Connection, $enabled, $runtime))->replayOutcome($s22AlphaActor, $s22PreSchemaMutation);
        $assert(false, 'Sprint 22 unauthorized runtime reached storage.');
    } catch (DurablePolicyAdministrationViolation $exception) {
        $assert($exception->errorCode === $code, 'Sprint 22 runtime guard returned unexpected code.');
    }
}
$assert(! $s22Connection->getSchemaBuilder()->hasTable('oneqay_policy_mutations'), 'Sprint 22 runtime denial occurred after schema mutation.');

$s22Migrations = [
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
$s22Actual = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($s22Actual);
$assert($s22Actual === $s22Migrations, 'Sprint 22 preservation requires the exact nine-migration set through Sprint 30.');
foreach ($s22Migrations as $migration) { (require __DIR__.'/../database/migrations/'.$migration)->up(); }
$assert($s22Connection->getSchemaBuilder()->hasTable('oneqay_policy_mutations'), 'Sprint 22 mutation journal missing.');
$assert($s22Connection->getSchemaBuilder()->hasTable('oneqay_initial_tenant_admin_provisionings'), 'Sprint 23 provisioning journal missing during Sprint 22 preservation.');
$assert($s22Connection->getSchemaBuilder()->hasTable('oneqay_protected_control_admin_mutations'), 'Sprint 24 lifecycle journal missing during Sprint 22 preservation.');
$assert($s22Connection->getSchemaBuilder()->hasTable('oneqay_identity_password_credentials'), 'Sprint 26 credential table missing during Sprint 22 preservation.');
$assert($s22Connection->getSchemaBuilder()->hasTable('oneqay_initial_password_enrollments'), 'Sprint 28 enrollment table missing during Sprint 22 preservation.');

$s22Connection->table('oneqay_tenants')->insert([['id' => 'tenant-alpha'], ['id' => 'tenant-beta']]);
$s22Connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-device-admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-target-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-no-membership-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-admin-beta'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-target-beta'],
]);
$s22Connection->table('oneqay_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-shared'],
    ['tenant_id' => 'tenant-beta', 'id' => 'organization-shared'],
]);
$s22Connection->table('oneqay_identity_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-admin-alpha', 'organization_id' => 'organization-shared'],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-device-admin-alpha', 'organization_id' => 'organization-shared'],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-target-alpha', 'organization_id' => 'organization-shared'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-admin-beta', 'organization_id' => 'organization-shared'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-target-beta', 'organization_id' => 'organization-shared'],
]);
$s22Connection->table('oneqay_outlets')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'outlet-shared', 'organization_id' => 'organization-shared'],
    ['tenant_id' => 'tenant-beta', 'id' => 'outlet-shared', 'organization_id' => 'organization-shared'],
]);
$s22Connection->table('oneqay_devices')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'device-shared', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared'],
    ['tenant_id' => 'tenant-beta', 'id' => 'device-shared', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared'],
]);
foreach ([
    ['tenant-alpha', 'synthetic-admin-alpha'],
    ['tenant-alpha', 'synthetic-device-admin-alpha'],
    ['tenant-alpha', 'synthetic-target-alpha'],
    ['tenant-beta', 'synthetic-admin-beta'],
    ['tenant-beta', 'synthetic-target-beta'],
] as [$tenant, $identity]) {
    $s22Connection->table('oneqay_outlet_access_grants')->insert(['tenant_id' => $tenant, 'identity_id' => $identity, 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared']);
    $s22Connection->table('oneqay_device_access_grants')->insert(['tenant_id' => $tenant, 'identity_id' => $identity, 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared', 'device_id' => 'device-shared']);
}
foreach ([['tenant-alpha', 'synthetic-admin-alpha'], ['tenant-beta', 'synthetic-admin-beta']] as [$tenant, $identity]) {
    $s22Connection->table('oneqay_roles')->insert(['tenant_id' => $tenant, 'id' => 'synthetic-control-role']);
    $s22Connection->table('oneqay_role_permissions')->insert(['tenant_id' => $tenant, 'role_id' => 'synthetic-control-role', 'permission_id' => AdministrationPermission::MANAGE]);
    $s22Connection->table('oneqay_tenant_role_assignments')->insert(['tenant_id' => $tenant, 'identity_id' => $identity, 'role_id' => 'synthetic-control-role']);
}
$s22Connection->table('oneqay_roles')->insert(['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-device-control-role']);
$s22Connection->table('oneqay_role_permissions')->insert(['tenant_id' => 'tenant-alpha', 'role_id' => 'synthetic-device-control-role', 'permission_id' => AdministrationPermission::MANAGE]);
$s22Connection->table('oneqay_device_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'synthetic-device-admin-alpha',
    'organization_id' => 'organization-shared',
    'outlet_id' => 'outlet-shared',
    'device_id' => 'device-shared',
    'role_id' => 'synthetic-device-control-role',
]);

$s22Read = new LaravelDurableRolePermissionRepository($s22Connection, true, 'ci');
$s22Policy = new DurableScopedAuthorizationPolicy($s22Read);
$s22AdminRepo = new LaravelDurablePolicyAdministrationRepository($s22Connection, true, 'ci');
$s22Transaction = new LaravelPersistenceTransaction($s22Connection, true, 'ci');
$s22Clock = new class implements PolicyAdministrationClock { public function nowUnix(): int { return 1786989000; } };
$s22Service = new DurablePolicyAdministrationService($s22Policy, $s22AdminRepo, $s22Transaction, $s22Clock);

$s22BetaActor = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('synthetic-admin-beta'), TenantId::fromString('tenant-beta'),
    OrganizationId::fromString('organization-shared'), OutletId::fromString('outlet-shared'), DeviceId::fromString('device-shared'),
);
$s22BetaService = new DurablePolicyAdministrationService($s22Policy, $s22AdminRepo, $s22Transaction, $s22Clock);
$s22DeviceAdminActor = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('synthetic-device-admin-alpha'), TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-shared'), OutletId::fromString('outlet-shared'), DeviceId::fromString('device-shared'),
);
$s22DeviceAdminService = new DurablePolicyAdministrationService($s22Policy, $s22AdminRepo, $s22Transaction, $s22Clock);
$s22TargetAlpha = PlatformIdentityId::fromString('synthetic-target-alpha');
$s22TargetContext = new VerifiedOrganizationalContext($s22TargetAlpha, TenantId::fromString('tenant-alpha'), OrganizationId::fromString('organization-shared'), OutletId::fromString('outlet-shared'), DeviceId::fromString('device-shared'));
$s22Operator = RoleIdentifier::fromString('synthetic-operator');
$s22Execute = PermissionIdentifier::fromString('synthetic.resource.execute');

$s22Create = DurablePolicyMutation::roleCreate(PolicyMutationId::fromString('mutation-create'), $s22AlphaActor, $s22Operator);
$assert($s22Service->apply($s22AlphaActor, $s22Create) === 'applied', 'Sprint 22 role create not applied.');
$assert($s22Service->apply($s22AlphaActor, $s22Create) === 'applied', 'Sprint 22 exact replay did not return prior outcome.');
$assert($s22Connection->table('oneqay_roles')->where('tenant_id', 'tenant-alpha')->where('id', 'synthetic-operator')->count() === 1, 'Sprint 22 role replay duplicated role.');

try {
    $s22Service->apply($s22AlphaActor, DurablePolicyMutation::roleCreate(PolicyMutationId::fromString('mutation-create'), $s22AlphaActor, RoleIdentifier::fromString('synthetic-different-role')));
    $assert(false, 'Sprint 22 conflicting mutation replay was accepted.');
} catch (DurablePolicyAdministrationViolation $exception) {
    $assert($exception->errorCode === DurablePolicyAdministrationViolation::MUTATION_CONFLICT, 'Sprint 22 replay conflict returned wrong code.');
}

$s22Grant = DurablePolicyMutation::permissionGrant(PolicyMutationId::fromString('mutation-grant'), $s22AlphaActor, $s22Operator, $s22Execute);
$assert($s22Service->apply($s22AlphaActor, $s22Grant) === 'applied', 'Sprint 22 permission grant failed.');

$assert($s22Policy->allows($s22DeviceAdminActor, AdministrationPermission::manage()), 'Sprint 22 device-scoped control fixture was not recognized at its exact device context.');
foreach ([
    DurablePolicyMutation::roleCreate(PolicyMutationId::fromString('scope-escape-role-create'), $s22DeviceAdminActor, RoleIdentifier::fromString('synthetic-scope-escape-role')),
    DurablePolicyMutation::permissionGrant(PolicyMutationId::fromString('scope-escape-permission'), $s22DeviceAdminActor, $s22Operator, PermissionIdentifier::fromString('synthetic.resource.scope-test')),
    DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString('scope-escape-tenant'), $s22DeviceAdminActor, PolicyMutationOperation::ROLE_ASSIGN_TENANT, $s22TargetAlpha, $s22Operator),
    DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString('scope-escape-organization'), $s22DeviceAdminActor, PolicyMutationOperation::ROLE_ASSIGN_ORGANIZATION, $s22TargetAlpha, $s22Operator),
    DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString('scope-escape-outlet'), $s22DeviceAdminActor, PolicyMutationOperation::ROLE_ASSIGN_OUTLET, $s22TargetAlpha, $s22Operator),
] as $scopeEscapeMutation) {
    try {
        $s22DeviceAdminService->apply($s22DeviceAdminActor, $scopeEscapeMutation);
        $assert(false, 'Sprint 22 narrower device control authority escaped to a broader policy scope.');
    } catch (DurablePolicyAdministrationViolation $exception) {
        $assert($exception->errorCode === DurablePolicyAdministrationViolation::AUTHORIZATION_DENIED, 'Sprint 22 scope-containment denial returned wrong code.');
    }
}
$assert($s22Connection->table('oneqay_policy_mutations')->where('tenant_id', 'tenant-alpha')->whereIn('mutation_id', ['scope-escape-role-create', 'scope-escape-permission', 'scope-escape-tenant', 'scope-escape-organization', 'scope-escape-outlet'])->doesntExist(), 'Sprint 22 scope-containment denial wrote mutation journal state.');

$s22DeviceAssign = DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString('device-control-assign'), $s22DeviceAdminActor, PolicyMutationOperation::ROLE_ASSIGN_DEVICE, $s22TargetAlpha, $s22Operator);
$assert($s22DeviceAdminService->apply($s22DeviceAdminActor, $s22DeviceAssign) === 'applied', 'Sprint 22 exact device-scoped administration was denied.');
$assert($s22Policy->allows($s22TargetContext, $s22Execute), 'Sprint 22 exact device-scoped administration did not apply target permission.');
$s22DeviceRevoke = DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString('device-control-revoke'), $s22DeviceAdminActor, PolicyMutationOperation::ROLE_REVOKE_DEVICE, $s22TargetAlpha, $s22Operator);
$assert($s22DeviceAdminService->apply($s22DeviceAdminActor, $s22DeviceRevoke) === 'applied', 'Sprint 22 exact device-scoped revocation was denied.');
$assert(! $s22Policy->allows($s22TargetContext, $s22Execute), 'Sprint 22 exact device-scoped revocation left permission active.');

$s22AssignTenant = DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString('mutation-assign-tenant'), $s22AlphaActor, PolicyMutationOperation::ROLE_ASSIGN_TENANT, $s22TargetAlpha, $s22Operator);
$assert($s22Service->apply($s22AlphaActor, $s22AssignTenant) === 'applied', 'Sprint 22 tenant assignment failed.');
$assert($s22Policy->allows($s22TargetContext, $s22Execute), 'Sprint 22 assigned target did not gain exact permission.');
$s22RevokeTenant = DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString('mutation-revoke-tenant'), $s22AlphaActor, PolicyMutationOperation::ROLE_REVOKE_TENANT, $s22TargetAlpha, $s22Operator);
$assert($s22Service->apply($s22AlphaActor, $s22RevokeTenant) === 'applied', 'Sprint 22 tenant revocation failed.');
$assert(! $s22Policy->allows($s22TargetContext, $s22Execute), 'Sprint 22 tenant revocation left permission active.');

foreach ([
    [PolicyMutationOperation::ROLE_ASSIGN_ORGANIZATION, 'mutation-assign-org'],
    [PolicyMutationOperation::ROLE_ASSIGN_OUTLET, 'mutation-assign-outlet'],
    [PolicyMutationOperation::ROLE_ASSIGN_DEVICE, 'mutation-assign-device'],
] as [$operation, $id]) {
    $mutation = DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString($id), $s22AlphaActor, $operation, $s22TargetAlpha, $s22Operator);
    $assert($s22Service->apply($s22AlphaActor, $mutation) === 'applied', 'Sprint 22 scoped assignment failed: '.$operation);
}

try {
    $s22Service->apply($s22AlphaActor, DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString('mutation-no-membership'), $s22AlphaActor, PolicyMutationOperation::ROLE_ASSIGN_ORGANIZATION, PlatformIdentityId::fromString('synthetic-no-membership-alpha'), $s22Operator));
    $assert(false, 'Sprint 22 assigned organization role without membership.');
} catch (DurablePolicyAdministrationViolation $exception) {
    $assert($exception->errorCode === DurablePolicyAdministrationViolation::TARGET_ACCESS_DENIED, 'Sprint 22 missing membership returned wrong code.');
}

try {
    $s22Service->apply($s22AlphaActor, DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString('mutation-cross-tenant'), $s22AlphaActor, PolicyMutationOperation::ROLE_ASSIGN_TENANT, PlatformIdentityId::fromString('synthetic-target-beta'), $s22Operator));
    $assert(false, 'Sprint 22 accepted cross-tenant target identity.');
} catch (DurablePolicyAdministrationViolation $exception) {
    $assert($exception->errorCode === DurablePolicyAdministrationViolation::TARGET_ACCESS_DENIED, 'Sprint 22 cross-tenant target returned wrong code.');
}

foreach ([
    DurablePolicyMutation::permissionGrant(PolicyMutationId::fromString('mutation-control-grant'), $s22AlphaActor, $s22Operator, AdministrationPermission::manage()),
    DurablePolicyMutation::permissionRevoke(PolicyMutationId::fromString('mutation-control-revoke'), $s22AlphaActor, RoleIdentifier::fromString('synthetic-control-role'), AdministrationPermission::manage()),
    DurablePolicyMutation::permissionGrant(PolicyMutationId::fromString('mutation-control-other'), $s22AlphaActor, RoleIdentifier::fromString('synthetic-control-role'), PermissionIdentifier::fromString('synthetic.resource.extra')),
    DurablePolicyMutation::roleAssignment(PolicyMutationId::fromString('mutation-control-assign'), $s22AlphaActor, PolicyMutationOperation::ROLE_ASSIGN_TENANT, $s22TargetAlpha, RoleIdentifier::fromString('synthetic-control-role')),
] as $protectedMutation) {
    try {
        $s22Service->apply($s22AlphaActor, $protectedMutation);
        $assert(false, 'Sprint 22 protected control authority mutation was accepted.');
    } catch (DurablePolicyAdministrationViolation $exception) {
        $assert($exception->errorCode === DurablePolicyAdministrationViolation::PROTECTED_CONTROL_AUTHORITY, 'Sprint 22 protected control mutation returned wrong code.');
    }
}

$s22BetaCreate = DurablePolicyMutation::roleCreate(PolicyMutationId::fromString('mutation-create'), $s22BetaActor, $s22Operator);
$assert($s22BetaService->apply($s22BetaActor, $s22BetaCreate) === 'applied', 'Sprint 22 same mutation ID was not independent across tenants.');
$assert($s22Connection->table('oneqay_policy_mutations')->where('mutation_id', 'mutation-create')->count() === 2, 'Sprint 22 mutation ID was incorrectly global.');

$s22NoAuthActor = new VerifiedOrganizationalContext($s22TargetAlpha, TenantId::fromString('tenant-alpha'), OrganizationId::fromString('organization-shared'), OutletId::fromString('outlet-shared'), DeviceId::fromString('device-shared'));
try {
    $s22Service->apply($s22NoAuthActor, DurablePolicyMutation::roleCreate(PolicyMutationId::fromString('mutation-no-auth'), $s22NoAuthActor, RoleIdentifier::fromString('synthetic-unauthorized')));
    $assert(false, 'Sprint 22 allowed principal without policy.manage.');
} catch (DurablePolicyAdministrationViolation $exception) {
    $assert($exception->errorCode === DurablePolicyAdministrationViolation::AUTHORIZATION_DENIED, 'Sprint 22 unauthorized actor returned wrong code.');
}

$s22FailRepo = new class($s22Connection) implements DurablePolicyAdministrationRepository {
    public function __construct(private \Illuminate\Database\Connection $connection) {}
    public function replayOutcome(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): ?string { return null; }
    public function hasControlAuthorityForScope(VerifiedOrganizationalContext $actor, PolicyAssignmentScope $scope): bool { return true; }
    public function isProtectedControlRole(VerifiedOrganizationalContext $actor, RoleIdentifier $role): bool { return false; }
    public function assertTargetEligible(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): void {}
    public function applyFresh(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation, int $occurredAtUnix): string {
        $this->connection->table('oneqay_roles')->insert(['tenant_id' => $actor->tenantId()->value(), 'id' => 'synthetic-rollback-role']);
        throw new RuntimeException('synthetic-policy-transaction-failure');
    }
};
$s22FailService = new DurablePolicyAdministrationService($s22Policy, $s22FailRepo, $s22Transaction, $s22Clock);
try {
    $s22FailService->apply($s22AlphaActor, DurablePolicyMutation::roleCreate(PolicyMutationId::fromString('mutation-rollback'), $s22AlphaActor, RoleIdentifier::fromString('synthetic-rollback-role')));
    $assert(false, 'Sprint 22 transaction failure was accepted.');
} catch (DurablePolicyAdministrationViolation $exception) {
    $assert($exception->errorCode === DurablePolicyAdministrationViolation::TRANSACTION_FAILURE, 'Sprint 22 transaction failure returned wrong code.');
}
$assert($s22Connection->table('oneqay_roles')->where('tenant_id', 'tenant-alpha')->where('id', 'synthetic-rollback-role')->doesntExist(), 'Sprint 22 transaction failure left a partial role.');

$s22RevokePermission = DurablePolicyMutation::permissionRevoke(PolicyMutationId::fromString('mutation-permission-revoke'), $s22AlphaActor, $s22Operator, $s22Execute);
$assert($s22Service->apply($s22AlphaActor, $s22RevokePermission) === 'applied', 'Sprint 22 permission revoke failed.');
$assert($s22Connection->table('oneqay_role_permissions')->where('tenant_id', 'tenant-alpha')->where('role_id', 'synthetic-operator')->where('permission_id', 'synthetic.resource.execute')->doesntExist(), 'Sprint 22 permission revoke left exact grant.');

$s22RepoSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelDurablePolicyAdministrationRepository.php');
$assert(! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $s22RepoSource), 'Sprint 22 repository introduced ownership-rewriting upsert.');
$assert(substr_count($s22RepoSource, "->where('tenant_id',") >= 8, 'Sprint 22 repository lost tenant-scoped predicates.');
$assert(str_contains($s22RepoSource, "in_array(\$runtime, ['local', 'test', 'ci'], true)"), 'Sprint 22 runtime allowlist changed.');
$assert(str_contains($s22RepoSource, 'controlAuthorityExistsForScope'), 'Sprint 22 repository lost control-authority scope containment.');
$assert($s22Connection->table('oneqay_policy_mutations')->where('tenant_id', 'tenant-alpha')->count() > 0, 'Sprint 22 mutation journal remained empty.');

$s22Manager->disconnect('s22_admin');
$s22Manager->purge('s22_admin');
$app['config']->set('database.connections.s22_admin', null);
@unlink($s22DbPath);
$s22Remove($s22Parent);
$assert(! file_exists($s22Parent), 'Sprint 22 workspace cleanup failed.');