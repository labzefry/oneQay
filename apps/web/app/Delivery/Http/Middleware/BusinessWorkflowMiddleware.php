<?php
// Author by Lab | zefry. A hidden menu is not an authorization boundary.
declare(strict_types=1);
namespace App\Delivery\Http\Middleware;
use Illuminate\Http\Request;
use Closure;
final class BusinessWorkflowMiddleware {
 public function handle(Request $r,Closure $next):mixed {
  $tenant=$r->session()->get(\App\Delivery\Http\Identity\FirstPartySessionKeys::TENANT);
  if(is_string($tenant)&&$r->is('pos','pos/*')){
   $p=app(\App\Infrastructure\BusinessExperience\LaravelBusinessDomains::class)->profile($tenant);
   if($p&&in_array($p['business_type'],\App\Application\BusinessExperience\ServiceWorkflow::MODULES,true)){
    if($r->is('pos')&&$r->isMethod('GET')){ $response=$next($r); return $response; }
    abort_unless($r->is('pos/business-handoff','pos/service','pos/service/*','pos/business/security','pos/business/security/*','pos/business/settings','pos/business/access','pos/business/access/*','pos/business/people','pos/business/people/*','pos/business/desktop','pos/business/desktop/*','pos/desktop'),403,'BUSINESS_WORKFLOW_MISMATCH');
   }
  }return $next($r);
 }
}
