<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use RuntimeException;

// Author by Lab | zefry
final class PolicyAdministrationDeliveryViolation extends RuntimeException
{
    public const INVALID_PAYLOAD = 'POLICY_ADMIN_DELIVERY_INVALID_PAYLOAD';
    public const AUTHORIZATION_DENIED = 'POLICY_ADMIN_DELIVERY_AUTHORIZATION_DENIED';
    public const MUTATION_CONFLICT = 'POLICY_ADMIN_DELIVERY_MUTATION_CONFLICT';
    public const PERSISTENCE_UNAVAILABLE = 'POLICY_ADMIN_DELIVERY_PERSISTENCE_UNAVAILABLE';
    public const MUTATION_FAILED = 'POLICY_ADMIN_DELIVERY_MUTATION_FAILED';

    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}
