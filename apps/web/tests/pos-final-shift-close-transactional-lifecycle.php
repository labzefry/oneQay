<?php

declare(strict_types=1);

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\CloseShiftCommand;
use App\Application\Pos\DeriveCashVariance;
use App\Application\Pos\FinalShiftCloseAuthorizationPolicy;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Pos\LaravelCloseShiftRepository;
use App\Infrastructure\Pos\LaravelExpectedCashSnapshotReader;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('c', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
$app->instance('request', Request::create('/'));
$app->make(Kernel::class)->bootstrap();

$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Sprint95 Final Shift Close regression failed: '.$case);
    }
};
$expectViolation = static function (callable $operation, string $case) use ($assert): void {
    try {
        $operation();
        $assert(false, $case.' accepted');
    } catch (PosTransactionViolation) {
    }
};

$dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s95-final-close-'.getmypid();
@mkdir($dir, 0700, true);
$db = $dir.DIRECTORY_SEPARATOR.'pos.sqlite';
$assert(touch($db), 'sqlite create');

$app['config']->set('database.default', 's95-final-close');
$app['config']->set('database.connections.s95-final-close', [
    'driver' => 'sqlite', 'database' => $db, 'prefix' => '', 'foreign_key_constraints' => false,
]);
$manager = $app->make('db');
$manager->purge('s95-final-close');
$manager->setDefaultConnection('s95-final-close');
$connection = $manager->connection('s95-final-close');
$connection->getPdo();

$connection->statement('CREATE TABLE oneqay_pos_shifts (tenant_id TEXT NOT NULL, shift_id TEXT NOT NULL, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, active_slot INTEGER NULL)');
$connection->statement('CREATE TABLE oneqay_pos_shift_opening_cash_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, opening_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_shift_closing_cash_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, operation_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, closing_cash_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, evidence_mode TEXT NOT NULL, correlation_id TEXT NOT NULL, recorded_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NULL, total_atomic INTEGER NOT NULL, applied_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, completed_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, void_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, reversed_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, refund_id TEXT NOT NULL, sale_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, refunded_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, refunded_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_cash_variance_review_decision_evidence (tenant_id TEXT NOT NULL, review_evidence_id TEXT NOT NULL, shift_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, closing_cash_evidence_id TEXT NOT NULL, explanation_actor_identity_id TEXT NOT NULL, reviewer_actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, cutoff_at_unix INTEGER NOT NULL, expected_cash_atomic INTEGER NOT NULL, observed_closing_cash_atomic INTEGER NOT NULL, variance_atomic INTEGER NOT NULL, variance_direction TEXT NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, review_outcome TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_shift_close_evidence (tenant_id TEXT NOT NULL, evidence_id TEXT NOT NULL, operation_id TEXT NOT NULL, payload_fingerprint TEXT NOT NULL, shift_id TEXT NOT NULL, opening_cash_evidence_id TEXT NOT NULL, closing_cash_evidence_id TEXT NOT NULL, closer_actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, cutoff_at_unix INTEGER NOT NULL, expected_cash_atomic INTEGER NOT NULL, observed_closing_cash_atomic INTEGER NOT NULL, variance_atomic INTEGER NOT NULL, variance_direction TEXT NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, review_evidence_id TEXT NULL, review_outcome TEXT NULL, correlation_id TEXT NOT NULL, closed_at_unix INTEGER NOT NULL)');

$tenant = 'tenant-alpha';
$organization = 'organization-alpha';
$closer = 'identity-closer';

$makeContext = static function (string $outlet, string $device, string $actor = 'identity-closer') use ($tenant, $organization): PosExecutionContext {
    return PosExecutionContext::fromVerified(new VerifiedOrganizationalContext(
        PlatformIdentityId::fromString($actor),
        TenantId::fromString($tenant),
        OrganizationId::fromString($organization),
        OutletId::fromString($outlet),
        DeviceId::fromString($device),
    ));
};

$seed = static function (
    string $suffix,
    string $opener,
    int $closingAtomic,
    ?string $reviewOutcome = null,
    string $explanationActor = 'identity-explainer',
    string $reviewerActor = 'identity-reviewer',
) use ($connection, $tenant, $organization): array {
    $outlet = 'outlet-'.$suffix;
    $device = 'device-'.$suffix;
    $shift = 'shift-'.$suffix;
    $opening = 'opening-'.$suffix;
    $closing = 'closing-'.$suffix;
    $connection->table('oneqay_pos_shifts')->insert([
        'tenant_id' => $tenant, 'shift_id' => $shift, 'actor_identity_id' => $opener,
        'organization_id' => $organization, 'outlet_id' => $outlet, 'device_id' => $device, 'active_slot' => 1,
    ]);
    $connection->table('oneqay_pos_shift_opening_cash_evidence')->insert([
        'tenant_id' => $tenant, 'evidence_id' => $opening, 'shift_id' => $shift,
        'organization_id' => $organization, 'outlet_id' => $outlet, 'device_id' => $device,
        'opening_cash_atomic' => 1000, 'currency' => 'IDR', 'currency_scale' => 0,
        'evidence_mode' => 'OPERATOR_OBSERVED_OPENING_CASH', 'recorded_at_unix' => 100,
    ]);
    $connection->table('oneqay_pos_shift_closing_cash_evidence')->insert([
        'tenant_id' => $tenant, 'evidence_id' => $closing, 'opening_cash_evidence_id' => $opening,
        'shift_id' => $shift, 'operation_id' => 'closing-operation-'.$suffix,
        'organization_id' => $organization, 'outlet_id' => $outlet, 'device_id' => $device,
        'closing_cash_atomic' => $closingAtomic, 'currency' => 'IDR', 'currency_scale' => 0,
        'evidence_mode' => 'OPERATOR_OBSERVED_CLOSING_CASH', 'correlation_id' => 'closing-correlation-'.$suffix,
        'recorded_at_unix' => 200,
    ]);
    if ($reviewOutcome !== null) {
        $variance = $closingAtomic - 1000;
        $connection->table('oneqay_pos_cash_variance_review_decision_evidence')->insert([
            'tenant_id' => $tenant, 'review_evidence_id' => 'review-'.$suffix, 'shift_id' => $shift,
            'opening_cash_evidence_id' => $opening, 'closing_cash_evidence_id' => $closing,
            'explanation_actor_identity_id' => $explanationActor, 'reviewer_actor_identity_id' => $reviewerActor,
            'organization_id' => $organization, 'outlet_id' => $outlet, 'cutoff_at_unix' => 200,
            'expected_cash_atomic' => 1000, 'observed_closing_cash_atomic' => $closingAtomic,
            'variance_atomic' => $variance, 'variance_direction' => $variance > 0 ? 'OVER' : 'SHORT',
            'currency' => 'IDR', 'currency_scale' => 0, 'review_outcome' => $reviewOutcome,
        ]);
    }
    return [$outlet, $device, $shift];
};

$repository = new LaravelCloseShiftRepository(
    $connection,
    new LaravelExpectedCashSnapshotReader($connection),
    new DeriveCashVariance(),
    new FinalShiftCloseAuthorizationPolicy(),
    true,
    'ci',
    true,
);

[$matchOutlet, $matchDevice, $matchShift] = $seed('match', 'identity-opener', 1000);
$matchContext = $makeContext($matchOutlet, $matchDevice);
$matchCommand = new CloseShiftCommand('close-operation-match');
$match = $connection->transaction(fn () => $repository->close($matchContext, $matchCommand, 'close-correlation-match', 220), 1);
$assert($match->varianceDirection() === 'MATCH' && $match->reviewEvidenceId() === null, 'MATCH close without review');
$assert($connection->table('oneqay_pos_shifts')->where('shift_id', $matchShift)->value('active_slot') === null, 'MATCH active slot released');
$replay = $connection->transaction(fn () => $repository->close($matchContext, $matchCommand, 'different-correlation-replay', 999), 1);
$assert($replay->evidenceId() === $match->evidenceId(), 'exact operation replay returns durable evidence');
$assert($replay->closedAtUnix() === 220, 'replay preserves original close timestamp');
$assert($connection->table('oneqay_pos_shift_close_evidence')->where('shift_id', $matchShift)->count() === 1, 'replay does not duplicate close evidence');
$expectViolation(
    fn () => $connection->transaction(fn () => $repository->close($matchContext, new CloseShiftCommand('close-operation-second'), 'close-correlation-second', 230), 1),
    'different operation cannot close already closed shift',
);

[$sameOutlet, $sameDevice, $sameShift] = $seed('same-opener', $closer, 1000);
$expectViolation(
    fn () => $connection->transaction(fn () => $repository->close($makeContext($sameOutlet, $sameDevice), new CloseShiftCommand('close-operation-same-opener'), 'close-correlation-same-opener', 220), 1),
    'closer equal to opener',
);
$assert((int) $connection->table('oneqay_pos_shifts')->where('shift_id', $sameShift)->value('active_slot') === 1, 'denied close keeps active shift');

[$rejectedOutlet, $rejectedDevice] = $seed('rejected', 'identity-opener-r', 1100, 'REVIEW_REJECTED');
$expectViolation(
    fn () => $connection->transaction(fn () => $repository->close($makeContext($rejectedOutlet, $rejectedDevice), new CloseShiftCommand('close-operation-rejected'), 'close-correlation-rejected', 220), 1),
    'nonzero variance with rejected review',
);

[$acceptedOutlet, $acceptedDevice, $acceptedShift] = $seed('accepted', 'identity-opener-a', 1100, 'REVIEW_ACCEPTED');
$accepted = $connection->transaction(fn () => $repository->close($makeContext($acceptedOutlet, $acceptedDevice), new CloseShiftCommand('close-operation-accepted'), 'close-correlation-accepted', 220), 1);
$assert($accepted->varianceDirection() === 'OVER' && $accepted->varianceAtomic() === 100, 'accepted OVER variance derived');
$assert($accepted->reviewOutcome() === 'REVIEW_ACCEPTED' && $accepted->reviewEvidenceId() === 'review-accepted', 'accepted review bound');
$assert($connection->table('oneqay_pos_shifts')->where('shift_id', $acceptedShift)->value('active_slot') === null, 'accepted nonzero close releases active slot');

[$reviewerOutlet, $reviewerDevice] = $seed('closer-reviewer', 'identity-opener-b', 1100, 'REVIEW_ACCEPTED', 'identity-explainer-b', $closer);
$expectViolation(
    fn () => $connection->transaction(fn () => $repository->close($makeContext($reviewerOutlet, $reviewerDevice), new CloseShiftCommand('close-operation-reviewer'), 'close-correlation-reviewer', 220), 1),
    'closer equal to reviewer',
);

[$timeOutlet, $timeDevice] = $seed('time', 'identity-opener-time', 1000);
$expectViolation(
    fn () => $connection->transaction(fn () => $repository->close($makeContext($timeOutlet, $timeDevice), new CloseShiftCommand('close-operation-time'), 'close-correlation-time', 199), 1),
    'close timestamp before cutoff',
);

@unlink($db);
@rmdir($dir);

echo "Sprint95 Final Shift Close transactional lifecycle regression passed.\n";
