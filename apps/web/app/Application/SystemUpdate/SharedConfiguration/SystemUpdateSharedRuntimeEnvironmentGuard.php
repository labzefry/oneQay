<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

// Author by Lab | zefry
interface SystemUpdateSharedRuntimeEnvironmentGuard
{
    public function assertReady(int $nowUnix): SystemUpdateSharedRuntimeEnvironmentStatus;
}
