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
    public const CREDENTIAL_EPOCH = 'oneqay.auth.credential_epoch';
    public const MFA_VERIFIED_AT = 'oneqay.auth.mfa_verified_at';
    public const MFA_FACTOR_EPOCH = 'oneqay.auth.mfa_factor_epoch';
    public const STEP_UP_VERIFIED_AT = 'oneqay.auth.step_up_verified_at';
    public const STEP_UP_SCOPE = 'oneqay.auth.step_up_scope';
    public const STEP_UP_CONTEXT = 'oneqay.auth.step_up_context';

    public const PENDING_IDENTITY = 'oneqay.auth.pending.identity_id';
    public const PENDING_TENANT = 'oneqay.auth.pending.tenant_id';
    public const PENDING_ORGANIZATION = 'oneqay.auth.pending.organization_id';
    public const PENDING_OUTLET = 'oneqay.auth.pending.outlet_id';
    public const PENDING_DEVICE = 'oneqay.auth.pending.device_id';
    public const PENDING_MFA_STATE = 'oneqay.auth.pending.mfa_state';

    public const RECOVERY_TENANT = 'oneqay.auth.recovery.tenant_id';
    public const RECOVERY_IDENTITY = 'oneqay.auth.recovery.identity_id';
    public const RECOVERY_CODE_ID = 'oneqay.auth.recovery.code_id';
    public const RECOVERY_STATE = 'oneqay.auth.recovery.state';
    public const RECOVERY_PROVED_AT = 'oneqay.auth.recovery.proved_at';
    public const RECOVERY_EXPIRES_AT = 'oneqay.auth.recovery.expires_at';

    public const TOTP_RECOVERY_TENANT = 'oneqay.auth.mfa_recovery.tenant_id';
    public const TOTP_RECOVERY_IDENTITY = 'oneqay.auth.mfa_recovery.identity_id';
    public const TOTP_RECOVERY_CODE_ID = 'oneqay.auth.mfa_recovery.code_id';
    public const TOTP_RECOVERY_FACTOR_EPOCH = 'oneqay.auth.mfa_recovery.factor_epoch';
    public const TOTP_RECOVERY_STATE = 'oneqay.auth.mfa_recovery.state';
    public const TOTP_RECOVERY_PROVED_AT = 'oneqay.auth.mfa_recovery.proved_at';
    public const TOTP_RECOVERY_EXPIRES_AT = 'oneqay.auth.mfa_recovery.expires_at';
    public const TOTP_RECOVERY_REPLACEMENT = 'oneqay.auth.mfa_recovery.replacement';

    public const MFA_ENROLLMENT_REQUIRED = 'enrollment_required';
    public const MFA_CHALLENGE_REQUIRED = 'challenge_required';

    /** @return list<string> Sprint 27 canonical full-context keys. */
    public static function all(): array
    {
        return [self::IDENTITY, self::TENANT, self::ORGANIZATION, self::OUTLET, self::DEVICE];
    }

    /** @return list<string> */
    public static function pending(): array
    {
        return [self::PENDING_IDENTITY, self::PENDING_TENANT, self::PENDING_ORGANIZATION, self::PENDING_OUTLET, self::PENDING_DEVICE, self::PENDING_MFA_STATE];
    }

    /** @return list<string> */
    public static function recovery(): array
    {
        return [self::RECOVERY_TENANT, self::RECOVERY_IDENTITY, self::RECOVERY_CODE_ID, self::RECOVERY_STATE, self::RECOVERY_PROVED_AT, self::RECOVERY_EXPIRES_AT];
    }

    /** @return list<string> */
    public static function totpRecovery(): array
    {
        return [
            self::TOTP_RECOVERY_TENANT,
            self::TOTP_RECOVERY_IDENTITY,
            self::TOTP_RECOVERY_CODE_ID,
            self::TOTP_RECOVERY_FACTOR_EPOCH,
            self::TOTP_RECOVERY_STATE,
            self::TOTP_RECOVERY_PROVED_AT,
            self::TOTP_RECOVERY_EXPIRES_AT,
            self::TOTP_RECOVERY_REPLACEMENT,
        ];
    }

    private function __construct() {}
}
