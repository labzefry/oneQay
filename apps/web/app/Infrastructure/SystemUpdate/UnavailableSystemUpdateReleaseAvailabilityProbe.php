<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate;

use App\Application\SystemUpdate\SystemUpdateReleaseAvailability;
use App\Application\SystemUpdate\SystemUpdateReleaseAvailabilityProbe;

// Author by Lab | zefry
final class UnavailableSystemUpdateReleaseAvailabilityProbe implements SystemUpdateReleaseAvailabilityProbe
{
    public function probe(): SystemUpdateReleaseAvailability
    {
        return SystemUpdateReleaseAvailability::unavailable();
    }
}
