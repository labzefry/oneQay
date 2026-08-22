<?php

declare(strict_types=1);

namespace App\Application\Identity;

// Author by Lab | zefry
interface FirstPartySessionAuthorityClock
{
    public function nowUnix(): int;
}
