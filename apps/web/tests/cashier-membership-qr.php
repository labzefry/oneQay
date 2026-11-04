<?php
// Author by Lab | zefry. Disposable fixtures; no live checkout or mail.
declare(strict_types=1);

$memberTestComplete=false;
register_shutdown_function(static function()use(&$memberTestComplete):void {
    if (!$memberTestComplete) {fwrite(STDERR,"CASHIER_MEMBERSHIP_NOT_COMPLETED\n");exit(1);}
});
require __DIR__.'/menu-guest-http.php';
(require __DIR__.'/../database/module-migrations/business/2026_11_04_000004_create_cashier_memberships.php')->up();

use App\Application\Business\BusinessViolation;
use App\Delivery\Http\Business\CashierMembershipController;
use App\Delivery\Http\Pos\DesktopMenuOrdersController;
use App\Infrastructure\Business\LaravelCashierMemberships;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

$config=['cashier_membership.enabled'=>true,'oneqay.menu_guest_ordering.enabled'=>true];
config($config);
$contexts->setVerified($businessOwner);
$memberService=$app->make(LaravelCashierMemberships::class);
$memberHttp=$app->make(CashierMembershipController::class);
$memberInput=['operation_id'=>'member-enrol-001','name'=>'Test Member','phone'=>'0812 1234 5678','email'=>'Member@Example.test'];
$memberReq=Request::create('/pos/business/menu-orders/member-register','POST',$memberInput);
$memberResult=$memberHttp($memberReq,'member-register')->getData(true);
$member=$memberResult['member'];
$assert($member['name']==='Test Member' && $member['status']==='cashier_enrolled','cashier enrollment creates selectable member');
$assert($memberHttp($memberReq,'member-register')->getData(true)===$memberResult,'exact enrollment retries return the same member');
$assert($memberService->lookup(['kind'=>'phone','value'=>'+6281212345678'])===$member,'normalized phone finds member');
$assert($memberService->lookup(['kind'=>'email','value'=>'MEMBER@example.test'])===$member,'email lookup is case insensitive');
$assert(!isset($member['email'],$member['phone']),'minimal projection never reveals contacts');
$expect(fn()=>$memberService->register(array_replace($memberInput,['operation_id'=>'member-enrol-002','email'=>'other@example.test']),'member-duplicate'),'MEMBER_CONTACT_EXISTS');
$expect(fn()=>$memberService->register(array_replace($memberInput,['email'=>'other@example.test']),'member-conflict'),'OPERATION_CONFLICT');
$assert($db->table('oneqay_cashier_memberships')->count()===1,'duplicate or conflicting retries create no member');
$storedCustomer=$db->table('oneqay_business_partners')->where('email','member@example.test')->first();
$assert($storedCustomer->kind==='CUSTOMER'&&!(bool)$storedCustomer->marketing_consent,'canonical customer and no automatic marketing consent');
$contexts->setVerified($otherOwner);
$assert($memberService->lookup(['kind'=>'email','value'=>'member@example.test'])===null,'other business cannot search member');
$contexts->setVerified($businessOwner);
$memberOrder=$guest->submit($host,array_replace($base,['operation_id'=>bin2hex(random_bytes(16))]));
$memberQuote=$checkout->quote($memberOrder['order_id'],[['product_id'=>'desktop-coffee','quantity'=>1]]);
$payment=['order_id'=>$memberOrder['order_id'],'operation_id'=>'member-order-pay-001','member_id'=>$member['id'],
    'tender_category'=>'CASH','tendered_atomic'=>$memberQuote['total_atomic'],'expected_total_atomic'=>$memberQuote['total_atomic'],
    'selected_lines'=>[['product_id'=>'desktop-coffee','quantity'=>1]]];
$expect(fn()=>$checkout->settle(array_replace($payment,['member_id'=>str_repeat('e',32)]),'member-payment-001'),'MEMBER_NOT_FOUND');
$memberPaid=$checkout->settle($payment,'member-payment-001');
$assert($db->table('oneqay_cashier_member_sales')->where('sale_id',$memberPaid['sale_id'])->value('member_id')===$member['id'],'edited paid QR checkout attributes its canonical sale to the chosen member');
$assert($checkout->settle($payment,'member-payment-001')['replayed'] && $db->table('oneqay_cashier_member_sales')->count()===1,'payment replay cannot duplicate member attribution');

$native=$app->make(DesktopMenuOrdersController::class);
$makeNative=static function(string $action,array $body,string $token=''):Request {
    $request=Request::create('/api/pos/desktop/v3/menu-orders/'.$action,'POST',[],[],[],[
        'CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer '.$token,
    ],json_encode($body,JSON_THROW_ON_ERROR));
    $request->attributes->set('oneqay.correlation_id','native-member-qr-test');
    return $request;
};
try { $native($makeNative('member-lookup',['kind'=>'email','value'=>'member@example.test']),'member-lookup');$assert(false,'no bearer accepted'); }
catch (HttpException $e) { $assert($e->getStatusCode()===401,'native request requires bearer independently of web context'); }
$assert($contexts->current()===null,'native adapter always clears request scope');
$contexts->setVerified($businessOwner);
$profile=$db->table('oneqay_business_profiles')->where('tenant_id',$t);
$profile->update(['business_type'=>'food']);
$nativeCashier=array_replace($cashier,['login'=>'membership-qr-cashier@example.test','operation_id'=>'membership-qr-create-cashier']);
$settings->cashier($nativeCashier);
$desktopAuth=$login->login(['login'=>$nativeCashier['login'],'password'=>$nativeCashier['password'],'totp'=>''],'native-membership-login');
$nativeResult=$native($makeNative('member-lookup',['kind'=>'email','value'=>'member@example.test'],$desktopAuth['token']),'member-lookup');
$assert($nativeResult->getStatusCode()===200&&$nativeResult->getData(true)['member']===$member,'actual cashier bearer resolves same member as web');
$contexts->setVerified($businessOwner);
$nativeOrder=$guest->submit($host,array_replace($base,['operation_id'=>bin2hex(random_bytes(16))]));
$nativeLookup=$native($makeNative('lookup',['kind'=>'qr','token'=>$nativeOrder['order_token']],$desktopAuth['token']),'lookup');
$assert($nativeLookup->getStatusCode()===200&&$nativeLookup->getData(true)['order']['order_id']===$nativeOrder['order_id'],'native POST QR lookup uses canonical inbox');
$nativeQuote=$native($makeNative('quote',['order_id'=>$nativeOrder['order_id'],'selected_lines'=>[['product_id'=>'desktop-coffee','quantity'=>1]]],$desktopAuth['token']),'quote');
$assert($nativeQuote->getStatusCode()===200&&$nativeQuote->getData(true)['quote']['total_atomic']==='16000','native quote accepts reductions and reprices using canonical stock');
$contexts->setVerified($businessOwner);
// Synthetic transfer of the register after the earlier owner's fixture sales.
$db->table('oneqay_pos_shifts')->where($checkoutScope)->where('device_id',$device)->where('active_slot',1)->update(['active_slot'=>null]);
$desktop->authenticate($desktopAuth['token']);
$openingOperation=$uuid();
$nativeOpening=$journal->sync([[
    'operation_id'=>$openingOperation,'kind'=>'shift_open','lease_id'=>$desktopAuth['snapshot']['lease_id'],
    'shift_id'=>$openingOperation,'created_at'=>time(),'lines'=>[],'tendered'=>100000,'table'=>'','order'=>'','note'=>'',
]],'native-qr-opening')['results'][0];
$assert($nativeOpening['state']==='accepted','native cashier opens own canonical shift');
$desktop->clear();
$nativePayment=['order_id'=>$nativeOrder['order_id'],'operation_id'=>'native-qr-pay-test-001','member_id'=>$member['id'],
    'tender_category'=>'CASH','tendered_atomic'=>'20000','expected_total_atomic'=>'16000',
    'selected_lines'=>[['product_id'=>'desktop-coffee','quantity'=>1]]];
$nativeSettled=$native($makeNative('settle',$nativePayment,$desktopAuth['token']),'settle')->getData(true);
$assert($nativeSettled['settlement']['status']==='paid','native bearer settles edited QR against its shift');
$nativeAgain=$native($makeNative('settle',$nativePayment,$desktopAuth['token']),'settle')->getData(true);
$assert($nativeAgain['settlement']['replayed'] && $db->table('oneqay_cashier_member_sales')->count()===2,'native payment replay creates no second sale or attachment');
$memberTestComplete=true;
echo 'CASHIER_MEMBERSHIP_'.$engine.'_PASS assertions='.$n.PHP_EOL;
