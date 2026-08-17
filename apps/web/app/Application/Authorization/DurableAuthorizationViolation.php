<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use RuntimeException;

// Author by Lab | zefry
final class DurableAuthorizationViolation extends RuntimeException
{
    public const PERSISTENCE_DISABLED = 'DURABLE_AUTHORIZATION_PERSISTENCE_DISABLED';
    public const RUNTIME_DENIED = 'DURABLE_AUTHORIZATION_RUNTIME_DENIED';
    public const POLICY_DATA_INVALID = 'DURABLE_AUTHORIZATION_POLICY_DATA_INVALID';
    public const STORAGE_FAILURE = 'DURABLE_AUTHORIZATION_STORAGE_FAILURE';
    public const PERMISSION_DENIED = 'DURABLE_AUTHORIZATION_PERMISSION_DENIED';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}
