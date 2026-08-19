<?php

declare(strict_types=1);

namespace App\Application\Identity;

use RuntimeException;

// Author by Lab | zefry
final class PrivilegedTotpMfaViolation extends RuntimeException
{
    public const FEATURE_DISABLED = 'PRIVILEGED_TOTP_MFA_FEATURE_DISABLED';
    public const PERSISTENCE_DISABLED = 'PRIVILEGED_TOTP_MFA_PERSISTENCE_DISABLED';
    public const RUNTIME_DENIED = 'PRIVILEGED_TOTP_MFA_RUNTIME_DENIED';
    public const AUTHORIZATION_DENIED = 'PRIVILEGED_TOTP_MFA_AUTHORIZATION_DENIED';
    public const FACTOR_STATE_INVALID = 'PRIVILEGED_TOTP_MFA_FACTOR_STATE_INVALID';
    public const ENROLLMENT_DENIED = 'PRIVILEGED_TOTP_MFA_ENROLLMENT_DENIED';
    public const VERIFICATION_FAILED = 'PRIVILEGED_TOTP_MFA_VERIFICATION_FAILED';
    public const REPLAY_DENIED = 'PRIVILEGED_TOTP_MFA_REPLAY_DENIED';
    public const STORAGE_FAILURE = 'PRIVILEGED_TOTP_MFA_STORAGE_FAILURE';
    public const TRANSACTION_FAILURE = 'PRIVILEGED_TOTP_MFA_TRANSACTION_FAILURE';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}
