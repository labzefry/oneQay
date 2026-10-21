<?php
declare(strict_types=1);
// Author by Lab | zefry. Disposable SQL only: tenant categories and actual canonical sale/void stock effects.
require __DIR__.'/merchandising-reports-currency.php';
(require __DIR__.'/../database/module-migrations/business/2026_10_21_000001_create_product_categories.php')->up();
foreach([18,24]as$num){$f=glob(__DIR__.'/../database/migrations/0000_00_00_'.str_pad((string)$num,6,'0',STR_PAD_LEFT).'_*.php')[0];(require $f)->up();}
$created=$merch->createCategory('category-create-one',['name'=>'  Jasa   servis  '],'category-correlation');
$assert($created['name']==='Jasa servis','category normalizes whitespace and can exist before products');
$assert($merch->createCategory('category-create-one',['name'=>'  Jasa   servis  '],'category-correlation')===$created,'category lost-response replay is stable');
$assert($merch->createCategory('category-create-two',['name'=>'jasa servis'],'category-correlation')===$created,'case-fold duplicate reuses existing category');
$assert(in_array('Jasa servis',$merch->products()['categories'],true),'empty category appears in selector and directory');
$store->scope=$context('tenant-b');$assert(!in_array('Jasa servis',$merch->products()['categories'],true),'category names cannot leak across tenants');$store->scope=$context();
$prior=$grants->p;$grants->p=[];$reject(fn()=>$merch->createCategory('category-denied',['name'=>'Injected'],'category-correlation'),'category creation requires current catalog permission');$grants->p=$prior;
$input=['sku'=>'SERVICE-01','name'=>'Jasa servis','category'=>'Jasa servis','price'=>'12.00','opening_quantity'=>'0','stock_mode'=>'unlimited'];
$unlimited=$merch->saveProduct('unlimited-product-create',$input,'stock-correlation');
$assert($unlimited['stock_mode']==='unlimited'&&$unlimited['available_quantity']==='0','unlimited has explicit policy without fabricated stock');
$assert((int)$db->table('oneqay_pos_inventory_baselines')->where('tenant_id','tenant-a')->where('product_id','service-01')->value('opening_quantity')===0,'unlimited baseline preserves zero physical quantity');
$reject(fn()=>$merch->saveProduct('invalid-unlimited-opening',array_replace($input,['sku'=>'INVALID-SERVICE','opening_quantity'=>'5']),'stock-correlation'),'unlimited cannot fabricate opening stock');
$reject(fn()=>$merch->saveProduct('invalid-stock-mode',array_replace($input,['sku'=>'INVALID-MODE','stock_mode'=>'infinite-injection']),'stock-correlation'),'unknown stock modes rejected');
$unchanged=$merch->saveProduct('legacy-edit-unlimited',array_replace(array_diff_key($input,['stock_mode'=>true]),['product_id'=>'service-01','version'=>1]),'stock-correlation');
$assert($unchanged['stock_mode']==='unlimited','legacy edit without mode preserves current mode');
$cashierRepo=new App\Infrastructure\Pos\LaravelPosCashierWorkspaceRepository($db,true,'ci',true,true);
$execution=App\Application\Pos\PosExecutionContext::fromVerified($context());
// No actual user register is opened: this is an isolated synthetic row in the disposable database.
$shift=str_repeat('e',32);$db->table('oneqay_pos_shifts')->insert(['tenant_id'=>'tenant-a','shift_id'=>$shift,'operation_id'=>'stock-test-shift','payload_fingerprint'=>str_repeat('1',64),'actor_identity_id'=>'identity-a','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','active_slot'=>1,'correlation_id'=>'stock-correlation','opened_at_unix'=>time()]);
$items=$cashierRepo->snapshot($execution)->catalogItems();$found=array_values(array_filter($items,fn($p)=>$p->productId()==='service-01'));
$assert(count($found)===1&&!$found[0]->tracksStock()&&$found[0]->availableQuantity()===0,'unlimited appears in cashier catalog at zero stock');
$sales=new App\Infrastructure\Pos\LaravelDurablePosSaleRepository($db,true,'ci',true,true);
$command=fn($operation,$product,$quantity,$currency='USD',$scale=2)=>new App\Application\Pos\SaleCommand($operation,App\Domain\Pos\Cart::fromLines([new App\Domain\Pos\CartLine(App\Domain\Pos\ProductId::fromString($product),$quantity)]),App\Domain\Pos\TenderCategory::CASH,App\Domain\Pos\Money::fromAtomicUnits(100000,$currency,$scale),'stock-correlation');
$cmd=$command('unlimited-sale-one','service-01',5);$sale=$sales->complete($execution,$cmd,time());$sales->complete($execution,$cmd,time());
$assert((int)$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id','tenant-a')->where('product_id','service-01')->value('available_quantity')===0,'unlimited sale and replay never decrement physical stock');
$assert(!(bool)$db->table('oneqay_pos_sale_lines')->where('sale_id',$sale->saleId())->value('stock_deducted'),'immutable sale line records no stock deduction');
$modeInput=array_replace($input,['product_id'=>'service-01','stock_mode'=>'tracked','version'=>2]);$merch->saveProduct('service-switch-tracked',$modeInput,'stock-correlation');
$sales->voidCompletedSale($execution,new App\Application\Pos\SaleVoidCommand('unlimited-void-after-mode-change',$sale->saleId()),'stock-void-correlation',time());
$assert((int)$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id','tenant-a')->where('product_id','service-01')->value('available_quantity')===0,'void of formerly unlimited sale cannot mint tracked stock');
try{$sales->complete($execution,$command('tracked-empty-sale','service-01',1),time());$assert(false,'zero tracked stock sold');}catch(App\Application\Pos\PosTransactionViolation){$assert(true,'tracked products still reject insufficient stock');}
$tracked=$sales->complete($execution,$command('tracked-sale-before-mode-change','coffee-01',2,'IDR',0),time());
$merch->saveProduct('coffee-switch-unlimited',array_replace($row,['sku'=>'COFFEE-01','product_id'=>'coffee-01','opening_quantity'=>'0','version'=>2,'stock_mode'=>'unlimited']),'stock-correlation');
$sales->voidCompletedSale($execution,new App\Application\Pos\SaleVoidCommand('tracked-void-after-mode-change',$tracked->saleId()),'stock-void-correlation',time());
$assert((int)$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id','tenant-a')->where('product_id','coffee-01')->value('available_quantity')===10,'void restores original tracked deduction even after unlimited switch');
$doc=$reports->read('products',['q'=>'COFFEE-01']);$assert(in_array('stock_mode',$doc['headers'],true)||in_array('Stock Mode',$doc['headers'],true)||in_array('Stock mode',$doc['headers'],true),'Excel report exposes stock mode');
$assert($doc['pdf_rows'][0][5]==='Unlimited','PDF clearly labels unlimited instead of showing a false sellable zero');
$oldHeaders=array_slice(App\Infrastructure\Business\SpreadsheetDocument::IMPORT_HEADERS,0,-1);$csv=implode(',',$oldHeaders)."\n".implode(',',array_map(fn($h)=>is_bool($row[$h]??'')?(($row[$h]??false)?'1':'0'):($row[$h]??''),$oldHeaders));
$assert(App\Infrastructure\Business\SpreadsheetDocument::import($csv,'csv')[0]['stock_mode']==='tracked','legacy imports remain tracked by default');
echo 'CATALOG_STOCK_MODES_'.$engine.'_PASS assertions='.$n.PHP_EOL;
