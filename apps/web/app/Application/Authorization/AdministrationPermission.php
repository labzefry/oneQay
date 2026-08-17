<?php

declare(strict_types=1);

namespace App\Application\Authorization;

// Author by Lab | zefry
final class AdministrationPermission
{
    public const MANAGE = 'authorization.policy.manage';

    public static function manage(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::MANAGE);
    }

    public static function isControl(PermissionIdentifier $permission): bool
    {
        return hash_equals(self::MANAGE, $permission->value());
    }
}
