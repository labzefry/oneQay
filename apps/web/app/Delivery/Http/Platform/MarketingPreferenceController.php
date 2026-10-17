<?php
declare(strict_types=1);
namespace App\Delivery\Http\Platform;
use App\Infrastructure\Platform\ControlPlaneCampaigns;
use App\Application\Business\BusinessAccess;
use App\Application\Platform\PlatformViolation;
use Illuminate\Http\Request;
use Inertia\Inertia;
// Author by Lab | zefry
final readonly class MarketingPreferenceController {
 public function __construct(private ControlPlaneCampaigns $campaigns){}
 public function publicPage():mixed{return Inertia::render('Platform/MarketingPreferences');}
 public function unsubscribe(Request $r):mixed{
  if(strlen($r->getContent())>512)return response()->json(['error'=>'INVALID_PREFERENCE_LINK'],422);
  try{$this->campaigns->unsubscribe((string)$r->input('token',''));return response()->json(['status'=>'saved'],200,['Cache-Control'=>'no-store']);}catch(PlatformViolation){return response()->json(['error'=>'INVALID_PREFERENCE_LINK'],422,['Cache-Control'=>'no-store']);}
 }
 public function save(Request $r,BusinessAccess $access):mixed{
  $context=$access->require('authorization.policy.manage');$db=app('db')->connection();$b=$db->table('oneqay_platform_businesses')->where('tenant_id',$context->tenantId()->value())->first();abort_unless($b&&is_bool($r->input('subscribed'))&&strlen($r->getContent())<512,422);
  $this->campaigns->preference($b->id,$r->boolean('subscribed'),'business-admin');return response()->json(['status'=>'saved'],200,['Cache-Control'=>'no-store']);
 }
}
