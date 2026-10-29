<?php
// Author by Lab | zefry. Synthetic data only; verifies scope and idempotency.
declare(strict_types=1);
$menuCompleted=false;register_shutdown_function(static function()use(&$menuCompleted):void{if(!$menuCompleted){fwrite(STDERR,"MENU_BOOK_TEST_DID_NOT_COMPLETE\n");exit(1);}});
require __DIR__.'/business-experience.php';
(require __DIR__.'/../database/module-migrations/business/2026_10_29_000001_create_menu_book_catalog.php')->up();
$contexts->setVerified($businessOwner);
$menu=new App\Infrastructure\Business\LaravelMenuBookCatalog($db,$app->make(App\Application\Business\BusinessAccess::class),$app->make(App\Application\Business\BusinessRepository::class),$app->make(App\Application\Business\MerchandisingRepository::class));
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
$resetTables=array_column($resetPlan['entries'],'table');$assert(array_search('oneqay_menu_book_items',$resetTables,true)<array_search('oneqay_menu_book_categories',$resetTables,true)&&array_search('oneqay_menu_book_categories',$resetTables,true)<array_search('oneqay_pos_sale_catalog_items',$resetTables,true),'product reset includes menu dependencies before catalog');
$controller=new App\Delivery\Http\Business\MenuBookController($menu,$app->make(App\Application\Business\Merchandising::class));
$request=Illuminate\Http\Request::create('/pos/business/menu-book/categories','POST',['operation_id'=>'http-menu-category-0001','data'=>['name'=>'Makanan']]);$request->headers->set('Accept','application/json');$response=$controller->category($request);$assert($response->getStatusCode()===200&&$response->getData(true)['record']['name']==='Makanan','authenticated category endpoint returns created category');
// Anonymous context must never expose an owner's menu configuration.
$contexts->clear();try{$menu->categories();$assert(false,'anonymous menu management allowed');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='CONTEXT_REQUIRED','anonymous management denied');}
$anonymousResponse=$controller->category($request);$assert($anonymousResponse->getStatusCode()===403,'anonymous management endpoint denied');
$contexts->setVerified($businessOwner);
$menuCompleted=true;echo 'MENU_BOOK_CATALOG_'.$engine.'_PASS assertions='.$n.PHP_EOL;
