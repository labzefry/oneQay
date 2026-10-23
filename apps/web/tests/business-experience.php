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
 $run=fn($action,$fields=[])=>$pos->command(['action'=>$action,'operation_id'=>'service-test-'.bin2hex(random_bytes(12))]+$fields);
 $resource=$run('resource.save',['id'=>'','version'=>0,'name'=>'Unit '.$type,'unit'=>$type==='laundry'?'kg':($type==='boarding'?'month':'night'),'rate_atomic'=>10000,'currency'=>'IDR','scale'=>0,'enabled'=>true]);
 $start=gmdate('Y-m-d');$end=gmdate('Y-m-d',time()+86400*3);
 $input=['action'=>'order.create','operation_id'=>'stable-order-'.$type,'resource_id'=>$resource['id'],'customer_name'=>'Synthetic customer','contact'=>'+628000000','start_date'=>$start,'end_date'=>$end,'quantity_milli'=>$type==='laundry'?1250:1000];
 $order=$pos->command($input);$assert($pos->command($input)===$order,'order replay does not duplicate');$expected=$type==='laundry'?12500:($type==='homestay'?30000:10000);$assert($order['total_atomic']===$expected,'vertical pricing uses its own rule');
 $workspace=$pos->read();$assert(count($workspace['resources'])===1&&count($workspace['orders'])===1&&$workspace['type']===$type,'vertical data isolated');
 if($type!=='laundry'){try{$run('order.create',array_diff_key($input,array_flip(['action','operation_id'])));$assert(false,'overlap allowed');}catch(PlatformViolation $e){$assert($e->reason==='ROOM_ALREADY_RESERVED','room overlap denied');}}
 $invoice=$workspace['invoices'][0];try{$run('payment.record',['invoice_id'=>$invoice['id'],'amount_atomic'=>$expected]);$assert(false,'payment before open');}catch(PlatformViolation $e){$assert($e->reason==='OWN_SHIFT_REQUIRED','own open register required');}
 $run('shift.open',['opening_atomic'=>5000]);
 try{$run('payment.record',['invoice_id'=>$invoice['id'],'amount_atomic'=>$expected+1]);$assert(false,'overpayment accepted');}catch(PlatformViolation $e){$assert($e->reason==='PAYMENT_EXCEEDS_BALANCE','overpayment blocked');}
 $payment=['action'=>'payment.record','operation_id'=>'stable-payment-'.$type,'invoice_id'=>$invoice['id'],'amount_atomic'=>$expected];$paid=$pos->command($payment);$assert($pos->command($payment)===$paid,'payment replay has one ledger effect');
 $version=1;foreach($type==='laundry'?['washing','drying','ironing','ready','collected']:['checked_in','checked_out']as$status){$run('order.transition',['id'=>$order['id'],'version'=>$version++,'status'=>$status]);}
 $close=$run('shift.close',['closing_atomic'=>5000+$expected]);$assert($close['variance_atomic']===0&&$close['received_atomic']===$expected,'isolated close report reconciles');
 try{$run('order.transition',['id'=>$order['id'],'version'=>$version,'status'=>$type==='laundry'?'washing':'checked_in']);$assert(false,'closed state reopened');}catch(PlatformViolation){$assert(true,'terminal state cannot reopen');}
}
$assert($db->table('oneqay_laundry_payments')->count()===1&&$db->table('oneqay_boarding_payments')->count()===1&&$db->table('oneqay_homestay_payments')->count()===1,'payment ledgers are physically separate');
$contexts->setVerified($owner);$oldProfile=$db->table('oneqay_business_profiles')->where('business_id',$b['id'])->first();$result=$domains->rename(['business_id'=>$b['id'],'slug'=>'kopikaylangsa','reason'=>'Synthetic owner rename','version'=>(int)$oldProfile->version,'operation_id'=>'rename-kopikay-test']);
$assert($result['url']==='https://kopikaylangsa.oneqay.example.test'&&!$domains->available('kopikay'),'retired name cannot be reused');
$assert($db->table('oneqay_mail_messages')->where('tenant_id',$t)->where('recipient_hash',hash('sha256',strtolower($b['email'])))->exists(),'domain change enqueues owner notification');
$contexts->setVerified($businessOwner);$security=new App\Infrastructure\Identity\LaravelBusinessOwnerSecurity();$status=$security->status();$clock->at+=30;
$request=Illuminate\Http\Request::create('/pos/business/security','POST',['action'=>'disable','password'=>'synthetic-long-password','code'=>$totp->at($clock->at),'version'=>$status['version']]);$request->setLaravelSession($app['session']->driver());$request->session()->start();$disabled=$security->command($request);
$assert($disabled['redirect']==='/business-login'&&App\Infrastructure\Identity\BusinessOwnerSecurityPolicy::disabled($db,$t,$businessOwner->identityId()->value()),'exact owner disables own MFA');
$assert(!App\Infrastructure\Identity\BusinessOwnerSecurityPolicy::disabled($db,'platform-owner','owner-identity'),'platform owner never optional');
$assert($app->make(App\Application\Identity\PrivilegedTotpMfaService::class)->requiredState($businessOwner->tenantId(),$businessOwner->identityId())===null,'disabled owner login no MFA challenge');
echo 'BUSINESS_EXPERIENCE_'.$engine.'_PASS assertions='.$n.PHP_EOL;
