<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Activation;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use App\Application\SystemUpdate\SystemUpdateDeploymentLock;
use App\Application\SystemUpdate\SystemUpdateDeploymentLockManager;

// Author by Lab | zefry
final class FilesystemSystemUpdateDeploymentLockManager implements SystemUpdateDeploymentLockManager
{
    public function __construct(
        private readonly string $privateRoot,
        private readonly SystemUpdateAtomicJsonFile $json,
    ) {
    }

    public function acquire(
        string $operationId,
        string $ownerIdentityRef,
        int $nowUnix,
        int $leaseSeconds,
    ): SystemUpdateDeploymentLock {
        if ($leaseSeconds < 30 || $leaseSeconds > 3600) {
            throw new SystemUpdateControlPlaneViolation('invalid_lock_lease');
        }

        $path = $this->lockPath();
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            throw new SystemUpdateControlPlaneViolation('deployment_lock_directory_unavailable');
        }

        $handle = @fopen($path, 'x');
        if ($handle === false) {
            $existing = $this->readExisting();

            if ($existing->isExpiredAt($nowUnix)) {
                throw new SystemUpdateControlPlaneViolation('deployment_lock_stale_reconciliation_required');
            }

            throw new SystemUpdateControlPlaneViolation('deployment_lock_held');
        }

        $lock = new SystemUpdateDeploymentLock(
            $operationId,
            $ownerIdentityRef,
            $nowUnix,
            $nowUnix + $leaseSeconds,
        );

        $payload = json_encode($lock->toSafeArray(), JSON_UNESCAPED_SLASHES);
        if (! is_string($payload) || fwrite($handle, $payload."\n") === false) {
            fclose($handle);
            @unlink($path);
            throw new SystemUpdateControlPlaneViolation('deployment_lock_write_failed');
        }

        fflush($handle);
        fclose($handle);
        @chmod($path, 0600);

        return $lock;
    }

    public function renew(
        SystemUpdateDeploymentLock $lock,
        int $nowUnix,
        int $leaseSeconds,
    ): SystemUpdateDeploymentLock {
        if ($leaseSeconds < 30 || $leaseSeconds > 3600) {
            throw new SystemUpdateControlPlaneViolation('invalid_lock_lease');
        }

        $existing = $this->readExisting();

        if (
            $existing->operationId() !== $lock->operationId()
            || $existing->ownerIdentityRef() !== $lock->ownerIdentityRef()
        ) {
            throw new SystemUpdateControlPlaneViolation('deployment_lock_owner_mismatch');
        }

        if ($existing->isExpiredAt($nowUnix)) {
            throw new SystemUpdateControlPlaneViolation('deployment_lock_stale_reconciliation_required');
        }

        $renewed = new SystemUpdateDeploymentLock(
            $lock->operationId(),
            $lock->ownerIdentityRef(),
            $lock->acquiredAtUnix(),
            $nowUnix + $leaseSeconds,
        );

        $this->json->write($this->lockPath(), $renewed->toSafeArray());

        return $renewed;
    }

    public function release(SystemUpdateDeploymentLock $lock): void
    {
        if (! is_file($this->lockPath())) {
            return;
        }

        $existing = $this->readExisting();

        if (
            $existing->operationId() !== $lock->operationId()
            || $existing->ownerIdentityRef() !== $lock->ownerIdentityRef()
        ) {
            throw new SystemUpdateControlPlaneViolation('deployment_lock_owner_mismatch');
        }

        if (! unlink($this->lockPath())) {
            throw new SystemUpdateControlPlaneViolation('deployment_lock_release_failed');
        }
    }

    private function readExisting(): SystemUpdateDeploymentLock
    {
        $payload = $this->json->read($this->lockPath());

        if ($payload === null) {
            throw new SystemUpdateControlPlaneViolation('deployment_lock_missing');
        }

        $operationId = $payload['operation_id'] ?? null;
        $ownerIdentityRef = $payload['owner_identity_ref'] ?? null;
        $acquiredAtUnix = $payload['acquired_at_unix'] ?? null;
        $leaseExpiresAtUnix = $payload['lease_expires_at_unix'] ?? null;

        if (
            ! is_string($operationId)
            || ! is_string($ownerIdentityRef)
            || ! is_int($acquiredAtUnix)
            || ! is_int($leaseExpiresAtUnix)
        ) {
            throw new SystemUpdateControlPlaneViolation('deployment_lock_malformed');
        }

        return new SystemUpdateDeploymentLock(
            $operationId,
            $ownerIdentityRef,
            $acquiredAtUnix,
            $leaseExpiresAtUnix,
        );
    }

    private function lockPath(): string
    {
        return rtrim($this->privateRoot, DIRECTORY_SEPARATOR).'/deployment-state/locks/system-update.lock';
    }
}
