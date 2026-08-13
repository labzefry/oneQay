<?php

declare(strict_types=1);

namespace App\Application\Pos;

use RuntimeException;

// Author by Lab | zefry
final class PosAccessViolation extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('POS context denied.');
    }
}
