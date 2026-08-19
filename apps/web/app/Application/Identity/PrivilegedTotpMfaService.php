<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
final readonly class PrivilegedTotpMfaService
{
    public function __construct(
        private PrivilegedTotpMfaRepository $repository,
        private PrivilegedTotpEngine $engine,
        private PersistenceTransaction $transaction,
        private PrivilegedTotpClock $clock,
    ) {}

    public function requiredState(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
    ): ?PrivilegedTotpMfaState {
        if (! $this->repository->protectedControlRequired($tenantId, $identityId)) {
            return null;
        }

        return $this->repository->factorState($tenantId, $identityId);
    }

    public function startEnrollment(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
    ): IssuedPrivilegedTotpEnrollment {
        $this->assertProtected($tenantId, $identityId);
        $state = $this->repository->factorState($tenantId, $identityId);

        if ($state->is(PrivilegedTotpMfaState::CONFIRMED)) {
            $this->fail(
                PrivilegedTotpMfaViolation::ENROLLMENT_DENIED,
                'Privileged TOTP enrollment is not available for a confirmed factor.',
            );
        }

        $createdAtUnix = $this->validNow();
        $freshSecret = $state->is(PrivilegedTotpMfaState::ABSENT)
            ? $this->engine->generateSecret()
            : null;

        try {
            $secret = $this->transaction->run(
                fn (): string => $this->repository->ensurePendingSecret(
                    $tenantId,
                    $identityId,
                    $freshSecret,
                    $createdAtUnix,
                ),
            );
        } catch (PrivilegedTotpMfaViolation $exception) {
            throw $exception;
        } catch (DurablePersistenceViolation) {
            $this->fail(
                PrivilegedTotpMfaViolation::TRANSACTION_FAILURE,
                'Privileged TOTP enrollment transaction failed.',
            );
        }

        if (! is_string($secret) || $secret === '') {
            $this->fail(
                PrivilegedTotpMfaViolation::TRANSACTION_FAILURE,
                'Privileged TOTP enrollment transaction returned invalid material.',
            );
        }

        $uri = $this->engine->provisioningUri($tenantId, $identityId, $secret);

        return new IssuedPrivilegedTotpEnrollment($secret, $uri);
    }

    public function confirmEnrollment(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] string $code,
    ): void {
        $this->assertCode($code);
        $this->assertProtected($tenantId, $identityId);
        $secret = $this->repository->pendingSecret($tenantId, $identityId);
        $now = $this->validNow();
        $matchedStep = $this->engine->matchTimeStep($secret, $code, $now);

        if ($matchedStep === null) {
            $this->verificationFailed();
        }

        try {
            $this->transaction->run(function () use ($tenantId, $identityId, $matchedStep, $now): void {
                $this->repository->confirmPendingStep(
                    $tenantId,
                    $identityId,
                    $matchedStep,
                    $now,
                );
            });
        } catch (PrivilegedTotpMfaViolation $exception) {
            throw $exception;
        } catch (DurablePersistenceViolation) {
            $this->fail(
                PrivilegedTotpMfaViolation::TRANSACTION_FAILURE,
                'Privileged TOTP confirmation transaction failed.',
            );
        }
    }

    public function challenge(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] string $code,
    ): int {
        $this->assertCode($code);
        $this->assertProtected($tenantId, $identityId);
        $secret = $this->repository->confirmedSecret($tenantId, $identityId);
        $now = $this->validNow();
        $matchedStep = $this->engine->matchTimeStep($secret, $code, $now);

        if ($matchedStep === null) {
            $this->verificationFailed();
        }

        try {
            $this->transaction->run(function () use ($tenantId, $identityId, $matchedStep): void {
                $this->repository->consumeConfirmedStep(
                    $tenantId,
                    $identityId,
                    $matchedStep,
                );
            });
        } catch (PrivilegedTotpMfaViolation $exception) {
            throw $exception;
        } catch (DurablePersistenceViolation) {
            $this->fail(
                PrivilegedTotpMfaViolation::TRANSACTION_FAILURE,
                'Privileged TOTP challenge transaction failed.',
            );
        }

        return $now;
    }

    private function assertProtected(TenantId $tenantId, PlatformIdentityId $identityId): void
    {
        if (! $this->repository->protectedControlRequired($tenantId, $identityId)) {
            $this->fail(
                PrivilegedTotpMfaViolation::AUTHORIZATION_DENIED,
                'Privileged TOTP MFA authorization denied.',
            );
        }
    }

    private function assertCode(#[\SensitiveParameter] string $code): void
    {
        if (preg_match('/\A[0-9]{6}\z/D', $code) !== 1) {
            $this->verificationFailed();
        }
    }

    private function validNow(): int
    {
        $now = $this->clock->nowUnix();
        if ($now <= 0) {
            $this->fail(
                PrivilegedTotpMfaViolation::TRANSACTION_FAILURE,
                'Privileged TOTP MFA clock returned an invalid timestamp.',
            );
        }

        return $now;
    }

    private function verificationFailed(): never
    {
        $this->fail(
            PrivilegedTotpMfaViolation::VERIFICATION_FAILED,
            'Privileged TOTP MFA verification failed.',
        );
    }

    private function fail(string $code, string $message): never
    {
        throw new PrivilegedTotpMfaViolation($code, $message);
    }
}
