<?php

declare(strict_types=1);

namespace App\Application\Identity;

use RuntimeException;

// Author by Lab | zefry
final class FirstPartySessionAuthorityViolation extends RuntimeException
{
    public const FEATURE_DISABLED = 'FEATURE_DISABLED';
    public const INVALID_STATE = 'INVALID_STATE';
    public const AUTHORITY_DENIED = 'AUTHORITY_DENIED';
    public const CURRENT_SESSION_TARGET = 'CURRENT_SESSION_TARGET';
    public const STORAGE_FAILURE = 'STORAGE_FAILURE';

    public function __construct(
        public readonly string $errorCode,
        string $message = 'First-party session authority request failed.',
    ) {
        parent::__construct($message);
    }
}
