<?php
declare(strict_types=1);
// Author by Lab | zefry. Isolated fixtures exercise boundaries and business-on rollback.
$source=file_get_contents(getenv('CONTROL_OPERATOR')?:__DIR__.'/../cpanel/update-oneqaydev-onboarding-recovery.php');
if(!is_string($source))throw new RuntimeException('Operator unavailable');
$tmp=sys_get_temp_dir().'/oneqay-ui-update-'.bin2hex(random_bytes(8));mkdir($tmp,0700);$op=$tmp.'/operator';mkdir($op,0700);
$code=preg_replace("~const U_ROOT = '[^']+';~",'const U_ROOT = '.var_export($tmp.'/private',true).';',$source,1,$n);assert($n===1);
$code=preg_replace("~const U_PUBLIC = '[^']+';~",'const U_PUBLIC = '.var_export($tmp.'/public',true).';',$code,1,$n);assert($n===1);
$fixtureEnv='APP_KEY="fixture-key"'."\nONEQAY_DB_PASSWORD=fixture-password\nSESSION_FILES=/fixture/shared-sessions\nONEQAY_PRODUCTION_BUSINESS_TRAFFIC_AUTHORIZED=true\nONEQAY_PLATFORM_ENABLED=true\nONEQAY_PLATFORM_OWNER_TENANT=fixture-tenant\nONEQAY_PLATFORM_OWNER_IDENTITY=fixture-owner\nONEQAY_MAIL_FEEDBACK_SECRET=fixture-unchanged-secret\nONEQAY_RUNNING_SOURCE_COMMIT=302379bd7e474f082bdfada13d434802edcedc68\nONEQAY_RUNNING_ARTIFACT_SHA256=0580e2d0a5b1add98094670682b50cfa4565e2885a20ca7750935c04d7c71613\n";
foreach(['U_OLD_ENV_SHA'=>hash('sha256',$fixtureEnv),'U_OLD_ENTRY_SHA'=>hash('sha256','old-business-entry'),'U_OLD_ROUTING_SHA'=>hash('sha256','old-business-routing')] as $key=>$value){$code=preg_replace("~const ".$key." = '[^']+';~",'const '.$key.' = '.var_export($value,true).';',$code,1,$n);assert($n===1);}
// Substitute filesystem and HTTPS only in this isolated fixture copy.
$code=preg_replace("~const L_APP = [^;]+;~",'const L_APP = '.var_export($tmp.'/old-app',true).';',$code,1);
$code=preg_replace("~const L_ENV = [^;]+;~",'const L_ENV = '.var_export($tmp.'/old.env',true).';',$code,1);
$code=preg_replace("~const L_ASSET_UNION_SHA = '[^']+';~",'',$code,1);
$code=str_replace('function lPreviousPublicVerify(): void','function fixtureUnusedPreviousHttps(): void',$code);
file_put_contents($op.'/update.php',$code);file_put_contents($op.'/install-modules.php','fixture-module-helper');file_put_contents($op.'/release-retention.php','fixture-retention-helper');require $op.'/update.php';
$count=0;
function testUi(bool $ok,string $label): void {global $count;if(!$ok)throw new RuntimeException($label);$count++;echo 'WORKSPACE_UPDATE_'.$label."=PASS\n";}
function deniedUi(callable $fn,string $code): void {try{$fn();throw new RuntimeException('Expected denial '.$code);}catch(WorkspaceUpdateStop $e){testUi($e->getMessage()===$code,'DENY_'.$code);}}
function oqReadEnv(string $raw): array {$r=[];foreach(explode("\n",trim($raw)) as $line){if(preg_match('/^([A-Z0-9_]+)=(.*)$/',$line,$m)){if(isset($r[$m[1]]))throw new RuntimeException('Duplicate');$r[$m[1]]=trim($m[2],'"');}}return $r;}
function oqHashTree(string $dir): string {$lines=[];foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST) as $e){if($e->isLink())throw new RuntimeException('Link');$key=substr($e->getPathname(),strlen($dir)+1);$lines[]=$e->isDir()?'d '.$key:'f '.$key.' '.hash_file('sha256',$e->getPathname());}sort($lines);return hash('sha256',implode("\n",$lines));}
function oqCopyTree(string $a,string $b,int $mode=0750): void {mkdir($b,$mode);foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($a,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST) as $e){if($e->isLink())throw new RuntimeException('Link');$to=$b.'/'.substr($e->getPathname(),strlen($a)+1);if($e->isDir())mkdir($to,$mode);else copy($e->getPathname(),$to);}}
function oqRemoveTree(string $dir): void {foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST) as $e){if($e->isDir())rmdir($e->getPathname());else unlink($e->getPathname());}rmdir($dir);}
const B_SOURCE='192c8a3f719006ecdc5f8a4216d09c285b747e59';const B_ARCHIVE='ff1a4e92eba9d2e5c79c33acc8aa788d4561863541242d16674094704ac0325f';
define('B_APP',$tmp.'/old-app');define('B_ENV',$tmp.'/old.env');const B_BRIDGE_ID='production-192c8a3f7190-r3';const B_ENTRY_NAME='oneqay-business-192c8a3f7190-r3.php';
define('B_MERCHANT',$tmp.'/merchant.json');define('B_MERCHANT_SHA',hash('sha256','fixture-merchant'));define('B_BRIDGE',hash('sha256','health-only-index'));
function bMerchant(): array {return ['merchant'=>['tenant_id'=>'fixture-tenant','identity_id'=>'fixture-owner']];}
function bStagingFingerprint(): string {return hash('sha256','protected-staging');}
function lPreviousPublicVerify(): void {testUi(file_get_contents(U_PUBLIC.'/.htaccess')==='old-business-routing'&&file_get_contents(U_PUBLIC.'/'.L_ENTRY_NAME)==='old-business-entry','ROLLBACK_OLD_BUSINESS_ENTRY_SERVED_FIXTURE');}
function bBridgeFor(string $sha): string {return '<?php $root='.var_export(B_APP,true).';$env='.var_export(B_ENV,true).';$sha='.var_export($sha,true).';// '.B_BRIDGE_ID;}
function bRewriteFor(string $raw,string $hosting): string {return 'DirectoryIndex '.B_ENTRY_NAME."\n".str_replace('index.php',B_ENTRY_NAME,$raw).$hosting;}
$r=['schema_version'=>1,'action'=>'CERTIFIED_ONBOARDING_ADDITIVE_UPDATE_PRESERVE_BUSINESS_ON','domain'=>'oneqaydev.n07.my.id','private_root'=>U_ROOT,'public_root'=>U_PUBLIC,'previous_source_commit'=>L_SOURCE,'previous_runtime_sha256'=>U_OLD_ENV_SHA,'previous_business_entry_sha256'=>U_OLD_ENTRY_SHA,'previous_routing_sha256'=>U_OLD_ROUTING_SHA,'merchant_receipt_sha256'=>'e1cddcd1f0334a85b073c0526ecbc6236ba530dbc3a671b00f4d5d18e4a3109a','operator_sha256'=>hash_file('sha256',$op.'/update.php'),'module_helper_sha256'=>hash_file('sha256',$op.'/install-modules.php'),'rollback_target_state'=>'BUSINESS_ON_PREVIOUS_CERTIFIED_RELEASE','authority_window_seconds'=>U_WINDOW,'retention_helper_sha256'=>hash_file('sha256',$op.'/release-retention.php'),'release_retention_policy'=>'VERIFIED_PRIVATE_ZIP_KEEP_ACTIVE','module_migrations_allowed'=>true,'module_migration_paths'=>['business/2026_10_20_000001_create_workforce_accounts_and_shift_recovery.php'],'historical_migrations_allowed'=>false,'owner_business_role_allowed'=>false,'merchant_reprovision_allowed'=>false,'staging_allowed'=>false,'updater_allowed'=>false,'external_payments_allowed'=>false,'runtime_config_delta'=>[],'website_content_refresh'=>['home','pricing','guides','terms','privacy'],'source_commit'=>U_SOURCE,'release_id'=>'production-'.substr(U_SOURCE,0,12),'archive_sha256'=>str_repeat('b',64),'manifest_sha256'=>str_repeat('c',64),'release_files_sha256'=>str_repeat('d',64),'release_file_count'=>6256,'source_certification_run_id'=>123];
function requestUi(array $r): void {global $op;file_put_contents($op.'/authority-request.json',json_encode($r,JSON_THROW_ON_ERROR));}
try {
    requestUi($r);testUi(uRequest()===$r,'EXACT_REQUEST');
    foreach(['owner_business_role_allowed','historical_migrations_allowed','merchant_reprovision_allowed','staging_allowed','updater_allowed','external_payments_allowed'] as $key){$bad=$r;$bad[$key]=true;requestUi($bad);deniedUi(fn()=>uRequest(),'REQUEST_SCOPE_INVALID');}
    $bad=$r;$bad['unexpected']=true;requestUi($bad);deniedUi(fn()=>uRequest(),'REQUEST_UNKNOWN_FIELDS');
    $bad=$r;$bad['source_commit']='../../escape';requestUi($bad);deniedUi(fn()=>uRequest(),'REQUEST_SOURCE_INVALID');requestUi($r);
    $sha=str_repeat('e',64);$now=1791720000;$c=['id'=>10,'user'=>['login'=>'labzefry','type'=>'User'],'author_association'=>'OWNER','issue_url'=>'https://api.github.com/repos/labzefry/oneQay/issues/946','body'=>uApprovalBody($sha,$r),'created_at'=>gmdate('Y-m-d\TH:i:s\Z',$now),'updated_at'=>gmdate('Y-m-d\TH:i:s\Z',$now)];
    testUi(uQualifyApproval($c,10,$sha,$r,$now)['expires_at_unix']===$now+3600,'NATIVE_EXACT_GRANT');
    foreach(['login','type'] as $key){$bad=$c;$bad['user'][$key]='wrong';deniedUi(fn()=>uQualifyApproval($bad,10,$sha,$r,$now),'NATIVE_OWNER_AUTHORITY_INVALID');}
    $bad=$c;$bad['author_association']='MEMBER';deniedUi(fn()=>uQualifyApproval($bad,10,$sha,$r,$now),'NATIVE_OWNER_AUTHORITY_INVALID');
    $bad=$c;$bad['body']=str_replace('ROLLBACK: GRANTED','ROLLBACK: DENIED',$bad['body']);deniedUi(fn()=>uQualifyApproval($bad,10,$sha,$r,$now),'EXACT_WORKSPACE_GRANT_MISSING');
    deniedUi(fn()=>uQualifyApproval($c,10,str_repeat('f',64),$r,$now),'EXACT_WORKSPACE_GRANT_MISSING');
    deniedUi(fn()=>uQualifyApproval($c,10,$sha,$r,$now-1),'AUTHORITY_WINDOW_EXPIRED');deniedUi(fn()=>uQualifyApproval($c,10,$sha,$r,$now+3601),'AUTHORITY_WINDOW_EXPIRED');
    $bad=$c;$bad['updated_at']=gmdate('Y-m-d\TH:i:s\Z',$now+1);deniedUi(fn()=>uQualifyApproval($bad,10,$sha,$r,$now),'AUTHORITY_TIMESTAMP_INVALID');
    $env=$fixtureEnv;
    $next=uCandidateEnv($env,$r);$oldCfg=oqReadEnv($env);$nextCfg=oqReadEnv($next);testUi($nextCfg['ONEQAY_RUNNING_SOURCE_COMMIT']===$r['source_commit']&&$nextCfg['ONEQAY_RUNNING_ARTIFACT_SHA256']===$r['archive_sha256'],'NEW_NATIVE_SOURCE_BINDING');
    unset($oldCfg['ONEQAY_RUNNING_SOURCE_COMMIT'],$oldCfg['ONEQAY_RUNNING_ARTIFACT_SHA256'],$nextCfg['ONEQAY_RUNNING_SOURCE_COMMIT'],$nextCfg['ONEQAY_RUNNING_ARTIFACT_SHA256']);testUi($oldCfg===$nextCfg,'SECRET_SESSION_BUSINESS_CONFIG_PRESERVED');
    deniedUi(fn()=>uCandidateEnv(str_replace(L_SOURCE,str_repeat('f',40),$env),$r),'PREVIOUS_NATIVE_SOURCE_BINDING_INVALID');
    $bad=$r;$bad['runtime_config_delta']=['APP_DEBUG'=>'true'];deniedUi(fn()=>uCandidateEnv($env,$bad),'RUNTIME_CONFIG_CHANGE_DENIED');
    $bad=$r;$bad['module_migrations_allowed']=false;requestUi($bad);deniedUi(fn()=>uRequest(),'REQUEST_SCOPE_INVALID');
    $bad=$r;$bad['module_migration_paths']=['other.php'];requestUi($bad);deniedUi(fn()=>uRequest(),'REQUEST_SCOPE_INVALID');requestUi($r);
    $p=uPaths($r);$bridge=uBridge($r,str_repeat('3',64));testUi(str_contains($bridge,$p['app'])&&str_contains($bridge,$p['env'])&&str_contains($bridge,$p['bridge_id'])&&!str_contains($bridge,L_APP),'NEW_BRIDGE_EXACT_PRIVATE_RELEASE');
    mkdir($p['app'].'/public',0700,true);file_put_contents($p['app'].'/public/.htaccess','RewriteRule ^ index.php [L]');$routing=uRouting($r,"\n# cPanel preserved\n");testUi(str_contains($routing,$p['entry_name'])&&str_contains($routing,'# cPanel preserved')&&!str_contains($routing,L_ENTRY_NAME),'NEW_ROUTING_PRESERVES_HANDLER');
    $old=$tmp.'/old-build';$new=$tmp.'/new-build';mkdir($old);mkdir($new);mkdir($old.'/assets');mkdir($new.'/assets');file_put_contents($old.'/manifest.json','old');file_put_contents($new.'/manifest.json','new');file_put_contents($old.'/assets/old.js','old-code');file_put_contents($new.'/assets/new.js','new-code');
    uUnionBuild($old,$new,$tmp.'/union');testUi(file_get_contents($tmp.'/union/manifest.json')==='new'&&file_get_contents($tmp.'/union/assets/old.js')==='old-code'&&file_get_contents($tmp.'/union/assets/new.js')==='new-code','OLD_AND_NEW_HASHED_ASSETS_RETAINED');
    file_put_contents($new.'/assets/old.js','collision');deniedUi(fn()=>uUnionBuild($old,$new,$tmp.'/collision'),'HASHED_ASSET_COLLISION');unlink($new.'/assets/old.js');
    mkdir(L_APP.'/database/migrations',0700,true);mkdir($p['app'].'/database/migrations',0700,true);mkdir(L_APP.'/public',0700,true);
    for($i=1;$i<=27;$i++){file_put_contents(L_APP.'/database/migrations/'.$i.'.php','same');file_put_contents($p['app'].'/database/migrations/'.$i.'.php','same');}
    file_put_contents(L_APP.'/public/.htaccess',file_get_contents($p['app'].'/public/.htaccess'));
    foreach(['pos/0000_00_00_000028_create_pos_inventory_replenishment_foundation.php','business/2026_10_12_000001_create_integrated_business_management.php','platform/2026_10_13_000001_create_platform_and_notification.php','business/2026_10_14_000001_create_merchandising_and_currency_preferences.php','platform/2026_10_15_000001_create_public_website.php','platform/2026_10_16_000001_create_control_plane_operations.php','pos/2026_10_18_000001_create_desktop_pos_sync.php','pos/2026_10_19_000001_create_desktop_pos_operations.php']as$f){foreach([L_APP,$p['app']]as$a){$m=$a.'/database/module-migrations/'.$f;@mkdir(dirname($m),0700,true);file_put_contents($m,'unchanged');}}
    file_put_contents($p['app'].'/database/module-migrations/business/2026_10_20_000001_create_workforce_accounts_and_shift_recovery.php','new module');
    uHistoricalSchema($p['app']);testUi(true,'HISTORICAL_MIGRATIONS_PRESERVED');file_put_contents($p['app'].'/database/migrations/27.php','changed');deniedUi(fn()=>uHistoricalSchema($p['app']),'HISTORICAL_SCHEMA_CHANGED');file_put_contents($p['app'].'/database/migrations/27.php','same');
    $module=$p['app'].'/database/module-migrations/platform/2026_10_13_000001_create_platform_and_notification.php';file_put_contents($module,'changed');deniedUi(fn()=>uHistoricalSchema($p['app']),'EXISTING_MODULE_SCHEMA_CHANGED');file_put_contents($module,'unchanged');
    // Source manifest rejects tampering, unlisted additions, links, and traversal before binding.
    $payload=$tmp.'/payload';mkdir($payload.'/'.$r['release_id'],0700,true);file_put_contents($payload.'/'.$r['release_id'].'/file.txt','certified');$raw=hash('sha256','certified').'  '.$r['release_id']."/file.txt\n";$small=$r;$small['release_files_sha256']=hash('sha256',$raw);$small['release_file_count']=1;file_put_contents($op.'/release-files.sha256',$raw);
    testUi(uSourceFiles($small,$payload)===1,'SOURCE_MANIFEST_MATCHES');file_put_contents($payload.'/'.$r['release_id'].'/file.txt','tampered');deniedUi(fn()=>uSourceFiles($small,$payload),'CERTIFIED_SOURCE_CHANGED');file_put_contents($payload.'/'.$r['release_id'].'/file.txt','certified');file_put_contents($payload.'/'.$r['release_id'].'/extra.txt','extra');deniedUi(fn()=>uSourceFiles($small,$payload),'UNEXPECTED_SOURCE_FILE');unlink($payload.'/'.$r['release_id'].'/extra.txt');
    symlink('file.txt',$payload.'/'.$r['release_id'].'/link.txt');deniedUi(fn()=>uSourceFiles($small,$payload),'SOURCE_SYMLINK_DENIED');unlink($payload.'/'.$r['release_id'].'/link.txt');
    $raw=hash('sha256','certified').'  '.$r['release_id']."/../escape\n";$small['release_files_sha256']=hash('sha256',$raw);file_put_contents($op.'/release-files.sha256',$raw);deniedUi(fn()=>uSourceFiles($small,$payload),'SOURCE_MANIFEST_PATH_INVALID');
    mkdir(U_PUBLIC,0700,true);mkdir(U_BACKUP,0700,true);file_put_contents(L_ENV,$fixtureEnv);file_put_contents(B_MERCHANT,'fixture-merchant');
    oqCopyTree($old,L_APP.'/public/build');oqCopyTree($old,U_BACKUP.'/build');uUnionBuild($old,$new,U_PUBLIC.'/build');
    $backupFiles=['.htaccess'=>'old-business-routing','index.php'=>'health-only-index','index.html'=>'preserved-placeholder',L_ENTRY_NAME=>'old-business-entry'];$hashes=[];
    foreach($backupFiles as $name=>$value){file_put_contents(U_BACKUP.'/'.$name,$value);file_put_contents(U_PUBLIC.'/'.$name,$name==='.htaccess'?'new-business-routing':$value);$hashes[$name]=hash('sha256',$value);}
    define('L_ASSET_UNION_SHA',oqHashTree($old));
    $snap=['files'=>$hashes,'asset_sha256'=>oqHashTree($old),'previous_env_sha256'=>U_OLD_ENV_SHA,'staging_fingerprint'=>bStagingFingerprint()];file_put_contents(U_BACKUP.'/snapshot.json',json_encode($snap,JSON_THROW_ON_ERROR));
    file_put_contents($p['entry'],'candidate-business-bridge');
    $restored=uRestore($r);testUi(str_contains(file_get_contents($p['entry']),'http_response_code(503)'),'ROLLBACK_DISABLES_CANDIDATE_DIRECT_ENTRY');testUi($restored['rollback_target']==='BUSINESS_ON_PREVIOUS_CERTIFIED_RELEASE'&&oqHashTree(U_PUBLIC.'/build')===oqHashTree($old)&&!file_exists(U_PUBLIC.'/build/assets/new.js'),'ROLLBACK_RESTORES_OLD_ASSETS_AND_BUSINESS_ON');
    testUi(hash_file('sha256',L_ENV)===U_OLD_ENV_SHA&&hash_file('sha256',B_MERCHANT)===B_MERCHANT_SHA,'ROLLBACK_PRESERVES_RUNTIME_MERCHANT');
    $snap['files']['.htaccess']=hash('sha256','health-off');file_put_contents(U_BACKUP.'/snapshot.json',json_encode($snap,JSON_THROW_ON_ERROR));deniedUi(fn()=>uRestore($r),'ROLLBACK_TARGET_NOT_PREVIOUS_BUSINESS_ON');
    testUi(str_contains($source,'uInstallModules($r)')&&!str_contains($source,'artisan migrate')&&!str_contains($source,'Artisan::call')&&!str_contains($source,'bCandidateEnv(')&&str_contains($source,"'BUSINESS_ON_PREVIOUS_CERTIFIED_RELEASE'"),'ONLY_BOUND_ADDITIVE_MODULE_INSTALL_PATH');
    testUi(!str_contains($source,'uPreparedReceiptRecovery(')&&str_contains($source,'PHP_EXTENSION_REQUIRED'),'NO_OLD_RECOVERY_AND_FILE_ENGINE_EXTENSIONS');
    $previous=['state'=>'BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING','business_traffic'=>'ON_PRESERVED','source_commit'=>L_SOURCE,'release_id'=>L_RELEASE,'archive_sha256'=>L_ARCHIVE,'source_integrity_files'=>6387,'native_business_runtime_gate'=>'NATIVE_PRODUCTION_PASS','runtime_sha256'=>U_OLD_ENV_SHA,'bridge_sha256'=>U_OLD_ENTRY_SHA,'routing_sha256'=>U_OLD_ROUTING_SHA,'public_asset_union_sha256'=>L_ASSET_UNION_SHA,'installed_migration_count'=>0,'historical_migrations'=>'PRESERVED','private_runtime_binding'=>'REGULAR_FILE_0600_HASH_VERIFIED'];
    uPreviousReceipt($previous);testUi(true,'PREDECESSOR_IMMUTABLE_RECEIPT');
    $previous['release_retention']=['state'=>'COMPLETE','freed_inodes'=>70000];uPreviousReceipt($previous);testUi(true,'RETENTION_REPORT_MUTATION_ALLOWED');
    foreach(array_keys($previous)as$key){if($key==='release_retention')continue;$bad=$previous;$bad[$key]='tampered';deniedUi(fn()=>uPreviousReceipt($bad),'PREVIOUS_RELEASE_RECEIPT_MISMATCH');}
    echo 'SHIFT_CLOSE_OPERATOR_TOTAL='.$count."\n";
}finally{oqRemoveTree($tmp);}
