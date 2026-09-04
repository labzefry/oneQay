<?php

declare(strict_types=1);

use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Authorization\DurableRolePermissionRepository;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Pos\CashVarianceExplanationResult;
use App\Application\Pos\CashVarianceResult;
use App\Application\Pos\CashVarianceReviewDecisionCommand;
use App\Application\Pos\CashVarianceReviewDecisionRepository;
use App\Application\Pos\CashVarianceReviewDecisionResult;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\RecordCashVarianceReviewDecision;
use App\Application\Pos\ShiftOpeningClock;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry

const S80_REVIEW_PERMISSION = 'pos.shift.cash-variance-review-decision.record';

final class S80ReviewEventLog
{
    /** @var list<string> */
    public array $events = [];
}

final class S80ReviewContextStore implements OrganizationalContextStore
{
    public function __construct(private ?VerifiedOrganizationalContext $context) {}
    public function current(): ?VerifiedOrganizationalContext { return $this->context; }
    public function setVerified(VerifiedOrganizationalContext $context): void { $this->context = $context; }
    public function clear(): void { $this->context = null; }
}

final class S80ReviewAuthorizationRepository implements DurableRolePermissionRepository
{
    /** @var array<string, true> */
    private array $grants = [];
    /** @var list<string> */
    public array $requestedPermissions = [];
    public int $calls = 0;

    public function __construct(private readonly S80ReviewEventLog $log) {}

    public function allows(VerifiedOrganizationalContext $context, PermissionIdentifier $permission): bool
    {
        $this->calls++;
        $this->requestedPermissions[] = $permission->value();
        $this->log->events[] = 'authorization';

        return isset($this->grants[implode('|', [
            $context->identityId()->value(),
            $context->tenantId()->value(),
            $context->organizationId()->value(),
            $context->outletId()?->value() ?? '',
            $permission->value(),
        ])]);
    }

    public function grant(string $identity, string $tenant, string $organization, string $outlet, string $permission): void
    {
        $this->grants[implode('|', [$identity, $tenant, $organization, $outlet, $permission])] = true;
    }
}

final class S80ReviewClock implements ShiftOpeningClock
{
    public int $calls = 0;
    public function __construct(private readonly S80ReviewEventLog $log, private readonly int $now) {}
    public function nowUnix(): int
    {
        $this->calls++;
        $this->log->events[] = 'clock';
        return $this->now;
    }
}

final class S80ReviewTransaction implements PersistenceTransaction
{
    public int $calls = 0;
    public function __construct(private readonly S80ReviewEventLog $log) {}
    public function run(callable $operation): mixed
    {
        $this->calls++;
        $this->log->events[] = 'transaction';
        return $operation();
    }
}

final class S80ReviewRepository implements CashVarianceReviewDecisionRepository
{
    public int $resolveCalls = 0;
    public int $recordCalls = 0;

    public function __construct(
        private readonly S80ReviewEventLog $log,
        private readonly string $explanationActor,
    ) {}

    public function resolveExplanation(
        PosExecutionContext $context,
        CashVarianceResult $variance,
        string $cashVarianceExplanationEvidenceId,
    ): CashVarianceExplanationResult {
        $this->resolveCalls++;
        $this->log->events[] = 'resolve';

        return new CashVarianceExplanationResult(
            $cashVarianceExplanationEvidenceId,
            'variance-explanation-operation-0001',
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $variance->shiftId(),
            $variance->openingCashEvidenceId(),
            $variance->closingCashEvidenceId(),
            $this->explanationActor,
            $variance->cutoffAtUnix(),
            $variance->expectedCashAtomic(),
            $variance->observedClosingAtomic(),
            $variance->varianceAtomic(),
            $variance->direction(),
            $variance->currency(),
            $variance->currencyScale(),
            'Authoritative variance explanation.',
            'variance-explanation-correlation-0001',
            3000,
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

        return new CashVarianceReviewDecisionResult(
            'variance-review-evidence-0001',
            $command->operationId(),
            $context->tenantId(),
            $context->organizationId(),
            $context->outletId(),
            $variance->shiftId(),
            $variance->openingCashEvidenceId(),
            $variance->closingCashEvidenceId(),
            $explanation->evidenceId(),
            $explanation->actorIdentityId(),
            $context->actorId(),
            $variance->cutoffAtUnix(),
            $variance->expectedCashAtomic(),
            $variance->observedClosingAtomic(),
            $variance->varianceAtomic(),
            $variance->direction(),
            $variance->currency(),
            $variance->currencyScale(),
            hash('sha256', 'authoritative-explanation'),
            $command->reviewOutcome(),
            $correlationId,
            $reviewedAtUnix,
        );
    }
}

$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException('Reviewer authorization regression failed: '.$case);
    }
};

$assertThrows = static function (callable $operation, string $expectedClass, string $case) use ($assert): Throwable {
    try {
        $operation();
    } catch (Throwable $exception) {
        $assert($exception instanceof $expectedClass, $case.' threw '.get_class($exception));
        return $exception;
    }
    $assert(false, $case.' did not throw');
    throw new RuntimeException('Unreachable');
};

$verified = static fn (string $identity, string $tenant, string $organization, string $outlet): VerifiedOrganizationalContext =>
    new VerifiedOrganizationalContext(
        PlatformIdentityId::fromString($identity),
        TenantId::fromString($tenant),
        OrganizationId::fromString($organization),
        OutletId::fromString($outlet),
        DeviceId::fromString('device-alpha'),
    );

$variance = new CashVarianceResult(
    'tenant-alpha',
    'organization-alpha',
    'outlet-alpha',
    'shift-alpha-0001',
    'opening-evidence-alpha-0001',
    'closing-evidence-alpha-0001',
    2000,
    900,
    1000,
    100,
    CashVarianceResult::DIRECTION_OVER,
    'IDR',
    0,
);
$command = new CashVarianceReviewDecisionCommand(
    'variance-review-operation-0001',
    'variance-explanation-evidence-0001',
    CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED,
);

$make = static function (
    VerifiedOrganizationalContext $context,
    S80ReviewAuthorizationRepository $authorization,
    S80ReviewEventLog $log,
    string $explanationActor = 'explainer-alpha',
): array {
    $repository = new S80ReviewRepository($log, $explanationActor);
    $transaction = new S80ReviewTransaction($log);
    $clock = new S80ReviewClock($log, 5000);
    $service = new RecordCashVarianceReviewDecision(
        $repository,
        new S80ReviewContextStore($context),
        new DurableScopedAuthorizationPolicy($authorization),
        $transaction,
        $clock,
    );
    return [$service, $repository, $transaction, $clock];
};

$reviewer = $verified('reviewer-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha');

// Deny by default before evidence read, clock, transaction, or write.
$log = new S80ReviewEventLog();
$authorization = new S80ReviewAuthorizationRepository($log);
[$service, $repository, $transaction, $clock] = $make($reviewer, $authorization, $log);
$denial = $assertThrows(
    fn () => $service->record($variance, $command, 'variance-review-correlation-denied'),
    DurableAuthorizationViolation::class,
    'absent reviewer permission',
);
$assert($denial instanceof DurableAuthorizationViolation, 'denial type');
$assert($log->events === ['authorization'], 'deny-before-effects ordering');
$assert($repository->resolveCalls === 0 && $repository->recordCalls === 0, 'repository called on denial');
$assert($clock->calls === 0 && $transaction->calls === 0, 'clock/transaction called on denial');
$assert($authorization->requestedPermissions === [S80_REVIEW_PERMISSION], 'exact reviewer permission request');

// Explanation permission cannot substitute for reviewer permission.
$wrongLog = new S80ReviewEventLog();
$wrongAuthorization = new S80ReviewAuthorizationRepository($wrongLog);
$wrongAuthorization->grant(
    'reviewer-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha',
    'pos.shift.cash-variance-explanation.record',
);
[$wrongService] = $make($reviewer, $wrongAuthorization, $wrongLog);
$assertThrows(
    fn () => $wrongService->record($variance, $command, 'variance-review-correlation-wrong-permission'),
    DurableAuthorizationViolation::class,
    'explanation permission substitution',
);

// Cross-scope grants do not authorize current tenant/organization/outlet.
foreach ([
    ['tenant-beta', 'organization-alpha', 'outlet-alpha'],
    ['tenant-alpha', 'organization-beta', 'outlet-alpha'],
    ['tenant-alpha', 'organization-alpha', 'outlet-beta'],
] as [$tenant, $organization, $outlet]) {
    $scopeLog = new S80ReviewEventLog();
    $scopeAuthorization = new S80ReviewAuthorizationRepository($scopeLog);
    $scopeAuthorization->grant('reviewer-alpha', $tenant, $organization, $outlet, S80_REVIEW_PERMISSION);
    [$scopeService] = $make($reviewer, $scopeAuthorization, $scopeLog);
    $assertThrows(
        fn () => $scopeService->record($variance, $command, 'variance-review-correlation-wrong-scope'),
        DurableAuthorizationViolation::class,
        'wrong-scope reviewer grant',
    );
}

// Authorized reviewer: exact ordering and reviewer attribution.
$happyLog = new S80ReviewEventLog();
$happyAuthorization = new S80ReviewAuthorizationRepository($happyLog);
$happyAuthorization->grant(
    'reviewer-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', S80_REVIEW_PERMISSION,
);
[$happyService, $happyRepository, $happyTransaction, $happyClock] = $make(
    $reviewer,
    $happyAuthorization,
    $happyLog,
);
$result = $happyService->record($variance, $command, 'variance-review-correlation-authorized');
$assert(
    $happyLog->events === ['authorization', 'resolve', 'clock', 'transaction', 'record'],
    'authorized ordering',
);
$assert($happyRepository->resolveCalls === 1 && $happyRepository->recordCalls === 1, 'authorized repository calls');
$assert($happyClock->calls === 1 && $happyTransaction->calls === 1, 'authorized clock/transaction calls');
$assert($result->reviewerActorIdentityId() === 'reviewer-alpha', 'reviewer attribution');
$assert($result->reviewOutcome() === CashVarianceReviewDecisionCommand::REVIEW_ACCEPTED, 'review outcome');

// Maker-checker remains enforced after authorization and explanation resolution.
$selfLog = new S80ReviewEventLog();
$selfAuthorization = new S80ReviewAuthorizationRepository($selfLog);
$selfAuthorization->grant(
    'reviewer-alpha', 'tenant-alpha', 'organization-alpha', 'outlet-alpha', S80_REVIEW_PERMISSION,
);
[$selfService, $selfRepository, $selfTransaction, $selfClock] = $make(
    $reviewer,
    $selfAuthorization,
    $selfLog,
    'reviewer-alpha',
);
$assertThrows(
    fn () => $selfService->record($variance, $command, 'variance-review-correlation-self-review'),
    PosTransactionViolation::class,
    'self-review',
);
$assert($selfLog->events === ['authorization', 'resolve'], 'self-review ordering');
$assert($selfRepository->recordCalls === 0, 'self-review wrote evidence');
$assert($selfClock->calls === 0 && $selfTransaction->calls === 0, 'self-review reached clock/transaction');

echo "Cash variance reviewer authorization regression: PASS\n";
