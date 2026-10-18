<?php
declare(strict_types=1);
// Author by Lab | zefry. Exact deployed 31-receipt baseline in disposable SQL only.
$root=getenv('CONTROL_TEST_ROOT')?:dirname(__DIR__,3);
require $root.'/apps/web/vendor/autoload.php';
require getenv('CONTROL_HELPER')?:__DIR__.'/../cpanel/install-oneqaydev-control-plane.php';
function uAssert(bool $ok,string $code):void {if(!$ok)throw new RuntimeException($code);}
foreach(['APP_ENV'=>'testing','APP_KEY'=>'base64:'.base64_encode(str_repeat('m',32)),'ONEQAY_RUNTIME_CLASS'=>'ci','APP_DEBUG'=>'false','ONEQAY_PERSISTENCE_ENABLED'=>'true','ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED'=>'true','ONEQAY_PLATFORM_ENABLED'=>'true','SESSION_DRIVER'=>'array','CACHE_STORE'=>'array']as$k=>$v){putenv($k.'='.$v);$_ENV[$k]=$v;$_SERVER[$k]=$v;}
$path=$root.'/apps/web';$app=require $path.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function(Throwable $e):void {fwrite(STDERR,get_class($e).': '.$e->getMessage()."\n");exit(1);});
$engine=getenv('CONTROL_TEST_ENGINE')?:'sqlite';
$config=$engine==='mysql'?['driver'=>'mysql','host'=>'127.0.0.1','port'=>3306,'database'=>'oneqay_control_upgrade_test','username'=>'root','password'=>'synthetic-test-only','charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true]:['driver'=>'sqlite','database'=>':memory:','prefix'=>'','foreign_key_constraints'=>true];
$app['config']->set('database.default','website_test');$app['config']->set('database.connections.website_test',$config);
$db=$app->make('db')->connection();$app->make('migration.repository')->createRepository();$app->make('migrator')->run([$path.'/database/migrations']);
foreach(['pos/0000_00_00_000028_create_pos_inventory_replenishment_foundation.php','business/2026_10_12_000001_create_integrated_business_management.php','platform/2026_10_13_000001_create_platform_and_notification.php','business/2026_10_14_000001_create_merchandising_and_currency_preferences.php','platform/2026_10_15_000001_create_public_website.php']as$f)$app->make('migrator')->run([$path.'/database/module-migrations/'.$f],['step'=>true]);
$owner=['id'=>str_repeat('a',32),'status'=>'invited','token_expires_at'=>time()+3600,'tenant_id'=>'existing-owner','identity_id'=>'existing-identity','organization_id'=>'existing-org','outlet_id'=>'existing-outlet','device_id'=>'existing-device'];
$accounts=new App\Infrastructure\Platform\LaravelBusinessAccountProvisioner($app,new App\Infrastructure\Identity\LaravelTenantSessionRevoker($db));$accounts->provision($owner,'synthetic-owner-password');

$credentials=$db->table('oneqay_identity_password_credentials')->get()->toJson();
$roles=$db->table('oneqay_outlet_role_assignments')->get()->toJson();
$permissions=$db->table('oneqay_role_permissions')->get()->toJson();
$db->table('oneqay_mail_settings')->where('id',1)->update(['version'=>7,'configuration'=>'fixture-opaque-existing-configuration','failures'=>3,'heartbeat_at'=>123456789]);
$mail=$db->table('oneqay_mail_settings')->get()->toJson();
$db->table('oneqay_business_settings')->insert(['tenant_id'=>$owner['tenant_id'],'organization_id'=>$owner['organization_id'],'business_name'=>'Existing business','currency'=>'IDR','symbol'=>'Rp','scale'=>0,'version'=>2,'updated_by'=>$owner['identity_id'],'updated_at_unix'=>123456789]);
$settings=$db->table('oneqay_business_settings')->get()->toJson();
$n=0;$ok=function(bool $v,string $why)use(&$n){uAssert($v,$why);++$n;};
$before=uSchemaSnapshot($db);$ok(count($before['receipts'])===32&&count($before['ddl'])===68,'exact deployed baseline');
$unknown=$db->table('migrations')->insertGetId(['migration'=>'unknown_historical_migration','batch'=>99]);
try{uModulePlan($db,$path,uSchemaSnapshot($db));throw new RuntimeException('unknown receipt accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='EXACT_THIRTY_TWO_RECEIPTS_REQUIRED','unknown receipt rejected');}
$db->table('migrations')->where('id',$unknown)->delete();$ok(uSchemaSnapshot($db)===$before,'unknown receipt test preserves baseline');
$db->getSchemaBuilder()->create('oneqay_platform_campaigns',fn($t)=>$t->string('unsafe'));
try{uModulePlan($db,$path,uSchemaSnapshot($db));throw new RuntimeException('partial prior DDL accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='CONTROL_PLANE_ALREADY_PRESENT_NO_AUTOMATIC_RETRY','partial DDL rejected');}
$db->getSchemaBuilder()->drop('oneqay_platform_campaigns');
try{uApplyModuleSchema($app,$path,fn($r)=>throw new RuntimeException('DURABLE_INTENT_FAILED'));throw new RuntimeException('checkpoint not enforced');}catch(RuntimeException $e){$ok($e->getMessage()==='DURABLE_INTENT_FAILED','no DDL before intent');}
$ok(uSchemaSnapshot($db)===$before,'failed checkpoint preserves database');
$states=[];$receipt=uApplyModuleSchema($app,$path,function($r)use(&$states){$states[]=$r['state'];});
$ok($receipt['installed_migration_count']===1&&$receipt['preserved_replenishment'],'only new control plane migration');
$ok($states===['ADDITIVE_SCHEMA_STARTED','ADDITIVE_SCHEMA_APPLIED'],'durable checkpoints');
$ok(count(uSchemaSnapshot($db)['receipts'])===33&&count(uSchemaSnapshot($db)['ddl'])===77,'one receipt nine tables');
$ok($credentials===$db->table('oneqay_identity_password_credentials')->get()->toJson(),'credentials unchanged');
$ok($accounts->verifyPassword($owner,'synthetic-owner-password'),'old owner credential still valid');
$ok($db->table('oneqay_mail_messages')->count()===0,'no outgoing email');
$ok($roles===$db->table('oneqay_outlet_role_assignments')->get()->toJson(),'existing roles unchanged');
$ok($permissions===$db->table('oneqay_role_permissions')->get()->toJson(),'existing permissions unchanged');
$ok($mail===$db->table('oneqay_mail_settings')->get()->toJson(),'SMTP configuration and health unchanged');
$ok($settings===$db->table('oneqay_business_settings')->get()->toJson(),'business currency and settings unchanged');
$ok($db->table('oneqay_site_pages')->count()===0&&$db->table('oneqay_site_registrations')->count()===0,'no invented website content or registrations');
try{uApplyModuleSchema($app,$path,fn($r)=>null);throw new RuntimeException('schema replay allowed');}catch(RuntimeException $e){$ok($e->getMessage()==='EXACT_THIRTY_TWO_RECEIPTS_REQUIRED','schema replay fail closed');}
$ok($credentials===$db->table('oneqay_identity_password_credentials')->get()->toJson(),'credentials remain unchanged after rejected replay');
echo 'CONTROL_UPGRADE_'.$engine.'_PASS assertions='.$n."\n";
