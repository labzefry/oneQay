<?php
declare(strict_types=1);

/** Author by Lab | zefry. Exact certified bilingual presentation update from the active 091312 release; preserves business-on rollback. */
const U_ROOT = '/home/pekd7254/oneqay-production-test';
const U_PUBLIC = '/home/pekd7254/public_html/oneqaydev.n07.my.id';
const U_OLD_ACTIVATOR = U_ROOT.'/oneqaydev-business-final-192c8a3f7190/activate.php';
const U_OLD_ACTIVATOR_SHA = 'bd529156ec6327841290643c075063ede635a1288bc6cb1075a42db8ec1a7a49';
const U_OLD_ENV_SHA = '475e25191708e23efc130dd538f2c70d6e8cbb3de5ff630da8ab4571dd47bf94';
const U_OLD_ENTRY_SHA = '23879d62c6df9faa98a3caca440bbd97b75afdd20c545b142506cd8f1760a6c0';
const U_OLD_ROUTING_SHA = '3d0924b1ad07c6248912668345f853836fd8375f61bf8a54cf7130599f96306b';
const U_OLD_REPORT_SHA = '9f3c684d6de2a1b59bb80b52e0d55ba5d222c420c2768664e1a5d7442f5bb36f';

const L_SOURCE = '091312accfd0a0e3f3b7d9e45941852fca7335bc';
const L_RELEASE = 'production-091312accfd0';
const L_APP = U_ROOT.'/releases/'.L_RELEASE.'/apps/web';
const L_ENV = U_ROOT.'/shared/production-business-runtime-091312accfd0-ui.env';
const L_ARCHIVE = 'b68131236e126544ee42004a0c83edb5be174e5cbd77451ef3e8868867686f5d';
const L_ENTRY_NAME = 'oneqay-business-091312accfd0-ui.php';
const L_ENTRY = U_PUBLIC.'/'.L_ENTRY_NAME;
const L_BRIDGE_ID = 'production-091312accfd0-ui';
const L_PREVIOUS_REPORT = U_ROOT.'/oneqaydev-ui-upgrade-091312accfd0/recovery-result.json';
const L_PREVIOUS_FILES = U_ROOT.'/oneqaydev-ui-upgrade-091312accfd0/release-files.sha256';
const L_PREVIOUS_FILES_SHA = 'f57ced13a027958ce1a31673953b9ca1974546efc1ef10ac0a1e19a030679342';
const L_ASSET_UNION_SHA = '7f4e953f4a888fbe6b0120266899b9ce192a2fa9d4dc4413baadfd66e46020a5';
const U_WINDOW = 3600;
const U_REPORT = __DIR__.'/update-result.json';
const U_BACKUP = __DIR__.'/rollback';
final class WorkspaceUpdateStop extends RuntimeException {}
function uAssert(bool $ok, string $code): void { if(!$ok)throw new WorkspaceUpdateStop($code); }
function uRead(string $p,int $max=2000000): string {
    clearstatcache(true,$p);
    uAssert(is_file($p)&&!is_link($p)&&is_readable($p),'REQUIRED_FILE_INVALID');
    $n=filesize($p);uAssert(is_int($n)&&$n>0&&$n<=$max,'FILE_SIZE_INVALID');
    $raw=file_get_contents($p);uAssert(is_string($raw)&&strlen($raw)===$n,'FILE_READ_FAILED');return $raw;
}
function uJson(string $p): array { $v=json_decode(uRead($p),true,32,JSON_THROW_ON_ERROR);uAssert(is_array($v)&&!array_is_list($v),'JSON_OBJECT_REQUIRED');return $v; }
function uWrite(string $p,string $raw,int $mode=0600): void {
    uAssert(!is_link($p),'WRITE_LINK_DENIED');$tmp=$p.'.stage-'.bin2hex(random_bytes(6));$mask=umask(0077);$f=@fopen($tmp,'x');umask($mask);uAssert($f!==false,'STAGE_CREATE_FAILED');
    try {uAssert(@chmod($tmp,$mode)&&fwrite($f,$raw)===strlen($raw),'STAGE_WRITE_FAILED');fclose($f);$f=null;uAssert(@rename($tmp,$p),'ATOMIC_RENAME_FAILED');}
    finally{if(is_resource($f))fclose($f);if(is_file($tmp))@unlink($tmp);}
}
function uReport(array $r): void { $r['attribution']='Lab | zefry';uWrite(U_REPORT,json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n"); }
function uRequest(): array {
    $r=uJson(__DIR__.'/authority-request.json');
    $expected=['schema_version'=>1,'action'=>'CERTIFIED_BILINGUAL_UPDATE_PRESERVE_BUSINESS_ON','domain'=>'oneqaydev.n07.my.id','private_root'=>U_ROOT,'public_root'=>U_PUBLIC,
        'previous_source_commit'=>'091312accfd0a0e3f3b7d9e45941852fca7335bc','previous_runtime_sha256'=>U_OLD_ENV_SHA,'previous_business_entry_sha256'=>U_OLD_ENTRY_SHA,'previous_routing_sha256'=>U_OLD_ROUTING_SHA,
        'merchant_receipt_sha256'=>'e1cddcd1f0334a85b073c0526ecbc6236ba530dbc3a671b00f4d5d18e4a3109a',
        'operator_sha256'=>(string)hash_file('sha256',__FILE__),'rollback_target_state'=>'BUSINESS_ON_PREVIOUS_CERTIFIED_RELEASE','authority_window_seconds'=>U_WINDOW,
        'migrations_allowed'=>false,'merchant_reprovision_allowed'=>false,'staging_allowed'=>false,'updater_allowed'=>false,'external_payments_allowed'=>false];
    foreach($expected as $k=>$v)uAssert(($r[$k]??null)===$v,'REQUEST_SCOPE_INVALID');
    $dynamic=['source_commit','release_id','archive_sha256','manifest_sha256','release_files_sha256','release_file_count','source_certification_run_id'];
    uAssert(count($r)===count($expected)+count($dynamic),'REQUEST_UNKNOWN_FIELDS');
    uAssert(preg_match('/\A[0-9a-f]{40}\z/D',$r['source_commit']??'')===1&&$r['source_commit']!==$expected['previous_source_commit'],'REQUEST_SOURCE_INVALID');
    uAssert(($r['release_id']??null)==='production-'.substr($r['source_commit'],0,12),'REQUEST_RELEASE_INVALID');
    foreach(['archive_sha256','manifest_sha256','release_files_sha256'] as $k)uAssert(preg_match('/\A[0-9a-f]{64}\z/D',$r[$k]??'')===1,'REQUEST_DIGEST_INVALID');
    uAssert(is_int($r['release_file_count'])&&$r['release_file_count']>=6252&&$r['release_file_count']<=6400&&is_int($r['source_certification_run_id'])&&$r['source_certification_run_id']>0,'REQUEST_CERTIFICATION_INVALID');
    return $r;
}
function uPaths(array $r): array {
    $id=$r['release_id'];$short=substr($r['source_commit'],0,12);
    return ['release'=>U_ROOT.'/releases/'.$id,'app'=>U_ROOT.'/releases/'.$id.'/apps/web','env'=>U_ROOT.'/shared/production-business-runtime-'.$short.'-ui.env',
        'entry_name'=>'oneqay-business-'.$short.'-ui.php','entry'=>U_PUBLIC.'/oneqay-business-'.$short.'-ui.php','bridge_id'=>$id.'-ui'];
}
function uLoad(array $r): void {
    uAssert(PHP_SAPI==='cli'&&PHP_VERSION_ID>=80200,'PHP_CLI_REQUIRED');
    foreach(['curl','pdo_mysql','mbstring','openssl','ctype','tokenizer','fileinfo'] as $e)uAssert(extension_loaded($e),'PHP_EXTENSION_REQUIRED');
    uAssert(realpath(U_ROOT)===U_ROOT&&realpath(U_PUBLIC)===U_PUBLIC&&realpath(U_ROOT.'/releases')===U_ROOT.'/releases','HOST_PATH_INVALID');
    uAssert(realpath(__DIR__)===U_ROOT.'/oneqaydev-language-update-'.substr($r['source_commit'],0,12)&&!is_link(__DIR__),'OPERATOR_DIRECTORY_INVALID');
    uAssert(hash_equals(U_OLD_ACTIVATOR_SHA,hash('sha256',uRead(U_OLD_ACTIVATOR))),'PREVIOUS_ACTIVATOR_CHANGED');require_once U_OLD_ACTIVATOR;
    uAssert(hash_equals(B_HELPER_SHA,hash('sha256',uRead(B_HELPER))),'PREVIOUS_INTEGRITY_HELPER_CHANGED');require_once B_HELPER;
}
function uApprovalBody(string $sha,array $r): string {
    return "ONEQAYDEV PRODUCTION BILINGUAL UPDATE AUTHORIZATION\nREQUEST_SHA256: ".$sha."\nTARGET: https://oneqaydev.n07.my.id\nRELEASE: ".$r['release_id']."\nSOURCE_COMMIT: ".$r['source_commit']."\nARTIFACT_SHA256: ".$r['archive_sha256']
        ."\nPRESENTATION_UPDATE: GRANTED\nPRESERVE_BUSINESS_ON: REQUIRED\nROLLBACK: GRANTED\nMIGRATIONS: NOT_ALLOWED\nMERCHANT_REPROVISION: NOT_ALLOWED\nSTAGING: NOT_ALLOWED\nUPDATER: NOT_ALLOWED\nEXTERNAL_PAYMENTS: NOT_ALLOWED\nWINDOW_SECONDS: ".U_WINDOW."\nAuthor by Lab | zefry";
}
function uQualifyApproval(array $c,int $id,string $sha,array $r,int $now): array {
    uAssert(($c['id']??null)===$id&&($c['user']['login']??null)==='labzefry'&&($c['user']['type']??null)==='User'&&($c['author_association']??null)==='OWNER','NATIVE_OWNER_AUTHORITY_INVALID');
    uAssert(($c['issue_url']??null)==='https://api.github.com/repos/labzefry/oneQay/issues/946','AUTHORITY_REPOSITORY_INVALID');$want=uApprovalBody($sha,$r);$body=$c['body']??null;
    uAssert(is_string($body)&&($body===$want||str_starts_with($body,$want."\n\n")),'EXACT_WORKSPACE_GRANT_MISSING');
    $created=$c['created_at']??'';uAssert(is_string($created)&&preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z\z/D',$created)===1&&($c['updated_at']??null)===$created,'AUTHORITY_TIMESTAMP_INVALID');
    $start=strtotime($created);uAssert(is_int($start)&&$now>=$start&&$now<=$start+U_WINDOW,'AUTHORITY_WINDOW_EXPIRED');
    return ['approval_comment_id'=>$id,'request_sha256'=>$sha,'authorized_at_unix'=>$start,'expires_at_unix'=>$start+U_WINDOW];
}
function uAuthority(string $locator,array $r,?int $at=null): array {
    uAssert(preg_match('/\A[1-9][0-9]{0,17}\z/D',$locator)===1,'AUTHORITY_LOCATOR_INVALID');$id=(int)$locator;
    $response=bHttps('https://api.github.com/repos/labzefry/oneQay/issues/comments/'.$id,'application/vnd.github+json');uAssert($response['status']===200,'NATIVE_AUTHORITY_LOOKUP_FAILED');
    $c=json_decode($response['body'],true,32,JSON_THROW_ON_ERROR);uAssert(is_array($c),'NATIVE_AUTHORITY_RESPONSE_INVALID');
    return uQualifyApproval($c,$id,hash('sha256',uRead(__DIR__.'/authority-request.json')),$r,$at??time());
}
function uSourceFiles(array $r,string $root,bool $runtime=false): int {
    $raw=uRead(__DIR__.'/release-files.sha256');uAssert(hash_equals($r['release_files_sha256'],hash('sha256',$raw)),'SOURCE_MANIFEST_CHANGED');$seen=[];$id=$r['release_id'];
    foreach(explode("\n",trim($raw)) as $line){uAssert(preg_match('/\A([0-9a-f]{64})  ('.preg_quote($id,'/').'\/[A-Za-z0-9._+@\/-]+)\z/D',$line,$m)===1&&!str_contains('/'.$m[2].'/','/../')&&!str_contains($m[2],'//')&&!isset($seen[$m[2]]),'SOURCE_MANIFEST_PATH_INVALID');
        $p=$root.'/'.$m[2];uAssert(is_file($p)&&!is_link($p)&&hash_equals($m[1],(string)hash_file('sha256',$p)),'CERTIFIED_SOURCE_CHANGED');$seen[$m[2]]=true;}
    uAssert(count($seen)===$r['release_file_count'],'SOURCE_FILE_COUNT_INVALID');
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$id,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST) as $e){$rel=substr($e->getPathname(),strlen($root)+1);if($runtime&&$rel===$id.'/apps/web/.env')continue;uAssert(!$e->isLink(),'SOURCE_SYMLINK_DENIED');if($e->isDir())continue;
        if(isset($seen[$rel]))continue;$allowed=$runtime&&(str_starts_with($rel,$id.'/apps/web/storage/framework/views/')||str_starts_with($rel,$id.'/apps/web/storage/framework/cache/data/')||str_starts_with($rel,$id.'/apps/web/storage/logs/')||in_array($rel,[$id.'/apps/web/bootstrap/cache/packages.php',$id.'/apps/web/bootstrap/cache/services.php'],true));uAssert($e->isFile()&&$allowed,'UNEXPECTED_SOURCE_FILE');}
    return count($seen);
}
function lPreviousIntegrity(): int {
    $r=['release_files_sha256'=>L_PREVIOUS_FILES_SHA,'release_id'=>L_RELEASE,'release_file_count'=>6256];$root=U_ROOT.'/releases';$runtime=true;
    $raw=uRead(L_PREVIOUS_FILES);uAssert(hash_equals($r['release_files_sha256'],hash('sha256',$raw)),'SOURCE_MANIFEST_CHANGED');$seen=[];$id=$r['release_id'];
    foreach(explode("\n",trim($raw)) as $line){uAssert(preg_match('/\A([0-9a-f]{64})  ('.preg_quote($id,'/').'\/[A-Za-z0-9._+@\/-]+)\z/D',$line,$m)===1&&!str_contains('/'.$m[2].'/','/../')&&!str_contains($m[2],'//')&&!isset($seen[$m[2]]),'SOURCE_MANIFEST_PATH_INVALID');
        $p=$root.'/'.$m[2];uAssert(is_file($p)&&!is_link($p)&&hash_equals($m[1],(string)hash_file('sha256',$p)),'CERTIFIED_SOURCE_CHANGED');$seen[$m[2]]=true;}
    uAssert(count($seen)===$r['release_file_count'],'SOURCE_FILE_COUNT_INVALID');
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$id,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST) as $e){$rel=substr($e->getPathname(),strlen($root)+1);if($runtime&&$rel===$id.'/apps/web/.env')continue;uAssert(!$e->isLink(),'SOURCE_SYMLINK_DENIED');if($e->isDir())continue;
        if(isset($seen[$rel]))continue;$allowed=$runtime&&(str_starts_with($rel,$id.'/apps/web/storage/framework/views/')||str_starts_with($rel,$id.'/apps/web/storage/framework/cache/data/')||str_starts_with($rel,$id.'/apps/web/storage/logs/')||in_array($rel,[$id.'/apps/web/bootstrap/cache/packages.php',$id.'/apps/web/bootstrap/cache/services.php'],true));uAssert($e->isFile()&&$allowed,'UNEXPECTED_SOURCE_FILE');}
    return count($seen);
}
function lPreviousPublicVerify(): void {
    uPublicVerify(['source_commit'=>L_SOURCE,'release_id'=>L_RELEASE]);
}
function uCheckPackage(array $r): void {
    $id=$r['release_id'];$tar=__DIR__.'/'.$id.'.tar.gz';uAssert(is_file($tar)&&!is_link($tar)&&hash_equals($r['archive_sha256'],(string)hash_file('sha256',$tar)),'CERTIFIED_ARCHIVE_CHANGED');
    $raw=uRead(__DIR__.'/'.$id.'.manifest.json');uAssert(hash_equals($r['manifest_sha256'],hash('sha256',$raw)),'CERTIFIED_MANIFEST_CHANGED');$m=json_decode($raw,true,32,JSON_THROW_ON_ERROR);
    uAssert(($m['source']['commit_sha']??null)===$r['source_commit']&&($m['release']['id']??null)===$id&&($m['artifact']['sha256']??null)===$r['archive_sha256']&&($m['artifact']['size_bytes']??null)===filesize(__DIR__.'/'.$id.'.tar.gz')&&($m['build']['provenance_reference']??null)==='github-actions:'.$r['source_certification_run_id'].':1:source-tested'&&($m['runtime']['source_ci_certified']??false)===true&&($m['runtime']['business_runtime_activation_ready']??false)===true&&($m['runtime']['host_business_acceptance_verified']??true)===false,'CERTIFICATION_BINDING_INVALID');
    $payload=__DIR__.'/payload';uAssert(realpath($payload)===$payload&&!is_link($payload),'PAYLOAD_PATH_INVALID');uSourceFiles($r,$payload);
    $meta=uJson($payload.'/'.$id.'/RELEASE.json');uAssert(($meta['release_id']??null)===$id&&($meta['source_commit']??null)===$r['source_commit']&&($meta['source_ci_certified']??false)===true&&($meta['business_runtime_activation_ready']??false)===true&&($meta['migration_count']??null)===27,'RELEASE_METADATA_INVALID');
}
function uPresentationOnly(string $newApp): void {
    foreach(['app','config','database','routes','resources/views','bootstrap'] as $rel){$left=L_APP.'/'.$rel;$right=$newApp.'/'.$rel;
        $files=[];foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($left,FilesystemIterator::SKIP_DOTS)) as $e){if(!$e->isFile())continue;$key=substr($e->getPathname(),strlen($left)+1);if($rel==='bootstrap'&&str_starts_with($key,'cache/'))continue;uAssert(!$e->isLink(),'PREVIOUS_BACKEND_LINK_DENIED');$files[$key]=(string)hash_file('sha256',$e->getPathname());}
        $actual=[];foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($right,FilesystemIterator::SKIP_DOTS)) as $e){if(!$e->isFile())continue;$key=substr($e->getPathname(),strlen($right)+1);if($rel==='bootstrap'&&str_starts_with($key,'cache/'))continue;uAssert(!$e->isLink(),'NEW_BACKEND_LINK_DENIED');if($rel==='resources/views'&&$key==='app.blade.php'){$oldView=uRead($left.'/'.$key);uAssert(substr_count($oldView,'<html lang="en">')===1&&uRead($e->getPathname())===str_replace('<html lang="en">','<html lang="id">',$oldView),'DOCUMENT_LANGUAGE_ONLY_REQUIRED');$actual[$key]=$files[$key];}else $actual[$key]=(string)hash_file('sha256',$e->getPathname());}ksort($files);ksort($actual);uAssert($files===$actual,'BACKEND_OR_SCHEMA_CHANGE_DENIED');}
    foreach(['composer.json','composer.lock','package.json','package-lock.json','artisan','public/index.php','public/.htaccess'] as $rel)uAssert(hash_equals((string)hash_file('sha256',L_APP.'/'.$rel),(string)hash_file('sha256',$newApp.'/'.$rel)),'LOCK_OR_ENTRY_CHANGE_DENIED');
}
function uCandidateEnv(string $old,array $r): string {
    $cfg=oqReadEnv($old);uAssert(($cfg['ONEQAY_RUNNING_SOURCE_COMMIT']??null)===L_SOURCE&&($cfg['ONEQAY_RUNNING_ARTIFACT_SHA256']??null)===L_ARCHIVE,'PREVIOUS_NATIVE_SOURCE_BINDING_INVALID');
    $new=$old;foreach(['ONEQAY_RUNNING_SOURCE_COMMIT'=>$r['source_commit'],'ONEQAY_RUNNING_ARTIFACT_SHA256'=>$r['archive_sha256']] as $key=>$value){$new=preg_replace('/^'.preg_quote($key,'/').'=[^\r\n]*$/m',$key.'='.$value,$new,-1,$n);uAssert(is_string($new)&&$n===1,'SOURCE_ENV_BINDING_INVALID');}
    $next=oqReadEnv($new);unset($cfg['ONEQAY_RUNNING_SOURCE_COMMIT'],$cfg['ONEQAY_RUNNING_ARTIFACT_SHA256'],$next['ONEQAY_RUNNING_SOURCE_COMMIT'],$next['ONEQAY_RUNNING_ARTIFACT_SHA256']);uAssert($cfg===$next,'MERCHANT_RUNTIME_CONFIG_CHANGE_DENIED');return $new;
}
function uBridge(array $r,string $envSha): string {
    $p=uPaths($r);$old=bBridgeFor($envSha);return str_replace([var_export(B_APP,true),var_export(B_ENV,true),B_BRIDGE_ID],[var_export($p['app'],true),var_export($p['env'],true),$p['bridge_id']],$old);
}
function uRouting(array $r,string $hosting): string { return str_replace(B_ENTRY_NAME,uPaths($r)['entry_name'],bRewriteFor(uRead(uPaths($r)['app'].'/public/.htaccess'),$hosting)); }
function uPreconditions(array $r): array {
    bPrivate(L_ENV);uAssert(hash_equals(U_OLD_ENV_SHA,(string)hash_file('sha256',L_ENV))&&hash_equals(U_OLD_ENV_SHA,(string)hash_file('sha256',L_APP.'/.env')),'PREVIOUS_ENV_CHANGED');
    uAssert(hash_equals(U_OLD_ENTRY_SHA,hash('sha256',uRead(L_ENTRY)))&&hash_equals(U_OLD_ROUTING_SHA,hash('sha256',uRead(U_PUBLIC.'/.htaccess')))&&hash_equals(B_BRIDGE,hash('sha256',uRead(U_PUBLIC.'/index.php'))),'PREVIOUS_PUBLIC_BINDING_CHANGED');
    uAssert(hash_equals(U_OLD_REPORT_SHA,hash('sha256',uRead(L_PREVIOUS_REPORT))),'PREVIOUS_HOST_REPORT_CHANGED');
    $host=uJson(L_PREVIOUS_REPORT);uAssert(($host['state']??null)==='BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING'&&($host['source_commit']??null)===L_SOURCE,'PREVIOUS_BUSINESS_NOT_ACTIVE');
    lPreviousIntegrity();uAssert(hash_equals(L_ASSET_UNION_SHA,oqHashTree(U_PUBLIC.'/build')),'PREVIOUS_PUBLIC_ASSETS_CHANGED');lPreviousPublicVerify();$merchant=bMerchant();$raw=uRead(L_ENV);$database=bDatabase(oqReadEnv($raw),$merchant);
    uCheckPackage($r);uPresentationOnly(__DIR__.'/payload/'.$r['release_id'].'/apps/web');$p=uPaths($r);
    uAssert(!file_exists($p['release'])&&!is_link($p['release'])&&!file_exists($p['env'])&&!is_link($p['env'])&&!file_exists($p['entry'])&&!is_link($p['entry'])&&!file_exists(U_BACKUP)&&!is_link(U_BACKUP),'UPDATE_ALREADY_PREPARED_NO_AUTOMATIC_RETRY');
    return ['raw_env'=>$raw,'merchant'=>$merchant,'database'=>$database,'staging_fingerprint'=>bStagingFingerprint(),'asset_sha256'=>oqHashTree(U_PUBLIC.'/build')];
}
function uBindExistingRuntime(string $env,string $target): void {
    uAssert(is_file($env)&&!is_link($env)&&((int)fileperms($env)&0077)===0,'PRIVATE_RUNTIME_INVALID');
    uAssert(!file_exists($target)&&!is_link($target),'RUNTIME_TARGET_ALREADY_EXISTS');
    $linked=function_exists('link')&&@link($env,$target);
    if(!$linked&&function_exists('symlink'))$linked=@symlink($env,$target);
    uAssert($linked,'PRIVATE_LINK_CAPABILITY_UNAVAILABLE');
    clearstatcache(true,$target);
    uAssert(hash_equals((string)hash_file('sha256',$env),(string)hash_file('sha256',$target))&&((int)fileperms($target)&0077)===0,'PRIVATE_RUNTIME_READBACK_FAILED');
}
function uRuntime(array $r): void {
    $p=uPaths($r);require_once $p['app'].'/vendor/autoload.php';$app=require $p['app'].'/bootstrap/app.php';$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    uAssert(config('app.env')==='production'&&config('app.debug')===false&&config('oneqay.runtime_class')==='production'&&config('database.oneqay_persistence_enabled')===true,'NEW_RUNTIME_CONFIG_INVALID');
    uAssert(\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows('production')&&env('ONEQAY_RUNNING_SOURCE_COMMIT')===$r['source_commit']&&env('ONEQAY_RUNNING_ARTIFACT_SHA256')===$r['archive_sha256'],'NEW_NATIVE_BUSINESS_GATE_DENIED');
    uAssert(config('session.driver')==='file'&&config('session.files')===OQ_SESSIONS&&config('session.secure')===true&&config('session.http_only')===true&&config('session.same_site')==='lax'&&config('session.domain')===null&&config('session.cookie')==='oneqaydev-production-session','SESSION_CONTINUITY_INVALID');
    foreach(bMerchant()['merchant'] as $k=>$v)if($k!=='provisioning_id')uAssert(config('merchant_context_bootstrap.grant.'.$k)===$v,'MERCHANT_CONTEXT_CHANGED');
    uAssert(config('merchant_context_bootstrap.enabled')===false&&config('oneqay.privileged_totp_mfa.enabled')===true&&config('oneqay.privileged_step_up.enabled')===true&&config('oneqay.session_control.enabled')===true&&config('oneqay.session_control.idle_ttl_seconds')===7200&&config('oneqay.session_control.absolute_ttl_seconds')===43200,'AUTHENTICATION_POLICY_CHANGED');
    foreach(['auth.first-party.login','auth.privileged-totp.enrollment.start','auth.privileged-totp.challenge','pos.operations.hub','pos.sales.complete','pos.shifts.close'] as $route)uAssert(\Illuminate\Support\Facades\Route::has($route),'NATIVE_ROUTE_MISSING');
    $h=$app->make('session')->driver()->getHandler();$id=bin2hex(random_bytes(20));$marker=bin2hex(random_bytes(24));try{uAssert($h->write($id,$marker)&&$h->read($id)===$marker,'SESSION_STORAGE_PROBE_FAILED');}finally{uAssert($h->destroy($id),'SESSION_PROBE_CLEANUP_FAILED');}
}
function uPublicVerify(array $r): array {
    oqVerifyHttps('/health/live','ok');oqVerifyHttps('/health/ready','ready');$p=uPaths($r);$probe=bin2hex(random_bytes(12));$evidence=[];
    foreach(['merchant_entry'=>['/','text/html'],'unauthenticated_pos'=>['/pos','application/json']] as $key=>[$url,$accept]){$v=bHttps('https://oneqaydev.n07.my.id'.$url.'?oneqay_ui_probe='.$probe,$accept);$e=bEntryEvidence($v);uAssert(preg_match('/^X-OneQay-Business-Bridge:\s*'.preg_quote($p['bridge_id'],'/').'\s*\r?$/im',$v['headers'])===1&&$e['entry_phase']==='native-gate-passed'&&$e['missing_php_capabilities']===[],'NEW_HTTPS_NATIVE_ENTRY_INVALID');$e['bridge_identity']='EXPECTED';
        if($key==='merchant_entry'&&$r['source_commit']!==L_SOURCE)uAssert(str_contains($v['body'],'<html lang="id">'),'INDONESIAN_DOCUMENT_DEFAULT_MISSING');
        if($key==='merchant_entry')uAssert($e['status']===200&&$e['merchant_entry_enabled']&&$e['login_context_present']&&$e['secure_session_cookie'],'NEW_MERCHANT_HTTPS_INVALID');else uAssert(in_array($e['status'],[401,403],true),'UNAUTHENTICATED_POS_NOT_DENIED');$evidence[$key]=$e;}
    $manifest=uJson($p['app'].'/public/build/manifest.json');$entry=$manifest['resources/js/app.ts']??null;uAssert(is_array($entry)&&is_string($entry['file']??null),'NEW_BUILD_ENTRY_INVALID');
    foreach(array_merge([$entry['file']],$entry['css']??[]) as $rel){uAssert(is_string($rel)&&preg_match('~\Aassets/[A-Za-z0-9._-]+\.(js|css)\z~D',$rel)===1,'ASSET_PATH_INVALID');$v=bHttps('https://oneqaydev.n07.my.id/build/'.$rel.'?oneqay_ui_probe='.$probe,'*/*');uAssert($v['status']===200&&hash_equals((string)hash_file('sha256',$p['app'].'/public/build/'.$rel),hash('sha256',$v['body'])),'NEW_ASSET_HTTPS_INVALID');}
    return $evidence;
}
function uUnionBuild(string $old,string $new,string $dest): void {
    oqCopyTree($old,$dest,0755);foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($new,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST) as $e){uAssert(!$e->isLink(),'NEW_BUILD_LINK_DENIED');$rel=substr($e->getPathname(),strlen($new)+1);$p=$dest.'/'.$rel;if($e->isDir()){if(!is_dir($p))uAssert(@mkdir($p,0755),'ASSET_DIRECTORY_FAILED');continue;}
        uAssert($e->isFile(),'NEW_BUILD_TYPE_INVALID');if(is_file($p)&&$rel!=='manifest.json')uAssert(hash_equals((string)hash_file('sha256',$p),(string)hash_file('sha256',$e->getPathname())),'HASHED_ASSET_COLLISION');uAssert(@copy($e->getPathname(),$p)&&@chmod($p,0644),'ASSET_COPY_FAILED');}
}
function uRestore(array $r): array {
    $snapshot=uJson(U_BACKUP.'/snapshot.json');uAssert(($snapshot['previous_env_sha256']??null)===U_OLD_ENV_SHA&&hash_equals(U_OLD_ENV_SHA,(string)hash_file('sha256',L_ENV)),'ROLLBACK_RUNTIME_CHANGED');
    $expected=['.htaccess'=>U_OLD_ROUTING_SHA,'index.php'=>B_BRIDGE,L_ENTRY_NAME=>U_OLD_ENTRY_SHA];
    uAssert(count($snapshot['files']??[])===4&&isset($snapshot['files']['index.html']),'ROLLBACK_SNAPSHOT_INCOMPLETE');
    foreach($expected as $name=>$sha)uAssert(($snapshot['files'][$name]??null)===$sha,'ROLLBACK_TARGET_NOT_PREVIOUS_BUSINESS_ON');
    foreach($snapshot['files']??[] as $name=>$sha){uAssert(in_array($name,['.htaccess','index.php','index.html',L_ENTRY_NAME],true)&&hash_equals($sha,hash('sha256',uRead(U_BACKUP.'/'.$name))),'ROLLBACK_SNAPSHOT_INVALID');}
    uAssert(hash_equals($snapshot['asset_sha256'],oqHashTree(U_BACKUP.'/build'))&&hash_equals($snapshot['asset_sha256'],L_ASSET_UNION_SHA),'ROLLBACK_ASSETS_CHANGED');
    $p=uPaths($r);$temp=U_PUBLIC.'/.oq-ui-restore-'.bin2hex(random_bytes(6));oqCopyTree(U_BACKUP.'/build',$temp,0755);$failed=U_PUBLIC.'/.oq-ui-replaced-'.bin2hex(random_bytes(6));uAssert(@rename(U_PUBLIC.'/build',$failed),'ROLLBACK_BUILD_RENAME_FAILED');
    if(!@rename($temp,U_PUBLIC.'/build')){@rename($failed,U_PUBLIC.'/build');throw new WorkspaceUpdateStop('ROLLBACK_BUILD_PROMOTION_FAILED');}
    foreach($snapshot['files'] as $name=>$sha)uWrite(U_PUBLIC.'/'.$name,uRead(U_BACKUP.'/'.$name),0644);
    lPreviousPublicVerify();uAssert(hash_equals($snapshot['staging_fingerprint'],bStagingFingerprint())&&hash_equals(B_MERCHANT_SHA,(string)hash_file('sha256',B_MERCHANT)),'ROLLBACK_PRESERVATION_FAILED');
    // Best-effort garbage cleanup never deletes either immutable release or runtime binding.
    try{oqRemoveTree($failed);}catch(Throwable){}
    return ['rollback_target'=>'BUSINESS_ON_PREVIOUS_CERTIFIED_RELEASE','previous_source_commit'=>L_SOURCE,'merchant_preserved'=>true,'sessions_preserved'=>true,'rollback_https_verified'=>true];
}
function uApply(string $locator,array $r): array {
    $grant=uAuthority($locator,$r);$pre=uPreconditions($r);uAssert(time()<=$grant['expires_at_unix'],'AUTHORITY_WINDOW_EXPIRED');$p=uPaths($r);$changed=false;$buildChanged=false;$previousBuild=null;
    try {
        uAssert(@mkdir(U_BACKUP,0700)&&@chmod(U_BACKUP,0700),'ROLLBACK_DIRECTORY_FAILED');$files=[];
        foreach(['.htaccess','index.php','index.html',L_ENTRY_NAME] as $name){$raw=uRead(U_PUBLIC.'/'.$name);uWrite(U_BACKUP.'/'.$name,$raw);$files[$name]=hash('sha256',$raw);}oqCopyTree(U_PUBLIC.'/build',U_BACKUP.'/build',0700);
        $snap=['files'=>$files,'asset_sha256'=>$pre['asset_sha256'],'previous_env_sha256'=>U_OLD_ENV_SHA,'staging_fingerprint'=>$pre['staging_fingerprint']];uWrite(U_BACKUP.'/snapshot.json',json_encode($snap,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
        uAssert(hash_equals($pre['asset_sha256'],oqHashTree(U_BACKUP.'/build')),'ROLLBACK_ASSET_READBACK_FAILED');foreach($files as $name=>$sha)uAssert(hash_equals($sha,(string)hash_file('sha256',U_BACKUP.'/'.$name)),'ROLLBACK_FILE_READBACK_FAILED');
        oqCopyTree(__DIR__.'/payload/'.$r['release_id'],$p['release']);uSourceFiles($r,U_ROOT.'/releases');uPresentationOnly($p['app']);
        $env=uCandidateEnv($pre['raw_env'],$r);uWrite($p['env'],$env);uBindExistingRuntime($p['env'],$p['app'].'/.env');
        foreach(['config.php','routes-v7.php','routes.php'] as $file)uAssert(!file_exists($p['app'].'/bootstrap/cache/'.$file)&&!is_link($p['app'].'/bootstrap/cache/'.$file),'CACHED_RUNTIME_DENIED');
        uRuntime($r);uSourceFiles($r,U_ROOT.'/releases',true);$envSha=hash('sha256',$env);$bridge=uBridge($r,$envSha);$routing=uRouting($r,uRead(U_PUBLIC.'/.htaccess'));
        $token=bin2hex(random_bytes(6));$stage=U_PUBLIC.'/.oq-ui-build-'.$token;uUnionBuild(U_PUBLIC.'/build',$p['app'].'/public/build',$stage);$stageSha=oqHashTree($stage);
        uAssert(hash_equals(U_OLD_ENV_SHA,(string)hash_file('sha256',L_ENV))&&hash_equals(B_MERCHANT_SHA,(string)hash_file('sha256',B_MERCHANT))&&hash_equals($pre['staging_fingerprint'],bStagingFingerprint()),'PRE_SWITCH_PRESERVATION_FAILED');
        uAssert(time()<=$grant['expires_at_unix'],'AUTHORITY_WINDOW_EXPIRED');uWrite($p['entry'],$bridge,0644);$previousBuild=U_PUBLIC.'/.oq-ui-old-build-'.$token;
        uAssert(@rename(U_PUBLIC.'/build',$previousBuild),'OLD_BUILD_RENAME_FAILED');$buildChanged=true;uAssert(@rename($stage,U_PUBLIC.'/build'),'NEW_BUILD_PROMOTION_FAILED');
        $changed=true;uWrite(U_PUBLIC.'/.htaccess',$routing,0644);$evidence=uPublicVerify($r);
        uAssert(hash_equals($stageSha,oqHashTree(U_PUBLIC.'/build'))&&hash_equals(hash('sha256',$bridge),(string)hash_file('sha256',$p['entry']))&&hash_equals(hash('sha256',$routing),(string)hash_file('sha256',U_PUBLIC.'/.htaccess'))&&hash_equals(B_BRIDGE,(string)hash_file('sha256',U_PUBLIC.'/index.php')),'PUBLIC_SWITCH_READBACK_FAILED');
        uAssert(hash_equals(U_OLD_ENV_SHA,(string)hash_file('sha256',L_ENV))&&hash_equals($envSha,(string)hash_file('sha256',$p['app'].'/.env'))&&hash_equals(B_MERCHANT_SHA,(string)hash_file('sha256',B_MERCHANT))&&hash_equals($pre['staging_fingerprint'],bStagingFingerprint()),'POST_SWITCH_PRESERVATION_FAILED');
        $out=array_merge(['state'=>'BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING','business_traffic'=>'ON_PRESERVED','source_commit'=>$r['source_commit'],'release_id'=>$r['release_id'],'previous_source_commit'=>L_SOURCE,'archive_sha256'=>$r['archive_sha256'],'source_integrity_files'=>$r['release_file_count'],'native_business_runtime_gate'=>'NATIVE_PRODUCTION_PASS','merchant_preserved'=>true,'sessions_preserved'=>true,'session_storage_probe'=>'FILE_WRITE_READ_DELETE_PASS','runtime_sha256'=>$envSha,'bridge_sha256'=>hash('sha256',$bridge),'routing_sha256'=>hash('sha256',$routing),'public_asset_union_sha256'=>$stageSha,'default_language'=>'id','supported_languages'=>['id','en'],'old_hashed_assets_retained'=>true,'rollback_snapshot_verified'=>true,'rollback_target'=>'BUSINESS_ON_PREVIOUS_CERTIFIED_RELEASE','https_evidence'=>$evidence,'migrations'=>'NOT_TOUCHED','staging'=>'NOT_TOUCHED','updater'=>'INACTIVE','external_payments'=>'NOT_PERFORMED','host_business_acceptance'=>'NOT_VERIFIED'], $grant,$pre['database']);uReport($out);
        try{oqRemoveTree($previousBuild);}catch(Throwable){}return $out;
    }catch(Throwable $e){$rollback='PUBLIC_SWITCH_NOT_PERFORMED';
        if($buildChanged){try{if(!is_dir(U_PUBLIC.'/build')&&is_string($previousBuild))uAssert(@rename($previousBuild,U_PUBLIC.'/build'),'BUILD_RESTORE_FAILED');uRestore($r);$rollback='BUSINESS_ON_PREVIOUS_RELEASE_RESTORED';}catch(Throwable){$rollback='ROLLBACK_FAILED_OWNER_ATTENTION_REQUIRED';}}
        uReport(['state'=>'STOPPED_FAIL_CLOSED','error_code'=>$e instanceof WorkspaceUpdateStop||$e instanceof BusinessActivationStop||$e instanceof OQStop?$e->getMessage():'INTERNAL_ERROR','rollback_state'=>$rollback,'source_commit'=>$r['source_commit'],'business_acceptance'=>'NOT_VERIFIED']);throw $e;}
}
function uReplay(string $locator,array $r,array $prior): array {
    uAssert(($prior['state']??null)==='BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING'&&($prior['source_commit']??null)===$r['source_commit']&&is_int($prior['authorized_at_unix']??null),'PREVIOUS_UPDATE_STOPPED_NO_AUTOMATIC_RETRY');
    $grant=uAuthority($locator,$r,$prior['authorized_at_unix']);foreach($grant as $k=>$v)uAssert(($prior[$k]??null)===$v,'REPLAY_AUTHORITY_CHANGED');$p=uPaths($r);
    foreach(['runtime_sha256'=>$p['env'],'bridge_sha256'=>$p['entry'],'routing_sha256'=>U_PUBLIC.'/.htaccess'] as $k=>$path)uAssert(hash_equals($prior[$k],hash('sha256',uRead($path))),'REPLAY_BINDING_CHANGED');
    uAssert(hash_equals($prior['public_asset_union_sha256'],oqHashTree(U_PUBLIC.'/build'))&&hash_equals(U_OLD_ENV_SHA,(string)hash_file('sha256',L_ENV))&&hash_equals(B_MERCHANT_SHA,(string)hash_file('sha256',B_MERCHANT))&&hash_equals(uJson(U_BACKUP.'/snapshot.json')['staging_fingerprint'],bStagingFingerprint()),'REPLAY_PRESERVATION_FAILED');
    uSourceFiles($r,U_ROOT.'/releases',true);uRuntime($r);uPublicVerify($r);$prior['idempotent_replay']=true;uReport($prior);return $prior;
}
if(PHP_SAPI==='cli'&&realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__){
    if(!in_array($argv[1]??'',['update','rollback'],true)||(($argv[1]==='update')?$argc!==3:$argc!==2)){fwrite(STDERR,"USAGE: php update.php update OWNER_COMMENT_ID | rollback\n");exit(64);}
    try{$r=uRequest();uLoad($r);$lock=@fopen(__DIR__.'/update.lock','c');uAssert($lock!==false&&@chmod(__DIR__.'/update.lock',0600)&&flock($lock,LOCK_EX|LOCK_NB),'UPDATE_LOCK_UNAVAILABLE');
        if($argv[1]==='rollback'){$out=array_merge(['state'=>'BUSINESS_ON_ROLLBACK_VERIFIED'],uRestore($r));uReport($out);}elseif(is_file(U_REPORT))$out=uReplay($argv[2],$r,uJson(U_REPORT));else $out=uApply($argv[2],$r);
        fwrite(STDOUT,"ONEQAYDEV|".$out['state']."\n");exit(0);
    }catch(Throwable $e){$code=$e instanceof WorkspaceUpdateStop||$e instanceof BusinessActivationStop||$e instanceof OQStop?$e->getMessage():'INTERNAL_ERROR';if(!is_file(U_REPORT))try{uReport(['state'=>'STOPPED_FAIL_CLOSED','error_code'=>$code,'public_switch'=>'NOT_PERFORMED','host_business_acceptance'=>'NOT_VERIFIED']);}catch(Throwable){}fwrite(STDERR,"ONEQAYDEV|STOPPED|".$code."\n");exit(1);}
}
