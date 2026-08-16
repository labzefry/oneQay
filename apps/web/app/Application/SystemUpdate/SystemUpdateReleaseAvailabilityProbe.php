<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
interface SystemUpdateReleaseAvailabilityProbe
{
    public function probe(): SystemUpdateReleaseAvailability;
}
