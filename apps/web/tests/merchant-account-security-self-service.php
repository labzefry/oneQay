<?php

declare(strict_types=1);

// Author by Lab | zefry

$root = dirname(__DIR__, 3);

$read = static function (string $relative) use ($root): string {
    $path = $root.DIRECTORY_SEPARATOR.$relative;
    $content = @file_get_contents($path);
    if (! is_string($content)) {
        throw new RuntimeException('Sprint199 source contract is unavailable: '.$relative);
    }
    return $content;
};

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint199 merchant account security self-service regression failed: '.$message);
    }
};

$controller = $read('apps/web/app/Delivery/Http/Pos/PosOperationsHubController.php');
$hub = $read('apps/web/resources/js/pages/Pos/OperationsHub.vue');
$foundation = $read('apps/web/resources/js/pages/Foundation.vue');
$blade = $read('apps/web/resources/views/app.blade.php');
$routes = $read('apps/web/routes/web.php');

foreach ([
    "Route::has('auth.password.change')",
    "Route::has('auth.recovery.codes.rotate')",
    "Route::has('auth.privileged-totp-recovery.codes.rotate')",
    "Route::has('auth.first-party.logout')",
] as $needle) {
    $assert(str_contains($controller, $needle), 'hub security capability must derive from existing route registration: '.$needle);
}

foreach ([
    '/auth/password/change',
    '/auth/recovery/codes/rotate',
    '/auth/mfa/recovery/codes/rotate',
    '/auth/logout',
    'Account & security',
    'Session protected',
    'recovery_codes',
    'X-XSRF-TOKEN',
] as $needle) {
    $assert(str_contains($hub, $needle), 'authenticated security UI contract missing '.$needle);
}

foreach ([
    '/auth/recovery/proof',
    '/auth/recovery/password-reset',
    '/auth/mfa/recovery/proof',
    '/auth/mfa/recovery/totp/replace/start',
    '/auth/mfa/recovery/totp/replace/confirm',
    'Recover password',
    'Lost authenticator?',
    'X-XSRF-TOKEN',
] as $needle) {
    $assert(str_contains($foundation, $needle), 'anonymous recovery UI contract missing '.$needle);
}

foreach ([
    'oneqay-password-recovery',
    'oneqay-totp-recovery',
    "config('oneqay.authentication_recovery.enabled', false)",
    "config('oneqay.privileged_totp_mfa.enabled', false)",
] as $needle) {
    $assert(str_contains($blade, $needle), 'server-derived recovery capability metadata missing '.$needle);
}

foreach ([
    "->name('auth.password.change')",
    "->name('auth.recovery.codes.rotate')",
    "->name('auth.recovery.proof')",
    "->name('auth.recovery.password-reset')",
    "->name('auth.privileged-totp-recovery.codes.rotate')",
    "->name('auth.privileged-totp-recovery.proof')",
    "->name('auth.privileged-totp-recovery.replace.start')",
    "->name('auth.privileged-totp-recovery.replace.confirm')",
    "->name('auth.first-party.logout')",
] as $needle) {
    $assert(str_contains($routes, $needle), 'canonical identity endpoint must remain authoritative: '.$needle);
}

$assert(! str_contains($hub, 'localStorage'), 'recovery material must not be persisted in localStorage');
$assert(! str_contains($hub, 'sessionStorage'), 'recovery material must not be persisted in sessionStorage');
$assert(! str_contains($foundation, 'localStorage'), 'anonymous recovery material must not be persisted in localStorage');
$assert(! str_contains($foundation, 'sessionStorage'), 'anonymous recovery material must not be persisted in sessionStorage');
$assert(! str_contains($controller, 'PermissionIdentifier::fromString'), 'self-service security projection must not introduce a new authorization permission');
$assert(! str_contains($foundation, 'provisioning_id'), 'merchant recovery must never expose bootstrap provisioning authority');

fwrite(STDOUT, "Sprint199 merchant account security self-service regression passed.\n");
