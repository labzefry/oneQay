<?php

declare(strict_types=1);

use App\Application\Identity\IdentityContextViolation;
use App\Application\Identity\RequireVerifiedPlatformIdentity;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Organization\OrganizationalAccessViolation;
use App\Application\Pos\CompleteSyntheticSale;
use App\Application\Pos\PosAccessViolation;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleCommand;
use App\Application\Tenancy\MissingTenantContext;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Outlet\OutletId;
use App\Domain\Pos\Cart;
use App\Domain\Pos\CartLine;
use App\Domain\Pos\CatalogItem;
use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use App\Domain\Pos\TenderCategory;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Identity\ServerVerifiedPlatformIdentity;
use App\Infrastructure\Organization\RequestOrganizationalContextStore;
use App\Infrastructure\Organization\SyntheticOrganizationalRelationshipVerifier;
use App\Infrastructure\Pos\InMemorySyntheticPosStore;
use App\Infrastructure\Tenancy\SyntheticTenantMembershipVerifier;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assertM74 = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("M7.4 regression failed: {$case}");
    }
};

$expectException = static function (string $class, callable $callback, string $case) use ($assertM74): Throwable {
    try {
        $callback();
    } catch (Throwable $exception) {
        $assertM74($exception instanceof $class, $case.' throws '.$class);

        return $exception;
    }

    $assertM74(false, $case.' must throw');
    throw new RuntimeException('unreachable');
};

$singleLineCart = static fn (string $productId, int $quantity): Cart => Cart::fromLines([
    new CartLine(ProductId::fromString($productId), $quantity),
]);

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
            'device' => 'device-alpha',
        ],
    ],
    'synthetic-principal-b' => [
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

$identityA = new ServerVerifiedPlatformIdentity(
    PlatformIdentityId::fromString('synthetic-principal-a'),
);
$identityB = new ServerVerifiedPlatformIdentity(
    PlatformIdentityId::fromString('synthetic-principal-b'),
);
$identityC = new ServerVerifiedPlatformIdentity(
    PlatformIdentityId::fromString('synthetic-principal-c'),
);

$alphaTenant = $memberships->verify('synthetic-principal-a', 'tenant-alpha');
$betaTenant = $memberships->verify('synthetic-principal-b', 'tenant-beta');
$assertM74($alphaTenant !== null, 'M74-CTRL-001 alpha verified tenant fixture');
$assertM74($betaTenant !== null, 'M74-CTRL-002 beta verified tenant fixture');

$contextStore = new RequestOrganizationalContextStore();
$enterContext = new EnterOrganizationalContext(
    new RequireVerifiedPlatformIdentity(),
    new RequireVerifiedTenantContext(),
    $memberships,
    $relationships,
    $contextStore,
);

$enterAlpha = static fn () => $enterContext->enter(
    $identityA,
    $alphaTenant,
    'organization-alpha',
    'outlet-alpha',
    'device-alpha',
);

$enterBeta = static fn () => $enterContext->enter(
    $identityB,
    $betaTenant,
    'organization-beta',
    'outlet-beta',
    'device-beta',
);

$enterAlpha();

$store = new InMemorySyntheticPosStore();

$store->seed(
    new CatalogItem(
        TenantId::fromString('tenant-alpha'),
        OutletId::fromString('outlet-alpha'),
        ProductId::fromString('synthetic-product-alpha'),
        'Synthetic Alpha Product',
        Money::fromAtomicUnits(1999, 'IDR', 0),
    ),
    10,
);
$store->seed(
    new CatalogItem(
        TenantId::fromString('tenant-alpha'),
        OutletId::fromString('outlet-alpha'),
        ProductId::fromString('synthetic-product-secondary'),
        'Synthetic Secondary Product',
        Money::fromAtomicUnits(501, 'IDR', 0),
    ),
    5,
);
$store->seed(
    new CatalogItem(
        TenantId::fromString('tenant-beta'),
        OutletId::fromString('outlet-beta'),
        ProductId::fromString('synthetic-product-beta'),
        'Synthetic Beta Product',
        Money::fromAtomicUnits(4900, 'IDR', 0),
    ),
    8,
);
$store->seed(
    new CatalogItem(
        TenantId::fromString('tenant-alpha'),
        OutletId::fromString('outlet-alpha'),
        ProductId::fromString('synthetic-product-collision'),
        'Synthetic Alpha Collision Product',
        Money::fromAtomicUnits(2500, 'IDR', 0),
    ),
    12,
);
$store->seed(
    new CatalogItem(
        TenantId::fromString('tenant-beta'),
        OutletId::fromString('outlet-beta'),
        ProductId::fromString('synthetic-product-collision'),
        'Synthetic Beta Collision Product',
        Money::fromAtomicUnits(3500, 'IDR', 0),
    ),
    12,
);

$service = new CompleteSyntheticSale($store, $contextStore);

$assertM74(
    Money::fromAtomicUnits(1999, 'IDR', 0)->multiply(3)->atomicUnits() === 5997,
    'M74-MONEY-001 exact integer multiplication',
);
$assertM74(
    Money::fromAtomicUnits(1234, 'BHD', 3)->scale() === 3,
    'M74-MONEY-002 explicit non-two scale is preserved',
);
$expectException(
    InvalidArgumentException::class,
    static fn () => Money::fromAtomicUnits(-1, 'IDR', 0),
    'M74-MONEY-003 negative money rejected',
);

$expectException(
    InvalidArgumentException::class,
    static fn () => Cart::fromLines([]),
    'M74-CART-001 empty cart rejected',
);
$expectException(
    InvalidArgumentException::class,
    static fn () => Cart::fromLines([
        new CartLine(ProductId::fromString('synthetic-product-alpha'), 1),
        new CartLine(ProductId::fromString('synthetic-product-alpha'), 2),
    ]),
    'M74-CART-002 duplicate product lines rejected',
);
$expectException(
    InvalidArgumentException::class,
    static fn () => new CartLine(ProductId::fromString('synthetic-product-alpha'), 0),
    'M74-CART-003 invalid quantity rejected',
);

$cashCart = Cart::fromLines([
    new CartLine(ProductId::fromString('synthetic-product-alpha'), 2),
    new CartLine(ProductId::fromString('synthetic-product-secondary'), 2),
]);
$cashCommand = new SaleCommand(
    'm74-op-alpha-cash-0001',
    $cashCart,
    TenderCategory::CASH,
    Money::fromAtomicUnits(6000, 'IDR', 0),
    'm74-correlation-alpha-0001',
);
$cashReceipt = $service->complete($cashCommand);
$assertM74($cashReceipt->tenantId() === 'tenant-alpha', 'M74-SALE-001 receipt tenant is verified alpha');
$assertM74($cashReceipt->actorId() === 'synthetic-principal-a', 'M74-SALE-001 receipt actor is verified identity');
$assertM74($cashReceipt->outletId() === 'outlet-alpha', 'M74-SALE-001 receipt outlet is verified');
$assertM74($cashReceipt->deviceId() === 'device-alpha', 'M74-SALE-001 receipt device is verified');
$assertM74(count($cashReceipt->lines()) === 2, 'M74-CART-004 receipt preserves two cart lines');
$assertM74($cashReceipt->lines()[0]->unitPrice()->atomicUnits() === 1999, 'M74-SALE-001 authoritative first catalog price');
$assertM74($cashReceipt->lines()[1]->unitPrice()->atomicUnits() === 501, 'M74-SALE-001 authoritative second catalog price');
$assertM74($cashReceipt->total()->atomicUnits() === 5000, 'M74-SALE-001 total derived server-side from catalog and cart');
$assertM74($cashReceipt->evidenceMode() === 'CASH_COUNTED', 'M74-SALE-001 cash evidence mode');
$assertM74($cashReceipt->appliedAmount()->atomicUnits() === 5000, 'M74-SALE-001 applied amount equals sale obligation');
$assertM74($cashReceipt->changeAmount()->atomicUnits() === 1000, 'M74-SALE-001 exact cash change');
$assertM74($store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha') === 8, 'M74-STOCK-001 first product stock decremented once');
$assertM74($store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-secondary') === 3, 'M74-STOCK-001 second product stock decremented once');
$assertM74($store->saleCount() === 1, 'M74-ATOMIC-001 one completed sale effect');
$assertM74($store->paymentEffectCount() === 1, 'M74-ATOMIC-001 one payment effect');

$replayReceipt = $service->complete(
    new SaleCommand(
        'm74-op-alpha-cash-0001',
        $cashCart,
        TenderCategory::CASH,
        Money::fromAtomicUnits(6000, 'IDR', 0),
        'm74-correlation-alpha-retry-0002',
    ),
);
$assertM74($replayReceipt->saleId() === $cashReceipt->saleId(), 'M74-IDEM-001 replay returns same sale identity');
$assertM74($store->saleCount() === 1, 'M74-IDEM-001 replay creates no duplicate sale');
$assertM74($store->paymentEffectCount() === 1, 'M74-IDEM-001 replay creates no duplicate payment effect');
$assertM74($store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha') === 8, 'M74-IDEM-001 replay creates no second first-product stock decrement');
$assertM74($store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-secondary') === 3, 'M74-IDEM-001 replay creates no second second-product stock decrement');

$beforeConflictSales = $store->saleCount();
$beforeConflictPayments = $store->paymentEffectCount();
$beforeConflictStock = $store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha');
$expectException(
    PosTransactionViolation::class,
    static fn () => $service->complete(
        new SaleCommand(
            'm74-op-alpha-cash-0001',
            $singleLineCart('synthetic-product-alpha', 1),
            TenderCategory::CASH,
            Money::fromAtomicUnits(6000, 'IDR', 0),
            'm74-correlation-alpha-conflict-0003',
        ),
    ),
    'M74-IDEM-002 conflicting idempotency payload rejected',
);
$assertM74($store->saleCount() === $beforeConflictSales, 'M74-IDEM-002 no sale mutation after conflict');
$assertM74($store->paymentEffectCount() === $beforeConflictPayments, 'M74-IDEM-002 no payment mutation after conflict');
$assertM74($store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha') === $beforeConflictStock, 'M74-IDEM-002 no stock mutation after conflict');

$manualReceipt = $service->complete(
    new SaleCommand(
        'm74-op-alpha-manual-0002',
        $singleLineCart('synthetic-product-alpha', 1),
        TenderCategory::MANUAL_EXTERNAL,
        Money::fromAtomicUnits(1999, 'IDR', 0),
        'm74-correlation-alpha-manual-0004',
    ),
);
$assertM74($manualReceipt->evidenceMode() === 'OPERATOR_RECORDED', 'M74-PAY-001 manual tender is operator-recorded');
$assertM74($manualReceipt->evidenceMode() !== 'PROVIDER_VERIFIED', 'M74-PAY-001 manual tender is never provider verified');
$assertM74($manualReceipt->changeAmount()->atomicUnits() === 0, 'M74-PAY-001 manual tender has no invented cash change');

$beforeCurrencySales = $store->saleCount();
$beforeCurrencyPayments = $store->paymentEffectCount();
$beforeCurrencyStock = $store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha');
$currencyException = $expectException(
    PosTransactionViolation::class,
    static fn () => $service->complete(
        new SaleCommand(
            'm74-op-alpha-currency-0003',
            $singleLineCart('synthetic-product-alpha', 1),
            TenderCategory::CASH,
            Money::fromAtomicUnits(5000, 'USD', 2),
            'm74-correlation-alpha-currency-0005',
        ),
    ),
    'M74-MONEY-004 currency mismatch rejected',
);
$assertM74($currencyException->getMessage() === 'POS transaction rejected.', 'M74-MONEY-004 safe generic failure');
$assertM74($store->saleCount() === $beforeCurrencySales, 'M74-MONEY-004 no partial sale');
$assertM74($store->paymentEffectCount() === $beforeCurrencyPayments, 'M74-MONEY-004 no partial payment');
$assertM74($store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha') === $beforeCurrencyStock, 'M74-MONEY-004 no partial stock mutation');

$expectException(
    IdentityContextViolation::class,
    static fn () => $enterContext->enter(null, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-alpha'),
    'M74-AUTH-001 missing identity rejected',
);
$expectException(
    MissingTenantContext::class,
    static fn () => $enterContext->enter($identityA, null, 'organization-alpha', 'outlet-alpha', 'device-alpha'),
    'M74-AUTH-002 missing tenant rejected',
);
$membershipException = $expectException(
    OrganizationalAccessViolation::class,
    static fn () => $enterContext->enter($identityC, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-alpha'),
    'M74-AUTH-003 missing membership rejected',
);
$assertM74($membershipException->getMessage() === 'Organizational context denied.', 'M74-AUTH-003 generic membership denial');

$enterContext->enter($identityA, $alphaTenant, 'organization-alpha');
$expectException(
    PosAccessViolation::class,
    static fn () => $service->complete(
        new SaleCommand(
            'm74-op-missing-org-0004',
            $singleLineCart('synthetic-product-alpha', 1),
            TenderCategory::CASH,
            Money::fromAtomicUnits(2000, 'IDR', 0),
            'm74-correlation-missing-org-0006',
        ),
    ),
    'M74-AUTH-004 missing required organizational context rejected',
);

$foreignTenantException = $expectException(
    OrganizationalAccessViolation::class,
    static fn () => $enterContext->enter($identityB, $alphaTenant, 'organization-alpha', 'outlet-alpha', 'device-alpha'),
    'M74-AUTH-005 foreign tenant identity rejected',
);
$assertM74(! str_contains($foreignTenantException->getMessage(), 'tenant-alpha'), 'M74-AUTH-005 denial leaks no target tenant');

foreach ([
    ['organization-beta', 'outlet-beta', 'device-beta'],
    ['organization-alpha', 'outlet-beta', 'device-beta'],
    ['organization-alpha', 'outlet-alpha', 'device-beta'],
] as [$organization, $outlet, $device]) {
    $foreignContextException = $expectException(
        OrganizationalAccessViolation::class,
        static fn () => $enterContext->enter($identityA, $alphaTenant, $organization, $outlet, $device),
        'M74-AUTH-006 foreign organizational context rejected',
    );
    $assertM74(
        ! str_contains($foreignContextException->getMessage(), 'tenant-beta')
        && ! str_contains($foreignContextException->getMessage(), 'outlet-beta')
        && ! str_contains($foreignContextException->getMessage(), 'device-beta'),
        'M74-AUTH-006 foreign context not leaked',
    );
}

$expectException(
    PosAccessViolation::class,
    static fn () => $service->complete(
        new SaleCommand(
            'm74-op-stale-context-0010',
            $singleLineCart('synthetic-product-alpha', 1),
            TenderCategory::CASH,
            Money::fromAtomicUnits(2000, 'IDR', 0),
            'm74-correlation-stale-context-0013',
        ),
    ),
    'M74-AUTH-007 failed request cannot inherit stale context',
);

$enterAlpha();
$beforeForeignProductSales = $store->saleCount();
$foreignProductException = $expectException(
    PosTransactionViolation::class,
    static fn () => $service->complete(
        new SaleCommand(
            'm74-op-foreign-product-0005',
            $singleLineCart('synthetic-product-beta', 1),
            TenderCategory::CASH,
            Money::fromAtomicUnits(5000, 'IDR', 0),
            'm74-correlation-foreign-product-0007',
        ),
    ),
    'M74-CAT-001 foreign product rejected',
);
$assertM74($foreignProductException->getMessage() === 'POS transaction rejected.', 'M74-CAT-001 generic foreign-product denial');
$assertM74(! str_contains($foreignProductException->getMessage(), 'beta'), 'M74-CAT-001 foreign payload not leaked');
$assertM74($store->saleCount() === $beforeForeignProductSales, 'M74-CAT-001 foreign product creates no sale');

$beforePaymentSales = $store->saleCount();
$beforePaymentEffects = $store->paymentEffectCount();
$beforePaymentStock = $store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha');
$expectException(
    PosTransactionViolation::class,
    static fn () => $service->complete(
        new SaleCommand(
            'm74-op-insufficient-payment-0007',
            $singleLineCart('synthetic-product-alpha', 1),
            TenderCategory::CASH,
            Money::fromAtomicUnits(1000, 'IDR', 0),
            'm74-correlation-insufficient-pay-0009',
        ),
    ),
    'M74-PAY-002 insufficient payment rejected',
);
$assertM74($store->saleCount() === $beforePaymentSales, 'M74-PAY-002 no partial sale');
$assertM74($store->paymentEffectCount() === $beforePaymentEffects, 'M74-PAY-002 no partial payment');
$assertM74($store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha') === $beforePaymentStock, 'M74-PAY-002 no partial stock');

$beforeStockSales = $store->saleCount();
$beforeStockPayments = $store->paymentEffectCount();
$beforeStockValue = $store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha');
$expectException(
    PosTransactionViolation::class,
    static fn () => $service->complete(
        new SaleCommand(
            'm74-op-insufficient-stock-0008',
            $singleLineCart('synthetic-product-alpha', 999),
            TenderCategory::CASH,
            Money::fromAtomicUnits(2_000_000, 'IDR', 0),
            'm74-correlation-insufficient-stock-0010',
        ),
    ),
    'M74-STOCK-002 insufficient stock rejected',
);
$assertM74($store->saleCount() === $beforeStockSales, 'M74-STOCK-002 no partial sale');
$assertM74($store->paymentEffectCount() === $beforeStockPayments, 'M74-STOCK-002 no partial payment');
$assertM74($store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-alpha') === $beforeStockValue, 'M74-STOCK-002 no partial stock');

$sharedOperationId = 'm74-op-cross-tenant-collision-0009';
$alphaCollisionReceipt = $service->complete(
    new SaleCommand(
        $sharedOperationId,
        $singleLineCart('synthetic-product-collision', 1),
        TenderCategory::CASH,
        Money::fromAtomicUnits(2500, 'IDR', 0),
        'm74-correlation-alpha-collision-0011',
    ),
);
$enterBeta();
$betaCollisionReceipt = $service->complete(
    new SaleCommand(
        $sharedOperationId,
        $singleLineCart('synthetic-product-collision', 1),
        TenderCategory::CASH,
        Money::fromAtomicUnits(3500, 'IDR', 0),
        'm74-correlation-beta-collision-0012',
    ),
);
$assertM74($alphaCollisionReceipt->tenantId() === 'tenant-alpha', 'M74-ISO-001 alpha collision remains alpha');
$assertM74($betaCollisionReceipt->tenantId() === 'tenant-beta', 'M74-ISO-001 beta collision remains beta');
$assertM74($alphaCollisionReceipt->saleId() !== $betaCollisionReceipt->saleId(), 'M74-ISO-001 tenant-scoped operation identity');
$assertM74($alphaCollisionReceipt->total()->atomicUnits() === 2500, 'M74-ISO-001 alpha authoritative price');
$assertM74($betaCollisionReceipt->total()->atomicUnits() === 3500, 'M74-ISO-001 beta authoritative price');
$assertM74($store->stockFor('tenant-alpha', 'outlet-alpha', 'synthetic-product-collision') === 11, 'M74-ISO-001 alpha stock isolated');
$assertM74($store->stockFor('tenant-beta', 'outlet-beta', 'synthetic-product-collision') === 11, 'M74-ISO-001 beta stock isolated');

$audit = $store->auditRecords();
$completed = array_values(array_filter(
    $audit,
    static fn (array $record): bool => $record['operation_id'] === 'm74-op-alpha-cash-0001'
        && $record['outcome'] === 'COMPLETED',
));
$replayed = array_values(array_filter(
    $audit,
    static fn (array $record): bool => $record['operation_id'] === 'm74-op-alpha-cash-0001'
        && $record['outcome'] === 'REPLAYED',
));
$assertM74(count($completed) === 1, 'M74-AUDIT-001 exactly one completion audit');
$assertM74(count($replayed) === 1, 'M74-AUDIT-001 replay audit recorded without duplicate business effect');
$assertM74(($completed[0]['tenant'] ?? null) === 'tenant-alpha', 'M74-AUDIT-001 tenant audit context');
$assertM74(($completed[0]['actor'] ?? null) === 'synthetic-principal-a', 'M74-AUDIT-001 actor audit context');
$assertM74(($completed[0]['organization'] ?? null) === 'organization-alpha', 'M74-AUDIT-001 organization audit context');
$assertM74(($completed[0]['outlet'] ?? null) === 'outlet-alpha', 'M74-AUDIT-001 outlet audit context');
$assertM74(($completed[0]['device'] ?? null) === 'device-alpha', 'M74-AUDIT-001 device audit context');
$assertM74(($completed[0]['correlation_id'] ?? null) === 'm74-correlation-alpha-0001', 'M74-AUDIT-001 original correlation evidence');
$assertM74(($replayed[0]['correlation_id'] ?? null) === 'm74-correlation-alpha-retry-0002', 'M74-AUDIT-001 replay correlation evidence');

foreach ([
    'synthetic-principal-a',
    'synthetic-principal-b',
    'synthetic-product-alpha',
    'synthetic-product-secondary',
    'synthetic-product-beta',
    'synthetic-product-collision',
] as $fixture) {
    $assertM74(str_starts_with($fixture, 'synthetic-'), 'M74-SYN-001 synthetic fixture marker');
}

$forbiddenFrameworkReferences = [
    'Illuminate\\',
    'Laravel\\',
    'Inertia\\',
    'Vue',
];
foreach ([
    __DIR__.'/../app/Domain/Pos',
    __DIR__.'/../app/Application/Pos',
] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());
        foreach ($forbiddenFrameworkReferences as $needle) {
            $assertM74(! str_contains($content, $needle), "M74-ARCH-001 {$file->getFilename()} must not reference {$needle}");
        }
    }
}

foreach ([
    __DIR__.'/../app/Domain/Pos',
    __DIR__.'/../app/Application/Pos',
    __DIR__.'/../app/Infrastructure/Pos',
] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());
        foreach (['Illuminate\\Database', 'Schema::', 'DB::', 'new PDO', 'mysqli_'] as $needle) {
            $assertM74(! str_contains($content, $needle), "M74-ARCH-002 {$file->getFilename()} must not introduce physical persistence");
        }
    }
}

fwrite(STDOUT, "M7.4 POS core synthetic regression passed.\n");
