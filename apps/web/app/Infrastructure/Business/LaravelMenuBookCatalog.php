<?php
// Author by Lab | zefry. Authenticated owner-facing classification only; no public order or checkout.
declare(strict_types=1);
namespace App\Infrastructure\Business;
use App\Application\Business\{BusinessAccess,BusinessInput,BusinessRepository,BusinessViolation,MenuBookAssignments,MerchandisingRepository};
use App\Application\Organization\VerifiedOrganizationalContext;
use Illuminate\Database\Connection;
final readonly class LaravelMenuBookCatalog implements MenuBookAssignments {
 public function __construct(private Connection $db,private BusinessAccess $access,private BusinessRepository $operations,private MerchandisingRepository $products){}
 private function scope(VerifiedOrganizationalContext $s):array{return ['tenant_id'=>$s->tenantId()->value(),'organization_id'=>$s->organizationId()->value(),'outlet_id'=>$s->outletId()->value()];}
 public function installed():bool{return $this->db->getSchemaBuilder()->hasTable('oneqay_menu_book_publication')&&$this->db->getSchemaBuilder()->hasTable('oneqay_menu_book_categories')&&$this->db->getSchemaBuilder()->hasTable('oneqay_menu_book_items')&&$this->db->getSchemaBuilder()->hasTable('oneqay_menu_book_media')&&$this->db->getSchemaBuilder()->hasColumn('oneqay_menu_book_publication','cover_id');}
 private function ready():void{if(!$this->installed())throw new BusinessViolation('MENU_BOOK_SCHEMA_REQUIRED',503);}
 public function categories():array{
  $s=$this->access->require('pos.catalog.prepare');$this->ready();return $this->db->table('oneqay_menu_book_categories')->where($this->scope($s))->orderBy('name')->orderBy('id')->limit(200)->get(['id','name'])->map(fn($r)=>(array)$r)->all();
 }
 public function createCategory(string $operation,array $input,string $correlation):array{
  $s=$this->access->require('pos.catalog.prepare');$this->ready();BusinessInput::fields($input,['name']);$name=preg_replace('/\s+/u',' ',BusinessInput::text($input['name'],120));$key=hash('sha256',mb_strtolower($name,'UTF-8'));
  return $this->operations->once($s,BusinessInput::text($operation,80),'menu.category.create',['name'=>$name],$correlation,function()use($s,$name,$key){$scope=$this->scope($s);$this->db->table('oneqay_organizations')->where('tenant_id',$scope['tenant_id'])->where('id',$scope['organization_id'])->lockForUpdate()->firstOrFail();$table=$this->db->table('oneqay_menu_book_categories');$old=(clone $table)->where($scope)->where('name_key',$key)->first();if($old)return ['id'=>$old->id,'name'=>$old->name];if((clone $table)->where($scope)->count()>=200)throw new BusinessViolation('MENU_CATEGORY_LIMIT',409);$id=bin2hex(random_bytes(16));$table->insert($scope+['id'=>$id,'name'=>$name,'name_key'=>$key,'created_at'=>time()]);return ['id'=>$id,'name'=>$name];});
 }
 public function assign(string $operation,array $input,string $correlation):array{
  $s=$this->access->require('pos.catalog.prepare');$this->ready();BusinessInput::fields($input,['product_id','category_id','visible','spotlight','version']);$product=BusinessInput::text($input['product_id'],64);$category=BusinessInput::text($input['category_id'],32);if(!preg_match('/\A[a-f0-9]{32}\z/D',$category))throw new BusinessViolation('MENU_CATEGORY_INVALID');$visible=BusinessInput::boolean($input['visible']);$spotlight=BusinessInput::boolean($input['spotlight']);$version=BusinessInput::integer($input['version'],0,1000000000);$data=['product_id'=>$product,'category_id'=>$category,'visible'=>$visible,'spotlight'=>$spotlight,'version'=>$version];
  return $this->operations->once($s,BusinessInput::text($operation,80),'menu.product.assign',$data,$correlation,function()use($s,$data,$product,$category,$visible,$spotlight,$version){$scope=$this->scope($s);if(!$this->products->product($s,$product,true))throw new BusinessViolation('PRODUCT_NOT_FOUND',404);if(!$this->db->table('oneqay_menu_book_categories')->where($scope)->where('id',$category)->lockForUpdate()->exists())throw new BusinessViolation('MENU_CATEGORY_NOT_FOUND',404);
   $q=$this->db->table('oneqay_menu_book_items')->where($scope)->where('product_id',$product);$old=(clone $q)->lockForUpdate()->first();if(($old?(int)$old->version:0)!==$version)throw new BusinessViolation('RECORD_CHANGED',409);$changes=['category_id'=>$category,'visible'=>$visible,'spotlight'=>$spotlight,'version'=>$version+1,'updated_at'=>time()];if($old){if($q->where('version',$version)->update($changes)!==1)throw new BusinessViolation('RECORD_CHANGED',409);}else $q->insert($scope+['product_id'=>$product]+$changes);return $data+['new_version'=>$version+1];});
 }
 public function publication():array {
  $s=$this->access->require('pos.catalog.prepare');$this->ready();$row=$this->db->table('oneqay_menu_book_publication')->where($this->scope($s))->first();return $row?['title'=>$row->title,'description'=>$row->description,'cover_id'=>$row->cover_id??null,'published'=>(bool)$row->published,'version'=>(int)$row->version]:['title'=>'Menu','description'=>'','cover_id'=>null,'published'=>false,'version'=>0];
 }
 public function savePublication(string $operation,array $input,string $correlation):array {
  $s=$this->access->require('pos.catalog.prepare');$this->ready();BusinessInput::fields($input,['title','description','action','version'],['cover_id']);$title=BusinessInput::text($input['title'],160);$description=BusinessInput::text($input['description'],500,true);$action=$input['action'];if(!in_array($action,['draft','publish','unpublish'],true))throw new BusinessViolation('MENU_PUBLICATION_ACTION_INVALID');$version=BusinessInput::integer($input['version'],0,1000000000);$hasCover=array_key_exists('cover_id',$input);$cover=$input['cover_id']??null;if($cover!==null&&(!is_string($cover)||!preg_match('/\A[a-f0-9]{32}\z/D',$cover)))throw new BusinessViolation('MENU_IMAGE_INVALID',422);$data=compact('title','description','action','version');if($hasCover)$data['cover_id']=$cover;
  return $this->operations->once($s,BusinessInput::text($operation,80),'menu.publication.save',$data,$correlation,function()use($s,$title,$description,$action,$version,$hasCover,$cover){
   $scope=$this->scope($s);$this->db->table('oneqay_organizations')->where('tenant_id',$scope['tenant_id'])->where('id',$scope['organization_id'])->lockForUpdate()->firstOrFail();$q=$this->db->table('oneqay_menu_book_publication')->where($scope);$old=(clone $q)->lockForUpdate()->first();if(($old?(int)$old->version:0)!==$version)throw new BusinessViolation('RECORD_CHANGED',409);
   if(!$hasCover)$cover=$old?->cover_id;
   if($cover!==null&&(!$this->db->getSchemaBuilder()->hasTable('oneqay_menu_book_media')||!$this->db->table('oneqay_menu_book_media')->where($scope)->where('id',$cover)->exists()))throw new BusinessViolation('MENU_IMAGE_NOT_FOUND',404);
   $snapshot=$old?->published_snapshot;$published=$old?(bool)$old->published:false;
   if($action==='publish'){
    $rows=$this->db->table('oneqay_menu_book_items as m')->join('oneqay_menu_book_categories as g',function($j){$j->on('g.tenant_id','=','m.tenant_id')->on('g.organization_id','=','m.organization_id')->on('g.outlet_id','=','m.outlet_id')->on('g.id','=','m.category_id');})->where('m.tenant_id',$scope['tenant_id'])->where('m.organization_id',$scope['organization_id'])->where('m.outlet_id',$scope['outlet_id'])->where('m.visible',true)->orderBy('g.name')->orderBy('m.product_id')->limit(501)->lockForUpdate()->get(['m.product_id','g.name as category','m.spotlight']);
    if($rows->count()>500)throw new BusinessViolation('MENU_PUBLIC_LIMIT',409);$items=$rows->map(fn($r)=>['product_id'=>$r->product_id,'category'=>$r->category,'spotlight'=>(bool)$r->spotlight])->all();$snapshot=json_encode(['format'=>1,'title'=>$title,'description'=>$description,'cover_id'=>$cover,'items'=>$items],JSON_THROW_ON_ERROR);$published=true;
   }elseif($action==='unpublish')$published=false;
   $next=['title'=>$title,'description'=>$description,'cover_id'=>$cover,'published'=>$published,'published_snapshot'=>$snapshot,'version'=>$version+1,'updated_at'=>time()];if($old){if($q->where('version',$version)->update($next)!==1)throw new BusinessViolation('RECORD_CHANGED',409);}else $q->insert($scope+$next);return ['title'=>$title,'description'=>$description,'cover_id'=>$cover,'published'=>$published,'version'=>$version+1];
  });
 }
 public function items(array $productIds):array {
  $s=$this->access->require('pos.catalog.prepare');$this->ready();if(count($productIds)>50)throw new BusinessViolation('MENU_PRODUCT_LIMIT');
  foreach($productIds as$id)BusinessInput::text($id,64);
  $rows=$this->db->table('oneqay_menu_book_items')->where($this->scope($s))->whereIn('product_id',$productIds)->get(['product_id','category_id','visible','spotlight','version']);$out=[];
  foreach($rows as$r)$out[$r->product_id]=['category_id'=>$r->category_id,'visible'=>(bool)$r->visible,'spotlight'=>(bool)$r->spotlight,'version'=>(int)$r->version];return $out;
 }
 public function item(string $product):?array{
  $s=$this->access->require('pos.catalog.prepare');$this->ready();$id=BusinessInput::text($product,64);$r=$this->db->table('oneqay_menu_book_items')->where($this->scope($s))->where('product_id',$id)->first(['product_id','category_id','visible','spotlight','version']);if(!$r)return null;$v=(array)$r;$v['visible']=(bool)$v['visible'];$v['spotlight']=(bool)$v['spotlight'];$v['version']=(int)$v['version'];return $v;
 }
}
