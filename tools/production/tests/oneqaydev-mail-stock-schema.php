<?php
declare(strict_types=1);
// Author by Lab | zefry. Exact deployed 36-receipt baseline in disposable SQL only.
$root=getenv('CONTROL_TEST_ROOT')?:dirname(__DIR__,3);
require $root.'/apps/web/vendor/autoload.php';
require getenv('CONTROL_HELPER')?:__DIR__.'/../cpanel/install-oneqaydev-mail-stock.php';
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

foreach(['pos/2026_10_19_000001_create_desktop_pos_operations.php','business/2026_10_20_000001_create_workforce_accounts_and_shift_recovery.php']as$f)$app->make('migrator')->run([$path.'/database/module-migrations/'.$f],['step'=>true]);
$db->table('oneqay_pos_sale_catalog_items')->insert(['tenant_id'=>$owner['tenant_id'],'outlet_id'=>$owner['outlet_id'],'product_id'=>'existing-product','display_name'=>'Existing product','unit_price_atomic'=>12345,'currency'=>'IDR','currency_scale'=>0,'available_quantity'=>7,'active'=>true]);
$before=uSchemaSnapshot($db);$ok(count($before['receipts'])===36&&count($before['ddl'])===87,'exact deployed 36/87 schema');
$id=$db->table('migrations')->insertGetId(['migration'=>'unknown','batch'=>99]);try{uModulePlan($db,$path,uSchemaSnapshot($db));throw new RuntimeException('unknown accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='EXACT_THIRTY_SIX_RECEIPTS_REQUIRED','unknown receipts refused');}$db->table('migrations')->where('id',$id)->delete();
$tmp=sys_get_temp_dir().'/oneqay-mail-stock-backup-'.bin2hex(random_bytes(6));mkdir($tmp,0700);$backup=uPrivateDatabaseBackup($db,$tmp.'/database.sql.gz');
$ok($backup['state']==='PRIVATE_BACKUP_VERIFIED'&&$backup['tables']===87&&$backup['rows']>0,'consistent private database backup');$ok((fileperms($tmp.'/database.sql.gz')&0777)===0600,'backup is private');
$raw=gzdecode(file_get_contents($tmp.'/database.sql.gz'));$ok(is_string($raw)&&hash('sha256',$raw)===$backup['logical_sha256']&&str_contains($raw,'oneqay_identity_password_credentials'),'backup reads back exact logical bytes');
if($engine==='sqlite'){$restored=new PDO('sqlite::memory:');$restored->exec($raw);$ok((int)$restored->query('SELECT COUNT(*) FROM migrations')->fetchColumn()===36,'backup restores all predecessor receipts in isolated database');}
$states=[];$receipt=uApplyModuleSchema($app,$path,function($r)use(&$states){$states[]=$r['state'];});$after=uSchemaSnapshot($db);
$ok($receipt['installed_migration_count']===2&&$states===['BOUNDED_SCHEMA_STARTED','BOUNDED_SCHEMA_APPLIED'],'only two migrations with durable checkpoint');$ok(count($after['receipts'])===38&&count($after['ddl'])===88,'exact new 38/88 totals');$product=$db->table('oneqay_pos_sale_catalog_items')->where('product_id','existing-product')->first();$ok((int)$product->available_quantity===7&&(int)$product->unit_price_atomic===12345&&(bool)$product->tracks_stock,'existing catalog values and tracked default preserved');uAssertHistoricalPreserved($before,$after);$n++;
$ok($credentials===$db->table('oneqay_identity_password_credentials')->get()->toJson()&&$roles===$db->table('oneqay_outlet_role_assignments')->get()->toJson()&&$permissions===$db->table('oneqay_role_permissions')->get()->toJson(),'accounts and permissions preserved');
$ok($mail===$db->table('oneqay_mail_settings')->get()->toJson()&&$settings===$db->table('oneqay_business_settings')->get()->toJson(),'SMTP credentials and settings preserved');
$ok($desktopCredentials===$db->table('oneqay_desktop_credentials')->get()->toJson()&&$desktopOperations===$db->table('oneqay_desktop_operations')->get()->toJson(),'offline authorities and operations preserved');
try{uAssertRollbackSafe($db);throw new RuntimeException('obsolete rollback accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='STOCK_SCHEMA_EXISTS_FORWARD_FIX_REQUIRED','stock schema publication requires compatible forward recovery');}
$bad=$after;$bad['stock']['oneqay_pos_sale_lines']['columns'][0]['nullable']=true;try{uAssertHistoricalPreserved($before,$bad);throw new RuntimeException('alter accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='EXISTING_STOCK_COLUMNS_OR_CONSTRAINTS_CHANGED','unapproved existing-column changes denied');}
try{uApplyModuleSchema($app,$path,fn($r)=>null);throw new RuntimeException('replay accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='EXACT_THIRTY_SIX_RECEIPTS_REQUIRED','no blind schema replay');}
// A non-tracked sale effect must prevent returning to old code, even when the route switch needs recovery.
$fakeSchema=new class{public function hasColumn(...$a):bool{return true;}};$unsafe=new class($fakeSchema){public function __construct(private $schema){}public function getSchemaBuilder(){return $this->schema;}public function table($name){return $this;}public function where(...$a){return $this;}public function exists(){return true;}};
try{uAssertRollbackSafe($unsafe);throw new RuntimeException('unsafe rollback accepted');}catch(RuntimeException $e){$ok($e->getMessage()==='STOCK_SCHEMA_EXISTS_FORWARD_FIX_REQUIRED','stock schema blocks obsolete business-on rollback without a check-then-promote race');}
unlink($tmp.'/database.sql.gz');rmdir($tmp);echo 'MAIL_STOCK_OPERATOR_SCHEMA_'.$engine.'_PASS assertions='.$n.PHP_EOL;
