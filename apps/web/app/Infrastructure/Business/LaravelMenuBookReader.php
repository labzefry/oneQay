<?php
// Author by Lab | zefry. Callers must derive scope from the business host or a verified business session.
declare(strict_types=1);
namespace App\Infrastructure\Business;
use Illuminate\Database\DatabaseManager;
final readonly class LaravelMenuBookReader {
 public function __construct(private DatabaseManager $manager){}
 public function read(array $scope,bool $requirePublished):?array {
  $db=$this->manager->connection();foreach(['oneqay_menu_book_publication','oneqay_menu_book_categories','oneqay_menu_book_items','oneqay_business_settings']as$table)abort_unless($db->getSchemaBuilder()->hasTable($table),404);
  $q=$db->table('oneqay_menu_book_publication')->where($scope);if($requirePublished)$q->where('published',true);$publication=$q->first(['title','description']);if(!$publication&&$requirePublished)return null;
  $rows=$db->table('oneqay_menu_book_items as m')->join('oneqay_menu_book_categories as g',function($j){$j->on('g.tenant_id','=','m.tenant_id')->on('g.organization_id','=','m.organization_id')->on('g.outlet_id','=','m.outlet_id')->on('g.id','=','m.category_id');})->join('oneqay_pos_sale_catalog_items as c',function($j){$j->on('c.tenant_id','=','m.tenant_id')->on('c.outlet_id','=','m.outlet_id')->on('c.product_id','=','m.product_id');})->where('m.tenant_id',$scope['tenant_id'])->where('m.organization_id',$scope['organization_id'])->where('m.outlet_id',$scope['outlet_id'])->where('m.visible',true)->where('c.active',true)->orderBy('g.name')->orderBy('c.display_name')->orderBy('c.product_id')->limit(501)->get(['g.name as category','c.display_name','c.unit_price_atomic','c.currency','c.currency_scale','m.spotlight']);
  abort_if($rows->count()>500,503,'MENU_PUBLIC_LIMIT');$items=$rows->map(fn($r)=>['category'=>$r->category,'name'=>$r->display_name,'price_atomic'=>(string)$r->unit_price_atomic,'currency'=>$r->currency,'scale'=>(int)$r->currency_scale,'spotlight'=>(bool)$r->spotlight])->all();
  $settings=$db->table('oneqay_business_settings')->where('tenant_id',$scope['tenant_id'])->where('organization_id',$scope['organization_id'])->first(['currency','symbol','scale']);$preferences=$settings?['business_name'=>'','currency'=>$settings->currency,'symbol'=>$settings->symbol,'scale'=>(int)$settings->scale,'version'=>0]:null;
  return ['title'=>$publication?->title??'Menu','description'=>$publication?->description??'','items'=>$items,'preferences'=>$preferences];
 }
}
