<?php

declare(strict_types=1);

use App\Application\Identity\FirstPartyIdentityCredentialVerifier;
use App\Application\Identity\PrivilegedStepUpService;
use App\Application\Identity\PrivilegedStepUpViolation;
use App\Application\Identity\PrivilegedTotpClock;
use App\Application\Identity\PrivilegedTotpEngine;
use App\Application\Identity\PrivilegedTotpMfaRepository;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Application\Identity\PrivilegedTotpMfaState;
use App\Application\Identity\PrivilegedTotpMfaViolation;
use App\Application\Identity\VerifyFirstPartyIdentityCredential;
use App\Application\Persistence\PersistenceTransaction;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint 31 privileged reauthentication regression failed: '.$message);
    }
};

$configSource = file_get_contents(__DIR__.'/../config/oneqay.php');
$routesSource = file_get_contents(__DIR__.'/../routes/web.php');
$keysSource = file_get_contents(__DIR__.'/../app/Delivery/Http/Identity/FirstPartySessionKeys.php');
$controllerSource = file_get_contents(__DIR__.'/../app/Delivery/Http/Identity/PrivilegedReauthenticationController.php');
$middlewareSource = file_get_contents(__DIR__.'/../app/Delivery/Http/Middleware/RequirePolicyAdministrationSessionContextMiddleware.php');
$providerSource = file_get_contents(__DIR__.'/../app/Providers/AppServiceProvider.php');
foreach ([$configSource, $routesSource, $keysSource, $controllerSource, $middlewareSource, $providerSource] as $source) {
    $assert(is_string($source) && $source !== '', 'required source fixture missing');
}

$assert(str_contains($configSource, "env('ONEQAY_PRIVILEGED_STEP_UP_ENABLED', false)"), 'feature source default is not disabled');
$assert(str_contains($configSource, "'freshness_seconds' => 300"), 'freshness is not fixed at 300 seconds');
$assert(str_contains($routesSource, "Route::post('/auth/reauthenticate/privileged'"), 'reauthentication route missing');
$assert(str_contains($routesSource, "config('oneqay.privileged_totp_mfa.enabled', false)"), 'route does not depend on TOTP arm');
$assert(str_contains($routesSource, "config('oneqay.privileged_step_up.enabled', false)"), 'route does not depend on step-up arm');
$assert(str_contains($routesSource, "'throttle:5,1', 'throttle:20,60'"), 'step-up throttling baseline missing');
$assert(str_contains($controllerSource, '$session->invalidate();'), 'successful step-up does not rotate session');
$assert(str_contains($controllerSource, '$session->regenerateToken();'), 'successful step-up does not regenerate CSRF token');
$assert(str_contains($controllerSource, 'FirstPartySessionKeys::MFA_VERIFIED_AT'), 'login-level MFA evidence is not preserved separately');
$assert(str_contains($controllerSource, "private const SCOPE = 'policy_administration'"), 'exact step-up scope missing');
$assert(str_contains($controllerSource, 'FirstPartySessionKeys::pending()'), 'pending MFA state is not rejected');
$assert(! str_contains($controllerSource, "'tenant_id' => \$payload"), 'controller trusts client-selected tenant context');
$assert(str_contains($middlewareSource, 'STEP_UP_FRESHNESS_SECONDS = 300'), 'middleware freshness constant changed');
$assert(str_contains($middlewareSource, '$now < $verifiedAt'), 'future-clock evidence is not rejected');
$assert(str_contains($middlewareSource, 'PrivilegedTotpMfaState::CONFIRMED'), 'current protected factor confirmation is not rechecked');
$assert(str_contains($middlewareSource, '$context !== $expectedContext'), 'exact server-context binding is not enforced');
$assert(str_contains($providerSource, 'PrivilegedStepUpClock::class'), 'server step-up clock is not bound');
$assert(str_contains($keysSource, "oneqay.auth.step_up_verified_at"), 'step-up timestamp key missing');
$assert(str_contains($keysSource, "oneqay.auth.step_up_scope"), 'step-up scope key missing');
$assert(str_contains($keysSource, "oneqay.auth.step_up_context"), 'step-up context key missing');

$credentialVerifier = new class implements FirstPartyIdentityCredentialVerifier {
    public function verify(TenantId $tenantId, PlatformIdentityId $identityId, #[\SensitiveParameter] string $password): bool
    {
        return $tenantId->value() === 'tenant-alpha'
            && $identityId->value() === 'admin-alpha'
            && hash_equals('correct-password', $password);
    }
};

$repository = new class implements PrivilegedTotpMfaRepository {
    public ?int $lastAcceptedStep = null;
    public bool $protected = true;

    public function protectedControlRequired(TenantId $tenantId, PlatformIdentityId $identityId): bool
    {
        return $this->protected;
    }

    public function factorState(TenantId $tenantId, PlatformIdentityId $identityId): PrivilegedTotpMfaState
    {
        return new PrivilegedTotpMfaState(PrivilegedTotpMfaState::CONFIRMED);
    }

    public function ensurePendingSecret(TenantId $tenantId, PlatformIdentityId $identityId, #[\SensitiveParameter] ?string $freshSecret, int $createdAtUnix): string
    {
        throw new LogicException('not used');
    }

    public function pendingSecret(TenantId $tenantId, PlatformIdentityId $identityId): string
    {
        throw new LogicException('not used');
    }

    public function confirmedSecret(TenantId $tenantId, PlatformIdentityId $identityId): string
    {
        return 'SYNTHETIC-S31-SECRET';
    }

    public function confirmPendingStep(TenantId $tenantId, PlatformIdentityId $identityId, int $matchedTimeStep, int $confirmedAtUnix): void
    {
        throw new LogicException('not used');
    }

    public function consumeConfirmedStep(TenantId $tenantId, PlatformIdentityId $identityId, int $matchedTimeStep): void
    {
        if ($this->lastAcceptedStep !== null && $matchedTimeStep <= $this->lastAcceptedStep) {
            throw new PrivilegedTotpMfaViolation(
                PrivilegedTotpMfaViolation::REPLAY_DENIED,
                'Privileged TOTP MFA verification failed.',
            );
        }
        $this->lastAcceptedStep = $matchedTimeStep;
    }
};

$engine = new class implements PrivilegedTotpEngine {
    public function generateSecret(): string
    {
        throw new LogicException('not used');
    }

    public function provisioningUri(TenantId $tenantId, PlatformIdentityId $identityId, #[\SensitiveParameter] string $secret): string
    {
        throw new LogicException('not used');
    }

    public function matchTimeStep(#[\SensitiveParameter] string $secret, #[\SensitiveParameter] string $code, int $nowUnix): ?int
    {
        return hash_equals('123456', $code) ? intdiv($nowUnix, 30) : null;
    }
};

$clock = new class implements PrivilegedTotpClock {
    public int $now = 1_800_000_000;

    public function nowUnix(): int
    {
        return $this->now;
    }
};

$transaction = new class implements PersistenceTransaction {
    public function run(callable $operation): mixed
    {
        return $operation();
    }
};

$mfa = new PrivilegedTotpMfaService($repository, $engine, $transaction, $clock);
$service = new PrivilegedStepUpService(new VerifyFirstPartyIdentityCredential($credentialVerifier), $mfa);
$tenant = TenantId::fromString('tenant-alpha');
$identity = PlatformIdentityId::fromString('admin-alpha');

$expectFailure = static function (callable $operation, string $message) use ($assert): void {
    try {
        $operation();
        $assert(false, $message);
    } catch (PrivilegedStepUpViolation $exception) {
        $assert(
            $exception->errorCode === PrivilegedStepUpViolation::VERIFICATION_FAILED,
            'failure surface is not generic',
        );
    }
};

$expectFailure(
    fn () => $service->verify($tenant, $identity, 'wrong-password', '123456'),
    'wrong password was accepted',
);
$assert($repository->lastAcceptedStep === null, 'wrong password consumed a TOTP step');

$expectFailure(
    fn () => $service->verify($tenant, $identity, 'correct-password', '000000'),
    'wrong TOTP was accepted',
);
$assert($repository->lastAcceptedStep === null, 'wrong TOTP consumed a time step');

$verifiedAt = $service->verify($tenant, $identity, 'correct-password', '123456');
$assert($verifiedAt === $clock->now, 'successful step-up timestamp is not the TOTP server timestamp');
$assert(
    $repository->lastAcceptedStep === intdiv($clock->now, 30),
    'successful step-up did not consume the matched TOTP step',
);

$expectFailure(
    fn () => $service->verify($tenant, $identity, 'correct-password', '123456'),
    'replayed TOTP step was accepted',
);

$repository->protected = false;
$clock->now += 30;
$expectFailure(
    fn () => $service->verify($tenant, $identity, 'correct-password', '123456'),
    'loss of protected-control status was accepted',
);

$assert(FirstPartySessionKeys::all() === [
    FirstPartySessionKeys::IDENTITY,
    FirstPartySessionKeys::TENANT,
    FirstPartySessionKeys::ORGANIZATION,
    FirstPartySessionKeys::OUTLET,
    FirstPartySessionKeys::DEVICE,
], 'Sprint 27 canonical full-context key contract changed');

echo "Sprint 31 privileged reauthentication step-up regression passed.\n";
