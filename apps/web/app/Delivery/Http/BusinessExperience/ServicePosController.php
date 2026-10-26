<?php
// Author by Lab | zefry.
declare(strict_types=1);
namespace App\Delivery\Http\BusinessExperience;
use App\Infrastructure\BusinessExperience\LaravelServicePos;
use App\Application\Platform\PlatformViolation;
use Illuminate\Http\Request;
use Inertia\Inertia;
final readonly class ServicePosController {
 public function __construct(private LaravelServicePos $pos){}
 public function page(Request $r):mixed{return Inertia::render('Business/ServicePos',['initial'=>$this->pos->read(),'destinations'=>array_values(array_filter(app(\App\Application\Business\BusinessAccess::class)->modules(),fn($m)=>in_array($m['key'],['business_settings','business_people','business_access','business_desktop'],true)))])->toResponse($r)->withHeaders(['Cache-Control'=>'no-store']);}
 public function read(Request $r):mixed{return response()->json($this->pos->read((int)$r->query('page',1)),200,['Cache-Control'=>'no-store']);}
 public function command(Request $r):mixed{abort_if(strlen($r->getContent())>8192,413);try{return response()->json(['record'=>$this->pos->command($r->except('_token')),'workspace'=>$this->pos->read()],200,['Cache-Control'=>'no-store']);}catch(PlatformViolation $e){return response()->json(['code'=>$e->reason],$e->status,['Cache-Control'=>'no-store']);}}
 public function reconcile(Request $r):mixed{abort_if(strlen($r->getContent())>8192,413);try{return response()->json(['record'=>$this->pos->reconcile($r->except('_token')),'workspace'=>$this->pos->read()],200,['Cache-Control'=>'no-store']);}catch(PlatformViolation $e){return response()->json(['code'=>$e->reason],$e->status,['Cache-Control'=>'no-store']);}}
 public function nativeReconcile(Request $r):mixed{abort_if(strlen($r->getContent())>8192,413);return $this->native($r,function()use($r){$in=$r->all();abort_if(($in['action']??'')==='resource.save',403);return ['record'=>$this->pos->reconcile($in),'workspace'=>$this->pos->read()];});}
 public function nativeRead(Request $r):mixed{return $this->native($r,fn()=>$this->pos->read((int)$r->query('page',1)));}
 public function nativeCommand(Request $r):mixed {abort_if(strlen($r->getContent())>8192,413);return $this->native($r,function()use($r){$input=$r->all();abort_unless(!array_diff(array_keys($input),['command','lease_id','created_at'])&&is_array($input['command']??null)&&is_string($input['lease_id']??null)&&is_int($input['created_at']??null),422);abort_if(($input['command']['action']??'')==='resource.save',403);$d=app(\App\Infrastructure\Pos\LaravelDesktopPosService::class);$lease=app('db')->table('oneqay_desktop_leases')->where('tenant_id',$d->scope()['tenant_id'])->where('scope_hash',$d->scopeHash())->where('id',$input['lease_id'])->where('shift_id','service-v1')->first();abort_unless($lease&&$input['created_at']>=$lease->issued_at&&$input['created_at']<=$lease->expires_at&&$input['created_at']<=time()+60,403);return ['record'=>$this->pos->command($input['command']),'workspace'=>$this->pos->read()];});}
 private function native(Request $r,callable $f):mixed{$d=app(\App\Infrastructure\Pos\LaravelDesktopPosService::class);try{$d->authenticate((string)$r->bearerToken());abort_unless(app(\App\Infrastructure\Pos\LaravelDesktopBusinessSettings::class)->cashierForScope($d->context()),403);return response()->json($f(),200,['Cache-Control'=>'no-store']);}catch(PlatformViolation $e){return response()->json(['code'=>$e->reason],$e->status,['Cache-Control'=>'no-store']);}finally{$d->clear();}}
}
