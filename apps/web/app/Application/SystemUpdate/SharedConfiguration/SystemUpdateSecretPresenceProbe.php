<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

// Author by Lab | zefry
interface SystemUpdateSecretPresenceProbe
{
    public function available(SystemUpdateSecretReference $reference): bool;
}
