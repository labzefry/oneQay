<?php
// Author by Lab | zefry. Real disposable SQL, synthetic recipients; no external SMTP sends.
declare(strict_types=1);
require __DIR__.'/../vendor/autoload.php';
use App\Application\Platform\{BusinessActivation,PlatformOwnerAccess,PlatformViolation};
use App\Application\Organization\{OrganizationalContextStore,VerifiedOrganizationalContext};
use App\Infrastructure\Platform\{LaravelPlatformStore,LaravelBusinessAccountProvisioner,PlatformTenantEligibility};
use App\Infrastructure\Identity\{LaravelTenantSessionRevoker,LaravelFirstPartyIdentityEligibilityVerifier};
use App\Infrastructure\Notification\LaravelMailStore;
use App\Domain\Tenancy\TenantId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Device\DeviceId;
foreach(['APP_ENV'=>'testing','APP_KEY'=>'base64:'.base64_encode(str_repeat('p',32)),'ONEQAY_RUNTIME_CLASS'=>'ci','APP_DEBUG'=>'false','ONEQAY_PERSISTENCE_ENABLED'=>'true','ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED'=>'true','ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED'=>'true','ONEQAY_PRIVILEGED_STEP_UP_ENABLED'=>'true','ONEQAY_PLATFORM_ENABLED'=>'true','ONEQAY_PLATFORM_OWNER_TENANT'=>'platform-owner','ONEQAY_PLATFORM_OWNER_IDENTITY'=>'owner-identity','SESSION_DRIVER'=>'array','CACHE_STORE'=>'array','APP_URL'=>'https://oneqay.example.test'] as $key=>$value){putenv($key.'='.$value);$_ENV[$key]=$value;$_SERVER[$key]=$value;}
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$engine=getenv('PLATFORM_TEST_ENGINE')?:'sqlite';
$config=$engine==='mysql'?['driver'=>'mysql','host'=>'127.0.0.1','port'=>3306,'database'=>'oneqay_platform_test','username'=>'root','password'=>'synthetic-test-only','charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true]:['driver'=>'sqlite','database'=>':memory:','prefix'=>'','foreign_key_constraints'=>true];
$app['config']->set('database.default','platform_test');$app['config']->set('database.connections.platform_test',$config);
$db=$app->make('db')->connection();
foreach(glob(__DIR__.'/../database/migrations/*.php')as$file)(require $file)->up();
(require __DIR__.'/../database/module-migrations/platform/2026_10_13_000001_create_platform_and_notification.php')->up();
$n=0;$assert=function(bool $ok,string $why)use(&$n):void{++$n;if(!$ok)throw new RuntimeException('FAIL: '.$why);};
$reject=function(callable $work,string $why)use($assert):void{try{$work();}catch(PlatformViolation $e){$assert(true,$why);return;}$assert(false,$why);};
$contexts=$app->make(OrganizationalContextStore::class);
$owner=new VerifiedOrganizationalContext(PlatformIdentityId::fromString('owner-identity'),TenantId::fromString('platform-owner'),OrganizationId::fromString('platform-org'),OutletId::fromString('platform-outlet'),DeviceId::fromString('platform-device'));
$access=new PlatformOwnerAccess($contexts,'platform-owner','owner-identity');$store=new LaravelPlatformStore($db);$mail=new LaravelMailStore($db,$app->make('encrypter'));
$accounts=new LaravelBusinessAccountProvisioner($app,new LaravelTenantSessionRevoker($db));
$service=new BusinessActivation($access,$store,$accounts,$mail,'https://oneqay.example.test');
$create=['operation_id'=>'test-business-create','action'=>'create','name'=>'Bisnis Nusantara','email'=>'owner@example.test','locale'=>'id','reason'=>'Synthetic fixture approval'];
$reject(fn()=>$service->command($create),'anonymous owner denied');
$contexts->setVerified(new VerifiedOrganizationalContext(PlatformIdentityId::fromString('owner-identity'),TenantId::fromString('other-tenant'),OrganizationId::fromString('other-org'),null,null));
$reject(fn()=>$service->command($create),'same identity in another tenant denied');
$contexts->setVerified($owner);$business=$service->command($create);$assert($business['status']==='pending','pending business created');
$assert($service->command($create)===$business,'creation replay stable');
$reject(fn()=>$service->command(array_merge($create,['name'=>'Different'])),'operation fingerprint conflict');
$assert($db->table('oneqay_platform_businesses')->count()===1,'one business only');
$command=fn(string $action,int $version,string $operation):array=>['operation_id'=>$operation,'action'=>$action,'id'=>$business['id'],'version'=>$version,'reason'=>'Synthetic state change'];
$reject(fn()=>$service->command($command('resume',1,'test-invalid-state')),'cannot resume pending');
$invited=$service->command($command('invite',1,'test-invite-business'));
$assert($invited['status']==='invited' && $db->table('oneqay_mail_messages')->count()===1,'invitation and outbox persisted');
$assert($service->command($command('invite',1,'test-invite-business'))===$invited,'invitation replay no second mail');
$reject(fn()=>$service->command($command('cancel',1,'test-stale-state')),'stale update denied');
$queued=$db->table('oneqay_mail_messages')->first();$payload=json_decode($app->make('encrypter')->decryptString($queued->payload),true);
$assert(!str_contains($queued->payload,'owner@example.test'),'payload encrypted at rest');
$assert(str_contains($payload['text'],'Aktivasi') || str_contains($payload['text'],'mengaktifkan'),'Indonesian invitation');
preg_match('/#token=([0-9a-f]{64})/',$payload['text'],$match);$token=$match[1];
$reject(fn()=>$service->activate(str_repeat('0',64),'synthetic-long-password'),'invalid token denied');
$reject(fn()=>$service->activate($token,'short'),'short password denied');
$context=$service->activate($token,'synthetic-long-password');
$assert($accounts->verifyPassword($context,'synthetic-long-password'),'activated credential passes canonical verifier');
$assert(!$accounts->verifyPassword($context,'incorrect-password'),'wrong credential denied');
$assert($service->activate($token,'synthetic-long-password')===$context,'lost activation response replay requires same password');
$reject(fn()=>$service->activate($token,'different-long-password'),'consumed token cannot change password');
$assert($db->table('oneqay_tenants')->where('id',$context['tenant_id'])->exists(),'actual tenant provisioned');
$assert($db->table('oneqay_tenant_role_assignments')->where('tenant_id',$context['tenant_id'])->count()===2,'protected owner plus operational roles');
$assert($db->table('oneqay_role_permissions')->where('tenant_id',$context['tenant_id'])->where('permission_id','business.finance.post')->exists(),'business owner can use finance');
$eligibility=new PlatformTenantEligibility(new LaravelFirstPartyIdentityEligibilityVerifier($db,true,'ci',true),$store);
$assert($eligibility->isEligible(TenantId::fromString($context['tenant_id']),PlatformIdentityId::fromString($context['identity_id'])),'active account eligible');
$service->command($command('suspend',3,'test-suspend-business'));
$assert(!$eligibility->isEligible(TenantId::fromString($context['tenant_id']),PlatformIdentityId::fromString($context['identity_id'])),'suspended account cannot authenticate');
$service->command($command('resume',4,'test-resume-business'));
$assert($eligibility->isEligible(TenantId::fromString($context['tenant_id']),PlatformIdentityId::fromString($context['identity_id'])),'resumed account eligible for fresh authentication');
$assert($mail->claim(time())===null,'unconfigured mail never sends');
$settings=['enabled'=>true,'host'=>'smtp.example.test','port'=>465,'username'=>'sender@example.test','password'=>'synthetic-smtp-secret','from'=>'sender@example.test','from_name'=>'oneQay','dkim_selector'=>'default','per_minute'=>20];
$mail->configure($settings,1,'owner-fixture',time());
$assert(!array_key_exists('password',$mail->configuration()),'SMTP password never exposed by settings');
$assert(!str_contains($db->table('oneqay_mail_settings')->value('configuration'),'synthetic-smtp-secret'),'SMTP password encrypted');
$reject(fn()=>$mail->configure($settings,1,'owner-fixture',time()),'stale settings rejected');
$invalid=$settings;$invalid['port']=25;$reject(fn()=>$mail->configure($invalid,2,'owner-fixture',time()),'unencrypted port denied');
$claimed=$mail->claim(time());$assert($claimed!==null && $claimed['id']===$queued->id,'worker claims persistent invitation');
$assert($mail->claim(time())===null,'leased message cannot be claimed again');
$mail->finish($claimed,'temporary','SMTP_451',time());$row=$db->table('oneqay_mail_messages')->where('id',$claimed['id'])->first();
$assert($row->status==='retry' && $row->next_attempt_at>time(),'transient rejection schedules backoff');
$assert($mail->claim(time())===null,'backoff respected');
$clock=(int)$row->next_attempt_at+1;$claimed=$mail->claim($clock);$mail->finish($claimed,'accepted','SMTP_ACCEPTED',$clock);
$assert($db->table('oneqay_mail_messages')->where('id',$claimed['id'])->value('status')==='accepted','acceptance is not falsely called delivered');
$event=['event_id'=>'provider-event-1','message_id'=>$claimed['id'],'recipient'=>'owner@example.test','type'=>'delivered'];$mail->feedback($event,$clock+1);$mail->feedback($event,$clock+2);
$assert($db->table('oneqay_mail_events')->where('event','delivered')->count()===1,'feedback replay idempotent');
$reject(fn()=>$mail->feedback(array_merge($event,['recipient'=>'other@example.test']),$clock),'feedback cannot alter another recipient');
$reject(fn()=>$mail->feedback(array_merge($event,['type'=>'complaint']),$clock),'provider event ID conflict denied');
$mail->feedback(array_merge($event,['event_id'=>'provider-deferred','type'=>'deferred']),$clock+3);
$assert($db->table('oneqay_mail_messages')->where('id',$claimed['id'])->value('status')==='delivered','late deferred does not downgrade delivery');
$mail->feedback(array_merge($event,['event_id'=>'provider-complaint','type'=>'complaint']),$clock+4);
$mail->feedback(array_merge($event,['event_id'=>'late-delivery']),$clock+5);
$assert($db->table('oneqay_mail_messages')->where('id',$claimed['id'])->value('status')==='complaint','late delivery cannot erase complaint');
$id=$mail->enqueue('other-tenant','future-message','owner@example.test','Synthetic mail','Never send to suppressed recipient',$clock);
$assert($db->table('oneqay_mail_messages')->where('id',$id)->value('status')==='suppressed','global sender suppression honored across tenants');
$reject(fn()=>$mail->retry($claimed['id'],'owner-fixture','Synthetic retry',true,$clock),'complaint cannot be manually retried');
$crash=$mail->enqueue('test-tenant','crash','crash@example.test','Synthetic mail','Lease recovery',$clock);
$job=$mail->claim($clock+10);$assert($job['id']===$crash,'new recipient claimed');$mail->claim($clock+131);
$assert($db->table('oneqay_mail_messages')->where('id',$crash)->value('status')==='unknown','expired lease never blindly resends');
$reject(fn()=>$mail->retry($crash,'owner-fixture','Synthetic retry',false,$clock+132),'unknown acceptance needs explicit duplicate acknowledgement');
$mail->retry($crash,'owner-fixture','Provider checked; synthetic retry',true,$clock+133);
$assert($db->table('oneqay_mail_messages')->where('id',$crash)->value('status')==='queued','owner retry requeues uncertain mail');
$assert(count($store->listing(1,'Nusantara')['rows'])===1,'business search works');
$assert(!str_contains(json_encode($store->listing(1,'')),$token),'invitation token omitted from platform responses');
$assert(!str_contains(json_encode($mail->report(1)),'owner@example.test'),'mail report excludes addresses and message bodies');
$mail->heartbeat(time());$assert($mail->report(1)['heartbeat_at']>0,'cron heartbeat persisted');
$assert($db->table('oneqay_platform_audit')->count()>=6,'owner actions audited');
echo 'PLATFORM_CONTROL_MAIL_'.$engine.'_PASS assertions='.$n.PHP_EOL;

// Actual session rows must remain revoked after suspend/resume, including another identity in the tenant.
$sessionTenant=$context['tenant_id'];$sessionIdentity=$context['identity_id'];
$db->table('oneqay_identity_first_party_sessions')->insert(['tenant_id'=>$sessionTenant,'authority_id'=>str_repeat('1',32),'public_handle'=>str_repeat('a',43),'identity_id'=>$sessionIdentity,'organization_id'=>$context['organization_id'],'outlet_id'=>$context['outlet_id'],'device_id'=>$context['device_id'],'credential_epoch'=>1,'factor_epoch'=>null,'issued_at_unix'=>time(),'last_seen_at_unix'=>time(),'expires_at_unix'=>time()+7200,'revoked_at_unix'=>null]);
$service->command($command('suspend',5,'test-session-revoke'));$service->command($command('resume',6,'test-session-resume'));
$assert($db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$sessionTenant)->whereNull('revoked_at_unix')->count()===0,'old sessions never resurrect');
$assert($store->loginBusiness('owner@example.test',null)['tenant_id']===$sessionTenant,'email resolves canonical business context');
$assert($store->loginBusiness('unknown@example.test',null)===null,'unknown email does not disclose accounts');
// Failed provisioning rolls back the account, activation state and audit atomically.
$another=$service->command(array_merge($create,['operation_id'=>'test-create-rollback','email'=>'rollback@example.test']));
$service->command(['operation_id'=>'test-invite-rollback','action'=>'invite','id'=>$another['id'],'version'=>1,'reason'=>'Rollback fixture']);
$row=$store->business($another['id']);
$badAccounts=new class implements App\Application\Platform\BusinessAccountProvisioner {
    public function provision(array $business,#[SensitiveParameter] string $password):void{throw new PlatformViolation('SYNTHETIC_PROVISION_FAILURE',503);}
    public function verifyPassword(array $business,#[SensitiveParameter] string $password):bool{return false;}
    public function revokeTenantSessions(string $tenant,int $now):void{}
};
$failedService=new BusinessActivation($access,$store,$badAccounts,$mail,'https://oneqay.example.test');
$raw=$db->table('oneqay_mail_messages')->where('tenant_id',$row['tenant_id'])->first();$body=json_decode($app->make('encrypter')->decryptString($raw->payload),true);preg_match('/#token=([0-9a-f]{64})/',$body['text'],$m);
$reject(fn()=>$failedService->activate($m[1],'synthetic-password-failure'),'failed provisioning propagates safely');
$assert($store->business($another['id'])['status']==='invited','activation remains redeemable after rollback');
$assert(!$db->table('oneqay_tenants')->where('id',$row['tenant_id'])->exists(),'failed activation creates no tenant');
// Terminal feedback binding and signature are enforced by the actual HTTP adapter.
$app['config']->set('platform.feedback_secret',str_repeat('s',40));
$controller=new App\Delivery\Http\Platform\PlatformController($access,$store,$service,$mail,new class implements App\Application\Notification\MailTransport {public function send(array $message):array{return ['accepted','SYNTHETIC'];}public function inspect():array{return [];}});
$raw=json_encode(['event_id'=>'http-bounce','message_id'=>$claimed['id'],'recipient'=>'owner@example.test','type'=>'hard_bounce']);$stamp=(string)time();
$req=Illuminate\Http\Request::create('/platform/mail/feedback','POST',[],[],[],['CONTENT_TYPE'=>'application/json'], $raw);
$assert($controller->feedback($req)->getStatusCode()===401,'missing signature denied');
$req->headers->set('X-OneQay-Timestamp',$stamp);$req->headers->set('X-OneQay-Signature',hash_hmac('sha256',$stamp.'.'.$raw,str_repeat('s',40)));
$assert($controller->feedback($req)->getStatusCode()===200,'signed feedback accepted');
$req->headers->set('X-OneQay-Timestamp',(string)(time()-301));$assert($controller->feedback($req)->getStatusCode()===401,'stale feedback denied');
// Routes remain genuinely authenticated and use the canonical fresh step-up boundary.
$routes=$app->make('router')->getRoutes();$route=$routes->match(Illuminate\Http\Request::create('/platform/businesses','POST'));
$middleware=$route->gatherMiddleware();
$assert(in_array('web',$middleware,true)&&in_array('session.active',$middleware,true),'owner route session and CSRF pipeline');
$assert(in_array(App\Delivery\Http\Middleware\RequirePolicyAdministrationSessionContextMiddleware::class,$middleware,true),'canonical step-up middleware retained');
$assert(in_array(App\Delivery\Http\Middleware\RequirePlatformSecurityMiddleware::class,$middleware,true),'platform ownership boundary retained');
echo 'PLATFORM_CONTROL_MAIL_EXTENDED_'.$engine.'_PASS assertions='.$n.PHP_EOL;
// A definitive mailbox-full bounce can retry, while provider delay must not duplicate mail.
$clock=time()+3600;
$soft=$mail->enqueue('mail-fixture','soft-bounce','soft@example.test','Synthetic mailbox full','Test',$clock);
// Drain any pre-existing queued synthetic messages before the chosen message.
$softJob=null;for($i=0;$i<10;$i++){ $candidate=$mail->claim($clock);if($candidate===null)break;$mail->finish($candidate,'accepted','SMTP_ACCEPTED',$clock);if($candidate['id']===$soft){$softJob=$candidate;break;} }
$assert($softJob!==null,'soft-bounce fixture accepted');
$mail->feedback(['event_id'=>'soft-failed','message_id'=>$soft,'recipient'=>'soft@example.test','type'=>'soft_bounce'],$clock+1);
$assert($db->table('oneqay_mail_messages')->where('id',$soft)->value('status')==='retry','definitive soft bounce schedules retry');
$assert(!$db->table('oneqay_mail_suppressions')->where('recipient_hash',hash('sha256','soft@example.test'))->exists(),'mailbox full is not permanent recipient suppression');
echo 'PLATFORM_MAIL_SOFT_BOUNCE_'.$engine.'_PASS assertions='.$n.PHP_EOL;
