<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\DurableRolePermissionRepository;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Pos\CashVarianceExplanationResult;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\CashVarianceReviewDecisionCommand;
use App\Application\Pos\CashVarianceReviewDecisionRepository;
use App\Application\Pos\CashVarianceReviewDecisionResult;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\RecordCashVarianceReviewDecision;
use App\Application\Pos\ShiftOpeningClock;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
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

final class S80EventLog
{
    /** @var list<string> */
    public array $events = [];
}

final class S80AuthorizationRepository implements DurableRolePermissionRepository
{
    /** @var array<string, true> */
    private array $grants = [];

    /** @var list<string> */
    public array $requestedPermissions = [];

    public int $calls = 0;

    public function __construct(private readonly S80EventLog $log) {}

    public function allows(
        VerifiedOrganizationalContext $context,
        PermissionIdentifier $permission,
    ): bool {
        $this->calls++;
        $this->requestedPermissions[] = $permission->value();
        $this->log->events[] = 'authorization';

        $key = implode('|', [
            $context->identityId()->value(),
            $context->tenantId()->value(),
            $context->organizationId()->value(),
            $context->outletId()?->value() ?? '',
            $permission->value(),
        ]);

        return isset($this->grants[$key]);
    }

    public function grant(
        string $identity,
        string $tenant,
        string $organization,
        string $outlet,
        string $permission,
    ): void {
        $this->grants[implode('|', [
            $identity,
            $tenant,
            $organization,
            $outlet,
            $permission,
        ])] = true;
    }
}

final class S80Clock implements ShiftOpeningClock
{
    public int $calls = 0;

    public function __construct(
        private readonly S80EventLog $log,
        private readonly int $now,
    ) {}

    public function nowUnix(): int
    {
        $this->calls++;
        $this->log->events[] = 'clock';

        return $this->now;
    }
}

final class S80Transaction implements PersistenceTransaction
{
    public int $calls = 0;

    public function __construct(
        private readonly S80EventLog $log,
        private readonly Connection $connection,
    ) {}

    public function run(callable $operation): mixed
    {
        $this->calls++;
        $this->log->events[] = 'transaction';

        return $this->connection->transaction($operation);
    }
}

final class S80ReviewRepository implements CashVarianceReviewDecisionRepository
{
    public int $resolveCalls = 0;
    public int $recordCalls = 0;

    public function __construct(
        private readonly S80EventLog $log,
        private readonly LaravelCashVarianceReviewDecisionRepository $inner,
    ) {}

    public function resolveExplanation(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        string $cashVarianceExplanationEvidenceId,
    ): CashVarianceExplanationResult {
        $this->resolveCalls++;
        $this->log->events[] = 'resolve';

        return $this->inner->resolveExplanation(
            $context,
            $variance,
            $cashVarianceExplanationEvidenceId,
        );
    }

    public function record(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        CashVarianceExplanationResult $explanation,
        CashVarianceReviewDecisionCommand $command,
        string $correlationId,
        int $reviewedAtUnix,
    ): CashVarianceReviewDecisionResult {
        $this->recordCalls++;
        $this->log->events[] = 'record';

        return $this->inner->record(
            $context,
            $variance,
            $explanation,
            $command,
            $correlationId,
            $reviewedAtUnix,
        );
    }
}

$app = require __DIR__.'/../bootstrap/app.php';
$app->instance('request', Request::create('/'));
$app->make(Kernel::class)->bootstrap();

$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Reviewer authorization regression failed: '.$case);
    }
};

$deny = static function (callable $operation, string $case) use ($assert): Throwable {
    try {
        $operation();
    } catch (Throwable $exception) {
        return $exception;
    }

    $assert(false, $case.' accepted');

    throw new RuntimeException('Unreachable');
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
    .DIRECTORY_SEPARATOR.'oneqay-review-authorization-'.getmypid();
@mkdir($workspace, 0700, true);
$db = $workspace.DIRECTORY_SEPARATOR.'review-decision.sqlite';
$assert(touch($db), 'SQLite create');

$app['config']->set('database.default', 'review_authorization');
$app['config']->set('database.connections.review_authorization', [
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
$manager->purge('review_authorization');
$manager->setDefaultConnection('review_authorization');
$connection = $manager->connection('review_authorization');
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

$permission = PosPermission::recordCashVarianceReviewDecision();
$assert(
    $permission->value() === 'pos.shift.cash-variance-review-decision.record',
    'exact reviewer permission identifier',
);
$assert(
    PosPermission::RECORD_CASH_VARIANCE_REVIEW_DECISION === $permission->value(),
    'reviewer permission constant/accessor mismatch',
);
$assert(
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION !== $permission->value(),
    'explanation permission reused as reviewer permission',
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

$makeAuthorization = static function (
    S80EventLog $log,
    ?array $grant = null,
): S80AuthorizationRepository {
    $authorization = new S80AuthorizationRepository($log);

    if ($grant !== null) {
        $authorization->grant(
            $grant['identity'],
            $grant['tenant'],
            $grant['organization'],
            $grant['outlet'],
            $grant['permission'],
        );
    }

    return $authorization;
};

$reviewGrant = static fn (
    array $fixture,
    string $actor,
    ?string $outlet = null,
): array => [
    'identity' => $actor,
    'tenant' => $fixture['tenant'],
    'organization' => $fixture['organization'],
    'outlet' => $outlet ?? $fixture['outlet'],
    'permission' => PosPermission::RECORD_CASH_VARIANCE_REVIEW_DECISION,
];

$makeService = static function (
    OrganizationalContextStore $contexts,
    Connection $connection,
    int $now,
    S80EventLog $log,
    S80AuthorizationRepository $authorization,
    bool $persistenceEnabled = true,
    string $runtimeClass = 'ci',
    bool $featureEnabled = true,
): array {
    $inner = new LaravelCashVarianceReviewDecisionRepository(
        $connection,
        $persistenceEnabled,
        $runtimeClass,
        $featureEnabled,
    );
    $repository = new S80ReviewRepository($log, $inner);
    $transaction = new S80Transaction($log, $connection);
    $clock = new S80Clock($log, $now);

    $service = new RecordCashVarianceReviewDecision(
        $repository,
        $contexts,
        new DurableScopedAuthorizationPolicy($authorization),
        $transaction,
        $clock,
    );

    return [$service, $repository, $transaction, $clock];
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

// Deny-by-default: absent reviewer grant fails before any evidence read or side effect.
$logDenied = new S80EventLog();
$authorizationDenied = $makeAuthorization($logDenied);
[$deniedService, $deniedRepository, $deniedTransaction, $deniedClock] = $makeService(
    $alphaReviewerContext,
    $connection,
    4800,
    $logDenied,
    $authorizationDenied,
);
$denial = $deny(
    fn () => $deniedService->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-auth-denied-0001',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-auth-denied-correlation-0001',
    ),
    'absent reviewer permission',
);
$assert($denial instanceof DurableAuthorizationViolation, 'absent permission exception type');
$assert(
    $denial instanceof DurableAuthorizationViolation
        && $denial->errorCode === DurableAuthorizationViolation::PERMISSION_DENIED,
    'absent permission denial code',
);
$assert($logDenied->events === ['authorization'], 'authorization denial ordering');
$assert($deniedRepository->resolveCalls === 0, 'denied evidence resolve invoked');
$assert($deniedRepository->recordCalls === 0, 'denied repository record invoked');
$assert($deniedClock->calls === 0, 'denied reviewer clock invoked');
$assert($deniedTransaction->calls === 0, 'denied transaction invoked');

// Explanation-author permission is intentionally insufficient for review authority.
$logExplanationOnly = new S80EventLog();
$authorizationExplanationOnly = $makeAuthorization($logExplanationOnly, [
    'identity' => $alpha['reviewer'],
    'tenant' => $alpha['tenant'],
    'organization' => $alpha['organization'],
    'outlet' => $alpha['outlet'],
    'permission' => PosPermission::RECORD_CASH_VARIANCE_EXPLANATION,
]);
[$explanationOnlyService, $explanationOnlyRepository, $explanationOnlyTransaction, $explanationOnlyClock]
    = $makeService(
        $alphaReviewerContext,
        $connection,
        4801,
        $logExplanationOnly,
        $authorizationExplanationOnly,
    );
$deny(
    fn () => $explanationOnlyService->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-auth-explanation-only-0001',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-auth-explanation-only-correlation-0001',
    ),
    'explanation permission only',
);
$assert($logExplanationOnly->events === ['authorization'], 'explanation-only denial ordering');
$assert(
    $authorizationExplanationOnly->requestedPermissions === [
        PosPermission::RECORD_CASH_VARIANCE_REVIEW_DECISION,
    ],
    'review service requested wrong permission',
);
$assert($explanationOnlyRepository->resolveCalls === 0, 'explanation-only evidence read');
$assert($explanationOnlyClock->calls === 0, 'explanation-only clock invoked');
$assert($explanationOnlyTransaction->calls === 0, 'explanation-only transaction invoked');

// Exact reviewer grant is scope-bound; a wrong-outlet grant is not authority.
$logWrongScope = new S80EventLog();
$authorizationWrongScope = $makeAuthorization(
    $logWrongScope,
    $reviewGrant($alpha, $alpha['reviewer'], 'outlet-not-authorized'),
);
[$wrongScopeService, $wrongScopeRepository, $wrongScopeTransaction, $wrongScopeClock]
    = $makeService(
        $alphaReviewerContext,
        $connection,
        4802,
        $logWrongScope,
        $authorizationWrongScope,
    );
$deny(
    fn () => $wrongScopeService->record(
        $alphaVariance,
        new CashVarianceReviewDecisionCommand(
            'variance-review-auth-wrong-scope-0001',
            $alpha['explanation'],
            CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
        ),
        'variance-review-auth-wrong-scope-correlation-0001',
    ),
    'wrong outlet reviewer grant',
);
$assert($logWrongScope->events === ['authorization'], 'wrong-scope denial ordering');
$assert($wrongScopeRepository->resolveCalls === 0, 'wrong-scope evidence read');
$assert($wrongScopeClock->calls === 0, 'wrong-scope clock invoked');
$assert($wrongScopeTransaction->calls === 0, 'wrong-scope transaction invoked');

// Self-review remains denied even when the explanation author has the reviewer permission.
$selfContext = $setContext($alpha, $alpha['explainer']);
$logSelf = new S80EventLog();
$authorizationSelf = $makeAuthorization(
    $logSelf,
    $reviewGrant($alpha, $alpha['explainer']),
);
[$selfService, $selfRepository, $selfTransaction, $selfClock] = $makeService(
    $selfContext,
    $connection,
    4900,
    $logSelf,
    $authorizationSelf,
);
$deny(
    fn () => $selfService->record(
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
$assert($logSelf->events === ['authorization', 'resolve'], 'self-review ordering');
$assert($selfRepository->resolveCalls === 1, 'self-review explanation resolution');
$assert($selfRepository->recordCalls === 0, 'self-review durable write');
$assert($selfClock->calls === 0, 'self-review clock invoked');
$assert($selfTransaction->calls === 0, 'self-review transaction invoked');

$alphaAuthorized = static function (
    int $now,
    OrganizationalContextStore $context,
    Connection $connection,
    array $fixture,
    callable $makeAuthorization,
    callable $reviewGrant,
    callable $makeService,
): array {
    $log = new S80EventLog();
    $authorization = $makeAuthorization(
        $log,
        $reviewGrant($fixture, $fixture['reviewer']),
    );

    return [
        ...$makeService($context, $connection, $now, $log, $authorization),
        $log,
        $authorization,
    ];
};

$connection->table('oneqay_pos_cash_variance_explanation_evidence')
    ->where('tenant_id', $alpha['tenant'])
    ->where('evidence_id', $alpha['explanation'])
    ->update(['expected_cash_atomic' => 901]);
[$service] = $alphaAuthorized(
    4901,
    $alphaReviewerContext,
    $connection,
    $alpha,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$deny(
    fn () => $service->record(
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

$crossOrgContext = $setContext(
    $alpha,
    $alpha['reviewer'],
    $beta['organization'],
    $alpha['outlet'],
);
$logCrossOrg = new S80EventLog();
$authorizationCrossOrg = $makeAuthorization(
    $logCrossOrg,
    $reviewGrant($alpha, $alpha['reviewer']),
);
[$crossOrgService] = $makeService(
    $crossOrgContext,
    $connection,
    4902,
    $logCrossOrg,
    $authorizationCrossOrg,
);
$deny(
    fn () => $crossOrgService->record(
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
$assert($logCrossOrg->events === [], 'cross-organization reached authorization');

$crossOutletContext = $setContext(
    $alpha,
    $alpha['reviewer'],
    $alpha['organization'],
    $beta['outlet'],
);
$logCrossOutlet = new S80EventLog();
$authorizationCrossOutlet = $makeAuthorization(
    $logCrossOutlet,
    $reviewGrant($alpha, $alpha['reviewer']),
);
[$crossOutletService] = $makeService(
    $crossOutletContext,
    $connection,
    4903,
    $logCrossOutlet,
    $authorizationCrossOutlet,
);
$deny(
    fn () => $crossOutletService->record(
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
$assert($logCrossOutlet->events === [], 'cross-outlet reached authorization');

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
[$wrongSignService, , , , $wrongSignLog] = $alphaAuthorized(
    4904,
    $alphaReviewerContext,
    $connection,
    $alpha,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$deny(
    fn () => $wrongSignService->record(
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
$assert($wrongSignLog->events === [], 'malformed variance reached authorization');

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
[$matchService, , , , $matchLog] = $alphaAuthorized(
    4905,
    $alphaReviewerContext,
    $connection,
    $alpha,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$deny(
    fn () => $matchService->record(
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
$assert($matchLog->events === [], 'MATCH review reached authorization');

$deny(
    fn () => new CashVarianceReviewDecisionCommand(
        'variance-review-operation-alias-0001',
        $alpha['explanation'],
        'APPROVE',
    ),
    'unknown outcome alias',
);

[$missingService] = $alphaAuthorized(
    4906,
    $alphaReviewerContext,
    $connection,
    $alpha,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$deny(
    fn () => $missingService->record(
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

[$crossTenantService] = $alphaAuthorized(
    4907,
    $alphaReviewerContext,
    $connection,
    $alpha,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$deny(
    fn () => $crossTenantService->record(
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

[$clockFailureService] = $alphaAuthorized(
    0,
    $alphaReviewerContext,
    $connection,
    $alpha,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$deny(
    fn () => $clockFailureService->record(
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
[$alphaService, $alphaRepository, $alphaTransaction, $alphaClock, $alphaLog, $alphaAuthorization]
    = $alphaAuthorized(
        5000,
        $alphaReviewerContext,
        $connection,
        $alpha,
        $makeAuthorization,
        $reviewGrant,
        $makeService,
    );
$alphaResult = $alphaService->record(
    $alphaVariance,
    $alphaCommand,
    'variance-review-correlation-alpha-0001',
);
$assert(
    $alphaLog->events === ['authorization', 'resolve', 'clock', 'transaction', 'record'],
    'authorized reviewer ordering',
);
$assert($alphaAuthorization->calls === 1, 'authorized permission check count');
$assert(
    $alphaAuthorization->requestedPermissions === [
        PosPermission::RECORD_CASH_VARIANCE_REVIEW_DECISION,
    ],
    'authorized requested permission mismatch',
);
$assert($alphaRepository->resolveCalls === 1, 'authorized explanation resolution count');
$assert($alphaRepository->recordCalls === 1, 'authorized durable record count');
$assert($alphaClock->calls === 1, 'authorized clock count');
$assert($alphaTransaction->calls === 1, 'authorized transaction count');
$assert($alphaResult->reviewerActorIdentityId() === $alpha['reviewer'], 'authoritative reviewer identity');
$assert($alphaResult->explanationActorIdentityId() === $alpha['explainer'], 'authoritative explanation author identity');
$assert($alphaResult->reviewOutcome() === CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED, 'REVIEW_ACCEPTED durable round-trip');
$assert($alphaResult->varianceAtomic() === 100 && $alphaResult->varianceDirection() === CashVarianceResult::DIRECTION_OVER, 'OVER subject preserved');
$assert(
    $alphaResult->explanationPayloadFingerprint() === hash('sha256', 'authoritative-alpha-explanation'),
    'exact explanation payload fingerprint binding',
);

[$replayService] = $alphaAuthorized(
    9999,
    $alphaReviewerContext,
    $connection,
    $alpha,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$replay = $replayService->record(
    $alphaVariance,
    $alphaCommand,
    'variance-review-correlation-alpha-retry',
);
$assert($replay->reviewEvidenceId() === $alphaResult->reviewEvidenceId(), 'exact replay evidence identity');
$assert($replay->reviewedAtUnix() === 5000, 'exact replay original reviewed timestamp');
$assert($replay->correlationId() === 'variance-review-correlation-alpha-0001', 'exact replay original correlation');

[$conflictService] = $alphaAuthorized(
    5001,
    $alphaReviewerContext,
    $connection,
    $alpha,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$deny(
    fn () => $conflictService->record(
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

[$secondDecisionService] = $alphaAuthorized(
    5002,
    $alphaReviewerContext,
    $connection,
    $alpha,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$deny(
    fn () => $secondDecisionService->record(
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

$betaAuthorized = static function (
    int $now,
    OrganizationalContextStore $context,
    Connection $connection,
    array $fixture,
    callable $makeAuthorization,
    callable $reviewGrant,
    callable $makeService,
): array {
    $log = new S80EventLog();
    $authorization = $makeAuthorization(
        $log,
        $reviewGrant($fixture, $fixture['reviewer']),
    );

    return [
        ...$makeService($context, $connection, $now, $log, $authorization),
        $log,
        $authorization,
    ];
};

[$malformedFingerprintService] = $betaAuthorized(
    5999,
    $betaReviewerContext,
    $connection,
    $beta,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$deny(
    fn () => $malformedFingerprintService->record(
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

[$betaService] = $betaAuthorized(
    6000,
    $betaReviewerContext,
    $connection,
    $beta,
    $makeAuthorization,
    $reviewGrant,
    $makeService,
);
$betaResult = $betaService->record(
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

echo "Cash variance reviewer authorization and durable decision regression: PASS\n";
