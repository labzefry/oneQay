<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert=static function(bool $ok,string $message):void{if(!$ok)throw new RuntimeException('Sprint178 merchant entry regression failed: '.$message);};
$blade=file_get_contents(__DIR__.'/../resources/views/app.blade.php');
$page=file_get_contents(__DIR__.'/../resources/js/pages/Foundation.vue');
$routes=file_get_contents(__DIR__.'/../routes/web.php');
$assert(is_string($blade)&&is_string($page)&&is_string($routes),'entry source missing');

foreach (["meta name=\"csrf-token\"","meta name=\"oneqay-merchant-entry\"","['local', 'test', 'ci']","database.oneqay_persistence_enabled","oneqay.session_control.enabled"] as $needle) {
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

echo "Sprint178 merchant first-party application entry regression passed.\n";
