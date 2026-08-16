<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
interface SystemUpdateDeploymentLockManager
{
    public function acquire(
        string $operationId,
        string $ownerIdentityRef,
        int $nowUnix,
        int $leaseSeconds,
    ): SystemUpdateDeploymentLock;

    public function renew(
        SystemUpdateDeploymentLock $lock,
        int $nowUnix,
        int $leaseSeconds,
    ): SystemUpdateDeploymentLock;

    public function release(SystemUpdateDeploymentLock $lock): void;
}
