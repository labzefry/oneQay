<?php

declare(strict_types=1);

use App\Application\Persistence\DurableContextGraph;
use App\Application\Persistence\DurableContextGraphRepository;
use App\Application\Persistence\DurableContextGraphService;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Persistence\LaravelDurableContextGraphRepository;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use App\Infrastructure\Tenancy\ServerVerifiedTenantContext;
use Illuminate\Database\QueryException;

// Author by Lab | zefry
if (! isset($app, $assert) || ! is_callable($assert)) {
    throw new RuntimeException('Sprint 19 persistence regression requires the M7.1 application harness.');
}

$assert(
    config('database.oneqay_persistence_enabled') === false,
    'Sprint 19 durable persistence must be disabled by default.',
);
$assert(extension_loaded('pdo_sqlite'), 'Sprint 19 persistence regression requires pdo_sqlite in CI.');

$removeS19Tree = null;
$removeS19Tree = static function (string $path) use (&$removeS19Tree): void {
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (! is_dir($path)) {
        return;
    }

    $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $item) {
        $removeS19Tree($item->getPathname());
    }
    @rmdir($path);
};

$s19Parent = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s19-app-'.getmypid();
$removeS19Tree($s19Parent);
$assert(@mkdir($s19Parent, 0700, false), 'Sprint 19 persistence workspace could not be created.');
$s19DatabasePath = $s19Parent.DIRECTORY_SEPARATOR.'durable.sqlite';
$assert(touch($s19DatabasePath), 'Sprint 19 disposable durable SQLite file could not be created.');

$app['config']->set('database.default', 's19_sqlite');
$app['config']->set('database.connections.s19_sqlite', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $s19DatabasePath,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);
$app['config']->set('oneqay.runtime_class', 'ci');

/** @var \Illuminate\Database\DatabaseManager $s19DatabaseManager */
$s19DatabaseManager = $app->make('db');
$s19DatabaseManager->purge('s19_sqlite');
$s19DatabaseManager->setDefaultConnection('s19_sqlite');
$s19Connection = $s19DatabaseManager->connection('s19_sqlite');
$s19Connection->getPdo();

$alphaGraph = new DurableContextGraph(
    TenantId::fromString('tenant-alpha'),
    PlatformIdentityId::fromString('synthetic-principal-shared'),
    OrganizationId::fromString('organization-shared'),
    OutletId::fromString('outlet-shared'),
    DeviceId::fromString('device-shared'),
);
$alphaContext = new ServerVerifiedTenantContext($alphaGraph->tenantId);

$disabledTransaction = new LaravelPersistenceTransaction($s19Connection, false, 'ci');
try {
    $disabledTransaction->run(static fn (): null => null);
    $assert(false, 'Sprint 19 disabled persistence transaction was accepted.');
} catch (DurablePersistenceViolation $exception) {
    $assert(
        $exception->errorCode === DurablePersistenceViolation::PERSISTENCE_DISABLED,
        'Sprint 19 disabled persistence returned an unexpected error.',
    );
}

$previewRepository = new LaravelDurableContextGraphRepository($s19Connection, true, 'preview');
try {
    $previewRepository->persist($alphaGraph);
    $assert(false, 'Sprint 19 Preview persistence was accepted.');
} catch (DurablePersistenceViolation $exception) {
    $assert(
        $exception->errorCode === DurablePersistenceViolation::RUNTIME_DENIED,
        'Sprint 19 Preview persistence returned an unexpected error.',
    );
}
$assert(
    $s19Connection->table('sqlite_master')->where('type', 'table')->where('name', 'oneqay_tenants')->doesntExist(),
    'Sprint 19 runtime denial must occur before schema/persistence mutation.',
);

$canonicalMigrationPath = __DIR__.'/../database/migrations/0000_00_00_000001_create_foundational_context_graph.php';
$assert(is_file($canonicalMigrationPath), 'Sprint 19 canonical foundational migration is missing.');
$canonicalMigration = require $canonicalMigrationPath;
$assert(
    $canonicalMigration instanceof \Illuminate\Database\Migrations\Migration,
    'Sprint 19 canonical migration must return a Laravel migration.',
);
$canonicalMigration->up();

$schema = $s19Connection->getSchemaBuilder();
foreach ([
    'oneqay_tenants',
    'oneqay_identities',
    'oneqay_organizations',
    'oneqay_identity_organizations',
    'oneqay_outlets',
    'oneqay_devices',
] as $tableName) {
    $assert($schema->hasTable($tableName), 'Sprint 19 foundational table is missing: '.$tableName);
}

$app['config']->set('database.oneqay_persistence_enabled', true);
/** @var DurableContextGraphRepository $s19Repository */
$s19Repository = $app->make(DurableContextGraphRepository::class);
/** @var PersistenceTransaction $s19Transaction */
$s19Transaction = $app->make(PersistenceTransaction::class);
$s19Service = new DurableContextGraphService(
    new RequireVerifiedTenantContext(),
    $s19Repository,
    $s19Transaction,
);

$s19Service->persist($alphaContext, $alphaGraph);
$alphaPersisted = $s19Service->findForVerifiedTenant(
    $alphaContext,
    $alphaGraph->identityId,
    $alphaGraph->deviceId,
);
$assert($alphaPersisted instanceof DurableContextGraph, 'Sprint 19 tenant-alpha durable graph was not found.');
$assert($alphaPersisted->equals($alphaGraph), 'Sprint 19 tenant-alpha durable graph changed after readback.');

$s19Service->persist($alphaContext, $alphaGraph);
$assert(
    $s19Connection->table('oneqay_devices')->where('tenant_id', 'tenant-alpha')->where('id', 'device-shared')->count() === 1,
    'Sprint 19 identical graph persistence is not idempotent.',
);

$betaGraph = new DurableContextGraph(
    TenantId::fromString('tenant-beta'),
    PlatformIdentityId::fromString('synthetic-principal-shared'),
    OrganizationId::fromString('organization-shared'),
    OutletId::fromString('outlet-shared'),
    DeviceId::fromString('device-shared'),
);
$betaContext = new ServerVerifiedTenantContext($betaGraph->tenantId);
$s19Service->persist($betaContext, $betaGraph);

$assert(
    $s19Connection->table('oneqay_devices')->where('id', 'device-shared')->count() === 2,
    'Sprint 19 tenant-owned identifier must be reusable across different tenants.',
);
$alphaStillScoped = $s19Service->findForVerifiedTenant(
    $alphaContext,
    $alphaGraph->identityId,
    $alphaGraph->deviceId,
);
$betaScoped = $s19Service->findForVerifiedTenant(
    $betaContext,
    $betaGraph->identityId,
    $betaGraph->deviceId,
);
$assert($alphaStillScoped?->tenantId->value() === 'tenant-alpha', 'Sprint 19 tenant-alpha read crossed tenant scope.');
$assert($betaScoped?->tenantId->value() === 'tenant-beta', 'Sprint 19 tenant-beta read crossed tenant scope.');

$betaOnlyGraph = new DurableContextGraph(
    TenantId::fromString('tenant-beta'),
    PlatformIdentityId::fromString('synthetic-principal-beta-only'),
    OrganizationId::fromString('organization-beta-only'),
    OutletId::fromString('outlet-beta-only'),
    DeviceId::fromString('device-beta-only'),
);
$s19Service->persist($betaContext, $betaOnlyGraph);
$assert(
    $s19Service->findForVerifiedTenant(
        $alphaContext,
        $betaOnlyGraph->identityId,
        $betaOnlyGraph->deviceId,
    ) === null,
    'Sprint 19 tenant-alpha read exposed tenant-beta-only durable graph.',
);

$gammaGraph = new DurableContextGraph(
    TenantId::fromString('tenant-gamma'),
    PlatformIdentityId::fromString('synthetic-principal-gamma'),
    OrganizationId::fromString('organization-gamma'),
    OutletId::fromString('outlet-gamma'),
    DeviceId::fromString('device-gamma'),
);
try {
    $s19Service->persist($alphaContext, $gammaGraph);
    $assert(false, 'Sprint 19 verified tenant-alpha context persisted tenant-gamma graph.');
} catch (DurablePersistenceViolation $exception) {
    $assert(
        $exception->errorCode === DurablePersistenceViolation::TENANT_CONTEXT_MISMATCH,
        'Sprint 19 cross-tenant service write returned an unexpected error.',
    );
}
$assert(
    $s19Connection->table('oneqay_tenants')->where('id', 'tenant-gamma')->doesntExist(),
    'Sprint 19 cross-tenant service denial occurred after mutation.',
);

$conflictGraph = new DurableContextGraph(
    $alphaGraph->tenantId,
    $alphaGraph->identityId,
    OrganizationId::fromString('organization-conflict'),
    $alphaGraph->outletId,
    $alphaGraph->deviceId,
);
try {
    $s19Service->persist($alphaContext, $conflictGraph);
    $assert(false, 'Sprint 19 silently rewrote an existing outlet/device parent relationship.');
} catch (DurablePersistenceViolation $exception) {
    $assert(
        $exception->errorCode === DurablePersistenceViolation::RELATIONSHIP_CONFLICT,
        'Sprint 19 relationship conflict returned an unexpected error.',
    );
}
$assert(
    $s19Connection->table('oneqay_organizations')
        ->where('tenant_id', 'tenant-alpha')
        ->where('id', 'organization-conflict')
        ->doesntExist(),
    'Sprint 19 relationship-conflict transaction left a partial organization row.',
);

try {
    $s19Transaction->run(function () use ($s19Connection): void {
        $s19Connection->table('oneqay_tenants')->insert(['id' => 'tenant-rollback']);
        throw new RuntimeException('synthetic-s19-transaction-failure');
    });
    $assert(false, 'Sprint 19 deliberate transaction failure was accepted.');
} catch (DurablePersistenceViolation $exception) {
    $assert(
        $exception->errorCode === DurablePersistenceViolation::TRANSACTION_FAILURE,
        'Sprint 19 transaction failure returned an unexpected error.',
    );
}
$assert(
    $s19Connection->table('oneqay_tenants')->where('id', 'tenant-rollback')->doesntExist(),
    'Sprint 19 failed transaction left partial durable state.',
);

try {
    $s19Connection->table('oneqay_outlets')->insert([
        'tenant_id' => 'tenant-alpha',
        'id' => 'outlet-cross-tenant-denied',
        'organization_id' => 'organization-beta-only',
    ]);
    $assert(false, 'Sprint 19 database accepted a cross-tenant parent relationship.');
} catch (QueryException) {
    // Expected: composite tenant-aware foreign key rejects the relationship.
}
$assert(
    $s19Connection->table('oneqay_outlets')
        ->where('tenant_id', 'tenant-alpha')
        ->where('id', 'outlet-cross-tenant-denied')
        ->doesntExist(),
    'Sprint 19 cross-tenant database denial left a row behind.',
);

$migrationSource = (string) file_get_contents($canonicalMigrationPath);
$repositorySource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Persistence/LaravelDurableContextGraphRepository.php');
foreach ([
    "primary(['tenant_id', 'id']",
    "foreign(['tenant_id', 'organization_id']",
    "foreign(['tenant_id', 'outlet_id']",
    "Forward-only generated migration; rollback is not authorized.",
] as $requiredMigrationBoundary) {
    $assert(
        str_contains($migrationSource, $requiredMigrationBoundary),
        'Sprint 19 migration lost a tenant-aware or forward-only boundary.',
    );
}
$assert(
    substr_count($repositorySource, "->where('tenant_id', \$tenant)") >= 4,
    'Sprint 19 repository lost explicit tenant-scoped read predicates.',
);
$assert(
    ! preg_match('/\b(updateOrInsert|upsert)\s*\(/', $repositorySource),
    'Sprint 19 repository introduced relationship-rewriting upsert behavior.',
);

$s19DatabaseManager->disconnect('s19_sqlite');
$s19DatabaseManager->purge('s19_sqlite');
$app['config']->set('database.oneqay_persistence_enabled', false);
$app['config']->set('database.connections.s19_sqlite', null);
@unlink($s19DatabasePath);
$removeS19Tree($s19Parent);
$assert(! file_exists($s19Parent), 'Sprint 19 durable persistence workspace cleanup failed.');
