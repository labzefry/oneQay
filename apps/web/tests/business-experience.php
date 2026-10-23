<?php
// Author by Lab | zefry. Disposable SQL; no DNS, hosting, or real email mutation.
declare(strict_types=1);
require __DIR__.'/simplified-pos-onboarding.php';
use App\Application\BusinessExperience\{BusinessDomain,ServiceWorkflow};
use App\Application\Platform\PlatformViolation;
use App\Infrastructure\BusinessExperience\{LaravelBusinessDomains,LaravelServicePos};
(require __DIR__.'/../database/module-migrations/business/2026_10_14_000001_create_merchandising_and_currency_preferences.php')->up();
foreach(['platform/2026_10_23_000001_create_business_experience.php','business/2026_10_23_000002_create_isolated_service_pos.php']as$file)(require __DIR__.'/../database/module-migrations/'.$file)->up();
$domains=$app->make(LaravelBusinessDomains::class);config(['platform.public_url'=>'https://oneqay.example.test']);
foreach(['www','api','foo--bar','-foo','foo-','a.b','UPPER','ab','foo/../bar',str_repeat('x',41)]as$slug){try{BusinessDomain::slug($slug);$assert(false,'unsafe slug accepted');}catch(PlatformViolation){$assert(true,'unsafe slug rejected');}}
$b=(array)$db->table('oneqay_platform_businesses')->where('tenant_id',$t)->firstOrFail();
$db->transaction(fn()=>$domains->claim($b,'kopikay','laundry'));$availability=$domains->availability('kopikay');$assert(!$availability['available']&&count($availability['suggestions'])===3,'availability gives three free suggestions');
try{$db->transaction(fn()=>$domains->claim($b,'kopikay','laundry'));$assert(false,'duplicate slug accepted');}catch(PlatformViolation $e){$assert($e->reason==='SUBDOMAIN_UNAVAILABLE','duplicate reservation rejected');}
$assert($domains->hostBusiness('kopikay.oneqay.example.test')['tenant_id']===$t,'host resolves canonical tenant');
try{$domains->hostBusiness('absent.oneqay.example.test');$assert(false,'unknown host accepted');}catch(PlatformViolation $e){$assert($e->status===404,'unknown host rejects');}
$contexts->setVerified($businessOwner);$pos=$app->make(LaravelServicePos::class);
foreach(['laundry','boarding','homestay']as$type){
 $db->table('oneqay_business_profiles')->where('business_id',$b['id'])->update(['business_type'=>$type]);
 $desktop=$app->make(App\Infrastructure\Pos\LaravelDesktopPosService::class);$desktop->authenticate($connectedOwner['token']);$nativeSnapshot=$desktop->snapshot(true);
 $assert($nativeSnapshot['catalog']===[]&&$nativeSnapshot['settings']['business_type']===$type&&$nativeSnapshot['settings']['workflow_snapshot']['type']===$type,'one Windows snapshot selects authenticated industry without retail catalog');
 try{$desktop->sync([],'synthetic-service-legacy-denied');$assert(false,'service can use generic retail sync');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===403,'retail synchronization rejects service business');}
 $desktop->clear();$contexts->setVerified($businessOwner);
 $run=fn($action,$fields=[])=>$pos->command(['action'=>$action,'operation_id'=>'service-test-'.bin2hex(random_bytes(12))]+$fields);
 $resource=$run('resource.save',['id'=>'','version'=>0,'name'=>'Unit '.$type,'unit'=>$type==='laundry'?'kg':($type==='boarding'?'month':'night'),'rate_atomic'=>10000,'currency'=>'IDR','scale'=>0,'enabled'=>true]);
 $start=gmdate('Y-m-d');$end=gmdate('Y-m-d',time()+86400*3);
 $input=['action'=>'order.create','operation_id'=>'stable-order-'.$type,'resource_id'=>$resource['id'],'customer_name'=>'Synthetic customer','contact'=>'+628000000','start_date'=>$start,'end_date'=>$end,'quantity_milli'=>$type==='laundry'?1250:1000];
 $order=$pos->command($input);$assert($pos->command($input)===$order,'order replay does not duplicate');$expected=$type==='laundry'?12500:($type==='homestay'?30000:10000);$assert($order['total_atomic']===$expected,'vertical pricing uses its own rule');
 $workspace=$pos->read();$assert(count($workspace['resources'])===1&&count($workspace['orders'])===1&&$workspace['type']===$type,'vertical data isolated');
 if($type!=='laundry'){try{$run('order.create',array_diff_key($input,array_flip(['action','operation_id'])));$assert(false,'overlap allowed');}catch(PlatformViolation $e){$assert($e->reason==='ROOM_ALREADY_RESERVED','room overlap denied');}}
 $db->table('oneqay_'.$type.'_orders')->where('id',$order['id'])->update(['outlet_id'=>'different-outlet']);$scoped=$pos->read();$assert($scoped['orders']===[]&&$scoped['invoices']===[],'cross-outlet orders and invoices are excluded');$db->table('oneqay_'.$type.'_orders')->where('id',$order['id'])->update(['outlet_id'=>$businessOwner->outletId()->value()]);
 $invoice=$workspace['invoices'][0];try{$run('payment.record',['invoice_id'=>$invoice['id'],'amount_atomic'=>$expected]);$assert(false,'payment before open');}catch(PlatformViolation $e){$assert($e->reason==='OWN_SHIFT_REQUIRED','own open register required');}
 $run('shift.open',['opening_atomic'=>5000]);
 $reset=$app->make(App\Infrastructure\Platform\ControlPlaneBusinessReset::class);$plan=$reset->preview($b,'transactions');$assert(in_array('RESET_CLOSE_OR_RECOVER_ACTIVE_SHIFTS',$plan['blocked'],true),'reset blocks active industry register');$assert(in_array('oneqay_'.$type.'_orders',array_column($plan['entries'],'table'),true),'reset preview includes industry-owned transaction records');
 $productPlan=$reset->preview($b,'products');$assert(in_array('RESET_TRANSACTIONS_DEPEND_ON_SERVICE_RESOURCES',$productPlan['blocked'],true),'resource-only reset cannot orphan industry orders');
 try{$run('payment.record',['invoice_id'=>$invoice['id'],'amount_atomic'=>$expected+1]);$assert(false,'overpayment accepted');}catch(PlatformViolation $e){$assert($e->reason==='PAYMENT_EXCEEDS_BALANCE','overpayment blocked');}
 $payment=['action'=>'payment.record','operation_id'=>'stable-payment-'.$type,'invoice_id'=>$invoice['id'],'amount_atomic'=>$expected];$paid=$pos->command($payment);$assert($pos->command($payment)===$paid,'payment replay has one ledger effect');
 $version=1;foreach($type==='laundry'?['washing','drying','ironing','ready','collected']:['checked_in','checked_out']as$status){$run('order.transition',['id'=>$order['id'],'version'=>$version++,'status'=>$status]);}
 $close=$run('shift.close',['closing_atomic'=>5000+$expected]);$assert($close['variance_atomic']===0&&$close['received_atomic']===$expected,'isolated close report reconciles');
 $edited=$run('resource.save',['id'=>$resource['id'],'version'=>1,'name'=>'Updated '.$type,'unit'=>$type==='laundry'?'kg':($type==='boarding'?'month':'night'),'rate_atomic'=>12000,'currency'=>'IDR','scale'=>0,'enabled'=>false]);$assert($edited['version']===2&&$pos->read()['orders'][0]['total_atomic']===$expected,'resource edits preserve historical prices');
 try{$run('order.transition',['id'=>$order['id'],'version'=>$version,'status'=>$type==='laundry'?'washing':'checked_in']);$assert(false,'closed state reopened');}catch(PlatformViolation){$assert(true,'terminal state cannot reopen');}
}
$assert($db->table('oneqay_laundry_payments')->count()===1&&$db->table('oneqay_boarding_payments')->count()===1&&$db->table('oneqay_homestay_payments')->count()===1,'payment ledgers are physically separate');
// Ambiguous responses: preserve committed work, tombstone uncommitted work under the same serialization lock.
$reordered=array_reverse($input,true);$assert($pos->reconcile($reordered)===$order,'reconciliation preserves a previously committed order');
$cancelledInput=['action'=>'shift.open','operation_id'=>'cancel-uncommitted-service-op','opening_atomic'=>2000];
$cancelled=$pos->reconcile($cancelledInput);$assert($cancelled['rejected']===true,'uncommitted request receives durable tombstone');
$assert($pos->command($cancelledInput)===$cancelled&&$pos->read()['shift']===null,'late request cannot execute after reconciliation');
try{$pos->command(array_replace($cancelledInput,['opening_atomic'=>3000]));$assert(false,'changed cancelled payload accepted');}catch(PlatformViolation $e){$assert($e->reason==='OPERATION_CONFLICT','tombstone still binds exact payload');}
$contexts->setVerified($cashierScope);try{(new App\Infrastructure\Identity\LaravelBusinessOwnerSecurity())->status();$assert(false,'cashier accesses owner security');}catch(App\Application\Business\BusinessViolation|App\Application\Authorization\DurableAuthorizationViolation|PlatformViolation){$assert(true,'cashier cannot access owner security');}
$contexts->setVerified($businessOwner);
$contexts->setVerified($owner);$oldProfile=$db->table('oneqay_business_profiles')->where('business_id',$b['id'])->first();$result=$domains->rename(['business_id'=>$b['id'],'slug'=>'kopikaylangsa','reason'=>'Synthetic owner rename','version'=>(int)$oldProfile->version,'operation_id'=>'rename-kopikay-test']);
$assert($result['url']==='https://kopikaylangsa.oneqay.example.test'&&!$domains->available('kopikay'),'retired name cannot be reused');
$assert($db->table('oneqay_mail_messages')->where('tenant_id',$t)->where('recipient_hash',hash('sha256',strtolower($b['email'])))->exists(),'domain change enqueues owner notification');
$contexts->setVerified($businessOwner);$security=new App\Infrastructure\Identity\LaravelBusinessOwnerSecurity();$status=$security->status();$clock->at+=30;
$request=Illuminate\Http\Request::create('/pos/business/security','POST',['action'=>'disable','password'=>'synthetic-long-password','code'=>$totp->at($clock->at),'version'=>$status['version']]);$request->setLaravelSession($app['session']->driver());$request->session()->start();$disabled=$security->command($request);
$assert($disabled['redirect']==='/business-login'&&App\Infrastructure\Identity\BusinessOwnerSecurityPolicy::disabled($db,$t,$businessOwner->identityId()->value()),'exact owner disables own MFA');
$assert(!App\Infrastructure\Identity\BusinessOwnerSecurityPolicy::disabled($db,'platform-owner','owner-identity'),'platform owner never optional');
$assert($app->make(App\Application\Identity\PrivilegedTotpMfaService::class)->requiredState($businessOwner->tenantId(),$businessOwner->identityId())===null,'disabled owner login no MFA challenge');
$securityRequest=function(array $in)use($app){$r=Illuminate\Http\Request::create('/pos/business/security','POST',$in);$r->setLaravelSession($app['session']->driver());$r->session()->start();return $r;};
$status=$security->status();try{$security->command($securityRequest(['action'=>'enable','password'=>'wrong-password','version'=>$status['version']]));$assert(false,'wrong password enables MFA');}catch(PlatformViolation $e){$assert($e->reason==='REAUTHENTICATION_FAILED','TOTP change requires current password');}
$issued=$security->command($securityRequest(['action'=>'enable','password'=>'synthetic-long-password','version'=>$status['version']]));
$assert(isset($issued['secret'])&&$security->status()['required']===false,'pending enrollment does not invalidate the active optional-MFA session');
try{$security->command($securityRequest(['action'=>'confirm','password'=>'synthetic-long-password','code'=>'invalid','version'=>$issued['version']]));$assert(false,'invalid enrollment code accepted');}catch(App\Application\Identity\PrivilegedTotpMfaViolation){$assert($security->status()['required']===false,'failed enrollment rolls back mandatory policy');}
$clock->at+=30;$newTotp=OTPHP\TOTP::createFromSecret($issued['secret']);
$confirmed=$security->command($securityRequest(['action'=>'confirm','password'=>'synthetic-long-password','code'=>$newTotp->at($clock->at),'version'=>$issued['version']]));
$assert($confirmed['redirect']==='/business-login'&&$security->status()['required']===true,'confirm enrollment activates policy and revokes sessions');
$contexts->setVerified($owner);try{$security->status();$assert(false,'platform owner optional MFA exposed');}catch(App\Application\Authorization\DurableAuthorizationViolation|PlatformViolation){$assert(true,'platform owner cannot disable mandatory MFA here');}

// Host-bound one-time handoff, tested without real DNS or network.
use App\Delivery\Http\Identity\FirstPartySessionKeys as SessionKeys;
$entry=$app->make(App\Delivery\Http\BusinessExperience\BusinessExperienceController::class);
$makeTicket=function(string $host,int $expiry)use($app,$db,$b){$ticket=bin2hex(random_bytes(32));$db->table('oneqay_business_handoffs')->insert(['token_hash'=>hash('sha256',$ticket),'business_id'=>$b['id'],'hostname'=>$host,'state_hash'=>hash('sha256','synthetic-browser-state'),'encrypted_session'=>$app->make('encrypter')->encryptString(json_encode([SessionKeys::TENANT=>$b['tenant_id'],SessionKeys::SESSION_AUTHORITY_ID=>'synthetic-bound-authority'])),'expires_at'=>$expiry,'consumed_at'=>null]);return $ticket;};
$entryRequest=function(string $host,string $ticket)use($app,$b){$r=Illuminate\Http\Request::create('https://'.$host.'/business-entry','POST',['ticket'=>$ticket]);$r->attributes->set('oneqay.host_business',$b);$r->setLaravelSession($app['session']->driver());$r->session()->start();$r->session()->put(['oneqay.handoff.state'=>'synthetic-browser-state','oneqay.handoff.expires'=>time()+120]);return $r;};
$host='kopikaylangsa.oneqay.example.test';$ticket=$makeTicket($host,time()+45);
try{$entry->consume($entryRequest('wrong.oneqay.example.test',$ticket));$assert(false,'wrong audience consumes ticket');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===401,'wrong-host handoff rejected');}
$wrongBrowser=$entryRequest($host,$ticket);$wrongBrowser->session()->put('oneqay.handoff.state','wrong-browser-state');try{$entry->consume($wrongBrowser);$assert(false,'other browser accepts handoff');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===401,'handoff binds initiating browser state');}
$r=$entryRequest($host,$ticket);$response=$entry->consume($r);$assert($response->getData(true)['redirect']==='/pos'&&$r->session()->get(SessionKeys::TENANT)===$b['tenant_id'],'handoff consumes on exact host');
try{$entry->consume($entryRequest($host,$ticket));$assert(false,'handoff replay accepted');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===401,'handoff replay rejected');}
$expired=$makeTicket($host,time()-1);try{$entry->consume($entryRequest($host,$expired));$assert(false,'expired handoff accepted');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===401,'expired handoff rejected');}
$assert(ServiceWorkflow::nextPeriod('2026-01-31','2026-01-31')==='2026-02-28'&&ServiceWorkflow::nextPeriod('2026-02-28','2026-01-31')==='2026-03-31','monthly billing preserves original anniversary across short months');
echo 'BUSINESS_EXPERIENCE_'.$engine.'_PASS assertions='.$n.PHP_EOL;
