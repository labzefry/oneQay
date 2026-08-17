<?php

declare(strict_types=1);

namespace App\Application\Authorization;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PolicyMutationOperation
{
    public const ROLE_CREATE = 'role.create';
    public const PERMISSION_GRANT = 'permission.grant';
    public const PERMISSION_REVOKE = 'permission.revoke';
    public const ROLE_ASSIGN_TENANT = 'role.assign.tenant';
    public const ROLE_ASSIGN_ORGANIZATION = 'role.assign.organization';
    public const ROLE_ASSIGN_OUTLET = 'role.assign.outlet';
    public const ROLE_ASSIGN_DEVICE = 'role.assign.device';
    public const ROLE_REVOKE_TENANT = 'role.revoke.tenant';
    public const ROLE_REVOKE_ORGANIZATION = 'role.revoke.organization';
    public const ROLE_REVOKE_OUTLET = 'role.revoke.outlet';
    public const ROLE_REVOKE_DEVICE = 'role.revoke.device';

    private const VALUES = [self::ROLE_CREATE, self::PERMISSION_GRANT, self::PERMISSION_REVOKE, self::ROLE_ASSIGN_TENANT, self::ROLE_ASSIGN_ORGANIZATION, self::ROLE_ASSIGN_OUTLET, self::ROLE_ASSIGN_DEVICE, self::ROLE_REVOKE_TENANT, self::ROLE_REVOKE_ORGANIZATION, self::ROLE_REVOKE_OUTLET, self::ROLE_REVOKE_DEVICE];

    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        if (! in_array($value, self::VALUES, true)) {
            throw new InvalidArgumentException('Policy mutation operation is invalid.');
        }
        return new self($value);
    }

    public function value(): string { return $this->value; }
    public function isPermissionMutation(): bool { return in_array($this->value, [self::PERMISSION_GRANT, self::PERMISSION_REVOKE], true); }
    public function isAssignmentMutation(): bool { return str_starts_with($this->value, 'role.assign.') || str_starts_with($this->value, 'role.revoke.'); }
    public function isRevocation(): bool { return $this->value === self::PERMISSION_REVOKE || str_starts_with($this->value, 'role.revoke.'); }

    public function scopeType(): string
    {
        foreach (['tenant', 'organization', 'outlet', 'device'] as $scope) {
            if (str_ends_with($this->value, '.'.$scope)) { return $scope; }
        }
        return 'tenant';
    }
}
