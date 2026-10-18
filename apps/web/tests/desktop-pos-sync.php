<?php
// Author by Lab | zefry. Real disposable SQL and canonical POS use cases, no live money.
declare(strict_types=1);
require __DIR__.'/platform-control-mail.php';
set_exception_handler(static function(Throwable $e):void{fwrite(STDERR,$e::class.': '.$e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL);exit(1);});
foreach(['platform/2026_10_16_000001_create_control_plane_operations.php','pos/2026_10_18_000001_create_desktop_pos_sync.php']as$file)(require __DIR__.'/../database/module-migrations/'.$file)->up();
$app['config']->set('oneqay.pos_sale_completion.enabled',true);$app['config']->set('oneqay.pos_shift_opening.enabled',true);
$app->forgetScopedInstances();
$contexts=$app->make(App\Application\Organization\OrganizationalContextStore::class);
$scope=new App\Application\Organization\VerifiedOrganizationalContext(App\Domain\Identity\PlatformIdentityId::fromString($context['identity_id']),App\Domain\Tenancy\TenantId::fromString($context['tenant_id']),App\Domain\Organization\OrganizationId::fromString($context['organization_id']),App\Domain\Outlet\OutletId::fromString($context['outlet_id']),App\Domain\Device\DeviceId::fromString($context['device_id']));
$contexts->setVerified($scope);$t=$context['tenant_id'];$identity=$context['identity_id'];
$db->table('oneqay_pos_sale_catalog_items')->insert(['tenant_id'=>$t,'outlet_id'=>$context['outlet_id'],'product_id'=>'desktop-coffee','display_name'=>'Kopi','unit_price_atomic'=>15000,'currency'=>'IDR','currency_scale'=>0,'available_quantity'=>20,'active'=>true]);
$shift=$app->make(App\Application\Pos\OpenShift::class)->open(new App\Application\Pos\ShiftOpeningCommand('desktop-test-open-shift'),'desktop-test-shift-correlation');
$cipher=$app->make('encrypter');
$db->table('oneqay_identity_totp_factors')->updateOrInsert(['tenant_id'=>$t,'identity_id'=>$identity],['secret_ciphertext'=>$cipher->encryptString(json_encode(['v'=>1,'tenant_id'=>$t,'identity_id'=>$identity,'secret'=>'JBSWY3DPEHPK3PXP'])),'factor_epoch'=>1,'created_at_unix'=>time()-60,'confirmed_at_unix'=>time()-50,'last_accepted_time_step'=>1]);
$epoch=$app->make(App\Application\Identity\FirstPartyCredentialEpochRepository::class)->current($scope->tenantId(),$scope->identityId());
$session=$app->make(App\Application\Identity\FirstPartySessionAuthorityService::class)->issue($scope->tenantId(),$scope->identityId(),$context['organization_id'],$context['outlet_id'],$context['device_id'],$epoch,1,'desktop-test-session-correlation');
$request=Illuminate\Http\Request::create('/pos/desktop/pair','POST');$request->setLaravelSession($app->make('session')->driver());
foreach(['tenant_id'=>App\Delivery\Http\Identity\FirstPartySessionKeys::TENANT,'identity_id'=>App\Delivery\Http\Identity\FirstPartySessionKeys::IDENTITY,'organization_id'=>App\Delivery\Http\Identity\FirstPartySessionKeys::ORGANIZATION,'outlet_id'=>App\Delivery\Http\Identity\FirstPartySessionKeys::OUTLET,'device_id'=>App\Delivery\Http\Identity\FirstPartySessionKeys::DEVICE]as$key=>$sessionKey)$request->session()->put($sessionKey,$context[$key]);
$request->session()->put(App\Delivery\Http\Identity\FirstPartySessionKeys::SESSION_AUTHORITY_ID,$session->authorityId());$request->session()->put(App\Delivery\Http\Identity\FirstPartySessionKeys::CREDENTIAL_EPOCH,$epoch);$request->session()->put(App\Delivery\Http\Identity\FirstPartySessionKeys::MFA_FACTOR_EPOCH,1);
$desktop=$app->make(App\Infrastructure\Pos\DesktopPosService::class);$pair=$desktop->pair($request);$connected=$desktop->exchange($pair['code']);
$assert(strlen($connected['token'])===64&&count($connected['snapshot']['catalog'])===1,'single use pairing returns scoped catalog and token');
$assert(!str_contains(json_encode($db->table('oneqay_desktop_credentials')->get()),$connected['token']),'server stores only token digest');
try{$desktop->exchange($pair['code']);$assert(false,'pair code reused');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===401,'consumed pairing rejected');}
$desktop->authenticate($connected['token']);$snapshot=$connected['snapshot'];
$op=static fn(int $q=1):array=>['created_at'=>time(),'kind'=>'sale','lease_id'=>$snapshot['lease_id'],'lines'=>[['product_id'=>'desktop-coffee','quantity'=>$q]],'operation_id'=>'desk-'.Illuminate\Support\Str::uuid()->toString(),'tendered'=>50000];
$first=$op(2);$result=$desktop->sync([$first],'desktop-test-sync-correlation')['results'][0];
$assert($result['state']==='accepted'&&$result['total']===30000&&$result['change']===20000,'offline cash sale uses server canonical completion');
$assert($desktop->sync([$first],'desktop-test-replay-correlation')['results'][0]===$result,'lost response replays exact durable outcome');
$assert((int)$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$t)->value('available_quantity')===18&&$db->table('oneqay_pos_sales')->where('tenant_id',$t)->count()===1,'retry has one sale and one stock decrement');
$changed=$first;$changed['tendered']=60000;$assert($desktop->sync([$changed],'desktop-test-conflict-correlation')['results'][0]['state']==='rejected','same operation ID changed payload rejected');
$assert($desktop->snapshot()['lease_id']===$snapshot['lease_id'],'stock refresh reuses immutable price lease instead of duplicating catalog');
$price=$op();$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$t)->update(['unit_price_atomic'=>17000]);$r=$desktop->sync([$price],'desktop-test-price-correlation')['results'][0];$assert($r['code']==='POS_CATALOG_CHANGED','server does not silently reprice offline receipt');
$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$t)->update(['unit_price_atomic'=>15000,'available_quantity'=>0]);$r=$desktop->sync([$op()],'desktop-test-stock-correlation')['results'][0];$assert($r['code']==='POS_STOCK_CHANGED','depleted stock becomes explicit conflict');
$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$t)->update(['available_quantity'=>18]);
$count=$op(7);$count['kind']='count';$count['tendered']=0;$r=$desktop->sync([$count],'desktop-test-count-correlation')['results'][0];$assert($r['state']==='review'&&(int)$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$t)->value('available_quantity')===18,'stock count is retained for review without inventory overwrite');
$bad=$op();$bad['lines'][0]['product_id']='other-tenant-product';$assert($desktop->sync([$bad],'desktop-test-product-correlation')['results'][0]['state']==='rejected','foreign product cannot be borrowed');
$bad=$op();$bad['lines'][0]['quantity']=1.5;$assert($desktop->sync([$bad],'desktop-test-numeric-correlation')['results'][0]['state']==='rejected','fractional quantity rejected before persistence');
$bad=$op();$bad['created_at']=$snapshot['expires_at']+1;$assert($desktop->sync([$bad],'desktop-test-lease-correlation')['results'][0]['code']==='POS_LEASE_INVALID','expired lease timestamp cannot be submitted');
$db->table('oneqay_pos_shifts')->where('tenant_id',$t)->update(['active_slot'=>null]);$r=$desktop->sync([$op()],'desktop-test-shift-correlation')['results'][0];$assert($r['code']==='POS_SHIFT_CHANGED','closed shift never silently reassigns sale');
$assert($desktop->sync([$first],'desktop-test-late-replay')['results'][0]===$result,'accepted replay still succeeds after shift closure');
$db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$t)->update(['revoked_at_unix'=>time()]);
try{$desktop->authenticate($connected['token']);$assert(false,'revoked device authority accepted');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===401&&$contexts->current()===null,'session revocation clears context and rejects native token');}
$assert($db->table('oneqay_desktop_operations')->where('tenant_id',$t)->count()===6,'accepted and review outcomes retained for recovery');
echo 'DESKTOP_POS_SYNC_'.$engine.'_PASS assertions='.$n.PHP_EOL;
