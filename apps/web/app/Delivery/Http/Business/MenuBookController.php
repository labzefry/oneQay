<?php
// Author by Lab | zefry. Only authenticated business catalog managers may edit menu metadata.
declare(strict_types=1);
namespace App\Delivery\Http\Business;
use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Business\{BusinessInput,BusinessViolation,Merchandising};
use App\Delivery\Http\SafeErrorEnvelope;
use App\Infrastructure\Business\LaravelMenuBookCatalog;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;
final readonly class MenuBookController {
 public function __construct(private LaravelMenuBookCatalog $menu,private Merchandising $products){}
 private function correlation(Request $r):string{return (string)$r->attributes->get('oneqay.correlation_id','correlation-missing');}
 private function respond(Request $r,callable $action):mixed{
  try{$v=$action();if(method_exists($v,'toResponse'))$v=$v->toResponse($r);$v->headers->set('Cache-Control','no-store, private');return $v;}
  catch(DurableAuthorizationViolation){$code='BUSINESS_PERMISSION_DENIED';$status=403;}
  catch(BusinessViolation $e){$code=$e->reason;$status=$e->status;}
  catch(QueryException $e){$conflict=str_starts_with((string)($e->errorInfo[0]??''),'23');$code=$conflict?'DUPLICATE_OR_INVALID_REFERENCE':'BUSINESS_STORAGE_UNAVAILABLE';$status=$conflict?409:503;}
  return response()->json(SafeErrorEnvelope::make($code,$this->correlation($r)),$status,['Cache-Control'=>'no-store, private']);
 }
 private function input(Request $r):array{if(strlen($r->getContent())>100000)throw new BusinessViolation('REQUEST_TOO_LARGE',413);$v=$r->except('_token');BusinessInput::fields($v,['operation_id','data']);if(!is_array($v['data']))throw new BusinessViolation('INVALID_FIELDS');return $v;}
 public function show(Request $r):mixed{return $this->respond($r,function()use($r){$page=BusinessInput::integer($r->query('page','1'),1,10000);$q=BusinessInput::text($r->query('q',''),120,true);$products=$this->products->products(['q'=>$q],$page);return Inertia::render('Business/MenuBook',['products'=>$products,'publication'=>$this->menu->publication(),'categories'=>$this->menu->categories(),'items'=>$this->menu->items(array_column($products['data'],'product_id')),'filters'=>['q'=>$q],'preferences'=>$this->products->preferences()]);});}
 public function publication(Request $r):mixed{return $this->respond($r,function()use($r){$v=$this->input($r);return response()->json(['record'=>$this->menu->savePublication(BusinessInput::text($v['operation_id'],80),$v['data'],$this->correlation($r))]);});}
 public function category(Request $r):mixed{return $this->respond($r,function()use($r){$v=$this->input($r);return response()->json(['record'=>$this->menu->createCategory(BusinessInput::text($v['operation_id'],80),$v['data'],$this->correlation($r))]);});}
 public function assign(Request $r):mixed{return $this->respond($r,function()use($r){$v=$this->input($r);return response()->json(['record'=>$this->menu->assign(BusinessInput::text($v['operation_id'],80),$v['data'],$this->correlation($r))]);});}
}
