<?php

declare(strict_types=1);

use App\Application\Authorization\PolicyAssignmentScope;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Application\Tenancy\TenantIsolationGuard;
use App\Application\Tenancy\TenantIsolationViolation;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Domain\Tenancy\TenantOwnedResourceReference;
use App\Infrastructure\Tenancy\ServerVerifiedTenantContext;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assertM72 = static function (bool $condition, string $case): void {
    if (! $condition) { throw new RuntimeException('M7.2 regression failed: '.$case); }
};

$alphaTenant = TenantId::fromString('tenant-alpha');
$betaTenant = TenantId::fromString('tenant-beta');
$alphaContext = new ServerVerifiedTenantContext($alphaTenant);
$betaContext = new ServerVerifiedTenantContext($betaTenant);
$guard = new TenantIsolationGuard(new RequireVerifiedTenantContext());
$guard->assertAccessible($alphaContext, new TenantOwnedResourceReference('synthetic-resource', $alphaTenant));
try {
    $guard->assertAccessible($alphaContext, new TenantOwnedResourceReference('synthetic-resource', $betaTenant));
    $assertM72(false, 'cross-tenant resource access accepted');
} catch (TenantIsolationViolation) {
    // Expected.
}

$actor = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('synthetic-admin-alpha'), $alphaTenant,
    OrganizationId::fromString('organization-alpha'), OutletId::fromString('outlet-alpha'), DeviceId::fromString('device-alpha'),
);
$tenantScope = PolicyAssignmentScope::fromVerifiedContext($actor, 'tenant');
$deviceScope = PolicyAssignmentScope::fromVerifiedContext($actor, 'device');
$assertM72($tenantScope->tenantId()->value() === 'tenant-alpha', 'tenant scope did not derive verified tenant');
$assertM72($deviceScope->deviceId()?->value() === 'device-alpha' && $deviceScope->matchesActor($actor), 'device scope did not derive verified context');

$migrationDir = __DIR__.'/../database/migrations';
$migrations = array_values(array_filter(scandir($migrationDir) ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($migrations);
$expected = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
    '0000_00_00_000007_create_identity_password_credentials.php',
];
$assertM72($migrations === $expected, 'exact seven-migration set through Sprint 26 changed');
$mutationMigration = (string) file_get_contents($migrationDir.'/'.$expected[3]);
foreach (['oneqay_policy_mutations', "primary(['tenant_id', 'mutation_id']", 'fk_policy_mutation_actor', 'Forward-only generated migration; rollback is not authorized.'] as $marker) {
    $assertM72(str_contains($mutationMigration, $marker), 'Sprint 22 migration boundary missing: '.$marker);
}
$provisioningMigration = (string) file_get_contents($migrationDir.'/'.$expected[4]);
foreach (['oneqay_initial_tenant_admin_provisionings', "primary('tenant_id'", 'fk_initial_tenant_admin_identity', 'fk_initial_tenant_admin_permission', 'Forward-only generated migration; rollback is not authorized.'] as $marker) {
    $assertM72(str_contains($provisioningMigration, $marker), 'Sprint 23 migration boundary missing: '.$marker);
}
$lifecycleMigration = (string) file_get_contents($migrationDir.'/'.$expected[5]);
foreach (['oneqay_protected_control_admin_mutations', "primary(['tenant_id', 'mutation_id']", 'fk_protected_control_admin_actor', 'fk_protected_control_admin_target', 'fk_protected_control_admin_permission', 'Forward-only generated migration; rollback is not authorized.'] as $marker) {
    $assertM72(str_contains($lifecycleMigration, $marker), 'Sprint 24 migration boundary missing: '.$marker);
}
$credentialMigration = (string) file_get_contents($migrationDir.'/'.$expected[6]);
foreach (['oneqay_identity_password_credentials', "string('identity_id', 96)", "primary(['tenant_id', 'identity_id']", 'fk_identity_password_credential_identity', "references(['tenant_id', 'id'])", 'Forward-only generated migration; rollback is not authorized.'] as $marker) {
    $assertM72(str_contains($credentialMigration, $marker), 'Sprint 26 credential migration boundary missing: '.$marker);
}

$appAuthorizationDir = __DIR__.'/../app/Application/Authorization';
foreach (glob($appAuthorizationDir.'/*.php') ?: [] as $file) {
    $source = (string) file_get_contents($file);
    $assertM72(! preg_match('/Illuminate\\\\|Schema::|DB::|new PDO|mysqli_/', $source), 'Application authorization gained framework/DB mechanics: '.basename($file));
}

$readRepo = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php');
$writeRepo = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelDurablePolicyAdministrationRepository.php');
$lifecycleRepo = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelProtectedControlAdministratorLifecycleRepository.php');
$assertM72(! preg_match('/\b(insert|insertOrIgnore|update|updateOrInsert|upsert|delete|truncate)\s*\(/', $readRepo), 'Sprint 21 read repository is no longer read-only');
$assertM72(substr_count($readRepo, "->where('tenant_id',") >= 5, 'Sprint 21 read repository lost tenant scoping');
$assertM72(substr_count($writeRepo, "->where('tenant_id',") >= 8, 'Sprint 22 mutation repository lost tenant scoping');
$assertM72(! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $writeRepo), 'Sprint 22 mutation repository introduced unrestricted upsert');
$assertM72(str_contains($writeRepo, "in_array(\$runtime, ['local', 'test', 'ci'], true)"), 'Sprint 22 runtime allowlist changed');
$assertM72(str_contains($writeRepo, 'oneqay_outlet_access_grants') && str_contains($writeRepo, 'oneqay_device_access_grants'), 'Sprint 22 assignment validation lost durable access boundary');
$assertM72(str_contains($writeRepo, "->where('permission_id', AdministrationPermission::MANAGE)"), 'Sprint 22 protected control role check missing');
$assertM72(str_contains($lifecycleRepo, 'oneqay_tenant_role_assignments as a'), 'Sprint 24 actor authority lost tenant-assignment root');
$assertM72(str_contains($lifecycleRepo, 'tenantControlPrincipalCount'), 'Sprint 24 last-principal safety check missing');
$assertM72(! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $lifecycleRepo), 'Sprint 24 lifecycle repository introduced unrestricted upsert');

echo "M7.2 tenant isolation regression passed with Sprint 26 credential-schema preservation.\n";
