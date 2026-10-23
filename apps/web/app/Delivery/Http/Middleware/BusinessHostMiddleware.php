<?php
// Author by Lab | zefry. Hostnames never substitute for tenant membership.
declare(strict_types=1);
namespace App\Delivery\Http\Middleware;
use App\Infrastructure\BusinessExperience\LaravelBusinessDomains;
use App\Delivery\Http\Identity\FirstPartySessionKeys as K;
use Illuminate\Http\Request;
use Closure;
final readonly class BusinessHostMiddleware {
 public function __construct(private LaravelBusinessDomains $domains){}
 public function handle(Request $r,Closure $next):mixed {
  if(!config('platform.business_domains_enabled')||!$this->domains->installed())return $next($r);
  abort_unless(in_array(config('session.domain'),[null,''],true),503,'HOST_ONLY_SESSION_COOKIES_REQUIRED');
  $business=$this->domains->hostBusiness(strtolower($r->getHost()));$r->attributes->set('oneqay.host_business',$business);
  if($business){
   abort_if($r->is('platform','platform/*','owner-login'),404);
   $tenant=$r->session()->get(K::TENANT);abort_if(is_string($tenant)&&$tenant!==$business['tenant_id'],403,'BUSINESS_HOST_SCOPE_MISMATCH');
   if($r->is('/')&&$r->isMethod('GET'))return redirect('/pos');
  }
  $result=$next($r);
  if(!$business&&$r->is('pos')&&$r->isMethod('GET')&&$result->getStatusCode()===200){
   $tenant=$r->session()->get(K::TENANT);$p=is_string($tenant)?$this->domains->profile($tenant):null;
   if($p&&$p['url']&&$p['status']==='active'){
    $keys=[...K::all(),K::CREDENTIAL_EPOCH,K::MFA_VERIFIED_AT,K::MFA_FACTOR_EPOCH,K::SESSION_AUTHORITY_ID];$session=$r->session()->only($keys);
    abort_unless(is_string($session[K::SESSION_AUTHORITY_ID]??null),403);
    $token=bin2hex(random_bytes(32));$db=app('db')->connection();$db->table('oneqay_business_handoffs')->where('expires_at','<',time())->delete();
    $db->table('oneqay_business_handoffs')->insert(['token_hash'=>hash('sha256',$token),'business_id'=>$p['business_id'],'hostname'=>parse_url($p['url'],PHP_URL_HOST),'encrypted_session'=>app('encrypter')->encryptString(json_encode($session,JSON_THROW_ON_ERROR)),'expires_at'=>time()+45,'consumed_at'=>null]);
    $r->session()->invalidate();$r->session()->regenerateToken();return redirect($p['url'].'/business-entry#ticket='.$token)->withHeaders(['Referrer-Policy'=>'no-referrer','Cache-Control'=>'no-store']);
   }
  }
  return $result;
 }
}
