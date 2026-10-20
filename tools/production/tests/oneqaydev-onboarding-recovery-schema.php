<?php
declare(strict_types=1);
// Author by Lab | zefry. Exact deployed 34-receipt baseline in disposable SQL only.
$root=getenv('CONTROL_TEST_ROOT')?:dirname(__DIR__,3);
require $root.'/apps/web/vendor/autoload.php';
require getenv('CONTROL_HELPER')?:__DIR__.'/../cpanel/install-oneqaydev-onboarding-recovery.php';
function uAssert(bool $ok,string $code):void {if(!$ok)throw new RuntimeException($code);}
foreach(['APP_ENV'=>'testing','APP_KEY'=>'base64:'.base64_encode(str_repeat('m',32)),'ONEQAY_RUNTIME_CLASS'=>'ci','APP_DEBUG'=>'false','ONEQAY_PERSISTENCE_ENABLED'=>'true','ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED'=>'true','ONEQAY_PLATFORM_ENABLED'=>'true','SESSION_DRIVER'=>'array','CACHE_STORE'=>'array']as$k=>$v){putenv($k.'='.$v);$_ENV[$k]=$v;$_SERVER[$k]=$v;}
$path=$root.'/apps/web';$app=require $path.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function(Throwable $e):void {fwrite(STDERR,get_class($e).': '.$e->getMessage()."\n");exit(1);});
$engine=getenv('CONTROL_TEST_ENGINE')?:'sqlite';
$config=$engine==='mysql'?['driver'=>'mysql','host'=>'127.0.0.1','port'=>3306,'database'=>'oneqay_control_upgrade_test','username'=>'root','password'=>'synthetic-test-only','charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true]:['driver'=>'sqlite','database'=>':memory:','prefix'=>'','foreign_key_constraints'=>true];
$app['config']->set('database.default','website_test');$app['config']->set('database.connections.website_test',$config);
$db=$app->make('db')->connection();$app->make('migration.repository')->createRepository();$app->make('migrator')->run([$path.'/database/migrations']);
foreach(['pos/0000_00_00_000028_create_pos_inventory_replenishment_foundation.php','business/2026_10_12_000001_create_integrated_business_management.php','platform/2026_10_13_000001_create_platform_and_notification.php','business/2026_10_14_000001_create_merchandising_and_currency_preferences.php','platform/2026_10_15_000001_create_public_website.php','platform/2026_10_16_000001_create_control_plane_operations.php','pos/2026_10_18_000001_create_desktop_pos_sync.php']as$f)$app->make('migrator')->run([$path.'/database/module-migrations/'.$f],['step'=>true]);
$owner=['id'=>str_repeat('a',32),'status'=>'invited','token_expires_at'=>time()+3600,'tenant_id'=>'existing-owner','identity_id'=>'existing-identity','organization_id'=>'existing-org','outlet_id'=>'existing-outlet','device_id'=>'existing-device'];
$accounts=new App\Infrastructure\Platform\LaravelBusinessAccountProvisioner($app,new App\Infrastructure\Identity\LaravelTenantSessionRevoker($db));$accounts->provision($owner,'synthetic-owner-password');

$credentials=$db->table('oneqay_identity_password_credentials')->get()->toJson();
$roles=$db->table('oneqay_outlet_role_assignments')->get()->toJson();
$permissions=$db->table('oneqay_role_permissions')->get()->toJson();
$db->table('oneqay_mail_settings')->where('id',1)->update(['version'=>7,'configuration'=>'fixture-opaque-existing-configuration','failures'=>3,'heartbeat_at'=>123456789]);
$mail=$db->table('oneqay_mail_settings')->get()->toJson();
$db->table('oneqay_business_settings')->insert(['tenant_id'=>$owner['tenant_id'],'organization_id'=>$owner['organization_id'],'business_name'=>'Existing business','currency'=>'IDR','symbol'=>'Rp','scale'=>0,'version'=>2,'updated_by'=>$owner['identity_id'],'updated_at_unix'=>123456789]);
$settings=$db->table('oneqay_business_settings')->get()->toJson();
$db->table('oneqay_desktop_credentials')->insert(['token_hash'=>str_repeat('c',64),'authority'=>'synthetic-encrypted-authority-preserve','expires_at'=>123456789,'revoked_at'=>null]);
$db->table('oneqay_desktop_operations')->insert(['tenant_id'=>$owner['tenant_id'],'operation_id'=>'existing-desktop-operation','scope_hash'=>str_repeat('a',64),'fingerprint'=>str_repeat('b',64),'organization_id'=>$owner['organization_id'],'outlet_id'=>$owner['outlet_id'],'device_id'=>$owner['device_id'],'identity_id'=>$owner['identity_id'],'kind'=>'sale','state'=>'accepted','result'=>'{"sale_id":"existing-sale"}','payload'=>'{"synthetic":true}','created_at'=>123456789]);
$desktopCredentials=$db->table('oneqay_desktop_credentials')->get()->toJson();$desktopOperations=$db->table('oneqay_desktop_operations')->get()->toJson();
$n=0;$ok=function(bool $v,string $why)use(&$n){uAssert($v,$why);++$n;};
$baseline=uSchemaSnapshot($db);try{uModulePlan($db,$path,$baseline);throw new RuntimeException('old schema accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='EXACT_THIRTY_FIVE_RECEIPTS_REQUIRED','old schema refused');}
// Only disposable test setup creates the already-deployed enterprise schema.
$app->make('migrator')->run([$path.'/database/module-migrations/pos/2026_10_19_000001_create_desktop_pos_operations.php'],['step'=>true]);
$before=uSchemaSnapshot($db);$ok(count($before['receipts'])===35&&count($before['ddl'])===84,'exact deployed baseline');
$unknown=$db->table('migrations')->insertGetId(['migration'=>'unknown','batch'=>99]);try{uModulePlan($db,$path,uSchemaSnapshot($db));throw new RuntimeException('unknown accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='EXACT_THIRTY_FIVE_RECEIPTS_REQUIRED','unknown receipts refused');}$db->table('migrations')->where('id',$unknown)->delete();
$db->table('oneqay_desktop_cashiers')->insert(['login'=>'cashier-existing@example.test','name'=>'Existing cashier','tenant_id'=>$owner['tenant_id'],'identity_id'=>$owner['identity_id'],'organization_id'=>$owner['organization_id'],'outlet_id'=>$owner['outlet_id'],'device_id'=>$owner['device_id'],'enabled'=>true,'pin_verifier'=>'opaque-existing-pin-verifier','version'=>4,'updated_at'=>123456789]);
$cashiers=$db->table('oneqay_desktop_cashiers')->get()->toJson();$states=[];$receipt=uApplyModuleSchema($app,$path,function($r)use(&$states){$states[]=$r['state'];});
$ok($receipt['installed_migration_count']===1&&$states===['ADDITIVE_SCHEMA_STARTED','ADDITIVE_SCHEMA_APPLIED'],'one exact migration with durable intent');
uAssertHistoricalPreserved($before,uSchemaSnapshot($db));$ok(count(uSchemaSnapshot($db)['receipts'])===36&&count(uSchemaSnapshot($db)['ddl'])===87,'exact additive totals');
$ok($db->table('oneqay_business_staff_accounts')->where('login','cashier-existing@example.test')->count()===1,'cashier linked to employee without changing identity');
$ok($db->table('oneqay_business_employees')->where('tenant_id',$owner['tenant_id'])->value('job_title')==='Cashier','default cashier job backfill');
$ok($db->table('oneqay_pos_shift_recoveries')->count()===0,'deployment does not reset production shifts');
$ok($cashiers===$db->table('oneqay_desktop_cashiers')->get()->toJson(),'cashier credentials and configuration preserved');
$ok($credentials===$db->table('oneqay_identity_password_credentials')->get()->toJson()&&$roles===$db->table('oneqay_outlet_role_assignments')->get()->toJson()&&$permissions===$db->table('oneqay_role_permissions')->get()->toJson(),'accounts and permissions retained');
$ok($mail===$db->table('oneqay_mail_settings')->get()->toJson()&&$settings===$db->table('oneqay_business_settings')->get()->toJson(),'SMTP and settings retained');
$ok($desktopCredentials===$db->table('oneqay_desktop_credentials')->get()->toJson()&&$desktopOperations===$db->table('oneqay_desktop_operations')->get()->toJson(),'offline sessions and operations retained');
try{uApplyModuleSchema($app,$path,fn($r)=>null);throw new RuntimeException('repeat migration accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='EXACT_THIRTY_FIVE_RECEIPTS_REQUIRED','completed schema not blindly reapplied');}
function uRead(string $p,int $max=2000000):string{$raw=file_get_contents($p);uAssert(is_string($raw)&&strlen($raw)<=$max,'fixture read');return $raw;}
$doc=json_decode(file_get_contents($path.'/resources/website/defaults.json'),true);$published=$doc['home'];$published['id']['blocks'][0]['title']='Owner custom old homepage';$published['id']['blocks'][0]['image']=str_repeat('a',32);$draft=$published;$draft['id']['blocks'][0]['title']='Owner unpublished draft';
$db->table('oneqay_site_media')->insert(['id'=>str_repeat('a',32),'mime'=>'image/png','bytes'=>1,'payload'=>'eA==','actor'=>'fixture','created_at'=>time()]);
$db->table('oneqay_site_revisions')->insert(['id'=>str_repeat('b',32),'slug'=>'home','document'=>json_encode($published),'actor'=>'fixture','created_at'=>time()]);$db->table('oneqay_site_pages')->insert(['slug'=>'home','version'=>4,'draft'=>json_encode($draft),'published_revision'=>str_repeat('b',32),'updated_at'=>time()]);
$publication=uPublishWebsiteContent($db,$path,str_repeat('c',40));$ok($publication['home']['mode']==='IMMUTABLE_REVISION_PUBLISHED'&&$publication['home']['custom_draft_preserved'],'publication preserves unpublished owner draft');
$newPage=$db->table('oneqay_site_pages')->where('slug','home')->first();$newDoc=json_decode($db->table('oneqay_site_revisions')->where('id',$newPage->published_revision)->value('document'),true);
$ok($newDoc['id']['blocks'][0]['image']===str_repeat('a',32)&&$newDoc['id']['blocks'][0]['title']===$doc['home']['id']['blocks'][0]['title'],'copy refresh retains uploaded image');
$ok(json_decode($newPage->draft,true)===$draft&&$db->table('oneqay_site_revisions')->where('id',str_repeat('b',32))->exists(),'prior revision and draft remain recoverable');
$ok($publication['privacy']['mode']==='CERTIFIED_DEFAULT_ON_SOURCE_SWITCH','unedited page uses certified new copy');
echo 'ONBOARDING_RECOVERY_SCHEMA_'.$engine.'_PASS assertions='.$n."\n";
