<?php

declare(strict_types=1);

namespace App\Application\Identity;

use RuntimeException;

// Author by Lab | zefry
final class RecoveryPasswordResetViolation extends RuntimeException
{
    public const RESET_FAILED = 'RESET_FAILED';

    public function __construct(
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }
}
