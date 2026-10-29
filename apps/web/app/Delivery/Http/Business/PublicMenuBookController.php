<?php
// Author by Lab | zefry. Public scope is derived exclusively from the verified business hostname.
declare(strict_types=1);
namespace App\Delivery\Http\Business;
use App\Infrastructure\Business\LaravelMenuBookReader;
use Illuminate\Http\Request;
use Inertia\Inertia;
final readonly class PublicMenuBookController {
 public function __construct(private LaravelMenuBookReader $reader){}
 public function show(Request $request):mixed {
  abort_unless(config('platform.business_domains_enabled')===true,404);$business=$request->attributes->get('oneqay.host_business');
  abort_unless(is_array($business)&&($business['status']??null)==='active'&&isset($business['tenant_id'],$business['organization_id'],$business['outlet_id']),404);
  $scope=['tenant_id'=>$business['tenant_id'],'organization_id'=>$business['organization_id'],'outlet_id'=>$business['outlet_id']];$view=$this->reader->read($scope,true);abort_unless($view!==null,404);
  return Inertia::render('Website/PublicMenuBook',$view+['preview'=>false])->toResponse($request)->withHeaders(['Cache-Control'=>'no-store, private','X-Robots-Tag'=>'noindex']);
 }
}
