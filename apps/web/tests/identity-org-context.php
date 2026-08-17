<?php

declare(strict_types=1);

use App\Application\Authorization\PermissionIdentifier;
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
    if (! $condition) {
        throw new RuntimeException("M7.3 regression failed: {$case}");
    }
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
    'synthetic-principal-c' => [
        ['tenant' => 'tenant-alpha', 'organization' => 'organization-alpha'],
    ],
]);

$alphaTenant = $memberships->verify('synthetic-principal-a', 'tenant-alpha');
$betaTenant = $memberships->verify('synthetic-principal-b', 'tenant-beta');
$assertM73($alphaTenant !== null && $betaTenant !== null, 'tenant membership positive controls');

$identityA = new ServerVerifiedPlatformIdentity(PlatformIdentityId::fromString('synthetic-principal-a'));
$identityB = new ServerVerifiedPlatformIdentity(PlatformIdentityId::fromString('synthetic-principal-b'));
$identityC = new ServerVerifiedPlatformIdentity(PlatformIdentityId::fromString('synthetic-principal-c'));
$store = new RequestOrganizationalContextStore();
$enter = new EnterOrganizationalContext(
    new RequireVerifiedPlatformIdentity(),
    new RequireVerifiedTenantContext(),
    $memberships,
    $relationships,
    $store,
);

$assertM73(PlatformIdentityId::fromString('  SYNTHETIC-PRINCIPAL-A  ')->value() === 'synthetic-principal-a', 'identity canonicalization');
try {
    PlatformIdentityId::fromString('synthetic.principal.a');
    $assertM73(false, 'malformed identity must fail');
} catch (InvalidArgumentException) {
    // Expected.
}

try {
    $enter->enter(null, $alphaTenant, 'organization-alpha');
    $assertM73(false, 'missing identity denied');
} catch (IdentityContextViolation) {
    $assertM73($store->current() === null, 'missing identity leaves no context');
}

$malformedIdentity = new class implements VerifiedPlatformIdentity {
    public function identityId(): string { return 'synthetic.principal.a'; }
};
try {
    $enter->enter($malformedIdentity, $alphaTenant, 'organization-alpha');
    $assertM73(false, 'malformed verified identity denied');
} catch (IdentityContextViolation) {
    $assertM73($store->current() === null, 'malformed identity leaves no context');
}
try {
    $enter->enter($identityA, null, 'organization-alpha');
    $assertM73(false, 'missing verified tenant denied');
} catch (MissingTenantContext) {
    $assertM73($store->current() === null, 'missing tenant leaves no context');
}

$organizationOnly = $enter->enter($identityA, $alphaTenant, 'organization-alpha');
$assertM73($organizationOnly->identityId()->value() === 'synthetic-principal-a', 'organization identity control');
$assertM73($organizationOnly->tenantId()->value() === 'tenant-alpha', 'organization tenant control');
$assertM73($organizationOnly->organizationId()->value() === 'organization-alpha', 'organization id control');
$assertM73($organizationOnly->outletId() === null && $organizationOnly->deviceId() === null, 'organization context has no implicit descendants');

$outletContext = $enter->enter($identityA, $alphaTenant, 'organization-alpha', 'outlet-alpha');
$assertM73($outletContext->outletId()?->value() === 'outlet-alpha' && $outletContext->deviceId() === null, 'outlet positive control');
$deviceContext = $enter->enter($identityA, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-alpha');
$assertM73($deviceContext->deviceId()?->value() === 'device-alpha', 'device positive control');

foreach ([
    [$identityC, $alphaTenant, 'organization-alpha', null, null, 'identity without tenant membership'],
    [$identityB, $alphaTenant, 'organization-alpha', null, null, 'identity belonging to another tenant'],
    [$identityA, $alphaTenant, 'organization-collision', 'outlet-collision', 'device-collision', 'foreign organization'],
    [$identityA, $alphaTenant, 'organization-alpha', 'outlet-secondary', 'device-secondary', 'outlet from another organization'],
    [$identityA, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-secondary', 'device from another outlet'],
    [$identityA, $alphaTenant, 'organization-alpha', 'outlet-not-granted', null, 'ungranted outlet hint'],
    [$identityA, $alphaTenant, 'organization-not-granted', null, null, 'ungranted organization hint'],
    [$identityA, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-not-granted', 'ungranted device hint'],
    [$identityA, $alphaTenant, 'organization-alpha', null, 'device-alpha', 'device without outlet'],
] as [$identity, $tenant, $organization, $outlet, $device, $case]) {
    try {
        $enter->enter($identity, $tenant, $organization, $outlet, $device);
        $assertM73(false, $case.' denied');
    } catch (OrganizationalAccessViolation $exception) {
        $assertM73($exception->getMessage() === 'Organizational context denied.', $case.' uses generic denial');
        foreach (['tenant-beta', 'organization-collision', 'device-collision'] as $foreign) {
            $assertM73(! str_contains($exception->getMessage(), $foreign), $case.' denial does not leak foreign data');
        }
    }
}

$betaCollision = $enter->enter($identityB, $betaTenant, 'organization-collision', 'outlet-collision', 'device-collision');
$assertM73($betaCollision->tenantId()->value() === 'tenant-beta', 'same textual resource IDs remain tenant-bound');
$assertM73($memberships->verify('synthetic-principal-a', 'tenant-beta') === null, 'raw tenant hint cannot create membership');

$enter->clear();
$assertM73($store->current() === null, 'no default organizational context');
$enter->enter($identityA, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-alpha');
$assertM73($store->current() !== null, 'verified context stored for request scope');
$enter->clear();
$assertM73($store->current() === null, 'request context clears');
$enter->enter($identityA, $alphaTenant, 'organization-alpha');
try {
    $enter->enter($identityA, $alphaTenant, 'organization-not-granted');
    $assertM73(false, 'failed subsequent request denied');
} catch (OrganizationalAccessViolation) {
    $assertM73($store->current() === null, 'failed request clears previous context');
}

foreach ([__DIR__.'/../app/Domain', __DIR__.'/../app/Application'] as $directory) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $content = (string) file_get_contents($file->getPathname());
        foreach (['Illuminate\\', 'Laravel\\', 'Inertia\\', 'Vue'] as $needle) {
            $assertM73(! str_contains($content, $needle), 'framework-independent boundary: '.$file->getPathname().' contains '.$needle);
        }
    }
}

$migrationDirectory = __DIR__.'/../database/migrations';
$s19 = $migrationDirectory.'/0000_00_00_000001_create_foundational_context_graph.php';
$s20 = $migrationDirectory.'/0000_00_00_000002_create_organizational_access_grants.php';
$s21 = $migrationDirectory.'/0000_00_00_000003_create_scoped_role_permission_policy.php';
$migrationFiles = glob($migrationDirectory.'/*.php') ?: [];
sort($migrationFiles, SORT_STRING);
$assertM73($migrationFiles === [$s19, $s20, $s21], 'migration set must remain exactly Sprint 19/20/21');

$accessRepositorySource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Access/LaravelDurableOrganizationalAccessRepository.php');
$tenantVerifierSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Tenancy/LaravelTenantMembershipVerifier.php');
$relationshipVerifierSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Organization/LaravelOrganizationalRelationshipVerifier.php');
$authorizationRepositorySource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php');
$roleSource = (string) file_get_contents(__DIR__.'/../app/Application/Authorization/RoleIdentifier.php');
$permissionSource = (string) file_get_contents(__DIR__.'/../app/Application/Authorization/PermissionIdentifier.php');
$s21Source = (string) file_get_contents($s21);

$assertM73(substr_count($accessRepositorySource, "->where('tenant_id',") >= 4, 'durable access lost tenant predicates');
$assertM73(! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $accessRepositorySource), 'durable access introduced unrestricted upsert');
$assertM73(str_contains($tenantVerifierSource, 'hasTenantMembership'), 'durable tenant verifier lost membership proof');
$assertM73(str_contains($relationshipVerifierSource, 'DurableOrganizationalAccessGrant'), 'durable relationship verifier lost scoped grant proof');
$assertM73(substr_count($authorizationRepositorySource, "->where('tenant_id',") >= 5, 'durable authorization lost tenant predicates');
$assertM73(! preg_match('/\b(insert|insertOrIgnore|update|updateOrInsert|upsert|delete|truncate)\s*\(/', $authorizationRepositorySource), 'durable authorization repository must remain read-only');
$assertM73(str_contains($roleSource, 'platform-superadmin'), 'platform-superadmin role reservation missing');
$assertM73(str_contains($permissionSource, "'platform.'"), 'platform permission reservation missing');
$assertM73(str_contains($s21Source, 'fk_organization_role_membership'), 'organization role assignment lost verified membership FK');
$assertM73(str_contains($s21Source, 'fk_outlet_role_access'), 'outlet role assignment lost verified access FK');
$assertM73(str_contains($s21Source, 'fk_device_role_access'), 'device role assignment lost verified access FK');

try {
    RoleIdentifier::fromString('platform-superadmin');
    $assertM73(false, 'tenant role policy accepted platform-superadmin');
} catch (InvalidArgumentException) {
    // Expected.
}
try {
    PermissionIdentifier::fromString('platform.system-update.install');
    $assertM73(false, 'tenant permission policy accepted platform updater permission');
} catch (InvalidArgumentException) {
    // Expected.
}

try {
    new SyntheticOrganizationalRelationshipVerifier([
        'principal-real' => [['tenant' => 'tenant-alpha', 'organization' => 'organization-alpha']],
    ]);
    $assertM73(false, 'synthetic-data-only');
} catch (InvalidArgumentException) {
    // Expected.
}

fwrite(STDOUT, "M7.3 identity and organizational context regression passed.\n");
