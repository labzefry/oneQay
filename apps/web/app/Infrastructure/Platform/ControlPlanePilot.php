<?php
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use App\Application\Platform\{BusinessActivation,PlatformViolation};
use Illuminate\Database\Connection;
// Author by Lab | zefry. Canonical evidence is read, never synthesized. Real cash requires an explicit owner attestation.
final readonly class ControlPlanePilot {
 public function __construct(private Connection $db){}
 public function inspect(array $b,?string $shift=null):array{
  $t=$b['tenant_id'];$q=$this->db->table('oneqay_pos_shifts')->where('tenant_id',$t);if($shift!==null)$q->where('shift_id',$shift);
  $s=$q->orderByDesc('opened_at_unix')->first();$steps=['JRN_004'=>false,'JRN_005'=>false,'JRN_006'=>false,'JRN_010'=>false];
  if(!$s)return ['status'=>'NOT_YET_VERIFIED','steps'=>$steps,'shift_id'=>null,'review'=>null];
  $opening=$this->db->table('oneqay_pos_shift_opening_cash_evidence')->where('tenant_id',$t)->where('shift_id',$s->shift_id)->first();
  $close=$this->db->table('oneqay_pos_shift_close_evidence')->where('tenant_id',$t)->where('shift_id',$s->shift_id)->first();
  $sales=$this->db->table('oneqay_pos_sales')->where('tenant_id',$t)->where('shift_id',$s->shift_id)->where('outlet_id',$s->outlet_id)->where('device_id',$s->device_id)->where('tender_category','CASH')->where('evidence_mode','CASH_COUNTED')->where('total_atomic','>',0)->where('completed_at_unix','>=',$s->opened_at_unix);
  if($close)$sales->where('completed_at_unix','<=',$close->cutoff_at_unix);$sale=$sales->orderBy('completed_at_unix')->first();$catalog=[];
  if($sale)foreach($this->db->table('oneqay_pos_sale_lines')->where('tenant_id',$t)->where('sale_id',$sale->sale_id)->get(['product_id'])as$line){$j=$this->db->table('oneqay_pos_catalog_preparation_journal')->where('tenant_id',$t)->where('outlet_id',$s->outlet_id)->where('product_id',$line->product_id)->where('after_sellable',true)->where('occurred_at_unix','<=',$sale->completed_at_unix)->orderByDesc('occurred_at_unix')->first();$catalog[]=$j?['product_id'=>$line->product_id,'mutation_id'=>$j->mutation_id]:null;}
  $closing=$close?$this->db->table('oneqay_pos_shift_closing_cash_evidence')->where('tenant_id',$t)->where('shift_id',$s->shift_id)->where('evidence_id',$close->closing_cash_evidence_id)->first():null;
  $steps['JRN_004']=count($catalog)>0&&!in_array(null,$catalog,true);$steps['JRN_005']=$opening!==null;$steps['JRN_006']=$sale!==null;$steps['JRN_010']=$close!==null&&$closing!==null&&$opening!==null&&$close->opening_cash_evidence_id===$opening->evidence_id&&$s->active_slot===null;
  $evidence=['shift_id'=>$s->shift_id,'outlet_id'=>$s->outlet_id,'device_id'=>$s->device_id,'opened_at'=>(int)$s->opened_at_unix,'catalog'=>$catalog,'opening'=>$opening?(array)$opening:null,'sale'=>$sale?(array)$sale:null,'close'=>$close?(array)$close:null];
  $digest=hash('sha256',json_encode($evidence,JSON_THROW_ON_ERROR));$review=$this->db->table('oneqay_platform_pilot_reviews')->where('tenant_id',$t)->where('shift_id',$s->shift_id)->first();
  return ['status'=>$review&&hash_equals($review->evidence_digest,$digest)?'OWNER_ATTESTED_REAL_CASH':(in_array(false,$steps,true)?'NOT_YET_VERIFIED':'READY_FOR_OWNER_ATTESTATION'),'steps'=>$steps,'shift_id'=>$s->shift_id,'evidence_digest'=>$digest,'evidence'=>$evidence,'review'=>$review?(array)$review:null];
 }
 public function verify(array $b,array $in,string $actor):array{
  if(($in['real_cash_confirmed']??false)!==true)throw new PlatformViolation('REAL_CASH_ATTESTATION_REQUIRED');
  $source=(string)env('ONEQAY_RUNNING_SOURCE_COMMIT','');if(config('oneqay.runtime_class')!=='production'||!preg_match('/\A[a-f0-9]{40}\z/D',$source)||!\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows((string)config('oneqay.runtime_class')))throw new PlatformViolation('PRODUCTION_SOURCE_REQUIRED');
  $r=$this->inspect($b,BusinessActivation::text($in['shift_id']??null,32));
  if($r['status']!=='READY_FOR_OWNER_ATTESTATION'||!is_string($in['evidence_digest']??null)||!hash_equals($r['evidence_digest'],$in['evidence_digest']))throw new PlatformViolation('PILOT_EVIDENCE_INCOMPLETE_OR_CHANGED',409);
  $this->db->table('oneqay_platform_pilot_reviews')->insert(['id'=>bin2hex(random_bytes(16)),'business_id'=>$b['id'],'tenant_id'=>$b['tenant_id'],'shift_id'=>$r['shift_id'],'source_commit'=>$source,'evidence_digest'=>$r['evidence_digest'],'receipt_reference'=>BusinessActivation::text($in['receipt_reference']??null,160),'actor'=>$actor,'verified_at'=>time()]);return ['status'=>'OWNER_ATTESTED_REAL_CASH','shift_id'=>$r['shift_id']];
 }
}
