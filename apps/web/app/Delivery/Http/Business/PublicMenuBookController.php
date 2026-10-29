<?php
// Author by Lab | zefry. Public menu scope is derived exclusively from the verified business hostname.
declare(strict_types=1);
namespace App\Delivery\Http\Business;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Inertia\Inertia;
final readonly class PublicMenuBookController {
 public function __construct(private \Illuminate\Database\DatabaseManager $manager){}
 public function show(Request $request):mixed {
  abort_unless(config('platform.business_domains_enabled')===true,404);$business=$request->attributes->get('oneqay.host_business');
  abort_unless(is_array($business)&&($business['status']??null)==='active'&&isset($business['tenant_id'],$business['organization_id'],$business['outlet_id']),404);
  $db=$this->manager->connection();foreach(['oneqay_menu_book_publication','oneqay_menu_book_categories','oneqay_menu_book_items']as$table)abort_unless($db->getSchemaBuilder()->hasTable($table),404);
  $scope=['tenant_id'=>$business['tenant_id'],'organization_id'=>$business['organization_id'],'outlet_id'=>$business['outlet_id']];$publication=$db->table('oneqay_menu_book_publication')->where($scope)->where('published',true)->first(['title','description']);abort_unless($publication!==null,404);
  $rows=$db->table('oneqay_menu_book_items as m')->join('oneqay_menu_book_categories as g',function($j){$j->on('g.tenant_id','=','m.tenant_id')->on('g.organization_id','=','m.organization_id')->on('g.outlet_id','=','m.outlet_id')->on('g.id','=','m.category_id');})->join('oneqay_pos_sale_catalog_items as c',function($j){$j->on('c.tenant_id','=','m.tenant_id')->on('c.outlet_id','=','m.outlet_id')->on('c.product_id','=','m.product_id');})->where('m.tenant_id',$scope['tenant_id'])->where('m.organization_id',$scope['organization_id'])->where('m.outlet_id',$scope['outlet_id'])->where('m.visible',true)->where('c.active',true)->orderBy('g.name')->orderBy('c.display_name')->orderBy('c.product_id')->limit(501)->get(['g.name as category','c.display_name','c.unit_price_atomic','c.currency','c.currency_scale','m.spotlight']);
  abort_if($rows->count()>500,503,'MENU_PUBLIC_LIMIT');$items=$rows->map(fn($r)=>['category'=>$r->category,'name'=>$r->display_name,'price_atomic'=>(string)$r->unit_price_atomic,'currency'=>$r->currency,'scale'=>(int)$r->currency_scale,'spotlight'=>(bool)$r->spotlight])->all();
  $settings=$db->table('oneqay_business_settings')->where('tenant_id',$scope['tenant_id'])->where('organization_id',$scope['organization_id'])->first(['currency','symbol','scale']);$preferences=$settings?['business_name'=>'','currency'=>$settings->currency,'symbol'=>$settings->symbol,'scale'=>(int)$settings->scale,'version'=>0]:null;
  return Inertia::render('Website/PublicMenuBook',['title'=>$publication->title,'description'=>$publication->description,'items'=>$items,'preferences'=>$preferences])->toResponse($request)->withHeaders(['Cache-Control'=>'no-store, private','X-Robots-Tag'=>'noindex']);
 }
}
