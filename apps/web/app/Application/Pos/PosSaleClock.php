<?php

declare(strict_types=1);

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

namespace App\Application\Pos;

// Author by Lab | zefry
interface PosSaleClock
{
    public function nowUnix(): int;
}

// Sprint48 JRN-005 Sprint46 compatibility preservation anchor.
