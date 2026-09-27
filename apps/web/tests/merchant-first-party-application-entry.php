<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert=static function(bool $ok,string $message):void{if(!$ok)throw new RuntimeException('Sprint178 merchant entry regression failed: '.$message);};
$blade=file_get_contents(__DIR__.'/../resources/views/app.blade.php');
$page=file_get_contents(__DIR__.'/../resources/js/pages/Foundation.vue');
$routes=file_get_contents(__DIR__.'/../routes/web.php');
$provider=file_get_contents(__DIR__.'/../app/Providers/AppServiceProvider.php');
$controller=file_get_contents(__DIR__.'/../app/Delivery/Http/Identity/FirstPartySessionController.php');
$authorityService=file_get_contents(__DIR__.'/../app/Application/Identity/FirstPartySessionAuthorityService.php');
$assert(
    is_string($blade)
    && is_string($page)
    && is_string($routes)
    && is_string($provider)
    && is_string($controller)
    && is_string($authorityService),
    'entry source missing',
);

foreach ([
    "meta name=\"csrf-token\"",
    "meta name=\"oneqay-merchant-entry\"",
    "['local', 'test', 'ci']",
    "request()->attributes->get('oneqay.runtime_compatibility_bridge') === 'merchant-core-ci'",
    "$runtimeClass === 'durable-staging'",
    "env('ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED', false)",
    "Route::has('auth.first-party.login')",
    "Route::has('pos.operations.hub')",
    'database.oneqay_persistence_enabled',
    'oneqay.session_control.enabled',
] as $needle) {
    $assert(str_contains($blade,$needle),'guarded bootstrap metadata missing '.$needle);
}
foreach (['/auth/login','/auth/mfa/totp/challenge','/auth/mfa/totp/enrollment/start','/auth/mfa/totp/enrollment/confirm',"location.assign('/pos')"] as $needle) {
    $assert(str_contains($page,$needle),'entry contract missing '.$needle);
}
$assert(str_contains($page,"v-if=\"enabled\""),'merchant entry must be UI guarded');
$assert(str_contains($page,'No public registration'),'public registration boundary missing');
$assert(!str_contains($routes,"Route::post('/register"),'public registration must not be introduced');
$assert(str_contains($routes,"Route::post('/auth/login'"),'existing first-party login route missing');
$assert(str_contains($routes,"Route::get('/', static fn () => Inertia::render('Foundation'"),'canonical root delivery must remain unchanged');
$assert(!str_contains($routes,"Inertia::render('MerchantEntry'"),'shared route must not be coupled to Sprint178');

// Sprint263: session-control may keep the TOTP repository operational for freshness
// inspection, but it must not silently turn an unarmed TOTP feature into a login
// enforcement requirement. The full-session authority must use the same explicit
// MFA feature arm as FirstPartySessionController. This preserves factor_epoch=NULL
// for password-only full sessions while keeping confirmed-factor enforcement when
// ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED is explicitly armed.
$sessionBindingStart=strpos($provider,'$this->app->scoped(FirstPartySessionAuthorityService::class');
$sessionBindingEnd=$sessionBindingStart===false?false:strpos($provider,'$this->app->scoped(DurablePosSaleRepository::class',$sessionBindingStart);
$assert(is_int($sessionBindingStart)&&is_int($sessionBindingEnd)&&$sessionBindingEnd>$sessionBindingStart,'session authority binding missing');
$sessionBinding=substr($provider,$sessionBindingStart,$sessionBindingEnd-$sessionBindingStart);
$assert(str_contains($sessionBinding,"(bool) config('oneqay.privileged_totp_mfa.enabled', false),"),'session authority MFA enforcement must follow explicit feature arm');
$assert(!str_contains($sessionBinding,'$this->mfaOperationalEnabled(),'),'session authority MFA enforcement must not be inferred from session-control');

$mfaRepositoryStart=strpos($provider,'$this->app->scoped(PrivilegedTotpMfaRepository::class');
$mfaRepositoryEnd=$mfaRepositoryStart===false?false:strpos($provider,'$this->app->scoped(PrivilegedTotpFactorEpochRepository::class',$mfaRepositoryStart);
$assert(is_int($mfaRepositoryStart)&&is_int($mfaRepositoryEnd)&&$mfaRepositoryEnd>$mfaRepositoryStart,'TOTP repository binding missing');
$mfaRepositoryBinding=substr($provider,$mfaRepositoryStart,$mfaRepositoryEnd-$mfaRepositoryStart);
$assert(str_contains($mfaRepositoryBinding,'$this->mfaOperationalEnabled(),'),'session-control TOTP freshness inspection capability must remain available');
$assert(str_contains($controller,"config('oneqay.privileged_totp_mfa.enabled', false)"),'login controller must retain explicit MFA feature gate');
$assert(str_contains($authorityService,'if (! $this->mfaEnabled)'),'session authority must retain disabled-MFA factor-null path');
$assert(str_contains($authorityService,'if ($factorEpoch !== null)'),'disabled-MFA authority must reject unexpected factor evidence');
$assert(str_contains($authorityService,'requiredState($tenantId, $identityId)'),'enabled-MFA authority must continue revalidating protected-control factor state');

echo "Sprint178 merchant first-party application entry regression passed.\n";