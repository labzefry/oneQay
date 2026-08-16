<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

// Author by Lab | zefry
interface SystemUpdateSharedConfigurationSource
{
    public function snapshot(): SystemUpdateSharedRuntimeConfiguration;
}
