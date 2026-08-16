<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
final readonly class SystemUpdateDeploymentLock
{
    public function __construct(
        private string $operationId,
        private string $ownerIdentityRef,
        private int $acquiredAtUnix,
        private int $leaseExpiresAtUnix,
    ) {
        if (preg_match('/\Aop-[0-9a-f]{16}\z/', $operationId) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_operation_id');
        }

        if ($ownerIdentityRef === '' || strlen($ownerIdentityRef) > 96) {
            throw new SystemUpdateControlPlaneViolation('invalid_lock_owner');
        }

        if ($acquiredAtUnix <= 0 || $leaseExpiresAtUnix <= $acquiredAtUnix) {
            throw new SystemUpdateControlPlaneViolation('invalid_lock_lease');
        }
    }

    public function operationId(): string
    {
        return $this->operationId;
    }

    public function ownerIdentityRef(): string
    {
        return $this->ownerIdentityRef;
    }

    public function acquiredAtUnix(): int
    {
        return $this->acquiredAtUnix;
    }

    public function leaseExpiresAtUnix(): int
    {
        return $this->leaseExpiresAtUnix;
    }

    public function isExpiredAt(int $nowUnix): bool
    {
        return $nowUnix > $this->leaseExpiresAtUnix;
    }

    /** @return array<string, scalar> */
    public function toSafeArray(): array
    {
        return [
            'lock_version' => 1,
            'operation_id' => $this->operationId,
            'owner_identity_ref' => $this->ownerIdentityRef,
            'acquired_at_unix' => $this->acquiredAtUnix,
            'lease_expires_at_unix' => $this->leaseExpiresAtUnix,
        ];
    }
}
