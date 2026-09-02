<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface InventoryBaselineClock
{
    public function nowUnix(): int;
}
