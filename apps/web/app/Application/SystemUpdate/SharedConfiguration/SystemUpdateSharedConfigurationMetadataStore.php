<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\SystemUpdatePreparedRelease;

// Author by Lab | zefry
interface SystemUpdateSharedConfigurationMetadataStore
{
    public function record(
        SystemUpdatePreparedRelease $release,
        SystemUpdateSharedConfigurationCompatibility $compatibility,
    ): void;
}
