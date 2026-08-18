<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\DurablePolicyAdministrationService;
use App\Application\Authorization\DurablePolicyAdministrationViolation;
use App\Application\Authorization\DurablePolicyMutation;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Authorization\InitialTenantAdministratorProvisioningService;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Authorization\PolicyMutationId;
use App\Application\Authorization\PolicyMutationOperation;
use App\Application\Authorization\ProtectedControlAdministratorLifecycleRepository;
use App\Application\Authorization\ProtectedControlAdministratorLifecycleService;
use App\Application\Authorization\ProtectedControlAdministratorLifecycleViolation;
use App\Application\Authorization\ProtectedControlAdministratorMutationId;
use App\Application\Authorization\ProtectedControlAdministratorOperation;
use App\Application\Authorization\RoleIdentifier;
use App\Application\Identity\VerifiedPlatformIdentity;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Authorization\LaravelDurablePolicyAdministrationRepository;
use App\Infrastructure\Authorization\LaravelDurableRolePermissionRepository;
use App\Infrastructure\Authorization\LaravelInitialTenantAdministratorProvisioningRepository;
use App\Infrastructure\Authorization\LaravelProtectedControlAdministratorLifecycleRepository;
use App\Infrastructure\Authorization\PreauthorizedInitialTenantAdministratorProvisioningAuthority;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;

// Author by Lab | zefry
if (! isset($app, $assert) || ! is_callable($assert)) {
    throw new RuntimeException('Sprint 24 protected control administrator lifecycle regression requires the M7.1 application harness.');
}
$assert(extension_loaded('pdo_sqlite'), 'Sprint 24 protected control administrator lifecycle regression requires pdo_sqlite.');

$s24Remove = static function (string $path): void {
    if (is_file($path) || is_link($path)) { @unlink($path); return; }
    if (! is_dir($path)) { return; }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($path);
};

$s24Parent = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s24-control-lifecycle-'.getmypid();
$s24Remove($s24Parent);
$assert(@mkdir($s24Parent, 0700, false), 'Sprint 24 workspace create failed.');
$s24DbPath = $s24Parent.DIRECTORY_SEPARATOR.'control-lifecycle.sqlite';
$assert(touch($s24DbPath), 'Sprint 24 SQLite create failed.');
$app['config']->set('database.connections.s24_control_lifecycle', [
    'driver' => 'sqlite', 'url' => null, 'database' => $s24DbPath, 'prefix' => '',
    'foreign_key_constraints' => true, 'busy_timeout' => null, 'journal_mode' => null, 'synchronous' => null,
]);
/** @var \Illuminate\Database\DatabaseManager $s24Manager */
$s24Manager = $app->make('db');
$s24Manager->purge('s24_control_lifecycle');
$s24Manager->setDefaultConnection('s24_control_lifecycle');
$s24Connection = $s24Manager->connection('s24_control_lifecycle');
$s24Connection->getPdo();

$s24ActorAlpha = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('synthetic-admin-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha'),
);
$s24PreSchemaMutationId = ProtectedControlAdministratorMutationId::fromString('guard-before-schema');
foreach ([[false, 'ci', ProtectedControlAdministratorLifecycleViolation::PERSISTENCE_DISABLED], [true, 'preview', ProtectedControlAdministratorLifecycleViolation::RUNTIME_DENIED], [true, 'production', ProtectedControlAdministratorLifecycleViolation::RUNTIME_DENIED]] as [$enabled, $runtime, $code]) {
    try {
        (new LaravelProtectedControlAdministratorLifecycleRepository($s24Connection, $enabled, $runtime))->hasTenantControlAuthority($s24ActorAlpha);
        $assert(false, 'Sprint 24 unauthorized runtime reached lifecycle storage.');
    } catch (ProtectedControlAdministratorLifecycleViolation $exception) {
        $assert($exception->errorCode === $code, 'Sprint 24 runtime guard returned unexpected code.');
    }
}
$assert(! $s24Connection->getSchemaBuilder()->hasTable('oneqay_protected_control_admin_mutations'), 'Sprint 24 runtime denial occurred after schema mutation.');

$s24Migrations = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
    '0000_00_00_000007_create_identity_password_credentials.php',
    '0000_00_00_000008_create_initial_password_enrollments.php',
];
$s24Actual = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($s24Actual);
$assert($s24Actual === $s24Migrations, 'Sprint 24 preservation requires exact eight-migration set through Sprint 28.');
foreach ($s24Migrations as $migration) { (require __DIR__.'/../database/migrations/'.$migration)->up(); }
$assert($s24Connection->getSchemaBuilder()->hasTable('oneqay_protected_control_admin_mutations'), 'Sprint 24 lifecycle journal missing.');
$assert($s24Connection->getSchemaBuilder()->hasTable('oneqay_identity_password_credentials'), 'Sprint 26 credential table missing during Sprint 24 preservation.');
$assert($s24Connection->getSchemaBuilder()->hasTable('oneqay_initial_password_enrollments'), 'Sprint 28 enrollment table missing during Sprint 24 preservation.');
$s24JournalColumns = $s24Connection->getSchemaBuilder()->getColumnListing('oneqay_protected_control_admin_mutations');
sort($s24JournalColumns);
$s24ExpectedJournalColumns = ['actor_identity_id', 'mutation_id', 'occurred_at_unix', 'operation', 'outcome', 'payload_fingerprint', 'permission_id', 'role_id', 'target_identity_id', 'tenant_id'];
sort($s24ExpectedJournalColumns);
$assert($s24JournalColumns === $s24ExpectedJournalColumns, 'Sprint 24 lifecycle journal contains unauthorized columns.');

foreach (['tenant-alpha', 'tenant-beta', 'tenant-gamma'] as $tenant) {
    $s24Connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
$s24Connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-delegate-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-second-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-device-admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-shared-id'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-admin-beta'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-delegate-beta'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-shared-id'],
    ['tenant_id' => 'tenant-gamma', 'id' => 'synthetic-admin-gamma'],
]);
foreach ([
    ['tenant-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha'],
    ['tenant-beta', 'organization-beta', 'outlet-beta', 'device-beta'],
    ['tenant-gamma', 'organization-gamma', 'outlet-gamma', 'device-gamma'],
] as [$tenant, $organization, $outlet, $device]) {
    $s24Connection->table('oneqay_organizations')->insert(['tenant_id' => $tenant, 'id' => $organization]);
    $s24Connection->table('oneqay_outlets')->insert(['tenant_id' => $tenant, 'id' => $outlet, 'organization_id' => $organization]);
    $s24Connection->table('oneqay_devices')->insert(['tenant_id' => $tenant, 'id' => $device, 'organization_id' => $organization, 'outlet_id' => $outlet]);
}
foreach (['synthetic-admin-alpha', 'synthetic-delegate-alpha', 'synthetic-second-alpha', 'synthetic-device-admin-alpha', 'synthetic-shared-id'] as $identity) {
    $s24Connection->table('oneqay_identity_organizations')->insert(['tenant_id' => 'tenant-alpha', 'identity_id' => $identity, 'organization_id' => 'organization-alpha']);
    $s24Connection->table('oneqay_outlet_access_grants')->insert(['tenant_id' => 'tenant-alpha', 'identity_id' => $identity, 'organization_id' => 'organization-alpha', 'outlet_id' => 'outlet-alpha']);
    $s24Connection->table('oneqay_device_access_grants')->insert(['tenant_id' => 'tenant-alpha', 'identity_id' => $identity, 'organization_id' => 'organization-alpha', 'outlet_id' => 'outlet-alpha', 'device_id' => 'device-alpha']);
}
foreach (['synthetic-admin-beta', 'synthetic-delegate-beta', 'synthetic-shared-id'] as $identity) {
    $s24Connection->table('oneqay_identity_organizations')->insert(['tenant_id' => 'tenant-beta', 'identity_id' => $identity, 'organization_id' => 'organization-beta']);
    $s24Connection->table('oneqay_outlet_access_grants')->insert(['tenant_id' => 'tenant-beta', 'identity_id' => $identity, 'organization_id' => 'organization-beta', 'outlet_id' => 'outlet-beta']);
    $s24Connection->table('oneqay_device_access_grants')->insert(['tenant_id' => 'tenant-beta', 'identity_id' => $identity, 'organization_id' => 'organization-beta', 'outlet_id' => 'outlet-beta', 'device_id' => 'device-beta']);
}
$s24Connection->table('oneqay_identity_organizations')->insert(['tenant_id' => 'tenant-gamma', 'identity_id' => 'synthetic-admin-gamma', 'organization_id' => 'organization-gamma']);
$s24Connection->table('oneqay_outlet_access_grants')->insert(['tenant_id' => 'tenant-gamma', 'identity_id' => 'synthetic-admin-gamma', 'organization_id' => 'organization-gamma', 'outlet_id' => 'outlet-gamma']);
$s24Connection->table('oneqay_device_access_grants')->insert(['tenant_id' => 'tenant-gamma', 'identity_id' => 'synthetic-admin-gamma', 'organization_id' => 'organization-gamma', 'outlet_id' => 'outlet-gamma', 'device_id' => 'device-gamma']);

$s24VerifiedIdentity = static fn (string $id): VerifiedPlatformIdentity => new class($id) implements VerifiedPlatformIdentity {
    public function __construct(private string $id) {}
    public function identityId(): string { return $this->id; }
};
$s24Clock = new class implements PolicyAdministrationClock { public function nowUnix(): int { return 1787017200; } };
$s24Transaction = new LaravelPersistenceTransaction($s24Connection, true, 'ci');
$s24ProvisionAuthority = new PreauthorizedInitialTenantAdministratorProvisioningAuthority([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-admin-alpha', 'provisioning_id' => 'bootstrap-alpha'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-admin-beta', 'provisioning_id' => 'bootstrap-beta'],
    ['tenant_id' => 'tenant-gamma', 'identity_id' => 'synthetic-admin-gamma', 'provisioning_id' => 'bootstrap-gamma'],
]);
$s24ProvisionRepo = new LaravelInitialTenantAdministratorProvisioningRepository($s24Connection, true, 'ci');
$s24ProvisionService = new InitialTenantAdministratorProvisioningService($s24ProvisionAuthority, $s24ProvisionRepo, $s24Transaction, $s24Clock);
foreach ([
    ['tenant-alpha', 'synthetic-admin-alpha', 'bootstrap-alpha'],
    ['tenant-beta', 'synthetic-admin-beta', 'bootstrap-beta'],
    ['tenant-gamma', 'synthetic-admin-gamma', 'bootstrap-gamma'],
] as [$tenant, $identity, $provisioning]) {
    $assert($s24ProvisionService->provision($s24VerifiedIdentity($identity), TenantId::fromString($tenant), InitialTenantAdministratorProvisioningId::fromString($provisioning)) === 'applied', 'Sprint 24 setup could not establish Sprint 23 initial control principal.');
}

$s24ProvisioningEvidenceBefore = $s24Connection->table('oneqay_initial_tenant_admin_provisionings')->orderBy('tenant_id')->get()->map(static fn ($row): array => (array) $row)->all();
$s24PolicyEvidenceBefore = $s24Connection->table('oneqay_policy_mutations')->count();

// The same canonical protected role may exist at a narrower device scope, but that actor must not delegate tenant control.
$s24Connection->table('oneqay_device_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'synthetic-device-admin-alpha',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-alpha',
    'role_id' => ProtectedControlAdministratorLifecycleRepository::CONTROL_ROLE,
]);

$s24Repo = new LaravelProtectedControlAdministratorLifecycleRepository($s24Connection, true, 'ci');
$s24Service = new ProtectedControlAdministratorLifecycleService($s24Repo, $s24Transaction, $s24Clock);
$s24AlphaActor = new VerifiedOrganizationalContext(PlatformIdentityId::fromString('synthetic-admin-alpha'), TenantId::fromString('tenant-alpha'), OrganizationId::fromString('organization-alpha'), OutletId::fromString('outlet-alpha'), DeviceId::fromString('device-alpha'));
$s24BetaActor = new VerifiedOrganizationalContext(PlatformIdentityId::fromString('synthetic-admin-beta'), TenantId::fromString('tenant-beta'), OrganizationId::fromString('organization-beta'), OutletId::fromString('outlet-beta'), DeviceId::fromString('device-beta'));
$s24DeviceActor = new VerifiedOrganizationalContext(PlatformIdentityId::fromString('synthetic-device-admin-alpha'), TenantId::fromString('tenant-alpha'), OrganizationId::fromString('organization-alpha'), OutletId::fromString('outlet-alpha'), DeviceId::fromString('device-alpha'));

$assert($s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-delegate-alpha'), ProtectedControlAdministratorMutationId::fromString('delegate-alpha-one'), ProtectedControlAdministratorOperation::delegate()) === 'applied', 'Sprint 24 delegation was not applied.');
$assert($s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-delegate-alpha'), ProtectedControlAdministratorMutationId::fromString('delegate-alpha-one'), ProtectedControlAdministratorOperation::delegate()) === 'applied', 'Sprint 24 exact replay did not return prior applied outcome.');
$assert($s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-delegate-alpha'), ProtectedControlAdministratorMutationId::fromString('delegate-alpha-nochange'), ProtectedControlAdministratorOperation::delegate()) === 'no_change', 'Sprint 24 already-assigned delegation did not return no_change.');
$assert($s24Connection->table('oneqay_tenant_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-delegate-alpha')->where('role_id', ProtectedControlAdministratorLifecycleRepository::CONTROL_ROLE)->count() === 1, 'Sprint 24 delegation duplicated tenant control assignment.');
foreach (['oneqay_organization_role_assignments', 'oneqay_outlet_role_assignments', 'oneqay_device_role_assignments'] as $table) {
    $assert($s24Connection->table($table)->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-delegate-alpha')->where('role_id', ProtectedControlAdministratorLifecycleRepository::CONTROL_ROLE)->doesntExist(), 'Sprint 24 delegation created a non-tenant assignment.');
}
$assert($s24Connection->table('oneqay_role_permissions')->where('tenant_id', 'tenant-alpha')->where('role_id', ProtectedControlAdministratorLifecycleRepository::CONTROL_ROLE)->count() === 1, 'Sprint 24 altered canonical control-role permissions.');
$assert($s24Connection->table('oneqay_role_permissions')->where('tenant_id', 'tenant-alpha')->where('role_id', ProtectedControlAdministratorLifecycleRepository::CONTROL_ROLE)->where('permission_id', AdministrationPermission::MANAGE)->exists(), 'Sprint 24 canonical control permission missing.');

$s24ReadRepo = new LaravelDurableRolePermissionRepository($s24Connection, true, 'ci');
$s24Policy = new DurableScopedAuthorizationPolicy($s24ReadRepo);
$s24DelegatedContext = new VerifiedOrganizationalContext(PlatformIdentityId::fromString('synthetic-delegate-alpha'), TenantId::fromString('tenant-alpha'), OrganizationId::fromString('organization-alpha'), OutletId::fromString('outlet-alpha'), DeviceId::fromString('device-alpha'));
$assert($s24Policy->allows($s24DelegatedContext, AdministrationPermission::manage()), 'Sprint 24 delegated target did not gain exact tenant control permission.');

try {
    $s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-second-alpha'), ProtectedControlAdministratorMutationId::fromString('delegate-alpha-one'), ProtectedControlAdministratorOperation::delegate());
    $assert(false, 'Sprint 24 same mutation ID with different target was accepted.');
} catch (ProtectedControlAdministratorLifecycleViolation $exception) {
    $assert($exception->errorCode === ProtectedControlAdministratorLifecycleViolation::MUTATION_CONFLICT, 'Sprint 24 mutation conflict returned wrong code.');
}
try {
    $s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-shared-id'), ProtectedControlAdministratorMutationId::fromString('delegate-foreign'), ProtectedControlAdministratorOperation::delegate());
    // Same textual ID exists in alpha, therefore this must be valid and tenant-bound to alpha.
} catch (Throwable $exception) {
    $assert(false, 'Sprint 24 same textual target ID was not tenant-bound to actor tenant: '.$exception::class);
}
$assert($s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-shared-id'), ProtectedControlAdministratorMutationId::fromString('revoke-shared-fixture'), ProtectedControlAdministratorOperation::revoke()) === 'applied', 'Sprint 24 same-textual-ID fixture cleanup failed.');
$assert($s24Connection->table('oneqay_tenant_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-shared-id')->where('role_id', ProtectedControlAdministratorLifecycleRepository::CONTROL_ROLE)->doesntExist(), 'Sprint 24 same-textual-ID fixture remained a control principal.');
try {
    $s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-delegate-beta'), ProtectedControlAdministratorMutationId::fromString('delegate-cross-tenant'), ProtectedControlAdministratorOperation::delegate());
    $assert(false, 'Sprint 24 foreign-tenant target was accepted.');
} catch (ProtectedControlAdministratorLifecycleViolation $exception) {
    $assert($exception->errorCode === ProtectedControlAdministratorLifecycleViolation::TENANT_RELATIONSHIP_DENIED, 'Sprint 24 foreign target returned wrong code.');
}
try {
    $s24Service->apply($s24DeviceActor, $s24VerifiedIdentity('synthetic-second-alpha'), ProtectedControlAdministratorMutationId::fromString('device-scope-escape'), ProtectedControlAdministratorOperation::delegate());
    $assert(false, 'Sprint 24 device-scoped control actor delegated tenant control.');
} catch (ProtectedControlAdministratorLifecycleViolation $exception) {
    $assert($exception->errorCode === ProtectedControlAdministratorLifecycleViolation::AUTHORIZATION_DENIED, 'Sprint 24 narrower-scope denial returned wrong code.');
}
$assert($s24Connection->table('oneqay_protected_control_admin_mutations')->where('tenant_id', 'tenant-alpha')->whereIn('mutation_id', ['delegate-cross-tenant', 'device-scope-escape'])->doesntExist(), 'Sprint 24 denied lifecycle attempt wrote journal state.');

$assert($s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-second-alpha'), ProtectedControlAdministratorMutationId::fromString('delegate-alpha-second'), ProtectedControlAdministratorOperation::delegate()) === 'applied', 'Sprint 24 second delegation failed.');
$assert($s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-delegate-alpha'), ProtectedControlAdministratorMutationId::fromString('revoke-alpha-delegate'), ProtectedControlAdministratorOperation::revoke()) === 'applied', 'Sprint 24 delegated administrator revocation failed.');
$assert(! $s24Policy->allows($s24DelegatedContext, AdministrationPermission::manage()), 'Sprint 24 revoked target retained tenant control permission.');
$assert($s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-delegate-alpha'), ProtectedControlAdministratorMutationId::fromString('revoke-alpha-absent'), ProtectedControlAdministratorOperation::revoke()) === 'no_change', 'Sprint 24 absent revocation did not return no_change.');

// Self-revocation is valid only because synthetic-second-alpha remains a tenant-scoped control principal.
$assert($s24Service->apply($s24AlphaActor, $s24VerifiedIdentity('synthetic-admin-alpha'), ProtectedControlAdministratorMutationId::fromString('self-revoke-alpha'), ProtectedControlAdministratorOperation::revoke()) === 'applied', 'Sprint 24 safe self-revocation failed.');
$s24SecondActor = new VerifiedOrganizationalContext(PlatformIdentityId::fromString('synthetic-second-alpha'), TenantId::fromString('tenant-alpha'), OrganizationId::fromString('organization-alpha'), OutletId::fromString('outlet-alpha'), DeviceId::fromString('device-alpha'));
try {
    $s24Service->apply($s24SecondActor, $s24VerifiedIdentity('synthetic-second-alpha'), ProtectedControlAdministratorMutationId::fromString('last-principal-alpha'), ProtectedControlAdministratorOperation::revoke());
    $assert(false, 'Sprint 24 final tenant control principal was revoked.');
} catch (ProtectedControlAdministratorLifecycleViolation $exception) {
    $assert($exception->errorCode === ProtectedControlAdministratorLifecycleViolation::LAST_CONTROL_PRINCIPAL, 'Sprint 24 last-principal denial returned wrong code.');
}
$assert($s24Connection->table('oneqay_tenant_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-second-alpha')->where('role_id', ProtectedControlAdministratorLifecycleRepository::CONTROL_ROLE)->exists(), 'Sprint 24 last-principal denial removed final assignment.');
$assert($s24Connection->table('oneqay_protected_control_admin_mutations')->where('tenant_id', 'tenant-alpha')->where('mutation_id', 'last-principal-alpha')->doesntExist(), 'Sprint 24 last-principal denial wrote journal state.');

// Same textual mutation ID remains independent across tenants.
$assert($s24Service->apply($s24BetaActor, $s24VerifiedIdentity('synthetic-delegate-beta'), ProtectedControlAdministratorMutationId::fromString('tenant-local-mutation'), ProtectedControlAdministratorOperation::delegate()) === 'applied', 'Sprint 24 beta tenant delegation failed.');
$assert($s24Service->apply($s24SecondActor, $s24VerifiedIdentity('synthetic-delegate-alpha'), ProtectedControlAdministratorMutationId::fromString('tenant-local-mutation'), ProtectedControlAdministratorOperation::delegate()) === 'applied', 'Sprint 24 same textual mutation ID was not tenant-local.');
$assert($s24Connection->table('oneqay_protected_control_admin_mutations')->where('mutation_id', 'tenant-local-mutation')->count() === 2, 'Sprint 24 mutation ID was treated as globally unique.');

// Sprint 22 generic policy administration must still reject protected control assignment/revocation.
$s24PolicyAdminRepo = new LaravelDurablePolicyAdministrationRepository($s24Connection, true, 'ci');
$s24PolicyAdminService = new DurablePolicyAdministrationService($s24Policy, $s24PolicyAdminRepo, $s24Transaction, $s24Clock);
try {
    $s24PolicyAdminService->apply(
        $s24SecondActor,
        DurablePolicyMutation::roleAssignment(
            PolicyMutationId::fromString('s24-generic-protected-assign'),
            $s24SecondActor,
            PolicyMutationOperation::ROLE_ASSIGN_TENANT,
            PlatformIdentityId::fromString('synthetic-delegate-alpha'),
            RoleIdentifier::fromString(ProtectedControlAdministratorLifecycleRepository::CONTROL_ROLE),
        ),
    );
    $assert(false, 'Sprint 24 weakened Sprint 22 generic protected-role assignment denial.');
} catch (DurablePolicyAdministrationViolation $exception) {
    $assert($exception->errorCode === DurablePolicyAdministrationViolation::PROTECTED_CONTROL_AUTHORITY, 'Sprint 24 generic protected-role denial returned wrong code.');
}
$assert($s24Connection->table('oneqay_policy_mutations')->where('tenant_id', 'tenant-alpha')->where('mutation_id', 's24-generic-protected-assign')->doesntExist(), 'Sprint 24 generic protected-role denial wrote Sprint 22 journal state.');

// A forced lifecycle journal failure must roll back assignment mutation.
$s24Connection->unprepared("CREATE TRIGGER s24_force_lifecycle_failure BEFORE INSERT ON oneqay_protected_control_admin_mutations WHEN NEW.mutation_id = 'forced-rollback' BEGIN SELECT RAISE(ABORT, 'forced'); END;");
try {
    $s24Service->apply($s24SecondActor, $s24VerifiedIdentity('synthetic-delegate-alpha'), ProtectedControlAdministratorMutationId::fromString('forced-rollback'), ProtectedControlAdministratorOperation::revoke());
    $assert(false, 'Sprint 24 forced lifecycle transaction failure unexpectedly succeeded.');
} catch (ProtectedControlAdministratorLifecycleViolation $exception) {
    $assert(in_array($exception->errorCode, [ProtectedControlAdministratorLifecycleViolation::STORAGE_FAILURE, ProtectedControlAdministratorLifecycleViolation::TRANSACTION_FAILURE], true), 'Sprint 24 forced transaction failure returned unsafe code.');
}
$s24Connection->unprepared('DROP TRIGGER s24_force_lifecycle_failure');
$assert($s24Connection->table('oneqay_tenant_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-delegate-alpha')->where('role_id', ProtectedControlAdministratorLifecycleRepository::CONTROL_ROLE)->exists(), 'Sprint 24 forced failure did not roll back assignment deletion.');
$assert($s24Connection->table('oneqay_protected_control_admin_mutations')->where('tenant_id', 'tenant-alpha')->where('mutation_id', 'forced-rollback')->doesntExist(), 'Sprint 24 forced failure left journal state.');

// Sprint 23 initial provisioning evidence and existing Sprint 22 journal evidence remain untouched.
$s24ProvisioningEvidenceAfter = $s24Connection->table('oneqay_initial_tenant_admin_provisionings')->orderBy('tenant_id')->get()->map(static fn ($row): array => (array) $row)->all();
$assert($s24ProvisioningEvidenceAfter === $s24ProvisioningEvidenceBefore, 'Sprint 24 modified Sprint 23 initial provisioning journal evidence.');
$assert($s24Connection->table('oneqay_policy_mutations')->count() === $s24PolicyEvidenceBefore, 'Sprint 24 modified Sprint 22 policy mutation journal evidence.');

$s24RepoSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelProtectedControlAdministratorLifecycleRepository.php');
$assert(! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $s24RepoSource), 'Sprint 24 repository introduced unrestricted ownership-rewriting upsert.');
$assert(str_contains($s24RepoSource, 'oneqay_tenant_role_assignments as a'), 'Sprint 24 actor authority is not rooted in tenant assignments.');
$assert(str_contains($s24RepoSource, 'tenantControlPrincipalCount'), 'Sprint 24 last-control-principal guard is missing.');
$assert(str_contains($s24RepoSource, "['local', 'test', 'ci']"), 'Sprint 24 runtime allowlist changed.');

$s24Manager->disconnect('s24_control_lifecycle');
$s24Manager->purge('s24_control_lifecycle');
$app['config']->set('database.connections.s24_control_lifecycle', null);
@unlink($s24DbPath);
$s24Remove($s24Parent);
$assert(! file_exists($s24Parent), 'Sprint 24 workspace cleanup failed.');