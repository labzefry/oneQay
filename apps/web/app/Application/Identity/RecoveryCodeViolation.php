<?php

declare(strict_types=1);

namespace App\Application\Identity;

use RuntimeException;

// Author by Lab | zefry
final class RecoveryCodeViolation extends RuntimeException
{
    public const VERIFICATION_FAILED = 'VERIFICATION_FAILED';
    public const ROTATION_FAILED = 'ROTATION_FAILED';
    public const FEATURE_DISABLED = 'FEATURE_DISABLED';
    public const PERSISTENCE_DISABLED = 'PERSISTENCE_DISABLED';
    public const RUNTIME_DENIED = 'RUNTIME_DENIED';
    public const STORAGE_FAILURE = 'STORAGE_FAILURE';
    public const TRANSACTION_FAILURE = 'TRANSACTION_FAILURE';

    public function __construct(
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }
}
