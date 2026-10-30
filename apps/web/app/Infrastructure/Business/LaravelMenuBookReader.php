<?php
// Author by Lab | zefry. Caller derives scope from the verified business host or session.
declare(strict_types=1);
namespace App\Infrastructure\Business;
use Illuminate\Database\DatabaseManager;
final readonly class LaravelMenuBookReader {
 public function __construct(private DatabaseManager $manager,private LaravelMenuBookMedia $media){}
 public function read(array $scope,bool $requirePublished):?array {
  $db=$this->manager->connection();foreach(['oneqay_menu_book_publication','oneqay_menu_book_categories','oneqay_menu_book_items','oneqay_business_settings']as$table)abort_unless($db->getSchemaBuilder()->hasTable($table),404);
  $q=$db->table('oneqay_menu_book_publication')->where($scope);if($requirePublished)$q->where('published',true);$publication=$q->first(['title','description','published_snapshot','cover_id']);if(!$publication&&$requirePublished)return null;
  if($requirePublished){
   try{$snapshot=json_decode($publication->published_snapshot??'',true,32,JSON_THROW_ON_ERROR);}catch(\Throwable){abort(503,'MENU_SNAPSHOT_INVALID');}
   abort_unless(is_array($snapshot)&&($snapshot['format']??null)===1&&is_string($snapshot['title']??null)&&is_string($snapshot['description']??null)&&is_array($snapshot['items']??null)&&array_is_list($snapshot['items'])&&count($snapshot['items'])<=500,503,'MENU_SNAPSHOT_INVALID');
   $indexed=[];foreach($snapshot['items']as$i){abort_unless(is_array($i)&&is_string($i['product_id']??null)&&is_string($i['category']??null)&&is_bool($i['spotlight']??null)&&!isset($indexed[$i['product_id']]),503,'MENU_SNAPSHOT_INVALID');$indexed[$i['product_id']]=$i;}
   $products=$db->table('oneqay_pos_sale_catalog_items')->where('tenant_id',$scope['tenant_id'])->where('outlet_id',$scope['outlet_id'])->where('active',true)->whereIn('product_id',array_keys($indexed))->get(['product_id','display_name','unit_price_atomic','currency','currency_scale'])->keyBy('product_id');$items=[];
   foreach($indexed as$id=>$i){$p=$products->get($id);if($p)$items[]=['product_id'=>$id,'category'=>$i['category'],'name'=>$p->display_name,'price_atomic'=>(string)$p->unit_price_atomic,'currency'=>$p->currency,'scale'=>(int)$p->currency_scale,'spotlight'=>$i['spotlight']];}
   $title=$snapshot['title'];$description=$snapshot['description'];$coverId=$snapshot['cover_id']??null;
  }else{
   $rows=$db->table('oneqay_menu_book_items as m')->join('oneqay_menu_book_categories as g',function($j){$j->on('g.tenant_id','=','m.tenant_id')->on('g.organization_id','=','m.organization_id')->on('g.outlet_id','=','m.outlet_id')->on('g.id','=','m.category_id');})->join('oneqay_pos_sale_catalog_items as c',function($j){$j->on('c.tenant_id','=','m.tenant_id')->on('c.outlet_id','=','m.outlet_id')->on('c.product_id','=','m.product_id');})->where('m.tenant_id',$scope['tenant_id'])->where('m.organization_id',$scope['organization_id'])->where('m.outlet_id',$scope['outlet_id'])->where('m.visible',true)->where('c.active',true)->orderBy('g.name')->orderBy('m.product_id')->limit(501)->get(['g.name as category','c.display_name','c.unit_price_atomic','c.currency','c.currency_scale','m.spotlight','m.product_id']);
   abort_if($rows->count()>500,503,'MENU_PUBLIC_LIMIT');$items=$rows->map(fn($r)=>['product_id'=>$r->product_id,'category'=>$r->category,'name'=>$r->display_name,'price_atomic'=>(string)$r->unit_price_atomic,'currency'=>$r->currency,'scale'=>(int)$r->currency_scale,'spotlight'=>(bool)$r->spotlight])->all();$title=$publication?->title??'Menu';$description=$publication?->description??'';$coverId=$publication?->cover_id;
  }
  $settings=$db->table('oneqay_business_settings')->where('tenant_id',$scope['tenant_id'])->where('organization_id',$scope['organization_id'])->first(['currency','symbol','scale']);$preferences=$settings?['business_name'=>'','currency'=>$settings->currency,'symbol'=>$settings->symbol,'scale'=>(int)$settings->scale,'version'=>0]:null;
  $cover_url=is_string($coverId)&&preg_match('/\A[a-f0-9]{32}\z/D',$coverId)?($requirePublished?'/menu/media/':'/pos/business/menu-book/media/').$coverId:null;
  return compact('title','description','items','preferences','cover_url');
 }
 public function publishedCover(array $scope,string $id):?array {
  $db=$this->manager->connection();if(!$db->getSchemaBuilder()->hasTable('oneqay_menu_book_publication'))return null;
  $raw=$db->table('oneqay_menu_book_publication')->where($scope)->where('published',true)->value('published_snapshot');
  if(!is_string($raw))return null;
  try{$snapshot=json_decode($raw,true,32,JSON_THROW_ON_ERROR);}catch(\Throwable){return null;}
  if(!is_array($snapshot)||($snapshot['format']??null)!==1||($snapshot['cover_id']??null)!==$id)return null;
  return $this->media->readScoped($scope,$id);
 }

}
