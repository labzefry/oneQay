<?php

declare(strict_types=1);

namespace App\Application\Bootstrap;

use RuntimeException;

// Author by Lab | zefry
final class MerchantContextBootstrapViolation extends RuntimeException
{
    public const AUTHORIZATION_DENIED = 'MERCHANT_CONTEXT_BOOTSTRAP_AUTHORIZATION_DENIED';
    public const TENANT_ALREADY_EXISTS = 'MERCHANT_CONTEXT_BOOTSTRAP_TENANT_ALREADY_EXISTS';
    public const INVALID_PASSWORD = 'MERCHANT_CONTEXT_BOOTSTRAP_INVALID_PASSWORD';
    public const INVALID_BOOTSTRAP = 'MERCHANT_CONTEXT_BOOTSTRAP_INVALID_BOOTSTRAP';
    public const PERSISTENCE_DISABLED = 'MERCHANT_CONTEXT_BOOTSTRAP_PERSISTENCE_DISABLED';
    public const RUNTIME_DENIED = 'MERCHANT_CONTEXT_BOOTSTRAP_RUNTIME_DENIED';
    public const RELATIONSHIP_CONFLICT = 'MERCHANT_CONTEXT_BOOTSTRAP_RELATIONSHIP_CONFLICT';
    public const STORAGE_FAILURE = 'MERCHANT_CONTEXT_BOOTSTRAP_STORAGE_FAILURE';
    public const TRANSACTION_FAILURE = 'MERCHANT_CONTEXT_BOOTSTRAP_TRANSACTION_FAILURE';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}
