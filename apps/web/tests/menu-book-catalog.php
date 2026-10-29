<?php
// Author by Lab | zefry. Synthetic data only; verifies scope and idempotency.
declare(strict_types=1);
$menuCompleted=false;register_shutdown_function(static function()use(&$menuCompleted):void{if(!$menuCompleted){fwrite(STDERR,"MENU_BOOK_TEST_DID_NOT_COMPLETE\n");exit(1);}});
require __DIR__.'/business-experience.php';
(require __DIR__.'/../database/module-migrations/business/2026_10_29_000001_create_menu_book_catalog.php')->up();
$contexts->setVerified($businessOwner);
$menu=$app->make(App\Infrastructure\Business\LaravelMenuBookCatalog::class);
$assert(Illuminate\Support\Facades\Route::has('business.menu-book'),'private menu route registered with application');
$first=$menu->createCategory('menu-category-create-0001',['name'=>'Minuman Kopi'],'menu-test');
$assert($first===$menu->createCategory('menu-category-create-0001',['name'=>'Minuman Kopi'],'menu-test'),'category replay is idempotent');
$assert($first===$menu->createCategory('menu-category-create-0002',['name'=>'minuman kopi'],'menu-test'),'category matching is case insensitive');
$assert(count($menu->categories())===1,'one independent menu category');
$data=['product_id'=>'desktop-coffee','category_id'=>$first['id'],'visible'=>true,'spotlight'=>false,'version'=>0];
$one=$menu->assign('menu-assign-product-0001',$data,'menu-test');$assert($one===$menu->assign('menu-assign-product-0001',$data,'menu-test'),'product assignment replay idempotent');
$assert($menu->items(['desktop-coffee','missing-product'])['desktop-coffee']['category_id']===$first['id'],'bounded item lookup returns existing scoped product assignment');
$assert($menu->item('desktop-coffee')['visible']===true&&$menu->item('desktop-coffee')['spotlight']===false,'visibility and spotlight preserved independently');
try{$menu->assign('menu-assign-stale-0001',$data,'menu-test');$assert(false,'stale version accepted');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='RECORD_CHANGED','stale update rejected');}
try{$menu->assign('menu-assign-invalid-0001',array_replace($data,['category_id'=>str_repeat('f',32),'version'=>1]),'menu-test');$assert(false,'foreign category accepted');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='MENU_CATEGORY_NOT_FOUND','unknown category denied');}
try{$menu->assign('menu-assign-missing-0001',array_replace($data,['product_id'=>'missing-product']),'menu-test');$assert(false,'foreign product accepted');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='PRODUCT_NOT_FOUND','unknown product denied');}
$assert($db->table('oneqay_pos_sale_catalog_items')->where('product_id','desktop-coffee')->value('unit_price_atomic')==15000,'menu never writes canonical product price');
$resetPlan=(new App\Infrastructure\Platform\ControlPlaneBusinessReset($db,$app->make('encrypter')))->preview(array_replace($b,['status'=>'suspended','updated_at'=>time()-120]),'products');
$resetTables=array_column($resetPlan['entries'],'table');$assert(in_array('oneqay_menu_book_publication',$resetTables,true),'product reset also removes stale published snapshots');$assert(array_search('oneqay_menu_book_items',$resetTables,true)<array_search('oneqay_menu_book_categories',$resetTables,true)&&array_search('oneqay_menu_book_categories',$resetTables,true)<array_search('oneqay_pos_sale_catalog_items',$resetTables,true),'product reset includes menu dependencies before catalog');
$controller=$app->make(App\Delivery\Http\Business\MenuBookController::class);
$request=Illuminate\Http\Request::create('/pos/business/menu-book/categories','POST',['operation_id'=>'http-menu-category-0001','data'=>['name'=>'Makanan']]);$request->headers->set('Accept','application/json');$response=$controller->category($request);$assert($response->getStatusCode()===200&&$response->getData(true)['record']['name']==='Makanan','authenticated category endpoint returns created category');
// A second synthetic, fully provisioned business cannot see or bind the first business's menu rows.
$contexts->setVerified($owner);
$other=$service->command(array_replace($create,['operation_id'=>'menu-second-business-create','name'=>'Synthetic second business','email'=>'menu-second@example.test']));
$service->command(['action'=>'invite','id'=>$other['id'],'version'=>1,'operation_id'=>'menu-second-business-invite','reason'=>'Synthetic isolation test']);
$otherBusiness=$store->business($other['id']);$otherMail=$db->table('oneqay_mail_messages')->where('tenant_id',$otherBusiness['tenant_id'])->first();$otherPayload=json_decode($app->make('encrypter')->decryptString($otherMail->payload),true);preg_match('/#token=([a-f0-9]{64})/',$otherPayload['text'],$otherToken);
$otherContext=$service->activate($otherToken[1],'synthetic-second-password');
$otherOwner=new App\Application\Organization\VerifiedOrganizationalContext(App\Domain\Identity\PlatformIdentityId::fromString($otherContext['identity_id']),App\Domain\Tenancy\TenantId::fromString($otherContext['tenant_id']),App\Domain\Organization\OrganizationId::fromString($otherContext['organization_id']),App\Domain\Outlet\OutletId::fromString($otherContext['outlet_id']),App\Domain\Device\DeviceId::fromString($otherContext['device_id']));
$contexts->setVerified($otherOwner);
$assert($menu->categories()===[]&&$menu->item('desktop-coffee')===null,'second tenant cannot read first tenant categories or item');
$db->table('oneqay_pos_sale_catalog_items')->insert(['tenant_id'=>$otherContext['tenant_id'],'outlet_id'=>$otherContext['outlet_id'],'product_id'=>'second-coffee','display_name'=>'Second coffee','unit_price_atomic'=>7000,'currency'=>'IDR','currency_scale'=>0,'available_quantity'=>3,'active'=>true]);
try{$menu->assign('second-cross-tenant-category',array_replace($data,['product_id'=>'second-coffee']),'menu-test');$assert(false,'cross-tenant category accepted');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='MENU_CATEGORY_NOT_FOUND','cross-tenant category denied');}
$secondCategory=$menu->createCategory('second-create-menu-category',['name'=>'Minuman Kopi'],'menu-test');
try{$menu->assign('second-cross-tenant-product',array_replace($data,['category_id'=>$secondCategory['id']]),'menu-test');$assert(false,'cross-tenant product accepted');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='PRODUCT_NOT_FOUND','cross-tenant product denied');}
$assert(count($menu->categories())===1&&$secondCategory['id']!==$first['id'],'identical category names stay independent between tenants');
$contexts->setVerified($businessOwner);
// Preview uses verified owner scope and the same projection as the published page.
$previewRequest=Illuminate\Http\Request::create('/pos/business/menu-book/preview','GET');$previewRequest->headers->set('X-Inertia','true');$previewRequest->setLaravelSession($app['session']->driver());
$previewResponse=$controller->preview($previewRequest);$previewData=json_decode($previewResponse->getContent(),true);$assert($previewResponse->getStatusCode()===200&&$previewData['props']['preview']===true&&count($previewData['props']['items'])===1,'unpublished menu can be privately previewed by its manager');
// Public rendering requires a middleware-derived active business and an explicit publication.
config(['platform.business_domains_enabled'=>true]);$public=$app->make(App\Delivery\Http\Business\PublicMenuBookController::class);$requestPublic=Illuminate\Http\Request::create('https://kopikaylangsa.oneqay.example.test/menu','GET');$requestPublic->headers->set('X-Inertia','true');$requestPublic->attributes->set('oneqay.host_business',$b);
try{$public->show($requestPublic);$assert(false,'unpublished menu rendered');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===404,'unpublished menu hidden');}
$publication=['title'=>'Menu Kopi','description'=>'Selamat datang','action'=>'publish','version'=>0];$saved=$menu->savePublication('publish-menu-test-0001',$publication,'menu-test');$assert($saved['version']===1&&$menu->savePublication('publish-menu-test-0001',$publication,'menu-test')===$saved,'publication is versioned and idempotent');
$requestPublic->setLaravelSession($app['session']->driver());config(['session.domain'=>null]);$hostMiddleware=$app->make(App\Delivery\Http\Middleware\BusinessHostMiddleware::class);$response=$hostMiddleware->handle($requestPublic,fn($r)=>$public->show($r));$payload=json_decode($response->getContent(),true);$assert($response->getStatusCode()===200&&$payload['props']['title']==='Menu Kopi'&&count($payload['props']['items'])===1,'actual business-host middleware resolves published menu and visible product');
$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$t)->where('product_id','desktop-coffee')->update(['unit_price_atomic'=>17000]);$pricePayload=json_decode($public->show($requestPublic)->getContent(),true);$assert($pricePayload['props']['items'][0]['price_atomic']==='17000','published menu uses latest canonical price');$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$t)->where('product_id','desktop-coffee')->update(['unit_price_atomic'=>15000]);
$assert(!str_contains($response->getContent(),$b['email'])&&!str_contains($response->getContent(),'cost_atomic')&&!str_contains($response->getContent(),'tenant_id'),'public menu excludes private account and cost data');
$noHost=Illuminate\Http\Request::create('https://oneqay.example.test/menu');try{$public->show($noHost);$assert(false,'main hostname exposes menu');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===404,'canonical main hostname cannot select another tenant');}
$otherRequest=Illuminate\Http\Request::create('https://other.oneqay.example.test/menu');$otherRequest->attributes->set('oneqay.host_business',$store->business($other['id']));try{$public->show($otherRequest);$assert(false,'unpublished second tenant reveals first menu');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===404,'second tenant cannot access first tenant menu');}
$menu->assign('menu-hide-published-product',array_replace($data,['visible'=>false,'version'=>1]),'menu-test');$payload=json_decode($public->show($requestPublic)->getContent(),true);$assert(count($payload['props']['items'])===1,'unpublished draft visibility changes do not alter live menu');
$draft=$menu->savePublication('save-menu-draft-test-02',array_replace($publication,['action'=>'draft','title'=>'Draft baru','version'=>1]),'menu-test');$payload=json_decode($public->show($requestPublic)->getContent(),true);$assert($draft['published']&&$payload['props']['title']==='Menu Kopi'&&count($payload['props']['items'])===1,'draft title and product changes are not public');
$previewData=json_decode($controller->preview($previewRequest)->getContent(),true);$assert($previewData['props']['title']==='Draft baru'&&$previewData['props']['items']===[],'private preview shows saved draft');
$publishAgain=$menu->savePublication('republish-menu-test-003',array_replace($publication,['title'=>'Draft baru','version'=>2]),'menu-test');$payload=json_decode($public->show($requestPublic)->getContent(),true);$assert($publishAgain['version']===3&&$payload['props']['title']==='Draft baru'&&$payload['props']['items']===[],'republish atomically promotes draft');
$menu->savePublication('unpublish-menu-test-0002',array_replace($publication,['action'=>'unpublish','version'=>3]),'menu-test');try{$public->show($requestPublic);$assert(false,'unpublished page remains visible');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===404,'unpublish immediately blocks public page');}
config(['platform.business_domains_enabled'=>false]);
// Anonymous context must never expose an owner's menu configuration.
$contexts->clear();try{$menu->categories();$assert(false,'anonymous menu management allowed');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='CONTEXT_REQUIRED','anonymous management denied');}
$anonymousResponse=$controller->category($request);$assert($anonymousResponse->getStatusCode()===403,'anonymous management endpoint denied');$assert($controller->preview($previewRequest)->getStatusCode()===403,'anonymous private preview denied');
$contexts->setVerified($businessOwner);
$scope=['tenant_id'=>$businessOwner->tenantId()->value(),'organization_id'=>$businessOwner->organizationId()->value(),'outlet_id'=>$businessOwner->outletId()->value()];
for($i=0;$i<198;$i++){$name='Synthetic category '.$i;$db->table('oneqay_menu_book_categories')->insert($scope+['id'=>bin2hex(random_bytes(16)),'name'=>$name,'name_key'=>hash('sha256',mb_strtolower($name,'UTF-8')),'created_at'=>time()]);}
$assert(count($menu->categories())===200,'all bounded categories remain selectable');
try{$menu->createCategory('menu-category-limit-0001',['name'=>'Over limit'],'menu-test');$assert(false,'unreachable category accepted');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='MENU_CATEGORY_LIMIT','category limit rejected safely');}
$assert($menu->createCategory('menu-category-existing-0003',['name'=>'Minuman Kopi'],'menu-test')===$first,'existing category remains selectable at limit');
$menuCompleted=true;echo 'MENU_BOOK_CATALOG_'.$engine.'_PASS assertions='.$n.PHP_EOL;
