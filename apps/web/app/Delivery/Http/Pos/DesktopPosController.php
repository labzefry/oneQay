<?php
// Author by Lab | zefry

declare(strict_types=1);
namespace App\Delivery\Http\Pos;
use App\Infrastructure\Pos\DesktopPosService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
final readonly class DesktopPosController {
 public function __construct(private DesktopPosService $desktop){}
 public function page():Response{return response()->view('pos.desktop');}
 public function pair(Request $r):Response{$pairing=$this->desktop->pair($r);return $r->expectsJson()?response()->json($pairing,200,['Cache-Control'=>'no-store']):response()->view('pos.desktop',compact('pairing'),200,['Cache-Control'=>'no-store']);}
 public function review(Request $r):Response{$operations=$this->desktop->review();return $r->expectsJson()?response()->json(['operations'=>$operations],200,['Cache-Control'=>'no-store']):response()->view('pos.desktop-review',compact('operations'),200,['Cache-Control'=>'no-store']);}
 public function exchange(Request $r):Response{try{return response()->json($this->desktop->exchange((string)$r->input('code')),200,['Cache-Control'=>'no-store']);}finally{$this->desktop->clear();}}
 public function snapshot(Request $r):Response{return $this->authorized($r,fn()=>$this->desktop->snapshot());}
 public function sync(Request $r):Response{abort_unless(strlen($r->getContent())<=1048576&&is_array($r->input('operations')),422);return $this->authorized($r,fn()=>$this->desktop->sync($r->input('operations',[]),(string)$r->attributes->get('oneqay.correlation_id')));}
 private function authorized(Request $r,callable $work):Response{try{$this->desktop->authenticate((string)$r->bearerToken());return response()->json($work(),200,['Cache-Control'=>'no-store']);}finally{$this->desktop->clear();}}
}
