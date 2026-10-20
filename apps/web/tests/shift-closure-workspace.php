<?php
// Author by Lab | zefry. Disposable SQL: a legacy owner shift closes only through independent canonical approval.
declare(strict_types=1);
require __DIR__.'/desktop-enterprise.php';
use App\Application\Pos\{OpenShift,ShiftOpeningCommand,RecordShiftOpeningCash,ShiftOpeningCashCommand,RecordShiftClosingCash,ShiftClosingCashCommand,CloseShift,CloseShiftCommand,ShiftOpeningClock,ShiftCloseClock,ViewShiftClosureWorkspace,ViewPosOperationsHub,PosExecutionContext};
use App\Application\Authorization\DurableAuthorizationViolation;
use App\Domain\Pos\Money;
use App\Infrastructure\Pos\LaravelDesktopBusinessSettings;
$app->forgetScopedInstances();$contexts=$app->make(App\Application\Organization\OrganizationalContextStore::class);$contexts->setVerified($businessOwner);
$clock=new class implements ShiftOpeningClock,ShiftCloseClock {public int $at;public function nowUnix():int{return $this->at;}};$clock->at=time()+10;$app->instance(ShiftOpeningClock::class,$clock);$app->instance(ShiftCloseClock::class,$clock);
$legacy=$app->make(OpenShift::class)->open(new ShiftOpeningCommand('legacy-web-open-closure'),'legacy-open-correlation');++$clock->at;
$app->make(RecordShiftOpeningCash::class)->record(new ShiftOpeningCashCommand('legacy-web-opening-cash',Money::fromAtomicUnits(200000,'IDR',0)),'legacy-opening-correlation');++$clock->at;
$assignment=$db->table('oneqay_tenant_role_assignments')->where('tenant_id',$t)->where('identity_id',$businessOwner->identityId()->value())->where('role_id','business-owner-operational')->get()->map(fn($v)=>(array)$v)->all();
$assert(count($assignment)===1,'fixture has operational assignment');$db->table('oneqay_tenant_role_assignments')->where('tenant_id',$t)->where('identity_id',$businessOwner->identityId()->value())->where('role_id','business-owner-operational')->delete();
$permissionsBefore=$db->table('oneqay_role_permissions')->get()->toJson();$view=$app->make(ViewShiftClosureWorkspace::class)->view();
$assert(!$view['permissions']['close']&&!$view['permissions']['record_cash']&&$view['permissions']['manage_access'],'legacy owner can inspect workflow without gaining mutation authority');
$assert($view['shift']['id']===$legacy->shiftId()&&$view['shift']['opened_by_you']&&$view['shift']['opening_cash']==='200000'&&$view['shift']['closing_cash']===null,'legacy active shift and actual opening cash visible');
$hub=$app->make(ViewPosOperationsHub::class)->view();$assert(!$hub->canShiftClose()&&$hub->canViewShiftClosure(),'owner navigation exposes permissions setup without granting close');
try{$app->make(RecordShiftClosingCash::class)->record(new ShiftClosingCashCommand('legacy-denied-closing',Money::fromAtomicUnits(200000,'IDR',0)),'legacy-denied-correlation');$assert(false,'unauthorized owner recorded cash');}catch(DurableAuthorizationViolation){$assert(true,'canonical cash mutation still denied without permission');}
$assert($permissionsBefore===$db->table('oneqay_role_permissions')->get()->toJson(),'viewing does not create or grant roles');
$request=Illuminate\Http\Request::create('/pos/shifts/close','GET');$request->headers->set('X-Inertia','true');$response=$app->make(App\Delivery\Http\Pos\PosShiftClosePageController::class)->__invoke()->toResponse($request);$page=json_decode($response->getContent(),true);
$assert($page['component']==='Pos/ShiftClose'&&$page['props']['workspace']['permissions']['record_cash']===false,'actual Inertia response contains setup state');
$contexts->clear();try{$app->make(ViewShiftClosureWorkspace::class)->view();$assert(false,'anonymous inspected shift');}catch(App\Application\Pos\PosAccessViolation){$assert(true,'anonymous denied');}$contexts->setVerified($businessOwner);
$db->table('oneqay_tenant_role_assignments')->insert($assignment);
$settings=$app->make(LaravelDesktopBusinessSettings::class);$settings->cashier(array_replace($cashier,['version'=>2,'password'=>'','pin'=>'','enabled'=>true]));
$directory=new App\Infrastructure\Platform\LaravelPlatformStore($db);$resolved=$directory->loginBusiness($cashier['login'],null);
$assert($resolved['identity_id']===$cashierScope->identityId()->value()&&$resolved['tenant_id']===$t,'existing enabled cashier resolves canonical web POS identity');
$assert(!isset($resolved['password'])&&!isset($resolved['pin_verifier']),'login resolution discloses no credential or PIN');
$assert($directory->loginBusiness($cashier['login'],str_repeat('f',32))===null,'wrong business selector denied');
$ownerBusiness=$db->table('oneqay_platform_businesses')->where('tenant_id',$t)->first();$assert($ownerBusiness!==null,'fixture managed business exists');$db->table('oneqay_platform_businesses')->where('id',$ownerBusiness->id)->update(['status'=>'suspended']);$assert($directory->loginBusiness($cashier['login'],null)===null,'suspended business staff login denied');$db->table('oneqay_platform_businesses')->where('id',$ownerBusiness->id)->update(['status'=>'active']);
$app->make(RecordShiftClosingCash::class)->record(new ShiftClosingCashCommand('legacy-record-closing',Money::fromAtomicUnits(200000,'IDR',0)),'legacy-record-correlation');++$clock->at;
$assert($app->make(ViewShiftClosureWorkspace::class)->view()['shift']['closing_cash']==='200000','counted cash appears without automatically closing');
try{$app->make(CloseShift::class)->close(new CloseShiftCommand('legacy-self-close-denied'),'legacy-self-close-correlation');$assert(false,'opener self-approved');}catch(App\Application\Pos\PosTransactionViolation|App\Application\Persistence\DurablePersistenceViolation){$assert(true,'opener cannot approve own shift');}
$assert($db->table('oneqay_pos_shifts')->where('tenant_id',$t)->where('shift_id',$legacy->shiftId())->value('active_slot')===1,'rejected approval keeps shift active');
$contexts->setVerified($cashierScope);$view=$app->make(ViewShiftClosureWorkspace::class)->view();$assert($view['permissions']['close']&&!$view['permissions']['manage_access']&&!$view['shift']['opened_by_you'],'cashier can approve without business administration');
$final=$app->make(CloseShift::class)->close(new CloseShiftCommand('legacy-independent-close'),'legacy-final-correlation');$assert($final->shiftId()===$legacy->shiftId(),'different authorized cashier closes canonical legacy shift');
$app->make(CloseShift::class)->close(new CloseShiftCommand('legacy-independent-close'),'legacy-final-correlation');$assert($db->table('oneqay_pos_shift_close_evidence')->where('tenant_id',$t)->where('shift_id',$legacy->shiftId())->count()===1,'final close replay creates exactly one evidence');
$connected=$app->make(App\Infrastructure\Pos\LaravelDesktopCashierLogin::class)->login(['login'=>$cashier['login'],'password'=>$cashier['password'],'totp'=>''],'legacy-desktop-login-correlation');$assert($connected['snapshot']['active_shift']===null,'Windows login no longer blocked by the closed web shift');
$contexts->setVerified($businessOwner);$settings->cashier(array_replace($cashier,['version'=>3,'password'=>'','pin'=>'','enabled'=>false]));$assert($directory->loginBusiness($cashier['login'],null)===null,'disabled cashier web login denied');
echo 'SHIFT_CLOSURE_WORKSPACE_'.$engine.'_PASS assertions='.$n.PHP_EOL;
