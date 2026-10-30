<?php
// Author by Lab | zefry. HTTP controller contract on synthetic SQLite/MySQL, never public/live.
declare(strict_types=1);
$httpCompleted=false;
register_shutdown_function(static function() use (&$httpCompleted):void {
    if (!$httpCompleted) { fwrite(STDERR,"MENU_GUEST_HTTP_NOT_COMPLETED\n"); exit(1); }
});
require __DIR__.'/menu-guest-checkout.php';

use App\Delivery\Http\Business\PublicMenuGuestOrderController;
use App\Delivery\Http\Business\MenuGuestCashierController;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

$contexts->setVerified($businessOwner);
$publicHttp=$app->make(PublicMenuGuestOrderController::class);
$cashierHttp=$app->make(MenuGuestCashierController::class);
$requestData=array_replace($base,['operation_id'=>bin2hex(random_bytes(16))]);
$submission=Request::create('/menu/orders','POST',$requestData);
$submission->attributes->set('oneqay.host_business',$host);
$submission->headers->set('Accept','application/json');

config(['platform.business_domains_enabled'=>false,'oneqay.menu_guest_ordering.enabled'=>false]);
try { $publicHttp($submission);$assert(false,'disabled guest endpoint accepted order'); }
catch(HttpException $e){$assert($e->getStatusCode()===404,'default-off public route/controller rejects');}
config(['oneqay.menu_guest_ordering.enabled'=>true]);
try { $publicHttp($submission);$assert(false,'non-business host allowed'); }
catch(HttpException $e){$assert($e->getStatusCode()===404,'business host feature required');}
config(['platform.business_domains_enabled'=>true]);
$noHost=Request::create('/menu/orders','POST',$requestData);
try { $publicHttp($noHost);$assert(false,'absent verified business host allowed'); }
catch(HttpException $e){$assert($e->getStatusCode()===404,'host middleware provenance required');}
$forged=Request::create('/menu/orders','POST',array_replace($requestData,['total_atomic'=>1]));
$forged->attributes->set('oneqay.host_business',$host);
$bad=$publicHttp($forged);
$assert($bad->getStatusCode()===422 && $bad->getData(true)['error']['code']==='INVALID_FIELDS',
    'guest request cannot provide client price');
$published=$publicHttp($submission);
$created=$published->getData(true)['order']??null;
$assert($published->getStatusCode()===202&&is_array($created)&&$created['status']==='pending_unpaid',
    'verified published guest order returns unpaid-only receipt');
$assert(!str_contains($published->getContent(),$requestData['phone']) &&
    !str_contains($published->getContent(),$requestData['email']) &&
    $created['table_label']===null,'anonymous response excludes private contact and optional table remains unset');
$assert($publicHttp($submission)->getData(true)['order']['order_id']===$created['order_id'],
    'HTTP idempotency returns same order');
$lookup=Request::create('/pos/business/menu-orders/lookup','GET',['kind'=>'qr','token'=>$created['order_token']]);
$found=$cashierHttp->lookup($lookup);
$assert($found->getStatusCode()===200 && $found->getData(true)['order']['order_id']===$created['order_id'],
    'cashier can resolve exact QR token in same outlet');
$phoneLookup=Request::create('/pos/business/menu-orders/lookup','GET',['kind'=>'phone','phone'=>$requestData['phone']]);
$phoneResponse=$cashierHttp->lookup($phoneLookup);
$assert($phoneResponse->getStatusCode()===200 && is_array($phoneResponse->getData(true)['orders']),
    'authorized exact telephone lookup');
$quoteRequest=Request::create('/pos/business/menu-orders/quote','POST',['order_id'=>$created['order_id']]);
$qResponse=$cashierHttp->quote($quoteRequest);
$currentQuote=$qResponse->getData(true)['quote']??null;
$assert($qResponse->getStatusCode()===200 && is_array($currentQuote)
    && $currentQuote['total_atomic']==='32000','cashier HTTP quote follows latest stock and price');
$checkoutRequest=Request::create('/pos/business/menu-orders/settle','POST',[
    'order_id'=>$created['order_id'],'operation_id'=>'qr-http-checkout-01',
    'tender_category'=>'CASH','tendered_atomic'=>'32000','expected_total_atomic'=>'32000',
]);
$cashResponse=$cashierHttp->settle($checkoutRequest);
$assert($cashResponse->getStatusCode()===200 && $cashResponse->getData(true)['settlement']['status']==='paid',
    'cashier HTTP payment is bound to POS canonical sale');
$again=$cashierHttp->settle($checkoutRequest);
$assert($again->getStatusCode()===200&&$again->getData(true)['settlement']['replayed']===true,
    'cashier HTTP retry does not charge again');
$reject=$cashierHttp->cancel(Request::create('/pos/business/menu-orders/cancel','POST',[
    'order_id'=>$created['order_id'],'operation_id'=>'qr-http-cancel-too-late',
]));
$assert($reject->getStatusCode()===409&&$reject->getData(true)['error']['code']==='ORDER_NOT_PENDING',
    'cashier cannot cancel already paid sale as if unpaid');
$contexts->clear();
$denied=$cashierHttp->lookup($lookup);
$assert($denied->getStatusCode()===403&&$denied->getData(true)['error']['code']==='CONTEXT_REQUIRED',
    'no verified cashier context cannot view guest identity');
$contexts->setVerified($businessOwner);
config(['oneqay.menu_guest_ordering.enabled'=>false,'platform.business_domains_enabled'=>false]);
$assert(!Illuminate\Support\Facades\Route::has('menu.public.order')
    && !Illuminate\Support\Facades\Route::has('business.menu-orders'),
    'both HTTP route families stay unregistered in default-off CI bootstrap');
$httpCompleted=true;
echo 'MENU_GUEST_HTTP_'.$engine.'_PASS assertions='.$n.PHP_EOL;
