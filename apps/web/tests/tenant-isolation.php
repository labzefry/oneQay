<?php

declare(strict_types=1);

use App\Application\Tenancy\MissingTenantContext;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Application\Tenancy\TenantIsolationGuard;
use App\Application\Tenancy\TenantIsolationViolation;
use App\Application\Tenancy\VerifiedTenantContext;
use App\Delivery\Http\Middleware\RequireVerifiedTenantContextMiddleware;
use App\Domain\Tenancy\TenantId;
use App\Domain\Tenancy\TenantOwnedResourceReference;
use App\Infrastructure\Tenancy\RequestTenantContextStore;
use App\Infrastructure\Tenancy\SyntheticTenantMembershipVerifier;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assertM72 = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("M7.2 regression failed: {$case}");
    }
};

$expectMissing = static function (Request $request, string $case) use ($assertM72): void {
    $store = new RequestTenantContextStore();
    $middleware = new RequireVerifiedTenantContextMiddleware($store, new RequireVerifiedTenantContext());
    try {
        $middleware->handle($request, static fn (): Response => new Response('unexpected', 200));
        $assertM72(false, $case);
    } catch (MissingTenantContext) {
        $assertM72($store->current() === null, $case.' must clear request-scoped context');
    }
};

$expectMissing(Request::create('/protected', 'GET'), 'M72-ISO-001 missing-context-denied');

$blankContext = new class implements VerifiedTenantContext {
    public function tenantId(): string { return '   '; }
};
try {
    (new RequireVerifiedTenantContext())->require($blankContext);
    $assertM72(false, 'M72-ISO-002 blank-context-denied');
} catch (MissingTenantContext) {
    // Expected.
}

try {
    TenantId::fromString('tenant.alpha');
    $assertM72(false, 'M72-ISO-003 malformed-tenant-id-denied');
} catch (InvalidArgumentException) {
    // Expected.
}

$malformedContext = new class implements VerifiedTenantContext {
    public function tenantId(): string { return 'tenant.alpha'; }
};
try {
    (new RequireVerifiedTenantContext())->require($malformedContext);
    $assertM72(false, 'M72-ISO-003 malformed verified context denied');
} catch (MissingTenantContext) {
    // Expected.
}

foreach ([
    Request::create('/protected', 'GET', server: ['HTTP_X_TENANT_ID' => 'tenant-alpha']),
    Request::create('/protected?tenant=tenant-alpha', 'GET'),
    Request::create('/tenant/tenant-alpha/resource/global-resource-001', 'GET'),
    Request::create('/protected', 'GET', server: ['HTTP_HOST' => 'tenant-alpha.example.test']),
    Request::create('/protected', 'GET', cookies: ['tenant' => 'tenant-alpha']),
    Request::create('/protected', 'GET', server: ['HTTP_X_CORRELATION_ID' => 'tenant-alpha']),
] as $index => $request) {
    $expectMissing($request, 'M72 client tenant surface is not authoritative #'.$index);
}

$verifier = new SyntheticTenantMembershipVerifier([
    'synthetic-principal-a' => ['tenant-alpha'],
    'synthetic-principal-b' => ['tenant-beta'],
]);
$alphaContext = $verifier->verify('synthetic-principal-a', 'tenant-alpha');
$betaContext = $verifier->verify('synthetic-principal-b', 'tenant-beta');
$assertM72($alphaContext !== null && $betaContext !== null, 'M72 verified tenant positive controls');
$assertM72($verifier->verify('synthetic-principal-a', 'tenant-beta') === null, 'raw tenant hint cannot bypass membership');

$guard = new TenantIsolationGuard(new RequireVerifiedTenantContext());
$alphaResource = new TenantOwnedResourceReference('global-resource-001', TenantId::fromString('tenant-alpha'));
$betaResource = new TenantOwnedResourceReference('global-resource-001', TenantId::fromString('tenant-beta'));
$guard->assertAccessible($alphaContext, $alphaResource);
foreach ([[$alphaContext, $betaResource], [$betaContext, $alphaResource]] as [$context, $resource]) {
    try {
        $guard->assertAccessible($context, $resource);
        $assertM72(false, 'cross-tenant resource access denied');
    } catch (TenantIsolationViolation $exception) {
        $assertM72($exception->getMessage() === 'Tenant isolation denied.', 'tenant denial remains generic');
        $assertM72(! str_contains($exception->getMessage(), 'global-resource-001'), 'tenant denial hides foreign payload');
    }
}
$assertM72($alphaResource->resourceId() === $betaResource->resourceId(), 'same global resource id collision control');

$store = new RequestTenantContextStore();
$assertM72($store->current() === null, 'no default tenant');
$store->setVerified($alphaContext);
$middleware = new RequireVerifiedTenantContextMiddleware($store, new RequireVerifiedTenantContext());
$response = $middleware->handle(Request::create('/future-tenant-owned-route', 'GET'), static fn (): Response => new Response('allowed', 200));
$assertM72($response->getStatusCode() === 200, 'verified tenant middleware positive control');
$assertM72($store->current() === null, 'request tenant context must not leak');

foreach ([__DIR__.'/../app/Domain', __DIR__.'/../app/Application'] as $directory) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $content = (string) file_get_contents($file->getPathname());
        foreach (['Illuminate\\', 'Laravel\\', 'Inertia\\', 'Vue'] as $needle) {
            $assertM72(! str_contains($content, $needle), 'framework-independent boundary: '.$file->getPathname().' contains '.$needle);
        }
    }
}

$migrationDirectory = __DIR__.'/../database/migrations';
$s19 = $migrationDirectory.'/0000_00_00_000001_create_foundational_context_graph.php';
$s20 = $migrationDirectory.'/0000_00_00_000002_create_organizational_access_grants.php';
$s21 = $migrationDirectory.'/0000_00_00_000003_create_scoped_role_permission_policy.php';
$migrationFiles = glob($migrationDirectory.'/*.php') ?: [];
sort($migrationFiles, SORT_STRING);
$assertM72($migrationFiles === [$s19, $s20, $s21], 'migration set must remain exactly Sprint 19/20/21');

$s19Source = (string) file_get_contents($s19);
foreach (["primary(['tenant_id', 'id']", "foreign(['tenant_id', 'identity_id']", "foreign(['tenant_id', 'organization_id']", "foreign(['tenant_id', 'outlet_id']", 'Forward-only generated migration; rollback is not authorized.'] as $boundary) {
    $assertM72(str_contains($s19Source, $boundary), 'Sprint 19 tenant boundary missing: '.$boundary);
}
$s20Source = (string) file_get_contents($s20);
foreach (['oneqay_outlet_access_grants', 'oneqay_device_access_grants', 'fk_outlet_access_membership', 'fk_device_access_outlet_grant', 'fk_device_access_device', 'Forward-only generated migration; rollback is not authorized.'] as $boundary) {
    $assertM72(str_contains($s20Source, $boundary), 'Sprint 20 access boundary missing: '.$boundary);
}
$s21Source = (string) file_get_contents($s21);
foreach (['oneqay_roles', 'oneqay_role_permissions', 'oneqay_tenant_role_assignments', 'oneqay_organization_role_assignments', 'oneqay_outlet_role_assignments', 'oneqay_device_role_assignments', 'fk_organization_role_membership', 'fk_outlet_role_access', 'fk_device_role_access', 'Forward-only generated migration; rollback is not authorized.'] as $boundary) {
    $assertM72(str_contains($s21Source, $boundary), 'Sprint 21 tenant-scoped policy boundary missing: '.$boundary);
}

foreach ([__DIR__.'/../app/Application/Persistence', __DIR__.'/../app/Application/Access', __DIR__.'/../app/Application/Authorization'] as $directory) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $content = (string) file_get_contents($file->getPathname());
        foreach (['Illuminate\\', 'Laravel\\', 'Schema::', 'DB::', 'new PDO', 'mysqli_'] as $needle) {
            $assertM72(! str_contains($content, $needle), 'Application persistence/access/authorization leaked Infrastructure dependency: '.$needle);
        }
    }
}

$contextRepository = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Persistence/LaravelDurableContextGraphRepository.php');
$assertM72(substr_count($contextRepository, "->where('tenant_id', \$tenant)") >= 4, 'context repository tenant predicates missing');
$assertM72(! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $contextRepository), 'context ownership-rewriting upsert introduced');

$accessRepository = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Access/LaravelDurableOrganizationalAccessRepository.php');
$assertM72(substr_count($accessRepository, "->where('tenant_id',") >= 4, 'access repository tenant predicates missing');
$assertM72(str_contains($accessRepository, "['local', 'test', 'ci']"), 'access runtime gate missing');

$authorizationRepository = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php');
$assertM72(substr_count($authorizationRepository, "->where('tenant_id',") >= 5, 'authorization repository tenant predicates missing');
$assertM72(str_contains($authorizationRepository, "['local', 'test', 'ci']"), 'authorization runtime gate missing');
$assertM72(! preg_match('/\b(insert|insertOrIgnore|update|updateOrInsert|upsert|delete|truncate)\s*\(/', $authorizationRepository), 'authorization repository must remain read-only');
$assertM72(! str_contains($authorizationRepository, 'Schema::'), 'authorization repository must not mutate schema');

$roleSource = (string) file_get_contents(__DIR__.'/../app/Application/Authorization/RoleIdentifier.php');
$permissionSource = (string) file_get_contents(__DIR__.'/../app/Application/Authorization/PermissionIdentifier.php');
$assertM72(str_contains($roleSource, 'platform-superadmin'), 'platform-superadmin reservation missing');
$assertM72(str_contains($permissionSource, "'platform.'"), 'platform permission namespace reservation missing');

try {
    new SyntheticTenantMembershipVerifier(['principal-real' => ['tenant-alpha']]);
    $assertM72(false, 'synthetic-data-only guard');
} catch (InvalidArgumentException) {
    // Expected.
}

fwrite(STDOUT, "M7.2 tenant isolation regression passed.\n");
