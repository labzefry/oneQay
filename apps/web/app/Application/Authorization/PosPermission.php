<?php

declare(strict_types=1);

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

namespace App\Application\Authorization;

// Author by Lab | zefry
final class PosPermission
{
    public const COMPLETE_SALE = 'pos.sale.complete';
    public const PREPARE_CATALOG = 'pos.catalog.prepare';
    public const OPEN_SHIFT = 'pos.shift.open';
    public const RECORD_SHIFT_OPENING_CASH = 'pos.shift.opening-cash.record';
    public const RECORD_SHIFT_CLOSING_CASH = 'pos.shift.closing-cash.record';
    public const VOID_SALE = 'pos.sale.void';
    public const REFUND_SALE = 'pos.sale.refund';
    public const INVENTORY_BASELINE = 'pos.inventory.baseline';

    public static function completeSale(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::COMPLETE_SALE);
    }

    public static function prepareCatalog(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::PREPARE_CATALOG);
    }

    public static function openShift(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::OPEN_SHIFT);
    }

    public static function recordShiftOpeningCash(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::RECORD_SHIFT_OPENING_CASH);
    }

    public static function recordShiftClosingCash(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::RECORD_SHIFT_CLOSING_CASH);
    }

    public static function voidSale(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::VOID_SALE);
    }

    public static function refundSale(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::REFUND_SALE);
    }

    public static function inventoryBaseline(): PermissionIdentifier
    {
        return PermissionIdentifier::fromString(self::INVENTORY_BASELINE);
    }
}
