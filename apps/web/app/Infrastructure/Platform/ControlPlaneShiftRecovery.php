<?php
// Author by Lab | zefry. Incident recovery releases a register, never certifies cash or deletes ledger evidence.
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use App\Application\Platform\{PlatformViolation,BusinessActivation};
use App\Application\Notification\MailStore;
use Illuminate\Database\Connection;
final readonly class ControlPlaneShiftRecovery {
 public function __construct(private Connection $db,private ?MailStore $mail=null){}
 public function listing(array $b):array {
  if(!$this->db->getSchemaBuilder()->hasTable('oneqay_pos_shift_recoveries'))return [];
  return $this->db->table('oneqay_pos_shifts')->where('tenant_id',$b['tenant_id'])->where('active_slot',1)->orderBy('opened_at_unix')->limit(100)->get()->map(fn($s)=>$this->snapshot((array)$s))->all();
 }
 private function snapshot(array $s):array {
  $cash=[];foreach(['opening','closing']as$kind){$r=$this->db->table('oneqay_pos_shift_'.$kind.'_cash_evidence')->where('tenant_id',$s['tenant_id'])->where('shift_id',$s['shift_id'])->first(['evidence_id',$kind.'_cash_atomic','currency','currency_scale']);$cash[$kind]=$r?(array)$r:null;}
  $sales=$this->db->table('oneqay_pos_sales')->where('tenant_id',$s['tenant_id'])->where('shift_id',$s['shift_id'])->count();
  $state=['shift_id'=>$s['shift_id'],'tenant_id'=>$s['tenant_id'],'organization_id'=>$s['organization_id'],'outlet_id'=>$s['outlet_id'],'device_id'=>$s['device_id'],'opened_at'=>(int)$s['opened_at_unix'],'sales_count'=>$sales,'cash'=>$cash];
  return $state+['review_digest'=>hash('sha256',json_encode($state,JSON_THROW_ON_ERROR))];
 }
 public function recover(array $b,array $in,string $actor):array {
  if(($in['reviewed']??false)!==true||($in['offline_reconciled']??false)!==true)throw new PlatformViolation('SHIFT_RECOVERY_REVIEW_REQUIRED');
  $id=BusinessActivation::text($in['shift_id']??null,32);$reason=BusinessActivation::text($in['reason']??null,300);if(strlen($reason)<12)throw new PlatformViolation('INCIDENT_REASON_REQUIRED');
  $s=$this->db->table('oneqay_pos_shifts')->where('tenant_id',$b['tenant_id'])->where('shift_id',$id)->first();if(!$s)throw new PlatformViolation('SHIFT_NOT_FOUND',404);
  $this->db->table('oneqay_devices')->where('tenant_id',$b['tenant_id'])->where('id',$s->device_id)->lockForUpdate()->firstOrFail();
  $s=$this->db->table('oneqay_pos_shifts')->where('tenant_id',$b['tenant_id'])->where('shift_id',$id)->lockForUpdate()->first();
  if((int)$s->active_slot!==1)throw new PlatformViolation('SHIFT_NOT_ACTIVE',409);
  $snapshot=$this->snapshot((array)$s);if(!is_string($in['review_digest']??null)||!hash_equals($snapshot['review_digest'],$in['review_digest']))throw new PlatformViolation('SHIFT_RECOVERY_PREVIEW_STALE',409);
  $now=time();$this->db->table('oneqay_pos_shift_recoveries')->insert(['tenant_id'=>$b['tenant_id'],'shift_id'=>$id,'actor'=>$actor,'reason'=>$reason,'review_digest'=>$snapshot['review_digest'],'snapshot'=>json_encode($snapshot,JSON_THROW_ON_ERROR),'recovered_at'=>$now]);
  $this->db->table('oneqay_pos_shifts')->where('tenant_id',$b['tenant_id'])->where('shift_id',$id)->where('active_slot',1)->update(['active_slot'=>null]);
  // Invalidate leases for every cashier on this register. Old FIFO entries remain on the device for operator reconciliation.
  $identities=$this->db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$b['tenant_id'])->where('organization_id',$s->organization_id)->where('outlet_id',$s->outlet_id)->where('device_id',$s->device_id)->pluck('identity_id')->all();$identities[]=$s->actor_identity_id;
  foreach(array_unique($identities)as$identity){$scope=['tenant_id'=>$b['tenant_id'],'identity_id'=>$identity,'organization_id'=>$s->organization_id,'outlet_id'=>$s->outlet_id,'device_id'=>$s->device_id];$this->db->table('oneqay_desktop_leases')->where('tenant_id',$b['tenant_id'])->where('scope_hash',hash('sha256',json_encode($scope,JSON_THROW_ON_ERROR)))->update(['expires_at'=>0]);}
  $this->db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$b['tenant_id'])->where('organization_id',$s->organization_id)->where('outlet_id',$s->outlet_id)->where('device_id',$s->device_id)->whereNull('revoked_at_unix')->update(['revoked_at_unix'=>$now]);
  $notification=null;if($this->mail&&isset($b['email']))$notification=$this->mail->enqueue($b['tenant_id'],'shift-recovery-'.$id,$b['email'],'oneQay · Pemulihan shift / Shift recovery',"Shift $id dipulihkan setelah insiden. Alasan: $reason\nSlot kasir telah dilepas. Transaksi dan bukti kas dipertahankan; tindakan ini bukan pengesahan penutupan kas. Periksa transaksi offline sebelum memulai shift baru.\nRegister released after incident recovery. Historical sales and cash evidence remain unchanged. This is not a financial close approval. Reconcile offline transactions before starting a new shift.\nCopyright oneQay",$now);
  return ['shift_id'=>$id,'state'=>'recovered_requires_reconciliation','notification_id'=>$notification];
 }
}
