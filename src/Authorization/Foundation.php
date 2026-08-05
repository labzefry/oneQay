<?php

declare(strict_types=1);

namespace OneQay\Authorization;

use OneQay\Auth\SessionGuard;
use OneQay\Tenant\TenantContextResolver;
use OneQay\Tenant\TenantIdentifier;

final readonly class AuthorizationSubject
{
    public function __construct(public string $userId, public TenantIdentifier $tenantId)
    {
        if ($this->userId === '') {
            throw new \InvalidArgumentException('Authorization subject user is required.');
        }
    }
}

final readonly class PermissionIdentifier
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        $containsIdentitySegment = false;

        foreach (explode('.', $normalized) as $segment) {
            if (str_starts_with($segment, 'tenant_') || str_starts_with($segment, 'user_')) {
                $containsIdentitySegment = true;
                break;
            }
        }

        $isValidFormat = preg_match('/^[a-z][a-z0-9_-]*(?:\.[a-z][a-z0-9_-]*)+$/', $normalized) === 1;

        if (!$isValidFormat || $containsIdentitySegment || strlen($normalized) > 96) {
            throw new \InvalidArgumentException('Permission identifier is invalid.');
        }

        $this->value = $normalized;
    }
}

final readonly class AuthorizationContext
{
    public function __construct(
        public AuthorizationSubject $subject,
        public PermissionIdentifier $permission,
        public string $correlationId,
    ) {
        if ($this->correlationId === '') {
            throw new \InvalidArgumentException('Correlation ID is required.');
        }
    }
}

final readonly class AuthorizationDecision
{
    private function __construct(public bool $isAllowed, public string $reasonCode) {}

    public static function allow(): self { return new self(true, 'AUTHORIZATION_ALLOWED'); }
    public static function deny(string $reasonCode): self { return new self(false, $reasonCode); }
}

interface AuthorizationPolicy
{
    public function decide(AuthorizationContext $context): AuthorizationDecision;
}

final class DenyByDefaultPolicy implements AuthorizationPolicy
{
    public function decide(AuthorizationContext $context): AuthorizationDecision
    {
        return AuthorizationDecision::deny(AuthorizationService::PERMISSION_DENIED);
    }
}

final class ExplicitGrantPolicy implements AuthorizationPolicy
{
    /** @var array<string, true> */
    private array $grants = [];

    public function grant(string $userId, TenantIdentifier $tenantId, PermissionIdentifier $permission): void
    {
        $this->grants[$this->key($userId, $tenantId, $permission)] = true;
    }

    public function decide(AuthorizationContext $context): AuthorizationDecision
    {
        $key = $this->key($context->subject->userId, $context->subject->tenantId, $context->permission);
        return isset($this->grants[$key])
            ? AuthorizationDecision::allow()
            : AuthorizationDecision::deny(AuthorizationService::PERMISSION_DENIED);
    }

    private function key(string $userId, TenantIdentifier $tenantId, PermissionIdentifier $permission): string
    {
        return hash('sha256', $userId . "\0" . $tenantId->value . "\0" . $permission->value);
    }
}

final class AuthorizationException extends \RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

final class AuthorizationService
{
    public const AUTHENTICATION_REQUIRED = 'AUTHORIZATION_AUTHENTICATION_REQUIRED';
    public const TENANT_REQUIRED = 'AUTHORIZATION_TENANT_REQUIRED';
    public const CONTEXT_INVALID = 'AUTHORIZATION_CONTEXT_INVALID';
    public const CROSS_TENANT_DENIED = 'AUTHORIZATION_CROSS_TENANT_DENIED';
    public const PERMISSION_DENIED = 'AUTHORIZATION_PERMISSION_DENIED';

    public function __construct(
        private readonly SessionGuard $guard,
        private readonly TenantContextResolver $tenantResolver,
        private readonly AuthorizationPolicy $policy,
    ) {}

    public function subject(string $fingerprint): AuthorizationSubject
    {
        $user = $this->guard->user($fingerprint);
        if ($user === null) {
            throw new AuthorizationException(self::AUTHENTICATION_REQUIRED, 'Authentication is required.');
        }

        $tenant = $this->tenantResolver->resolve($fingerprint);
        if ($tenant === null) {
            throw new AuthorizationException(self::TENANT_REQUIRED, 'Tenant context is required.');
        }

        return new AuthorizationSubject($user->id, $tenant->tenantId);
    }

    public function evaluate(AuthorizationContext $context, string $fingerprint): AuthorizationDecision
    {
        $active = $this->subject($fingerprint);
        if ($active->userId !== $context->subject->userId) {
            throw new AuthorizationException(self::CONTEXT_INVALID, 'Authorization context is invalid.');
        }
        if (!$active->tenantId->equals($context->subject->tenantId)) {
            throw new AuthorizationException(self::CROSS_TENANT_DENIED, 'Cross-tenant authorization is denied.');
        }

        return $this->policy->decide($context);
    }

    public function requireAllowed(AuthorizationContext $context, string $fingerprint): void
    {
        $decision = $this->evaluate($context, $fingerprint);
        if (!$decision->isAllowed) {
            throw new AuthorizationException(self::PERMISSION_DENIED, 'Permission is denied.');
        }
    }
}
