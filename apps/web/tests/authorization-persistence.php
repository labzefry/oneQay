<?php

declare(strict_types=1);

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
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

$assert(extension_loaded('pdo_sqlite'), 'Sprint 21 authorization persistence regression requires pdo_sqlite.');
$s21Remove = static function (string $path): void {
    if (is_file($path) || is_link($path)) { @unlink($path); return; }
    if (! is_dir($path)) { return; }
    foreach (new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS) as $item) {
        if ($item->isDir() && ! $item->isLink()) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($item->getPathname(), FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $child) {
                $child->isDir() ? @rmdir($child->getPathname()) : @unlink($child->getPathname());
            }
            @rmdir($item->getPathname());
        } else { @unlink($item->getPathname()); }
    }
    @rmdir($path);
};

$s21Parent = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s21-authz-'.getmypid();
$s21Remove($s21Parent);
$assert(@mkdir($s21Parent, 0700, false), 'Sprint 21 auth workspace create failed.');
$s21DbPath = $s21Parent.DIRECTORY_SEPARATOR.'authorization.sqlite';
$assert(touch($s21DbPath), 'Sprint 21 auth SQLite create failed.');

$app['config']->set('database.connections.s21_auth', [
    'driver' => 'sqlite', 'url' => null, 'database' => $s21DbPath, 'prefix' => '',
    'foreign_key_constraints' => true, 'busy_timeout' => null, 'journal_mode' => null, 'synchronous' => null,
]);
/** @var \Illuminate\Database\DatabaseManager $s21Manager */
$s21Manager = $app->make('db');
$s21Manager->purge('s21_auth');
$s21Manager->setDefaultConnection('s21_auth');
$s21Connection = $s21Manager->connection('s21_auth');
$s21Connection->getPdo();

$s21Migrations = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
    '0000_00_00_000007_create_identity_password_credentials.php',
];
$s21Actual = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($s21Actual);
$assert($s21Actual === $s21Migrations, 'Sprint 21 preservation requires exact seven-migration set through Sprint 26.');
foreach ($s21Migrations as $migration) { (require __DIR__.'/../database/migrations/'.$migration)->up(); }

foreach (['oneqay_roles', 'oneqay_role_permissions', 'oneqay_tenant_role_assignments', 'oneqay_organization_role_assignments', 'oneqay_outlet_role_assignments', 'oneqay_device_role_assignments', 'oneqay_policy_mutations', 'oneqay_initial_tenant_admin_provisionings', 'oneqay_protected_control_admin_mutations', 'oneqay_identity_password_credentials'] as $table) {
    $assert($s21Connection->getSchemaBuilder()->hasTable($table), 'Sprint 21-Sprint 26 preserved table missing: '.$table);
}

$s21Connection->table('oneqay_tenants')->insert([['id' => 'tenant-alpha'], ['id' => 'tenant-beta']]);
$s21Connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-reader'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-reader'],
]);
$s21Connection->table('oneqay_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-shared'],
    ['tenant_id' => 'tenant-beta', 'id' => 'organization-shared'],
]);
$s21Connection->table('oneqay_identity_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-reader', 'organization_id' => 'organization-shared'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-reader', 'organization_id' => 'organization-shared'],
]);
$s21Connection->table('oneqay_outlets')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'outlet-shared', 'organization_id' => 'organization-shared'],
    ['tenant_id' => 'tenant-beta', 'id' => 'outlet-shared', 'organization_id' => 'organization-shared'],
]);
$s21Connection->table('oneqay_devices')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'device-shared', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared'],
    ['tenant_id' => 'tenant-beta', 'id' => 'device-shared', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared'],
]);
$s21Connection->table('oneqay_outlet_access_grants')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-reader', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-reader', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared'],
]);
$s21Connection->table('oneqay_device_access_grants')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-reader', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared', 'device_id' => 'device-shared'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-reader', 'organization_id' => 'organization-shared', 'outlet_id' => 'outlet-shared', 'device_id' => 'device-shared'],
]);
$s21Connection->table('oneqay_roles')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-reader-role'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-reader-role'],
]);
$s21Connection->table('oneqay_role_permissions')->insert([
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'synthetic-reader-role', 'permission_id' => 'synthetic.resource.read'],
    ['tenant_id' => 'tenant-beta', 'role_id' => 'synthetic-reader-role', 'permission_id' => 'synthetic.resource.beta-only'],
]);
$s21Connection->table('oneqay_tenant_role_assignments')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'synthetic-reader', 'role_id' => 'synthetic-reader-role'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-reader', 'role_id' => 'synthetic-reader-role'],
]);

$s21Repo = new LaravelDurableRolePermissionRepository($s21Connection, true, 'ci');
$s21Policy = new DurableScopedAuthorizationPolicy($s21Repo);
$s21Alpha = new VerifiedOrganizationalContext(PlatformIdentityId::fromString('synthetic-reader'), TenantId::fromString('tenant-alpha'), OrganizationId::fromString('organization-shared'), OutletId::fromString('outlet-shared'), DeviceId::fromString('device-shared'));
$s21Beta = new VerifiedOrganizationalContext(PlatformIdentityId::fromString('synthetic-reader'), TenantId::fromString('tenant-beta'), OrganizationId::fromString('organization-shared'), OutletId::fromString('outlet-shared'), DeviceId::fromString('device-shared'));
$assert($s21Policy->allows($s21Alpha, PermissionIdentifier::fromString('synthetic.resource.read')), 'Sprint 21 alpha exact permission was not allowed.');
$assert(! $s21Policy->allows($s21Alpha, PermissionIdentifier::fromString('synthetic.resource.beta-only')), 'Sprint 21 alpha crossed into beta permission facts.');
$assert($s21Policy->allows($s21Beta, PermissionIdentifier::fromString('synthetic.resource.beta-only')), 'Sprint 21 beta exact permission was not allowed.');
$assert(! $s21Policy->allows(null, PermissionIdentifier::fromString('synthetic.resource.read')), 'Sprint 21 missing verified context did not fail closed.');

$s21RepoSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php');
$assert(! preg_match('/\b(insert|insertOrIgnore|update|updateOrInsert|upsert|delete|truncate)\s*\(/', $s21RepoSource), 'Sprint 21 read-only repository gained mutation behavior.');
$assert(substr_count($s21RepoSource, "->where('tenant_id',") >= 5, 'Sprint 21 repository lost explicit tenant predicates.');

$s21Manager->disconnect('s21_auth');
$s21Manager->purge('s21_auth');
$app['config']->set('database.connections.s21_auth', null);
@unlink($s21DbPath);
$s21Remove($s21Parent);
$assert(! file_exists($s21Parent), 'Sprint 21 authorization workspace cleanup failed.');
