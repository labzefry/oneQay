<?php
// Author by Lab | zefry. Disposable SQL only; no external mail or hosting access.
declare(strict_types=1);
require __DIR__.'/desktop-enterprise.php';
use App\Application\Platform\PlatformViolation;
use App\Infrastructure\Notification\LaravelMailPresentation;
$contexts->setVerified($businessOwner);$ownerEmail=$db->table('oneqay_platform_businesses')->where('tenant_id',$t)->value('email');
$before=$db->table('oneqay_identity_password_credentials')->get()->toJson();
try{$settings->cashier(array_replace($cashier,['login'=>strtoupper($ownerEmail),'operation_id'=>'owner-duplicate-cashier']));$assert(false,'owner duplicate accepted');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='OWNER_EMAIL_USE_EXISTING_ACCOUNT','owner email gives actionable duplicate error');}
$grant=['account_type'=>'owner','login'=>$ownerEmail,'name'=>'Owner cashier','password'=>'','pin'=>'192837','enabled'=>true,'version'=>0,'operation_id'=>'owner-cashier-existing-identity'];
$result=$settings->cashier($grant);$assert($settings->cashier($grant)===$result,'owner enablement replay idempotent');try{$settings->cashier(array_replace($grant,['pin'=>'998877']));$assert(false,'owner replay changed PIN');}catch(PlatformViolation $e){$assert($e->reason==='OPERATION_CONFLICT','owner replay rejects changed PIN');}
$assert($db->table('oneqay_desktop_cashiers')->where('login',$ownerEmail)->value('identity_id')===$businessOwner->identityId()->value(),'owner keeps canonical identity');
$assert($before===$db->table('oneqay_identity_password_credentials')->get()->toJson(),'enabling owner never changes credentials');
$assert($settings->page()['owner_account']['login']===$ownerEmail,'settings offers owner enablement');
try{$login->login(['login'=>$ownerEmail,'password'=>'synthetic-long-password','totp'=>''],'owner-no-mfa');$assert(false,'owner MFA bypassed');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$assert($e->getStatusCode()===401,'owner POS retains required MFA');}
$clock=new class implements App\Application\Identity\PrivilegedTotpClock{public int $at;public function __construct(){$this->at=time();}public function nowUnix():int{return $this->at;}};
$app->instance(App\Application\Identity\PrivilegedTotpClock::class,$clock);$mfa=$app->make(App\Application\Identity\PrivilegedTotpMfaService::class);$enrollment=$mfa->startEnrollment($businessOwner->tenantId(),$businessOwner->identityId());$totp=OTPHP\TOTP::createFromSecret($enrollment->secret());$mfa->confirmEnrollment($businessOwner->tenantId(),$businessOwner->identityId(),$totp->at($clock->at));$clock->at+=30;
$connectedOwner=$login->login(['login'=>$ownerEmail,'password'=>'synthetic-long-password','totp'=>$totp->at($clock->at)],'owner-native-valid');$assert($connectedOwner['snapshot']['scope']['identity_id']===$businessOwner->identityId()->value(),'owner POS accepts same password and TOTP');$contexts->setVerified($businessOwner);
foreach(['business/2026_10_12_000001_create_integrated_business_management.php','business/2026_10_20_000001_create_workforce_accounts_and_shift_recovery.php']as$file)(require __DIR__.'/../database/module-migrations/'.$file)->up();
$employees=$db->table('oneqay_business_employees')->count();
try{$app->make(App\Application\Business\BusinessManagement::class)->execute('employee.save','owner-email-employee-denied',['code'=>'DUP-OWNER','name'=>'Duplicate owner','department'=>'Operations','job_title'=>'Cashier','start_date'=>'2026-10-23','active'=>true,'pos_account'=>['login'=>$ownerEmail,'password'=>'synthetic-other-password','pin'=>'998877','version'=>0,'enabled'=>true]],'duplicate-owner-employee');$assert(false,'owner reused as a staff login');}catch(App\Application\Business\BusinessViolation $e){$assert($e->reason==='OWNER_EMAIL_USE_EXISTING_ACCOUNT'&&$e->status===409,'employee flow receives actionable owner-email error');}
$assert($db->table('oneqay_business_employees')->count()===$employees,'duplicate staff request rolls back employee creation');
(require __DIR__.'/../database/module-migrations/platform/2026_10_15_000001_create_public_website.php')->up();
$siteStore=new App\Infrastructure\Website\LaravelWebsiteStore($db,[],$mail,'https://oneqay.example.test');$site=new App\Application\Website\WebsiteManagement($access,$store,$siteStore);
$registration=['name'=>'New business','email'=>'new-business@example.test','phone'=>'+628123456789','business_type'=>'retail','locale'=>'id','consent'=>true];$site->register($registration);$count=$db->table('oneqay_mail_messages')->count();
foreach([$registration['email'],strtoupper($registration['email']),$cashier['login'],$ownerEmail]as$email){try{$site->register(array_replace($registration,['email'=>$email]));$assert(false,'email reused');}catch(PlatformViolation $e){$assert($e->reason==='EMAIL_ALREADY_REGISTERED'&&$e->status===409,'existing owner/cashier email rejected');}}
$assert($count===$db->table('oneqay_mail_messages')->count(),'duplicate registration never queues another email');
$new=$db->table('oneqay_platform_businesses')->where('email',$registration['email'])->first();$payload=json_decode($app->make('encrypter')->decryptString($db->table('oneqay_mail_messages')->where('tenant_id',$new->tenant_id)->value('payload')),true);preg_match('/#token=([a-f0-9]{64})/',$payload['text'],$matches);
$request=Illuminate\Http\Request::create('/activate-business','POST',['token'=>$matches[1],'password'=>'synthetic-new-owner-password']);$request->headers->set('Accept','application/json');$request->setLaravelSession($app['session']->driver());$request->session()->start();$controller=$app->make(App\Delivery\Http\Platform\PlatformController::class);$response=$controller->activate($request);$body=$response->getData(true);
$assert($body['status']==='active'&&$body['redirect']==='/business-login'&&$body['security_required'],'activation enters canonical MFA onboarding');
$assert(!isset($body['login_context'])&&!str_contains($response->getContent(),$matches[1]),'activation response omits identifiers and token');
$request->headers->set('Accept','text/html');$request->replace(['token'=>$matches[1],'password'=>'synthetic-new-owner-password']);$response=$controller->activate($request);$assert($response->getStatusCode()===303&&parse_url($response->getTargetUrl(),PHP_URL_PATH)==='/business-login','browser activation redirects instead of displaying JSON');
$text="Halo pemilik akun,\n<script>alert(1)</script>\nhttps://oneqay.example.test/activate-business#token=".str_repeat('a',64)."\nhttps://evil.example/activate-business#token=".str_repeat('b',64);
$html=LaravelMailPresentation::html('Aktivasi <oneQay>',$text,'https://oneqay.example.test');$assert(str_contains($html,'lang="id"')&&!str_contains($html,'<script>')&&str_contains($html,'&lt;script&gt;'),'Indonesian mail escapes hostile content');
$assert(str_contains($html,'Verifikasi &amp; aktifkan akun')&&!str_contains($html,'href="https://evil.example'),'only first-party activation links become buttons');
$assert(str_contains($html,'font-size:16px')&&!str_contains($html,'<img'),'readable email with no tracking images');$assert(str_contains(LaravelMailPresentation::html('Welcome','Hello owner','https://oneqay.example.test'),'lang="en"'),'English mail layout');
echo 'SIMPLIFIED_POS_ONBOARDING_'.$engine.'_PASS assertions='.$n.PHP_EOL;
