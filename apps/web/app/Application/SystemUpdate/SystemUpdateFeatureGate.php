<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
interface SystemUpdateFeatureGate
{
    public function controlPlaneEnabled(): bool;

    public function installEnabled(): bool;
}
