<?php

declare(strict_types=1);

namespace App\Application\Identity;

use RuntimeException;

// Author by Lab | zefry
final class FirstControlPrincipalCredentialBootstrapViolation extends RuntimeException
{
    public const INVALID_PASSWORD = 'INVALID_PASSWORD';
    public const FEATURE_DISABLED = 'FEATURE_DISABLED';
    public const BOOTSTRAP_INELIGIBLE = 'BOOTSTRAP_INELIGIBLE';
    public const CREDENTIAL_ALREADY_EXISTS = 'CREDENTIAL_ALREADY_EXISTS';
    public const ACTIVE_ENROLLMENT_EXISTS = 'ACTIVE_ENROLLMENT_EXISTS';
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
