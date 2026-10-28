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
  if(app()->environment('production'))abort_unless(config('session.secure')===true,503,'SECURE_SESSION_COOKIES_REQUIRED');
  try{$business=$this->domains->hostBusiness(strtolower($r->getHost()));}catch(\App\Application\Platform\PlatformViolation $e){abort($e->reason==='SUBDOMAIN_INVALID'?404:$e->status,$e->reason);}$r->attributes->set('oneqay.host_business',$business);
  if($business){
   abort_if($r->is('platform','platform/*','owner-login'),404);
   $tenant=$r->session()->get(K::TENANT);abort_if(is_string($tenant)&&$tenant!==$business['tenant_id'],403,'BUSINESS_HOST_SCOPE_MISMATCH');
   if($r->is('/')&&$r->isMethod('GET'))return redirect('/pos');
  }
  $result=$next($r);
  if(!$business&&$r->is('pos')&&$r->isMethod('GET')&&$result->getStatusCode()===200){
   $tenant=$r->session()->get(K::TENANT);$p=is_string($tenant)?$this->domains->profile($tenant):null;
   if($p&&$p['url']&&$p['status']==='active'){
    return redirect($p['url'].'/business-entry?begin=1')->withHeaders(['Referrer-Policy'=>'no-referrer','Cache-Control'=>'no-store']);
   }
  }
  return $result;
 }
}
