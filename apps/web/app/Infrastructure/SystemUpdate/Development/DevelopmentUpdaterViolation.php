<?php

declare(strict_types=1);

namespace App\Infrastructure\SystemUpdate\Development;

use RuntimeException;

// Author by Lab | zefry
final class DevelopmentUpdaterViolation extends RuntimeException
{
    public function __construct(private readonly string $safeCode)
    {
        parent::__construct('Governed development updater request denied.');
    }

    public function safeCode(): string
    {
        return $this->safeCode;
    }
}
