<?php

declare(strict_types=1);

use App\Application\Identity\RequireVerifiedPlatformIdentity;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Pos\CompleteSyntheticSale;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Preview\PreviewProfile;
use App\Application\Preview\TechnicalPreviewJourney;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Domain\Pos\SaleReceipt;
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

$makeJourney = static function (): array {
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

    return [
        'journey' => new TechnicalPreviewJourney(
            $fixtures,
            $memberships,
            $tenantContexts,
            $organizations,
            new CompleteSyntheticSale($fixtures, $organizationalContexts),
        ),
        'tenant_contexts' => $tenantContexts,
        'organizational_contexts' => $organizationalContexts,
    ];
};

$initialAdjustment = static fn (SaleReceipt $receipt): array => [
    'sale_id' => $receipt->saleId(),
    'status' => 'COMPLETED',
    'void_operation_id' => null,
    'refund_operation_id' => null,
    'refund_amount_atomic' => 0,
    'tender_category' => $receipt->tenderCategory()->value,
    'idempotent_replay' => false,
];

$receiptSnapshot = static function (
    SaleReceipt $receipt,
    PreviewProfile $profile,
    array $adjustment,
): array {
    return [
        'sale_id' => $receipt->saleId(),
        'tenant_id' => $receipt->tenantId(),
        'organization_id' => $profile->organizationId(),
        'actor_id' => $receipt->actorId(),
        'outlet_id' => $receipt->outletId(),
        'device_id' => $receipt->deviceId(),
        'total_atomic' => $receipt->total()->atomicUnits(),
        'tender_category' => $receipt->tenderCategory()->value,
        'adjustment' => $adjustment,
    ];
};

$stackA = $makeJourney();
/** @var TechnicalPreviewJourney $journeyA */
$journeyA = $stackA['journey'];
$alphaA = $journeyA->profile('synthetic-principal-a');
$betaA = $journeyA->profile('synthetic-principal-b');
$assert($alphaA !== null && $betaA !== null, 'ADJ-001 allowlisted profiles resolve');

$cashSale = $journeyA->completeSale(
    $alphaA,
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
$cashCompletedSnapshot = $receiptSnapshot($cashSale, $alphaA, $initialAdjustment($cashSale));

// Discard the sale-producing fixture/journey. A fresh Preview request must be
// able to consume only the server-owned receipt snapshot; object memory is not authority.
unset($journeyA, $stackA);
$stackB = $makeJourney();
/** @var TechnicalPreviewJourney $journeyB */
$journeyB = $stackB['journey'];
$alphaB = $journeyB->profile('synthetic-principal-a');
$betaB = $journeyB->profile('synthetic-principal-b');
$assert($alphaB !== null && $betaB !== null, 'ADJ-003 fresh request profiles resolve');

$expect(
    PosTransactionViolation::class,
    static fn () => $journeyB->refundCashSale(
        $alphaB,
        'preview-adjust-refund-before-void-0001',
        $cashCompletedSnapshot,
    ),
    'ADJ-004 refund before void fails closed across a fresh fixture',
);

$expect(
    PosTransactionViolation::class,
    static fn () => $journeyB->voidSale(
        $betaB,
        'preview-adjust-foreign-void-0001',
        $cashCompletedSnapshot,
    ),
    'ADJ-005 foreign tenant cannot consume alpha receipt snapshot',
);

$voidOperationId = 'preview-adjust-void-alpha-0001';
$void = $journeyB->voidSale($alphaB, $voidOperationId, $cashCompletedSnapshot);
$assert($void['status'] === 'VOIDED', 'ADJ-006 completed cash sale becomes VOIDED');
$assert($void['refund_amount_atomic'] === 0, 'ADJ-007 void alone has no cash refund effect');
$assert($void['idempotent_replay'] === false, 'ADJ-008 first void is not replay');
$cashVoidedSnapshot = array_replace($cashCompletedSnapshot, ['adjustment' => $void]);

unset($journeyB, $stackB);
$stackC = $makeJourney();
/** @var TechnicalPreviewJourney $journeyC */
$journeyC = $stackC['journey'];
$alphaC = $journeyC->profile('synthetic-principal-a');
$assert($alphaC !== null, 'ADJ-009 second fresh request profile resolves');

$voidReplay = $journeyC->voidSale($alphaC, $voidOperationId, $cashVoidedSnapshot);
$assert($voidReplay['status'] === 'VOIDED', 'ADJ-010 exact void replay preserves status across request boundary');
$assert($voidReplay['idempotent_replay'] === true, 'ADJ-011 exact void replay is marked');

$expect(
    PosTransactionViolation::class,
    static fn () => $journeyC->voidSale(
        $alphaC,
        'preview-adjust-void-alpha-competing-0002',
        $cashVoidedSnapshot,
    ),
    'ADJ-012 competing void operation fails closed',
);

$beforeRefund = $journeyC->reconcileCash(
    $alphaC,
    'preview-adjust-shift-alpha-0001',
    'preview-adjust-opening-alpha-0001',
    1000,
    5000,
    6000,
    8000,
    'preview-adjust-close-before-refund-0001',
);
$assert($beforeRefund->expectedCashAtomic() === 6000, 'ADJ-013 void alone does not reduce expected cash');
$assert($beforeRefund->varianceAtomic() === 0, 'ADJ-014 pre-refund observed cash matches 6000 expected');

$refundOperationId = 'preview-adjust-refund-alpha-0001';
$refund = $journeyC->refundCashSale($alphaC, $refundOperationId, $cashVoidedSnapshot);
$assert($refund['status'] === 'REFUNDED', 'ADJ-015 voided cash sale becomes REFUNDED');
$assert($refund['refund_amount_atomic'] === 5000, 'ADJ-016 refund amount equals authoritative full sale total');
$assert($refund['idempotent_replay'] === false, 'ADJ-017 first refund is not replay');
$cashRefundedSnapshot = array_replace($cashVoidedSnapshot, ['adjustment' => $refund]);

unset($journeyC, $stackC);
$stackD = $makeJourney();
/** @var TechnicalPreviewJourney $journeyD */
$journeyD = $stackD['journey'];
$alphaD = $journeyD->profile('synthetic-principal-a');
$assert($alphaD !== null, 'ADJ-018 third fresh request profile resolves');

$refundReplay = $journeyD->refundCashSale($alphaD, $refundOperationId, $cashRefundedSnapshot);
$assert($refundReplay['status'] === 'REFUNDED', 'ADJ-019 exact refund replay preserves status across request boundary');
$assert($refundReplay['idempotent_replay'] === true, 'ADJ-020 exact refund replay is marked');

$expect(
    PosTransactionViolation::class,
    static fn () => $journeyD->refundCashSale(
        $alphaD,
        'preview-adjust-refund-alpha-competing-0002',
        $cashRefundedSnapshot,
    ),
    'ADJ-021 competing refund operation fails closed',
);

$tamperedSnapshot = array_replace($cashVoidedSnapshot, ['tenant_id' => 'tenant-beta']);
$expect(
    PosTransactionViolation::class,
    static fn () => $journeyD->refundCashSale(
        $alphaD,
        'preview-adjust-refund-tampered-0003',
        $tamperedSnapshot,
    ),
    'ADJ-022 tampered receipt scope fails closed',
);

$afterRefund = $journeyD->reconcileCash(
    $alphaD,
    'preview-adjust-shift-alpha-0002',
    'preview-adjust-opening-alpha-0002',
    1000,
    5000,
    1000,
    8001,
    'preview-adjust-close-after-refund-0002',
    5000,
);
$assert($afterRefund->expectedCashAtomic() === 1000, 'ADJ-023 full cash refund explicitly offsets cash sale');
$assert($afterRefund->varianceAtomic() === 0, 'ADJ-024 post-refund observed cash matches opening-only expected');

$manualSale = $journeyD->completeSale(
    $alphaD,
    [['product_id' => 'synthetic-product-alpha', 'quantity' => 1]],
    'MANUAL_EXTERNAL',
    1999,
    'preview-adjust-manual-alpha-0002',
    'preview-adjust-manual-correlation-alpha-0002',
);
$manualCompletedSnapshot = $receiptSnapshot($manualSale, $alphaD, $initialAdjustment($manualSale));

unset($journeyD, $stackD);
$stackE = $makeJourney();
/** @var TechnicalPreviewJourney $journeyE */
$journeyE = $stackE['journey'];
$alphaE = $journeyE->profile('synthetic-principal-a');
$assert($alphaE !== null, 'ADJ-025 manual-sale fresh request profile resolves');

$manualVoid = $journeyE->voidSale(
    $alphaE,
    'preview-adjust-manual-void-alpha-0002',
    $manualCompletedSnapshot,
);
$assert($manualVoid['status'] === 'VOIDED', 'ADJ-026 manual external sale may carry synthetic void evidence');
$manualVoidedSnapshot = array_replace($manualCompletedSnapshot, ['adjustment' => $manualVoid]);

$expect(
    PosTransactionViolation::class,
    static fn () => $journeyE->refundCashSale(
        $alphaE,
        'preview-adjust-manual-refund-alpha-0002',
        $manualVoidedSnapshot,
    ),
    'ADJ-027 non-cash sale cannot create cash refund evidence',
);

$expect(
    InvalidArgumentException::class,
    static fn () => $journeyE->reconcileCash(
        $alphaE,
        'preview-adjust-shift-alpha-0003',
        'preview-adjust-opening-alpha-0003',
        1000,
        1000,
        0,
        8002,
        'preview-adjust-close-invalid-refund-0003',
        1001,
    ),
    'ADJ-028 refund total above cash sales fails closed',
);

$assert($stackE['tenant_contexts']->current() === null, 'ADJ-029 tenant context clears after fresh-instance adjustment operations');
$assert($stackE['organizational_contexts']->current() === null, 'ADJ-030 organizational context clears after fresh-instance adjustment operations');

echo "Technical Preview sale adjustment journey regression passed.\n";
