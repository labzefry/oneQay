<?php

namespace App\Application\Access;

use RuntimeException;

// Author by Lab | zefry
final class DurableOrganizationalAccessViolation extends RuntimeException
{
    public const PERSISTENCE_DISABLED = 'DURABLE_ACCESS_PERSISTENCE_DISABLED';
    public const RUNTIME_DENIED = 'DURABLE_ACCESS_RUNTIME_DENIED';
    public const MEMBERSHIP_REQUIRED = 'DURABLE_ACCESS_MEMBERSHIP_REQUIRED';
    public const RELATIONSHIP_CONFLICT = 'DURABLE_ACCESS_RELATIONSHIP_CONFLICT';
    public const STORAGE_FAILURE = 'DURABLE_ACCESS_STORAGE_FAILURE';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}
