<?php

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

$assertM72 = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("M7.2 regression failed: {$case}");
    }
};

$expectMissing = static function (
    Request $request,
    string $case,
) use ($assertM72): void {
    $store = new RequestTenantContextStore();
    $middleware = new RequireVerifiedTenantContextMiddleware(
        $store,
        new RequireVerifiedTenantContext(),
    );

    try {
        $middleware->handle($request, static fn (): Response => new Response('unexpected', 200));
        $assertM72(false, $case);
    } catch (MissingTenantContext) {
        $assertM72($store->current() === null, $case.' must clear request-scoped context');
    }
};

$expectMissing(Request::create('/protected', 'GET'), 'M72-ISO-001 missing-context-denied');

$blankContext = new class implements VerifiedTenantContext {
    public function tenantId(): string
    {
        return '   ';
    }
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
    public function tenantId(): string
    {
        return 'tenant.alpha';
    }
};
try {
    (new RequireVerifiedTenantContext())->require($malformedContext);
    $assertM72(false, 'M72-ISO-003 malformed verified context denied');
} catch (MissingTenantContext) {
    // Expected.
}

$expectMissing(
    Request::create('/protected', 'GET', server: ['HTTP_X_TENANT_ID' => 'tenant-alpha']),
    'M72-ISO-004 client-tenant-header-not-authoritative',
);
$expectMissing(
    Request::create('/protected?tenant=tenant-alpha', 'GET'),
    'M72-ISO-005 client-query-tenant-not-authoritative',
);
$expectMissing(
    Request::create('/tenant/tenant-alpha/resource/global-resource-001', 'GET'),
    'M72-ISO-006 client-route-tenant-not-authoritative',
);
$expectMissing(
    Request::create('/protected', 'GET', server: ['HTTP_HOST' => 'tenant-alpha.example.test']),
    'M72-ISO-007 host-subdomain-not-authoritative',
);
$expectMissing(
    Request::create('/protected', 'GET', cookies: ['tenant' => 'tenant-alpha']),
    'TI-04 client-cookie-tenant-not-authoritative',
);

$verifier = new SyntheticTenantMembershipVerifier([
    'synthetic-principal-a' => ['tenant-alpha'],
    'synthetic-principal-b' => ['tenant-beta'],
]);
$alphaContext = $verifier->verify('synthetic-principal-a', 'tenant-alpha');
$betaContext = $verifier->verify('synthetic-principal-b', 'tenant-beta');
$assertM72($alphaContext !== null, 'M72-ISO-015 alpha positive verification control');
$assertM72($betaContext !== null, 'M72-ISO-015 beta positive verification control');
$assertM72(
    $verifier->verify('synthetic-principal-a', 'tenant-beta') === null,
    'raw tenant hint must not bypass synthetic server-side grant verification',
);

$guard = new TenantIsolationGuard(new RequireVerifiedTenantContext());
$alphaResource = new TenantOwnedResourceReference(
    'global-resource-001',
    TenantId::fromString('tenant-alpha'),
);
$betaResourceWithSameGlobalId = new TenantOwnedResourceReference(
    'global-resource-001',
    TenantId::fromString('tenant-beta'),
);

$guard->assertAccessible($alphaContext, $alphaResource);
try {
    $guard->assertAccessible($alphaContext, $betaResourceWithSameGlobalId);
    $assertM72(false, 'M72-ISO-008 cross-tenant-resource-read-denied');
} catch (TenantIsolationViolation $exception) {
    $assertM72(
        $exception->getMessage() === 'Tenant isolation denied.',
        'M72-ISO-013 denial message must remain generic',
    );
    $assertM72(
        ! str_contains($exception->getMessage(), 'tenant-beta')
        && ! str_contains($exception->getMessage(), 'global-resource-001'),
        'M72-ISO-013 error-does-not-leak-foreign-payload',
    );
}

try {
    $guard->assertAccessible($betaContext, $alphaResource);
    $assertM72(false, 'M72-ISO-009 cross-tenant-resource-write-denied');
} catch (TenantIsolationViolation) {
    // Expected.
}

$assertM72(
    $alphaResource->resourceId() === $betaResourceWithSameGlobalId->resourceId(),
    'M72-ISO-010 control requires the same global resource identifier across tenants',
);
try {
    $guard->assertAccessible($alphaContext, $betaResourceWithSameGlobalId);
    $assertM72(false, 'M72-ISO-010 global-id-does-not-bypass-scope');
} catch (TenantIsolationViolation) {
    // Expected.
}

$store = new RequestTenantContextStore();
$assertM72($store->current() === null, 'M72-ISO-012 no-default-tenant');
$store->setVerified($alphaContext);
$middleware = new RequireVerifiedTenantContextMiddleware(
    $store,
    new RequireVerifiedTenantContext(),
);
$response = $middleware->handle(
    Request::create('/future-tenant-owned-route', 'GET'),
    static fn (): Response => new Response('allowed', 200),
);
$assertM72($response->getStatusCode() === 200, 'M72-ISO-015 verified-context-positive-control middleware');
$assertM72($store->current() === null, 'M72-ISO-011 request-context-does-not-leak');
$expectMissing(
    Request::create('/future-tenant-owned-route', 'GET', server: ['HTTP_X_TENANT_ID' => 'tenant-alpha']),
    'M72-ISO-011 next request cannot inherit previous context',
);
$expectMissing(
    Request::create('/protected', 'GET', server: ['HTTP_X_CORRELATION_ID' => 'tenant-alpha']),
    'M72-ISO-014 correlation-id-is-not-tenant-proof',
);

$forbiddenFrameworkReferences = ['Illuminate\\', 'Laravel\\', 'Inertia\\', 'Vue'];
foreach ([
    __DIR__.'/../app/Domain',
    __DIR__.'/../app/Application',
] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());
        foreach ($forbiddenFrameworkReferences as $needle) {
            $assertM72(
                ! str_contains($content, $needle),
                'M72-ARCH-001 framework-independent boundary: '.$file->getPathname().' contains '.$needle,
            );
        }
    }
}

$forbiddenPersistenceReferences = ['Illuminate\\Database', 'Schema::', 'DB::', 'new PDO', 'mysqli_'];
foreach ([
    __DIR__.'/../app/Domain/Tenancy',
    __DIR__.'/../app/Application/Tenancy',
] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());
        foreach ($forbiddenPersistenceReferences as $needle) {
            $assertM72(
                ! str_contains($content, $needle),
                'M72-GOV-001 tenancy Application/Domain persistence boundary: '.$file->getPathname().' contains '.$needle,
            );
        }
    }
}

$migrationDirectory = __DIR__.'/../database/migrations';
$s19Migration = $migrationDirectory.'/0000_00_00_000001_create_foundational_context_graph.php';
$s20Migration = $migrationDirectory.'/0000_00_00_000002_create_organizational_access_grants.php';
$assertM72(is_dir($migrationDirectory), 'M72-PERSIST-001 canonical migration directory missing');
$assertM72(is_file($s19Migration), 'M72-PERSIST-001 Sprint 19 migration missing');
$assertM72(is_file($s20Migration), 'M72-PERSIST-001 Sprint 20 migration missing');
$migrationFiles = glob($migrationDirectory.'/*.php') ?: [];
sort($migrationFiles, SORT_STRING);
$assertM72(
    $migrationFiles === [$s19Migration, $s20Migration],
    'M72-PERSIST-001 migration set must remain exactly Sprint 19 plus Sprint 20',
);

$s19MigrationSource = (string) file_get_contents($s19Migration);
foreach ([
    "primary(['tenant_id', 'id']",
    "foreign(['tenant_id', 'identity_id']",
    "foreign(['tenant_id', 'organization_id']",
    "foreign(['tenant_id', 'outlet_id']",
    "Forward-only generated migration; rollback is not authorized.",
] as $requiredBoundary) {
    $assertM72(
        str_contains($s19MigrationSource, $requiredBoundary),
        'M72-PERSIST-001 Sprint 19 tenant-aware migration boundary missing: '.$requiredBoundary,
    );
}

$s20MigrationSource = (string) file_get_contents($s20Migration);
foreach ([
    'oneqay_outlet_access_grants',
    'oneqay_device_access_grants',
    'fk_outlet_access_membership',
    'fk_device_access_outlet_grant',
    'fk_device_access_device',
    'Forward-only generated migration; rollback is not authorized.',
] as $requiredBoundary) {
    $assertM72(
        str_contains($s20MigrationSource, $requiredBoundary),
        'M72-PERSIST-004 Sprint 20 access migration boundary missing: '.$requiredBoundary,
    );
}

foreach ([
    __DIR__.'/../app/Application/Persistence',
    __DIR__.'/../app/Application/Access',
] as $applicationDirectory) {
    $assertM72(is_dir($applicationDirectory), 'M72-PERSIST-002 Application persistence/access contracts missing');
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($applicationDirectory)) as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());
        foreach (['Illuminate\\', 'Laravel\\', 'Schema::', 'DB::', 'new PDO', 'mysqli_'] as $needle) {
            $assertM72(
                ! str_contains($content, $needle),
                'M72-PERSIST-002 Application persistence/access leaked Infrastructure dependency: '.$needle,
            );
        }
    }
}

$repositoryPath = __DIR__.'/../app/Infrastructure/Persistence/LaravelDurableContextGraphRepository.php';
$repositorySource = (string) file_get_contents($repositoryPath);
$assertM72(
    substr_count($repositorySource, "->where('tenant_id', \$tenant)") >= 4,
    'M72-PERSIST-003 explicit tenant-scoped context repository predicates missing',
);
$assertM72(
    ! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $repositorySource),
    'M72-PERSIST-003 unrestricted context ownership-rewriting upsert introduced',
);

$accessRepositoryPath = __DIR__.'/../app/Infrastructure/Access/LaravelDurableOrganizationalAccessRepository.php';
$assertM72(is_file($accessRepositoryPath), 'M72-PERSIST-004 durable organizational access repository missing');
$accessRepositorySource = (string) file_get_contents($accessRepositoryPath);
$assertM72(
    substr_count($accessRepositorySource, "->where('tenant_id',") >= 4,
    'M72-PERSIST-004 explicit tenant-scoped access repository predicates missing',
);
$assertM72(
    ! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $accessRepositorySource),
    'M72-PERSIST-004 unrestricted access ownership-rewriting upsert introduced',
);
$assertM72(
    str_contains($accessRepositorySource, "in_array(\$runtime, ['local', 'test', 'ci'], true)"),
    'M72-PERSIST-004 Local/Test/CI durable access runtime gate missing',
);

try {
    new SyntheticTenantMembershipVerifier(['principal-real' => ['tenant-alpha']]);
    $assertM72(false, 'M72-GOV-002 synthetic-data-only');
} catch (InvalidArgumentException) {
    // Expected.
}

fwrite(STDOUT, "M7.2 tenant isolation regression passed.\n");
