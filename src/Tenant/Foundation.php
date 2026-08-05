<?php

declare(strict_types=1);

namespace OneQay\Tenant;

use OneQay\Auth\SessionGuard;
use OneQay\Auth\SessionStore;

final readonly class TenantIdentifier
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '' || strlen($normalized) > 64) {
            throw new \InvalidArgumentException('Tenant identifier is required and must not exceed 64 characters.');
        }

        if (str_contains($normalized, '.') || str_contains($normalized, '/') || str_contains($normalized, ':')) {
            throw new \InvalidArgumentException('Tenant identifier must not be a domain, URL, or path.');
        }

        if (preg_match('/^[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?$/', $normalized) !== 1) {
            throw new \InvalidArgumentException('Tenant identifier format is invalid.');
        }

        $this->value = $normalized;
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }
}

final readonly class TenantContext
{
    public function __construct(public TenantIdentifier $tenantId)
    {
    }
}

final class TenantContextException extends \RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message)
    {
        parent::__construct($message);
    }
}

interface TenantContextResolver
{
    public function select(string $tenantIdentifier, string $fingerprint): TenantContext;

    public function resolve(string $fingerprint): ?TenantContext;

    public function requireContext(string $fingerprint): TenantContext;

    public function clear(): void;
}

final class SessionTenantContextResolver implements TenantContextResolver
{
    public const REQUIRED = 'TENANT_CONTEXT_REQUIRED';
    public const INVALID = 'TENANT_CONTEXT_INVALID';
    public const UNAVAILABLE = 'TENANT_CONTEXT_UNAVAILABLE';

    private const ACTIVE_TENANT_ID = 'tenant.active_id';

    public function __construct(
        private readonly SessionStore $session,
        private readonly SessionGuard $guard,
    ) {
    }

    public function select(string $tenantIdentifier, string $fingerprint): TenantContext
    {
        if ($this->guard->user($fingerprint) === null) {
            throw new TenantContextException(self::UNAVAILABLE, 'Tenant context requires an authenticated session.');
        }

        try {
            $tenantId = new TenantIdentifier($tenantIdentifier);
        } catch (\InvalidArgumentException $exception) {
            throw new TenantContextException(self::INVALID, 'Tenant context is invalid.');
        }

        $current = $this->session->get(self::ACTIVE_TENANT_ID);
        if (!is_string($current) || !hash_equals($current, $tenantId->value)) {
            $this->session->regenerate();
        }

        $this->session->put(self::ACTIVE_TENANT_ID, $tenantId->value);

        return new TenantContext($tenantId);
    }

    public function resolve(string $fingerprint): ?TenantContext
    {
        if ($this->guard->user($fingerprint) === null) {
            $this->clear();
            return null;
        }

        $stored = $this->session->get(self::ACTIVE_TENANT_ID);
        if (!is_string($stored)) {
            return null;
        }

        try {
            return new TenantContext(new TenantIdentifier($stored));
        } catch (\InvalidArgumentException) {
            $this->clear();
            throw new TenantContextException(self::INVALID, 'Tenant context is invalid.');
        }
    }

    public function requireContext(string $fingerprint): TenantContext
    {
        $context = $this->resolve($fingerprint);

        if ($context === null) {
            throw new TenantContextException(self::REQUIRED, 'Tenant context is required.');
        }

        return $context;
    }

    public function clear(): void
    {
        $this->session->remove(self::ACTIVE_TENANT_ID);
    }
}
