<?php

declare(strict_types=1);

use App\Application\Authorization\PosPermission;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Pos\CompleteSale;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\SaleCommand;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Pos\Cart;
use App\Domain\Pos\CartLine;
use App\Domain\Pos\Money;
use App\Domain\Pos\ProductId;
use App\Domain\Pos\TenderCategory;
use App\Domain\Tenancy\TenantId;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_ENV'=>'testing',
    'APP_KEY'=>'base64:'.base64_encode(str_repeat('s', 32)),
    'APP_DEBUG'=>'false',
    'APP_URL'=>'http://localhost',
    'ONEQAY_RUNTIME_CLASS'=>'ci',
    'ONEQAY_PERSISTENCE_ENABLED'=>'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED'=>'true',
    'ONEQAY_POS_SALE_COMPLETION_ENABLED'=>'true',
    'SESSION_DRIVER'=>'array',
    'CACHE_STORE'=>'array',
] as $key=>$value) {
    putenv($key.'='.$value);
    $_ENV[$key]=$value;
    $_SERVER[$key]=$value;
}

$app=require __DIR__.'/../bootstrap/app.php';
$app->instance('request', Request::create('/'));
$app->make(Kernel::class)->bootstrap();

$assert=static function(bool $ok,string $case):void {
    if(!$ok){throw new RuntimeException('Sprint49 active-shift regression failed: '.$case);}
};

$dir=sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s49-'.getmypid();
@mkdir($dir,0700,true);
$db=$dir.DIRECTORY_SEPARATOR.'pos.sqlite';
$assert(touch($db),'sqlite create');

$app['config']->set('database.default','s49');
$app['config']->set('database.connections.s49',[
    'driver'=>'sqlite','database'=>$db,'prefix'=>'','foreign_key_constraints'=>true,
]);
$app['config']->set('database.oneqay_persistence_enabled',true);
$app['config']->set('oneqay.runtime_class','ci');
$app['config']->set('oneqay.pos_sale_completion.enabled',true);

$manager=$app->make('db');
$manager->purge('s49');
$manager->setDefaultConnection('s49');
$connection=$manager->connection('s49');
$connection->getPdo();

$migrations=array_values(array_filter(scandir(__DIR__.'/../database/migrations')?:[],fn(string $f):bool=>str_ends_with($f,'.php')));
sort($migrations);
$assert(count($migrations)===18,'migration count');
foreach(range(1,18) as $i){
    $prefix=sprintf('0000_00_00_%06d_',$i);
    $assert(count(array_filter($migrations,fn(string $f):bool=>str_starts_with($f,$prefix)))===1,'migration '.$i);
}
foreach($migrations as $migration){(require __DIR__.'/../database/migrations/'.$migration)->up();}

foreach(['tenant-alpha','tenant-beta'] as $tenant){$connection->table('oneqay_tenants')->insert(['id'=>$tenant]);}
foreach([['tenant-alpha','cashier-alpha'],['tenant-beta','cashier-beta']] as [$tenant,$id]){
    $connection->table('oneqay_identities')->insert(['tenant_id'=>$tenant,'id'=>$id]);
}
foreach([['tenant-alpha','organization-alpha'],['tenant-beta','organization-beta']] as [$tenant,$org]){
    $connection->table('oneqay_organizations')->insert(['tenant_id'=>$tenant,'id'=>$org]);
}
foreach([['tenant-alpha','cashier-alpha','organization-alpha'],['tenant-beta','cashier-beta','organization-beta']] as [$tenant,$id,$org]){
    $connection->table('oneqay_identity_organizations')->insert(['tenant_id'=>$tenant,'identity_id'=>$id,'organization_id'=>$org]);
}
foreach([
    ['tenant-alpha','outlet-alpha','organization-alpha'],
    ['tenant-alpha','outlet-alt','organization-alpha'],
    ['tenant-beta','outlet-beta','organization-beta'],
] as [$tenant,$outlet,$org]){
    $connection->table('oneqay_outlets')->insert(['tenant_id'=>$tenant,'id'=>$outlet,'organization_id'=>$org]);
}
foreach([
    ['tenant-alpha','device-alpha','organization-alpha','outlet-alpha'],
    ['tenant-alpha','device-other','organization-alpha','outlet-alpha'],
    ['tenant-alpha','device-alt','organization-alpha','outlet-alt'],
    ['tenant-beta','device-beta','organization-beta','outlet-beta'],
] as [$tenant,$device,$org,$outlet]){
    $connection->table('oneqay_devices')->insert(['tenant_id'=>$tenant,'id'=>$device,'organization_id'=>$org,'outlet_id'=>$outlet]);
}

$connection->table('oneqay_roles')->insert([['tenant_id'=>'tenant-alpha','id'=>'cashier-role']]);
$connection->table('oneqay_role_permissions')->insert([
    ['tenant_id'=>'tenant-alpha','role_id'=>'cashier-role','permission_id'=>PosPermission::COMPLETE_SALE],
]);
$connection->table('oneqay_outlet_role_assignments')->insert([
    ['tenant_id'=>'tenant-alpha','identity_id'=>'cashier-alpha','organization_id'=>'organization-alpha','outlet_id'=>'outlet-alpha','role_id'=>'cashier-role'],
]);
$connection->table('oneqay_pos_sale_catalog_items')->insert([
    'tenant_id'=>'tenant-alpha','outlet_id'=>'outlet-alpha','product_id'=>'product-a',
    'display_name'=>'Product A','unit_price_atomic'=>2500,'currency'=>'IDR','currency_scale'=>0,
    'available_quantity'=>10,'active'=>true,
]);

$app->forgetScopedInstances();
$contexts=$app->make(OrganizationalContextStore::class);
$contexts->setVerified(new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('cashier-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
    OutletId::fromString('outlet-alpha'),
    DeviceId::fromString('device-alpha'),
));
$sales=$app->make(CompleteSale::class);

$make=static fn(string $op,int $qty=1):SaleCommand=>new SaleCommand(
    $op,
    Cart::fromLines([new CartLine(ProductId::fromString('product-a'),$qty)]),
    TenderCategory::CASH,
    Money::fromAtomicUnits(3000*$qty,'IDR',0),
    'correlation-'.$op,
);

$deny=static function(string $op) use($sales,$connection,$assert,$make):void{
    $salesBefore=$connection->table('oneqay_pos_sales')->count();
    $qtyBefore=(int)$connection->table('oneqay_pos_sale_catalog_items')->where('product_id','product-a')->value('available_quantity');
    try{$sales->complete($make($op));$assert(false,$op.' accepted');}catch(PosTransactionViolation){}
    $assert($connection->table('oneqay_pos_sales')->count()===$salesBefore,$op.' sale mutation');
    $assert((int)$connection->table('oneqay_pos_sale_catalog_items')->where('product_id','product-a')->value('available_quantity')===$qtyBefore,$op.' stock mutation');
};

$deny('no-shift');

$insertShift=static function(array $row) use($connection):void{
    $connection->table('oneqay_pos_shifts')->insert(array_merge([
        'payload_fingerprint'=>str_repeat('a',64),
        'active_slot'=>1,
        'opened_at_unix'=>1000,
    ],$row));
};

$insertShift([
    'tenant_id'=>'tenant-beta','shift_id'=>str_repeat('b',32),'operation_id'=>'shift-beta',
    'actor_identity_id'=>'cashier-beta','organization_id'=>'organization-beta',
    'outlet_id'=>'outlet-beta','device_id'=>'device-beta','correlation_id'=>'corr-beta',
]);
$deny('wrong-tenant');

$insertShift([
    'tenant_id'=>'tenant-alpha','shift_id'=>str_repeat('c',32),'operation_id'=>'shift-alt',
    'actor_identity_id'=>'cashier-alpha','organization_id'=>'organization-alpha',
    'outlet_id'=>'outlet-alt','device_id'=>'device-alt','correlation_id'=>'corr-alt',
]);
$deny('wrong-outlet');

$insertShift([
    'tenant_id'=>'tenant-alpha','shift_id'=>str_repeat('d',32),'operation_id'=>'shift-other-device',
    'actor_identity_id'=>'cashier-alpha','organization_id'=>'organization-alpha',
    'outlet_id'=>'outlet-alpha','device_id'=>'device-other','correlation_id'=>'corr-other',
]);
$deny('wrong-device');

$insertShift([
    'tenant_id'=>'tenant-alpha','shift_id'=>str_repeat('e',32),'operation_id'=>'shift-exact',
    'actor_identity_id'=>'cashier-alpha','organization_id'=>'organization-alpha',
    'outlet_id'=>'outlet-alpha','device_id'=>'device-alpha','correlation_id'=>'corr-exact',
]);

$before=(array)$connection->table('oneqay_pos_shifts')->where('operation_id','shift-exact')->first();
$command=$make('sale-success',2);
$receipt=$sales->complete($command);
$assert($receipt->total()->atomicUnits()===5000,'sale total');
$assert((int)$connection->table('oneqay_pos_sale_catalog_items')->where('product_id','product-a')->value('available_quantity')===8,'stock decrement');
$after=(array)$connection->table('oneqay_pos_shifts')->where('operation_id','shift-exact')->first();
$assert($after===$before,'sale mutated shift');

$connection->table('oneqay_pos_shifts')->where('operation_id','shift-exact')->update(['active_slot'=>null]);
$replay=$sales->complete($command);
$assert($replay->saleId()===$receipt->saleId(),'replay after inactive shift');
$assert((int)$connection->table('oneqay_pos_sale_catalog_items')->where('product_id','product-a')->value('available_quantity')===8,'replay stock');

try{$sales->complete($make('sale-success',1));$assert(false,'conflicting replay accepted');}catch(PosTransactionViolation){}
$deny('fresh-after-inactive');

$manager->disconnect('s49');
$manager->purge('s49');
@unlink($db);
@rmdir($dir);

echo "Sprint49 JRN-006 active-shift sale precondition regression passed.\n";
