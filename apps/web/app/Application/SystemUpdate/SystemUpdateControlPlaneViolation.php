<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

use RuntimeException;

// Author by Lab | zefry
final class SystemUpdateControlPlaneViolation extends RuntimeException
{
    public function __construct(private readonly string $safeCode)
    {
        parent::__construct('System update control plane request denied.');
    }

    public function safeCode(): string
    {
        return $this->safeCode;
    }
}
