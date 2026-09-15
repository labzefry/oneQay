<?php

declare(strict_types=1);

use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\PosExecutionContext;
use App\Application\Pos\PosTransactionViolation;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Pos\LaravelPosProductSalesPerformanceWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('p', 32));
foreach (['APP_ENV' => 'testing','APP_KEY' => $testKey,'APP_DEBUG' => 'false','ONEQAY_RUNTIME_CLASS' => 'ci'] as $key => $value) { putenv($key.'='.$value); $_ENV[$key] = $value; $_SERVER[$key] = $value; }
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $console */ $console = $app->make(Kernel::class); $console->bootstrap();
$assert = static function (bool $condition, string $message): void { if (! $condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); } };
$databasePath = sys_get_temp_dir().'/oneqay-s166-product-performance-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint166 disposable database could not be created.');
$app['config']->set('database.default', 's166_product_performance');
$app['config']->set('database.connections.s166_product_performance', ['driver'=>'sqlite','database'=>$databasePath,'prefix'=>'','foreign_key_constraints'=>false]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */ $databaseManager = $app->make('db'); $databaseManager->purge('s166_product_performance'); $connection = $databaseManager->connection('s166_product_performance');
$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, total_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_lines (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, line_no INTEGER NOT NULL, product_id TEXT NOT NULL, quantity INTEGER NOT NULL, unit_price_atomic INTEGER NOT NULL, line_total_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, void_id TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, refund_id TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_catalog_items (tenant_id TEXT NOT NULL, outlet_id TEXT NOT NULL, product_id TEXT NOT NULL, display_name TEXT NOT NULL, active INTEGER NOT NULL)');
$connection->table('oneqay_pos_sale_catalog_items')->insert([
 ['tenant_id'=>'tenant-a','outlet_id'=>'outlet-a','product_id'=>'product-rice','display_name'=>'Rice','active'=>1],
 ['tenant_id'=>'tenant-a','outlet_id'=>'outlet-a','product_id'=>'product-tea','display_name'=>'Archived Tea','active'=>0],
 ['tenant_id'=>'tenant-a','outlet_id'=>'outlet-b','product_id'=>'product-rice','display_name'=>'Other Outlet Rice','active'=>1],
]);
$connection->table('oneqay_pos_sales')->insert([
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-rice-live','organization_id'=>'org-a','outlet_id'=>'outlet-a','total_atomic'=>2000,'currency'=>'IDR','currency_scale'=>0],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-rice-void-refund','organization_id'=>'org-a','outlet_id'=>'outlet-a','total_atomic'=>3000,'currency'=>'IDR','currency_scale'=>0],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-rice-usd','organization_id'=>'org-a','outlet_id'=>'outlet-a','total_atomic'=>1234,'currency'=>'USD','currency_scale'=>2],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-tea-live','organization_id'=>'org-a','outlet_id'=>'outlet-a','total_atomic'=>2000,'currency'=>'IDR','currency_scale'=>0],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-foreign-outlet','organization_id'=>'org-a','outlet_id'=>'outlet-b','total_atomic'=>99000,'currency'=>'IDR','currency_scale'=>0],
 ['tenant_id'=>'tenant-b','sale_id'=>'sale-foreign-tenant','organization_id'=>'org-b','outlet_id'=>'outlet-a','total_atomic'=>88000,'currency'=>'IDR','currency_scale'=>0],
]);
$connection->table('oneqay_pos_sale_lines')->insert([
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-rice-live','line_no'=>1,'product_id'=>'product-rice','quantity'=>2,'unit_price_atomic'=>1000,'line_total_atomic'=>2000,'currency'=>'IDR','currency_scale'=>0],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-rice-void-refund','line_no'=>1,'product_id'=>'product-rice','quantity'=>3,'unit_price_atomic'=>1000,'line_total_atomic'=>3000,'currency'=>'IDR','currency_scale'=>0],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-rice-usd','line_no'=>1,'product_id'=>'product-rice','quantity'=>1,'unit_price_atomic'=>1234,'line_total_atomic'=>1234,'currency'=>'USD','currency_scale'=>2],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-tea-live','line_no'=>1,'product_id'=>'product-tea','quantity'=>4,'unit_price_atomic'=>500,'line_total_atomic'=>2000,'currency'=>'IDR','currency_scale'=>0],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-foreign-outlet','line_no'=>1,'product_id'=>'product-rice','quantity'=>99,'unit_price_atomic'=>1000,'line_total_atomic'=>99000,'currency'=>'IDR','currency_scale'=>0],
 ['tenant_id'=>'tenant-b','sale_id'=>'sale-foreign-tenant','line_no'=>1,'product_id'=>'product-rice','quantity'=>88,'unit_price_atomic'=>1000,'line_total_atomic'=>88000,'currency'=>'IDR','currency_scale'=>0],
]);
$connection->table('oneqay_pos_sale_voids')->insert([['tenant_id'=>'tenant-a','sale_id'=>'sale-rice-void-refund','void_id'=>'void-rice']]);
$connection->table('oneqay_pos_sale_cash_refunds')->insert([['tenant_id'=>'tenant-a','sale_id'=>'sale-rice-void-refund','refund_id'=>'refund-rice']]);
$verified = new VerifiedOrganizationalContext(PlatformIdentityId::fromString('identity-a'),TenantId::fromString('tenant-a'),OrganizationId::fromString('org-a'),OutletId::fromString('outlet-a'),DeviceId::fromString('device-a'));
$context = PosExecutionContext::fromVerified($verified);
$repository = new LaravelPosProductSalesPerformanceWorkspaceRepository($connection, true, 'ci', true, true);
$snapshot = $repository->read($context);
$assert($snapshot->tenantId()==='tenant-a' && $snapshot->organizationId()==='org-a' && $snapshot->outletId()==='outlet-a', 'Sprint166 scope changed.');
$assert($snapshot->truncated()===false, 'Sprint166 small dataset must not be truncated.');
$rows=$snapshot->rows(); $assert(count($rows)===3, 'Sprint166 must preserve product/currency buckets without foreign-scope leakage.');
$byKey=[]; foreach($rows as $row){$byKey[$row['product_id'].'|'.$row['currency'].'|'.$row['scale']]=$row;}
$riceIdr=$byKey['product-rice|IDR|0']??null; $assert(is_array($riceIdr), 'Sprint166 IDR rice bucket is missing.');
$assert($riceIdr['gross_quantity']==='5' && $riceIdr['voided_quantity']==='3' && $riceIdr['net_quantity']==='2', 'Sprint166 product quantity arithmetic is incorrect.');
$assert($riceIdr['gross_atomic']==='5000' && $riceIdr['voided_atomic']==='3000' && $riceIdr['net_atomic']==='2000', 'Sprint166 product value arithmetic is incorrect.');
$riceUsd=$byKey['product-rice|USD|2']??null; $assert(is_array($riceUsd)&&$riceUsd['net_atomic']==='1234', 'Sprint166 must preserve historical currency/scale boundaries.');
$tea=$byKey['product-tea|IDR|0']??null; $assert(is_array($tea)&&$tea['display_name']==='Archived Tea'&&$tea['net_quantity']==='4', 'Sprint166 inactive catalog history must remain visible.');
$assert(strpos(file_get_contents(__DIR__.'/../app/Infrastructure/Pos/LaravelPosProductSalesPerformanceWorkspaceRepository.php'), 'oneqay_pos_sale_cash_refunds')===false, 'Sprint166 must not subtract CASH refund a second time.');
for($i=1;$i<=248;$i++){
 $productId=sprintf('bulk-product-%03d',$i); $saleId=sprintf('bulk-sale-%03d',$i);
 $connection->table('oneqay_pos_sale_catalog_items')->insert(['tenant_id'=>'tenant-a','outlet_id'=>'outlet-a','product_id'=>$productId,'display_name'=>'Bulk Product '.$i,'active'=>1]);
 $connection->table('oneqay_pos_sales')->insert(['tenant_id'=>'tenant-a','sale_id'=>$saleId,'organization_id'=>'org-a','outlet_id'=>'outlet-a','total_atomic'=>1,'currency'=>'IDR','currency_scale'=>0]);
 $connection->table('oneqay_pos_sale_lines')->insert(['tenant_id'=>'tenant-a','sale_id'=>$saleId,'line_no'=>1,'product_id'=>$productId,'quantity'=>1,'unit_price_atomic'=>1,'line_total_atomic'=>1,'currency'=>'IDR','currency_scale'=>0]);
}
$bounded=$repository->read($context); $assert(count($bounded->rows())===250&&$bounded->truncated()===true, 'Sprint166 bounded read/truncation contract changed.');
$connection->table('oneqay_pos_sales')->insert(['tenant_id'=>'tenant-a','sale_id'=>'sale-orphan','organization_id'=>'org-a','outlet_id'=>'outlet-a','total_atomic'=>1000000,'currency'=>'IDR','currency_scale'=>0]);
$connection->table('oneqay_pos_sale_lines')->insert(['tenant_id'=>'tenant-a','sale_id'=>'sale-orphan','line_no'=>1,'product_id'=>'missing-product','quantity'=>1000,'unit_price_atomic'=>1000,'line_total_atomic'=>1000000,'currency'=>'IDR','currency_scale'=>0]);
try{$repository->read($context);$assert(false,'Sprint166 accepted orphaned sale-line product evidence.');}catch(PosTransactionViolation){}
foreach([[false,'ci',true,true],[true,'production',true,true],[true,'ci',false,true],[true,'ci',true,false]] as [$persistence,$runtime,$reporting,$feature]){
 try{(new LaravelPosProductSalesPerformanceWorkspaceRepository($connection,$persistence,$runtime,$reporting,$feature))->read($context);$assert(false,'Sprint166 accepted a disallowed delivery state.');}catch(PosTransactionViolation){}
}
$databaseManager->disconnect('s166_product_performance'); $databaseManager->purge('s166_product_performance'); @unlink($databasePath);
fwrite(STDOUT, "Sprint166 POS product sales performance regression passed.\n");
