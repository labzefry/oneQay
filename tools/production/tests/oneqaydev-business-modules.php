<?php
declare(strict_types=1);
// Author by Lab | zefry. Disposable real SQL only; no production connections or mail.
require __DIR__.'/../../../apps/web/vendor/autoload.php';
require __DIR__.'/../cpanel/install-oneqaydev-business-modules.php';
function uAssert(bool $ok,string $code):void {if(!$ok)throw new RuntimeException($code);}
foreach(['APP_ENV'=>'testing','APP_KEY'=>'base64:'.base64_encode(str_repeat('m',32)),'ONEQAY_RUNTIME_CLASS'=>'ci','APP_DEBUG'=>'false','ONEQAY_PERSISTENCE_ENABLED'=>'true','ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED'=>'true','ONEQAY_PLATFORM_ENABLED'=>'true','SESSION_DRIVER'=>'array','CACHE_STORE'=>'array']as$k=>$v){putenv($k.'='.$v);$_ENV[$k]=$v;$_SERVER[$k]=$v;}
$path=realpath(__DIR__.'/../../../apps/web');$app=require $path.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function(Throwable $e):void {fwrite(STDERR,get_class($e).': '.$e->getMessage()."\n");exit(1);});
$engine=getenv('MODULE_TEST_ENGINE')?:'sqlite';
$config=$engine==='mysql'?['driver'=>'mysql','host'=>'127.0.0.1','port'=>3306,'database'=>getenv('MODULE_TEST_PREINSTALLED_REPLENISHMENT')==='1'?'oneqay_modules_28_test':'oneqay_modules_test','username'=>'root','password'=>'synthetic-test-only','charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci','prefix'=>'','strict'=>true]:['driver'=>'sqlite','database'=>':memory:','prefix'=>'','foreign_key_constraints'=>true];
$app['config']->set('database.default','module_test');$app['config']->set('database.connections.module_test',$config);
$db=$app->make('db')->connection();$app->make('migration.repository')->createRepository();$app->make('migrator')->run([$path.'/database/migrations']);
$owner=['id'=>str_repeat('a',32),'status'=>'invited','token_expires_at'=>time()+3600,'tenant_id'=>'existing-owner','identity_id'=>'existing-identity','organization_id'=>'existing-org','outlet_id'=>'existing-outlet','device_id'=>'existing-device'];
$accounts=new App\Infrastructure\Platform\LaravelBusinessAccountProvisioner($app,new App\Infrastructure\Identity\LaravelTenantSessionRevoker($db));$accounts->provision($owner,'synthetic-owner-password');
$credentials=$db->table('oneqay_identity_password_credentials')->get()->toJson();
$n=0;$ok=function(bool $v,string $why)use(&$n){uAssert($v,$why);++$n;};
$preinstalled=getenv('MODULE_TEST_PREINSTALLED_REPLENISHMENT')==='1';
if($preinstalled)$app->make('migrator')->run([uModulePaths($path)[0]],['step'=>true]);
$before=uSchemaSnapshot($db);$ok(count($before['receipts'])===($preinstalled?28:27),'exact historical receipts');
if($preinstalled){
    $unknown=$db->table('migrations')->insertGetId(['migration'=>'unknown_historical_migration','batch'=>99]);
    try{uModulePlan($db,$path,uSchemaSnapshot($db));throw new RuntimeException('unknown receipt accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='EXACT_HISTORICAL_RECEIPTS_REQUIRED','unknown receipt rejected');}
    $db->table('migrations')->where('id',$unknown)->delete();$ok(uSchemaSnapshot($db)===$before,'unknown receipt test preserves baseline');
    $db->getSchemaBuilder()->table('oneqay_pos_inventory_replenishments',fn($t)=>$t->dropUnique('uq_pos_inventory_replenishments_operation'));
    try{uModulePlan($db,$path,uSchemaSnapshot($db));throw new RuntimeException('unsafe existing schema accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='REPLENISHMENT_INDEX_MISSING','existing tenant uniqueness mandatory');}
    $db->getSchemaBuilder()->table('oneqay_pos_inventory_replenishments',fn($t)=>$t->unique(['tenant_id','operation_id'],'uq_pos_inventory_replenishments_operation'));
    $ok(uModulePlan($db,$path,uSchemaSnapshot($db))['preserved_replenishment'],'schema contract restored');$before=uSchemaSnapshot($db);
}
try{uApplyModuleSchema($app,$path,fn($r)=>throw new RuntimeException('DURABLE_INTENT_FAILED'));throw new RuntimeException('checkpoint not enforced');}catch(RuntimeException $e){$ok($e->getMessage()==='DURABLE_INTENT_FAILED','no DDL before intent');}
$ok(uSchemaSnapshot($db)===$before,'failed checkpoint preserves database');
$states=[];$receipt=uApplyModuleSchema($app,$path,function($r)use(&$states){$states[]=$r['state'];});
uAssert($receipt['installed_migration_count']===($preinstalled?2:3)&&$receipt['preserved_replenishment']===$preinstalled,'exact pending migration plan');
$ok($states===['ADDITIVE_SCHEMA_STARTED','ADDITIVE_SCHEMA_APPLIED'],'durable schema checkpoints');
$ok(count(uModuleTables())===20,'exactly twenty additive tables');
$ok(count(uSchemaSnapshot($db)['receipts'])===30,'only three added migration receipts');
$ok($credentials===$db->table('oneqay_identity_password_credentials')->get()->toJson(),'credentials unchanged');
$ok($accounts->verifyPassword($owner,'synthetic-owner-password'),'old owner credential still valid');
$ok($db->table('oneqay_mail_settings')->first()->configuration==='{}','no automatic SMTP setup');
$ok($db->table('oneqay_mail_messages')->count()===0,'no outgoing email');
$result=uGrantExistingOwnerBusinessAccess($app,$owner,str_repeat('a',40));
$ok($result['role']==='oneqay-business-manager','canonical business role granted');
$ok($app->make(App\Application\Organization\OrganizationalContextStore::class)->current()===null,'owner context cleared');
$count=$db->table('oneqay_outlet_role_assignments')->count();uGrantExistingOwnerBusinessAccess($app,$owner,str_repeat('a',40));
$ok($db->table('oneqay_outlet_role_assignments')->count()===$count,'role assignment replay idempotent');
$ok($db->table('oneqay_role_permissions')->where('tenant_id',$owner['tenant_id'])->where('role_id','oneqay-business-manager')->where('permission_id','business.finance.post')->exists(),'owner finance access ready');
$bad=$owner;$bad['identity_id']='unprivileged-identity';$db->table('oneqay_identities')->insert(['tenant_id'=>$owner['tenant_id'],'id'=>$bad['identity_id']]);
try{uGrantExistingOwnerBusinessAccess($app,$bad,str_repeat('b',40));throw new RuntimeException('unauthorized grant allowed');}catch(App\Application\Business\BusinessViolation|App\Application\Authorization\DurablePolicyAdministrationViolation|App\Application\Authorization\DurableAuthorizationViolation $e){$ok(true,'unprivileged identity rejected');}
$ok($app->make(App\Application\Organization\OrganizationalContextStore::class)->current()===null,'rejected context cleared');
try{uApplyModuleSchema($app,$path,fn($r)=>null);throw new RuntimeException('schema replay allowed');}catch(RuntimeException $e){$ok($e->getMessage()==='EXACT_HISTORICAL_RECEIPTS_REQUIRED','schema replay fail closed');}
$ok($credentials===$db->table('oneqay_identity_password_credentials')->get()->toJson(),'credentials remain unchanged after grants');
echo 'BUSINESS_MODULE_UPGRADE_'.$engine.'_'.($preinstalled?'28':'27').'_PASS assertions='.$n."\n";
