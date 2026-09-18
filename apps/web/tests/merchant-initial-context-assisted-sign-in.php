<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint180 assisted sign-in regression failed: '.$message);
    }
};

$blade = file_get_contents(__DIR__.'/../resources/views/app.blade.php');
$page = file_get_contents(__DIR__.'/../resources/js/pages/Foundation.vue');
$controller = file_get_contents(__DIR__.'/../app/Delivery/Http/Identity/FirstPartySessionController.php');
$routes = file_get_contents(__DIR__.'/../routes/web.php');

$assert(is_string($blade) && is_string($page) && is_string($controller) && is_string($routes), 'required source is missing.');

foreach ([
    "['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id']",
    "config('merchant_context_bootstrap.grant', [])",
    "['local', 'test', 'ci']",
    "config('database.oneqay_persistence_enabled', false)",
    "config('oneqay.session_control.enabled', false)",
    'count($merchantLoginContext) === 5',
    'id="oneqay-merchant-login-context"',
    'JSON_HEX_TAG',
    'JSON_HEX_QUOT',
] as $needle) {
    $assert(str_contains($blade, $needle), 'server-assisted context guard missing '.$needle);
}

$assert(! str_contains($blade, 'provisioning_id'), 'provisioning identifier must never be serialized into merchant entry HTML.');

foreach ([
    "document.getElementById('oneqay-merchant-login-context')",
    "Object.keys(raw).sort().join('|')",
    'merchantContext !== null',
    "...merchantContext",
    "password: password.value",
    "/auth/login",
    "/auth/mfa/totp/challenge",
    "/auth/mfa/totp/enrollment/start",
    "/auth/mfa/totp/enrollment/confirm",
    "location.assign('/pos')",
    'No internal IDs are required.',
] as $needle) {
    $assert(str_contains($page, $needle), 'assisted sign-in contract missing '.$needle);
}

foreach ([
    'v-model="tenantId"',
    'v-model="identityId"',
    'v-model="organizationId"',
    'v-model="outletId"',
    'v-model="deviceId"',
] as $forbidden) {
    $assert(! str_contains($page, $forbidden), 'opaque internal identifier remains manually editable: '.$forbidden);
}

$assert(str_contains($controller, "'tenant_id',"), 'existing first-party login contract must remain authoritative.');
$assert(str_contains($controller, "'identity_id',"), 'existing identity login contract must remain authoritative.');
$assert(str_contains($controller, "'organization_id',"), 'existing organization login contract must remain authoritative.');
$assert(str_contains($controller, "'outlet_id',"), 'existing outlet login contract must remain authoritative.');
$assert(str_contains($controller, "'device_id',"), 'existing device login contract must remain authoritative.');
$assert(str_contains($routes, "Route::post('/auth/login'"), 'existing first-party login route missing.');
$assert(! str_contains($routes, "Route::post('/register"), 'public registration must remain absent.');

echo "Sprint180 merchant initial context-assisted sign-in regression passed.\n";
