<?php

declare(strict_types=1);

use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\CashVarianceReviewDecisionCommand;
use App\Application\Pos\CashVarianceReviewDecisionRepository;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\RecordCashVarianceReviewDecision;
use App\Application\Pos\ShiftOpeningClock;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use App\Infrastructure\Pos\LaravelCashVarianceReviewDecisionRepository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('q', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint79 review decision regression failed: '.$case);
    }
};

$deny = static function (callable $operation, string $case) use ($assert): void {
    $rejected = false;
    try {
        $operation();
    } catch (Throwable) {
        $rejected = true;
    }
    $assert($rejected, $case.' accepted');
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
    .DIRECTORY_SEPARATOR.'oneqay-s79-review-decision-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'review-decision.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 's79_review_decision');
$app['config']->set('database.connections.s79_review_decision', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $db,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);
$app['config']->set('database.oneqay_persistence_enabled', true);
$app['config']->set('oneqay.runtime_class', 'ci');

$manager = $app->make('db');
$manager->purge('s79_review_decision');
$manager->setDefaultConnection('s79_review_decision');
$connection = $manager->connection('s79_review_decision');
$connection->getPdo();
$connection->statement('PRAGMA foreign_keys = ON');

$migrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($migrations);
$assert(count($migrations) === 26, 'exact migration count through #26');
for ($index = 1; $index <= 26; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $assert(
        count(array_filter(
            $migrations,
            static fn (string $file): bool => str_starts_with($file, $prefix),
        )) === 1,
        'migration #'.$index.' exact',
    );
}
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$assert(
    ! $app->bound(CashVarianceReviewDecisionRepository::class),
    'runtime repository binding unexpectedly published',
);

$fixtures = [
    'alpha' => [
        'tenant' => 'tenant-alpha',
        'organization' => 'organization-alpha',
        'outlet' => 'outlet-alpha',
        'device' => 'device-alpha',
        'explainer' => 'explainer-alpha',
        'reviewer' => 'reviewer-alpha',
        'shift' => str_repeat('a', 32),
        'opening' => 'opening-alpha-evidence-000000001',
        'closing' => 'closing-alpha-evidence-000000001',
        'explanation' => 'varexp-alpha-evidence-000000001',
        'expected' => 900,
        'observed' => 1000,
        'variance' => 100,
        'direction' => CashVarianceResult::DIRECTION_OVER,
        'cutoff' => 2000,
    ],
    'beta' => [
        'tenant' => 'tenant-beta',
        'organization' => 'organization-beta',
        'outlet' => 'outlet-beta',
        'device' => 'device-beta',
        'explainer' => 'explainer-beta',
        'reviewer' => 'reviewer-beta',
        'shift' => str_repeat('b', 32),
        'opening' => 'opening-beta-evidence-0000000002',
        'closing' => 'closing-beta-evidence-0000000002',
        'explanation' => 'varexp-beta-evidence-0000000002',
        'expected' => 1100,
        'observed' => 1000,
        'variance' => -100,
        'direction' => CashVarianceResult::DIRECTION_SHORT,
        'cutoff' => 3000,
    ],
];

foreach ($fixtures as $name => $fixture) {
    $connection->table('oneqay_tenants')->insert(['id' => $fixture['tenant']]);
    $connection->table('oneqay_organizations')->insert([
        'tenant_id' => $fixture['tenant'],
        'id' => $fixture['organization'],
    ]);

    foreach ([$fixture['explainer'], $fixture['reviewer']] as $identity) {
        $connection->table('oneqay_identities')->insert([
            'tenant_id' => $fixture['tenant'],
            'id' => $identity,
        ]);
        $connection->table('oneqay_identity_organizations')->insert([
            'tenant_id' => $fixture['tenant'],
            'identity_id' => $identity,
            'organization_id' => $fixture['organization'],
        ]);
    }

    $connection->table('oneqay_outlets')->insert([
        'tenant_id' => $fixture['tenant'],
        'id' => $fixture['outlet'],
        'organization_id' => $fixture['organization'],
    ]);
    $connection->table('oneqay_devices')->insert([
        'tenant_id' => $fixture['tenant'],
        'id' => $fixture['device'],
        'organization_id' => $fixture['organization'],
        'outlet_id' => $fixture['outlet'],
    ]);
    $connection->table('oneqay_pos_shifts')->insert([
        'tenant_id' => $fixture['tenant'],
        'shift_id' => $fixture['shift'],
        'operation_id' => 'shift-operation-'.$name,
        'payload_fingerprint' => hash('sha256', $fixture['tenant'].'|shift'),
        'actor_identity_id' => $fixture['explainer'],
        'organization_id' => $fixture['organization'],
        'outlet_id' => $fixture['outlet'],
        'device_id' => $fixture['device'],
        'active_slot' => 1,
        'correlation_id' => 'shift-correlation-'.$name,
        'opened_at_unix' => 1000,
    ]);
    $connection->table('oneqay_pos_shift_opening_cash_evidence')->insert([
        'tenant_id' => $fixture['tenant'],
        'evidence_id' => $fixture['opening'],
        'operation_id' => 'opening-operation-'.$name,
        'payload_fingerprint' => hash('sha256', $fixture['tenant'].'|opening'),
        'shift_id' => $fixture['shift'],
        'actor_identity_id' => $fixture['explainer'],
        'organization_id' => $fixture['organization'],
        'outlet_id' => $fixture['outlet'],
        'device_id' => $fixture['device'],
        'opening_cash_atomic' => 100,
        'currency' => 'IDR',
        'currency_scale' => 0,
        'evidence_mode' => 'OPERATOR_DECLARED_OPENING_CASH',
        'correlation_id' => 'opening-correlation-'.$name,
        'recorded_at_unix' => 1100,
    ]);
    $connection->table('oneqay_pos_shift_closing_cash_evidence')->insert([
        'tenant_id' => $fixture['tenant'],
        'evidence_id' => $fixture['closing'],
        'operation_id' => 'closing-operation-'.$name,
        'payload_fingerprint' => hash('sha256', $fixture['tenant'].'|closing'),
        'shift_id' => $fixture['shift'],
        'opening_cash_evidence_id' => $fixture['opening'],
        'actor_identity_id' => $fixture['explainer'],
        'organization_id' => $fixture['organization'],
        'outlet_id' => $fixture['outlet'],
        'device_id' => $fixture['device'],
        'closing_cash_atomic' => $fixture['observed'],
        'currency' => 'IDR',
        'currency_scale' => 0,
        'evidence_mode' => 'OPERATOR_OBSERVED_CLOSING_CASH',
        'correlation_id' => 'closing-correlation-'.$name,
        'recorded_at_unix' => $fixture['cutoff'],
    ]);
    $connection->table('oneqay_pos_cash_variance_explanation_evidence')->insert([
        'tenant_id' => $fixture['tenant'],
        'evidence_id' => $fixture['explanation'],
        'operation_id' => 'variance-explain-operation-'.$name,
        'payload_fingerprint' => hash('sha256', 'authoritative-'.$name.'-explanation'),
        'shift_id' => $fixture['shift'],
        'opening_cash_evidence_id' => $fixture['opening'],
        'closing_cash_evidence_id' => $fixture['closing'],
        'actor_identity_id' => $fixture['explainer'],
        'organization_id' => $fixture['organization'],
        'outlet_id' => $fixture['outlet'],
        'cutoff_at_unix' => $fixture['cutoff'],
        'expected_cash_atomic' => $fixture['expected'],
        'observed_closing_cash_atomic' => $fixture['observed'],
        'variance_atomic' => $fixture['variance'],
        'variance_direction' => $fixture['direction'],
        'currency' => 'IDR',
        'currency_scale' => 0,
        'explanation_text' => 'Authoritative '.$name.' variance explanation.',
        'correlation_id' => 'variance-explain-correlation-'.$name,
        'recorded_at_unix' => 4000,
    ]);
}

$variance = static fn (array $fixture): CashVarianceResult => new CashVarianceResult(
    $fixture['tenant'],
    $fixture['organization'],
    $fixture['outlet'],
    $fixture['shift'],
    $fixture['opening'],
    $fixture['closing'],
    $fixture['cutoff'],
    $fixture['expected'],
    $fixture['observed'],
    $fixture['variance'],
    $fixture['direction'],
    'IDR',
    0,
);

$setContext = static function (
    array $fixture,
    string $actor,
    ?string $organization = null,
    ?string $outlet = null,
) use ($app): OrganizationalContextStore {
    $app->forgetScopedInstances();
    $contexts = $app->make(OrganizationalContextStore::class);
    $contexts->setVerified(new VerifiedOrganizationalContext(
        PlatformIdentityId::fromString($actor),
        TenantId::fromString($fixture['tenant']),
        OrganizationId::fromString($organization ?? $fixture['organization']),
        OutletId::fromString($outlet ?? $fixture['outlet']),
        DeviceId::fromString($fixture['device']),
    ));

    return $contexts;
};

$makeService = static function (
    OrganizationalContextStore $contexts,
    Connection $connection,
    int $now,
    bool $persistenceEnabled = true,
    string $runtimeClass = 'ci',
    bool $featureEnabled = true,
): RecordCashVarianceReviewDecision {
    $repository = new LaravelCashVarianceReviewDecisionRepository(
        $connection,
        $persistenceEnabled,
        $runtimeClass,
        $featureEnabled,
    );
    $transaction = new LaravelPersistenceTransaction(
        $connection,
        $persistenceEnabled,
        $runtimeClass,
    );
    $clock = new class($now) implements ShiftOpeningClock {
        public function __construct(private int $now) {}
        public function nowUnix(): int { return $this->now; }
    };

    return new RecordCashVarianceReviewDecision(
        $repository,
        $contexts,
        $transaction,
        $clock,
    );
};

$alpha = $fixtures['alpha'];
$beta = $fixtures['beta'];
$alphaVariance = $variance($alpha);
$betaVariance = $variance($beta);

$alphaShiftBefore = (array) $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', $alpha['tenant'])
    ->where('shift_id', $alpha['shift'])
    ->first();

$alphaReviewerContext = $setContext($alpha, $alpha['reviewer']);

$deny(
    fn () => $makeService($setContext($alpha, $alpha['explainer']), $connection, 4900)->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-self-0001',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-self-0001',
    ),
    'self-review',
);

$connection->table('oneqay_pos_cash_variance_explanation_evidence')
    ->where('tenant_id', $alpha['tenant'])
    ->where('evidence_id', $alpha['explanation'])
    ->update(['expected_cash_atomic' => 901]);
$deny(
    fn () => $makeService($alphaReviewerContext, $connection, 4901)->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-wrong-subject-0001',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-wrong-subject-0001',
    ),
    'wrong explanation subject',
);
$connection->table('oneqay_pos_cash_variance_explanation_evidence')
    ->where('tenant_id', $alpha['tenant'])
    ->where('evidence_id', $alpha['explanation'])
    ->update(['expected_cash_atomic' => 900]);

$deny(
    fn () => $makeService(
        $setContext($alpha, $alpha['reviewer'], $beta['organization'], $alpha['outlet']),
        $connection,
        4902,
    )->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-wrong-org-0001',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-wrong-org-0001',
    ),
    'cross-organization context',
);
$deny(
    fn () => $makeService(
        $setContext($alpha, $alpha['reviewer'], $alpha['organization'], $beta['outlet']),
        $connection,
        4903,
    )->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-wrong-outlet-0001',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-wrong-outlet-0001',
    ),
    'cross-outlet context',
);

$wrongSign = new CashVarianceResult(
    $alpha['tenant'],
    $alpha['organization'],
    $alpha['outlet'],
    $alpha['shift'],
    $alpha['opening'],
    $alpha['closing'],
    $alpha['cutoff'],
    $alpha['expected'],
    $alpha['observed'],
    100,
    CashVarianceResult::DIRECTION_SHORT,
    'IDR',
    0,
);
$deny(
    fn () => $makeService($alphaReviewerContext, $connection, 4904)->record(
        $wrongSign,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-sign-0001',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-sign-0001',
    ),
    'malformed sign direction',
);

$match = new CashVarianceResult(
    $alpha['tenant'],
    $alpha['organization'],
    $alpha['outlet'],
    $alpha['shift'],
    $alpha['opening'],
    $alpha['closing'],
    $alpha['cutoff'],
    1000,
    1000,
    0,
    CashVarianceResult::DIRECTION_MATCH,
    'IDR',
    0,
);
$deny(
    fn () => $makeService($alphaReviewerContext, $connection, 4905)->record(
        $match,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-match-0001',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-match-0001',
    ),
    'MATCH review',
);

$deny(
    fn () => new CashVarianceReviewDecisionCommand(
        'variance-review-operation-alias-0001',
        $alpha['explanation'],
        'APPROVE',
    ),
    'unknown outcome alias',
);

$deny(
    fn () => $makeService($alphaReviewerContext, $connection, 4906)->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-missing-0001',
            'varexp-missing-evidence-000000001',
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-missing-0001',
    ),
    'missing explanation',
);
$deny(
    fn () => $makeService($alphaReviewerContext, $connection, 4907)->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-cross-0001',
            $beta['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-cross-0001',
    ),
    'cross-tenant explanation',
);
$deny(
    fn () => $makeService($alphaReviewerContext, $connection, 0)->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-clock-0001',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-clock-0001',
    ),
    'non-positive reviewed timestamp',
);

$sharedOperation = 'variance-review-operation-shared-0001';
$alphaCommand = new CashVarianceReviewDecisionCommand(
    $sharedOperation,
    $alpha['explanation'],
    CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
);
$alphaResult = $makeService($alphaReviewerContext, $connection, 5000)->record(
    $alphaVariance,
    $alphaCommand,
    'variance-review-correlation-alpha-0001',
);

$assert($alphaResult->reviewerActorIdentityId() === $alpha['reviewer'], 'authoritative reviewer identity');
$assert($alphaResult->explanationActorIdentityId() === $alpha['explainer'], 'authoritative explanation author identity');
$assert($alphaResult->reviewOutcome() === CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED, 'REVIEW_ACCEPTED durable round-trip');
$assert($alphaResult->varianceAtomic() === 100 && $alphaResult->varianceDirection() === CashVarianceResult::DIRECTION_OVER, 'OVER subject preserved');
$assert(
    $alphaResult->explanationPayloadFingerprint() === hash('sha256', 'authoritative-alpha-explanation'),
    'exact explanation payload fingerprint binding',
);

$replay = $makeService($alphaReviewerContext, $connection, 9999)->record(
    $alphaVariance,
    $alphaCommand,
    'variance-review-correlation-alpha-retry',
);
$assert($replay->reviewEvidenceId() === $alphaResult->reviewEvidenceId(), 'exact replay evidence identity');
$assert($replay->reviewedAtUnix() === 5000, 'exact replay original reviewed timestamp');
$assert($replay->correlationId() === 'variance-review-correlation-alpha-0001', 'exact replay original correlation');

$deny(
    fn () => $makeService($alphaReviewerContext, $connection, 5001)->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            $sharedOperation,
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_REJECTED,
        ),
        'variance-review-conflict-0001',
    ),
    'conflicting operation replay',
);
$deny(
    fn () => $makeService($alphaReviewerContext, $connection, 5002)->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-alpha-0002',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-second-0002',
    ),
    'competing independent decision',
);

$betaReviewerContext = $setContext($beta, $beta['reviewer']);
$connection->table('oneqay_pos_cash_variance_explanation_evidence')
    ->where('tenant_id', $beta['tenant'])
    ->where('evidence_id', $beta['explanation'])
    ->update(['payload_fingerprint' => 'malformed']);
$deny(
    fn () => $makeService($betaReviewerContext, $connection, 5999)->record(
        $betaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-operation-fingerprint-0001',
            $beta['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_REJECTED,
        ),
        'variance-review-fingerprint-0001',
    ),
    'malformed explanation fingerprint',
);
$connection->table('oneqay_pos_cash_variance_explanation_evidence')
    ->where('tenant_id', $beta['tenant'])
    ->where('evidence_id', $beta['explanation'])
    ->update(['payload_fingerprint' => hash('sha256', 'authoritative-beta-explanation')]);

$betaResult = $makeService($betaReviewerContext, $connection, 6000)->record(
    $betaVariance,
    new CashVarianceReviewDecisionCommand(
        $sharedOperation,
        $beta['explanation'],
        CashVarianceReviewDecisionCommand::REVIEW_REJECTED,
    ),
    'variance-review-correlation-beta-0001',
);
$assert($betaResult->reviewOutcome() === CashVarianceReviewDecisionCommand::REVIEW_REJECTED, 'REVIEW_REJECTED durable round-trip');
$assert($betaResult->varianceAtomic() === -100 && $betaResult->varianceDirection() === CashVarianceResult::DIRECTION_SHORT, 'SHORT subject preserved');
$assert($alphaResult->operationId() === $sharedOperation && $betaResult->operationId() === $sharedOperation, 'cross-tenant operation isolation');
$assert(
    $connection->table('oneqay_pos_cash_variance_review_decision_evidence')->count() === 2,
    'exact reviewer-decision evidence count',
);

$alphaShiftAfter = (array) $connection->table('oneqay_pos_shifts')
    ->where('tenant_id', $alpha['tenant'])
    ->where('shift_id', $alpha['shift'])
    ->first();
$assert($alphaShiftBefore === $alphaShiftAfter, 'no shift-state mutation');
$assert(
    ! in_array(
        'close_state',
        $connection->getSchemaBuilder()->getColumnListing('oneqay_pos_cash_variance_review_decision_evidence'),
        true,
    ),
    'no close authority side effect',
);

$execution = PosExecutionContext::fromVerified($alphaReviewerContext->current());
$deny(
    fn () => (new LaravelCashVarianceReviewDecisionRepository($connection, false, 'ci', true))
        ->resolveExplanation($execution, $alphaVariance, $alpha['explanation']),
    'persistence disabled',
);
$deny(
    fn () => (new LaravelCashVarianceReviewDecisionRepository($connection, true, 'production', true))
        ->resolveExplanation($execution, $alphaVariance, $alpha['explanation']),
    'Production runtime',
);
$deny(
    fn () => (new LaravelCashVarianceReviewDecisionRepository($connection, true, 'ci', false))
        ->resolveExplanation($execution, $alphaVariance, $alpha['explanation']),
    'source feature disabled',
);

$adapterSource = (string) file_get_contents(
    __DIR__.'/../app/Infrastructure/Pos/LaravelCashVarianceReviewDecisionRepository.php',
);
$assert(! str_contains($adapterSource, '->update('), 'adapter update path absent');
$assert(! str_contains($adapterSource, '->delete('), 'adapter delete path absent');

@unlink($db);
@rmdir($workspace);

echo "Sprint79 durable cash variance reviewer decision regression: PASS\n";
