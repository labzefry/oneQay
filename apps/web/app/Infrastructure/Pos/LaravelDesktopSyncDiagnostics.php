<?php
// Author by Lab | zefry. POS HTTP 200 may contain a rejected item: record item outcomes explicitly.
declare(strict_types=1);
namespace App\Infrastructure\Pos;
use Illuminate\Database\Connection;
final readonly class LaravelDesktopSyncDiagnostics {
 public function __construct(private Connection $db){}
 public function record(array $scope,mixed $event,array $result):void {
  if(!$this->db->getSchemaBuilder()->hasTable('oneqay_desktop_sync_diagnostics')||!is_array($event)||!is_string($event['operation_id']??null)||!preg_match('/\Adesk-[a-f0-9-]{36}\z/D',$event['operation_id']))return;
  $this->db->table('oneqay_desktop_sync_diagnostics')->updateOrInsert(['tenant_id'=>$scope['tenant_id'],'operation_id'=>$event['operation_id']],['device_id'=>$scope['device_id'],'identity_id'=>$scope['identity_id'],'kind'=>in_array($event['kind']??null,['sale','count','shift_open','shift_close'],true)?$event['kind']:'unknown','state'=>$result['state'],'code'=>$result['code'],'correlation_id'=>substr($result['correlation_id'],0,128),'last_seen'=>time()]);
 }
 public function listing(?string $tenant=null):array {
  if(!$this->db->getSchemaBuilder()->hasTable('oneqay_desktop_sync_diagnostics'))return ['available'=>false,'rows'=>[],'counts'=>[],'retention_days'=>3];
  $q=$this->db->table('oneqay_desktop_sync_diagnostics')->where('last_seen','>=',time()-259200);if($tenant!==null)$q->where('tenant_id',$tenant);
  return ['available'=>true,'rows'=>(clone $q)->orderByDesc('last_seen')->orderBy('operation_id')->limit(100)->get()->map(fn($r)=>(array)$r)->all(),'counts'=>(clone $q)->selectRaw('state, COUNT(*) AS total')->groupBy('state')->pluck('total','state')->all(),'retention_days'=>3];
 }
 public function prune():void {if($this->db->getSchemaBuilder()->hasTable('oneqay_desktop_sync_diagnostics'))$this->db->table('oneqay_desktop_sync_diagnostics')->where('last_seen','<',time()-259200)->delete();}
}
