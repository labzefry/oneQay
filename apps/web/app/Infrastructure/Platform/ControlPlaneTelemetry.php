<?php
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use Illuminate\Database\Connection;
// Author by Lab | zefry. Aggregate metrics only; no IPs, request bodies, query strings, SQL or exception messages.
final readonly class ControlPlaneTelemetry {
 public const BINS=['le50'=>50,'le100'=>100,'le250'=>250,'le500'=>500,'le750'=>750,'le1000'=>1000,'le3000'=>3000,'gt3000'=>null];
 public function __construct(private Connection $db){}
 public function record(int $status,int $ms,string $route,string $correlation,string $code=''):void{
  if(!config('platform.enabled')||!config('database.oneqay_persistence_enabled')||!$this->db->getSchemaBuilder()->hasTable('oneqay_platform_http_metrics'))return;
  $now=time();$bucket=intdiv($now,60)*60;$ms=max(0,min($ms,600000));$route=$this->token($route,'unnamed');$correlation=$this->token($correlation,'unavailable');$code=substr($this->token($code,'HTTP_'.$status),0,64);$bin='gt3000';
  foreach(self::BINS as$k=>$max)if($max!==null&&$ms<=$max){$bin=$k;break;}
  $this->db->transaction(function()use($status,$ms,$route,$correlation,$code,$bin,$now,$bucket):void{
   $this->db->table('oneqay_platform_http_metrics')->insertOrIgnore(['bucket'=>$bucket]);$q=$this->db->table('oneqay_platform_http_metrics')->where('bucket',$bucket);$r=(clone $q)->lockForUpdate()->first();
   $q->update(['requests'=>(int)$r->requests+1,'client_errors'=>(int)$r->client_errors+($status>=400&&$status<500?1:0),'server_errors'=>(int)$r->server_errors+($status>=500?1:0),'total_ms'=>(int)$r->total_ms+$ms,'max_ms'=>max((int)$r->max_ms,$ms),$bin=>(int)$r->$bin+1]);
   if($status<400||(in_array($status,[401,403,404,419,429],true)&&!str_starts_with($code,'PAGE_')))return;
   $id=hash('sha256',$route.'|'.$status.'|'.$code);$q=$this->db->table('oneqay_platform_errors')->where('id',$id);$e=(clone$q)->lockForUpdate()->first();
   if(!$e){if($this->db->table('oneqay_platform_errors')->count()>=5000)return;$this->db->table('oneqay_platform_errors')->insert(['id'=>$id,'code'=>$code,'route'=>$route,'http_status'=>$status,'correlation_id'=>$correlation,'state'=>'open','version'=>1,'occurrences'=>1,'first_seen'=>$now,'last_seen'=>$now]);}
   else $q->update(['occurrences'=>(int)$e->occurrences+1,'last_seen'=>$now,'correlation_id'=>$correlation,'state'=>$e->state==='resolved'?'open':$e->state,'version'=>(int)$e->version+1]);
  });
 }
 public function sample():array{
  (new \App\Infrastructure\Pos\DesktopSyncDiagnostics($this->db))->prune();$at=time();$t=hrtime(true);$this->db->select('SELECT 1');$latency=round((hrtime(true)-$t)/1000000,2);
  $free=@disk_free_space(storage_path());$total=@disk_total_space(storage_path());
  $s=['sampled_at'=>$at,'database'=>['state'=>'healthy','latency_ms'=>$latency,'engine'=>$this->db->getDriverName(),'table_count'=>count($this->db->getSchemaBuilder()->getTableListing(null,false))],'disk_free_bytes'=>$free===false?null:(int)$free,'disk_total_bytes'=>$total===false?null:(int)$total,'php_memory_bytes'=>memory_get_usage(true),'php_version'=>PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION,'source_commit'=>(string)env('ONEQAY_RUNNING_SOURCE_COMMIT',''),'worker_at'=>(int)$this->db->table('oneqay_mail_settings')->where('id',1)->value('heartbeat_at'),'session_directory_writable'=>is_writable((string)config('session.files')),'scope'=>'application_process_and_filesystem'];
  $this->db->table('oneqay_platform_health_samples')->insert(['sampled_at'=>$at,'measurements'=>json_encode($s,JSON_THROW_ON_ERROR)]);
  $this->db->table('oneqay_platform_http_metrics')->where('bucket','<',$at-604800)->delete();$this->db->table('oneqay_platform_health_samples')->where('sampled_at','<',$at-604800)->delete();$this->db->table('oneqay_platform_errors')->where('state','resolved')->where('last_seen','<',$at-2592000)->delete();return $s;
 }
 public function dashboard():array{
  $sum=array_fill_keys(['requests','client_errors','server_errors','total_ms',...array_keys(self::BINS)],0);$hours=[];
  foreach($this->db->table('oneqay_platform_http_metrics')->where('bucket','>=',time()-86400)->orderBy('bucket')->limit(1441)->get()as$row){$r=(array)$row;foreach($sum as$k=>$v)$sum[$k]+=(int)$r[$k];$h=intdiv((int)$r['bucket'],3600)*3600;$hours[$h]??=['at'=>$h,'requests'=>0,'errors'=>0,'total_ms'=>0];$hours[$h]['requests']+=(int)$r['requests'];$hours[$h]['errors']+=(int)$r['server_errors'];$hours[$h]['total_ms']+=(int)$r['total_ms'];}
  $c=0;$p95=null;foreach(self::BINS as$k=>$bound){$c+=$sum[$k];if($sum['requests']&&$c>=ceil($sum['requests']*.95)){$p95=$bound;break;}}
  $samples=$this->db->table('oneqay_platform_health_samples')->orderByDesc('id')->limit(48)->get()->map(fn($r)=>json_decode($r->measurements,true,32,JSON_THROW_ON_ERROR))->all();
  return ['totals'=>$sum,'p95_upper_bound_ms'=>$p95,'p95_over_3000'=>$sum['requests']>0&&$p95===null,'hours'=>array_values($hours),'latest'=>$samples[0]??null,'samples'=>array_reverse($samples),'errors'=>$this->db->table('oneqay_platform_errors')->orderByDesc('last_seen')->limit(100)->get()->map(fn($r)=>(array)$r)->all(),'window_seconds'=>86400,'retention_days'=>7];
 }
 private function token(string $s,string $fallback):string{return preg_match('/\A[A-Za-z0-9][A-Za-z0-9_.:-]{0,127}\z/D',$s)?$s:$fallback;}
}
