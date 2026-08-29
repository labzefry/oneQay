<?php

declare(strict_types=1);

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
