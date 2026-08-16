<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\SystemUpdatePreparedRelease;

// Author by Lab | zefry
interface SystemUpdateSharedConfigurationGuard
{
    public function assertCompatible(
        SystemUpdatePreparedRelease $release,
        int $nowUnix,
    ): SystemUpdateSharedConfigurationCompatibility;
}
