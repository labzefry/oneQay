<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class RecoveryCodeService
{
    private const CODE_PATTERN = '/\Arq1\.[A-Za-z0-9_-]{22}\.[A-Za-z0-9_-]{43}\z/D';
    private const CODE_ID_PATTERN = '/\A[0-9a-f]{32}\z/D';

    public function __construct(
        private VerifyFirstPartyIdentityCredential $credentials,
        private RecoveryCodeRepository $repository,
        private PersistenceTransaction $transaction,
        private RecoveryCodeClock $clock,
    ) {}

    public function rotate(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] string $password,
        string $correlationId,
    ): IssuedRecoveryCodeSet {
        $this->assertCorrelationId($correlationId, true);

        if (! $this->credentials->verify($tenantId, $identityId, $password)) {
            $this->rotationFailed();
        }

        $now = $this->validNow(true);

        try {
            $codes = $this->transaction->run(
                fn (): array => $this->repository->rotate(
                    $tenantId,
                    $identityId,
                    $now,
                    $correlationId,
                ),
            );

            return new IssuedRecoveryCodeSet($codes);
        } catch (RecoveryCodeViolation|DurablePersistenceViolation|InvalidArgumentException) {
            $this->rotationFailed();
        }
    }

    public function prove(
        #[\SensitiveParameter] string $recoveryCode,
        string $correlationId,
    ): VerifiedRecoveryProof {
        $this->assertCorrelationId($correlationId, false);

        if (preg_match(self::CODE_PATTERN, $recoveryCode) !== 1) {
            $this->verificationFailed();
        }

        $now = $this->validNow(false);

        try {
            $verified = $this->transaction->run(
                fn (): array => $this->repository->consume(
                    $recoveryCode,
                    $now,
                    $correlationId,
                ),
            );

            if (array_keys($verified) !== ['tenant_id', 'identity_id', 'code_id', 'proved_at_unix']
                || ! is_string($verified['tenant_id'] ?? null)
                || ! is_string($verified['identity_id'] ?? null)
                || ! is_string($verified['code_id'] ?? null)
                || preg_match(self::CODE_ID_PATTERN, $verified['code_id']) !== 1
                || ! is_int($verified['proved_at_unix'] ?? null)
                || $verified['proved_at_unix'] !== $now) {
                $this->verificationFailed();
            }

            return new VerifiedRecoveryProof(
                TenantId::fromString($verified['tenant_id']),
                PlatformIdentityId::fromString($verified['identity_id']),
                $verified['code_id'],
                $verified['proved_at_unix'],
            );
        } catch (RecoveryCodeViolation|DurablePersistenceViolation|InvalidArgumentException) {
            $this->verificationFailed();
        }
    }

    private function assertCorrelationId(string $correlationId, bool $rotation): void
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
        throw new RecoveryCodeViolation(
            RecoveryCodeViolation::ROTATION_FAILED,
            'Authentication recovery request failed.',
        );
    }

    private function verificationFailed(): never
    {
        throw new RecoveryCodeViolation(
            RecoveryCodeViolation::VERIFICATION_FAILED,
            'Authentication recovery request failed.',
        );
    }
}
