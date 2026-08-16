<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate;

use App\Application\SystemUpdate\SystemUpdateFeatureGate;

// Author by Lab | zefry
final readonly class ConfiguredSystemUpdateFeatureGate implements SystemUpdateFeatureGate
{
    public function __construct(
        private bool $controlPlaneEnabled,
        private bool $installEnabled,
    ) {
    }

    public function controlPlaneEnabled(): bool
    {
        return $this->controlPlaneEnabled;
    }

    public function installEnabled(): bool
    {
        return $this->installEnabled;
    }
}
