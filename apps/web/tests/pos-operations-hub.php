<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\DurableRolePermissionRepository;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\FinalShiftClosePermission;
use App\Application\Pos\ViewPosOperationsHub;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;

require __DIR__.'/../vendor/autoload.php';

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$context = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('identity-a'),
    TenantId::fromString('tenant-a'),
    OrganizationId::fromString('org-a'),
    OutletId::fromString('outlet-a'),
    DeviceId::fromString('device-a'),
);

$store = new class($context) implements OrganizationalContextStore {
    public function __construct(private ?VerifiedOrganizationalContext $context) {}
    public function current(): ?VerifiedOrganizationalContext { return $this->context; }
    public function setVerified(VerifiedOrganizationalContext $context): void { $this->context = $context; }
    public function clear(): void { $this->context = null; }
};

$viewFor = static function (array $grants) use ($store): ViewPosOperationsHub {
    $repository = new class($grants) implements DurableRolePermissionRepository {
        /** @param list<string> $grants */
        public function __construct(private array $grants) {}
        public function allows(VerifiedOrganizationalContext $context, PermissionIdentifier $permission): bool
        {
            return in_array($permission->value(), $this->grants, true);
        }
    };

    return new ViewPosOperationsHub($store, new DurableScopedAuthorizationPolicy($repository));
};

$all = $viewFor([
    'pos.shift.open',
    'pos.shift.opening-cash.record',
    'pos.sale.complete',
    'pos.reporting.sales-summary.view',
    'pos.sale.void',
    'pos.sale.refund',
    'pos.catalog.prepare',
    'pos.inventory.baseline',
    FinalShiftClosePermission::IDENTIFIER,
])->view();
$assert($all->tenantId() === 'tenant-a' && $all->organizationId() === 'org-a', 'Sprint162 hub changed verified tenant or organization scope.');
$assert($all->outletId() === 'outlet-a' && $all->deviceId() === 'device-a', 'Sprint162 hub changed verified outlet or device scope.');
$assert($all->canShiftStart() && $all->canCashier() && $all->canReporting(), 'Sprint162 hub failed existing shift/cashier/reporting grants.');
$assert($all->canCorrections() && $all->canCatalogInventorySetup() && $all->canShiftClose(), 'Sprint162 hub failed existing correction/setup/close grants.');

$cashierOnly = $viewFor(['pos.sale.complete'])->view();
$assert($cashierOnly->canCashier(), 'Sprint162 hub must expose cashier for existing complete-sale grant.');
$assert(! $cashierOnly->canShiftStart() && ! $cashierOnly->canReporting() && ! $cashierOnly->canCorrections(), 'Sprint162 hub broadened unrelated permission access.');
$assert(! $cashierOnly->canCatalogInventorySetup() && ! $cashierOnly->canShiftClose(), 'Sprint162 hub broadened setup or close access.');

$partialShift = $viewFor(['pos.sale.complete', 'pos.shift.open'])->view();
$assert(! $partialShift->canShiftStart(), 'Sprint162 shift-start navigation must require both open-shift and opening-cash grants.');

$partialSetup = $viewFor(['pos.sale.complete', 'pos.catalog.prepare'])->view();
$assert(! $partialSetup->canCatalogInventorySetup(), 'Sprint162 catalog/setup navigation must require both catalog and baseline grants.');

$voidOnly = $viewFor(['pos.sale.void'])->view();
$assert($voidOnly->canCorrections(), 'Sprint162 corrections navigation must preserve independently granted void authority.');
$assert(! $voidOnly->canCashier(), 'Sprint162 correction grant must not imply cashier access.');

$reportOnly = $viewFor(['pos.reporting.sales-summary.view'])->view();
$assert($reportOnly->canReporting() && ! $reportOnly->canCashier(), 'Sprint162 reporting access must remain independently scoped.');

$closeOnly = $viewFor([FinalShiftClosePermission::IDENTIFIER])->view();
$assert($closeOnly->canShiftClose() && ! $closeOnly->canCashier(), 'Sprint162 final-shift-close access must reuse only its canonical permission.');

try {
    $viewFor([])->view();
    $assert(false, 'Sprint162 hub allowed a context with no POS permission.');
} catch (DurableAuthorizationViolation) {
    // Expected deny-by-default boundary.
}

$controllerSource = file_get_contents(__DIR__.'/../app/Delivery/Http/Pos/PosOperationsHubController.php');
$assert(is_string($controllerSource), 'Sprint162 controller source could not be read.');
foreach ([
    'pos.catalog-inventory.setup',
    'pos.shift-start.workspace',
    'pos.cashier.workspace',
    'pos.reporting.sales-summary',
    'pos.reporting.sales-history',
    'pos.sales.corrections.workspace',
    'pos.shifts.close.page',
] as $routeName) {
    $assert(str_contains($controllerSource, $routeName), "Sprint162 hub lost canonical route {$routeName}.");
}
$assert(str_contains($controllerSource, 'Route::has($routeName)'), 'Sprint162 hub must filter every destination through delivered named-route existence.');

fwrite(STDOUT, "Sprint162 POS operations hub regression passed.\n");
