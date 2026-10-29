<?php
// Author by Lab | zefry. Authenticated owner-facing classification only; no public order or checkout.
declare(strict_types=1);
namespace App\Infrastructure\Business;
use App\Application\Business\{BusinessAccess,BusinessInput,BusinessRepository,BusinessViolation,MerchandisingRepository};
use App\Application\Organization\VerifiedOrganizationalContext;
use Illuminate\Database\Connection;
final readonly class LaravelMenuBookCatalog {
 public function __construct(private Connection $db,private BusinessAccess $access,private BusinessRepository $operations,private MerchandisingRepository $products){}
 private function scope(VerifiedOrganizationalContext $s):array{return ['tenant_id'=>$s->tenantId()->value(),'organization_id'=>$s->organizationId()->value(),'outlet_id'=>$s->outletId()->value()];}
 private function ready():void{if(!$this->db->getSchemaBuilder()->hasTable('oneqay_menu_book_categories')||!$this->db->getSchemaBuilder()->hasTable('oneqay_menu_book_items'))throw new BusinessViolation('MENU_BOOK_SCHEMA_REQUIRED',503);}
 public function categories():array{
  $s=$this->access->require('pos.catalog.prepare');$this->ready();return $this->db->table('oneqay_menu_book_categories')->where($this->scope($s))->orderBy('name')->orderBy('id')->limit(200)->get(['id','name'])->map(fn($r)=>(array)$r)->all();
 }
 public function createCategory(string $operation,array $input,string $correlation):array{
  $s=$this->access->require('pos.catalog.prepare');$this->ready();BusinessInput::fields($input,['name']);$name=preg_replace('/\s+/u',' ',BusinessInput::text($input['name'],120));$key=hash('sha256',mb_strtolower($name,'UTF-8'));
  return $this->operations->once($s,BusinessInput::text($operation,80),'menu.category.create',['name'=>$name],$correlation,function()use($s,$name,$key){$scope=$this->scope($s);$table=$this->db->table('oneqay_menu_book_categories');$old=(clone $table)->where($scope)->where('name_key',$key)->first();if($old)return ['id'=>$old->id,'name'=>$old->name];$id=bin2hex(random_bytes(16));$table->insert($scope+['id'=>$id,'name'=>$name,'name_key'=>$key,'created_at'=>time()]);return ['id'=>$id,'name'=>$name];});
 }
 public function assign(string $operation,array $input,string $correlation):array{
  $s=$this->access->require('pos.catalog.prepare');$this->ready();BusinessInput::fields($input,['product_id','category_id','visible','spotlight','version']);$product=BusinessInput::text($input['product_id'],64);$category=BusinessInput::text($input['category_id'],32);if(!preg_match('/\A[a-f0-9]{32}\z/D',$category))throw new BusinessViolation('MENU_CATEGORY_INVALID');$visible=BusinessInput::boolean($input['visible']);$spotlight=BusinessInput::boolean($input['spotlight']);$version=BusinessInput::integer($input['version'],0,1000000000);$data=['product_id'=>$product,'category_id'=>$category,'visible'=>$visible,'spotlight'=>$spotlight,'version'=>$version];
  return $this->operations->once($s,BusinessInput::text($operation,80),'menu.product.assign',$data,$correlation,function()use($s,$data,$product,$category,$visible,$spotlight,$version){$scope=$this->scope($s);if(!$this->products->product($s,$product,true))throw new BusinessViolation('PRODUCT_NOT_FOUND',404);if(!$this->db->table('oneqay_menu_book_categories')->where($scope)->where('id',$category)->lockForUpdate()->exists())throw new BusinessViolation('MENU_CATEGORY_NOT_FOUND',404);
   $q=$this->db->table('oneqay_menu_book_items')->where($scope)->where('product_id',$product);$old=(clone $q)->lockForUpdate()->first();if(($old?(int)$old->version:0)!==$version)throw new BusinessViolation('RECORD_CHANGED',409);$changes=['category_id'=>$category,'visible'=>$visible,'spotlight'=>$spotlight,'version'=>$version+1,'updated_at'=>time()];if($old){if($q->where('version',$version)->update($changes)!==1)throw new BusinessViolation('RECORD_CHANGED',409);}else $q->insert($scope+['product_id'=>$product]+$changes);return $data+['new_version'=>$version+1];});
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
