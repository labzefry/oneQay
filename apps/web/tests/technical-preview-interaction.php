<?php

declare(strict_types=1);

use App\Application\Identity\RequireVerifiedPlatformIdentity;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Pos\CompleteSyntheticSale;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Preview\PreviewProfile;
use App\Application\Preview\TechnicalPreviewJourney;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Infrastructure\Organization\RequestOrganizationalContextStore;
use App\Infrastructure\Organization\SyntheticOrganizationalRelationshipVerifier;
use App\Infrastructure\Preview\DeterministicPreviewFixture;
use App\Infrastructure\Tenancy\RequestTenantContextStore;
use App\Infrastructure\Tenancy\SyntheticTenantMembershipVerifier;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("M7.4A regression failed: {$case}");
    }
};

$expect = static function (string $class, callable $callback, string $case) use ($assert): Throwable {
    try {
        $callback();
    } catch (Throwable $exception) {
        $assert($exception instanceof $class, $case.' throws '.$class);
        return $exception;
    }

    $assert(false, $case.' must throw');
    throw new RuntimeException('unreachable');
};

$fixtures = new DeterministicPreviewFixture();
$memberships = new SyntheticTenantMembershipVerifier([
    'synthetic-principal-a' => ['tenant-alpha'],
    'synthetic-principal-b' => ['tenant-beta'],
]);
$relationships = new SyntheticOrganizationalRelationshipVerifier([
    'synthetic-principal-a' => [[
        'tenant' => 'tenant-alpha',
        'organization' => 'organization-alpha',
        'outlet' => 'outlet-alpha',
        'device' => 'device-alpha',
    ]],
    'synthetic-principal-b' => [[
        'tenant' => 'tenant-beta',
        'organization' => 'organization-beta',
        'outlet' => 'outlet-beta',
        'device' => 'device-beta',
    ]],
]);
$tenantContexts = new RequestTenantContextStore();
$organizationalContexts = new RequestOrganizationalContextStore();
$organizations = new EnterOrganizationalContext(
    new RequireVerifiedPlatformIdentity(),
    new RequireVerifiedTenantContext(),
    $memberships,
    $relationships,
    $organizationalContexts,
);
$journey = new TechnicalPreviewJourney(
    $fixtures,
    $memberships,
    $tenantContexts,
    $organizations,
    new CompleteSyntheticSale($fixtures, $organizationalContexts),
);

$profiles = $journey->profiles();
$assert(count($profiles) === 2, 'M74A-AUTH-001 exactly two allowlisted demo identities');
$assert($journey->profile('synthetic-principal-a') !== null, 'M74A-AUTH-002 alpha identity allowlisted');
$assert($journey->profile('attacker-controlled') === null, 'M74A-AUTH-003 arbitrary identity denied');

$alpha = $journey->profile('synthetic-principal-a');
$beta = $journey->profile('synthetic-principal-b');
$assert($alpha instanceof PreviewProfile, 'M74A-CTX-001 alpha profile resolved');
$assert($beta instanceof PreviewProfile, 'M74A-CTX-002 beta profile resolved');

$alphaCatalog = $journey->catalog($alpha);
$betaCatalog = $journey->catalog($beta);
$assert(count($alphaCatalog) === 2, 'M74A-CAT-001 alpha catalog is deterministic');
$assert(count($betaCatalog) === 2, 'M74A-CAT-002 beta catalog is deterministic');
foreach ($alphaCatalog as $item) {
    $assert($item->tenantId()->value() === 'tenant-alpha', 'M74A-ISO-001 alpha catalog tenant isolated');
    $assert($item->outletId()->value() === 'outlet-alpha', 'M74A-ISO-002 alpha catalog outlet isolated');
}
foreach ($betaCatalog as $item) {
    $assert($item->tenantId()->value() === 'tenant-beta', 'M74A-ISO-003 beta catalog tenant isolated');
    $assert($item->outletId()->value() === 'outlet-beta', 'M74A-ISO-004 beta catalog outlet isolated');
}
$assert($tenantContexts->current() === null, 'M74A-CTX-003 tenant context cleared after catalog');
$assert($organizationalContexts->current() === null, 'M74A-CTX-004 org context cleared after catalog');

$forged = new PreviewProfile(
    'synthetic-principal-a',
    'Forged',
    'tenant-beta',
    'organization-beta',
    'outlet-beta',
    'device-beta',
);
$expect(
    OrganizationalAccessViolation::class,
    static fn () => $journey->catalog($forged),
    'M74A-ISO-005 client-forged foreign context denied',
);

$cashReceipt = $journey->completeSale(
    $alpha,
    [
        ['product_id' => 'synthetic-product-alpha', 'quantity' => 2],
        ['product_id' => 'synthetic-product-secondary', 'quantity' => 2],
    ],
    'CASH',
    6000,
    'preview-op-alpha-0001',
    'preview-correlation-alpha-0001',
);
$assert($cashReceipt->tenantId() === 'tenant-alpha', 'M74A-SALE-001 receipt tenant server verified');
$assert($cashReceipt->actorId() === 'synthetic-principal-a', 'M74A-SALE-002 receipt actor server verified');
$assert($cashReceipt->total()->atomicUnits() === 5000, 'M74A-SALE-003 authoritative server total');
$assert($cashReceipt->changeAmount()->atomicUnits() === 1000, 'M74A-SALE-004 exact cash change');
$assert($cashReceipt->evidenceMode() === 'CASH_COUNTED', 'M74A-SALE-005 cash evidence mode');
$assert($cashReceipt->correlationId() === 'preview-correlation-alpha-0001', 'M74A-OBS-001 correlation preserved');

$replay = $journey->completeSale(
    $alpha,
    [
        ['product_id' => 'synthetic-product-alpha', 'quantity' => 2],
        ['product_id' => 'synthetic-product-secondary', 'quantity' => 2],
    ],
    'CASH',
    6000,
    'preview-op-alpha-0001',
    'preview-correlation-alpha-retry-0002',
);
$assert($replay->saleId() === $cashReceipt->saleId(), 'M74A-IDEM-001 stable operation replay returns same sale');

$manual = $journey->completeSale(
    $alpha,
    [['product_id' => 'synthetic-product-alpha', 'quantity' => 1]],
    'MANUAL_EXTERNAL',
    1999,
    'preview-op-alpha-manual-0002',
    'preview-correlation-alpha-manual-0003',
);
$assert($manual->evidenceMode() === 'OPERATOR_RECORDED', 'M74A-PAY-001 manual tender stays operator recorded');
$assert($manual->evidenceMode() !== 'PROVIDER_VERIFIED', 'M74A-PAY-002 no invented provider verification');

$expect(
    PosTransactionViolation::class,
    static fn () => $journey->completeSale(
        $beta,
        [['product_id' => 'synthetic-product-alpha', 'quantity' => 1]],
        'CASH',
        5000,
        'preview-op-beta-foreign-0003',
        'preview-correlation-beta-foreign-0004',
    ),
    'M74A-ISO-006 beta cannot transact alpha catalog item',
);

$expect(
    PosTransactionViolation::class,
    static fn () => $journey->completeSale(
        $beta,
        [['product_id' => 'synthetic-product-beta', 'quantity' => 1]],
        'CASH',
        1000,
        'preview-op-beta-underpay-0004',
        'preview-correlation-beta-underpay-0005',
    ),
    'M74A-PAY-003 insufficient tender safely rejected',
);

$assert($tenantContexts->current() === null, 'M74A-CTX-005 tenant context cleared after sale');
$assert($organizationalContexts->current() === null, 'M74A-CTX-006 org context cleared after sale');

echo "M7.4A Technical Preview interaction regression passed.\n";
