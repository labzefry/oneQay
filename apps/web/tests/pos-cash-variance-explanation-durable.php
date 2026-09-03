<?php

declare(strict_types=1);

use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Pos\CashVarianceExplanationCommand;
use App\Application\Pos\CashVarianceExplanationRepository;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\DeriveCashVariance;
use App\Application\Pos\ExpectedCashResult;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\RecordCashVarianceExplanation;
use App\Application\Pos\ShiftClosingCashResult;
use App\Application\Pos\ShiftOpeningClock;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Pos\Money;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use App\Infrastructure\Pos\LaravelCashVarianceExplanationRepository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use InvalidArgumentException;

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
        throw new RuntimeException('Sprint70 cash variance explanation regression failed: '.$case);
    }
};

$deny = static function (callable $operation, string $case) use ($assert): void {
    try {
        $operation();
        $assert(false, $case.' accepted');
    } catch (Throwable) {
    }
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
    .DIRECTORY_SEPARATOR.'oneqay-s70-variance-explanation-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'variance-explanation.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 's70_variance_explanation');
$app['config']->set('database.connections.s70_variance_explanation', [
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
$manager->purge('s70_variance_explanation');
$manager->setDefaultConnection('s70_variance_explanation');
$connection = $manager->connection('s70_variance_explanation');
$connection->getPdo();
$connection->statement('PRAGMA foreign_keys = ON');

$migrations = array_values(array_filter(
    scandir(__DIR__.'/../database/migrations') ?: [],
    static fn (string $file): bool => str_ends_with($file, '.php'),
));
sort($migrations);
$assert(count($migrations) === 25, 'exact migration count through #25');
for ($index = 1; $index <= 25; $index++) {
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
    ! $app->bound(CashVarianceExplanationRepository::class),
    'runtime repository binding unexpectedly published',
);

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
foreach ([
    ['tenant-alpha', 'explainer-alpha'],
    ['tenant-beta', 'explainer-beta'],
] as [$tenant, $identity]) {
    $connection->table('oneqay_identities')->insert([
        'tenant_id' => $tenant,
        'id' => $identity,
    ]);
}
foreach ([
    ['tenant-alpha', 'organization-alpha'],
    ['tenant-beta', 'organization-beta'],
] as [$tenant, $organization]) {
    $connection->table('oneqay_organizations')->insert([
        'tenant_id' => $tenant,
        'id' => $organization,
    ]);
}
foreach ([
    ['tenant-alpha', 'explainer-alpha', 'organization-alpha'],
    ['tenant-beta', 'explainer-beta', 'organization-beta'],
] as [$tenant, $identity, $organization]) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => $tenant,
        'identity_id' => $identity,
        'organization_id' => $organization,
    ]);
}
foreach ([
    ['tenant-alpha', 'outlet-alpha', 'organization-alpha'],
    ['tenant-beta', 'outlet-beta', 'organization-beta'],
] as [$tenant, $outlet, $organization]) {
    $connection->table('oneqay_outlets')->insert([
        'tenant_id' => $tenant,
        'id' => $outlet,
        'organization_id' => $organization,
    ]);
}
foreach ([
    ['tenant-alpha', 'device-alpha', 'organization-alpha', 'outlet-alpha'],
    ['tenant-beta', 'device-beta', 'organization-beta', 'outlet-beta'],
] as [$tenant, $device, $organization, $outlet]) {
    $connection->table('oneqay_devices')->insert([
        'tenant_id' => $tenant,
        'id' => $device,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
    ]);
}

$alphaShift = str_repeat('a', 32);
$betaShift = str_repeat('b', 32);
foreach ([
    ['tenant-alpha', $alphaShift, 'shift-operation-alpha', 'explainer-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha', 'shift-correlation-alpha'],
    ['tenant-beta', $betaShift, 'shift-operation-beta', 'explainer-beta', 'organization-beta', 'outlet-beta', 'device-beta', 'shift-correlation-beta'],
] as [$tenant, $shift, $operation, $actor, $organization, $outlet, $device, $correlation]) {
    $connection->table('oneqay_pos_shifts')->insert([
        'tenant_id' => $tenant,
        'shift_id' => $shift,
        'operation_id' => $operation,
        'payload_fingerprint' => hash('sha256', $tenant.'|'.$shift),
        'actor_identity_id' => $actor,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
        'device_id' => $device,
        'active_slot' => 1,
        'correlation_id' => $correlation,
        'opened_at_unix' => 1000,
    ]);
}

$alphaOpening = 'opening-alpha-evidence-000000001';
$alphaClosing = 'closing-alpha-evidence-000000001';
$betaOpening = 'opening-beta-evidence-0000000002';
$betaClosing = 'closing-beta-evidence-0000000002';

foreach ([
    ['tenant-alpha', $alphaOpening, 'opening-operation-alpha', $alphaShift, 'explainer-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha', 100, 'IDR', 0, 'opening-correlation-alpha', 1100],
    ['tenant-beta', $betaOpening, 'opening-operation-beta', $betaShift, 'explainer-beta', 'organization-beta', 'outlet-beta', 'device-beta', 100, 'IDR', 0, 'opening-correlation-beta', 1200],
] as [$tenant, $evidence, $operation, $shift, $actor, $organization, $outlet, $device, $atomic, $currency, $scale, $correlation, $recorded]) {
    $connection->table('oneqay_pos_shift_opening_cash_evidence')->insert([
        'tenant_id' => $tenant,
        'evidence_id' => $evidence,
        'operation_id' => $operation,
        'payload_fingerprint' => hash('sha256', $tenant.'|'.$evidence),
        'shift_id' => $shift,
        'actor_identity_id' => $actor,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
        'device_id' => $device,
        'opening_cash_atomic' => $atomic,
        'currency' => $currency,
        'currency_scale' => $scale,
        'evidence_mode' => 'OPERATOR_DECLARED_OPENING_CASH',
        'correlation_id' => $correlation,
        'recorded_at_unix' => $recorded,
    ]);
}

foreach ([
    ['tenant-alpha', $alphaClosing, 'closing-operation-alpha', $alphaOpening, $alphaShift, 'explainer-alpha', 'organization-alpha', 'outlet-alpha', 'device-alpha', 1000, 'IDR', 0, 'closing-correlation-alpha', 2000],
    ['tenant-beta', $betaClosing, 'closing-operation-beta', $betaOpening, $betaShift, 'explainer-beta', 'organization-beta', 'outlet-beta', 'device-beta', 1000, 'IDR', 0, 'closing-correlation-beta', 3000],
] as [$tenant, $evidence, $operation, $opening, $shift, $actor, $organization, $outlet, $device, $atomic, $currency, $scale, $correlation, $recorded]) {
    $connection->table('oneqay_pos_shift_closing_cash_evidence')->insert([
        'tenant_id' => $tenant,
        'evidence_id' => $evidence,
        'operation_id' => $operation,
        'payload_fingerprint' => hash('sha256', $tenant.'|'.$evidence),
        'shift_id' => $shift,
        'opening_cash_evidence_id' => $opening,
        'actor_identity_id' => $actor,
        'organization_id' => $organization,
        'outlet_id' => $outlet,
        'device_id' => $device,
        'closing_cash_atomic' => $atomic,
        'currency' => $currency,
        'currency_scale' => $scale,
        'evidence_mode' => 'OPERATOR_OBSERVED_CLOSING_CASH',
        'correlation_id' => $correlation,
        'recorded_at_unix' => $recorded,
    ]);
}

$deriveVariance = new DeriveCashVariance();

$alphaExpected = new ExpectedCashResult(
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    $alphaShift,
    $alphaOpening,
    $alphaClosing,
    2000,
    Money::fromAtomicUnits(900, 'IDR', 0),
);
$alphaClosingResult = new ShiftClosingCashResult(
    $alphaClosing,
    $alphaOpening,
    $alphaShift,
    'closing-operation-alpha',
    'tenant-alpha',
    'outlet-alpha',
    'device-alpha',
    Money::fromAtomicUnits(1000, 'IDR', 0),
    'OPERATOR_OBSERVED_CLOSING_CASH',
    'closing-correlation-alpha',
    2000,
);
$alphaVariance = $deriveVariance->derive($alphaExpected, $alphaClosingResult);
$assert($alphaVariance->direction() === CashVarianceResult::DIRECTION_OVER, 'alpha OVER prerequisite');
$assert($alphaVariance->varianceAtomic() === 100, 'alpha variance prerequisite');

$betaExpected = new ExpectedCashResult(
    'tenant-beta',
    'organization-beta',
    'outlet-beta',
    $betaShift,
    $betaOpening,
    $betaClosing,
    3000,
    Money::fromAtomicUnits(1100, 'IDR', 0),
);
$betaClosingResult = new ShiftClosingCashResult(
    $betaClosing,
    $betaOpening,
    $betaShift,
    'closing-operation-beta',
    'tenant-beta',
    'outlet-beta',
    'device-beta',
    Money::fromAtomicUnits(1000, 'IDR', 0),
    'OPERATOR_OBSERVED_CLOSING_CASH',
    'closing-correlation-beta',
    3000,
);
$betaVariance = $deriveVariance->derive($betaExpected, $betaClosingResult);
$assert($betaVariance->direction() === CashVarianceResult::DIRECTION_SHORT, 'beta SHORT prerequisite');
$assert($betaVariance->varianceAtomic() === -100, 'beta variance prerequisite');

$setContext = static function (
    string $identity,
    string $tenant,
    string $organization,
    string $outlet,
    string $device,
) use ($app): OrganizationalContextStore {
    $app->forgetScopedInstances();
    $contexts = $app->make(OrganizationalContextStore::class);
    $contexts->setVerified(new VerifiedOrganizationalContext(
        PlatformIdentityId::fromString($identity),
        TenantId::fromString($tenant),
        OrganizationId::fromString($organization),
        OutletId::fromString($outlet),
        DeviceId::fromString($device),
    ));

    return $contexts;
};

$makeService = static function (
    OrganizationalContextStore $contexts,
    Connection $connection,
    int $recordedAtUnix,
    bool $persistenceEnabled = true,
    string $runtimeClass = 'ci',
    bool $featureEnabled = true,
): RecordCashVarianceExplanation {
    $repository = new LaravelCashVarianceExplanationRepository(
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
    $clock = new class($recordedAtUnix) implements ShiftOpeningClock {
        public function __construct(private int $recordedAtUnix) {}
        public function nowUnix(): int { return $this->recordedAtUnix; }
    };

    return new RecordCashVarianceExplanation(
        $repository,
        $contexts,
        $transaction,
        $clock,
    );
};

$openingAlphaBefore = (array) $connection->table('oneqay_pos_shift_opening_cash_evidence')
    ->where('tenant_id', 'tenant-alpha')
    ->where('evidence_id', $alphaOpening)
    ->first();
$closingAlphaBefore = (array) $connection->table('oneqay_pos_shift_closing_cash_evidence')
    ->where('tenant_id', 'tenant-alpha')
    ->where('evidence_id', $alphaClosing)
    ->first();

$contexts = $setContext(
    'explainer-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    'device-alpha',
);
$service = $makeService($contexts, $connection, 4000);
$command = new CashVarianceExplanationCommand(
    'variance-explain-operation-alpha-0001',
    "  Drawer recount confirms operator cash surplus.\r\nSecond count matched.  ",
);
$result = $service->record(
    $alphaVariance,
    $command,
    'variance-explain-correlation-alpha-0001',
);

$assert($result->tenantId() === 'tenant-alpha', 'alpha tenant');
$assert($result->organizationId() === 'organization-alpha', 'alpha organization');
$assert($result->outletId() === 'outlet-alpha', 'alpha outlet');
$assert($result->shiftId() === $alphaShift, 'alpha shift');
$assert($result->openingCashEvidenceId() === $alphaOpening, 'alpha opening evidence');
$assert($result->closingCashEvidenceId() === $alphaClosing, 'alpha closing evidence');
$assert($result->actorIdentityId() === 'explainer-alpha', 'alpha actor attribution');
$assert($result->cutoffAtUnix() === 2000, 'alpha cutoff');
$assert($result->expectedCashAtomic() === 900, 'alpha expected');
$assert($result->observedClosingCashAtomic() === 1000, 'alpha observed');
$assert($result->varianceAtomic() === 100, 'alpha signed variance');
$assert($result->varianceDirection() === CashVarianceResult::DIRECTION_OVER, 'alpha direction');
$assert($result->currency() === 'IDR' && $result->currencyScale() === 0, 'alpha money metadata');
$assert(
    $result->explanationText() === "Drawer recount confirms operator cash surplus.\nSecond count matched.",
    'canonical explanation text',
);
$assert($result->recordedAtUnix() === 4000, 'alpha recorded time');
$assert(
    $connection->table('oneqay_pos_cash_variance_explanation_evidence')
        ->where('tenant_id', 'tenant-alpha')->count() === 1,
    'alpha explanation persisted exactly once',
);

$row = (array) $connection->table('oneqay_pos_cash_variance_explanation_evidence')
    ->where('tenant_id', 'tenant-alpha')
    ->where('evidence_id', $result->evidenceId())
    ->first();
$assert(($row['actor_identity_id'] ?? null) === 'explainer-alpha', 'stored actor');
$assert((int) ($row['expected_cash_atomic'] ?? -1) === 900, 'stored expected');
$assert((int) ($row['observed_closing_cash_atomic'] ?? -1) === 1000, 'stored observed');
$assert((int) ($row['variance_atomic'] ?? 0) === 100, 'stored signed variance');
$assert(($row['variance_direction'] ?? null) === 'OVER', 'stored direction');
$assert(($row['explanation_text'] ?? null) === $result->explanationText(), 'stored explanation');

$replayService = $makeService($contexts, $connection, 9999);
$replay = $replayService->record(
    $alphaVariance,
    $command,
    'variance-explain-correlation-alpha-retry',
);
$assert($replay->evidenceId() === $result->evidenceId(), 'exact replay evidence identity');
$assert($replay->correlationId() === 'variance-explain-correlation-alpha-0001', 'replay original correlation');
$assert($replay->recordedAtUnix() === 4000, 'replay original recorded time');
$assert(
    $connection->table('oneqay_pos_cash_variance_explanation_evidence')
        ->where('tenant_id', 'tenant-alpha')->count() === 1,
    'exact replay duplicated evidence',
);

$deny(
    fn () => $service->record(
        $alphaVariance,
        new CashVarianceExplanationCommand(
            'variance-explain-operation-alpha-0001',
            'Conflicting explanation payload.',
        ),
        'variance-explain-correlation-alpha-conflict',
    ),
    'conflicting operation replay',
);
$deny(
    fn () => $service->record(
        $alphaVariance,
        new CashVarianceExplanationCommand(
            'variance-explain-operation-alpha-0002',
            'Second authoritative explanation.',
        ),
        'variance-explain-correlation-alpha-second',
    ),
    'second authoritative explanation',
);

$matchVariance = new CashVarianceResult(
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    $alphaShift,
    $alphaOpening,
    $alphaClosing,
    2000,
    1000,
    1000,
    0,
    CashVarianceResult::DIRECTION_MATCH,
    'IDR',
    0,
);
$deny(
    fn () => $service->record(
        $matchVariance,
        new CashVarianceExplanationCommand(
            'variance-explain-operation-match-0001',
            'Match must not require explanation.',
        ),
        'variance-explain-correlation-match',
    ),
    'MATCH explanation',
);

$wrongSign = new CashVarianceResult(
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    $alphaShift,
    $alphaOpening,
    $alphaClosing,
    2000,
    900,
    1000,
    100,
    CashVarianceResult::DIRECTION_SHORT,
    'IDR',
    0,
);
$deny(
    fn () => $service->record(
        $wrongSign,
        new CashVarianceExplanationCommand(
            'variance-explain-operation-sign-0001',
            'Invalid sign/direction.',
        ),
        'variance-explain-correlation-sign',
    ),
    'sign direction mismatch',
);

$wrongMagnitude = new CashVarianceResult(
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    $alphaShift,
    $alphaOpening,
    $alphaClosing,
    2000,
    900,
    1000,
    99,
    CashVarianceResult::DIRECTION_OVER,
    'IDR',
    0,
);
$deny(
    fn () => $service->record(
        $wrongMagnitude,
        new CashVarianceExplanationCommand(
            'variance-explain-operation-magnitude-0001',
            'Invalid arithmetic magnitude.',
        ),
        'variance-explain-correlation-magnitude',
    ),
    'variance arithmetic mismatch',
);

try {
    new CashVarianceExplanationCommand('variance-explain-empty-0001', " \r\n ");
    $assert(false, 'empty explanation accepted');
} catch (InvalidArgumentException) {
}
try {
    new CashVarianceExplanationCommand(
        'variance-explain-too-long-0001',
        str_repeat('x', 4097),
    );
    $assert(false, 'oversized explanation accepted');
} catch (InvalidArgumentException) {
}

$deny(
    fn () => $makeService($contexts, $connection, 0)->record(
        $alphaVariance,
        new CashVarianceExplanationCommand(
            'variance-explain-operation-zero-clock',
            'Clock must be positive.',
        ),
        'variance-explain-correlation-zero-clock',
    ),
    'non-positive recorded clock',
);

$alphaExecution = PosExecutionContext::fromVerified($contexts->current());
$deny(
    fn () => (new LaravelCashVarianceExplanationRepository($connection, false, 'ci', true))->record(
        $alphaExecution,
        $alphaVariance,
        new CashVarianceExplanationCommand(
            'variance-explain-operation-disabled-persistence',
            'Persistence disabled test.',
        ),
        'variance-explain-correlation-disabled-persistence',
        5000,
    ),
    'persistence disabled adapter',
);
$deny(
    fn () => (new LaravelCashVarianceExplanationRepository($connection, true, 'ci', false))->record(
        $alphaExecution,
        $alphaVariance,
        new CashVarianceExplanationCommand(
            'variance-explain-operation-disabled-feature',
            'Feature disabled test.',
        ),
        'variance-explain-correlation-disabled-feature',
        5000,
    ),
    'source feature disabled adapter',
);
$deny(
    fn () => (new LaravelCashVarianceExplanationRepository($connection, true, 'production', true))->record(
        $alphaExecution,
        $alphaVariance,
        new CashVarianceExplanationCommand(
            'variance-explain-operation-production',
            'Production runtime denial test.',
        ),
        'variance-explain-correlation-production',
        5000,
    ),
    'Production runtime adapter',
);

$contexts = $setContext(
    'explainer-beta',
    'tenant-beta',
    'organization-beta',
    'outlet-beta',
    'device-beta',
);
$deny(
    fn () => $makeService($contexts, $connection, 6000)->record(
        $alphaVariance,
        new CashVarianceExplanationCommand(
            'variance-explain-operation-context-mismatch',
            'Cross-tenant context must fail.',
        ),
        'variance-explain-correlation-context-mismatch',
    ),
    'cross-tenant context mismatch',
);

$betaService = $makeService($contexts, $connection, 7000);
$beta = $betaService->record(
    $betaVariance,
    new CashVarianceExplanationCommand(
        'variance-explain-operation-alpha-0001',
        'Till recount confirms cash shortage after final observation.',
    ),
    'variance-explain-correlation-beta-0001',
);
$assert($beta->tenantId() === 'tenant-beta', 'beta tenant');
$assert($beta->varianceAtomic() === -100, 'beta signed SHORT');
$assert($beta->varianceDirection() === CashVarianceResult::DIRECTION_SHORT, 'beta SHORT direction');
$assert(
    $connection->table('oneqay_pos_cash_variance_explanation_evidence')
        ->where('tenant_id', 'tenant-alpha')
        ->where('operation_id', 'variance-explain-operation-alpha-0001')
        ->count() === 1,
    'alpha operation lost after beta reuse',
);
$assert(
    $connection->table('oneqay_pos_cash_variance_explanation_evidence')
        ->where('tenant_id', 'tenant-beta')
        ->where('operation_id', 'variance-explain-operation-alpha-0001')
        ->count() === 1,
    'cross-tenant operation reuse not isolated',
);

$openingAlphaAfter = (array) $connection->table('oneqay_pos_shift_opening_cash_evidence')
    ->where('tenant_id', 'tenant-alpha')
    ->where('evidence_id', $alphaOpening)
    ->first();
$closingAlphaAfter = (array) $connection->table('oneqay_pos_shift_closing_cash_evidence')
    ->where('tenant_id', 'tenant-alpha')
    ->where('evidence_id', $alphaClosing)
    ->first();
$assert($openingAlphaAfter === $openingAlphaBefore, 'explanation mutated opening evidence');
$assert($closingAlphaAfter === $closingAlphaBefore, 'explanation mutated closing evidence');
$assert($connection->table('oneqay_pos_sales')->count() === 0, 'explanation created or mutated sale');
$assert($connection->table('oneqay_pos_sale_cash_refunds')->count() === 0, 'explanation created or mutated refund');

$reflection = new ReflectionClass(App\Application\Pos\CashVarianceExplanationResult::class);
$assert($reflection->isReadOnly(), 'result is not readonly');
foreach (['approve', 'reject', 'waive', 'close', 'transition'] as $method) {
    $assert(! $reflection->hasMethod($method), 'result exposes lifecycle method '.$method);
}

$migration25 = require __DIR__.'/../database/migrations/0000_00_00_000025_create_pos_cash_variance_explanation_evidence_foundation.php';
try {
    $migration25->down();
    $assert(false, 'migration #25 rollback executed');
} catch (LogicException) {
}

$manager->disconnect('s70_variance_explanation');
$manager->purge('s70_variance_explanation');
@unlink($db);
@rmdir($workspace);

echo "Sprint70 JRN-010 cash variance explanation durable regression passed.\n";
