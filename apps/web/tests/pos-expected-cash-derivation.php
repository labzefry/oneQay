<?php

declare(strict_types=1);

use App\Application\Pos\DeriveExpectedCash;
use App\Application\Pos\PosTransactionViolation;
use App\Application\Pos\ShiftClosingCashResult;
use App\Domain\Pos\Money;
use App\Infrastructure\Pos\LaravelExpectedCashRepository;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_ENV'=>'testing',
    'APP_KEY'=>'base64:'.base64_encode(str_repeat('e',32)),
    'APP_DEBUG'=>'false',
    'APP_URL'=>'http://localhost',
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
    if(!$ok){throw new RuntimeException('Sprint60 expected-cash regression failed: '.$case);}
};
$expectViolation=static function(callable $call,string $case) use($assert):void {
    try{$call();$assert(false,$case.' accepted');}catch(PosTransactionViolation){}
};

$dir=sys_get_temp_dir().DIRECTORY_SEPARATOR.'oneqay-s60-expected-'.getmypid();
@mkdir($dir,0700,true);
$db=$dir.DIRECTORY_SEPARATOR.'pos.sqlite';
$assert(touch($db),'sqlite create');
$app['config']->set('database.default','s60-expected');
$app['config']->set('database.connections.s60-expected',[
    'driver'=>'sqlite','database'=>$db,'prefix'=>'','foreign_key_constraints'=>true,
]);
$manager=$app->make('db');
$manager->purge('s60-expected');
$manager->setDefaultConnection('s60-expected');
$connection=$manager->connection('s60-expected');
$connection->getPdo();
$connection->statement('PRAGMA foreign_keys = ON');

$migrations=array_values(array_filter(scandir(__DIR__.'/../database/migrations')?:[],fn(string $f):bool=>str_ends_with($f,'.php')));
sort($migrations);
$assert(count($migrations)===24,'migration count');
foreach(range(1,24) as $i){
    $prefix=sprintf('0000_00_00_%06d_',$i);
    $assert(count(array_filter($migrations,fn(string $f):bool=>str_starts_with($f,$prefix)))===1,'migration '.$i);
}
foreach($migrations as $migration){(require __DIR__.'/../database/migrations/'.$migration)->up();}

foreach(['tenant-alpha','tenant-beta'] as $tenant){$connection->table('oneqay_tenants')->insert(['id'=>$tenant]);}
foreach([
    ['tenant-alpha','cashier-alpha'],['tenant-beta','cashier-beta'],
] as [$tenant,$id]){$connection->table('oneqay_identities')->insert(['tenant_id'=>$tenant,'id'=>$id]);}
foreach([
    ['tenant-alpha','organization-alpha'],['tenant-alpha','organization-other'],['tenant-beta','organization-beta'],
] as [$tenant,$org]){$connection->table('oneqay_organizations')->insert(['tenant_id'=>$tenant,'id'=>$org]);}
foreach([
    ['tenant-alpha','outlet-alpha','organization-alpha'],['tenant-alpha','outlet-other','organization-other'],['tenant-beta','outlet-beta','organization-beta'],
] as [$tenant,$outlet,$org]){$connection->table('oneqay_outlets')->insert(['tenant_id'=>$tenant,'id'=>$outlet,'organization_id'=>$org]);}
foreach([
    ['tenant-alpha','device-alpha','organization-alpha','outlet-alpha'],
    ['tenant-alpha','device-other','organization-other','outlet-other'],
    ['tenant-beta','device-beta','organization-beta','outlet-beta'],
] as [$tenant,$device,$org,$outlet]){$connection->table('oneqay_devices')->insert(['tenant_id'=>$tenant,'id'=>$device,'organization_id'=>$org,'outlet_id'=>$outlet]);}

$repository=new LaravelExpectedCashRepository($connection);
$derive=new DeriveExpectedCash($repository);
$method=new ReflectionMethod(DeriveExpectedCash::class,'derive');
$params=$method->getParameters();
$assert(count($params)===1,'derive parameter count');
$assert(($params[0]->getType()?->__toString()??'')===ShiftClosingCashResult::class,'derive accepts canonical closing result only');

$makeScenario=static function(
    string $name,
    int $openingAtomic=1000,
    int $openingAt=100,
    int $closingAt=200,
    string $openingCurrency='IDR',
    int $openingScale=0,
    ?string $closingCurrency=null,
    ?int $closingScale=null,
    string $closingOrganization='organization-alpha',
    string $closingOutlet='outlet-alpha',
    string $closingDevice='device-alpha',
) use($connection):array {
    $tenant='tenant-alpha';
    $organization='organization-alpha';
    $outlet='outlet-alpha';
    $device='device-alpha';
    $shiftId=substr(hash('sha256','shift-'.$name),0,32);
    $openingId='open-'.substr(hash('sha256','open-'.$name),0,24);
    $closingId='close-'.substr(hash('sha256','close-'.$name),0,23);
    $closingCurrency??=$openingCurrency;
    $closingScale??=$openingScale;

    $connection->table('oneqay_pos_shifts')->insert([
        'tenant_id'=>$tenant,'shift_id'=>$shiftId,'operation_id'=>'shift-'.$name,
        'payload_fingerprint'=>hash('sha256','shift-'.$name),'actor_identity_id'=>'cashier-alpha',
        'organization_id'=>$organization,'outlet_id'=>$outlet,'device_id'=>$device,
        'active_slot'=>null,'correlation_id'=>'corr-shift-'.$name,'opened_at_unix'=>90,
    ]);
    $connection->table('oneqay_pos_shift_opening_cash_evidence')->insert([
        'tenant_id'=>$tenant,'evidence_id'=>$openingId,'operation_id'=>'open-'.$name,
        'payload_fingerprint'=>hash('sha256','open-'.$name),'shift_id'=>$shiftId,
        'actor_identity_id'=>'cashier-alpha','organization_id'=>$organization,'outlet_id'=>$outlet,'device_id'=>$device,
        'opening_cash_atomic'=>$openingAtomic,'currency'=>$openingCurrency,'currency_scale'=>$openingScale,
        'evidence_mode'=>'OPERATOR_OBSERVED_OPENING_CASH','correlation_id'=>'corr-open-'.$name,'recorded_at_unix'=>$openingAt,
    ]);
    $connection->table('oneqay_pos_shift_closing_cash_evidence')->insert([
        'tenant_id'=>$tenant,'evidence_id'=>$closingId,'operation_id'=>'close-'.$name,
        'payload_fingerprint'=>hash('sha256','close-'.$name),'shift_id'=>$shiftId,'opening_cash_evidence_id'=>$openingId,
        'actor_identity_id'=>'cashier-alpha','organization_id'=>$closingOrganization,'outlet_id'=>$closingOutlet,'device_id'=>$closingDevice,
        'closing_cash_atomic'=>$openingAtomic,'currency'=>$closingCurrency,'currency_scale'=>$closingScale,
        'evidence_mode'=>'OPERATOR_OBSERVED_CLOSING_CASH','correlation_id'=>'corr-close-'.$name,'recorded_at_unix'=>$closingAt,
    ]);
    $token=new ShiftClosingCashResult(
        $closingId,$openingId,$shiftId,'close-'.$name,$tenant,$closingOutlet,$closingDevice,
        Money::fromAtomicUnits($openingAtomic,$closingCurrency,$closingScale),
        'OPERATOR_OBSERVED_CLOSING_CASH','corr-close-'.$name,$closingAt,
    );
    return compact('tenant','organization','outlet','device','shiftId','openingId','closingId','openingAt','closingAt','token');
};

$insertSale=static function(array $scenario,string $name,int $applied,int $at,string $tender='CASH',?string $shiftId=null,string $currency='IDR',int $scale=0) use($connection):string {
    $saleId='sale-'.substr(hash('sha256',$scenario['shiftId'].'|'.$name),0,24);
    $connection->table('oneqay_pos_sales')->insert([
        'tenant_id'=>$scenario['tenant'],'sale_id'=>$saleId,'operation_id'=>'sale-op-'.$name.'-'.$scenario['shiftId'],
        'payload_fingerprint'=>hash('sha256','sale-'.$name.'-'.$scenario['shiftId']),'actor_identity_id'=>'cashier-alpha',
        'organization_id'=>$scenario['organization'],'outlet_id'=>$scenario['outlet'],'device_id'=>$scenario['device'],
        'shift_id'=>$shiftId===null?$scenario['shiftId']:$shiftId,'total_atomic'=>$applied,'currency'=>$currency,'currency_scale'=>$scale,
        'tender_category'=>$tender,'evidence_mode'=>$tender==='CASH'?'CASH_COUNTED':'OPERATOR_RECORDED',
        'applied_atomic'=>$applied,'change_atomic'=>0,'correlation_id'=>'corr-sale-'.$name,'completed_at_unix'=>$at,
    ]);
    return $saleId;
};
$insertLegacyNullSale=static function(array $scenario,string $name,int $applied,int $at) use($connection):string {
    $saleId='sale-'.substr(hash('sha256','legacy|'.$scenario['shiftId'].'|'.$name),0,24);
    $connection->table('oneqay_pos_sales')->insert([
        'tenant_id'=>$scenario['tenant'],'sale_id'=>$saleId,'operation_id'=>'legacy-op-'.$name.'-'.$scenario['shiftId'],
        'payload_fingerprint'=>hash('sha256','legacy-'.$name.'-'.$scenario['shiftId']),'actor_identity_id'=>'cashier-alpha',
        'organization_id'=>$scenario['organization'],'outlet_id'=>$scenario['outlet'],'device_id'=>$scenario['device'],
        'shift_id'=>null,'total_atomic'=>$applied,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'CASH_COUNTED',
        'applied_atomic'=>$applied,'change_atomic'=>0,'correlation_id'=>'corr-legacy-'.$name,'completed_at_unix'=>$at,
    ]);
    return $saleId;
};
$insertVoid=static function(array $scenario,string $saleId,string $name,int $amount,int $at=160,string $currency='IDR',int $scale=0) use($connection):string {
    $voidId='void-'.substr(hash('sha256',$saleId.'|'.$name),0,24);
    $connection->table('oneqay_pos_sale_voids')->insert([
        'tenant_id'=>$scenario['tenant'],'void_id'=>$voidId,'operation_id'=>'void-op-'.$name.'-'.$scenario['shiftId'],
        'payload_fingerprint'=>hash('sha256','void-'.$name.'-'.$scenario['shiftId']),'sale_id'=>$saleId,'actor_identity_id'=>'cashier-alpha',
        'organization_id'=>$scenario['organization'],'outlet_id'=>$scenario['outlet'],'device_id'=>$scenario['device'],
        'reversed_atomic'=>$amount,'currency'=>$currency,'currency_scale'=>$scale,'tender_category'=>'CASH','evidence_mode'=>'FULL_SALE_VOID',
        'correlation_id'=>'corr-void-'.$name,'voided_at_unix'=>$at,
    ]);
    return $voidId;
};
$insertRefund=static function(array $scenario,string $saleId,string $voidId,string $name,int $amount,int $at,string $currency='IDR',int $scale=0) use($connection):string {
    $refundId='refund-'.substr(hash('sha256',$saleId.'|'.$name),0,22);
    $connection->table('oneqay_pos_sale_cash_refunds')->insert([
        'tenant_id'=>$scenario['tenant'],'refund_id'=>$refundId,'operation_id'=>'refund-op-'.$name.'-'.$scenario['shiftId'],
        'payload_fingerprint'=>hash('sha256','refund-'.$name.'-'.$scenario['shiftId']),'sale_id'=>$saleId,'void_id'=>$voidId,
        'actor_identity_id'=>'cashier-alpha','organization_id'=>$scenario['organization'],'outlet_id'=>$scenario['outlet'],'device_id'=>$scenario['device'],
        'refunded_atomic'=>$amount,'currency'=>$currency,'currency_scale'=>$scale,'tender_category'=>'CASH','evidence_mode'=>'FULL_CASH_REFUND',
        'correlation_id'=>'corr-refund-'.$name,'refunded_at_unix'=>$at,
    ]);
    return $refundId;
};

$countEvidence=static function() use($connection):array {
    $tables=['oneqay_pos_sales','oneqay_pos_sale_voids','oneqay_pos_sale_cash_refunds','oneqay_pos_shift_opening_cash_evidence','oneqay_pos_shift_closing_cash_evidence'];
    $out=[];foreach($tables as $table){$out[$table]=$connection->table($table)->count();}return $out;
};

$base=$makeScenario('base');
$before=$countEvidence();
$inside=true;$allSnapshotQueriesInside=true;$queryCount=0;
$connection->listen(static function($event) use($connection,&$inside,&$allSnapshotQueriesInside,&$queryCount):void {
    if(!$inside||!str_contains((string)$event->sql,'oneqay_pos_')){return;}
    $queryCount++;
    if($connection->transactionLevel()<1){$allSnapshotQueriesInside=false;}
});
$result=$derive->derive($base['token']);
$inside=false;
$assert($result->expectedCash()->atomicUnits()===1000,'opening-only amount');
$assert($result->tenantId()==='tenant-alpha'&&$result->organizationId()==='organization-alpha','result context');
$assert($result->openingCashEvidenceId()===$base['openingId']&&$result->closingCashEvidenceId()===$base['closingId'],'result evidence ids');
$assert($result->cutoffAtUnix()===200,'result cutoff');
$assert($queryCount>0&&$allSnapshotQueriesInside,'stable snapshot transaction');
$assert($before===$countEvidence(),'read-only row counts');
$repeat=$derive->derive($base['token']);
$assert($repeat->expectedCash()->canonicalFingerprintPart()===$result->expectedCash()->canonicalFingerprintPart(),'deterministic repeat');

$saleId=$insertSale($base,'cash',250,150);
$assert($derive->derive($base['token'])->expectedCash()->atomicUnits()===1250,'cash sale contributes once');
$voidId=$insertVoid($base,$saleId,'cash',250);
$assert($derive->derive($base['token'])->expectedCash()->atomicUnits()===1250,'void contributes zero');
$insertRefund($base,$saleId,$voidId,'cash',250,170);
$assert($derive->derive($base['token'])->expectedCash()->atomicUnits()===1000,'refund subtracts once');

$manual=$makeScenario('manual');
$insertSale($manual,'external',500,150,'MANUAL_EXTERNAL');
$assert($derive->derive($manual['token'])->expectedCash()->atomicUnits()===1000,'non-cash excluded');

$legacy=$makeScenario('legacy');
$insertLegacyNullSale($legacy,'cash-null',100,150);
$expectViolation(fn()=> $derive->derive($legacy['token']),'historical null binding');

$crossTenant=$makeScenario('cross-tenant');
$forgedTenant=new ShiftClosingCashResult(
    $crossTenant['closingId'],$crossTenant['openingId'],$crossTenant['shiftId'],'close-cross-tenant','tenant-beta','outlet-alpha','device-alpha',
    Money::fromAtomicUnits(1000,'IDR',0),'OPERATOR_OBSERVED_CLOSING_CASH','corr-close-cross-tenant',200,
);
$expectViolation(fn()=> $derive->derive($forgedTenant),'cross tenant');

$crossOrg=$makeScenario('cross-org',1000,100,200,'IDR',0,null,null,'organization-other','outlet-other','device-other');
$expectViolation(fn()=> $derive->derive($crossOrg['token']),'cross organization outlet device');

$moneyMismatch=$makeScenario('money-mismatch',1000,100,200,'IDR',0,'USD',0);
$expectViolation(fn()=> $derive->derive($moneyMismatch['token']),'opening closing currency mismatch');

$saleMoneyMismatch=$makeScenario('sale-money-mismatch');
$insertSale($saleMoneyMismatch,'usd',100,150,'CASH',null,'USD',0);
$expectViolation(fn()=> $derive->derive($saleMoneyMismatch['token']),'sale currency mismatch');

$sameTime=$makeScenario('same-time');
$insertSale($sameTime,'same',100,200);
$expectViolation(fn()=> $derive->derive($sameTime['token']),'same timestamp ambiguity');

$late=$makeScenario('late');
$insertSale($late,'late',100,201);
$expectViolation(fn()=> $derive->derive($late['token']),'late sale');

$malformed=$makeScenario('malformed');
$saleA=$insertSale($malformed,'a',100,140);
$saleB=$insertSale($malformed,'b',100,145);
$voidB=$insertVoid($malformed,$saleB,'b',100,155);
$insertRefund($malformed,$saleA,$voidB,'wrong-link',100,170);
$expectViolation(fn()=> $derive->derive($malformed['token']),'malformed sale void refund relationship');

$refundMismatch=$makeScenario('refund-mismatch');
$saleR=$insertSale($refundMismatch,'r',100,140);
$voidR=$insertVoid($refundMismatch,$saleR,'r',100,155);
$insertRefund($refundMismatch,$saleR,$voidR,'r',101,170);
$expectViolation(fn()=> $derive->derive($refundMismatch['token']),'would-be-negative malformed refund');

$overflow=$makeScenario('overflow',PHP_INT_MAX);
$insertSale($overflow,'one',1,150);
$expectViolation(fn()=> $derive->derive($overflow['token']),'arithmetic overflow');

$manager->disconnect('s60-expected');
$manager->purge('s60-expected');
@unlink($db);@rmdir($dir);

echo "Sprint60 JRN-010 expected-cash derivation regression passed.\n";
