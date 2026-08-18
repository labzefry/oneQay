<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\DurablePolicyAdministrationService;
use App\Application\Authorization\DurablePolicyAdministrationViolation;
use App\Application\Authorization\DurablePolicyMutation;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\InitialTenantAdministratorProvisioningService;
use App\Application\Authorization\InitialTenantAdministratorProvisioningViolation;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Authorization\PolicyMutationId;
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
use App\Infrastructure\Authorization\PreauthorizedInitialTenantAdministratorProvisioningAuthority;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;

// Author by Lab | zefry
if (! isset($app, $assert) || ! is_callable($assert)) {
    throw new RuntimeException('Sprint 23 initial tenant administrator provisioning regression requires the M7.1 application harness.');
}
$assert(extension_loaded('pdo_sqlite'), 'Sprint 23 initial tenant administrator provisioning regression requires pdo_sqlite.');

$s23Remove = static function (string $path): void {
    if (is_file($path) || is_link($path)) { @unlink($path); return; }
    if (! is_dir($path)) { return; }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($path);
};

$s23Parent = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s23-initial-admin-'.getmypid();
$s23Remove($s23Parent);
$assert(@mkdir($s23Parent, 0700, false), 'Sprint 23 workspace create failed.');
$s23DbPath = $s23Parent.DIRECTORY_SEPARATOR.'provisioning.sqlite';
$assert(touch($s23DbPath), 'Sprint 23 SQLite create failed.');
$app['config']->set('database.connections.s23_initial_admin', [
    'driver' => 'sqlite', 'url' => null, 'database' => $s23DbPath, 'prefix' => '',
    'foreign_key_constraints' => true, 'busy_timeout' => null, 'journal_mode' => null, 'synchronous' => null,
]);
/** @var \Illuminate\Database\DatabaseManager $s23Manager */
$s23Manager = $app->make('db');
$s23Manager->purge('s23_initial_admin');
$s23Manager->setDefaultConnection('s23_initial_admin');
$s23Connection = $s23Manager->connection('s23_initial_admin');
$s23Connection->getPdo();

$s23TenantAlpha = TenantId::fromString('tenant-alpha');
$s23IdentityAlphaId = PlatformIdentityId::fromString('synthetic-admin-alpha');
$s23ProvisioningOne = InitialTenantAdministratorProvisioningId::fromString('initial-one');
foreach ([[false, 'ci', InitialTenantAdministratorProvisioningViolation::PERSISTENCE_DISABLED], [true, 'preview', InitialTenantAdministratorProvisioningViolation::RUNTIME_DENIED], [true, 'production', InitialTenantAdministratorProvisioningViolation::RUNTIME_DENIED]] as [$enabled, $runtime, $code]) {
    try {
        (new LaravelInitialTenantAdministratorProvisioningRepository($s23Connection, $enabled, $runtime))
            ->replayOutcome($s23TenantAlpha, $s23IdentityAlphaId, $s23ProvisioningOne);
        $assert(false, 'Sprint 23 unauthorized runtime reached storage.');
    } catch (InitialTenantAdministratorProvisioningViolation $exception) {
        $assert($exception->errorCode === $code, 'Sprint 23 runtime guard returned unexpected code.');
    }
}
$assert(! $s23Connection->getSchemaBuilder()->hasTable('oneqay_initial_tenant_admin_provisionings'), 'Sprint 23 runtime denial occurred after schema mutation.');

$s23Migrations = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
    '0000_00_00_000007_create_identity_password_credentials.php',
];
$s23Actual = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($s23Actual);
$assert($s23Actual === $s23Migrations, 'Sprint 23 preservation requires exact seven-migration set through Sprint 26.');
foreach ($s23Migrations as $migration) { (require __DIR__.'/../database/migrations/'.$migration)->up(); }
$assert($s23Connection->getSchemaBuilder()->hasTable('oneqay_initial_tenant_admin_provisionings'), 'Sprint 23 provisioning journal missing.');
$assert($s23Connection->getSchemaBuilder()->hasTable('oneqay_protected_control_admin_mutations'), 'Sprint 24 lifecycle journal missing during Sprint 23 preservation.');
$assert($s23Connection->getSchemaBuilder()->hasTable('oneqay_identity_password_credentials'), 'Sprint 26 credential table missing during Sprint 23 preservation.');

$journalColumns = $s23Connection->getSchemaBuilder()->getColumnListing('oneqay_initial_tenant_admin_provisionings');
sort($journalColumns);
$expectedJournalColumns = ['identity_id', 'occurred_at_unix', 'outcome', 'payload_fingerprint', 'permission_id', 'provisioning_id', 'role_id', 'tenant_id'];
sort($expectedJournalColumns);
$assert($journalColumns === $expectedJournalColumns, 'Sprint 23 provisioning journal contains unauthorized columns.');

foreach (['tenant-alpha', 'tenant-beta', 'tenant-gamma', 'tenant-delta', 'tenant-epsilon', 'tenant-zeta'] as $tenant) {
    $s23Connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
$s23Connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-conflict-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-admin-beta'],
    ['tenant_id' => 'tenant-gamma', 'id' => 'synthetic-admin-gamma'],
    ['tenant_id' => 'tenant-delta', 'id' => 'synthetic-admin-delta'],
    ['tenant_id' => 'tenant-zeta', 'id' => 'synthetic-admin-zeta'],
]);

$s23Authority = new PreauthorizedInitialTenantAdministratorProvisioningAuthority([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-admin-alpha', 'provisioning_id' => 'initial-one'],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-conflict-alpha', 'provisioning_id' => 'initial-one'],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-admin-alpha', 'provisioning_id' => 'second-init'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-admin-beta', 'provisioning_id' => 'initial-one'],
    ['tenant_id' => 'tenant-gamma', 'identity_id' => 'synthetic-admin-gamma', 'provisioning_id' => 'initial-gamma'],
    ['tenant_id' => 'tenant-delta', 'identity_id' => 'synthetic-admin-delta', 'provisioning_id' => 'initial-delta'],
    ['tenant_id' => 'tenant-epsilon', 'identity_id' => 'synthetic-admin-beta', 'provisioning_id' => 'initial-epsilon'],
    ['tenant_id' => 'tenant-zeta', 'identity_id' => 'synthetic-admin-zeta', 'provisioning_id' => 'initial-zeta'],
]);
$s23Repo = new LaravelInitialTenantAdministratorProvisioningRepository($s23Connection, true, 'ci');
$s23Transaction = new LaravelPersistenceTransaction($s23Connection, true, 'ci');
$s23Clock = new class implements PolicyAdministrationClock { public function nowUnix(): int { return 1787013000; } };
$s23Service = new InitialTenantAdministratorProvisioningService($s23Authority, $s23Repo, $s23Transaction, $s23Clock);
$s23Identity = static fn (string $id): VerifiedPlatformIdentity => new class($id) implements VerifiedPlatformIdentity {
    public function __construct(private string $id) {}
    public function identityId(): string { return $this->id; }
};

$alphaActor = $s23Identity('synthetic-admin-alpha');
$assert($s23Service->provision($alphaActor, TenantId::fromString('tenant-alpha'), InitialTenantAdministratorProvisioningId::fromString('initial-one')) === 'applied', 'Sprint 23 initial administrator provisioning was not applied.');
$assert($s23Service->provision($alphaActor, TenantId::fromString('tenant-alpha'), InitialTenantAdministratorProvisioningId::fromString('initial-one')) === 'applied', 'Sprint 23 exact replay did not return prior outcome.');
$assert($s23Connection->table('oneqay_initial_tenant_admin_provisionings')->where('tenant_id', 'tenant-alpha')->count() === 1, 'Sprint 23 replay duplicated journal state.');
$assert($s23Connection->table('oneqay_roles')->where('tenant_id', 'tenant-alpha')->where('id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)->count() === 1, 'Sprint 23 exact control role missing.');
$assert($s23Connection->table('oneqay_role_permissions')->where('tenant_id', 'tenant-alpha')->where('role_id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)->where('permission_id', AdministrationPermission::MANAGE)->count() === 1, 'Sprint 23 exact control permission missing.');
$assert($s23Connection->table('oneqay_role_permissions')->where('tenant_id', 'tenant-alpha')->where('role_id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)->count() === 1, 'Sprint 23 created extra control-role permissions.');
$assert($s23Connection->table('oneqay_tenant_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-admin-alpha')->where('role_id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)->count() === 1, 'Sprint 23 exact tenant control assignment missing.');
foreach (['oneqay_organization_role_assignments', 'oneqay_outlet_role_assignments', 'oneqay_device_role_assignments'] as $table) {
    $assert($s23Connection->table($table)->where('tenant_id', 'tenant-alpha')->where('role_id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)->doesntExist(), 'Sprint 23 created a non-tenant control assignment.');
}

try {
    $s23Service->provision($s23Identity('synthetic-conflict-alpha'), TenantId::fromString('tenant-alpha'), InitialTenantAdministratorProvisioningId::fromString('initial-one'));
    $assert(false, 'Sprint 23 conflicting replay was accepted.');
} catch (InitialTenantAdministratorProvisioningViolation $exception) {
    $assert($exception->errorCode === InitialTenantAdministratorProvisioningViolation::PROVISIONING_CONFLICT, 'Sprint 23 conflicting replay returned wrong code.');
}
try {
    $s23Service->provision($alphaActor, TenantId::fromString('tenant-alpha'), InitialTenantAdministratorProvisioningId::fromString('second-init'));
    $assert(false, 'Sprint 23 second initialization was accepted.');
} catch (InitialTenantAdministratorProvisioningViolation $exception) {
    $assert($exception->errorCode === InitialTenantAdministratorProvisioningViolation::ALREADY_INITIALIZED, 'Sprint 23 second initialization returned wrong code.');
}

$assert($s23Service->provision($s23Identity('synthetic-admin-beta'), TenantId::fromString('tenant-beta'), InitialTenantAdministratorProvisioningId::fromString('initial-one')) === 'applied', 'Sprint 23 same textual provisioning ID was not tenant-local.');
$assert($s23Connection->table('oneqay_initial_tenant_admin_provisionings')->where('provisioning_id', 'initial-one')->count() === 2, 'Sprint 23 provisioning ID was treated as globally unique.');

$emptyAuthorityService = new InitialTenantAdministratorProvisioningService(
    new PreauthorizedInitialTenantAdministratorProvisioningAuthority([]),
    $s23Repo,
    $s23Transaction,
    $s23Clock,
);
try {
    $emptyAuthorityService->provision($s23Identity('synthetic-admin-gamma'), TenantId::fromString('tenant-gamma'), InitialTenantAdministratorProvisioningId::fromString('initial-gamma'));
    $assert(false, 'Sprint 23 missing authority was accepted.');
} catch (InitialTenantAdministratorProvisioningViolation $exception) {
    $assert($exception->errorCode === InitialTenantAdministratorProvisioningViolation::AUTHORIZATION_DENIED, 'Sprint 23 missing authority returned wrong code.');
}

try {
    $s23Service->provision($s23Identity('synthetic-admin-beta'), TenantId::fromString('tenant-epsilon'), InitialTenantAdministratorProvisioningId::fromString('initial-epsilon'));
    $assert(false, 'Sprint 23 foreign-tenant identity was accepted.');
} catch (InitialTenantAdministratorProvisioningViolation $exception) {
    $assert($exception->errorCode === InitialTenantAdministratorProvisioningViolation::TENANT_RELATIONSHIP_DENIED, 'Sprint 23 foreign-tenant identity returned wrong code.');
}

$s23Connection->table('oneqay_roles')->insert(['tenant_id' => 'tenant-gamma', 'id' => 'preexisting-control']);
$s23Connection->table('oneqay_role_permissions')->insert(['tenant_id' => 'tenant-gamma', 'role_id' => 'preexisting-control', 'permission_id' => AdministrationPermission::MANAGE]);
try {
    $s23Service->provision($s23Identity('synthetic-admin-gamma'), TenantId::fromString('tenant-gamma'), InitialTenantAdministratorProvisioningId::fromString('initial-gamma'));
    $assert(false, 'Sprint 23 reused bootstrap after pre-existing control authority.');
} catch (InitialTenantAdministratorProvisioningViolation $exception) {
    $assert($exception->errorCode === InitialTenantAdministratorProvisioningViolation::ALREADY_INITIALIZED, 'Sprint 23 pre-existing control authority returned wrong code.');
}

$s23Connection->table('oneqay_roles')->insert(['tenant_id' => 'tenant-delta', 'id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE]);
$s23Connection->table('oneqay_role_permissions')->insert(['tenant_id' => 'tenant-delta', 'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE, 'permission_id' => 'synthetic.other']);
try {
    $s23Service->provision($s23Identity('synthetic-admin-delta'), TenantId::fromString('tenant-delta'), InitialTenantAdministratorProvisioningId::fromString('initial-delta'));
    $assert(false, 'Sprint 23 rewrote incompatible control-role state.');
} catch (InitialTenantAdministratorProvisioningViolation $exception) {
    $assert($exception->errorCode === InitialTenantAdministratorProvisioningViolation::ROLE_STATE_CONFLICT, 'Sprint 23 incompatible role state returned wrong code.');
}

foreach (['tenant-gamma', 'tenant-delta', 'tenant-epsilon'] as $tenant) {
    $assert($s23Connection->table('oneqay_initial_tenant_admin_provisionings')->where('tenant_id', $tenant)->doesntExist(), 'Sprint 23 denied attempt wrote journal state.');
}

$s23Connection->unprepared("CREATE TRIGGER s23_force_journal_failure BEFORE INSERT ON oneqay_initial_tenant_admin_provisionings WHEN NEW.tenant_id = 'tenant-zeta' BEGIN SELECT RAISE(ABORT, 'forced'); END;");
try {
    $s23Service->provision($s23Identity('synthetic-admin-zeta'), TenantId::fromString('tenant-zeta'), InitialTenantAdministratorProvisioningId::fromString('initial-zeta'));
    $assert(false, 'Sprint 23 forced transaction failure unexpectedly succeeded.');
} catch (InitialTenantAdministratorProvisioningViolation $exception) {
    $assert(in_array($exception->errorCode, [InitialTenantAdministratorProvisioningViolation::STORAGE_FAILURE, InitialTenantAdministratorProvisioningViolation::TRANSACTION_FAILURE], true), 'Sprint 23 transaction failure returned an unsafe code.');
}
$s23Connection->unprepared('DROP TRIGGER s23_force_journal_failure');
$assert($s23Connection->table('oneqay_roles')->where('tenant_id', 'tenant-zeta')->where('id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)->doesntExist(), 'Sprint 23 transaction failure left role state.');
$assert($s23Connection->table('oneqay_role_permissions')->where('tenant_id', 'tenant-zeta')->where('role_id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)->doesntExist(), 'Sprint 23 transaction failure left permission state.');
$assert($s23Connection->table('oneqay_tenant_role_assignments')->where('tenant_id', 'tenant-zeta')->where('role_id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)->doesntExist(), 'Sprint 23 transaction failure left assignment state.');
$assert($s23Connection->table('oneqay_initial_tenant_admin_provisionings')->where('tenant_id', 'tenant-zeta')->doesntExist(), 'Sprint 23 transaction failure left journal state.');

$s23Read = new LaravelDurableRolePermissionRepository($s23Connection, true, 'ci');
$s23Policy = new DurableScopedAuthorizationPolicy($s23Read);
$s23PolicyRepo = new LaravelDurablePolicyAdministrationRepository($s23Connection, true, 'ci');
$s23PolicyService = new DurablePolicyAdministrationService($s23Policy, $s23PolicyRepo, $s23Transaction, $s23Clock);
$s23AlphaContext = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('synthetic-admin-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('synthetic-organization'),
    OutletId::fromString('synthetic-outlet'),
    DeviceId::fromString('synthetic-device'),
);
$assert($s23Policy->allows($s23AlphaContext, AdministrationPermission::manage()), 'Sprint 23 initial administrator is not recognized by the Sprint 21 evaluator.');
try {
    $s23PolicyService->apply(
        $s23AlphaContext,
        DurablePolicyMutation::permissionGrant(
            PolicyMutationId::fromString('s23-protected-role-rewrite'),
            $s23AlphaContext,
            RoleIdentifier::fromString(InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE),
            PermissionIdentifier::fromString('synthetic.extra'),
        ),
    );
    $assert(false, 'Sprint 23 newly protected control role was mutable through Sprint 22 administration.');
} catch (DurablePolicyAdministrationViolation $exception) {
    $assert($exception->errorCode === DurablePolicyAdministrationViolation::PROTECTED_CONTROL_AUTHORITY, 'Sprint 23 protected role preservation returned wrong code.');
}
$assert($s23Connection->table('oneqay_policy_mutations')->where('tenant_id', 'tenant-alpha')->where('mutation_id', 's23-protected-role-rewrite')->doesntExist(), 'Sprint 23 protected-role denial wrote Sprint 22 journal state.');

$assert($s23Connection->table('oneqay_initial_tenant_admin_provisionings')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-admin-alpha')->where('role_id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)->where('permission_id', AdministrationPermission::MANAGE)->where('outcome', 'applied')->exists(), 'Sprint 23 journal evidence is incomplete.');

$s23Manager->purge('s23_initial_admin');
$s23Remove($s23Parent);