<?php

declare(strict_types=1);

namespace App\Application\Identity;

use RuntimeException;

// Author by Lab | zefry
final class AuthenticatedPasswordChangeViolation extends RuntimeException
{
    public const CHANGE_FAILED = 'CHANGE_FAILED';

    public function __construct(
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }
}
