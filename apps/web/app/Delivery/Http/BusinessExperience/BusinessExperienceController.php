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
 public function entry(Request $r):mixed{abort_unless($r->attributes->get('oneqay.host_business'),404);return Inertia::render('Platform/BusinessEntry')->toResponse($r)->withHeaders(['Referrer-Policy'=>'no-referrer','Cache-Control'=>'no-store']);}
 public function consume(Request $r):mixed {
  $b=$r->attributes->get('oneqay.host_business');$ticket=$r->input('ticket');abort_unless($b&&is_string($ticket)&&preg_match('/\A[a-f0-9]{64}\z/D',$ticket),401);
  app('db')->connection()->transaction(function()use($r,$b,$ticket){$db=app('db')->connection();$row=$db->table('oneqay_business_handoffs')->where('token_hash',hash('sha256',$ticket))->lockForUpdate()->first();abort_unless($row&&$row->business_id===$b['id']&&$row->hostname===$r->getHost()&&$row->expires_at>=time()&&$row->consumed_at===null,401);
   $session=json_decode(app('encrypter')->decryptString($row->encrypted_session),true,32,JSON_THROW_ON_ERROR);abort_unless(($session[K::TENANT]??null)===$b['tenant_id']&&is_string($session[K::SESSION_AUTHORITY_ID]??null),401);
   $db->table('oneqay_business_handoffs')->where('token_hash',$row->token_hash)->update(['consumed_at'=>time(),'encrypted_session'=>'']);$r->session()->invalidate();$r->session()->put($session);$r->session()->regenerateToken();
  });return response()->json(['redirect'=>'/pos'])->withHeaders(['Cache-Control'=>'no-store','Referrer-Policy'=>'no-referrer']);
 }
 private function respond(callable $f):mixed{try{return $f();}catch(PlatformViolation $e){return response()->json(['code'=>$e->reason],$e->status,['Cache-Control'=>'no-store']);}}
}
