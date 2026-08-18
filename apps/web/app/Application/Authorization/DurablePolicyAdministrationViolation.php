<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use RuntimeException;

// Author by Lab | zefry
final class DurablePolicyAdministrationViolation extends RuntimeException
{
    public const AUTHORIZATION_DENIED = 'DURABLE_POLICY_ADMIN_AUTHORIZATION_DENIED';
    public const PERSISTENCE_DISABLED = 'DURABLE_POLICY_ADMIN_PERSISTENCE_DISABLED';
    public const RUNTIME_DENIED = 'DURABLE_POLICY_ADMIN_RUNTIME_DENIED';
    public const INVALID_MUTATION = 'DURABLE_POLICY_ADMIN_INVALID_MUTATION';
    public const PROTECTED_CONTROL_AUTHORITY = 'DURABLE_POLICY_ADMIN_PROTECTED_CONTROL_AUTHORITY';
    public const TARGET_SCOPE_INVALID = 'DURABLE_POLICY_ADMIN_TARGET_SCOPE_INVALID';
    public const TARGET_ACCESS_DENIED = 'DURABLE_POLICY_ADMIN_TARGET_ACCESS_DENIED';
    public const MUTATION_CONFLICT = 'DURABLE_POLICY_ADMIN_MUTATION_CONFLICT';
    public const RELATIONSHIP_CONFLICT = 'DURABLE_POLICY_ADMIN_RELATIONSHIP_CONFLICT';
    public const STORAGE_FAILURE = 'DURABLE_POLICY_ADMIN_STORAGE_FAILURE';
    public const TRANSACTION_FAILURE = 'DURABLE_POLICY_ADMIN_TRANSACTION_FAILURE';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}
