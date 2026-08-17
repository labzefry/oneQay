<?php

declare(strict_types=1);

use App\Application\Access\DurableOrganizationalAccessGrant;
use App\Application\Access\DurableOrganizationalAccessService;
use App\Application\Access\DurableOrganizationalAccessViolation;
use App\Application\Identity\RequireVerifiedPlatformIdentity;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Persistence\DurableContextGraph;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Access\LaravelDurableOrganizationalAccessRepository;
use App\Infrastructure\Identity\ServerVerifiedPlatformIdentity;
use App\Infrastructure\Organization\LaravelOrganizationalRelationshipVerifier;
use App\Infrastructure\Organization\RequestOrganizationalContextStore;
use App\Infrastructure\Organization\SyntheticOrganizationalRelationshipVerifier;
use App\Infrastructure\Persistence\LaravelDurableContextGraphRepository;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use App\Infrastructure\Tenancy\LaravelTenantMembershipVerifier;
use App\Infrastructure\Tenancy\SyntheticTenantMembershipVerifier;

// Author by Lab | zefry
if (! isset($app, $assert) || ! is_callable($assert)) {
    throw new RuntimeException('Sprint 20 access persistence regression requires the M7.1 application harness.');
}

$assert(extension_loaded('pdo_sqlite'), 'Sprint 20 access persistence regression requires pdo_sqlite in CI.');

$removeS20Tree = null;
$removeS20Tree = static function (string $path) use (&$removeS20Tree): void {
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (! is_dir($path)) {
        return;
    }

    $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $item) {
        $removeS20Tree($item->getPathname());
    }
    @rmdir($path);
};

$s20Parent = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s20-access-'.getmypid();
$removeS20Tree($s20Parent);
$assert(@mkdir($s20Parent, 0700, false), 'Sprint 20 access workspace could not be created.');
$s20DatabasePath = $s20Parent.DIRECTORY_SEPARATOR.'access.sqlite';
$assert(touch($s20DatabasePath), 'Sprint 20 disposable access SQLite file could not be created.');

$app['config']->set('database.default', 's20_sqlite');
$app['config']->set('database.connections.s20_sqlite', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $s20DatabasePath,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);
$app['config']->set('oneqay.runtime_class', 'ci');
$app['config']->set('database.oneqay_persistence_enabled', false);

/** @var \Illuminate\Database\DatabaseManager $s20DatabaseManager */
$s20DatabaseManager = $app->make('db');
$s20DatabaseManager->purge('s20_sqlite');
$s20DatabaseManager->setDefaultConnection('s20_sqlite');
$s20Connection = $s20DatabaseManager->connection('s20_sqlite');
$s20Connection->getPdo();

$disabledAccess = new LaravelDurableOrganizationalAccessRepository($s20Connection, false, 'ci');
try {
    $disabledAccess->hasTenantMembership(
        TenantId::fromString('tenant-alpha'),
        PlatformIdentityId::fromString('synthetic-principal-alpha'),
    );
    $assert(false, 'Sprint 20 disabled durable access repository was accepted.');
} catch (DurableOrganizationalAccessViolation $exception) {
    $assert(
        $exception->errorCode === DurableOrganizationalAccessViolation::PERSISTENCE_DISABLED,
        'Sprint 20 disabled durable access returned an unexpected error.',
    );
}

$previewAccess = new LaravelDurableOrganizationalAccessRepository($s20Connection, true, 'preview');
try {
    $previewAccess->hasTenantMembership(
        TenantId::fromString('tenant-alpha'),
        PlatformIdentityId::fromString('synthetic-principal-alpha'),
    );
    $assert(false, 'Sprint 20 Preview durable access repository was accepted.');
} catch (DurableOrganizationalAccessViolation $exception) {
    $assert(
        $exception->errorCode === DurableOrganizationalAccessViolation::RUNTIME_DENIED,
        'Sprint 20 Preview durable access returned an unexpected error.',
    );
}

$s19MigrationPath = __DIR__.'/../database/migrations/0000_00_00_000001_create_foundational_context_graph.php';
$s20MigrationPath = __DIR__.'/../database/migrations/0000_00_00_000002_create_organizational_access_grants.php';
$assert(is_file($s19MigrationPath), 'Sprint 20 requires the canonical Sprint 19 migration.');
$assert(is_file($s20MigrationPath), 'Sprint 20 canonical organizational access migration is missing.');
(require $s19MigrationPath)->up();
(require $s20MigrationPath)->up();

$schema = $s20Connection->getSchemaBuilder();
$assert($schema->hasTable('oneqay_outlet_access_grants'), 'Sprint 20 outlet access table is missing.');
$assert($schema->hasTable('oneqay_device_access_grants'), 'Sprint 20 device access table is missing.');

$graphRepository = new LaravelDurableContextGraphRepository($s20Connection, true, 'ci');
$transaction = new LaravelPersistenceTransaction($s20Connection, true, 'ci');
$accessRepository = new LaravelDurableOrganizationalAccessRepository($s20Connection, true, 'ci');
$accessService = new DurableOrganizationalAccessService($accessRepository, $transaction);

$alphaGraph = new DurableContextGraph(
    TenantId::fromString('tenant-alpha'),
    PlatformIdentityId::fromString('synthetic-principal-alpha'),
    OrganizationId::fromString('organization-shared'),
    OutletId::fromString('outlet-shared'),
    DeviceId::fromString('device-shared'),
);
$alphaSecondaryGraph = new DurableContextGraph(
    TenantId::fromString('tenant-alpha'),
    PlatformIdentityId::fromString('synthetic-principal-alpha'),
    OrganizationId::fromString('organization-shared'),
    OutletId::fromString('outlet-secondary'),
    DeviceId::fromString('device-secondary'),
);
$betaGraph = new DurableContextGraph(
    TenantId::fromString('tenant-beta'),
    PlatformIdentityId::fromString('synthetic-principal-beta'),
    OrganizationId::fromString('organization-shared'),
    OutletId::fromString('outlet-shared'),
    DeviceId::fromString('device-shared'),
);
$graphRepository->persist($alphaGraph);
$graphRepository->persist($alphaSecondaryGraph);
$graphRepository->persist($betaGraph);

$syntheticMemberships = new SyntheticTenantMembershipVerifier([
    'synthetic-principal-alpha' => ['tenant-alpha'],
    'synthetic-principal-beta' => ['tenant-beta'],
]);
$syntheticRelationships = new SyntheticOrganizationalRelationshipVerifier([
    'synthetic-principal-alpha' => [[
        'tenant' => 'tenant-alpha',
        'organization' => 'organization-shared',
        'outlet' => 'outlet-shared',
        'device' => 'device-shared',
    ]],
    'synthetic-principal-beta' => [[
        'tenant' => 'tenant-beta',
        'organization' => 'organization-shared',
        'outlet' => 'outlet-shared',
        'device' => 'device-shared',
    ]],
]);
$syntheticEnter = new EnterOrganizationalContext(
    new RequireVerifiedPlatformIdentity(),
    new RequireVerifiedTenantContext(),
    $syntheticMemberships,
    $syntheticRelationships,
    new RequestOrganizationalContextStore(),
);

$alphaTenant = $syntheticMemberships->verify('synthetic-principal-alpha', 'tenant-alpha');
$betaTenant = $syntheticMemberships->verify('synthetic-principal-beta', 'tenant-beta');
$assert($alphaTenant !== null && $betaTenant !== null, 'Sprint 20 synthetic verified tenant setup failed.');

$alphaVerified = $syntheticEnter->enter(
    new ServerVerifiedPlatformIdentity(PlatformIdentityId::fromString('synthetic-principal-alpha')),
    $alphaTenant,
    'organization-shared',
    'outlet-shared',
    'device-shared',
);
$accessService->recordVerifiedContext($alphaVerified);
$accessService->recordVerifiedContext($alphaVerified);
$syntheticEnter->clear();

$assert(
    $s20Connection->table('oneqay_outlet_access_grants')
        ->where('tenant_id', 'tenant-alpha')
        ->where('identity_id', 'synthetic-principal-alpha')
        ->where('organization_id', 'organization-shared')
        ->where('outlet_id', 'outlet-shared')
        ->count() === 1,
    'Sprint 20 identical outlet access recording is not idempotent.',
);
$assert(
    $s20Connection->table('oneqay_device_access_grants')
        ->where('tenant_id', 'tenant-alpha')
        ->where('identity_id', 'synthetic-principal-alpha')
        ->where('organization_id', 'organization-shared')
        ->where('outlet_id', 'outlet-shared')
        ->where('device_id', 'device-shared')
        ->count() === 1,
    'Sprint 20 identical device access recording is not idempotent.',
);

$betaVerified = $syntheticEnter->enter(
    new ServerVerifiedPlatformIdentity(PlatformIdentityId::fromString('synthetic-principal-beta')),
    $betaTenant,
    'organization-shared',
    'outlet-shared',
    'device-shared',
);
$accessService->recordVerifiedContext($betaVerified);
$syntheticEnter->clear();

$durableTenantVerifier = new LaravelTenantMembershipVerifier($accessRepository);
$durableRelationshipVerifier = new LaravelOrganizationalRelationshipVerifier($accessRepository);

$alphaDurableTenant = $durableTenantVerifier->verify('synthetic-principal-alpha', 'tenant-alpha');
$assert($alphaDurableTenant !== null, 'Sprint 20 durable tenant membership positive control failed.');
$assert(
    $durableTenantVerifier->verify('synthetic-principal-alpha', 'tenant-beta') === null,
    'Sprint 20 durable tenant verifier crossed tenant membership scope.',
);
$assert(
    $durableTenantVerifier->verify('synthetic.principal.alpha', 'tenant-alpha') === null,
    'Sprint 20 durable tenant verifier accepted malformed identity.',
);

$alphaIdentity = PlatformIdentityId::fromString('synthetic-principal-alpha');
$alphaTenantId = TenantId::fromString('tenant-alpha');
$sharedOrganization = OrganizationId::fromString('organization-shared');
$sharedOutlet = OutletId::fromString('outlet-shared');
$sharedDevice = DeviceId::fromString('device-shared');
$secondaryOutlet = OutletId::fromString('outlet-secondary');
$secondaryDevice = DeviceId::fromString('device-secondary');

$assert(
    $durableRelationshipVerifier->verify($alphaIdentity, $alphaTenantId, $sharedOrganization, null, null),
    'Sprint 20 organization-level durable membership must verify.',
);
$assert(
    $durableRelationshipVerifier->verify($alphaIdentity, $alphaTenantId, $sharedOrganization, $sharedOutlet, null),
    'Sprint 20 granted outlet access must verify.',
);
$assert(
    $durableRelationshipVerifier->verify($alphaIdentity, $alphaTenantId, $sharedOrganization, $sharedOutlet, $sharedDevice),
    'Sprint 20 granted device access must verify.',
);
$assert(
    ! $durableRelationshipVerifier->verify($alphaIdentity, $alphaTenantId, $sharedOrganization, $secondaryOutlet, null),
    'Sprint 20 ungranted outlet became authorized merely because it exists.',
);
$assert(
    ! $durableRelationshipVerifier->verify($alphaIdentity, $alphaTenantId, $sharedOrganization, $secondaryOutlet, $secondaryDevice),
    'Sprint 20 ungranted device became authorized merely because it exists.',
);
$assert(
    ! $durableRelationshipVerifier->verify($alphaIdentity, $alphaTenantId, $sharedOrganization, null, $sharedDevice),
    'Sprint 20 durable verifier accepted device scope without outlet scope.',
);
$assert(
    ! $durableRelationshipVerifier->verify(
        $alphaIdentity,
        TenantId::fromString('tenant-beta'),
        $sharedOrganization,
        $sharedOutlet,
        $sharedDevice,
    ),
    'Sprint 20 durable relationship verifier crossed tenant scope.',
);

$betaIdentity = PlatformIdentityId::fromString('synthetic-principal-beta');
$assert(
    $durableRelationshipVerifier->verify(
        $betaIdentity,
        TenantId::fromString('tenant-beta'),
        $sharedOrganization,
        $sharedOutlet,
        $sharedDevice,
    ),
    'Sprint 20 same organization/outlet/device identifiers must remain independently grantable under tenant-beta.',
);

try {
    $accessRepository->record(new DurableOrganizationalAccessGrant(
        TenantId::fromString('tenant-alpha'),
        PlatformIdentityId::fromString('synthetic-principal-missing'),
        $sharedOrganization,
        $sharedOutlet,
        $sharedDevice,
    ));
    $assert(false, 'Sprint 20 durable access accepted a grant without organization membership.');
} catch (DurableOrganizationalAccessViolation $exception) {
    $assert(
        $exception->errorCode === DurableOrganizationalAccessViolation::MEMBERSHIP_REQUIRED,
        'Sprint 20 missing-membership grant returned an unexpected error.',
    );
}

$accessSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Access/LaravelDurableOrganizationalAccessRepository.php');
$tenantVerifierSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Tenancy/LaravelTenantMembershipVerifier.php');
$relationshipVerifierSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Organization/LaravelOrganizationalRelationshipVerifier.php');
$migrationSource = (string) file_get_contents($s20MigrationPath);

foreach ([
    "oneqay_outlet_access_grants",
    "oneqay_device_access_grants",
    "fk_outlet_access_membership",
    "fk_device_access_outlet_grant",
    "fk_device_access_device",
    "Forward-only generated migration; rollback is not authorized.",
] as $requiredBoundary) {
    $assert(str_contains($migrationSource, $requiredBoundary), 'Sprint 20 migration boundary missing: '.$requiredBoundary);
}
$assert(
    substr_count($accessSource, "->where('tenant_id',") >= 4,
    'Sprint 20 durable access repository lost explicit tenant-scoped predicates.',
);
$assert(
    ! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $accessSource),
    'Sprint 20 durable access repository introduced ownership-rewriting upsert behavior.',
);
foreach ([$accessSource, $tenantVerifierSource, $relationshipVerifierSource] as $source) {
    $assert(
        str_contains($source, "['local', 'test', 'ci']") || ! str_contains($source, 'runtimeClass'),
        'Sprint 20 durable access runtime boundary changed unexpectedly.',
    );
}

$s20DatabaseManager->disconnect('s20_sqlite');
$s20DatabaseManager->purge('s20_sqlite');
$app['config']->set('database.oneqay_persistence_enabled', false);
$app['config']->set('database.connections.s20_sqlite', null);
@unlink($s20DatabasePath);
$removeS20Tree($s20Parent);
$assert(! file_exists($s20Parent), 'Sprint 20 durable access workspace cleanup failed.');
