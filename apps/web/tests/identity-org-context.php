<?php

declare(strict_types=1);

use App\Application\Identity\IdentityContextViolation;
use App\Application\Identity\RequireVerifiedPlatformIdentity;
use App\Application\Identity\VerifiedPlatformIdentity;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Tenancy\MissingTenantContext;
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
        [
            'tenant' => 'tenant-alpha',
            'organization' => 'organization-alpha',
        ],
        [
            'tenant' => 'tenant-alpha',
            'organization' => 'organization-alpha',
            'outlet' => 'outlet-alpha',
        ],
        [
            'tenant' => 'tenant-alpha',
            'organization' => 'organization-alpha',
            'outlet' => 'outlet-alpha',
            'device' => 'device-alpha',
        ],
        [
            'tenant' => 'tenant-alpha',
            'organization' => 'organization-secondary',
            'outlet' => 'outlet-secondary',
            'device' => 'device-secondary',
        ],
    ],
    'synthetic-principal-b' => [
        [
            'tenant' => 'tenant-beta',
            'organization' => 'organization-collision',
            'outlet' => 'outlet-collision',
            'device' => 'device-collision',
        ],
        [
            'tenant' => 'tenant-beta',
            'organization' => 'organization-beta',
            'outlet' => 'outlet-beta',
            'device' => 'device-beta',
        ],
    ],
    'synthetic-principal-c' => [
        [
            'tenant' => 'tenant-alpha',
            'organization' => 'organization-alpha',
        ],
    ],
]);

$alphaTenant = $memberships->verify('synthetic-principal-a', 'tenant-alpha');
$betaTenant = $memberships->verify('synthetic-principal-b', 'tenant-beta');
$assertM73($alphaTenant !== null, 'M73-CTRL-001 alpha tenant membership positive control');
$assertM73($betaTenant !== null, 'M73-CTRL-002 beta tenant membership positive control');

$identityA = new ServerVerifiedPlatformIdentity(
    PlatformIdentityId::fromString('synthetic-principal-a'),
);
$identityB = new ServerVerifiedPlatformIdentity(
    PlatformIdentityId::fromString('synthetic-principal-b'),
);
$identityC = new ServerVerifiedPlatformIdentity(
    PlatformIdentityId::fromString('synthetic-principal-c'),
);

$store = new RequestOrganizationalContextStore();
$enter = new EnterOrganizationalContext(
    new RequireVerifiedPlatformIdentity(),
    new App\Application\Tenancy\RequireVerifiedTenantContext(),
    $memberships,
    $relationships,
    $store,
);

// M73-ID-001 canonical immutable identity primitive.
$canonicalIdentity = PlatformIdentityId::fromString('  SYNTHETIC-PRINCIPAL-A  ');
$assertM73(
    $canonicalIdentity->value() === 'synthetic-principal-a',
    'M73-ID-001 identity identifier canonicalizes deterministically',
);
try {
    PlatformIdentityId::fromString('synthetic.principal.a');
    $assertM73(false, 'M73-ID-002 malformed identity must fail');
} catch (InvalidArgumentException) {
    // Expected.
}

// M73-ID-003 missing verified identity denied.
try {
    $enter->enter(null, $alphaTenant, 'organization-alpha');
    $assertM73(false, 'M73-ID-003 missing identity denied');
} catch (IdentityContextViolation) {
    $assertM73($store->current() === null, 'missing identity leaves no organizational context');
}

// M73-ID-004 malformed verified identity denied.
$malformedIdentity = new class implements VerifiedPlatformIdentity {
    public function identityId(): string
    {
        return 'synthetic.principal.a';
    }
};
try {
    $enter->enter($malformedIdentity, $alphaTenant, 'organization-alpha');
    $assertM73(false, 'M73-ID-004 malformed verified identity denied');
} catch (IdentityContextViolation) {
    $assertM73($store->current() === null, 'malformed identity leaves no organizational context');
}

// M73-TEN-001 missing tenant context denied by M7.2 fail-closed boundary.
try {
    $enter->enter($identityA, null, 'organization-alpha');
    $assertM73(false, 'M73-TEN-001 missing verified tenant context denied');
} catch (MissingTenantContext) {
    $assertM73($store->current() === null, 'missing tenant leaves no organizational context');
}

// M73-CTRL-003 positive organization-only context.
$organizationOnly = $enter->enter($identityA, $alphaTenant, 'organization-alpha');
$assertM73(
    $organizationOnly->identityId()->value() === 'synthetic-principal-a',
    'verified organizational context preserves identity without making it tenant authority',
);
$assertM73(
    $organizationOnly->tenantId()->value() === 'tenant-alpha',
    'verified organizational context derives tenant from verified tenant context',
);
$assertM73(
    $organizationOnly->organizationId()->value() === 'organization-alpha',
    'organization positive control',
);
$assertM73($organizationOnly->outletId() === null, 'organization-only context has no implicit outlet');
$assertM73($organizationOnly->deviceId() === null, 'organization-only context has no implicit device');

// M73-CTRL-004 positive outlet context.
$outletContext = $enter->enter(
    $identityA,
    $alphaTenant,
    'organization-alpha',
    'outlet-alpha',
);
$assertM73(
    $outletContext->outletId()?->value() === 'outlet-alpha',
    'outlet positive control requires server relationship',
);
$assertM73($outletContext->deviceId() === null, 'outlet context has no implicit device');

// M73-CTRL-005 positive device context.
$deviceContext = $enter->enter(
    $identityA,
    $alphaTenant,
    'organization-alpha',
    'outlet-alpha',
    'device-alpha',
);
$assertM73(
    $deviceContext->deviceId()?->value() === 'device-alpha',
    'device positive control requires server relationship',
);

// M73-AUTHZ-001 identity without tenant membership denied even with a synthetic org relationship.
try {
    $enter->enter($identityC, $alphaTenant, 'organization-alpha');
    $assertM73(false, 'M73-AUTHZ-001 identity without tenant membership denied');
} catch (OrganizationalAccessViolation $exception) {
    $assertM73(
        $exception->getMessage() === 'Organizational context denied.',
        'denial remains generic',
    );
}

// M73-AUTHZ-002 identity belonging to another tenant denied.
try {
    $enter->enter($identityB, $alphaTenant, 'organization-alpha');
    $assertM73(false, 'M73-AUTHZ-002 foreign tenant identity denied');
} catch (OrganizationalAccessViolation) {
    // Expected.
}

// M73-AUTHZ-003 organization from another tenant denied.
try {
    $enter->enter(
        $identityA,
        $alphaTenant,
        'organization-collision',
        'outlet-collision',
        'device-collision',
    );
    $assertM73(false, 'M73-AUTHZ-003 foreign tenant organization denied');
} catch (OrganizationalAccessViolation $exception) {
    $assertM73(
        ! str_contains($exception->getMessage(), 'tenant-beta')
        && ! str_contains($exception->getMessage(), 'organization-collision')
        && ! str_contains($exception->getMessage(), 'outlet-collision')
        && ! str_contains($exception->getMessage(), 'device-collision'),
        'foreign context denial does not leak foreign payload',
    );
}

// M73-AUTHZ-004 same global org/outlet/device identifiers do not bypass tenant scope.
$betaCollision = $enter->enter(
    $identityB,
    $betaTenant,
    'organization-collision',
    'outlet-collision',
    'device-collision',
);
$assertM73(
    $betaCollision->tenantId()->value() === 'tenant-beta',
    'collision positive control belongs only to beta verified tenant',
);
try {
    $enter->enter(
        $identityA,
        $alphaTenant,
        'organization-collision',
        'outlet-collision',
        'device-collision',
    );
    $assertM73(false, 'M73-AUTHZ-004 global identifier collision must not bypass tenant scope');
} catch (OrganizationalAccessViolation) {
    // Expected.
}

// M73-AUTHZ-005 outlet from another organization denied.
try {
    $enter->enter(
        $identityA,
        $alphaTenant,
        'organization-alpha',
        'outlet-secondary',
        'device-secondary',
    );
    $assertM73(false, 'M73-AUTHZ-005 outlet from another organization denied');
} catch (OrganizationalAccessViolation) {
    // Expected.
}

// M73-AUTHZ-006 device from another outlet denied.
try {
    $enter->enter(
        $identityA,
        $alphaTenant,
        'organization-alpha',
        'outlet-alpha',
        'device-secondary',
    );
    $assertM73(false, 'M73-AUTHZ-006 device from another outlet denied');
} catch (OrganizationalAccessViolation) {
    // Expected.
}

// M73-AUTHZ-007 client organization/outlet/device hints select only; they never grant access.
foreach ([
    ['organization-alpha', 'outlet-not-granted', null],
    ['organization-not-granted', null, null],
    ['organization-alpha', 'outlet-alpha', 'device-not-granted'],
] as [$organizationHint, $outletHint, $deviceHint]) {
    try {
        $enter->enter($identityA, $alphaTenant, $organizationHint, $outletHint, $deviceHint);
        $assertM73(false, 'M73-AUTHZ-007 raw organizational hint escalation denied');
    } catch (OrganizationalAccessViolation) {
        // Expected.
    }
}

// M73-AUTHZ-008 device cannot exist without an outlet.
try {
    $enter->enter($identityA, $alphaTenant, 'organization-alpha', null, 'device-alpha');
    $assertM73(false, 'M73-AUTHZ-008 device without outlet denied');
} catch (OrganizationalAccessViolation) {
    // Expected.
}

// M73-AUTHZ-009 raw tenant hint cannot override server-verified membership.
$assertM73(
    $memberships->verify('synthetic-principal-a', 'tenant-beta') === null,
    'M73-AUTHZ-009 raw tenant hint cannot create membership',
);

// M73-CTX-001 no default organizational context.
$enter->clear();
$assertM73($store->current() === null, 'M73-CTX-001 no default organizational context');

// M73-CTX-002 request context can be explicitly established then cleared.
$enter->enter(
    $identityA,
    $alphaTenant,
    'organization-alpha',
    'outlet-alpha',
    'device-alpha',
);
$assertM73($store->current() !== null, 'M73-CTX-002 verified context stored for bounded request scope');
$enter->clear();
$assertM73($store->current() === null, 'M73-CTX-002 request context clears');

// M73-CTX-003 failed next request cannot inherit stale context.
$enter->enter($identityA, $alphaTenant, 'organization-alpha');
$assertM73($store->current() !== null, 'M73-CTX-003 stale-context positive setup');
try {
    $enter->enter($identityA, $alphaTenant, 'organization-not-granted');
    $assertM73(false, 'M73-CTX-003 failed request must deny');
} catch (OrganizationalAccessViolation) {
    $assertM73($store->current() === null, 'M73-CTX-003 failed request clears previous context before evaluation');
}

// M73-ARCH-001 Domain/Application remain framework-independent.
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
            $assertM73(
                ! str_contains($content, $needle),
                'M73-ARCH-001 framework-independent boundary: '.$file->getPathname().' contains '.$needle,
            );
        }
    }
}

// M73-GOV-001 no database/schema/migration/SQL implementation.
$forbiddenPersistenceReferences = [
    'Illuminate\\Database',
    'Schema::',
    'DB::',
    'new PDO',
    'mysqli_',
];
foreach ([
    __DIR__.'/../app/Domain/Identity',
    __DIR__.'/../app/Domain/Organization',
    __DIR__.'/../app/Domain/Outlet',
    __DIR__.'/../app/Domain/Device',
    __DIR__.'/../app/Application/Identity',
    __DIR__.'/../app/Application/Organization',
    __DIR__.'/../app/Infrastructure/Identity',
    __DIR__.'/../app/Infrastructure/Organization',
] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());
        foreach ($forbiddenPersistenceReferences as $needle) {
            $assertM73(
                ! str_contains($content, $needle),
                'M73-GOV-001 persistence boundary: '.$file->getPathname().' contains '.$needle,
            );
        }
    }
}
$assertM73(! is_dir(__DIR__.'/../database/migrations'), 'M73-GOV-001 no migration directory introduced');

// M73-GOV-002 synthetic relationship evidence cannot be initialized with a real principal identifier.
try {
    new SyntheticOrganizationalRelationshipVerifier([
        'principal-real' => [
            [
                'tenant' => 'tenant-alpha',
                'organization' => 'organization-alpha',
            ],
        ],
    ]);
    $assertM73(false, 'M73-GOV-002 synthetic-data-only');
} catch (InvalidArgumentException) {
    // Expected.
}

fwrite(STDOUT, "M7.3 identity and organizational context regression passed.\n");
