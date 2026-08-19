<?php

declare(strict_types=1);

namespace App\Application\Identity;

use RuntimeException;

// Author by Lab | zefry
final class PrivilegedStepUpViolation extends RuntimeException
{
    public const VERIFICATION_FAILED = 'PRIVILEGED_STEP_UP_VERIFICATION_FAILED';

    public function __construct(
        public readonly string $errorCode = self::VERIFICATION_FAILED,
        string $message = 'Privileged reauthentication failed.',
    ) {
        parent::__construct($message);
    }
}
