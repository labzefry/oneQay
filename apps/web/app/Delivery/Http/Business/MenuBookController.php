<?php
// Author by Lab | zefry. Only authenticated business catalog managers may edit menu metadata.
declare(strict_types=1);
namespace App\Delivery\Http\Business;
use App\Application\Authorization\DurableAuthorizationViolation;
use App\Application\Business\{BusinessAccess,BusinessInput,BusinessViolation,Merchandising};
use App\Delivery\Http\SafeErrorEnvelope;
use App\Infrastructure\Business\{LaravelMenuBookCatalog,LaravelMenuBookReader,LaravelMenuBookMedia};
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;
final readonly class MenuBookController {
 public function __construct(private LaravelMenuBookCatalog $menu,private Merchandising $products,private BusinessAccess $access,private LaravelMenuBookReader $reader,private LaravelMenuBookMedia $images){}
 private function correlation(Request $r):string{return (string)$r->attributes->get('oneqay.correlation_id','correlation-missing');}
 private function respond(Request $r,callable $action):mixed{
  try{$v=$action();if(method_exists($v,'toResponse'))$v=$v->toResponse($r);$v->headers->set('Cache-Control','no-store, private');return $v;}
  catch(DurableAuthorizationViolation){$code='BUSINESS_PERMISSION_DENIED';$status=403;}
  catch(BusinessViolation $e){$code=$e->reason;$status=$e->status;}
  catch(QueryException $e){$conflict=str_starts_with((string)($e->errorInfo[0]??''),'23');$code=$conflict?'DUPLICATE_OR_INVALID_REFERENCE':'BUSINESS_STORAGE_UNAVAILABLE';$status=$conflict?409:503;}
  return response()->json(SafeErrorEnvelope::make($code,$this->correlation($r)),$status,['Cache-Control'=>'no-store, private']);
 }
 private function input(Request $r):array{if(strlen($r->getContent())>100000)throw new BusinessViolation('REQUEST_TOO_LARGE',413);$v=$r->except('_token');BusinessInput::fields($v,['operation_id','data']);if(!is_array($v['data']))throw new BusinessViolation('INVALID_FIELDS');return $v;}
 public function show(Request $r):mixed{return $this->respond($r,function()use($r){$page=BusinessInput::integer($r->query('page','1'),1,10000);$q=BusinessInput::text($r->query('q',''),120,true);$products=$this->products->products(['q'=>$q],$page);return Inertia::render('Business/MenuBook',['products'=>$products,'publication'=>$this->menu->publication(),'media'=>$this->images->listing(),'categories'=>$this->menu->categories(),'items'=>$this->menu->items(array_column($products['data'],'product_id')),'filters'=>['q'=>$q],'preferences'=>$this->products->preferences(),'guest_ordering_enabled'=>(bool)config('oneqay.menu_guest_ordering.enabled',false)]);});}
 public function preview(Request $r):mixed{return $this->respond($r,function(){$s=$this->access->require('pos.catalog.prepare');$scope=['tenant_id'=>$s->tenantId()->value(),'organization_id'=>$s->organizationId()->value(),'outlet_id'=>$s->outletId()->value()];$view=$this->reader->read($scope,false);return Inertia::render('Website/PublicMenuBook',$view+['preview'=>true]);});}
 public function publication(Request $r):mixed{return $this->respond($r,function()use($r){$v=$this->input($r);return response()->json(['record'=>$this->menu->savePublication(BusinessInput::text($v['operation_id'],80),$v['data'],$this->correlation($r))]);});}
 public function category(Request $r):mixed{return $this->respond($r,function()use($r){$v=$this->input($r);return response()->json(['record'=>$this->menu->createCategory(BusinessInput::text($v['operation_id'],80),$v['data'],$this->correlation($r))]);});}
 public function assign(Request $r):mixed{return $this->respond($r,function()use($r){$v=$this->input($r);return response()->json(['record'=>$this->menu->assign(BusinessInput::text($v['operation_id'],80),$v['data'],$this->correlation($r))]);});}
 public function uploadMedia(Request $r):mixed{return $this->respond($r,function()use($r){
  $this->access->require('pos.catalog.prepare');
  $file=$r->file('image');
  if(!$file instanceof \Illuminate\Http\UploadedFile||!$file->isValid())throw new BusinessViolation('MENU_IMAGE_INVALID',422);
  if($file->getSize()>LaravelMenuBookMedia::MAX_BYTES)throw new BusinessViolation('MENU_IMAGE_SIZE_INVALID',422);
  $bytes=file_get_contents($file->getRealPath());if($bytes===false)throw new BusinessViolation('MENU_IMAGE_INVALID',422);
  return response()->json(['record'=>$this->images->store($bytes,$this->correlation($r))]);
 });}
 public function media(Request $r,string $id):mixed{return $this->respond($r,function()use($id){
  $image=$this->images->privateImage($id);abort_unless($image!==null,404);return MenuImageResponse::make($image);
 });}

 public function removeMedia(Request $r):mixed{return $this->respond($r,function()use($r){
  $v=$this->input($r);BusinessInput::fields($v['data'],['id']);
  return response()->json(['record'=>$this->images->remove(BusinessInput::text($v['operation_id'],80),BusinessInput::text($v['data']['id'],32),$this->correlation($r))]);
 });}

}
