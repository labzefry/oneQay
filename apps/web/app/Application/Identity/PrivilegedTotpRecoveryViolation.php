<?php

declare(strict_types=1);

namespace App\Application\Identity;

use RuntimeException;

// Author by Lab | zefry
final class PrivilegedTotpRecoveryViolation extends RuntimeException
{
    public const FEATURE_DISABLED = 'FEATURE_DISABLED';
    public const PERSISTENCE_DISABLED = 'PERSISTENCE_DISABLED';
    public const RUNTIME_DENIED = 'RUNTIME_DENIED';
    public const ROTATION_FAILED = 'ROTATION_FAILED';
    public const VERIFICATION_FAILED = 'VERIFICATION_FAILED';
    public const RECOVERY_STATE_INVALID = 'RECOVERY_STATE_INVALID';
    public const REPLACEMENT_FAILED = 'REPLACEMENT_FAILED';
    public const EPOCH_INVALID = 'EPOCH_INVALID';

    public function __construct(public readonly string $reason, string $message)
    {
        parent::__construct($message);
    }
}
