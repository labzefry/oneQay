<?php

declare(strict_types=1);

namespace App\Application\Identity;

// Author by Lab | zefry
interface PrivilegedTotpClock
{
    public function nowUnix(): int;
}
