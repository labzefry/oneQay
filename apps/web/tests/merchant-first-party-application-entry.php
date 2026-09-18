<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME'=>'oneQay','APP_ENV'=>'testing','APP_KEY'=>'base64:'.base64_encode(str_repeat('e',32)),
    'APP_DEBUG'=>'false','APP_URL'=>'http://localhost','ONEQAY_RUNTIME_CLASS'=>'ci',
    'ONEQAY_PERSISTENCE_ENABLED'=>'true','ONEQAY_SESSION_CONTROL_ENABLED'=>'true',
    'ONEQAY_SESSION_CONTROL_IDLE_TTL_SECONDS'=>'7200','ONEQAY_SESSION_CONTROL_ABSOLUTE_TTL_SECONDS'=>'43200',
    'SESSION_DRIVER'=>'array','CACHE_STORE'=>'array',
] as $key=>$value) { putenv($key.'='.$value); $_ENV[$key]=$value; $_SERVER[$key]=$value; }

$app=require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */ $kernel=$app->make(Kernel::class); $kernel->bootstrap();
$assert=static function(bool $ok,string $message):void{if(!$ok)throw new RuntimeException('Sprint178 merchant entry regression failed: '.$message);};

$request=Request::create('/','GET',server:['HTTP_ACCEPT'=>'text/html','HTTP_X_INERTIA'=>'true','HTTP_X_INERTIA_VERSION'=>'']);
$response=$kernel->handle($request); $kernel->terminate($request,$response);
$assert($response->getStatusCode()===200,'guarded root must render');
$payload=json_decode((string)$response->getContent(),true);
$assert(is_array($payload)&&($payload['component']??null)==='MerchantEntry','guarded root must deliver MerchantEntry');
$props=$payload['props']??[];
$assert(is_string($props['csrf_token']??null)&&$props['csrf_token']!=='','CSRF token must be delivered');
$assert(($props['pos_url']??null)==='/pos','POS destination must remain existing operations hub');
$assert(!array_key_exists('password',$props),'credential material must not be server-delivered');

$app['config']->set('oneqay.runtime_class','production');
$request=Request::create('/','GET',server:['HTTP_ACCEPT'=>'text/html','HTTP_X_INERTIA'=>'true','HTTP_X_INERTIA_VERSION'=>'']);
$response=$kernel->handle($request); $kernel->terminate($request,$response);
$assert($response->getStatusCode()===200,'production-like root foundation response');
$payload=json_decode((string)$response->getContent(),true);
$assert(is_array($payload)&&($payload['component']??null)==='Foundation','production-like runtime must not deliver merchant entry');

$routes=$app['router']->getRoutes();
$assert($routes->getByName('auth.first-party.login')!==null,'existing first-party login route missing');
$assert($routes->getByName('auth.privileged-totp.challenge')!==null || !(bool)$app['config']->get('oneqay.privileged_totp_mfa.enabled',false),'MFA challenge contract inconsistent');

$page=file_get_contents(__DIR__.'/../resources/js/pages/MerchantEntry.vue');
$assert(is_string($page),'MerchantEntry source missing');
foreach (['/auth/login','/auth/mfa/totp/challenge','/auth/mfa/totp/enrollment/start','/auth/mfa/totp/enrollment/confirm','window.location.assign(props.pos_url)'] as $needle) {
    $assert(str_contains($page,$needle),'entry contract missing '.$needle);
}
$assert(!str_contains($page,'register'),'public registration must not be introduced');

echo "Sprint178 merchant first-party application entry regression passed.\n";
