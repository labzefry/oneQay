<?php

declare(strict_types=1);

namespace App\Delivery\Http\Identity;

// Author by Lab | zefry
final class FirstPartySessionKeys
{
    public const IDENTITY = 'oneqay.auth.identity_id';
    public const TENANT = 'oneqay.auth.tenant_id';
    public const ORGANIZATION = 'oneqay.auth.organization_id';
    public const OUTLET = 'oneqay.auth.outlet_id';
    public const DEVICE = 'oneqay.auth.device_id';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::IDENTITY,
            self::TENANT,
            self::ORGANIZATION,
            self::OUTLET,
            self::DEVICE,
        ];
    }

    private function __construct()
    {
    }
}
