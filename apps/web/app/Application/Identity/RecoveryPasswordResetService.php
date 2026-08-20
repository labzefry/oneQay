<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Application\Persistence\DurablePersistenceViolation;
use App\Application\Persistence\PersistenceTransaction;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class RecoveryPasswordResetService
{
    private const CODE_ID_PATTERN = '/\A[0-9a-f]{32}\z/D';
    private const MIN_PASSWORD_BYTES = 12;
    private const MAX_PASSWORD_BYTES = 4096;

    public function __construct(
        private RecoveryPasswordResetRepository $repository,
        private PersistenceTransaction $transaction,
        private RecoveryCodeClock $clock,
    ) {}

    public function reset(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $codeId,
        #[\SensitiveParameter] string $password,
        string $correlationId,
    ): void {
        if (preg_match(self::CODE_ID_PATTERN, $codeId) !== 1
            || $correlationId === ''
            || strlen($correlationId) > 128
            || strlen($password) < self::MIN_PASSWORD_BYTES
            || strlen($password) > self::MAX_PASSWORD_BYTES) {
            $this->failed();
        }

        $now = $this->clock->nowUnix();
        if ($now <= 0) {
            $this->failed();
        }

        try {
            $this->transaction->run(function () use (
                $tenantId,
                $identityId,
                $codeId,
                $password,
                $now,
                $correlationId,
            ): void {
                $this->repository->complete(
                    $tenantId,
                    $identityId,
                    $codeId,
                    $password,
                    $now,
                    $correlationId,
                );
            });
        } catch (RecoveryPasswordResetViolation|DurablePersistenceViolation|InvalidArgumentException) {
            $this->failed();
        }
    }

    private function failed(): never
    {
        throw new RecoveryPasswordResetViolation(
            RecoveryPasswordResetViolation::RESET_FAILED,
            'Authentication recovery request failed.',
        );
    }
}
