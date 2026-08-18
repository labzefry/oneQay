<?php

declare(strict_types=1);

namespace App\Application\Identity;

use RuntimeException;

// Author by Lab | zefry
final class InitialPasswordEnrollmentViolation extends RuntimeException
{
    public const AUTHORIZATION_DENIED = 'AUTHORIZATION_DENIED';
    public const SELF_ENROLLMENT_DENIED = 'SELF_ENROLLMENT_DENIED';
    public const TARGET_INELIGIBLE = 'TARGET_INELIGIBLE';
    public const CREDENTIAL_ALREADY_EXISTS = 'CREDENTIAL_ALREADY_EXISTS';
    public const ACTIVE_ENROLLMENT_EXISTS = 'ACTIVE_ENROLLMENT_EXISTS';
    public const ENROLLMENT_CONFLICT = 'ENROLLMENT_CONFLICT';
    public const INVALID_ENROLLMENT = 'INVALID_ENROLLMENT';
    public const INVALID_PASSWORD = 'INVALID_PASSWORD';
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
