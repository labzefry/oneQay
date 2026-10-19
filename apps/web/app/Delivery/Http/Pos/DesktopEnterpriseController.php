<?php
// Author by Lab | zefry
declare(strict_types=1);
namespace App\Delivery\Http\Pos;
use App\Infrastructure\Pos\{LaravelDesktopPosService,DesktopBusinessSettings,DesktopCashierLogin,DesktopOperations};
use Illuminate\Http\Request;
use Inertia\Inertia;
final readonly class DesktopEnterpriseController {
 public function __construct(private LaravelDesktopPosService $desktop,private DesktopBusinessSettings $settings,private DesktopCashierLogin $login,private DesktopOperations $operations){}
 public function settings(Request $r):mixed {return Inertia::render('Business/DesktopSettings',$this->settings->page())->toResponse($r)->withHeaders(['Cache-Control'=>'no-store']);}
 public function saveSettings(Request $r):mixed {abort_unless(strlen($r->getContent())<=65536,422);return response()->json($this->settings->save($r->except('_token')),200,['Cache-Control'=>'no-store']);}
 public function cashier(Request $r):mixed {abort_unless(strlen($r->getContent())<=4096,422);return response()->json($this->settings->cashier($r->except('_token')),200,['Cache-Control'=>'no-store']);}
 public function login(Request $r):mixed {abort_unless(strlen($r->getContent())<=4096,422);try{return response()->json($this->login->login($r->all(),(string)$r->attributes->get('oneqay.correlation_id')),200,['Cache-Control'=>'no-store']);}finally{$this->desktop->clear();}}
 public function snapshot(Request $r):mixed {return $this->authorized($r,fn()=>$this->desktop->snapshot(true));}
 public function sync(Request $r):mixed {abort_unless(strlen($r->getContent())<=1048576&&is_array($r->input('operations')),422);return $this->authorized($r,fn()=>$this->operations->sync($r->input('operations'),(string)$r->attributes->get('oneqay.correlation_id')));}
 private function authorized(Request $r,callable $work):mixed {try{$this->desktop->authenticate((string)$r->bearerToken());abort_unless($this->settings->cashierForScope($this->desktop->context()),403);return response()->json($work(),200,['Cache-Control'=>'no-store']);}finally{$this->desktop->clear();}}
}
