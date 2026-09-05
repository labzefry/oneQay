<?php

declare(strict_types=1);

use App\Application\Identity\RequireVerifiedPlatformIdentity;
use App\Application\Organization\EnterOrganizationalContext;
use App\Application\Pos\CashVarianceReviewDecisionCommand;
use App\Application\Pos\CompleteSyntheticSale;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Preview\TechnicalPreviewJourney;
use App\Application\Preview\TechnicalPreviewVarianceReviewJourney;
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
        fwrite(STDERR, "Preview variance-review regression failed: {$case}\n");
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
    'synthetic-principal-reviewer-a' => ['tenant-alpha'],
    'synthetic-principal-reviewer-b' => ['tenant-beta'],
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
    'synthetic-principal-reviewer-a' => [[
        'tenant' => 'tenant-alpha',
        'organization' => 'organization-alpha',
        'outlet' => 'outlet-alpha',
        'device' => 'device-alpha-reviewer',
    ]],
    'synthetic-principal-reviewer-b' => [[
        'tenant' => 'tenant-beta',
        'organization' => 'organization-beta',
        'outlet' => 'outlet-beta',
        'device' => 'device-beta-reviewer',
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
$preview = new TechnicalPreviewJourney(
    $fixtures,
    $memberships,
    $tenantContexts,
    $organizations,
    new CompleteSyntheticSale($fixtures, $organizationalContexts),
);
$reviews = new TechnicalPreviewVarianceReviewJourney($fixtures, $preview);

$alpha = $preview->profile('synthetic-principal-a');
$beta = $preview->profile('synthetic-principal-b');
$assert($alpha !== null && $beta !== null, 'VR-001 public operators resolve');
$assert(count($preview->profiles()) === 2, 'VR-002 public sign-in exposes only two operator profiles');
$assert($preview->profile('synthetic-principal-reviewer-a') === null, 'VR-003 hidden reviewer cannot resolve through public profile lookup');
$assert($fixtures->verifiedIdentity('synthetic-principal-reviewer-a') !== null, 'VR-004 hidden reviewer remains server-verifiable');

$reviewer = $reviews->reviewerFor($alpha);
$assert($reviewer->principalId() === 'synthetic-principal-reviewer-a', 'VR-005 alpha paired reviewer resolves server-side');
$assert($reviewer->principalId() !== $alpha->principalId(), 'VR-006 maker and checker actors differ');
$assert($reviewer->tenantId() === $alpha->tenantId(), 'VR-007 reviewer tenant matches maker');
$assert($reviewer->organizationId() === $alpha->organizationId(), 'VR-008 reviewer organization matches maker');
$assert($reviewer->outletId() === $alpha->outletId(), 'VR-009 reviewer outlet matches maker');

$variance = $preview->reconcileCash(
    $alpha,
    'preview-review-shift-alpha-0001',
    'preview-review-opening-alpha-0001',
    1000,
    5000,
    6100,
    9001,
    'preview-review-close-alpha-0001',
);
$assert($variance->direction() === 'OVER', 'VR-010 non-zero OVER variance derives canonically');
$assert($variance->varianceAtomic() === 100, 'VR-011 variance magnitude is +100');

$reconciliation = [
    'tenant_id' => $variance->tenantId(),
    'organization_id' => $variance->organizationId(),
    'outlet_id' => $variance->outletId(),
    'device_id' => $alpha->deviceId(),
    'shift_id' => $variance->shiftId(),
    'opening_cash_evidence_id' => $variance->openingCashEvidenceId(),
    'closing_cash_evidence_id' => $variance->closingCashEvidenceId(),
    'expected_cash_atomic' => $variance->expectedCashAtomic(),
    'observed_closing_atomic' => $variance->observedClosingAtomic(),
    'variance_atomic' => $variance->varianceAtomic(),
    'variance_direction' => $variance->direction(),
    'currency' => $variance->currency(),
    'cutoff_at_unix' => $variance->cutoffAtUnix(),
];

$expect(
    PosTransactionViolation::class,
    static fn () => $reviews->reviewDecision(
        $alpha,
        $reconciliation,
        'preview-varrev-before-explanation-0001',
        CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        'preview-varrev-before-correlation-0001',
        9100,
    ),
    'VR-012 review before explanation fails closed',
);

$explanation = $reviews->recordExplanation(
    $alpha,
    $reconciliation,
    'preview-varexp-alpha-0001',
    "  Selisih kas berasal dari koreksi penghitungan fisik akhir.\r\nSudah diverifikasi ulang.  ",
    'preview-varexp-correlation-alpha-0001',
    9101,
);
$assert($explanation['actor_identity_id'] === $alpha->principalId(), 'VR-013 explanation maker is signed-in operator');
$assert($explanation['explanation_text'] === "Selisih kas berasal dari koreksi penghitungan fisik akhir.\nSudah diverifikasi ulang.", 'VR-014 explanation text uses canonical command normalization');
$assert($explanation['idempotent_replay'] === false, 'VR-015 first explanation is not replay');

$withExplanation = array_replace($reconciliation, ['explanation' => $explanation]);
$explanationReplay = $reviews->recordExplanation(
    $alpha,
    $withExplanation,
    'preview-varexp-alpha-0001',
    "Selisih kas berasal dari koreksi penghitungan fisik akhir.\nSudah diverifikasi ulang.",
    'preview-varexp-correlation-alpha-replay-0002',
    9102,
);
$assert($explanationReplay['evidence_id'] === $explanation['evidence_id'], 'VR-016 exact explanation replay preserves evidence');
$assert($explanationReplay['idempotent_replay'] === true, 'VR-017 exact explanation replay is idempotent');

$expect(
    PosTransactionViolation::class,
    static fn () => $reviews->recordExplanation(
        $alpha,
        $withExplanation,
        'preview-varexp-alpha-competing-0002',
        'Different competing explanation.',
        'preview-varexp-competing-correlation-0002',
        9103,
    ),
    'VR-018 competing explanation fails closed',
);

$expect(
    PosTransactionViolation::class,
    static fn () => $reviews->recordExplanation(
        $beta,
        $reconciliation,
        'preview-varexp-beta-foreign-0001',
        'Cross tenant explanation must fail.',
        'preview-varexp-beta-correlation-0001',
        9104,
    ),
    'VR-019 foreign tenant cannot explain alpha variance',
);

$tamperedReconciliation = array_replace($reconciliation, ['variance_atomic' => 99]);
$expect(
    PosTransactionViolation::class,
    static fn () => $reviews->recordExplanation(
        $alpha,
        $tamperedReconciliation,
        'preview-varexp-alpha-tampered-0003',
        'Tampered arithmetic must fail.',
        'preview-varexp-tampered-correlation-0003',
        9105,
    ),
    'VR-020 tampered variance arithmetic fails closed',
);

$match = $preview->reconcileCash(
    $alpha,
    'preview-review-shift-alpha-match-0002',
    'preview-review-opening-alpha-match-0002',
    1000,
    5000,
    6000,
    9002,
    'preview-review-close-alpha-match-0002',
);
$matchSnapshot = array_replace($reconciliation, [
    'shift_id' => $match->shiftId(),
    'opening_cash_evidence_id' => $match->openingCashEvidenceId(),
    'closing_cash_evidence_id' => $match->closingCashEvidenceId(),
    'expected_cash_atomic' => $match->expectedCashAtomic(),
    'observed_closing_atomic' => $match->observedClosingAtomic(),
    'variance_atomic' => $match->varianceAtomic(),
    'variance_direction' => $match->direction(),
    'cutoff_at_unix' => $match->cutoffAtUnix(),
]);
$expect(
    PosTransactionViolation::class,
    static fn () => $reviews->recordExplanation(
        $alpha,
        $matchSnapshot,
        'preview-varexp-alpha-match-0004',
        'MATCH should not require explanation.',
        'preview-varexp-match-correlation-0004',
        9106,
    ),
    'VR-021 MATCH cannot enter variance explanation workflow',
);

$review = $reviews->reviewDecision(
    $alpha,
    $withExplanation,
    'preview-varrev-alpha-0001',
    CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
    'preview-varrev-correlation-alpha-0001',
    9201,
);
$assert($review['review_outcome'] === CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED, 'VR-022 reviewer can accept explanation');
$assert($review['explanation_actor_identity_id'] === $alpha->principalId(), 'VR-023 review binds maker actor');
$assert($review['reviewer_actor_identity_id'] === $reviewer->principalId(), 'VR-024 review binds hidden checker actor');
$assert($review['reviewer_actor_identity_id'] !== $review['explanation_actor_identity_id'], 'VR-025 maker-checker separation is explicit');
$assert($review['idempotent_replay'] === false, 'VR-026 first review is not replay');

$withReview = array_replace($withExplanation, ['review' => $review]);
$reviewReplay = $reviews->reviewDecision(
    $alpha,
    $withReview,
    'preview-varrev-alpha-0001',
    CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
    'preview-varrev-correlation-alpha-replay-0002',
    9202,
);
$assert($reviewReplay['review_evidence_id'] === $review['review_evidence_id'], 'VR-027 exact review replay preserves evidence');
$assert($reviewReplay['idempotent_replay'] === true, 'VR-028 exact review replay is idempotent');

$expect(
    PosTransactionViolation::class,
    static fn () => $reviews->reviewDecision(
        $alpha,
        $withReview,
        'preview-varrev-alpha-0001',
        CashVarianceReviewDecisionCommand::REVIEW_REJECTED,
        'preview-varrev-correlation-alpha-competing-0003',
        9203,
    ),
    'VR-029 competing ACCEPT/REJECT decision fails closed',
);

$tamperedExplanation = $explanation;
$tamperedExplanation['payload_fingerprint'] = str_repeat('0', 64);
$tamperedExplanationSnapshot = array_replace($reconciliation, ['explanation' => $tamperedExplanation]);
$expect(
    PosTransactionViolation::class,
    static fn () => $reviews->reviewDecision(
        $alpha,
        $tamperedExplanationSnapshot,
        'preview-varrev-alpha-tampered-0004',
        CashVarianceReviewDecisionCommand::REVIEW_REJECTED,
        'preview-varrev-tampered-correlation-0004',
        9204,
    ),
    'VR-030 tampered explanation fingerprint cannot be reviewed',
);

$assert($tenantContexts->current() === null, 'VR-031 tenant context clears after maker-checker operations');
$assert($organizationalContexts->current() === null, 'VR-032 organizational context clears after maker-checker operations');

echo "Technical Preview variance-review journey regression passed.\n";
