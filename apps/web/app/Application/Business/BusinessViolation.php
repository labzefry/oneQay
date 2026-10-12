<?php

declare(strict_types=1);

namespace App\Application\Business;

// Author by Lab | zefry
final class BusinessViolation extends \RuntimeException
{
    public function __construct(public readonly string $reason, public readonly int $status = 422)
    {
        parent::__construct($reason);
    }
}
