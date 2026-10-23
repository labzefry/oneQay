<?php
// Author by Lab | zefry.
declare(strict_types=1);
namespace App\Delivery\Http\BusinessExperience;
use App\Infrastructure\BusinessExperience\LaravelBusinessDomains;
use App\Application\Platform\PlatformViolation;
use App\Delivery\Http\Identity\FirstPartySessionKeys as K;
use Illuminate\Http\Request;
use Inertia\Inertia;
final readonly class BusinessExperienceController {
 public function __construct(private LaravelBusinessDomains $domains){}
 public function availability(Request $r):mixed{return $this->respond(fn()=>response()->json($this->domains->availability((string)$r->query('slug',''))));}
 public function domain(Request $r):mixed{app(\App\Application\Platform\PlatformOwnerAccess::class)->require();$b=app(\App\Application\Platform\PlatformStore::class)->business((string)$r->query('id',''));return response()->json($this->domains->profile($b['tenant_id']));}
 public function rename(Request $r):mixed{return $this->respond(fn()=>response()->json($this->domains->rename($r->except('_token'))));}
 public function entry(Request $r):mixed{
  $b=$r->attributes->get('oneqay.host_business');abort_unless($b,404);
  if($r->query('begin')==='1'){$state=bin2hex(random_bytes(32));$r->session()->put(['oneqay.handoff.state'=>$state,'oneqay.handoff.expires'=>time()+120]);$root=\App\Application\BusinessExperience\BusinessDomain::root((string)config('platform.public_url'));return redirect('https://'.$root.'/pos/business-handoff?target='.rawurlencode((string)$b['id']).'&state='.$state)->withHeaders(['Referrer-Policy'=>'no-referrer','Cache-Control'=>'no-store']);}
  return Inertia::render('Platform/BusinessEntry')->toResponse($r)->withHeaders(['Referrer-Policy'=>'no-referrer','Cache-Control'=>'no-store']);
 }
 public function issue(Request $r):mixed{
  abort_unless(config('platform.business_domains_enabled')&&!$r->attributes->get('oneqay.host_business'),404);
  $state=$r->query('state');$p=$this->domains->profile((string)$r->session()->get(K::TENANT));abort_unless($p&&$p['status']==='active'&&$p['business_id']===$r->query('target')&&$p['url']&&is_string($state)&&preg_match('/\A[a-f0-9]{64}\z/D',$state),403);
  $session=[];foreach([...K::all(),K::CREDENTIAL_EPOCH,K::MFA_VERIFIED_AT,K::MFA_FACTOR_EPOCH,K::SESSION_AUTHORITY_ID]as$key){if($r->session()->has($key))$session[$key]=$r->session()->get($key);}
  abort_unless(is_string($session[K::SESSION_AUTHORITY_ID]??null),403);$token=bin2hex(random_bytes(32));$db=app('db')->connection();$db->table('oneqay_business_handoffs')->where('expires_at','<',time())->delete();
  $db->table('oneqay_business_handoffs')->insert(['token_hash'=>hash('sha256',$token),'business_id'=>$p['business_id'],'hostname'=>parse_url($p['url'],PHP_URL_HOST),'state_hash'=>hash('sha256',$state),'encrypted_session'=>app('encrypter')->encryptString(json_encode($session,JSON_THROW_ON_ERROR)),'expires_at'=>time()+45,'consumed_at'=>null]);
  $r->session()->invalidate();$r->session()->regenerateToken();return redirect($p['url'].'/business-entry#ticket='.$token)->withHeaders(['Referrer-Policy'=>'no-referrer','Cache-Control'=>'no-store']);
 }
 public function consume(Request $r):mixed {
  $b=$r->attributes->get('oneqay.host_business');$ticket=$r->input('ticket');abort_unless($b&&is_string($ticket)&&preg_match('/\A[a-f0-9]{64}\z/D',$ticket),401);
  app('db')->connection()->transaction(function()use($r,$b,$ticket){$db=app('db')->connection();$row=$db->table('oneqay_business_handoffs')->where('token_hash',hash('sha256',$ticket))->lockForUpdate()->first();abort_unless($row&&$row->business_id===$b['id']&&$row->hostname===$r->getHost()&&$row->expires_at>=time()&&$row->consumed_at===null&&is_string($r->session()->get('oneqay.handoff.state'))&&hash_equals($row->state_hash,hash('sha256',$r->session()->get('oneqay.handoff.state')))&&(int)$r->session()->get('oneqay.handoff.expires')>=time(),401);
   $session=json_decode(app('encrypter')->decryptString($row->encrypted_session),true,32,JSON_THROW_ON_ERROR);abort_unless(($session[K::TENANT]??null)===$b['tenant_id']&&is_string($session[K::SESSION_AUTHORITY_ID]??null),401);
   $db->table('oneqay_business_handoffs')->where('token_hash',$row->token_hash)->update(['consumed_at'=>time(),'encrypted_session'=>'']);$r->session()->invalidate();$r->session()->put($session);$r->session()->regenerateToken();
  });return response()->json(['redirect'=>'/pos'])->withHeaders(['Cache-Control'=>'no-store','Referrer-Policy'=>'no-referrer']);
 }
 private function respond(callable $f):mixed{try{return $f();}catch(PlatformViolation $e){return response()->json(['code'=>$e->reason],$e->status,['Cache-Control'=>'no-store']);}}
}
