<?php

namespace App\Application\Persistence;

use RuntimeException;

// Author by Lab | zefry
final class DurablePersistenceViolation extends RuntimeException
{
    public const VERIFIED_TENANT_REQUIRED = 'DURABLE_PERSISTENCE_VERIFIED_TENANT_REQUIRED';
    public const TENANT_CONTEXT_MISMATCH = 'DURABLE_PERSISTENCE_TENANT_CONTEXT_MISMATCH';
    public const PERSISTENCE_DISABLED = 'DURABLE_PERSISTENCE_DISABLED';
    public const RUNTIME_DENIED = 'DURABLE_PERSISTENCE_RUNTIME_DENIED';
    public const RELATIONSHIP_CONFLICT = 'DURABLE_PERSISTENCE_RELATIONSHIP_CONFLICT';
    public const STORAGE_FAILURE = 'DURABLE_PERSISTENCE_STORAGE_FAILURE';
    public const TRANSACTION_FAILURE = 'DURABLE_PERSISTENCE_TRANSACTION_FAILURE';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}
