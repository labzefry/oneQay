<?php

declare(strict_types=1);

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\CashVarianceReviewDecisionCommand;
use App\Application\Pos\DeriveCashVariance;
use App\Application\Pos\PosCashVarianceReconciliationWorkspaceSnapshot;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Pos\LaravelExpectedCashSnapshotReader;
use App\Infrastructure\Pos\LaravelPosCashVarianceReconciliationWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('v', 32));
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

$deny = static function (callable $operation, string $message) use ($assert): void {
    try {
        $operation();
        $assert(false, $message);
    } catch (PosTransactionViolation) {
        // Expected fail-closed boundary.
    }
};

$databasePath = sys_get_temp_dir().'/oneqay-s163-variance-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint163 disposable database could not be created.');

$app['config']->set('database.default', 's163_variance');
$app['config']->set('database.connections.s163_variance', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'foreign_key_constraints' => false,
]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s163_variance');
$connection = $databaseManager->connection('s163_variance');

$connection->statement('CREATE TABLE oneqay_pos_shifts (tenant_id TEXT NOT NULL, shift_id TEXT NOT NULL, operation_id TEXT, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, active_slot INTEGER NULL)');
$connection->statement('CREATE TABLE oneqay_pos_shift_opening_cash_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, operation_id TEXT NOT NULL, shift_id TEXT NOT NULL, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, opening_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, correlation_id TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_shift_closing_cash_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, operation_id TEXT NOT NULL, shift_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, closing_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, correlation_id TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT, sale_id TEXT, organization_id TEXT, outlet_id TEXT, device_id TEXT, shift_id TEXT, tender_category TEXT, evidence_mode TEXT, completed_at_unix INTEGER, applied_atomic INTEGER, total_atomic INTEGER, currency TEXT, currency_scale INTEGER)');
$connection->statement('CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT, sale_id TEXT, void_id TEXT, organization_id TEXT, outlet_id TEXT, reversed_atomic INTEGER, currency TEXT, currency_scale INTEGER, tender_category TEXT, evidence_mode TEXT, voided_at_unix INTEGER)');
$connection->statement('CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT, sale_id TEXT, refund_id TEXT, void_id TEXT, organization_id TEXT, outlet_id TEXT, refunded_atomic INTEGER, currency TEXT, currency_scale INTEGER, tender_category TEXT, evidence_mode TEXT, refunded_at_unix INTEGER)');
$connection->statement('CREATE TABLE oneqay_pos_cash_variance_explanation_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, operation_id TEXT NOT NULL, payload_fingerprint TEXT NOT NULL, shift_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, closing_cash_evidence_id TEXT NOT NULL, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, cutoff_at_unix INTEGER NOT NULL, expected_cash_atomic INTEGER NOT NULL, observed_closing_cash_atomic INTEGER NOT NULL, variance_atomic INTEGER NOT NULL, variance_direction TEXT NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, explanation_text TEXT NOT NULL, correlation_id TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_cash_variance_review_decision_evidence (tenant_id TEXT NOT NULL, review_evidence_id TEXT NOT NULL, operation_id TEXT NOT NULL, payload_fingerprint TEXT NOT NULL, shift_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, closing_cash_evidence_id TEXT NOT NULL, cash_variance_explanation_evidence_id TEXT NOT NULL, explanation_actor_identity_id TEXT NOT NULL, reviewer_actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, cutoff_at_unix INTEGER NOT NULL, expected_cash_atomic INTEGER NOT NULL, observed_closing_cash_atomic INTEGER NOT NULL, variance_atomic INTEGER NOT NULL, variance_direction TEXT NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, explanation_payload_fingerprint TEXT NOT NULL, review_outcome TEXT NOT NULL, correlation_id TEXT NOT NULL, reviewed_at_unix INTEGER NOT NULL)');

$shiftOver = str_repeat('a', 32);
$shiftMatch = str_repeat('b', 32);
$shiftRejected = str_repeat('c', 32);
$shiftExplain = str_repeat('d', 32);
$shiftClosed = str_repeat('e', 32);
$shiftForeign = str_repeat('f', 32);

$fixtures = [
    [$shiftOver, 'device-sale-a', 'maker-a', 'closing-a', 'opening-a', 1000, 1100, 2100, 1, 'org-a', 'outlet-a'],
    [$shiftMatch, 'device-sale-b', 'maker-b', 'closing-b', 'opening-b', 500, 500, 2200, 1, 'org-a', 'outlet-a'],
    [$shiftRejected, 'device-sale-c', 'maker-c', 'closing-c', 'opening-c', 1000, 900, 2300, 1, 'org-a', 'outlet-a'],
    [$shiftExplain, 'device-sale-d', 'maker-d', 'closing-d', 'opening-d', 700, 750, 2400, 1, 'org-a', 'outlet-a'],
    [$shiftClosed, 'device-sale-e', 'maker-e', 'closing-e', 'opening-e', 400, 450, 2500, null, 'org-a', 'outlet-a'],
    [$shiftForeign, 'device-sale-f', 'maker-f', 'closing-f', 'opening-f', 300, 350, 2600, 1, 'org-a', 'outlet-b'],
];

foreach ($fixtures as [$shift, $device, $actor, $closingId, $openingId, $openingAtomic, $closingAtomic, $closingAt, $active, $organization, $outlet]) {
    $connection->table('oneqay_pos_shifts')->insert([
        'tenant_id' => 'tenant-a', 'shift_id' => $shift, 'operation_id' => 'shift-'.$shift,
        'actor_identity_id' => 'opener-'.$actor, 'organization_id' => $organization,
        'outlet_id' => $outlet, 'device_id' => $device, 'active_slot' => $active,
    ]);
    $connection->table('oneqay_pos_shift_opening_cash_evidence')->insert([
        'tenant_id' => 'tenant-a', 'evidence_id' => $openingId, 'operation_id' => 'opening-operation-'.$openingId,
        'shift_id' => $shift, 'actor_identity_id' => 'opener-'.$actor, 'organization_id' => $organization,
        'outlet_id' => $outlet, 'device_id' => $device, 'opening_cash_atomic' => $openingAtomic,
        'currency' => 'IDR', 'currency_scale' => 0, 'evidence_mode' => 'OPERATOR_OBSERVED_OPENING_CASH',
        'correlation_id' => 'opening-correlation-'.$openingId, 'recorded_at_unix' => 1000,
    ]);
    $connection->table('oneqay_pos_shift_closing_cash_evidence')->insert([
        'tenant_id' => 'tenant-a', 'evidence_id' => $closingId, 'operation_id' => 'closing-operation-'.$closingId,
        'shift_id' => $shift, 'opening_cash_evidence_id' => $openingId, 'actor_identity_id' => $actor,
        'organization_id' => $organization, 'outlet_id' => $outlet, 'device_id' => $device,
        'closing_cash_atomic' => $closingAtomic, 'currency' => 'IDR', 'currency_scale' => 0,
        'evidence_mode' => 'OPERATOR_OBSERVED_CLOSING_CASH', 'correlation_id' => 'closing-correlation-'.$closingId,
        'recorded_at_unix' => $closingAt,
    ]);
}

$explanationA = 'varexp-'.str_repeat('a', 25);
$explanationC = 'varexp-'.str_repeat('c', 25);
$connection->table('oneqay_pos_cash_variance_explanation_evidence')->insert([
    [
        'tenant_id' => 'tenant-a', 'evidence_id' => $explanationA, 'operation_id' => 'variance-explain-operation-a',
        'payload_fingerprint' => hash('sha256', 'explain-a'), 'shift_id' => $shiftOver,
        'opening_cash_evidence_id' => 'opening-a', 'closing_cash_evidence_id' => 'closing-a',
        'actor_identity_id' => 'maker-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a',
        'cutoff_at_unix' => 2100, 'expected_cash_atomic' => 1000, 'observed_closing_cash_atomic' => 1100,
        'variance_atomic' => 100, 'variance_direction' => 'OVER', 'currency' => 'IDR', 'currency_scale' => 0,
        'explanation_text' => 'Counted surplus after physical drawer recount.',
        'correlation_id' => 'variance-explain-correlation-a', 'recorded_at_unix' => 2110,
    ],
    [
        'tenant_id' => 'tenant-a', 'evidence_id' => $explanationC, 'operation_id' => 'variance-explain-operation-c',
        'payload_fingerprint' => hash('sha256', 'explain-c'), 'shift_id' => $shiftRejected,
        'opening_cash_evidence_id' => 'opening-c', 'closing_cash_evidence_id' => 'closing-c',
        'actor_identity_id' => 'maker-c', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a',
        'cutoff_at_unix' => 2300, 'expected_cash_atomic' => 1000, 'observed_closing_cash_atomic' => 900,
        'variance_atomic' => -100, 'variance_direction' => 'SHORT', 'currency' => 'IDR', 'currency_scale' => 0,
        'explanation_text' => 'Drawer short after closing count.',
        'correlation_id' => 'variance-explain-correlation-c', 'recorded_at_unix' => 2310,
    ],
]);

$connection->table('oneqay_pos_cash_variance_review_decision_evidence')->insert([
    'tenant_id' => 'tenant-a', 'review_evidence_id' => 'varrev-'.str_repeat('c', 25),
    'operation_id' => 'variance-review-operation-c', 'payload_fingerprint' => hash('sha256', 'review-c'),
    'shift_id' => $shiftRejected, 'opening_cash_evidence_id' => 'opening-c', 'closing_cash_evidence_id' => 'closing-c',
    'cash_variance_explanation_evidence_id' => $explanationC, 'explanation_actor_identity_id' => 'maker-c',
    'reviewer_actor_identity_id' => 'reviewer-c', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a',
    'cutoff_at_unix' => 2300, 'expected_cash_atomic' => 1000, 'observed_closing_cash_atomic' => 900,
    'variance_atomic' => -100, 'variance_direction' => 'SHORT', 'currency' => 'IDR', 'currency_scale' => 0,
    'explanation_payload_fingerprint' => hash('sha256', 'explain-c'),
    'review_outcome' => CashVarianceReviewDecisionCommand::REVIEW_REJECTED,
    'correlation_id' => 'variance-review-correlation-c', 'reviewed_at_unix' => 2320,
]);

$contextFor = static fn (string $actor, string $device = 'current-review-device', string $outlet = 'outlet-a'): PosExecutionContext => PosExecutionContext::fromVerified(
    new VerifiedOrganizationalContext(
        PlatformIdentityId::fromString($actor),
        TenantId::fromString('tenant-a'),
        OrganizationId::fromString('org-a'),
        OutletId::fromString($outlet),
        DeviceId::fromString($device),
    ),
);

$makeRepository = static fn (
    bool $persistence = true,
    string $runtime = 'ci',
    bool $workspace = true,
    bool $closing = true,
): LaravelPosCashVarianceReconciliationWorkspaceRepository => new LaravelPosCashVarianceReconciliationWorkspaceRepository(
    $connection,
    new LaravelExpectedCashSnapshotReader($connection),
    new DeriveCashVariance(),
    $persistence,
    $runtime,
    $workspace,
    $closing,
);

$repository = $makeRepository();
$reviewerContext = $contextFor('reviewer-a');
$data = $repository->read($reviewerContext, 'closing-a');
$assert(count($data->cases()) === 4, 'Workspace must show only active same-outlet cases.');
$caseDevices = array_column($data->cases(), 'device_id');
$assert(in_array('device-sale-a', $caseDevices, true), 'Workspace must preserve cross-device same-outlet reviewer visibility.');
$assert(! in_array('device-sale-e', $caseDevices, true), 'Closed shift must not remain in reconciliation queue.');
$assert(! in_array('device-sale-f', $caseDevices, true), 'Foreign outlet must not leak into reconciliation queue.');

$selected = $data->selected();
$assert(is_array($selected), 'Selected reconciliation case missing.');
$assert($selected['expected_cash_atomic'] === 1000, 'Expected cash must be derived from canonical opening evidence.');
$assert($selected['observed_closing_cash_atomic'] === 1100, 'Observed closing cash mismatch.');
$assert($selected['variance_atomic'] === 100 && $selected['variance_direction'] === 'OVER', 'Canonical OVER variance derivation changed.');
$assert($selected['device_id'] === 'device-sale-a', 'Selected case must preserve original shift device, not current reviewer device.');
$assert(is_array($selected['explanation']) && $selected['explanation']['evidence_id'] === $explanationA, 'Existing explanation evidence not resolved.');
$assert($selected['review'] === null, 'Review unexpectedly exists for pending case.');

$reviewSnapshot = new PosCashVarianceReconciliationWorkspaceSnapshot($data, false, true);
$reviewSelected = $reviewSnapshot->selected();
$assert(is_array($reviewSelected) && $reviewSelected['can_record_review'] === true, 'Independent review permission must enable review for another actor explanation.');
$assert($reviewSelected['can_record_explanation'] === false, 'Review-only actor must not gain explanation authority.');
$assert($reviewSelected['review_ready_for_final_close'] === false, 'Pending review must not appear ready for final close.');

$selfData = $repository->read($contextFor('maker-a'), 'closing-a');
$selfSnapshot = new PosCashVarianceReconciliationWorkspaceSnapshot($selfData, true, true);
$selfSelected = $selfSnapshot->selected();
$assert(is_array($selfSelected) && $selfSelected['self_review_blocked'] === true, 'Explanation author must be visibly blocked from self-review.');
$assert($selfSelected['can_record_review'] === false, 'Maker-checker rule must deny self-review action.');

$explainData = $repository->read($contextFor('explainer-d'), 'closing-d');
$explainSnapshot = new PosCashVarianceReconciliationWorkspaceSnapshot($explainData, true, false);
$explainSelected = $explainSnapshot->selected();
$assert(is_array($explainSelected) && $explainSelected['variance_direction'] === 'OVER', 'Explanation candidate variance changed.');
$assert($explainSelected['can_record_explanation'] === true, 'Explanation-only actor must be able to explain non-zero variance without prior evidence.');
$assert($explainSelected['can_record_review'] === false, 'Explanation permission must not imply reviewer authority.');

$matchData = $repository->read($reviewerContext, 'closing-b');
$matchSnapshot = new PosCashVarianceReconciliationWorkspaceSnapshot($matchData, true, true);
$matchSelected = $matchSnapshot->selected();
$assert(is_array($matchSelected) && $matchSelected['variance_direction'] === 'MATCH' && $matchSelected['variance_atomic'] === 0, 'MATCH derivation changed.');
$assert($matchSelected['review_ready_for_final_close'] === true, 'MATCH must require no variance explanation/review prerequisite.');
$assert($matchSelected['can_record_explanation'] === false && $matchSelected['can_record_review'] === false, 'MATCH must expose no reconciliation mutation.');

$rejectedData = $repository->read($reviewerContext, 'closing-c');
$rejectedSnapshot = new PosCashVarianceReconciliationWorkspaceSnapshot($rejectedData, true, true);
$rejectedSelected = $rejectedSnapshot->selected();
$assert(is_array($rejectedSelected) && $rejectedSelected['variance_direction'] === 'SHORT', 'SHORT derivation changed.');
$assert($rejectedSelected['review_rejected_terminal'] === true, 'REVIEW_REJECTED must be surfaced as terminal.');
$assert($rejectedSelected['review_ready_for_final_close'] === false, 'Rejected review must not satisfy final-close variance prerequisite.');
$assert($rejectedSelected['can_record_review'] === false, 'Terminal reviewed evidence must expose no second decision action.');

$acceptedReview = [
    'tenant_id' => 'tenant-a', 'review_evidence_id' => 'varrev-'.str_repeat('a', 25),
    'operation_id' => 'variance-review-operation-a', 'payload_fingerprint' => hash('sha256', 'review-a'),
    'shift_id' => $shiftOver, 'opening_cash_evidence_id' => 'opening-a', 'closing_cash_evidence_id' => 'closing-a',
    'cash_variance_explanation_evidence_id' => $explanationA, 'explanation_actor_identity_id' => 'maker-a',
    'reviewer_actor_identity_id' => 'reviewer-a', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a',
    'cutoff_at_unix' => 2100, 'expected_cash_atomic' => 1000, 'observed_closing_cash_atomic' => 1100,
    'variance_atomic' => 100, 'variance_direction' => 'OVER', 'currency' => 'IDR', 'currency_scale' => 0,
    'explanation_payload_fingerprint' => hash('sha256', 'explain-a'),
    'review_outcome' => CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
    'correlation_id' => 'variance-review-correlation-a', 'reviewed_at_unix' => 2120,
];
$connection->table('oneqay_pos_cash_variance_review_decision_evidence')->insert($acceptedReview);
$acceptedSnapshot = new PosCashVarianceReconciliationWorkspaceSnapshot(
    $repository->read($reviewerContext, 'closing-a'),
    false,
    true,
);
$acceptedSelected = $acceptedSnapshot->selected();
$assert(is_array($acceptedSelected) && $acceptedSelected['review_ready_for_final_close'] === true, 'Accepted review must satisfy variance prerequisite.');
$assert($acceptedSelected['review_rejected_terminal'] === false, 'Accepted review must not be marked rejected.');

$resolved = $repository->resolveVariance($contextFor('another-authorized-actor'), 'closing-a');
$assert($resolved->tenantId() === 'tenant-a' && $resolved->organizationId() === 'org-a' && $resolved->outletId() === 'outlet-a', 'Server-resolved variance scope changed.');
$assert($resolved->expectedCashAtomic() === 1000 && $resolved->observedClosingAtomic() === 1100 && $resolved->varianceAtomic() === 100, 'Mutation orchestration subject must remain server-authoritative.');

$deny(fn () => $repository->read($reviewerContext, 'closing-f'), 'Explicit foreign-outlet selection must fail closed.');
$deny(fn () => $repository->read($reviewerContext, 'closing-e'), 'Explicit closed-shift selection must fail closed.');
$deny(fn () => $makeRepository(false)->read($reviewerContext, null), 'Persistence-disabled workspace must fail closed.');
$deny(fn () => $makeRepository(true, 'production')->read($reviewerContext, null), 'Production runtime must remain outside Sprint163 allowlist.');
$deny(fn () => $makeRepository(true, 'ci', false)->read($reviewerContext, null), 'Disabled workspace feature must fail closed.');
$deny(fn () => $makeRepository(true, 'ci', true, false)->read($reviewerContext, null), 'Disabled closing-cash capability must fail closed.');

$connection->table('oneqay_pos_cash_variance_explanation_evidence')->insert([
    'tenant_id' => 'tenant-a', 'evidence_id' => 'varexp-'.str_repeat('9', 25),
    'operation_id' => 'variance-explain-duplicate-c', 'payload_fingerprint' => hash('sha256', 'duplicate-c'),
    'shift_id' => $shiftRejected, 'opening_cash_evidence_id' => 'opening-c', 'closing_cash_evidence_id' => 'closing-c',
    'actor_identity_id' => 'another-maker', 'organization_id' => 'org-a', 'outlet_id' => 'outlet-a',
    'cutoff_at_unix' => 2300, 'expected_cash_atomic' => 1000, 'observed_closing_cash_atomic' => 900,
    'variance_atomic' => -100, 'variance_direction' => 'SHORT', 'currency' => 'IDR', 'currency_scale' => 0,
    'explanation_text' => 'Duplicate evidence should fail closed.', 'correlation_id' => 'duplicate-correlation-c',
    'recorded_at_unix' => 2330,
]);
$deny(fn () => $repository->read($reviewerContext, 'closing-c'), 'Duplicate explanation evidence must fail closed.');

$databaseManager->disconnect('s163_variance');
$databaseManager->purge('s163_variance');
@unlink($databasePath);

fwrite(STDOUT, "Sprint163 POS cash variance reconciliation workspace regression passed.\n");
