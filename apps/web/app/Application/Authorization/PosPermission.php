<?php

declare(strict_types=1);

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

namespace App\Application\Authorization;

// Author by Lab | zefry
final class PosPermission
{
    public const COMPLETE_SALE = 'pos.sale.complete';

    public static function completeSale(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::COMPLETE_SALE);
    }
}
