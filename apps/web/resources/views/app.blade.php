<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $merchantRuntimeAllowed = in_array(
            strtolower(trim((string) config('oneqay.runtime_class', ''))),
            ['local', 'test', 'ci', 'staging'],
            true,
        );
        $merchantGrant = config('merchant_context_bootstrap.grant', []);
        $merchantLoginContext = [];

        if (is_array($merchantGrant)) {
            foreach (['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id'] as $key) {
                $value = $merchantGrant[$key] ?? null;
                if (! is_string($value) || $value === '' || trim($value) !== $value) {
                    $merchantLoginContext = [];
                    break;
                }
                $merchantLoginContext[$key] = $value;
            }
        }

        $merchantEntryEnabled = $merchantRuntimeAllowed
            && (bool) config('database.oneqay_persistence_enabled', false)
            && (bool) config('oneqay.session_control.enabled', false)
            && count($merchantLoginContext) === 5;

        $passwordRecoveryEnabled = $merchantRuntimeAllowed
            && (bool) config('oneqay.authentication_recovery.enabled', false)
            && (int) config('oneqay.authentication_recovery.restricted_session_ttl_seconds', 0) === 600;
        $totpRecoveryEnabled = $passwordRecoveryEnabled
            && (bool) config('oneqay.privileged_totp_mfa.enabled', false);
    @endphp
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="oneqay-merchant-entry" content="{{ $merchantEntryEnabled ? 'enabled' : 'disabled' }}">
    <meta name="oneqay-password-recovery" content="{{ $passwordRecoveryEnabled ? 'enabled' : 'disabled' }}">
    <meta name="oneqay-totp-recovery" content="{{ $totpRecoveryEnabled ? 'enabled' : 'disabled' }}">
    @if ($merchantEntryEnabled)
        <script id="oneqay-merchant-login-context" type="application/json">{!! json_encode(
            $merchantLoginContext,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        ) !!}</script>
    @endif
    <title>oneQay</title>
    @vite('resources/js/app.ts')
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
