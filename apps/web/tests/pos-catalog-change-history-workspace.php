<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\DurableRolePermissionRepository;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ViewPosCatalogChangeHistoryWorkspace;
use App\Application\Pos\ViewPosOperationsHub;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Pos\LaravelPosCatalogChangeHistoryWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('a', 32));
foreach ([
    'APP_ENV' => 'testing',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $console */
$console = $app->make(Kernel::class);
$console->bootstrap();

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$databasePath = sys_get_temp_dir().'/oneqay-s169-catalog-history-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint169 disposable database could not be created.');
$app['config']->set('database.default', 's169_catalog_history');
$app['config']->set('database.connections.s169_catalog_history', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => false,
]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s169_catalog_history');
$connection = $databaseManager->connection('s169_catalog_history');

$connection->statement('CREATE TABLE oneqay_pos_catalog_preparation_journal (tenant_id TEXT NOT NULL, mutation_id TEXT NOT NULL, operation_id TEXT NOT NULL, payload_fingerprint TEXT NOT NULL, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, product_id TEXT NOT NULL, before_exists INTEGER NOT NULL, before_display_name TEXT NULL, before_unit_price_atomic INTEGER NULL, before_currency TEXT NULL, before_currency_scale INTEGER NULL, before_sellable INTEGER NULL, after_display_name TEXT NOT NULL, after_unit_price_atomic INTEGER NOT NULL, after_currency TEXT NOT NULL, after_currency_scale INTEGER NOT NULL, after_sellable INTEGER NOT NULL, correlation_id TEXT NOT NULL, occurred_at_unix INTEGER NOT NULL)');

$journalRow = static function (
    string $seed,
    string $tenant,
    string $organization,
    string $outlet,
    string $device,
    string $product,
    int $occurredAt,
    bool $beforeExists,
    ?string $beforeName,
    ?int $beforePrice,
    ?string $beforeCurrency,
    ?int $beforeScale,
    ?bool $beforeSellable,
    string $afterName,
    int $afterPrice,
    string $afterCurrency = 'IDR',
    int $afterScale = 0,
    bool $afterSellable = true,
): array {
    return [
        'tenant_id' => $tenant,
        'mutation_id' => substr(hash('sha256', 'mutation-'.$seed), 0, 32),
        'operation_id' => 'operation-'.$seed,
        'payload_fingerprint' => hash('sha256', 'payload-'.$seed),
        'actor_identity_id' => 'identity-'.$seed,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
        'device_id' => $device,
        'product_id' => $product,
        'before_exists' => $beforeExists ? 1 : 0,
        'before_display_name' => $beforeName,
        'before_unit_price_atomic' => $beforePrice,
        'before_currency' => $beforeCurrency,
        'before_currency_scale' => $beforeScale,
        'before_sellable' => $beforeSellable === null ? null : ($beforeSellable ? 1 : 0),
        'after_display_name' => $afterName,
        'after_unit_price_atomic' => $afterPrice,
        'after_currency' => $afterCurrency,
        'after_currency_scale' => $afterScale,
        'after_sellable' => $afterSellable ? 1 : 0,
        'correlation_id' => 'correlation-'.$seed,
        'occurred_at_unix' => $occurredAt,
    ];
};

$bulk = [];
for ($i = 0; $i < 199; $i++) {
    $bulk[] = $journalRow(
        'bulk-'.$i,
        'tenant-a',
        'org-a',
        'outlet-a',
        $i % 2 === 0 ? 'device-b' : 'device-c',
        'bulk-product-'.$i,
        1789400000 + $i,
        false,
        null,
        null,
        null,
        null,
        null,
        'Bulk Product '.$i,
        1000 + $i,
    );
}
$connection->table('oneqay_pos_catalog_preparation_journal')->insert($bulk);

$createRow = $journalRow('create-main', 'tenant-a', 'org-a', 'outlet-a', 'device-b', 'product-main', 1789500000, false, null, null, null, null, null, 'Product Main', 12500, 'IDR', 0, true);
$updateRow = $journalRow('update-main', 'tenant-a', 'org-a', 'outlet-a', 'device-c', 'product-main', 1789500100, true, 'Product Main', 12500, 'IDR', 0, true, 'Product Main Premium', 15000, 'IDR', 0, false);
$connection->table('oneqay_pos_catalog_preparation_journal')->insert([$createRow, $updateRow]);
$connection->table('oneqay_pos_catalog_preparation_journal')->insert([
    $journalRow('foreign-outlet', 'tenant-a', 'org-a', 'outlet-b', 'device-z', 'foreign-outlet', 1789600000, false, null, null, null, null, null, 'Foreign Outlet', 9000),
    $journalRow('foreign-org', 'tenant-a', 'org-b', 'outlet-a', 'device-z', 'foreign-org', 1789600001, false, null, null, null, null, null, 'Foreign Org', 9000),
    $journalRow('foreign-tenant', 'tenant-b', 'org-a', 'outlet-a', 'device-z', 'foreign-tenant', 1789600002, false, null, null, null, null, null, 'Foreign Tenant', 9000),
]);

$verified = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('device-a'),
);
$context = PosExecutionContext::fromVerified($verified);
$repository = new LaravelPosCatalogChangeHistoryWorkspaceRepository($connection, true, 'ci', true, true);
$snapshot = $repository->read($context);

$assert($snapshot->tenantId() === 'tenant-a' && $snapshot->organizationId() === 'org-a', 'Sprint169 changed tenant/organization scope.');
$assert($snapshot->outletId() === 'outlet-a' && $snapshot->deviceId() === 'device-a', 'Sprint169 changed outlet/requester-device scope.');
$assert($snapshot->truncated(), 'Sprint169 must signal when more than 200 scoped journal rows exist.');
$assert(count($snapshot->changes()) === 200, 'Sprint169 bounded response must expose exactly the newest 200 scoped changes.');

$mainChanges = array_values(array_filter($snapshot->changes(), static fn (array $change): bool => $change['product_id'] === 'product-main'));
$assert(count($mainChanges) === 2, 'Sprint169 lost canonical create/update evidence for the target product.');
$assert($mainChanges[0]['change_type'] === 'UPDATE' && $mainChanges[0]['device_id'] === 'device-c', 'Sprint169 must order newest evidence first and preserve cross-device provenance.');
$assert($mainChanges[0]['before']['display_name'] === 'Product Main' && $mainChanges[0]['before']['unit_price_atomic'] === '12500', 'Sprint169 UPDATE before-state changed.');
$assert($mainChanges[0]['after']['display_name'] === 'Product Main Premium' && $mainChanges[0]['after']['unit_price_atomic'] === '15000' && $mainChanges[0]['after']['sellable'] === false, 'Sprint169 UPDATE after-state changed.');
$assert($mainChanges[1]['change_type'] === 'CREATE' && $mainChanges[1]['before'] === null && $mainChanges[1]['device_id'] === 'device-b', 'Sprint169 CREATE semantics or cross-device visibility changed.');
$assert(! in_array('foreign-outlet', array_column($snapshot->changes(), 'product_id'), true), 'Sprint169 leaked foreign outlet evidence.');
$assert(! in_array('foreign-org', array_column($snapshot->changes(), 'product_id'), true), 'Sprint169 leaked foreign organization evidence.');
$assert(! in_array('foreign-tenant', array_column($snapshot->changes(), 'product_id'), true), 'Sprint169 leaked foreign tenant evidence.');

$store = new class($verified) implements OrganizationalContextStore {
    public function __construct(private ?VerifiedOrganizationalContext $context) {}
    public function current(): ?VerifiedOrganizationalContext { return $this->context; }
    public function setVerified(VerifiedOrganizationalContext $context): void { $this->context = $context; }
    public function clear(): void { $this->context = null; }
};

$authorizationFor = static function (array $grants): DurableScopedAuthorizationPolicy {
    $authorizationRepository = new class($grants) implements DurableRolePermissionRepository {
        /** @param list<string> $grants */
        public function __construct(private array $grants) {}
        public function allows(VerifiedOrganizationalContext $context, PermissionIdentifier $permission): bool
        {
            return in_array($permission->value(), $this->grants, true);
        }
    };
    return new DurableScopedAuthorizationPolicy($authorizationRepository);
};

$allowedView = new ViewPosCatalogChangeHistoryWorkspace($repository, $store, $authorizationFor(['pos.catalog.prepare']));
$assert($allowedView->view()->outletId() === 'outlet-a', 'Sprint169 existing catalog-prepare authority must allow catalog history.');
try {
    (new ViewPosCatalogChangeHistoryWorkspace($repository, $store, $authorizationFor(['pos.inventory.baseline'])))->view();
    $assert(false, 'Sprint169 inventory-baseline authority must not imply catalog history access.');
} catch (DurableAuthorizationViolation) {
    // Expected deny-by-default boundary.
}

$catalogOnlyHub = (new ViewPosOperationsHub($store, $authorizationFor(['pos.catalog.prepare'])))->view();
$assert($catalogOnlyHub->canCatalogChangeHistory(), 'Sprint169 Hub must expose catalog history for catalog-prepare authority.');
$assert(! $catalogOnlyHub->canCatalogInventorySetup(), 'Sprint169 catalog-prepare authority alone must not broaden catalog+inventory setup.');

foreach ([[false, 'ci', true, true], [true, 'production', true, true], [true, 'ci', false, true], [true, 'ci', true, false]] as [$persistence, $runtime, $workspace, $catalog]) {
    try {
        (new LaravelPosCatalogChangeHistoryWorkspaceRepository($connection, $persistence, $runtime, $workspace, $catalog))->read($context);
        $assert(false, 'Sprint169 workspace accepted a disallowed persistence/runtime/feature state.');
    } catch (PosTransactionViolation) {
        // Expected.
    }
}

$controllerSource = file_get_contents(__DIR__.'/../app/Delivery/Http/Pos/PosOperationsHubController.php');
$providerSource = file_get_contents(__DIR__.'/../app/Providers/PosCatalogChangeHistoryWorkspaceServiceProvider.php');
$assert(is_string($controllerSource) && str_contains($controllerSource, 'pos.catalog.history') && str_contains($controllerSource, 'Route::has($routeName)'), 'Sprint169 Hub route discovery lost catalog-history Route::has guard.');
$assert(is_string($providerSource) && str_contains($providerSource, "ONEQAY_POS_CATALOG_CHANGE_HISTORY_ENABLED") === false, 'Sprint169 provider should read the config key rather than environment state directly.');
$assert(str_contains($providerSource, "config('pos_catalog_change_history.enabled', false)"), 'Sprint169 provider lost default-false feature config gate.');

$connection->table('oneqay_pos_catalog_preparation_journal')
    ->where('tenant_id', 'tenant-a')
    ->where('mutation_id', $updateRow['mutation_id'])
    ->update(['before_exists' => 0]);
try {
    $repository->read($context);
    $assert(false, 'Sprint169 accepted CREATE-shaped evidence with non-null before-state.');
} catch (PosTransactionViolation) {
    // Expected fail-closed integrity boundary.
}

$databaseManager->disconnect('s169_catalog_history');
$databaseManager->purge('s169_catalog_history');
@unlink($databasePath);

fwrite(STDOUT, "Sprint169 POS catalog change history workspace regression passed.\n");
