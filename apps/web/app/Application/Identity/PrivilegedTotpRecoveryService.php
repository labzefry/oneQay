<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PrivilegedTotpRecoveryService
{
    private const CODE_PATTERN = '/\Amq1\.[A-Za-z0-9_-]{22}\.[A-Za-z0-9_-]{43}\z/D';
    private const TOTP_PATTERN = '/\A[0-9]{6}\z/D';

    public function __construct(
        private VerifyFirstPartyIdentityCredential $credentials,
        private PrivilegedTotpMfaService $totp,
        private PrivilegedTotpEngine $engine,
        private PrivilegedTotpFactorEpochRepository $epochs,
        private PrivilegedTotpRecoveryRepository $repository,
        private PersistenceTransaction $transaction,
        private PrivilegedTotpRecoveryClock $clock,
    ) {}

    public function rotate(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] string $password,
        #[\SensitiveParameter] string $currentTotp,
        string $correlationId,
    ): IssuedPrivilegedTotpRecoveryCodeSet {
        $this->assertCorrelation($correlationId, true);
        if (! $this->credentials->verify($tenantId, $identityId, $password)) {
            $this->rotationFailed();
        }
        $this->assertTotp($currentTotp, true);

        try {
            $this->totp->challenge($tenantId, $identityId, $currentTotp);
            $epoch = $this->epochs->currentEpoch($tenantId, $identityId);
            $now = $this->validNow(true);
            $codes = $this->transaction->run(fn (): array => $this->repository->rotate(
                $tenantId,
                $identityId,
                $epoch,
                $now,
                $correlationId,
            ));

            return new IssuedPrivilegedTotpRecoveryCodeSet($codes);
        } catch (PrivilegedTotpMfaViolation|PrivilegedTotpRecoveryViolation|DurablePersistenceViolation|InvalidArgumentException) {
            $this->rotationFailed();
        }
    }

    public function prove(
        #[\SensitiveParameter] string $recoveryCode,
        string $correlationId,
    ): VerifiedPrivilegedTotpRecoveryProof {
        $this->assertCorrelation($correlationId, false);
        if (preg_match(self::CODE_PATTERN, $recoveryCode) !== 1) {
            $this->verificationFailed();
        }
        $now = $this->validNow(false);

        try {
            $verified = $this->transaction->run(fn (): array => $this->repository->consume(
                $recoveryCode,
                $now,
                $correlationId,
            ));
            if (array_keys($verified) !== ['tenant_id', 'identity_id', 'code_id', 'factor_epoch', 'proved_at_unix']
                || ! is_string($verified['tenant_id'] ?? null)
                || ! is_string($verified['identity_id'] ?? null)
                || ! is_string($verified['code_id'] ?? null)
                || ! is_int($verified['factor_epoch'] ?? null)
                || ! is_int($verified['proved_at_unix'] ?? null)
                || $verified['factor_epoch'] < 0
                || $verified['proved_at_unix'] !== $now) {
                $this->verificationFailed();
            }

            return new VerifiedPrivilegedTotpRecoveryProof(
                TenantId::fromString($verified['tenant_id']),
                PlatformIdentityId::fromString($verified['identity_id']),
                $verified['code_id'],
                $verified['factor_epoch'],
                $verified['proved_at_unix'],
            );
        } catch (PrivilegedTotpRecoveryViolation|DurablePersistenceViolation|InvalidArgumentException) {
            $this->verificationFailed();
        }
    }

    /** @return array{secret:string,provisioning_uri:string,sealed_replacement:string} */
    public function startReplacement(VerifiedPrivilegedTotpRecoveryProof $proof): array
    {
        try {
            $this->repository->assertProofCurrent($proof);
            $current = $this->epochs->currentEpoch($proof->tenantId(), $proof->identityId());
            if ($current !== $proof->factorEpoch()) {
                $this->replacementFailed();
            }
            $secret = $this->engine->generateSecret();
            if (preg_match('/\A[A-Z2-7]{32}\z/D', $secret) !== 1) {
                $this->replacementFailed();
            }
            $now = $this->validNow(false);
            $sealed = $this->repository->sealReplacementSecret($proof, $secret, $now);

            return [
                'secret' => $secret,
                'provisioning_uri' => $this->engine->provisioningUri($proof->tenantId(), $proof->identityId(), $secret),
                'sealed_replacement' => $sealed,
            ];
        } catch (PrivilegedTotpRecoveryViolation|DurablePersistenceViolation) {
            $this->replacementFailed();
        }
    }

    public function confirmReplacement(
        VerifiedPrivilegedTotpRecoveryProof $proof,
        #[\SensitiveParameter] string $sealedReplacement,
        #[\SensitiveParameter] string $newTotp,
        string $correlationId,
    ): int {
        $this->assertCorrelation($correlationId, false);
        $this->assertTotp($newTotp, false);
        try {
            $this->repository->assertProofCurrent($proof);
            $current = $this->epochs->currentEpoch($proof->tenantId(), $proof->identityId());
            if ($current !== $proof->factorEpoch()) {
                $this->replacementFailed();
            }
            $secret = $this->repository->openReplacementSecret($proof, $sealedReplacement);
            $now = $this->validNow(false);
            $matched = $this->engine->matchTimeStep($secret, $newTotp, $now);
            if ($matched === null) {
                $this->replacementFailed();
            }

            $newEpoch = $this->transaction->run(fn (): int => $this->repository->replaceFactor(
                $proof,
                $secret,
                $matched,
                $now,
                $correlationId,
            ));
            if ($newEpoch !== $proof->factorEpoch() + 1) {
                $this->replacementFailed();
            }

            return $newEpoch;
        } catch (PrivilegedTotpRecoveryViolation|DurablePersistenceViolation) {
            $this->replacementFailed();
        }
    }

    private function assertTotp(string $code, bool $rotation): void
    {
        if (preg_match(self::TOTP_PATTERN, $code) !== 1) {
            $rotation ? $this->rotationFailed() : $this->replacementFailed();
        }
    }

    private function assertCorrelation(string $correlationId, bool $rotation): void
    {
        if ($correlationId === '' || strlen($correlationId) > 128) {
            $rotation ? $this->rotationFailed() : $this->verificationFailed();
        }
    }

    private function validNow(bool $rotation): int
    {
        $now = $this->clock->nowUnix();
        if ($now <= 0) {
            $rotation ? $this->rotationFailed() : $this->verificationFailed();
        }
        return $now;
    }

    private function rotationFailed(): never
    {
        throw new PrivilegedTotpRecoveryViolation(PrivilegedTotpRecoveryViolation::ROTATION_FAILED, 'Privileged TOTP recovery request failed.');
    }

    private function verificationFailed(): never
    {
        throw new PrivilegedTotpRecoveryViolation(PrivilegedTotpRecoveryViolation::VERIFICATION_FAILED, 'Privileged TOTP recovery request failed.');
    }

    private function replacementFailed(): never
    {
        throw new PrivilegedTotpRecoveryViolation(PrivilegedTotpRecoveryViolation::REPLACEMENT_FAILED, 'Privileged TOTP recovery request failed.');
    }
}
