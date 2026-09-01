<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
interface CatalogPreparationClock
{
    public function nowUnix(): int;
}

// Sprint48 JRN-005 Sprint47 JRN-006 compatibility preservation anchor.
