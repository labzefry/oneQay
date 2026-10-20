<?php
// Author by Lab | zefry. Disposable SQL, synthetic credentials, queued email only.
declare(strict_types=1);
require __DIR__.'/platform-control-mail.php';
set_exception_handler(static function(Throwable $e):void{fwrite(STDERR,$e::class.': '.$e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL);exit(1);});
foreach(['platform/2026_10_16_000001_create_control_plane_operations.php','pos/2026_10_18_000001_create_desktop_pos_sync.php','pos/2026_10_19_000001_create_desktop_pos_operations.php']as$file)(require __DIR__.'/../database/module-migrations/'.$file)->up();
foreach(['pos_sale_completion','pos_shift_opening','pos_shift_opening_cash_evidence','pos_shift_closing_cash_evidence','pos_cash_variance_reconciliation']as$feature)$app['config']->set('oneqay.'.$feature.'.enabled',true);
putenv('ONEQAY_POS_SHIFT_CLOSE_ENABLED=true');$_ENV['ONEQAY_POS_SHIFT_CLOSE_ENABLED']='true';$_SERVER['ONEQAY_POS_SHIFT_CLOSE_ENABLED']='true';
$app->forgetScopedInstances();$contexts=$app->make(App\Application\Organization\OrganizationalContextStore::class);
$businessOwner=new App\Application\Organization\VerifiedOrganizationalContext(App\Domain\Identity\PlatformIdentityId::fromString($context['identity_id']),App\Domain\Tenancy\TenantId::fromString($context['tenant_id']),App\Domain\Organization\OrganizationId::fromString($context['organization_id']),App\Domain\Outlet\OutletId::fromString($context['outlet_id']),App\Domain\Device\DeviceId::fromString($context['device_id']));
$contexts->setVerified($businessOwner);$t=$context['tenant_id'];$settings=$app->make(App\Infrastructure\Pos\LaravelDesktopBusinessSettings::class);
$prefs=$settings::defaults();$prefs['report_recipients']=['accounting@example.test'];$prefs['kitchens']=[['id'=>'bar','name'=>'Bar','categories'=>['Minuman']]];
$assert($settings->save(['version'=>0,'settings'=>$prefs])['version']===1,'owner saves receipt, security and kitchen preferences');
$assert($settings->save(['version'=>0,'settings'=>$prefs])['version']===1,'settings retry idempotent');
$cashier=['login'=>'cashier@example.test','name'=>'Kasir Sintetis','password'=>'synthetic-cashier-password','pin'=>'482913','enabled'=>true,'version'=>0,'operation_id'=>'desktop-create-cashier-one'];$settings->cashier($cashier);$settings->cashier($cashier);
$assert($db->table('oneqay_desktop_cashiers')->count()===1,'cashier provisioning replay creates one canonical identity');
$assert(!str_contains($db->table('oneqay_desktop_cashiers')->value('pin_verifier'),'482913'),'PIN hashed and encrypted');
$db->table('oneqay_pos_sale_catalog_items')->insert(['tenant_id'=>$t,'outlet_id'=>$context['outlet_id'],'product_id'=>'desktop-coffee','display_name'=>'Kopi','unit_price_atomic'=>15000,'currency'=>'IDR','currency_scale'=>0,'available_quantity'=>20,'active'=>true]);
$desktop=$app->make(App\Infrastructure\Pos\LaravelDesktopPosService::class);$login=$app->make(App\Infrastructure\Pos\LaravelDesktopCashierLogin::class);
$connected=$login->login(['login'=>$cashier['login'],'password'=>$cashier['password'],'totp'=>''],'desktop-login-correlation');
$assert($connected['snapshot']['protocol']===2&&$connected['snapshot']['shift_id']==='','direct cashier login before shift');
$assert($connected['snapshot']['cashier_name']===$cashier['name']&&isset($connected['snapshot']['pin']['hash']),'native gets cashier and protected PIN verifier');
$desktop->authenticate($connected['token']);$cashierScope=$contexts->current();
try{$settings->save(['version'=>1,'settings'=>$prefs]);$assert(false,'cashier allowed owner settings');}catch(App\Application\Authorization\DurableAuthorizationViolation){$assert(true,'cashier denied owner configuration');}
$journal=$app->make(App\Infrastructure\Pos\LaravelDesktopOperations::class);$uuid=fn()=>'desk-'.Illuminate\Support\Str::uuid();$open=$uuid();$lease=$connected['snapshot']['lease_id'];
$event=fn(string $id,string $kind,int $cash,array $lines=[]):array=>['operation_id'=>$id,'kind'=>$kind,'lease_id'=>$lease,'shift_id'=>$open,'created_at'=>time(),'lines'=>$lines,'tendered'=>$cash,'table'=>'04','order'=>'A-001','note'=>''];
$opening=$event($open,'shift_open',100000);$r=$journal->sync([$opening],'desktop-opening-correlation')['results'][0];$assert($r['state']==='accepted'&&isset($r['shift_id']),'canonical shift and opening cash');
$assert($journal->sync([$opening],'desktop-opening-retry')['results'][0]===$r,'opening replay exact');
$sale=$event($uuid(),'sale',20000,[['product_id'=>'desktop-coffee','quantity'=>1]]);$r=$journal->sync([$sale],'desktop-sale-correlation')['results'][0];$assert($r['state']==='accepted'&&$r['total']===15000,'canonical sale succeeds');
$assert($journal->sync([$sale],'desktop-sale-retry')['results'][0]===$r,'sale replay exact');$bad=$sale;$bad['table']='05';$assert($journal->sync([$bad],'desktop-sale-change')['results'][0]['state']==='rejected','changed table on retry rejected');
$closing=$event($uuid(),'shift_close',115000);$r=$journal->sync([$closing],'desktop-close-correlation')['results'][0];if(($r['code']??'')==='POS_SYNC_RETRY'){usleep(1100000);$r=$journal->sync([$closing],'desktop-close-correlation')['results'][0];}
$assert($r['state']==='pending'&&$r['report']['variance']===0,'closure needs independent finalization');$assert(strlen($db->table('oneqay_pos_shift_closing_cash_evidence')->where('tenant_id',$t)->value('evidence_id'))===32,'closing evidence fits the existing strict MySQL column');$assert($r['mail_queued']===1,'provisional report queued');$before=$db->table('oneqay_mail_messages')->count();$journal->sync([$closing],'desktop-close-retry');$assert($before===$db->table('oneqay_mail_messages')->count(),'email not duplicated');
$contexts->setVerified($businessOwner);$assert(count($journal->pendingClosures())===1,'scoped owner closure list');$r=$journal->finalize($closing['operation_id'],'desktop-finalize-correlation');$assert($r['state']==='accepted'&&$r['report']['final'],'independent owner canonical finalization');
$contexts->setVerified($cashierScope);$assert($journal->sync([$closing],'desktop-final-ack')['results'][0]['state']==='accepted','final acknowledgement reaches cashier');
$contexts->setVerified($businessOwner);$settings->cashier(array_replace($cashier,['version'=>1,'password'=>'','pin'=>'','enabled'=>false]));
try{$desktop->authenticate($connected['token']);$assert(false,'disabled cashier authenticated');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===401,'disable revokes native session');}
echo 'DESKTOP_ENTERPRISE_'.$engine.'_PASS assertions='.$n.PHP_EOL;
