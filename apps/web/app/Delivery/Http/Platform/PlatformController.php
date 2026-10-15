<?php
declare(strict_types=1);
namespace App\Delivery\Http\Platform;
use App\Application\Platform\{PlatformOwnerAccess,PlatformStore,BusinessActivation,PlatformViolation};
use App\Application\Notification\{MailStore,MailTransport};
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\Request;
use Inertia\Inertia;
// Author by Lab | zefry
final readonly class PlatformController
{
    public function __construct(private PlatformOwnerAccess $access,private PlatformStore $store,private BusinessActivation $activation,private MailStore $mail,private MailTransport $transport, private ?\App\Application\Website\WebsiteStore $website = null) {}
    public function show(Request $r):mixed
    {
        return $this->respond($r,function ()use($r):mixed {
            $this->access->require();
            $businesses=$this->store->listing(max(1,$r->integer('page',1)),(string)$r->query('search',''));
            return Inertia::render('Platform/Control',['businesses'=>$businesses,'registrations'=>$this->website?->registrationDetails(array_column($businesses['rows'],'id'))??[],
                'mail'=>$this->mail->report(max(1,$r->integer('mail_page',1))),'settings'=>$this->mail->configuration(),
                'page'=>max(1,$r->integer('page',1)),'mail_page'=>max(1,$r->integer('mail_page',1)),
                'feedback_configured'=>strlen((string)config('platform.feedback_secret',''))>=32])->toResponse($r);
        });
    }
    public function command(Request $r):mixed { return $this->respond($r,fn()=>response()->json(['status'=>'saved','record'=>$this->activation->command($this->payload($r))])); }
    public function configure(Request $r):mixed
    {
        return $this->respond($r,function ()use($r):mixed {
            $owner=$this->access->require();$input=$this->payload($r);$version=$input['version']??null;$operation=BusinessActivation::text($input['operation_id']??null,128);unset($input['version'],$input['operation_id']);
            if(!is_int($version))throw new PlatformViolation('INVALID_VERSION');
            $this->store->once($owner->tenantId()->value().'/'.$owner->identityId()->value(),$operation,['action'=>'mail.configure','version'=>$version,'settings_digest'=>hash('sha256',json_encode($input,JSON_THROW_ON_ERROR))],function()use($input,$version,$owner):array{$this->mail->configure($input,$version,$owner->identityId()->value(),time());return ['saved'=>true];});
            return response()->json(['status'=>'saved']);
        });
    }
    public function inspect(Request $r):mixed { return $this->respond($r,function():mixed{$this->access->require();return response()->json($this->transport->inspect());}); }
    public function retry(Request $r):mixed
    {
        return $this->respond($r,function ()use($r):mixed {
            $owner=$this->access->require();$input=$this->payload($r);
            if(array_diff(array_keys($input),['id','reason','accept_duplicate_risk','operation_id']))throw new PlatformViolation('UNKNOWN_FIELDS');
            $operation=BusinessActivation::text($input['operation_id']??null,128);
            $this->store->once($owner->tenantId()->value().'/'.$owner->identityId()->value(),$operation,['action'=>'mail.retry','input'=>$input],function()use($input,$owner):array {
            $this->mail->retry(BusinessActivation::text($input['id']??null,32),$owner->identityId()->value(),BusinessActivation::text($input['reason']??null,300),($input['accept_duplicate_risk']??false)===true,time()); return ['saved'=>true]; });
            return response()->json(['status'=>'saved']);
        });
    }
    public function activate(Request $r):mixed
    {
        return $this->respond($r,function ()use($r):mixed {
            $input=$this->payload($r);
            if(array_diff(array_keys($input),['token','password']))throw new PlatformViolation('INVALID_ACTIVATION');
            $token=$input['token']??null;$password=$input['password']??null;
            if(!is_string($token)||!is_string($password))throw new PlatformViolation('INVALID_ACTIVATION');
            return response()->json(['status'=>'active','login_context'=>$this->activation->activate($token,$password)]);
        });
    }
    public function feedback(Request $r):mixed
    {
        return $this->respond($r,function ()use($r):mixed {
            $raw=$r->getContent();$stamp=$r->header('X-OneQay-Timestamp','');$signature=$r->header('X-OneQay-Signature','');$secret=(string)config('platform.feedback_secret','');
            if(strlen($raw)>8192||strlen($secret)<32||!preg_match('/\A[0-9]{10}\z/',$stamp)||abs(time()-(int)$stamp)>300
                ||!hash_equals(hash_hmac('sha256',$stamp.'.'.$raw,$secret),$signature))throw new PlatformViolation('FEEDBACK_AUTHENTICATION_FAILED',401);
            $payload=json_decode($raw,true,16,JSON_THROW_ON_ERROR);
            if(!is_array($payload)||array_diff(array_keys($payload),['event_id','message_id','recipient','type']))throw new PlatformViolation('INVALID_FEEDBACK');
            $this->mail->feedback($payload,time());return response()->json(['status'=>'recorded']);
        });
    }
    private function payload(Request $r):array {if(strlen($r->getContent())>8192)throw new PlatformViolation('REQUEST_TOO_LARGE',413);return $r->except('_token');}
    private function respond(Request $r,callable $work):mixed
    {
        try{$response=$work();$response->headers->set('Cache-Control','no-store, private');$response->headers->set('Referrer-Policy','no-referrer');return $response;}
        catch(PlatformViolation $e){$code=$e->reason;$status=$e->status;}
        catch(\JsonException|\InvalidArgumentException){$code='INVALID_FIELDS';$status=422;}
        catch(\Throwable $e){\Illuminate\Support\Facades\Log::error('Platform operation failed', ['exception_class'=>$e::class]);$code='PLATFORM_OPERATION_UNAVAILABLE';$status=503;}
        return response()->json(SafeErrorEnvelope::make($code,(string)$r->attributes->get('oneqay.correlation_id','correlation-missing')),$status,['Cache-Control'=>'no-store, private']);
    }
}
