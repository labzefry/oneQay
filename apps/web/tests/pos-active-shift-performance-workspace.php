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
use App\Infrastructure\Pos\LaravelPosActiveShiftPerformanceWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('q', 32));
foreach (['APP_ENV' => 'testing','APP_KEY' => $testKey,'APP_DEBUG' => 'false','ONEQAY_RUNTIME_CLASS' => 'ci'] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $console */
$console = $app->make(Kernel::class);
$console->bootstrap();

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$databasePath = sys_get_temp_dir().'/oneqay-s167-active-shift-performance-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint167 disposable database could not be created.');

$app['config']->set('database.default', 's167_active_shift_performance');
$app['config']->set('database.connections.s167_active_shift_performance', ['driver'=>'sqlite','database'=>$databasePath,'prefix'=>'','foreign_key_constraints'=>false]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s167_active_shift_performance');
$connection = $databaseManager->connection('s167_active_shift_performance');

$connection->statement('CREATE TABLE oneqay_pos_shifts (tenant_id TEXT NOT NULL, shift_id TEXT NOT NULL, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, active_slot INTEGER NULL, opened_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NULL, total_atomic INTEGER NOT NULL, applied_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, completed_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, reversed_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, refund_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, refunded_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL)');

$connection->table('oneqay_pos_shifts')->insert([
 ['tenant_id'=>'tenant-a','shift_id'=>'shift-active-a','actor_identity_id'=>'identity-opener','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','active_slot'=>1,'opened_at_unix'=>1000],
 ['tenant_id'=>'tenant-a','shift_id'=>'shift-other-device','actor_identity_id'=>'identity-other','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-b','active_slot'=>1,'opened_at_unix'=>1000],
]);
$connection->table('oneqay_pos_sales')->insert([
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-cash-live','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','shift_id'=>'shift-active-a','total_atomic'=>1000,'applied_atomic'=>1000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'CASH_COUNTED','completed_at_unix'=>1100],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-cash-refunded','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','shift_id'=>'shift-active-a','total_atomic'=>2000,'applied_atomic'=>2000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'CASH_COUNTED','completed_at_unix'=>1200],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-manual-live','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','shift_id'=>'shift-active-a','total_atomic'=>3000,'applied_atomic'=>3000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'MANUAL_EXTERNAL','evidence_mode'=>'OPERATOR_RECORDED','completed_at_unix'=>1300],
 ['tenant_id'=>'tenant-a','sale_id'=>'sale-other-device','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-b','shift_id'=>'shift-other-device','total_atomic'=>99000,'applied_atomic'=>99000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'CASH_COUNTED','completed_at_unix'=>1250],
]);
$connection->table('oneqay_pos_sale_voids')->insert(['tenant_id'=>'tenant-a','sale_id'=>'sale-cash-refunded','void_id'=>'void-cash-refunded','organization_id'=>'org-a','outlet_id'=>'outlet-a','reversed_atomic'=>2000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'FULL_SALE_VOID']);
$connection->table('oneqay_pos_sale_cash_refunds')->insert(['tenant_id'=>'tenant-a','sale_id'=>'sale-cash-refunded','refund_id'=>'refund-cash-refunded','void_id'=>'void-cash-refunded','organization_id'=>'org-a','outlet_id'=>'outlet-a','refunded_atomic'=>2000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'FULL_CASH_REFUND']);

$verified = new VerifiedOrganizationalContext(PlatformIdentityId::fromString('identity-a'),TenantId::fromString('tenant-a'),OrganizationId::fromString('org-a'),OutletId::fromString('outlet-a'),DeviceId::fromString('device-a'));
$context = PosExecutionContext::fromVerified($verified);
$repository = new LaravelPosActiveShiftPerformanceWorkspaceRepository($connection, true, 'ci', true, true);
$snapshot = $repository->read($context);
$assert($snapshot->tenantId()==='tenant-a'&&$snapshot->organizationId()==='org-a'&&$snapshot->outletId()==='outlet-a'&&$snapshot->deviceId()==='device-a','Sprint167 exact scope changed.');
$activeShift=$snapshot->activeShift();
$assert(is_array($activeShift)&&$activeShift['shift_id']==='shift-active-a'&&$activeShift['opener_actor_identity_id']==='identity-opener'&&$activeShift['opened_at_unix']===1000,'Sprint167 active shift identity is incorrect.');
$byKey=[]; foreach($snapshot->buckets() as $bucket){$byKey[$bucket['tender_category'].'|'.$bucket['currency'].'|'.$bucket['scale']]=$bucket;}
$assert(count($byKey)===2,'Sprint167 must isolate active exact-device tender/currency buckets.');
$cash=$byKey['CASH|IDR|0']??null; $assert(is_array($cash),'Sprint167 CASH bucket is missing.');
$assert($cash['completed_sales']==='2'&&$cash['voided_sales']==='1'&&$cash['active_sales']==='1'&&$cash['refunded_sales']==='1','Sprint167 CASH transaction counts are incorrect.');
$assert($cash['gross_atomic']==='3000'&&$cash['voided_atomic']==='2000'&&$cash['active_net_atomic']==='1000'&&$cash['refunded_cash_atomic']==='2000','Sprint167 CASH value arithmetic is incorrect.');
$manual=$byKey['MANUAL_EXTERNAL|IDR|0']??null; $assert(is_array($manual),'Sprint167 MANUAL_EXTERNAL bucket is missing.');
$assert($manual['completed_sales']==='1'&&$manual['voided_sales']==='0'&&$manual['active_sales']==='1'&&$manual['refunded_sales']==='0'&&$manual['gross_atomic']==='3000'&&$manual['active_net_atomic']==='3000'&&$manual['refunded_cash_atomic']==='0','Sprint167 manual-external performance is incorrect.');

$connection->table('oneqay_pos_sales')->insert(['tenant_id'=>'tenant-a','sale_id'=>'legacy-null-after-open','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','shift_id'=>null,'total_atomic'=>500,'applied_atomic'=>500,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'CASH_COUNTED','completed_at_unix'=>1400]);
try{$repository->read($context);$assert(false,'Sprint167 accepted legacy unbound sale evidence after active shift opening.');}catch(PosTransactionViolation){}
$connection->table('oneqay_pos_sales')->where('sale_id','legacy-null-after-open')->delete();
$connection->table('oneqay_pos_sale_cash_refunds')->where('refund_id','refund-cash-refunded')->update(['organization_id'=>'org-corrupt']);
try{$repository->read($context);$assert(false,'Sprint167 accepted mismatched cash-refund scope evidence.');}catch(PosTransactionViolation){}
$connection->table('oneqay_pos_sale_cash_refunds')->where('refund_id','refund-cash-refunded')->update(['organization_id'=>'org-a']);

foreach([[false,'ci',true,true],[true,'production',true,true],[true,'ci',false,true],[true,'ci',true,false]] as [$persistence,$runtime,$reporting,$feature]){
 try{(new LaravelPosActiveShiftPerformanceWorkspaceRepository($connection,$persistence,$runtime,$reporting,$feature))->read($context);$assert(false,'Sprint167 accepted a disallowed delivery state.');}catch(PosTransactionViolation){}
}

$connection->table('oneqay_pos_shifts')->where('tenant_id','tenant-a')->where('shift_id','shift-active-a')->update(['active_slot'=>null]);
$empty=$repository->read($context); $assert($empty->activeShift()===null&&$empty->buckets()===[],'Sprint167 no-active-shift state must be explicit and empty.');

$databaseManager->disconnect('s167_active_shift_performance'); $databaseManager->purge('s167_active_shift_performance'); @unlink($databasePath);
fwrite(STDOUT, "Sprint167 POS active shift performance regression passed.\n");
