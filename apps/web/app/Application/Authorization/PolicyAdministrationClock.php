<?php

declare(strict_types=1);

namespace App\Application\Authorization;

// Author by Lab | zefry
interface PolicyAdministrationClock
{
    public function nowUnix(): int;
}
