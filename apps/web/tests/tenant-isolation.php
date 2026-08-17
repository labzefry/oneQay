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

// M72-ISO-001 missing-context-denied.
$expectMissing(Request::create('/protected', 'GET'), 'M72-ISO-001 missing-context-denied');

// M72-ISO-002 blank-context-denied.
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

// M72-ISO-003 malformed-tenant-id-denied.
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

// M72-ISO-004 client-tenant-header-not-authoritative.
$expectMissing(
    Request::create('/protected', 'GET', server: ['HTTP_X_TENANT_ID' => 'tenant-alpha']),
    'M72-ISO-004 client-tenant-header-not-authoritative',
);

// M72-ISO-005 client-query-tenant-not-authoritative.
$expectMissing(
    Request::create('/protected?tenant=tenant-alpha', 'GET'),
    'M72-ISO-005 client-query-tenant-not-authoritative',
);

// M72-ISO-006 client-route-tenant-not-authoritative.
$expectMissing(
    Request::create('/tenant/tenant-alpha/resource/global-resource-001', 'GET'),
    'M72-ISO-006 client-route-tenant-not-authoritative',
);

// M72-ISO-007 host-subdomain-not-authoritative.
$expectMissing(
    Request::create('/protected', 'GET', server: ['HTTP_HOST' => 'tenant-alpha.example.test']),
    'M72-ISO-007 host-subdomain-not-authoritative',
);

// Cookie/client state is also non-authoritative under TI-04.
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

// M72-ISO-015 verified-context-positive-control.
$guard->assertAccessible($alphaContext, $alphaResource);

// M72-ISO-008 cross-tenant-resource-read-denied.
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

// M72-ISO-009 cross-tenant-resource-write-denied.
try {
    $guard->assertAccessible($betaContext, $alphaResource);
    $assertM72(false, 'M72-ISO-009 cross-tenant-resource-write-denied');
} catch (TenantIsolationViolation) {
    // Expected.
}

// M72-ISO-010 global-id-does-not-bypass-scope.
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

// M72-ISO-011 request-context-does-not-leak and M72-ISO-012 no-default-tenant.
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

// M72-ISO-014 correlation-id-is-not-tenant-proof.
$expectMissing(
    Request::create('/protected', 'GET', server: ['HTTP_X_CORRELATION_ID' => 'tenant-alpha']),
    'M72-ISO-014 correlation-id-is-not-tenant-proof',
);

// M72-ARCH-001 Domain/Application remain framework-independent even after durable persistence contracts.
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
                'M72-ARCH-001 domain-application-framework-independent: '.$file->getPathname().' contains '.$needle,
            );
        }
    }
}

// M72-GOV-001 tenancy core remains persistence-framework independent.
$forbiddenPersistenceReferences = ['Illuminate\\Database', 'Schema::', 'DB::', 'new PDO', 'mysqli_'];
foreach ([
    __DIR__.'/../app/Domain/Tenancy',
    __DIR__.'/../app/Application/Tenancy',
    __DIR__.'/../app/Infrastructure/Tenancy',
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
                'M72-GOV-001 tenancy core persistence boundary: '.$file->getPathname().' contains '.$needle,
            );
        }
    }
}

// M72-PERSIST-001 canonical migration set is bounded and tenant-aware.
$migrationDirectory = __DIR__.'/../database/migrations';
$canonicalMigration = $migrationDirectory.'/0000_00_00_000001_create_foundational_context_graph.php';
$assertM72(is_dir($migrationDirectory), 'M72-PERSIST-001 canonical migration directory missing');
$assertM72(is_file($canonicalMigration), 'M72-PERSIST-001 canonical foundational migration missing');
$migrationFiles = glob($migrationDirectory.'/*.php') ?: [];
sort($migrationFiles, SORT_STRING);
$assertM72(
    $migrationFiles === [$canonicalMigration],
    'M72-PERSIST-001 migration set must remain exactly the Sprint 19 foundational migration',
);
$migrationSource = (string) file_get_contents($canonicalMigration);
foreach ([
    "primary(['tenant_id', 'id']",
    "foreign(['tenant_id', 'identity_id']",
    "foreign(['tenant_id', 'organization_id']",
    "foreign(['tenant_id', 'outlet_id']",
    "Forward-only generated migration; rollback is not authorized.",
] as $requiredBoundary) {
    $assertM72(
        str_contains($migrationSource, $requiredBoundary),
        'M72-PERSIST-001 tenant-aware migration boundary missing: '.$requiredBoundary,
    );
}

// M72-PERSIST-002 Application persistence contracts contain no DB/framework mechanics.
$applicationPersistenceDirectory = __DIR__.'/../app/Application/Persistence';
$assertM72(is_dir($applicationPersistenceDirectory), 'M72-PERSIST-002 Application persistence contracts missing');
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($applicationPersistenceDirectory)) as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $content = (string) file_get_contents($file->getPathname());
    foreach (['Illuminate\\', 'Laravel\\', 'Schema::', 'DB::', 'new PDO', 'mysqli_'] as $needle) {
        $assertM72(
            ! str_contains($content, $needle),
            'M72-PERSIST-002 Application persistence leaked Infrastructure dependency: '.$needle,
        );
    }
}

// M72-PERSIST-003 Infrastructure reads remain explicitly tenant scoped and cannot silently rewrite ownership.
$repositoryPath = __DIR__.'/../app/Infrastructure/Persistence/LaravelDurableContextGraphRepository.php';
$assertM72(is_file($repositoryPath), 'M72-PERSIST-003 durable repository missing');
$repositorySource = (string) file_get_contents($repositoryPath);
$assertM72(
    substr_count($repositorySource, "->where('tenant_id', \$tenant)") >= 4,
    'M72-PERSIST-003 explicit tenant-scoped repository predicates missing',
);
$assertM72(
    ! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $repositorySource),
    'M72-PERSIST-003 unrestricted relationship-rewriting upsert introduced',
);
$assertM72(
    str_contains($repositorySource, "in_array(\$runtime, ['local', 'test', 'ci'], true)"),
    'M72-PERSIST-003 Local/Test/CI runtime gate missing from repository',
);

// M72-GOV-002 synthetic-data-only remains enforced for membership fixtures.
try {
    new SyntheticTenantMembershipVerifier(['principal-real' => ['tenant-alpha']]);
    $assertM72(false, 'M72-GOV-002 synthetic-data-only');
} catch (InvalidArgumentException) {
    // Expected.
}

fwrite(STDOUT, "M7.2 tenant isolation regression passed.\n");
