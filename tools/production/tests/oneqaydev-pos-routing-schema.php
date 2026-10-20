<?php
declare(strict_types=1);
// Author by Lab | zefry. Exact deployed 34-receipt baseline in disposable SQL only.
$root=getenv('CONTROL_TEST_ROOT')?:dirname(__DIR__,3);
require $root.'/apps/web/vendor/autoload.php';
require getenv('CONTROL_HELPER')?:__DIR__.'/../cpanel/verify-oneqaydev-pos-routing-schema.php';
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
$db->enableQueryLog();$states=[];$receipt=uApplyModuleSchema($app,$path,function($r)use(&$states){$states[]=$r['state'];});
$ok($receipt['installed_migration_count']===0&&$receipt['module_migrations']===[]&&$states===['EXISTING_SCHEMA_VERIFIED'],'code only no migration path');
foreach($db->getQueryLog() as $query)$ok(preg_match('/\A(?:select|show|pragma)\b/i',ltrim($query['query']))===1,'verification SQL is read only');$db->disableQueryLog();
$ok(uSchemaSnapshot($db)===$before,'all 35 receipts and 84 DDL retained');
$ok($credentials===$db->table('oneqay_identity_password_credentials')->get()->toJson()&&$roles===$db->table('oneqay_outlet_role_assignments')->get()->toJson()&&$permissions===$db->table('oneqay_role_permissions')->get()->toJson(),'accounts and permissions retained');
$ok($mail===$db->table('oneqay_mail_settings')->get()->toJson()&&$settings===$db->table('oneqay_business_settings')->get()->toJson(),'SMTP and settings retained');
$ok($desktopCredentials===$db->table('oneqay_desktop_credentials')->get()->toJson()&&$desktopOperations===$db->table('oneqay_desktop_operations')->get()->toJson(),'offline sessions and operations retained');
uApplyModuleSchema($app,$path,fn($r)=>null);$ok(uSchemaSnapshot($db)===$before,'read only verification repeats safely');
echo 'POS_ROUTING_SCHEMA_'.$engine.'_PASS assertions='.$n."\n";
