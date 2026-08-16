<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedConfigurationSource;
use App\Application\SystemUpdate\SharedConfiguration\SystemUpdateSharedRuntimeConfiguration;

// Author by Lab | zefry
final class LaravelSystemUpdateSharedConfigurationSource implements SystemUpdateSharedConfigurationSource
{
    public function snapshot(): SystemUpdateSharedRuntimeConfiguration
    {
        return new SystemUpdateSharedRuntimeConfiguration(
            SystemUpdateSharedRuntimeConfiguration::PROFILE,
            SystemUpdateSharedRuntimeConfiguration::LAYOUT_VERSION,
            (string) config('oneqay.runtime_class', ''),
            (string) config('app.env', ''),
            (bool) config('app.debug', false),
            (string) config('app.url', ''),
            (string) config('logging.default', ''),
            (string) config('session.driver', ''),
            (string) config('cache.default', ''),
        );
    }
}
