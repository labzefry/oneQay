<?php
// Author by Lab | zefry
declare(strict_types=1);
namespace App\Infrastructure\Pos;
use App\Application\Pos\{OpenShift,ShiftOpeningCommand,RecordShiftOpeningCash,ShiftOpeningCashCommand,RecordShiftClosingCash,ShiftClosingCashCommand,CloseShift,CloseShiftCommand,DeriveCashVariance,RecordCashVarianceExplanation,CashVarianceExplanationCommand,PosTransactionViolation};
use App\Application\Notification\MailStore;
use App\Domain\Pos\Money;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connection;
/** FIFO journal composes canonical use cases, never overwrites historical evidence. */
final readonly class DesktopOperations {
 public function __construct(private Connection $db,private Container $app,private LaravelDesktopPosService $desktop,private DesktopBusinessSettings $settings){}
 public function sync(array $events,string $correlation):array {
  abort_unless(array_is_list($events)&&count($events)<=50,422);$results=[];
  foreach($events as$event){try{$r=$this->accept($event,$correlation);}catch(\InvalidArgumentException|\ValueError){$r=['operation_id'=>is_array($event)?($event['operation_id']??''):'','state'=>'rejected','code'=>'POS_INVALID_OPERATION','correlation_id'=>$correlation];}catch(PosTransactionViolation){$r=['operation_id'=>$event['operation_id'],'state'=>'pending','code'=>'POS_SHIFT_REQUIRES_REVIEW','correlation_id'=>$correlation];}$results[]=$r;if(!in_array($r['state'],['accepted','review'],true))break;}return ['results'=>$results,'server_time'=>time()];
 }
 private function accept(mixed $e,string $correlation):array {
  if(!is_array($e)||count($e)!==10||array_diff(array_keys($e),['operation_id','kind','lease_id','shift_id','created_at','lines','tendered','table','order','note']))throw new \InvalidArgumentException();
  foreach(['operation_id','shift_id']as$k)if(!is_string($e[$k])||!preg_match('/\Adesk-[a-f0-9-]{36}\z/D',$e[$k]))throw new \InvalidArgumentException();
  if(!is_string($e['lease_id'])||!preg_match('/\A[a-f0-9]{32}\z/D',$e['lease_id'])||!is_int($e['created_at'])||!is_int($e['tendered'])||$e['tendered']<0||$e['tendered']>9007199254740991||!in_array($e['kind'],['shift_open','sale','shift_close','count'],true)||!is_array($e['lines'])||!array_is_list($e['lines'])||count($e['lines'])>100)throw new \InvalidArgumentException();
  foreach(['table'=>24,'order'=>32,'note'=>500]as$k=>$max)if(!is_string($e[$k])||mb_strlen($e[$k])>$max||preg_match('/[\x00-\x1f\x7f]/',$e[$k]))throw new \InvalidArgumentException();
  if(in_array($e['kind'],['shift_open','shift_close'],true)&&$e['lines']!==[])throw new \InvalidArgumentException();ksort($e);$fingerprint=hash('sha256',json_encode($e,JSON_THROW_ON_ERROR));$scope=$this->desktop->scope();$hash=$this->desktop->scopeHash();
  return $this->db->transaction(function()use($e,$fingerprint,$scope,$hash,$correlation){
   $this->db->table('oneqay_devices')->where('tenant_id',$scope['tenant_id'])->where('id',$scope['device_id'])->lockForUpdate()->firstOrFail();
   $lease=$this->db->table('oneqay_desktop_leases')->where('id',$e['lease_id'])->where('scope_hash',$hash)->where('shift_id','enterprise-v2')->first();if(!$lease)throw new \InvalidArgumentException();
   $q=$this->db->table('oneqay_desktop_events')->where('tenant_id',$scope['tenant_id'])->where('operation_id',$e['operation_id']);$old=(clone$q)->first();if($old){if(!hash_equals($old->fingerprint,$fingerprint)||!hash_equals($old->scope_hash,$hash))throw new \InvalidArgumentException();if($old->state!=='pending'||isset(json_decode($old->result,true)['report']))return json_decode($old->result,true,32,JSON_THROW_ON_ERROR);}
   if($e['created_at']<(int)$lease->issued_at||$e['created_at']>(int)$lease->expires_at||$e['created_at']>time()+60)throw new \InvalidArgumentException();
   $cached=json_decode($lease->snapshot,true,64,JSON_THROW_ON_ERROR);$currency=$cached['catalog'][0]['currency'];$scale=$cached['catalog'][0]['scale'];$r=['operation_id'=>$e['operation_id'],'state'=>'accepted','code'=>'POS_SYNC_ACCEPTED','correlation_id'=>$correlation];
   if($e['kind']==='shift_open'){
    if($e['operation_id']!==$e['shift_id'])throw new \InvalidArgumentException();$shift=$this->app->make(OpenShift::class)->open(new ShiftOpeningCommand($e['operation_id']),$correlation);$this->app->make(RecordShiftOpeningCash::class)->record(new ShiftOpeningCashCommand($e['operation_id'].'-cash',Money::fromAtomicUnits($e['tendered'],$currency,$scale)),$correlation);$r['shift_id']=$shift->shiftId();
   }else{
    $open=$this->db->table('oneqay_desktop_events')->where('tenant_id',$scope['tenant_id'])->where('operation_id',$e['shift_id'])->where('kind','shift_open')->where('state','accepted')->where('scope_hash',$hash)->first();if(!$open||$e['created_at']<json_decode($open->payload,true)['created_at'])throw new \InvalidArgumentException();$realShift=json_decode($open->result,true)['shift_id'];
    if($this->db->table('oneqay_desktop_events')->where('tenant_id',$scope['tenant_id'])->where('local_shift_id',$e['shift_id'])->where('kind','shift_close')->where('state','accepted')->exists())throw new \InvalidArgumentException();
    $active=$this->db->table('oneqay_pos_shifts')->where('tenant_id',$scope['tenant_id'])->where('shift_id',$realShift)->where('active_slot',1)->first();if(!$active||$active->actor_identity_id!==$scope['identity_id'])throw new PosTransactionViolation();
    if($e['kind']==='sale'||$e['kind']==='count'){
     $bound=substr(hash('sha256',$lease->id.'|'.$realShift),0,32);$this->db->table('oneqay_desktop_leases')->insertOrIgnore(['id'=>$bound,'scope_hash'=>$hash,'tenant_id'=>$scope['tenant_id'],'shift_id'=>$realShift,'catalog_revision'=>$lease->catalog_revision,'snapshot'=>$lease->snapshot,'issued_at'=>$lease->issued_at,'expires_at'=>$lease->expires_at]);$op=array_intersect_key($e,array_flip(['created_at','kind','lease_id','lines','operation_id','tendered']));$op['lease_id']=$bound;$r=$this->desktop->sync([$op],$correlation)['results'][0];
    }else{
     $lastSale=(int)$this->db->table('oneqay_pos_sales')->where('tenant_id',$scope['tenant_id'])->where('shift_id',$realShift)->max('completed_at_unix');if($lastSale>=time())return array_replace($r,['state'=>'pending','code'=>'POS_SYNC_RETRY']);
     $closing=$this->app->make(RecordShiftClosingCash::class)->record(new ShiftClosingCashCommand($e['operation_id'].'-cash',Money::fromAtomicUnits($e['tendered'],$currency,$scale)),$correlation);$expected=$this->app->make(LaravelExpectedCashSnapshotReader::class)->deriveFrom($closing);$variance=$this->app->make(DeriveCashVariance::class)->derive($expected,$closing);
     if($variance->varianceAtomic()!==0&&$e['note']!=='')$this->app->make(RecordCashVarianceExplanation::class)->record($variance,new CashVarianceExplanationCommand($e['operation_id'].'-note',$e['note']),$correlation);
     $r['state']='pending';$r['code']='POS_SHIFT_REQUIRES_REVIEW';$r['shift_id']=$realShift;
     $r['report']=['shift_id'=>$realShift,'cashier'=>$this->settings->native($this->desktop->context())['cashier_name'],'closed_at'=>$e['created_at'],'expected'=>$expected->expectedCash()->atomicUnits(),'counted'=>$e['tendered'],'variance'=>$variance->varianceAtomic(),'currency'=>$currency,'scale'=>$scale,'final'=>$r['state']==='accepted'];
     $r['mail_queued']=$this->queueReport($r['report'],$this->desktop->context());
    }
   }
   $q->updateOrInsert(['tenant_id'=>$scope['tenant_id'],'operation_id'=>$e['operation_id']],['scope_hash'=>$hash,'fingerprint'=>$fingerprint,'local_shift_id'=>$e['shift_id'],'kind'=>$e['kind'],'state'=>$r['state'],'payload'=>json_encode($e,JSON_THROW_ON_ERROR),'result'=>json_encode($r,JSON_THROW_ON_ERROR),'created_at'=>time()]);return $r;
  });
 }
 public function pendingClosures():array {
  $s=$this->app->make(\App\Application\Business\BusinessAccess::class)->require('authorization.policy.manage');
  $rows=$this->db->table('oneqay_desktop_events as e')->join('oneqay_desktop_leases as l',function($j){$j->on('l.scope_hash','=','e.scope_hash')->on('l.tenant_id','=','e.tenant_id');})->where('e.tenant_id',$s->tenantId()->value())->where('e.kind','shift_close')->where('e.state','pending')->select('e.operation_id','e.result','l.snapshot')->distinct()->get();$out=[];
  foreach($rows as$row){$lease=json_decode($row->snapshot,true);$scope=$lease['scope'];if($scope['organization_id']!==$s->organizationId()->value()||$scope['outlet_id']!==$s->outletId()->value()||$scope['device_id']!==$s->deviceId()->value())continue;$r=json_decode($row->result,true);if(isset($r['report']))$out[$row->operation_id]=$r;}return array_values($out);
 }
 public function finalize(string $id,string $correlation):array {
  $s=$this->app->make(\App\Application\Business\BusinessAccess::class)->require('authorization.policy.manage');
  return $this->db->transaction(function()use($s,$id,$correlation){
   $this->db->table('oneqay_devices')->where('tenant_id',$s->tenantId()->value())->where('id',$s->deviceId()->value())->lockForUpdate()->firstOrFail();
   $q=$this->db->table('oneqay_desktop_events')->where('tenant_id',$s->tenantId()->value())->where('operation_id',$id)->where('kind','shift_close');$row=$q->lockForUpdate()->first();abort_unless($row,404);$r=json_decode($row->result,true,32,JSON_THROW_ON_ERROR);abort_unless(isset($r['report']),409);$report=$r['report'];
   $shift=$this->db->table('oneqay_pos_shifts')->where('tenant_id',$s->tenantId()->value())->where('shift_id',$report['shift_id'])->where('organization_id',$s->organizationId()->value())->where('outlet_id',$s->outletId()->value())->where('device_id',$s->deviceId()->value())->first();abort_unless($shift,404);if($row->state==='accepted')return $r;
   try{$closed=$this->app->make(CloseShift::class)->close(new CloseShiftCommand($id.'-final'),$correlation);}catch(PosTransactionViolation){abort(409,'POS_SHIFT_REQUIRES_INDEPENDENT_REVIEW');}
   abort_unless($closed->shiftId()===$report['shift_id'],409);$r['state']='accepted';$r['code']='POS_SYNC_ACCEPTED';$r['report']['final']=true;$r['report']['finalized_at']=time();$r['mail_queued']=$this->queueReport($r['report'],$s);$q->update(['state'=>'accepted','result'=>json_encode($r,JSON_THROW_ON_ERROR)]);return $r;
  });
 }
 private function queueReport(array $r,\App\Application\Organization\VerifiedOrganizationalContext $scope):int {$emails=$this->settings->read($scope)['settings']['report_recipients'];foreach($emails as$email)$this->app->make(MailStore::class)->enqueue($scope->tenantId()->value(),'shift-report|'.$r['shift_id'].'|'.($r['final']?'final':'review').'|'.hash('sha256',$email),$email,'Laporan tutup kasir oneQay · '.$r['shift_id'],$this->reportText($r),time());return count($emails);}
 private function reportText(array $r):string {$m=fn($v)=>$r['currency'].' '.number_format($v/(10**$r['scale']),$r['scale'],',','.');return "LAPORAN TUTUP KASIR / SHIFT CLOSURE\nKasir: {$r['cashier']}\nShift: {$r['shift_id']}\nWaktu: ".gmdate('Y-m-d H:i:s',$r['closed_at']+25200)." WIB\nKas seharusnya: ".$m($r['expected'])."\nKas dihitung: ".$m($r['counted'])."\nSelisih: ".$m($r['variance'])."\nStatus: ".($r['final']?'Disahkan':'Menunggu persetujuan penutupan di web')."\n\n© oneQay";}
}
