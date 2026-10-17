<?php
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use App\Application\Platform\{BusinessActivation,PlatformViolation};
use App\Application\Notification\MailStore;
use Illuminate\Database\Connection;
// Author by Lab | zefry. Snapshot reviewed audiences, recheck consent on fan-out, reuse bounded SMTP outbox.
final readonly class ControlPlaneCampaigns {
 public function __construct(private Connection $db,private MailStore $mail,private string $publicUrl,private string $signingKey){}
 public function listing():array{
  return $this->db->table('oneqay_platform_campaigns')->orderByDesc('updated_at')->limit(100)->get()->map(function($r):array{$a=(array)$r;$a['delivery']=$this->db->table('oneqay_platform_campaign_recipients as r')->leftJoin('oneqay_mail_messages as m','m.id','=','r.message_id')->where('r.campaign_id',$r->id)->selectRaw('COALESCE(m.status, r.state) AS outcome, COUNT(*) AS total')->groupByRaw('COALESCE(m.status, r.state)')->pluck('total','outcome')->all();return $a;})->all();
 }
 private function campaign(string $id,bool $lock=false):array{$q=$this->db->table('oneqay_platform_campaigns')->where('id',$id);$r=($lock?$q->lockForUpdate():$q)->first();if(!$r)throw new PlatformViolation('CAMPAIGN_NOT_FOUND',404);return(array)$r;}
 private function audience(array $c):array{
  $q=$this->db->table('oneqay_platform_businesses as b')->where('b.status','active');if($c['locale']!=='all')$q->where('b.locale',$c['locale']);
  if($c['channel']==='email')$q->join('oneqay_platform_marketing_preferences as p','p.business_id','=','b.id')->where('p.subscribed',true);
  $rows=$q->orderBy('b.id')->limit(10001)->get(['b.id','b.email','b.locale'])->map(fn($r)=>(array)$r)->all();if(count($rows)>10000)throw new PlatformViolation('CAMPAIGN_AUDIENCE_LIMIT');
  if($c['channel']==='email'){$hashes=array_map(fn($r)=>hash('sha256',strtolower($r['email'])),$rows);$suppressed=[];foreach(array_chunk($hashes,500)as$chunk)foreach($this->db->table('oneqay_mail_suppressions')->whereIn('recipient_hash',$chunk)->pluck('recipient_hash')as$h)$suppressed[$h]=true;$rows=array_values(array_filter($rows,fn($r)=>!isset($suppressed[hash('sha256',strtolower($r['email']))])));}
  return $rows;
 }
 public function preview(string $id):array{
  $c=$this->campaign($id);$rows=$this->audience($c);$snapshot=array_map(fn($r)=>[$r['id'],hash('sha256',strtolower($r['email']))],$rows);
  return ['id'=>$id,'version'=>(int)$c['version'],'count'=>count($rows),'digest'=>hash('sha256',json_encode([$c['id'],$c['version'],$c['channel'],$c['subject'],$c['body'],$c['locale'],$snapshot],JSON_THROW_ON_ERROR)),'channel'=>$c['channel'],'subject'=>$c['subject'],'body'=>$c['body'],'recipients'=>array_slice(array_map(fn($r)=>['id'=>$r['id'],'email_hint'=>substr($r['email'],0,1).'***@'.(explode('@',$r['email'])[1]??'')],$rows),0,5)];
 }
 public function command(array $in):array{
  $action=$in['action'];$id=$in['id']??'';$now=time();
  if($action==='campaign.save'){
   $c=$id!==''?$this->campaign(BusinessActivation::text($id,32),true):null;if((int)($c['version']??0)!==$in['version'])throw new PlatformViolation('STALE_VERSION',409);if($c!==null&&$c['status']!=='draft')throw new PlatformViolation('CAMPAIGN_ALREADY_QUEUED',409);
   $locale=$in['locale']??'all';$channel=$in['channel']??'email';$body=$in['body']??null;
   if(!in_array($locale,['id','en','all'],true)||!in_array($channel,['email','in_app'],true)||!is_string($body)||trim($body)===''||strlen($body)>12000||preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]/',$body))throw new PlatformViolation('INVALID_CAMPAIGN');
   $data=['version'=>$in['version']+1,'name'=>BusinessActivation::text($in['name']??null,120),'subject'=>BusinessActivation::text($in['subject']??null,200),'body'=>trim($body),'locale'=>$locale,'channel'=>$channel,'status'=>'draft','updated_at'=>$now];
   if($c===null){if($this->db->table('oneqay_platform_campaigns')->where('status','draft')->count()>=100)throw new PlatformViolation('CAMPAIGN_DRAFT_LIMIT');$id=bin2hex(random_bytes(16));$this->db->table('oneqay_platform_campaigns')->insert(['id'=>$id,'created_at'=>$now]+$data);}else $this->db->table('oneqay_platform_campaigns')->where('id',$id)->update($data);
   return ['id'=>$id,'version'=>$data['version']];
  }
  $c=$this->campaign(BusinessActivation::text($id,32),true);if((int)$c['version']!==$in['version'])throw new PlatformViolation('STALE_VERSION',409);
  if($action==='campaign.cancel'){
   if($c['status']==='cancelled')throw new PlatformViolation('INVALID_CAMPAIGN_TRANSITION',409);
   $this->db->table('oneqay_platform_campaigns')->where('id',$id)->update(['status'=>'cancelled','version'=>$in['version']+1,'updated_at'=>$now]);
   $this->suppress($this->db->table('oneqay_platform_campaign_recipients')->where('campaign_id',$id),'CAMPAIGN_CANCELLED');return ['id'=>$id,'status'=>'cancelled','in_flight_or_sent_messages_may_complete'=>true];
  }
  if($c['status']!=='draft'||($in['reviewed']??false)!==true)throw new PlatformViolation('CAMPAIGN_REVIEW_REQUIRED',409);
  $preview=$this->preview($id);if(!is_string($in['review_digest']??null)||!hash_equals($preview['digest'],$in['review_digest']))throw new PlatformViolation('AUDIENCE_CHANGED_REVIEW_AGAIN',409);if(!$preview['count'])throw new PlatformViolation('NO_ELIGIBLE_RECIPIENTS');
  $due=$in['due_at']??$now;if(!is_int($due)||$due<$now-60||$due>$now+2592000)throw new PlatformViolation('INVALID_CAMPAIGN_SCHEDULE');
  if($c['channel']==='email'&&(!str_starts_with($this->publicUrl,'https://')||strlen($this->signingKey)<32))throw new PlatformViolation('MARKETING_CONFIGURATION_REQUIRED');
  foreach($this->audience($c)as$r)$this->db->table('oneqay_platform_campaign_recipients')->insert(['campaign_id'=>$id,'business_id'=>$r['id'],'recipient_hash'=>hash('sha256',strtolower($r['email'])),'state'=>'selected']);
  $this->db->table('oneqay_platform_campaigns')->where('id',$id)->update(['version'=>$in['version']+1,'status'=>'scheduled','due_at'=>max($now,$due),'review_digest'=>$preview['digest'],'recipient_count'=>$preview['count'],'updated_at'=>$now]);return ['id'=>$id,'status'=>'scheduled','recipients'=>$preview['count']];
 }
 public function work(int $limit=50):int{
  $done=0;$deadline=time()+15;
  while($done<max(1,min(100,$limit))&&time()<$deadline){
   $worked=$this->db->transaction(function():bool{
    $this->db->table('oneqay_mail_settings')->where('id',1)->lockForUpdate()->firstOrFail();
    $c=$this->db->table('oneqay_platform_campaigns')->where('status','scheduled')->where('due_at','<=',time())->orderBy('due_at')->lockForUpdate()->first();if(!$c)return false;
    $r=$this->db->table('oneqay_platform_campaign_recipients')->where('campaign_id',$c->id)->where('state','selected')->orderBy('business_id')->lockForUpdate()->first();
    if(!$r){$this->db->table('oneqay_platform_campaigns')->where('id',$c->id)->update(['status'=>$c->channel==='in_app'?'published':'dispatched','updated_at'=>time()]);return true;}
    $b=$this->db->table('oneqay_platform_businesses')->where('id',$r->business_id)->first();$state='skipped';$message=null;
    if($b&&$b->status==='active'&&hash_equals($r->recipient_hash,hash('sha256',strtolower($b->email)))){
     if($c->channel==='in_app')$state='published';
     elseif($this->subscribed($b->id)){$url=rtrim($this->publicUrl,'/').'/marketing/preferences#token='.$this->token($b->id);$footer=$b->locale==='id'?"\n\nBerhenti berlangganan informasi marketing oneQay: ":"\n\nUnsubscribe from oneQay marketing updates: ";$message=$this->mail->enqueue($b->tenant_id,'campaign-'.$c->id.'-'.$b->id,$b->email,$c->subject,$c->body.$footer.$url."\nCopyright oneQay",time());$state='queued';}
    }
    $this->db->table('oneqay_platform_campaign_recipients')->where('campaign_id',$c->id)->where('business_id',$r->business_id)->update(['state'=>$state,'message_id'=>$message]);return true;
   });if(!$worked)break;++$done;
  }return $done;
 }
 public function subscribed(string $id):bool{return(bool)$this->db->table('oneqay_platform_marketing_preferences')->where('business_id',$id)->value('subscribed');}
 private function suppress(\Illuminate\Database\Query\Builder $recipients,string $code):void{
  $ids=$recipients->whereNotNull('message_id')->pluck('message_id')->all();foreach(array_chunk($ids,500)as$chunk)$this->db->table('oneqay_mail_messages')->whereIn('id',$chunk)->whereIn('status',['queued','retry'])->update(['status'=>'suppressed','safe_code'=>$code,'updated_at'=>time()]);
 }
 public function preference(string $id,bool $subscribed,string $source):void{
  $this->db->transaction(function()use($id,$subscribed,$source):void{$this->db->table('oneqay_mail_settings')->where('id',1)->lockForUpdate()->firstOrFail();$this->db->table('oneqay_platform_marketing_preferences')->updateOrInsert(['business_id'=>$id],['subscribed'=>$subscribed,'source'=>$source,'updated_at'=>time()]);if(!$subscribed)$this->suppress($this->db->table('oneqay_platform_campaign_recipients')->where('business_id',$id),'MARKETING_UNSUBSCRIBED');});
 }
 private function token(string $id):string{if(strlen($this->signingKey)<32)throw new PlatformViolation('MARKETING_SIGNING_KEY_REQUIRED');return $id.'.'.hash_hmac('sha256','oneqay-marketing-optout-v1|'.$id,$this->signingKey);}
 public function unsubscribe(string $token):void{if(!preg_match('/\A([a-f0-9]{32})\.([a-f0-9]{64})\z/D',$token,$m)||!hash_equals($this->token($m[1]),$token))throw new PlatformViolation('INVALID_PREFERENCE_LINK');$this->preference($m[1],false,'recipient-optout');}
 public function announcements(string $tenant):array{
  return $this->db->table('oneqay_platform_campaign_recipients as r')->join('oneqay_platform_campaigns as c','c.id','=','r.campaign_id')->join('oneqay_platform_businesses as b','b.id','=','r.business_id')->where('b.tenant_id',$tenant)->where('c.channel','in_app')->where('c.status','published')->where('r.state','published')->orderByDesc('c.updated_at')->limit(5)->get(['c.id','c.subject','c.body','c.updated_at'])->map(fn($r)=>(array)$r)->all();
 }
}
