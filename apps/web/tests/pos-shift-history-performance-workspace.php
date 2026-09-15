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
use App\Infrastructure\Pos\LaravelPosShiftHistoryPerformanceWorkspaceRepository;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('r', 32));
foreach (['APP_ENV'=>'testing','APP_KEY'=>$testKey,'APP_DEBUG'=>'false','ONEQAY_RUNTIME_CLASS'=>'ci'] as $key => $value) {
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

$databasePath = sys_get_temp_dir().'/oneqay-s168-shift-history-'.bin2hex(random_bytes(8)).'.sqlite';
$assert(touch($databasePath), 'Sprint168 disposable database could not be created.');

$app['config']->set('database.default', 's168_shift_history');
$app['config']->set('database.connections.s168_shift_history', ['driver'=>'sqlite','database'=>$databasePath,'prefix'=>'','foreign_key_constraints'=>false]);
/** @var \Illuminate\Database\DatabaseManager $databaseManager */
$databaseManager = $app->make('db');
$databaseManager->purge('s168_shift_history');
$connection = $databaseManager->connection('s168_shift_history');

$connection->statement('CREATE TABLE oneqay_pos_shifts (tenant_id TEXT NOT NULL, shift_id TEXT NOT NULL, actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, active_slot INTEGER NULL, opened_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_shift_close_evidence (tenant_id TEXT NOT NULL, shift_id TEXT NOT NULL, closer_actor_identity_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, cutoff_at_unix INTEGER NOT NULL, expected_cash_atomic INTEGER NOT NULL, observed_closing_cash_atomic INTEGER NOT NULL, variance_atomic INTEGER NOT NULL, variance_direction TEXT NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, review_outcome TEXT NULL, closed_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sales (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, device_id TEXT NOT NULL, shift_id TEXT NULL, total_atomic INTEGER NOT NULL, applied_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL, completed_at_unix INTEGER NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_voids (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, reversed_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL)');
$connection->statement('CREATE TABLE oneqay_pos_sale_cash_refunds (tenant_id TEXT NOT NULL, sale_id TEXT NOT NULL, refund_id TEXT NOT NULL, void_id TEXT NOT NULL, organization_id TEXT NOT NULL, outlet_id TEXT NOT NULL, refunded_atomic INTEGER NOT NULL, currency TEXT NOT NULL, currency_scale INTEGER NOT NULL, tender_category TEXT NOT NULL, evidence_mode TEXT NOT NULL)');

$shiftOld = str_repeat('a', 32);
$shiftNew = str_repeat('b', 32);
$shiftActive = str_repeat('c', 32);

$connection->table('oneqay_pos_shifts')->insert([
    ['tenant_id'=>'tenant-a','shift_id'=>$shiftOld,'actor_identity_id'=>'identity-opener-a','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','active_slot'=>null,'opened_at_unix'=>1000],
    ['tenant_id'=>'tenant-a','shift_id'=>$shiftNew,'actor_identity_id'=>'identity-opener-b','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-b','active_slot'=>null,'opened_at_unix'=>2100],
    ['tenant_id'=>'tenant-a','shift_id'=>$shiftActive,'actor_identity_id'=>'identity-active','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','active_slot'=>1,'opened_at_unix'=>3100],
    ['tenant_id'=>'tenant-a','shift_id'=>str_repeat('d',32),'actor_identity_id'=>'identity-foreign','organization_id'=>'org-a','outlet_id'=>'outlet-b','device_id'=>'device-x','active_slot'=>null,'opened_at_unix'=>1000],
]);
$connection->table('oneqay_pos_shift_close_evidence')->insert([
    ['tenant_id'=>'tenant-a','shift_id'=>$shiftOld,'closer_actor_identity_id'=>'identity-closer-a','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','cutoff_at_unix'=>1900,'expected_cash_atomic'=>500,'observed_closing_cash_atomic'=>500,'variance_atomic'=>0,'variance_direction'=>'MATCH','currency'=>'IDR','currency_scale'=>0,'review_outcome'=>null,'closed_at_unix'=>2000],
    ['tenant_id'=>'tenant-a','shift_id'=>$shiftNew,'closer_actor_identity_id'=>'identity-closer-b','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-b','cutoff_at_unix'=>2900,'expected_cash_atomic'=>6000,'observed_closing_cash_atomic'=>6100,'variance_atomic'=>100,'variance_direction'=>'OVER','currency'=>'IDR','currency_scale'=>0,'review_outcome'=>'REVIEW_ACCEPTED','closed_at_unix'=>3000],
    ['tenant_id'=>'tenant-a','shift_id'=>str_repeat('d',32),'closer_actor_identity_id'=>'identity-closer-x','organization_id'=>'org-a','outlet_id'=>'outlet-b','device_id'=>'device-x','cutoff_at_unix'=>1900,'expected_cash_atomic'=>0,'observed_closing_cash_atomic'=>0,'variance_atomic'=>0,'variance_direction'=>'MATCH','currency'=>'IDR','currency_scale'=>0,'review_outcome'=>null,'closed_at_unix'=>2000],
]);
$connection->table('oneqay_pos_sales')->insert([
    ['tenant_id'=>'tenant-a','sale_id'=>'sale-old','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-a','shift_id'=>$shiftOld,'total_atomic'=>500,'applied_atomic'=>500,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'CASH_COUNTED','completed_at_unix'=>1500],
    ['tenant_id'=>'tenant-a','sale_id'=>'sale-new-cash-live','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-b','shift_id'=>$shiftNew,'total_atomic'=>1000,'applied_atomic'=>1000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'CASH_COUNTED','completed_at_unix'=>2200],
    ['tenant_id'=>'tenant-a','sale_id'=>'sale-new-cash-refunded','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-b','shift_id'=>$shiftNew,'total_atomic'=>2000,'applied_atomic'=>2000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'CASH_COUNTED','completed_at_unix'=>2300],
    ['tenant_id'=>'tenant-a','sale_id'=>'sale-new-manual','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-b','shift_id'=>$shiftNew,'total_atomic'=>3000,'applied_atomic'=>3000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'MANUAL_EXTERNAL','evidence_mode'=>'OPERATOR_RECORDED','completed_at_unix'=>2400],
]);
$connection->table('oneqay_pos_sale_voids')->insert(['tenant_id'=>'tenant-a','sale_id'=>'sale-new-cash-refunded','void_id'=>'void-new-cash','organization_id'=>'org-a','outlet_id'=>'outlet-a','reversed_atomic'=>2000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'FULL_SALE_VOID']);
$connection->table('oneqay_pos_sale_cash_refunds')->insert(['tenant_id'=>'tenant-a','sale_id'=>'sale-new-cash-refunded','refund_id'=>'refund-new-cash','void_id'=>'void-new-cash','organization_id'=>'org-a','outlet_id'=>'outlet-a','refunded_atomic'=>2000,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'FULL_CASH_REFUND']);

$verified = new VerifiedOrganizationalContext(PlatformIdentityId::fromString('identity-a'),TenantId::fromString('tenant-a'),OrganizationId::fromString('org-a'),OutletId::fromString('outlet-a'),DeviceId::fromString('device-a'));
$context = PosExecutionContext::fromVerified($verified);
$repository = new LaravelPosShiftHistoryPerformanceWorkspaceRepository($connection, true, 'ci', true, true, true);

$snapshot = $repository->read($context, null);
$assert($snapshot->tenantId()==='tenant-a'&&$snapshot->organizationId()==='org-a'&&$snapshot->outletId()==='outlet-a'&&$snapshot->requesterDeviceId()==='device-a','Sprint168 exact requester scope changed.');
$closed = $snapshot->closedShifts();
$assert(count($closed)===2&&$closed[0]['shift_id']===$shiftNew&&$closed[1]['shift_id']===$shiftOld,'Sprint168 closed shift ordering/scope is incorrect.');
$selected = $snapshot->selectedShift();
$assert(is_array($selected)&&$selected['shift_id']===$shiftNew&&$selected['device_id']==='device-b','Sprint168 must allow same-outlet cross-device historical selection.');
$assert($selected['duration_seconds']===900&&$selected['expected_cash_atomic']==='6000'&&$selected['observed_closing_cash_atomic']==='6100'&&$selected['variance_atomic']==='100'&&$selected['variance_direction']==='OVER'&&$selected['review_outcome']==='REVIEW_ACCEPTED','Sprint168 selected close evidence is incorrect.');
$byKey=[]; foreach($snapshot->buckets() as $bucket){$byKey[$bucket['tender_category'].'|'.$bucket['currency'].'|'.$bucket['scale']]=$bucket;}
$assert(count($byKey)===2,'Sprint168 selected shift tender/currency buckets are incorrect.');
$cash=$byKey['CASH|IDR|0']??null; $assert(is_array($cash),'Sprint168 CASH bucket is missing.');
$assert($cash['completed_sales']==='2'&&$cash['voided_sales']==='1'&&$cash['active_sales']==='1'&&$cash['refunded_sales']==='1','Sprint168 CASH counts are incorrect.');
$assert($cash['gross_atomic']==='3000'&&$cash['voided_atomic']==='2000'&&$cash['active_net_atomic']==='1000'&&$cash['refunded_cash_atomic']==='2000','Sprint168 CASH arithmetic is incorrect.');
$manual=$byKey['MANUAL_EXTERNAL|IDR|0']??null; $assert(is_array($manual)&&$manual['active_net_atomic']==='3000'&&$manual['refunded_cash_atomic']==='0','Sprint168 manual performance is incorrect.');

$old = $repository->read($context, $shiftOld);
$assert($old->selectedShift()['shift_id']===$shiftOld&&count($old->buckets())===1&&$old->buckets()[0]['active_net_atomic']==='500','Sprint168 explicit closed shift selection is incorrect.');
try{$repository->read($context,str_repeat('f',32));$assert(false,'Sprint168 accepted a selected shift outside the bounded history.');}catch(PosTransactionViolation){}

$connection->table('oneqay_pos_sales')->insert(['tenant_id'=>'tenant-a','sale_id'=>'legacy-null-in-shift','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-b','shift_id'=>null,'total_atomic'=>100,'applied_atomic'=>100,'currency'=>'IDR','currency_scale'=>0,'tender_category'=>'CASH','evidence_mode'=>'CASH_COUNTED','completed_at_unix'=>2500]);
try{$repository->read($context,$shiftNew);$assert(false,'Sprint168 accepted legacy unbound sale evidence inside selected shift window.');}catch(PosTransactionViolation){}
$connection->table('oneqay_pos_sales')->where('sale_id','legacy-null-in-shift')->delete();

$connection->table('oneqay_pos_shift_close_evidence')->where('shift_id',$shiftNew)->update(['device_id'=>'device-corrupt']);
try{$repository->read($context,null);$assert(false,'Sprint168 accepted close evidence with a mismatched device.');}catch(PosTransactionViolation){}
$connection->table('oneqay_pos_shift_close_evidence')->where('shift_id',$shiftNew)->update(['device_id'=>'device-b']);

$missingCloseShift=str_repeat('e',32);
$connection->table('oneqay_pos_shifts')->insert(['tenant_id'=>'tenant-a','shift_id'=>$missingCloseShift,'actor_identity_id'=>'identity-missing','organization_id'=>'org-a','outlet_id'=>'outlet-a','device_id'=>'device-c','active_slot'=>null,'opened_at_unix'=>4000]);
try{$repository->read($context,null);$assert(false,'Sprint168 accepted a closed shift without Final Shift Close evidence.');}catch(PosTransactionViolation){}
$connection->table('oneqay_pos_shifts')->where('shift_id',$missingCloseShift)->delete();

foreach([[false,'ci',true,true,true],[true,'production',true,true,true],[true,'ci',false,true,true],[true,'ci',true,false,true],[true,'ci',true,true,false]] as [$persistence,$runtime,$reporting,$feature,$shiftClose]){
    try{(new LaravelPosShiftHistoryPerformanceWorkspaceRepository($connection,$persistence,$runtime,$reporting,$feature,$shiftClose))->read($context,null);$assert(false,'Sprint168 accepted a disallowed delivery state.');}catch(PosTransactionViolation){}
}

$databaseManager->disconnect('s168_shift_history');
$databaseManager->purge('s168_shift_history');
@unlink($databasePath);
fwrite(STDOUT, "Sprint168 POS shift history performance regression passed.\n");
