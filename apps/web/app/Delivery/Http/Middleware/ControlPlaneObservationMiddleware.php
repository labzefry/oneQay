<?php
declare(strict_types=1);
namespace App\Delivery\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Infrastructure\Platform\ControlPlaneTelemetry;
// Author by Lab | zefry. Telemetry failure never replaces the actual application response.
final readonly class ControlPlaneObservationMiddleware {
 public function __construct(private ControlPlaneTelemetry $telemetry){}
 public function handle(Request $r,Closure $next):mixed{
  $start=hrtime(true);$response=null;
  try{return $response=$next($r);}
  finally{try{$code='';if($response instanceof \Illuminate\Http\JsonResponse){$data=$response->getData(true);$candidate=$data['error']['code']??'';if(is_string($candidate))$code=$candidate;}$this->telemetry->record($response?->getStatusCode()??500,(int)((hrtime(true)-$start)/1000000),(string)$r->route()?->getName(),(string)$r->attributes->get('oneqay.correlation_id','unavailable'),$code);}catch(\Throwable){}}
 }
}
