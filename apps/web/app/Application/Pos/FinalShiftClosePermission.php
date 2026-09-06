<?php

declare(strict_types=1);

namespace App\Application\Pos;

use App\Application\Authorization\PermissionIdentifier;

// Author by Lab | zefry
final class FinalShiftClosePermission
{
    public const IDENTIFIER = 'pos.shift.close';

    public static function identifier(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::IDENTIFIER);
    }
}
