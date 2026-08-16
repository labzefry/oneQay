<?php

declare(strict_types=1);

use App\Application\SystemUpdate\Security\PrivilegedReauthenticationVerifier;
use App\Application\SystemUpdate\Security\PrivilegedSecurityAuditEvent;
use App\Application\SystemUpdate\Security\PrivilegedSecurityAuditSink;
use App\Application\SystemUpdate\Security\PrivilegedTotpSecretProvider;
use App\Application\SystemUpdate\Security\PrivilegedUpdateAuthorizationViolation;
use App\Application\SystemUpdate\Security\PrivilegedUpdateCapability;
use App\Application\SystemUpdate\Security\PrivilegedUpdateSecurityPolicy;
use App\Application\SystemUpdate\Security\RequirePrivilegedUpdateAuthorization;
use App\Application\SystemUpdate\Security\VerifyPrivilegedUpdateStepUp;
use App\Domain\Identity\PlatformIdentityId;
use App\Infrastructure\SystemUpdate\Security\Rfc6238PrivilegedTotpVerifier;
use App\Infrastructure\SystemUpdate\Security\ServerVerifiedPrivilegedPlatformIdentity;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assertPrivileged = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException("Privileged update security regression failed: {$case}");
    }
};

$expectDenied = static function (callable $attempt, string $case) use ($assertPrivileged): void {
    try {
        $attempt();
        $assertPrivileged(false, $case);
    } catch (PrivilegedUpdateAuthorizationViolation $exception) {
        $assertPrivileged(
            $exception->getMessage() === 'Privileged update authorization denied.',
            $case.' must remain generic',
        );
    }
};

$identityId = PlatformIdentityId::fromString('synthetic-platform-admin');
$rfcSecret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

$secretProvider = new class($rfcSecret) implements PrivilegedTotpSecretProvider {
    public function __construct(private readonly string $secret)
    {
    }

    public function base32SecretFor(PlatformIdentityId $identityId): ?string
    {
        return $identityId->value() === 'synthetic-platform-admin' ? $this->secret : null;
    }
};

$totp = new Rfc6238PrivilegedTotpVerifier($secretProvider);

// RFC 6238 SHA-1 vectors reduced to the required six digits.
$assertPrivileged($totp->verify($identityId, '287082', 59), 'RFC6238-001 timestamp 59');
$assertPrivileged($totp->verify($identityId, '081804', 1_111_111_109), 'RFC6238-002 timestamp 1111111109');
$assertPrivileged(! $totp->verify($identityId, '000000', 59), 'RFC6238-003 incorrect code denied');
$assertPrivileged(! $totp->verify($identityId, '28708', 59), 'RFC6238-004 malformed code denied');

$badSecretProvider = new class implements PrivilegedTotpSecretProvider {
    public function base32SecretFor(PlatformIdentityId $identityId): ?string
    {
        return 'not-a-base32-secret';
    }
};
$assertPrivileged(
    ! (new Rfc6238PrivilegedTotpVerifier($badSecretProvider))->verify($identityId, '287082', 59),
    'RFC6238-005 malformed secret material denied',
);

$audit = new class implements PrivilegedSecurityAuditSink {
    /** @var list<array<string, int|string|null>> */
    public array $events = [];

    public function record(PrivilegedSecurityAuditEvent $event): void
    {
        $this->events[] = $event->safeContext();
    }
};

$reauthentication = new class implements PrivilegedReauthenticationVerifier {
    public function verify(PlatformIdentityId $identityId, string $credential): bool
    {
        return $identityId->value() === 'synthetic-platform-admin'
            && hash_equals('SYNTHETIC_REAUTH_SECRET_82d1', $credential);
    }
};

$sessionA = hash('sha256', 'synthetic-session-a');
$identity = new ServerVerifiedPrivilegedPlatformIdentity(
    $identityId,
    true,
    [PrivilegedUpdateCapability::INSTALL],
    50,
    $sessionA,
);

$stepUp = new VerifyPrivilegedUpdateStepUp($reauthentication, $totp, $audit);
$evidence = $stepUp->verify(
    $identity,
    'SYNTHETIC_REAUTH_SECRET_82d1',
    '287082',
    59,
);

$authorization = (new RequirePrivilegedUpdateAuthorization($audit))->require(
    $identity,
    $evidence,
    60,
);

$assertPrivileged(
    $authorization->identityId()->value() === 'synthetic-platform-admin',
    'AUTHZ-001 canonical identity preserved',
);
$assertPrivileged(
    $authorization->capability() === PrivilegedUpdateCapability::INSTALL,
    'AUTHZ-002 install capability bound',
);
$assertPrivileged($authorization->authorizedAtUnix() === 60, 'AUTHZ-003 authorization timestamp bound');

$nonSuperadmin = new ServerVerifiedPrivilegedPlatformIdentity(
    $identityId,
    false,
    [PrivilegedUpdateCapability::INSTALL],
    50,
    $sessionA,
);
$expectDenied(
    static fn () => $stepUp->verify($nonSuperadmin, 'SYNTHETIC_REAUTH_SECRET_82d1', '287082', 59),
    'AUTHZ-NEG-001 non-superadmin denied',
);

$missingCapability = new ServerVerifiedPrivilegedPlatformIdentity(
    $identityId,
    true,
    [],
    50,
    $sessionA,
);
$expectDenied(
    static fn () => $stepUp->verify($missingCapability, 'SYNTHETIC_REAUTH_SECRET_82d1', '287082', 59),
    'AUTHZ-NEG-002 missing capability denied',
);

$staleSession = new ServerVerifiedPrivilegedPlatformIdentity(
    $identityId,
    true,
    [PrivilegedUpdateCapability::INSTALL],
    1,
    $sessionA,
);
$expectDenied(
    static fn () => $stepUp->verify($staleSession, 'SYNTHETIC_REAUTH_SECRET_82d1', '287082', 1_000),
    'AUTHZ-NEG-003 stale privileged session denied',
);

$expectDenied(
    static fn () => $stepUp->verify($identity, 'WRONG_REAUTH_SECRET', '287082', 59),
    'AUTHZ-NEG-004 failed explicit re-auth denied',
);
$expectDenied(
    static fn () => $stepUp->verify($identity, 'SYNTHETIC_REAUTH_SECRET_82d1', '000000', 59),
    'AUTHZ-NEG-005 failed TOTP denied',
);
$expectDenied(
    static fn () => $stepUp->verify($identity, 'SYNTHETIC_REAUTH_SECRET_82d1', '28708A', 59),
    'AUTHZ-NEG-006 malformed TOTP input denied',
);

$sessionBIdentity = new ServerVerifiedPrivilegedPlatformIdentity(
    $identityId,
    true,
    [PrivilegedUpdateCapability::INSTALL],
    50,
    hash('sha256', 'synthetic-session-b'),
);
$expectDenied(
    static fn () => (new RequirePrivilegedUpdateAuthorization($audit))->require($sessionBIdentity, $evidence, 60),
    'AUTHZ-NEG-007 cross-session step-up replay denied',
);

$expectDenied(
    static fn () => (new RequirePrivilegedUpdateAuthorization($audit))->require($identity, $evidence, 400),
    'AUTHZ-NEG-008 stale step-up evidence denied',
);

$predatingIdentity = new ServerVerifiedPrivilegedPlatformIdentity(
    $identityId,
    true,
    [PrivilegedUpdateCapability::INSTALL],
    100,
    $sessionA,
);
$expectDenied(
    static fn () => (new RequirePrivilegedUpdateAuthorization($audit))->require($predatingIdentity, $evidence, 110),
    'AUTHZ-NEG-009 step-up predating authenticated session denied',
);

$failingAudit = new class implements PrivilegedSecurityAuditSink {
    public function record(PrivilegedSecurityAuditEvent $event): void
    {
        throw new RuntimeException('synthetic audit sink unavailable');
    }
};
$expectDenied(
    static fn () => (new VerifyPrivilegedUpdateStepUp($reauthentication, $totp, $failingAudit))->verify(
        $identity,
        'SYNTHETIC_REAUTH_SECRET_82d1',
        '287082',
        59,
    ),
    'AUTHZ-NEG-010 audit failure must fail closed',
);

$serializedAudit = json_encode($audit->events, JSON_THROW_ON_ERROR);
foreach ([
    'SYNTHETIC_REAUTH_SECRET_82d1',
    'WRONG_REAUTH_SECRET',
    '287082',
    '000000',
    $rfcSecret,
] as $sensitiveValue) {
    $assertPrivileged(
        ! str_contains($serializedAudit, $sensitiveValue),
        'AUDIT-001 sanitized audit must not contain re-auth/TOTP/secret material',
    );
}

foreach ($audit->events as $event) {
    $assertPrivileged(
        array_keys($event) === [
            'event',
            'action',
            'outcome',
            'identity_id',
            'capability',
            'occurred_at_unix',
            'failure_code',
        ],
        'AUDIT-002 audit context field envelope must remain bounded',
    );
}

$assertPrivileged(
    PrivilegedUpdateSecurityPolicy::SESSION_MAX_AGE_SECONDS === 900,
    'POLICY-001 privileged session freshness is bounded',
);
$assertPrivileged(
    PrivilegedUpdateSecurityPolicy::STEP_UP_MAX_AGE_SECONDS === 300,
    'POLICY-002 step-up freshness is bounded',
);
$assertPrivileged(
    PrivilegedUpdateSecurityPolicy::RATE_LIMIT_PER_MINUTE === 5
        && PrivilegedUpdateSecurityPolicy::RATE_LIMIT_PER_HOUR === 20,
    'POLICY-003 privileged mutation rate limits are bounded',
);

fwrite(STDOUT, "Privileged update security regression passed.\n");
