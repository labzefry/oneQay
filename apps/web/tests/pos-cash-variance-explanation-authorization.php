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
use App\Application\Pos\CashVarianceExplanationCommand;
use App\Application\Pos\CashVarianceExplanationRepository;
use App\Application\Pos\CashVarianceExplanationResult;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\RecordCashVarianceExplanation;
use App\Application\Pos\ShiftOpeningClock;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry

final class S74EventLog
{
    /** @var list<string> */
    public array $events = [];
}

final class S74ContextStore implements OrganizationalContextStore
{
    public function __construct(private ?VerifiedOrganizationalContext $context) {}

    public function current(): ?VerifiedOrganizationalContext
    {
        return $this->context;
    }

    public function setVerified(VerifiedOrganizationalContext $context): void
    {
        $this->context = $context;
    }

    public function clear(): void
    {
        $this->context = null;
    }
}

final class S74AuthorizationRepository implements DurableRolePermissionRepository
{
    /** @var array<string, true> */
    public array $grants = [];

    /** @var list<string> */
    public array $requestedPermissions = [];

    public int $calls = 0;

    public function __construct(private readonly S74EventLog $log) {}

    public function allows(
        VerifiedOrganizationalContext $context,
        PermissionIdentifier $permission,
    ): bool {
        $this->calls++;
        $this->requestedPermissions[] = $permission->value();
        $this->log->events[] = 'authorization';

        $outlet = $context->outletId()?->value() ?? '';
        $key = implode('|', [
            $context->identityId()->value(),
            $context->tenantId()->value(),
            $context->organizationId()->value(),
            $outlet,
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

    public function revokeAll(): void
    {
        $this->grants = [];
    }
}

final class S74Clock implements ShiftOpeningClock
{
    public int $calls = 0;

    public function __construct(
        private readonly S74EventLog $log,
        private readonly int $now,
    ) {}

    public function nowUnix(): int
    {
        $this->calls++;
        $this->log->events[] = 'clock';

        return $this->now;
    }
}

final class S74Transaction implements PersistenceTransaction
{
    public int $calls = 0;

    public function __construct(private readonly S74EventLog $log) {}

    public function run(callable $operation): mixed
    {
        $this->calls++;
        $this->log->events[] = 'transaction';

        return $operation();
    }
}

final class S74ExplanationRepository implements CashVarianceExplanationRepository
{
    public int $calls = 0;

    public function __construct(private readonly S74EventLog $log) {}

    public function record(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        CashVarianceExplanationCommand $command,
        string $correlationId,
        int $recordedAtUnix,
    ): CashVarianceExplanationResult {
        $this->calls++;
        $this->log->events[] = 'repository';

        return new CashVarianceExplanationResult(
            'variance-explanation-evidence-0001',
            $command->operationId(),
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $variance->shiftId(),
            $variance->openingCashEvidenceId(),
            $variance->closingCashEvidenceId(),
            $context->actorId(),
            $variance->cutoffAtUnix(),
            $variance->expectedCashAtomic(),
            $variance->observedClosingAtomic(),
            $variance->varianceAtomic(),
            $variance->direction(),
            $variance->currency(),
            $variance->currencyScale(),
            $command->explanationText(),
            $correlationId,
            $recordedAtUnix,
        );
    }
}

$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Sprint74 authorization regression failed: '.$case);
    }
};

$assertThrows = static function (
    callable $operation,
    string $expectedClass,
    string $case,
) use ($assert): Throwable {
    try {
        $operation();
    } catch (Throwable $exception) {
        $assert(
            $exception instanceof $expectedClass,
            $case.' threw '.get_class($exception).' instead of '.$expectedClass,
        );

        return $exception;
    }

    $assert(false, $case.' did not throw');

    throw new RuntimeException('Unreachable');
};

$verified = static fn (
    string $identity,
    string $tenant,
    string $organization,
    string $outlet,
    string $device,
): VerifiedOrganizationalContext => new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString($identity),
    TenantId::fromString($tenant),
    OrganizationId::fromString($organization),
    OutletId::fromString($outlet),
    DeviceId::fromString($device),
);

$variance = static fn (
    string $tenant,
    string $organization,
    string $outlet,
    int $expected,
    int $observed,
    int $signed,
    string $direction,
): CashVarianceResult => new CashVarianceResult(
    $tenant,
    $organization,
    $outlet,
    'shift-alpha-0001',
    'opening-evidence-alpha-0001',
    'closing-evidence-alpha-0001',
    2000,
    $expected,
    $observed,
    $signed,
    $direction,
    'IDR',
    0,
);

$makeService = static function (
    VerifiedOrganizationalContext $verifiedContext,
    S74AuthorizationRepository $authorizationRepository,
    S74EventLog $log,
    int $clockAt = 4000,
): array {
    $contexts = new S74ContextStore($verifiedContext);
    $repository = new S74ExplanationRepository($log);
    $transaction = new S74Transaction($log);
    $clock = new S74Clock($log, $clockAt);

    $service = new RecordCashVarianceExplanation(
        $repository,
        $contexts,
        new DurableScopedAuthorizationPolicy($authorizationRepository),
        $transaction,
        $clock,
    );

    return [$service, $repository, $transaction, $clock];
};

$permission = PosPermission::recordCashVarianceExplanation();
$assert(
    $permission->value() === 'pos.shift.cash-variance-explanation.record',
    'exact permission identifier',
);
$assert(
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION === $permission->value(),
    'permission constant/accessor mismatch',
);

$alphaVerified = $verified(
    'explainer-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    'device-alpha',
);
$alphaOver = $variance(
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    900,
    1000,
    100,
    CashVarianceResult::DIRECTION_OVER,
);
$alphaCommand = new CashVarianceExplanationCommand(
    'variance-explain-auth-alpha-0001',
    'Authorized variance explanation.',
);

// Authorized OVER: exact ordering and actor attribution.
$log = new S74EventLog();
$authorization = new S74AuthorizationRepository($log);
$authorization->grant(
    'explainer-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION,
);
[$service, $repository, $transaction, $clock] = $makeService(
    $alphaVerified,
    $authorization,
    $log,
);
$result = $service->record(
    $alphaOver,
    $alphaCommand,
    'variance-explain-auth-correlation-alpha',
);
$assert(
    $log->events === ['authorization', 'clock', 'transaction', 'repository'],
    'authorized ordering',
);
$assert($authorization->calls === 1, 'authorization call count');
$assert($clock->calls === 1, 'clock call count');
$assert($transaction->calls === 1, 'transaction call count');
$assert($repository->calls === 1, 'repository call count');
$assert(
    $authorization->requestedPermissions === ['pos.shift.cash-variance-explanation.record'],
    'requested permission mismatch',
);
$assert($result->actorIdentityId() === 'explainer-alpha', 'actor attribution');
$assert($result->varianceDirection() === CashVarianceResult::DIRECTION_OVER, 'OVER result');

// Authorized SHORT uses the same exact permission.
$betaVerified = $verified(
    'explainer-beta',
    'tenant-beta',
    'organization-beta',
    'outlet-beta',
    'device-beta',
);
$betaShort = $variance(
    'tenant-beta',
    'organization-beta',
    'outlet-beta',
    1100,
    1000,
    -100,
    CashVarianceResult::DIRECTION_SHORT,
);
$logShort = new S74EventLog();
$authorizationShort = new S74AuthorizationRepository($logShort);
$authorizationShort->grant(
    'explainer-beta',
    'tenant-beta',
    'organization-beta',
    'outlet-beta',
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION,
);
[$shortService] = $makeService($betaVerified, $authorizationShort, $logShort, 5000);
$shortResult = $shortService->record(
    $betaShort,
    new CashVarianceExplanationCommand(
        'variance-explain-auth-beta-0001',
        'Authorized short explanation.',
    ),
    'variance-explain-auth-correlation-beta',
);
$assert($shortResult->varianceAtomic() === -100, 'SHORT signed variance');
$assert(
    $shortResult->varianceDirection() === CashVarianceResult::DIRECTION_SHORT,
    'SHORT direction',
);

// Absent grant denies before clock, transaction, and repository.
$logDenied = new S74EventLog();
$authorizationDenied = new S74AuthorizationRepository($logDenied);
[$deniedService, $deniedRepository, $deniedTransaction, $deniedClock] = $makeService(
    $alphaVerified,
    $authorizationDenied,
    $logDenied,
);
$denial = $assertThrows(
    fn () => $deniedService->record(
        $alphaOver,
        new CashVarianceExplanationCommand(
            'variance-explain-auth-denied-0001',
            'Denied explanation.',
        ),
        'variance-explain-auth-correlation-denied',
    ),
    DurableAuthorizationViolation::class,
    'absent permission',
);
$assert(
    $denial instanceof DurableAuthorizationViolation
        && $denial->errorCode === DurableAuthorizationViolation::PERMISSION_DENIED,
    'permission denial code',
);
$assert($logDenied->events === ['authorization'], 'denial ordering');
$assert($deniedClock->calls === 0, 'denied clock invoked');
$assert($deniedTransaction->calls === 0, 'denied transaction invoked');
$assert($deniedRepository->calls === 0, 'denied repository invoked');

// A grant for another permission is not sufficient.
$logWrongPermission = new S74EventLog();
$authorizationWrongPermission = new S74AuthorizationRepository($logWrongPermission);
$authorizationWrongPermission->grant(
    'explainer-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    PosPermission::RECORD_SHIFT_CLOSING_CASH,
);
[$wrongPermissionService] = $makeService(
    $alphaVerified,
    $authorizationWrongPermission,
    $logWrongPermission,
);
$assertThrows(
    fn () => $wrongPermissionService->record(
        $alphaOver,
        new CashVarianceExplanationCommand(
            'variance-explain-auth-wrong-permission',
            'Wrong permission must not authorize.',
        ),
        'variance-explain-auth-correlation-wrong-permission',
    ),
    DurableAuthorizationViolation::class,
    'wrong permission',
);

// Cross-tenant grant does not authorize the current context.
$logCrossTenant = new S74EventLog();
$authorizationCrossTenant = new S74AuthorizationRepository($logCrossTenant);
$authorizationCrossTenant->grant(
    'explainer-alpha',
    'tenant-beta',
    'organization-alpha',
    'outlet-alpha',
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION,
);
[$crossTenantService] = $makeService(
    $alphaVerified,
    $authorizationCrossTenant,
    $logCrossTenant,
);
$assertThrows(
    fn () => $crossTenantService->record(
        $alphaOver,
        new CashVarianceExplanationCommand(
            'variance-explain-auth-cross-tenant',
            'Cross-tenant grant must not authorize.',
        ),
        'variance-explain-auth-correlation-cross-tenant',
    ),
    DurableAuthorizationViolation::class,
    'cross-tenant grant',
);

// Cross-organization grant does not authorize the current context.
$logCrossOrganization = new S74EventLog();
$authorizationCrossOrganization = new S74AuthorizationRepository($logCrossOrganization);
$authorizationCrossOrganization->grant(
    'explainer-alpha',
    'tenant-alpha',
    'organization-beta',
    'outlet-alpha',
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION,
);
[$crossOrganizationService] = $makeService(
    $alphaVerified,
    $authorizationCrossOrganization,
    $logCrossOrganization,
);
$assertThrows(
    fn () => $crossOrganizationService->record(
        $alphaOver,
        new CashVarianceExplanationCommand(
            'variance-explain-auth-cross-organization',
            'Cross-organization grant must not authorize.',
        ),
        'variance-explain-auth-correlation-cross-organization',
    ),
    DurableAuthorizationViolation::class,
    'cross-organization grant',
);

// Cross-outlet grant does not authorize the current context.
$logCrossOutlet = new S74EventLog();
$authorizationCrossOutlet = new S74AuthorizationRepository($logCrossOutlet);
$authorizationCrossOutlet->grant(
    'explainer-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-beta',
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION,
);
[$crossOutletService] = $makeService(
    $alphaVerified,
    $authorizationCrossOutlet,
    $logCrossOutlet,
);
$assertThrows(
    fn () => $crossOutletService->record(
        $alphaOver,
        new CashVarianceExplanationCommand(
            'variance-explain-auth-cross-outlet',
            'Cross-outlet grant must not authorize.',
        ),
        'variance-explain-auth-correlation-cross-outlet',
    ),
    DurableAuthorizationViolation::class,
    'cross-outlet grant',
);

// Administrator-like identity labels do not bypass durable authorization.
$adminVerified = $verified(
    'administrator-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    'device-alpha',
);
$logAdmin = new S74EventLog();
$authorizationAdmin = new S74AuthorizationRepository($logAdmin);
[$adminService] = $makeService($adminVerified, $authorizationAdmin, $logAdmin);
$assertThrows(
    fn () => $adminService->record(
        $alphaOver,
        new CashVarianceExplanationCommand(
            'variance-explain-auth-admin-label',
            'Role label must not authorize.',
        ),
        'variance-explain-auth-correlation-admin-label',
    ),
    DurableAuthorizationViolation::class,
    'administrator label bypass',
);

// Exact replay remains authorization-bound.
$logReplay = new S74EventLog();
$authorizationReplay = new S74AuthorizationRepository($logReplay);
$authorizationReplay->grant(
    'explainer-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION,
);
[$replayService, $replayRepository, $replayTransaction, $replayClock] = $makeService(
    $alphaVerified,
    $authorizationReplay,
    $logReplay,
);
$replayCommand = new CashVarianceExplanationCommand(
    'variance-explain-auth-replay-0001',
    'Replay still requires authorization.',
);
$replayService->record(
    $alphaOver,
    $replayCommand,
    'variance-explain-auth-correlation-replay-first',
);
$firstRepositoryCalls = $replayRepository->calls;
$firstTransactionCalls = $replayTransaction->calls;
$firstClockCalls = $replayClock->calls;
$authorizationReplay->revokeAll();
$logReplay->events = [];
$assertThrows(
    fn () => $replayService->record(
        $alphaOver,
        $replayCommand,
        'variance-explain-auth-correlation-replay-second',
    ),
    DurableAuthorizationViolation::class,
    'replay without permission',
);
$assert($logReplay->events === ['authorization'], 'replay denial ordering');
$assert($replayRepository->calls === $firstRepositoryCalls, 'replay repository called after denial');
$assert($replayTransaction->calls === $firstTransactionCalls, 'replay transaction called after denial');
$assert($replayClock->calls === $firstClockCalls, 'replay clock called after denial');

// MATCH is rejected before authorization.
$match = $variance(
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    1000,
    1000,
    0,
    CashVarianceResult::DIRECTION_MATCH,
);
$logMatch = new S74EventLog();
$authorizationMatch = new S74AuthorizationRepository($logMatch);
$authorizationMatch->grant(
    'explainer-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION,
);
[$matchService] = $makeService($alphaVerified, $authorizationMatch, $logMatch);
$assertThrows(
    fn () => $matchService->record(
        $match,
        new CashVarianceExplanationCommand(
            'variance-explain-auth-match-0001',
            'MATCH must not be explainable.',
        ),
        'variance-explain-auth-correlation-match',
    ),
    PosTransactionViolation::class,
    'MATCH explanation',
);
$assert($authorizationMatch->calls === 0, 'MATCH reached authorization');

// Malformed arithmetic is rejected before authorization.
$malformed = $variance(
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    900,
    1000,
    99,
    CashVarianceResult::DIRECTION_OVER,
);
$logMalformed = new S74EventLog();
$authorizationMalformed = new S74AuthorizationRepository($logMalformed);
$authorizationMalformed->grant(
    'explainer-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION,
);
[$malformedService] = $makeService(
    $alphaVerified,
    $authorizationMalformed,
    $logMalformed,
);
$assertThrows(
    fn () => $malformedService->record(
        $malformed,
        new CashVarianceExplanationCommand(
            'variance-explain-auth-malformed-0001',
            'Malformed variance must fail.',
        ),
        'variance-explain-auth-correlation-malformed',
    ),
    PosTransactionViolation::class,
    'malformed variance',
);
$assert($authorizationMalformed->calls === 0, 'malformed variance reached authorization');

// Exact context mismatch is rejected before authorization.
$mismatchedVariance = $variance(
    'tenant-beta',
    'organization-alpha',
    'outlet-alpha',
    900,
    1000,
    100,
    CashVarianceResult::DIRECTION_OVER,
);
$logMismatch = new S74EventLog();
$authorizationMismatch = new S74AuthorizationRepository($logMismatch);
$authorizationMismatch->grant(
    'explainer-alpha',
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    PosPermission::RECORD_CASH_VARIANCE_EXPLANATION,
);
[$mismatchService] = $makeService(
    $alphaVerified,
    $authorizationMismatch,
    $logMismatch,
);
$assertThrows(
    fn () => $mismatchService->record(
        $mismatchedVariance,
        new CashVarianceExplanationCommand(
            'variance-explain-auth-context-mismatch',
            'Context mismatch must fail first.',
        ),
        'variance-explain-auth-correlation-context-mismatch',
    ),
    PosTransactionViolation::class,
    'variance context mismatch',
);
$assert($authorizationMismatch->calls === 0, 'context mismatch reached authorization');

echo "Sprint74 JRN-010 cash variance explanation authorization regression passed.\n";
