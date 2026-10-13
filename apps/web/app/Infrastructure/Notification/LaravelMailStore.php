<?php
declare(strict_types=1);
namespace App\Infrastructure\Notification;
use App\Application\Notification\MailStore;
use App\Application\Platform\{BusinessActivation, PlatformViolation};
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Connection;
// Author by Lab | zefry. One shared connection makes activation and its outbox atomic.
final readonly class LaravelMailStore implements MailStore
{
    public function __construct(private Connection $db, private Encrypter $cipher) {}
    private function settings(bool $lock = false): object
    {
        $q = $this->db->table('oneqay_mail_settings')->where('id',1);
        return ($lock ? $q->lockForUpdate() : $q)->first() ?? throw new PlatformViolation('MAIL_SCHEMA_REQUIRED',503);
    }
    public function enqueue(string $tenant, string $key, string $recipient, string $subject, string $text, int $now): string
    {
        BusinessActivation::email($recipient); BusinessActivation::text($subject,200);
        if ($tenant === '' || strlen($tenant)>96 || strlen($text)>32768 || $text==='') throw new PlatformViolation('INVALID_MAIL');
        $dedupe = hash('sha256',$tenant.'|'.$key);
        $payload = json_encode(['recipient'=>$recipient,'subject'=>$subject,'text'=>$text],JSON_THROW_ON_ERROR);
        $fingerprint = hash('sha256',$payload);
        return $this->db->transaction(function () use ($tenant,$dedupe,$recipient,$payload,$fingerprint,$now): string {
            $this->settings(true);
            $old = $this->db->table('oneqay_mail_messages')->where('dedupe_key',$dedupe)->first();
            if ($old !== null) {
                if (!hash_equals($old->fingerprint,$fingerprint)) throw new PlatformViolation('MAIL_IDEMPOTENCY_CONFLICT',409);
                return $old->id;
            }
            $id=bin2hex(random_bytes(16)); $recipientHash=hash('sha256',strtolower($recipient));
            $suppressed=$this->db->table('oneqay_mail_suppressions')->where('recipient_hash',$recipientHash)->exists();
            $this->db->table('oneqay_mail_messages')->insert(['id'=>$id,'tenant_id'=>$tenant,'dedupe_key'=>$dedupe,'fingerprint'=>$fingerprint,'recipient_hash'=>$recipientHash,
                'payload'=>$this->cipher->encryptString($payload),'status'=>$suppressed?'suppressed':'queued','attempts'=>0,'next_attempt_at'=>$now,'created_at'=>$now,'updated_at'=>$now]);
            $this->event($id,$suppressed?'suppressed':'queued','OUTBOX_CREATED',$now);
            return $id;
        });
    }
    public function claim(int $now): ?array
    {
        return $this->db->transaction(function () use ($now): ?array {
            $settings=$this->settings(true);
            // Uncertain acceptance must not be retried automatically after a crash.
            $expired=$this->db->table('oneqay_mail_messages')->where('status','sending')->where('lease_until','<=',$now)->limit(100)->get();
            foreach($expired as $row) {
                $this->db->table('oneqay_mail_messages')->where('id',$row->id)->update(['status'=>'unknown','safe_code'=>'WORKER_LEASE_EXPIRED','lease'=>null,'lease_until'=>null,'updated_at'=>$now]);
                $this->event($row->id,'unknown','WORKER_LEASE_EXPIRED',$now);
            }
            $configuration=json_decode($settings->configuration,true,32,JSON_THROW_ON_ERROR);
            if (($configuration['enabled']??false)!==true || (int)$settings->paused_until>$now) return null;
            $slot=intdiv($now,60); $count=(int)$settings->minute_slot===$slot?(int)$settings->minute_count:0;
            if ($count >= (int)($configuration['per_minute']??20)) return null;
            $row=$this->db->table('oneqay_mail_messages')->whereIn('status',['queued','retry'])->where('next_attempt_at','<=',$now)->orderBy('next_attempt_at')->orderBy('id')->lockForUpdate()->first();
            if($row===null)return null;
            if($this->db->table('oneqay_mail_suppressions')->where('recipient_hash',$row->recipient_hash)->exists()) {
                $this->db->table('oneqay_mail_messages')->where('id',$row->id)->update(['status'=>'suppressed','updated_at'=>$now]);
                $this->event($row->id,'suppressed','RECIPIENT_SUPPRESSED',$now);
                // Next cron continues with the remaining queue; no spin on a suppressed row.
                return null;
            }
            $lease=bin2hex(random_bytes(16));
            $this->db->table('oneqay_mail_messages')->where('id',$row->id)->update(['status'=>'sending','attempts'=>(int)$row->attempts+1,'lease'=>$lease,'lease_until'=>$now+120,'updated_at'=>$now]);
            $this->db->table('oneqay_mail_settings')->where('id',1)->update(['minute_slot'=>$slot,'minute_count'=>$count+1]);
            return ['id'=>$row->id,'tenant_id'=>$row->tenant_id,'lease'=>$lease,'attempt'=>(int)$row->attempts+1] + json_decode($this->cipher->decryptString($row->payload),true,32,JSON_THROW_ON_ERROR);
        });
    }
    public function finish(array $message, string $outcome, string $code, int $now): void
    {
        if(!in_array($outcome,['accepted','temporary','permanent','unknown'],true) || !preg_match('/\A[A-Z0-9_.-]{1,64}\z/',$code)) throw new PlatformViolation('INVALID_TRANSPORT_RESULT');
        $this->db->transaction(function () use ($message,$outcome,$code,$now): void {
            $settings=$this->settings(true);
            $row=$this->db->table('oneqay_mail_messages')->where('id',$message['id'])->lockForUpdate()->first();
            if($row===null || $row->lease!==$message['lease'] || $row->status!=='sending') return; // A terminal feedback event can win the race.
            $status=match($outcome){'temporary'=>(int)$row->attempts>=5?'failed':'retry','permanent'=>'failed',default=>$outcome};
            $delay=min(21600,60*(2 ** max(0,(int)$row->attempts-1)))+random_int(0,30);
            $this->db->table('oneqay_mail_messages')->where('id',$row->id)->update(['status'=>$status,'safe_code'=>$code,'next_attempt_at'=>$now+$delay,'lease'=>null,'lease_until'=>null,'updated_at'=>$now]);
            $failures=$outcome==='accepted'?0:(int)$settings->failures+1;
            $this->db->table('oneqay_mail_settings')->where('id',1)->update(['failures'=>$failures,'paused_until'=>$failures>=3?$now+300:0]);
            $this->event($row->id,$status,$code,$now);
            // Only a definitive unknown-recipient result is a suppression, never generic 5xx/auth errors.
            if($code==='SMTP_5.1.1')$this->suppress($row->recipient_hash,'hard_bounce',$now);
        });
    }
    public function feedback(array $event, int $now): void
    {
        $id=BusinessActivation::text($event['message_id']??null,32);
        $eventId=BusinessActivation::text($event['event_id']??null,128);
        $type=$event['type']??null;
        if(!in_array($type,['delivered','hard_bounce','complaint','deferred','soft_bounce','failed'],true))throw new PlatformViolation('INVALID_FEEDBACK');
        $recipient=BusinessActivation::email($event['recipient']??null);
        $this->db->transaction(function () use ($id,$eventId,$type,$recipient,$now): void {
            $this->settings(true);
            $row=$this->db->table('oneqay_mail_messages')->where('id',$id)->lockForUpdate()->first();
            if($row===null || (int)$row->attempts<1 || !hash_equals($row->recipient_hash,hash('sha256',strtolower($recipient))))throw new PlatformViolation('FEEDBACK_MESSAGE_MISMATCH');
            $key=hash('sha256','feedback|'.$eventId);
            $existing=$this->db->table('oneqay_mail_events')->where('id',$key)->first();
            if($existing!==null) {
                if($existing->message_id!==$id || $existing->event!==$type)throw new PlatformViolation('FEEDBACK_EVENT_CONFLICT',409);
                return;
            }
            $this->db->table('oneqay_mail_events')->insert(['id'=>$key,'message_id'=>$id,'event'=>$type,'safe_code'=>'AUTHENTICATED_FEEDBACK','occurred_at'=>$now]);
            if(in_array($type,['hard_bounce','complaint'],true))$this->suppress($row->recipient_hash,$type,$now);
            // Late/out-of-order events can never erase a complaint or hard bounce.
            if(in_array($row->status,['complaint','hard_bounce','suppressed'],true))return;
            if($row->status==='delivered' && in_array($type,['deferred','soft_bounce','failed'],true))return;
            $this->db->table('oneqay_mail_messages')->where('id',$id)->update(['status'=>$type==='soft_bounce'?((int)$row->attempts<5?'retry':'failed'):$type,'next_attempt_at'=>$now+min(21600,300*(2 ** max(0,(int)$row->attempts-1)))+random_int(0,30),'lease'=>null,'lease_until'=>null,'updated_at'=>$now,'safe_code'=>'AUTHENTICATED_FEEDBACK']);
            // deferred means the receiving provider owns retry; do not send a duplicate from our queue.
        });
    }
    public function retry(string $id,string $actor,string $reason,bool $acceptDuplicateRisk,int $now): void
    {
        BusinessActivation::text($reason,300);
        $this->db->transaction(function () use ($id,$actor,$reason,$acceptDuplicateRisk,$now): void {
            $this->settings(true);
            $row=$this->db->table('oneqay_mail_messages')->where('id',$id)->lockForUpdate()->first();
            if($row===null || !in_array($row->status,['failed','unknown'],true))throw new PlatformViolation('MAIL_RETRY_DENIED',409);
            if($row->status==='unknown' && !$acceptDuplicateRisk)throw new PlatformViolation('DUPLICATE_RISK_CONFIRMATION_REQUIRED',409);
            if($this->db->table('oneqay_mail_suppressions')->where('recipient_hash',$row->recipient_hash)->exists())throw new PlatformViolation('RECIPIENT_SUPPRESSED',409);
            $this->db->table('oneqay_mail_messages')->where('id',$id)->update(['status'=>'queued','attempts'=>0,'next_attempt_at'=>$now,'safe_code'=>'OWNER_RETRY','updated_at'=>$now]);
            $this->event($id,'manual_retry','OWNER_RETRY',$now);
            $this->db->table('oneqay_platform_audit')->insert(['actor'=>$actor,'action'=>'mail.retry','business_id'=>$id,'reason'=>$reason,'occurred_at'=>$now]);
        });
    }
    public function report(int $page): array
    {
        $settings=$this->settings(); $counts=$this->db->table('oneqay_mail_messages')->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total','status')->all();
        return ['counts'=>$counts,'total'=>array_sum($counts),'heartbeat_at'=>(int)$settings->heartbeat_at,'paused_until'=>(int)$settings->paused_until,
            'oldest_queued_at'=>$this->db->table('oneqay_mail_messages')->whereIn('status',['queued','retry'])->min('created_at'),
            'suppressed_recipients'=>$this->db->table('oneqay_mail_suppressions')->count(),
            'messages'=>$this->db->table('oneqay_mail_messages')->orderByDesc('created_at')->orderBy('id')->offset((max(1,$page)-1)*25)->limit(25)->get(['id','tenant_id','status','attempts','safe_code','next_attempt_at','created_at','updated_at'])->map(fn($r)=>(array)$r)->all(),
            'events'=>$this->db->table('oneqay_mail_events')->orderByDesc('occurred_at')->orderBy('id')->limit(40)->get()->map(fn($r)=>(array)$r)->all()];
    }
    public function configuration(): array
    {
        $settings=$this->settings();
        $data=json_decode($settings->configuration,true,32,JSON_THROW_ON_ERROR);
        $data['password_configured']=isset($data['password']);unset($data['password']);
        return $data+['version'=>(int)$settings->version,'enabled'=>false,'host'=>'','port'=>465,'username'=>'','from'=>'','from_name'=>'oneQay','dkim_selector'=>'default','per_minute'=>20];
    }
    /** Secret access is restricted to the infrastructure transport adapter. */
    public function transportConfiguration(): array
    {
        $data=json_decode($this->settings()->configuration,true,32,JSON_THROW_ON_ERROR);
        if(isset($data['password']))$data['password']=$this->cipher->decryptString($data['password']);
        return $data;
    }
    public function configure(array $settings,int $version,string $actor,int $now): void
    {
        $allowed=['enabled','host','port','username','password','from','from_name','dkim_selector','per_minute'];
        if(array_diff(array_keys($settings),$allowed))throw new PlatformViolation('UNKNOWN_FIELDS');
        if(!is_bool($settings['enabled']??null) || !in_array($settings['port']??null,[465,587],true) || !is_int($settings['per_minute']??null) || $settings['per_minute']<1 || $settings['per_minute']>60)throw new PlatformViolation('INVALID_MAIL_SETTINGS');
        $settings['host']=strtolower(BusinessActivation::text($settings['host']??null,253));
        if(!preg_match('/\A(?=.{1,253}\z)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}\z/',$settings['host']))throw new PlatformViolation('INVALID_SMTP_HOST');
        foreach(['username'=>254,'from_name'=>120,'dkim_selector'=>63] as $key=>$max)$settings[$key]=BusinessActivation::text($settings[$key]??null,$max);
        if(!preg_match('/\A[a-zA-Z0-9_-]{1,63}\z/',$settings['dkim_selector']))throw new PlatformViolation('INVALID_DKIM_SELECTOR');
        $settings['from']=BusinessActivation::email($settings['from']??null);
        $password=$settings['password']??'';
        if(!is_string($password)||strlen($password)>4096)throw new PlatformViolation('INVALID_MAIL_PASSWORD');
        $this->db->transaction(function () use ($settings,$password,$version,$actor,$now): void {
            $old=$this->settings(true);
            if((int)$old->version!==$version)throw new PlatformViolation('STALE_VERSION',409);
            $prior=json_decode($old->configuration,true,32,JSON_THROW_ON_ERROR);
            $changed=($prior['host']??'')!==$settings['host'] || ($prior['username']??'')!==$settings['username'];
            if($password===''){
                if($changed || !isset($prior['password']))throw new PlatformViolation('SMTP_PASSWORD_REQUIRED');
                $settings['password']=$prior['password'];
            }else $settings['password']=$this->cipher->encryptString($password);
            $this->db->table('oneqay_mail_settings')->where('id',1)->update(['configuration'=>json_encode($settings,JSON_THROW_ON_ERROR),'version'=>$version+1]);
            $this->db->table('oneqay_platform_audit')->insert(['actor'=>$actor,'action'=>'mail.configure','business_id'=>'','reason'=>'SMTP configuration updated; secrets omitted','occurred_at'=>$now]);
        });
    }
    public function heartbeat(int $now): void { $this->db->table('oneqay_mail_settings')->where('id',1)->update(['heartbeat_at'=>$now]); }
    private function event(string $id,string $type,string $code,int $now): void
    { $this->db->table('oneqay_mail_events')->insert(['id'=>bin2hex(random_bytes(32)),'message_id'=>$id,'event'=>$type,'safe_code'=>$code,'occurred_at'=>$now]); }
    private function suppress(string $recipient,string $reason,int $now): void
    {
        $this->db->table('oneqay_mail_suppressions')->updateOrInsert(['recipient_hash'=>$recipient],['reason'=>$reason,'created_at'=>$now]);
        $this->db->table('oneqay_mail_messages')->where('recipient_hash',$recipient)->whereIn('status',['queued','retry'])->update(['status'=>'suppressed','safe_code'=>'RECIPIENT_SUPPRESSED','updated_at'=>$now]);
    }
}
