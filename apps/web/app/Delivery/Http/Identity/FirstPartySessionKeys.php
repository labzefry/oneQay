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
    public const MFA_VERIFIED_AT = 'oneqay.auth.mfa_verified_at';

    public const PENDING_IDENTITY = 'oneqay.auth.pending.identity_id';
    public const PENDING_TENANT = 'oneqay.auth.pending.tenant_id';
    public const PENDING_ORGANIZATION = 'oneqay.auth.pending.organization_id';
    public const PENDING_OUTLET = 'oneqay.auth.pending.outlet_id';
    public const PENDING_DEVICE = 'oneqay.auth.pending.device_id';
    public const PENDING_MFA_STATE = 'oneqay.auth.pending.mfa_state';

    public const MFA_ENROLLMENT_REQUIRED = 'enrollment_required';
    public const MFA_CHALLENGE_REQUIRED = 'challenge_required';

    /** @return list<string> Sprint 27 canonical full-context keys. */
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

    /** @return list<string> */
    public static function pending(): array
    {
        return [
            self::PENDING_IDENTITY,
            self::PENDING_TENANT,
            self::PENDING_ORGANIZATION,
            self::PENDING_OUTLET,
            self::PENDING_DEVICE,
            self::PENDING_MFA_STATE,
        ];
    }

    private function __construct()
    {
    }
}
