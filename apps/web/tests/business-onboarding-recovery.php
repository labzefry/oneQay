<?php
// Author by Lab | zefry. Disposable SQL only. No external mail or real financial operations.
declare(strict_types=1);
require __DIR__.'/shift-closure-workspace.php';
foreach(['business/2026_10_12_000001_create_integrated_business_management.php','business/2026_10_20_000001_create_workforce_accounts_and_shift_recovery.php','platform/2026_10_15_000001_create_public_website.php']as$file)(require __DIR__.'/../database/module-migrations/'.$file)->up();
use App\Application\Business\{BusinessManagement,BusinessViolation,WorkforceAccounts};
use App\Application\Platform\{ControlPlaneManagement,PlatformViolation};
use App\Infrastructure\Platform\ControlPlaneShiftRecovery;
$contexts->setVerified($businessOwner);$staff=$app->make(WorkforceAccounts::class);$businessService=$app->make(BusinessManagement::class);
$assert(count($staff->jobs($businessOwner))===6,'built-in cashier and common job descriptions available');
$assert(count($staff->accounts($businessOwner))===1,'existing cashier backfilled into employees without new identity');
$beforeRoles=$db->table('oneqay_role_permissions')->get()->toJson();
$run=fn($action,$data,$op)=>$businessService->execute($action,$op,$data,'workforce-test-correlation');
$job=$run('job.save',['name'=>'Customer Success','description'=>'Membantu pelanggan dan mengelola pertanyaan.'],'staff-create-job-custom');
$assert(count($staff->jobs($businessOwner))===7,'owner adds tenant job description');
$assert($beforeRoles===$db->table('oneqay_role_permissions')->get()->toJson(),'custom job creates no permissions');
$account=['login'=>'unified@example.test','password'=>'synthetic-unified-staff-password','pin'=>'395718','version'=>0,'enabled'=>true];
$data=['code'=>'STF-TEST','name'=>'Employee cashier','department'=>'Operations','job_title'=>$job['name'],'start_date'=>'2026-10-20','active'=>true,'pos_account'=>$account];
$employee=$run('employee.save',$data,'staff-create-with-cashier');$linked=$staff->accounts($businessOwner)[$employee['id']];
$assert($linked['login']===$account['login']&&(bool)$linked['enabled'],'employee creation also creates linked cashier');
$assert($run('employee.save',$data,'staff-create-with-cashier')===$employee,'staff command replay creates exactly one account');
$assert($db->table('oneqay_business_staff_accounts')->count()===2,'one link per employee and cashier');
$row=$db->table('oneqay_desktop_cashiers')->where('login',$account['login'])->first();
$assert($db->table('oneqay_outlet_role_assignments')->where('tenant_id',$t)->where('identity_id',$row->identity_id)->where('role_id','oneqay-cashier')->exists(),'cashier gets canonical POS role only');
$assert(!$db->table('oneqay_tenant_role_assignments')->where('tenant_id',$t)->where('identity_id',$row->identity_id)->exists(),'staff cashier receives no tenant administrator role');
$updated=$data;unset($updated['pos_account']);$updated+=['id'=>$employee['id'],'version'=>1];$updated['active']=false;$run('employee.save',$updated,'staff-disable-employee');
$assert(!$db->table('oneqay_desktop_cashiers')->where('login',$account['login'])->value('enabled'),'disabling employee also disables cashier');
try{$settings->cashier(['login'=>$linked['login'],'name'=>'Changed','version'=>2,'enabled'=>false,'operation_id'=>'staff-foreign-link-attempt','employee_id'=>str_repeat('f',32)]);$assert(false,'unknown employee linked');}catch(BusinessViolation){$assert($db->table('oneqay_desktop_cashiers')->where('login',$linked['login'])->value('name')===$data['name'],'foreign staff link rejected atomically');}
$contexts->setVerified($cashierScope);try{$run('job.save',['name'=>'Injected','description'=>'No authority'],'staff-cashier-job-denied');$assert(false,'cashier created job');}catch(App\Application\Authorization\DurableAuthorizationViolation){$assert(true,'cashier cannot configure jobs');}
$contexts->setVerified($businessOwner);++$clock->at;$shift=$app->make(App\Application\Pos\OpenShift::class)->open(new App\Application\Pos\ShiftOpeningCommand('recovery-test-shift-open'),'recovery-open-correlation');
$recovery=new ControlPlaneShiftRecovery($db,$mail);$b=(array)$db->table('oneqay_platform_businesses')->where('tenant_id',$t)->first();$preview=$recovery->listing($b)[0];
$command=['action'=>'shift.recover','id'=>$b['id'],'version'=>(int)$b['version'],'reason'=>'Synthetic incident; offline queue reviewed and secured','shift_id'=>$shift->shiftId(),'review_digest'=>$preview['review_digest'],'reviewed'=>true,'offline_reconciled'=>true,'operation_id'=>'recover-shift-test-operation'];
$control=$app->make(ControlPlaneManagement::class);try{$control->command($command);$assert(false,'business owner recovered platform shift');}catch(PlatformViolation){$assert(true,'business owner cannot use platform recovery');}
$contexts->setVerified($owner);$reject(fn()=>$control->command(array_replace($command,['offline_reconciled'=>false])),'offline reconciliation acknowledgment required');
$reject(fn()=>$control->command(array_replace($command,['review_digest'=>str_repeat('0',64)])),'stale recovery snapshot denied');
$wrong=$command;$wrong['id']=str_repeat('f',32);$reject(fn()=>$control->command($wrong),'cross business shift denied');
$salesBefore=$db->table('oneqay_pos_sales')->get()->toJson();$cashBefore=$db->table('oneqay_pos_shift_closing_cash_evidence')->get()->toJson();$result=$control->command($command);
$assert($result['state']==='recovered_requires_reconciliation','owner recovery explicitly not a financial close');
$assert($control->command($command)===$result,'recovery lost-response replay idempotent');
$assert($db->table('oneqay_pos_shifts')->where('tenant_id',$t)->where('shift_id',$shift->shiftId())->value('active_slot')===null,'only active register slot released');
$assert($db->table('oneqay_pos_shift_recoveries')->where('tenant_id',$t)->where('shift_id',$shift->shiftId())->count()===1,'immutable incident recovery receipt');
$assert($salesBefore===$db->table('oneqay_pos_sales')->get()->toJson()&&$cashBefore===$db->table('oneqay_pos_shift_closing_cash_evidence')->get()->toJson(),'financial history unchanged');
$assert(!$db->table('oneqay_pos_shift_close_evidence')->where('tenant_id',$t)->where('shift_id',$shift->shiftId())->exists(),'recovery never forges final-close evidence');
$siteStore=new App\Infrastructure\Website\LaravelWebsiteStore($db,json_decode(file_get_contents(__DIR__.'/../resources/website/defaults.json'),true),$mail,'https://oneqay.example.test');$site=new App\Application\Website\WebsiteManagement($access,$store,$siteStore);
$contexts->clear();$registration=['name'=>'Email verified business','email'=>'verify-auto@example.test','phone'=>'+628123456789','business_type'=>'retail','locale'=>'id','consent'=>true];$site->register($registration);
$new=(array)$db->table('oneqay_platform_businesses')->where('email',$registration['email'])->first();$assert($new['status']==='invited','public registration automatically sends verification without approval');
$message=$db->table('oneqay_mail_messages')->where('tenant_id',$new['tenant_id'])->first();$payload=json_decode($app->make('encrypter')->decryptString($message->payload),true);preg_match('/#token=([a-f0-9]{64})/',$payload['text'],$m);$token=$m[1];
$assert(!str_contains(json_encode($new),$token),'database stores only token hash');$assert(!$db->table('oneqay_tenants')->where('id',$new['tenant_id'])->exists(),'no tenant authority before email proof');
$site->resend(['email'=>$new['email'],'consent'=>true]);$reject(fn()=>$service->activate($token,'synthetic-verified-password'),'resend invalidates old token');
$replacement=$db->table('oneqay_mail_messages')->where('tenant_id',$new['tenant_id'])->where('id','!=',$message->id)->first();$payload=json_decode($app->make('encrypter')->decryptString($replacement->payload),true);preg_match('/#token=([a-f0-9]{64})/',$payload['text'],$m);
$db->table('oneqay_platform_businesses')->where('id',$new['id'])->update(['token_expires_at'=>time()-1]);$reject(fn()=>$service->activate($m[1],'synthetic-verified-password'),'expired verification denied');$db->table('oneqay_platform_businesses')->where('id',$new['id'])->update(['token_expires_at'=>time()+86400]);
$activated=$service->activate($m[1],'synthetic-verified-password');$assert($store->business($new['id'])['status']==='active','email proof activates canonical business directly');
$assert($store->loginBusiness($new['email'],null)['tenant_id']===$activated['tenant_id'],'verified business can use canonical login');
$contexts->setVerified($owner);$new=$store->business($new['id']);$service->command(['action'=>'suspend','operation_id'=>'test-suspend-verified','id'=>$new['id'],'version'=>(int)$new['version'],'reason'=>'Synthetic misuse reason for account owner']);
$reject(fn()=>$service->activate($m[1],'synthetic-verified-password'),'old verification token cannot reactivate suspended account');$assert($db->table('oneqay_mail_messages')->where('tenant_id',$new['tenant_id'])->count()===3,'suspension reason queued to business owner');
$assert($site->resend(['email'=>'missing@example.test','consent'=>true])===['status'=>'received'],'resend does not reveal account existence');
$route=$routes->match(Illuminate\Http\Request::create('/platform/mail/test','POST'));$assert(in_array(App\Delivery\Http\Middleware\RequirePolicyAdministrationSessionContextMiddleware::class,$route->gatherMiddleware(),true),'SMTP test requires privileged step-up');
$request=Illuminate\Http\Request::create('/platform/mail/test','POST',[],[],[],['CONTENT_TYPE'=>'application/json'],json_encode(['recipient'=>'smtp-test@example.test','operation_id'=>'test-smtp-send-once']));$controller=$app->make(App\Delivery\Http\Platform\PlatformController::class);$response=$controller->test($request);$assert($response->getStatusCode()===202,'SMTP test queues via real controller');$msg=json_decode($response->getContent(),true)['record']['message_id'];$assert($controller->test($request)->getStatusCode()===202&&$db->table('oneqay_mail_messages')->where('id',$msg)->count()===1,'SMTP test replay creates one outbox message');
echo 'BUSINESS_ONBOARDING_RECOVERY_'.$engine.'_PASS assertions='.$n.PHP_EOL;
