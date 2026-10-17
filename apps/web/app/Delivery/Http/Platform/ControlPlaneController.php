<?php
declare(strict_types=1);
namespace App\Delivery\Http\Platform;
use App\Application\Platform\{ControlPlaneManagement,PlatformViolation};
use App\Delivery\Http\SafeErrorEnvelope;
use Illuminate\Http\Request;
// Author by Lab | zefry
final readonly class ControlPlaneController {
 public function __construct(private ControlPlaneManagement $control){}
 public function overview(Request $r):mixed{return $this->respond($r,fn()=> $this->control->dashboard());}
 public function business(Request $r,string $id):mixed{return $this->respond($r,fn()=> $this->control->detail($id));}
 public function preview(Request $r,string $id):mixed{return $this->respond($r,fn()=> $this->control->preview($id));}
 public function command(Request $r):mixed{return $this->respond($r,function()use($r):array{if(strlen($r->getContent())>20000)throw new PlatformViolation('REQUEST_TOO_LARGE',413);return ['status'=>'saved','record'=>$this->control->command($r->except('_token'))];});}
 private function respond(Request $r,callable $work):mixed{
  try{return response()->json($work(),200,['Cache-Control'=>'no-store, private']);}
  catch(PlatformViolation $e){$code=$e->reason;$status=$e->status;}
  catch(\InvalidArgumentException|\JsonException){$code='INVALID_FIELDS';$status=422;}
  catch(\Throwable $e){\Illuminate\Support\Facades\Log::error('Control plane operation failed',['exception_class'=>$e::class]);$code='CONTROL_PLANE_UNAVAILABLE';$status=503;}
  return response()->json(SafeErrorEnvelope::make($code,(string)$r->attributes->get('oneqay.correlation_id','correlation-missing')),$status,['Cache-Control'=>'no-store, private']);
 }
}
