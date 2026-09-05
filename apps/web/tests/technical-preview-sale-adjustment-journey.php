<?php

declare(strict_types=1);

use App\Application\Identity\RequireVerifiedPlatformIdentity;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Pos\CompleteSyntheticSale;
use App\Application\Pos\PosTransactionViolation;
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
        fwrite(STDERR, "Preview sale adjustment regression failed: {$case}\n");
        exit(1);
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

$alpha = $journey->profile('synthetic-principal-a');
$beta = $journey->profile('synthetic-principal-b');
$assert($alpha !== null && $beta !== null, 'ADJ-001 allowlisted profiles resolve');

$cashSale = $journey->completeSale(
    $alpha,
    [
        ['product_id' => 'synthetic-product-alpha', 'quantity' => 2],
        ['product_id' => 'synthetic-product-secondary', 'quantity' => 2],
    ],
    'CASH',
    6000,
    'preview-adjust-sale-alpha-0001',
    'preview-adjust-correlation-alpha-0001',
);
$assert($cashSale->total()->atomicUnits() === 5000, 'ADJ-002 authoritative cash sale total is 5000');

$expect(
    PosTransactionViolation::class,
    static fn () => $journey->refundCashSale(
        $alpha,
        $cashSale->saleId(),
        'preview-adjust-refund-before-void-0001',
    ),
    'ADJ-003 refund before void fails closed',
);

$expect(
    PosTransactionViolation::class,
    static fn () => $journey->voidSale(
        $beta,
        $cashSale->saleId(),
        'preview-adjust-foreign-void-0001',
    ),
    'ADJ-004 foreign tenant cannot void alpha sale',
);

$void = $journey->voidSale(
    $alpha,
    $cashSale->saleId(),
    'preview-adjust-void-alpha-0001',
);
$assert($void['status'] === 'VOIDED', 'ADJ-005 completed cash sale becomes VOIDED');
$assert($void['refund_amount_atomic'] === 0, 'ADJ-006 void alone has no cash refund effect');
$assert($void['idempotent_replay'] === false, 'ADJ-007 first void is not replay');

$voidReplay = $journey->voidSale(
    $alpha,
    $cashSale->saleId(),
    'preview-adjust-void-alpha-0001',
);
$assert($voidReplay['status'] === 'VOIDED', 'ADJ-008 exact void replay preserves status');
$assert($voidReplay['idempotent_replay'] === true, 'ADJ-009 exact void replay is marked');

$expect(
    PosTransactionViolation::class,
    static fn () => $journey->voidSale(
        $alpha,
        $cashSale->saleId(),
        'preview-adjust-void-alpha-competing-0002',
    ),
    'ADJ-010 competing void operation fails closed',
);

$beforeRefund = $journey->reconcileCash(
    $alpha,
    'preview-adjust-shift-alpha-0001',
    'preview-adjust-opening-alpha-0001',
    1000,
    5000,
    6000,
    8000,
    'preview-adjust-close-before-refund-0001',
);
$assert($beforeRefund->expectedCashAtomic() === 6000, 'ADJ-011 void alone does not reduce expected cash');
$assert($beforeRefund->varianceAtomic() === 0, 'ADJ-012 pre-refund observed cash matches 6000 expected');

$refund = $journey->refundCashSale(
    $alpha,
    $cashSale->saleId(),
    'preview-adjust-refund-alpha-0001',
);
$assert($refund['status'] === 'REFUNDED', 'ADJ-013 voided cash sale becomes REFUNDED');
$assert($refund['refund_amount_atomic'] === 5000, 'ADJ-014 refund amount equals authoritative full sale total');
$assert($refund['idempotent_replay'] === false, 'ADJ-015 first refund is not replay');

$refundReplay = $journey->refundCashSale(
    $alpha,
    $cashSale->saleId(),
    'preview-adjust-refund-alpha-0001',
);
$assert($refundReplay['status'] === 'REFUNDED', 'ADJ-016 exact refund replay preserves status');
$assert($refundReplay['idempotent_replay'] === true, 'ADJ-017 exact refund replay is marked');

$expect(
    PosTransactionViolation::class,
    static fn () => $journey->refundCashSale(
        $alpha,
        $cashSale->saleId(),
        'preview-adjust-refund-alpha-competing-0002',
    ),
    'ADJ-018 competing refund operation fails closed',
);

$afterRefund = $journey->reconcileCash(
    $alpha,
    'preview-adjust-shift-alpha-0002',
    'preview-adjust-opening-alpha-0002',
    1000,
    5000,
    1000,
    8001,
    'preview-adjust-close-after-refund-0002',
    5000,
);
$assert($afterRefund->expectedCashAtomic() === 1000, 'ADJ-019 full cash refund explicitly offsets cash sale');
$assert($afterRefund->varianceAtomic() === 0, 'ADJ-020 post-refund observed cash matches opening-only expected');

$manualSale = $journey->completeSale(
    $alpha,
    [['product_id' => 'synthetic-product-alpha', 'quantity' => 1]],
    'MANUAL_EXTERNAL',
    1999,
    'preview-adjust-manual-alpha-0002',
    'preview-adjust-manual-correlation-alpha-0002',
);
$manualVoid = $journey->voidSale(
    $alpha,
    $manualSale->saleId(),
    'preview-adjust-manual-void-alpha-0002',
);
$assert($manualVoid['status'] === 'VOIDED', 'ADJ-021 manual external sale may carry synthetic void evidence');
$expect(
    PosTransactionViolation::class,
    static fn () => $journey->refundCashSale(
        $alpha,
        $manualSale->saleId(),
        'preview-adjust-manual-refund-alpha-0002',
    ),
    'ADJ-022 non-cash sale cannot create cash refund evidence',
);

$expect(
    InvalidArgumentException::class,
    static fn () => $journey->reconcileCash(
        $alpha,
        'preview-adjust-shift-alpha-0003',
        'preview-adjust-opening-alpha-0003',
        1000,
        1000,
        0,
        8002,
        'preview-adjust-close-invalid-refund-0003',
        1001,
    ),
    'ADJ-023 refund total above cash sales fails closed',
);

$assert($tenantContexts->current() === null, 'ADJ-024 tenant context clears after adjustment operations');
$assert($organizationalContexts->current() === null, 'ADJ-025 organizational context clears after adjustment operations');

echo "Technical Preview sale adjustment journey regression passed.\n";
