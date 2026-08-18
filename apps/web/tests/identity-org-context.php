<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\PolicyAssignmentScope;
use App\Application\Authorization\RoleIdentifier;
use App\Application\Identity\IdentityContextViolation;
use App\Application\Identity\RequireVerifiedPlatformIdentity;
use App\Application\Identity\VerifiedPlatformIdentity;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Tenancy\MissingTenantContext;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Domain\Identity\PlatformIdentityId;
use App\Infrastructure\Identity\ServerVerifiedPlatformIdentity;
use App\Infrastructure\Organization\RequestOrganizationalContextStore;
use App\Infrastructure\Organization\SyntheticOrganizationalRelationshipVerifier;
use App\Infrastructure\Tenancy\SyntheticTenantMembershipVerifier;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assertM73 = static function (bool $condition, string $case): void {
    if (! $condition) { throw new RuntimeException('M7.3 regression failed: '.$case); }
};

$memberships = new SyntheticTenantMembershipVerifier([
    'synthetic-principal-a' => ['tenant-alpha'],
    'synthetic-principal-b' => ['tenant-beta'],
    'synthetic-principal-c' => [],
]);
$relationships = new SyntheticOrganizationalRelationshipVerifier([
    'synthetic-principal-a' => [
        ['tenant' => 'tenant-alpha', 'organization' => 'organization-alpha'],
        ['tenant' => 'tenant-alpha', 'organization' => 'organization-alpha', 'outlet' => 'outlet-alpha'],
        ['tenant' => 'tenant-alpha', 'organization' => 'organization-alpha', 'outlet' => 'outlet-alpha', 'device' => 'device-alpha'],
        ['tenant' => 'tenant-alpha', 'organization' => 'organization-secondary', 'outlet' => 'outlet-secondary', 'device' => 'device-secondary'],
    ],
    'synthetic-principal-b' => [
        ['tenant' => 'tenant-beta', 'organization' => 'organization-collision', 'outlet' => 'outlet-collision', 'device' => 'device-collision'],
        ['tenant' => 'tenant-beta', 'organization' => 'organization-beta', 'outlet' => 'outlet-beta', 'device' => 'device-beta'],
    ],
    'synthetic-principal-c' => [['tenant' => 'tenant-alpha', 'organization' => 'organization-alpha']],
]);

$alphaTenant = $memberships->verify('synthetic-principal-a', 'tenant-alpha');
$betaTenant = $memberships->verify('synthetic-principal-b', 'tenant-beta');
$assertM73($alphaTenant !== null && $betaTenant !== null, 'tenant membership positive controls');

$identityA = new ServerVerifiedPlatformIdentity(PlatformIdentityId::fromString('synthetic-principal-a'));
$identityB = new ServerVerifiedPlatformIdentity(PlatformIdentityId::fromString('synthetic-principal-b'));
$identityC = new ServerVerifiedPlatformIdentity(PlatformIdentityId::fromString('synthetic-principal-c'));
$store = new RequestOrganizationalContextStore();
$enter = new EnterOrganizationalContext(new RequireVerifiedPlatformIdentity(), new RequireVerifiedTenantContext(), $memberships, $relationships, $store);

$assertM73(PlatformIdentityId::fromString('  SYNTHETIC-PRINCIPAL-A  ')->value() === 'synthetic-principal-a', 'identity canonicalization');
try { PlatformIdentityId::fromString('synthetic.principal.a'); $assertM73(false, 'malformed identity must fail'); } catch (InvalidArgumentException) {}
try { $enter->enter(null, $alphaTenant, 'organization-alpha'); $assertM73(false, 'missing identity denied'); } catch (IdentityContextViolation) { $assertM73($store->current() === null, 'missing identity leaves no context'); }

$malformedIdentity = new class implements VerifiedPlatformIdentity { public function identityId(): string { return 'synthetic.principal.a'; } };
try { $enter->enter($malformedIdentity, $alphaTenant, 'organization-alpha'); $assertM73(false, 'malformed verified identity denied'); } catch (IdentityContextViolation) { $assertM73($store->current() === null, 'malformed identity leaves no context'); }
try { $enter->enter($identityA, null, 'organization-alpha'); $assertM73(false, 'missing tenant denied'); } catch (MissingTenantContext) { $assertM73($store->current() === null, 'missing tenant clears context'); }

$org = $enter->enter($identityA, $alphaTenant, 'organization-alpha');
$assertM73($org->tenantId()->value() === 'tenant-alpha' && $org->organizationId()->value() === 'organization-alpha', 'organization positive control');
$assertM73($org->outletId() === null && $org->deviceId() === null, 'organization has no implicit descendants');
$outlet = $enter->enter($identityA, $alphaTenant, 'organization-alpha', 'outlet-alpha');
$device = $enter->enter($identityA, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-alpha');
$assertM73($outlet->outletId()?->value() === 'outlet-alpha' && $device->deviceId()?->value() === 'device-alpha', 'outlet/device positive control');

foreach ([
    [$identityC, $alphaTenant, 'organization-alpha', null, null, 'identity without membership'],
    [$identityB, $alphaTenant, 'organization-alpha', null, null, 'foreign identity'],
    [$identityA, $alphaTenant, 'organization-collision', 'outlet-collision', 'device-collision', 'foreign organization'],
    [$identityA, $alphaTenant, 'organization-alpha', 'outlet-secondary', 'device-secondary', 'foreign outlet'],
    [$identityA, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-secondary', 'foreign device'],
    [$identityA, $alphaTenant, 'organization-alpha', 'outlet-not-granted', null, 'ungranted outlet'],
    [$identityA, $alphaTenant, 'organization-not-granted', null, null, 'ungranted organization'],
    [$identityA, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-not-granted', 'ungranted device'],
    [$identityA, $alphaTenant, 'organization-alpha', null, 'device-alpha', 'device without outlet'],
] as [$identity, $tenant, $organization, $outletId, $deviceId, $case]) {
    try {
        $enter->enter($identity, $tenant, $organization, $outletId, $deviceId);
        $assertM73(false, $case.' denied');
    } catch (OrganizationalAccessViolation $exception) {
        $assertM73($exception->getMessage() === 'Organizational context denied.', $case.' generic denial');
        $assertM73(! str_contains($exception->getMessage(), 'tenant-beta'), $case.' no foreign leak');
    }
}

$betaCollision = $enter->enter($identityB, $betaTenant, 'organization-collision', 'outlet-collision', 'device-collision');
$assertM73($betaCollision->tenantId()->value() === 'tenant-beta', 'same textual IDs remain tenant-bound');
$assertM73($memberships->verify('synthetic-principal-a', 'tenant-beta') === null, 'raw tenant hint cannot create membership');

$enter->clear();
$assertM73($store->current() === null, 'no default organizational context');
$enter->enter($identityA, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-alpha');
$enter->clear();
$assertM73($store->current() === null, 'request context clears');
$enter->enter($identityA, $alphaTenant, 'organization-alpha');
try { $enter->enter($identityA, $alphaTenant, 'organization-not-granted'); $assertM73(false, 'failed subsequent request denied'); } catch (OrganizationalAccessViolation) { $assertM73($store->current() === null, 'failed request clears previous context'); }

foreach ([__DIR__.'/../app/Domain', __DIR__.'/../app/Application'] as $directory) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') { continue; }
        $content = (string) file_get_contents($file->getPathname());
        foreach (['Illuminate\\', 'Laravel\\', 'Inertia\\', 'Vue'] as $needle) {
            $assertM73(! str_contains($content, $needle), 'framework-independent boundary: '.$file->getPathname().' contains '.$needle);
        }
    }
}

$migrationDir = __DIR__.'/../database/migrations';
$expectedNames = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
];
$actual = array_values(array_filter(scandir($migrationDir) ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($actual);
$assertM73($actual === $expectedNames, 'migration set must remain exact Sprint 19/20/21/22/23');

$accessSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Access/LaravelDurableOrganizationalAccessRepository.php');
$tenantVerifier = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Tenancy/LaravelTenantMembershipVerifier.php');
$relationshipVerifier = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Organization/LaravelOrganizationalRelationshipVerifier.php');
$readRepo = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php');
$writeRepo = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelDurablePolicyAdministrationRepository.php');
$s21Migration = (string) file_get_contents($migrationDir.'/'.$expectedNames[2]);
$s22Migration = (string) file_get_contents($migrationDir.'/'.$expectedNames[3]);
$s23Migration = (string) file_get_contents($migrationDir.'/'.$expectedNames[4]);

$assertM73(substr_count($accessSource, "->where('tenant_id',") >= 4, 'durable access lost tenant predicates');
$assertM73(! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $accessSource), 'durable access introduced unrestricted upsert');
$assertM73(str_contains($tenantVerifier, 'hasTenantMembership'), 'durable tenant verifier lost membership proof');
$assertM73(str_contains($relationshipVerifier, 'DurableOrganizationalAccessGrant'), 'relationship verifier lost durable grant proof');
$assertM73(substr_count($readRepo, "->where('tenant_id',") >= 5 && ! preg_match('/\b(insert|insertOrIgnore|update|updateOrInsert|upsert|delete|truncate)\s*\(/', $readRepo), 'Sprint 21 read policy boundary changed');
$assertM73(substr_count($writeRepo, "->where('tenant_id',") >= 8 && ! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $writeRepo), 'Sprint 22 tenant-safe mutation boundary changed');
$assertM73(str_contains($writeRepo, 'oneqay_identity_organizations') && str_contains($writeRepo, 'oneqay_outlet_access_grants') && str_contains($writeRepo, 'oneqay_device_access_grants'), 'Sprint 22 target eligibility lost organizational access chain');
$assertM73(str_contains($s21Migration, 'fk_organization_role_membership') && str_contains($s21Migration, 'fk_outlet_role_access') && str_contains($s21Migration, 'fk_device_role_access'), 'Sprint 21 relational role constraints changed');
$assertM73(str_contains($s22Migration, 'fk_policy_mutation_actor') && str_contains($s22Migration, "primary(['tenant_id', 'mutation_id']"), 'Sprint 22 journal tenant/actor constraints missing');
$assertM73(str_contains($s23Migration, 'oneqay_initial_tenant_admin_provisionings') && str_contains($s23Migration, 'fk_initial_tenant_admin_identity') && str_contains($s23Migration, 'fk_initial_tenant_admin_permission'), 'Sprint 23 provisioning journal constraints missing');

$policyScope = PolicyAssignmentScope::fromVerifiedContext($device, 'device');
$assertM73($policyScope->matchesActor($device) && $policyScope->deviceId()?->value() === 'device-alpha', 'policy target scope must derive from verified organizational context');
$assertM73(AdministrationPermission::MANAGE === 'authorization.policy.manage', 'policy administration control permission changed');

try { RoleIdentifier::fromString('platform-superadmin'); $assertM73(false, 'tenant role accepted platform-superadmin'); } catch (InvalidArgumentException) {}
try { PermissionIdentifier::fromString('platform.system-update.install'); $assertM73(false, 'tenant permission accepted updater capability'); } catch (InvalidArgumentException) {}
try { new SyntheticOrganizationalRelationshipVerifier(['principal-real' => [['tenant' => 'tenant-alpha', 'organization' => 'organization-alpha']]]); $assertM73(false, 'synthetic-data-only'); } catch (InvalidArgumentException) {}

fwrite(STDOUT, "M7.3 identity and organizational context regression passed with Sprint 23 preservation.\n");
