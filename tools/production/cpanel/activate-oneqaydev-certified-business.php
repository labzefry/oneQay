<?php
declare(strict_types=1);

/**
 * oneQayDev exact-installed-source business activation successor.
 * Author by Lab | zefry.
 * Requires an independently authenticated, exact-request Product Owner grant.
 * Never grants its own authority, provisions, migrates, writes SQL, or changes staging.
 */
const B_ROOT = '/home/pekd7254/oneqay-production-test';
const B_PUBLIC = '/home/pekd7254/public_html/oneqaydev.n07.my.id';
const B_STAGING = '/home/pekd7254/public_html/oneqay.n07.my.id';
const B_SOURCE = '192c8a3f719006ecdc5f8a4216d09c285b747e59';
const B_RELEASE = 'production-192c8a3f7190';
const B_ARCHIVE = 'ff1a4e92eba9d2e5c79c33acc8aa788d4561863541242d16674094704ac0325f';
const B_BRIDGE = '6870348b43644d61fd80d7dcef4bdc4b9158a1a314ce558d6752dda8ab1c2934';
const B_MERCHANT_SHA = 'e1cddcd1f0334a85b073c0526ecbc6236ba530dbc3a671b00f4d5d18e4a3109a';
const B_HELPER_SHA = 'ec4930fdac26d265a48ad0ad56d9d74fdeb52c0bbc1a7ffd03eb8cb385e9c776';
const B_HELPER = B_ROOT.'/oneqaydev-upgrade-192c8a3f7190/upgrade.php';
const B_APP = B_ROOT.'/releases/'.B_RELEASE.'/apps/web';
const B_ENV = B_ROOT.'/shared/production-business-runtime-192c8a3f7190.env';
const B_PREPARED_ENV = B_ROOT.'/shared/production-runtime-192c8a3f7190.env';
const B_MERCHANT = B_ROOT.'/shared/oneqaydev-first-merchant-credential.json';
const B_REQUEST = __DIR__.'/authority-request.json';
const B_REPORT = __DIR__.'/activation-result.json';
const B_BACKUP = __DIR__.'/rollback';
const B_EXPIRES = '2026-10-14T23:59:59+07:00';

final class BusinessActivationStop extends RuntimeException {}
function bAssert(bool $yes, string $code): void {
    if (!$yes) throw new BusinessActivationStop($code);
}
function bRead(string $path, int $limit = 1048576): string {
    bAssert(is_file($path) && !is_link($path) && is_readable($path), 'REQUIRED_FILE_INVALID');
    $size=filesize($path); bAssert(is_int($size) && $size>0 && $size<=$limit, 'FILE_SIZE_INVALID');
    $raw=file_get_contents($path); bAssert(is_string($raw) && strlen($raw)===$size, 'FILE_READ_FAILED'); return $raw;
}
function bJson(string $path): array {
    $v=json_decode(bRead($path),true,32,JSON_THROW_ON_ERROR);
    bAssert(is_array($v) && !array_is_list($v),'JSON_OBJECT_REQUIRED'); return $v;
}
function bPrivate(string $path): void {
    bRead($path); bAssert(((int)fileperms($path)&0077)===0,'PRIVATE_FILE_PERMISSION_INVALID');
}
function bWrite(string $path, string $raw, int $mode=0600): void {
    bAssert(!is_link($path),'WRITE_SYMLINK_DENIED');
    $tmp=$path.'.stage-'.bin2hex(random_bytes(8));
    $mask=umask(0077);$f=@fopen($tmp,'x');umask($mask);
    bAssert($f!==false,'PRIVATE_STAGE_FAILED');
    try {
        bAssert(@chmod($tmp,$mode) && fwrite($f,$raw)===strlen($raw),'PRIVATE_WRITE_FAILED');
        fclose($f);$f=null;bAssert(@rename($tmp,$path),'ATOMIC_WRITE_FAILED');
    } finally { if(is_resource($f))fclose($f);if(is_file($tmp))@unlink($tmp); }
}
function bReport(array $v): void {
    $v['attribution']='Lab | zefry';
    bWrite(B_REPORT,json_encode($v,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
}
function bLoad(): void {
    bAssert(PHP_SAPI==='cli' && PHP_VERSION_ID>=80200,'PHP_CLI_REQUIRED');
    foreach(['pdo_mysql','curl','mbstring','openssl'] as $ext) bAssert(extension_loaded($ext),'PHP_EXTENSION_REQUIRED');
    bAssert(realpath(B_ROOT)===B_ROOT && realpath(B_PUBLIC)===B_PUBLIC
        && realpath(B_ROOT.'/releases')===B_ROOT.'/releases'
        && realpath(B_STAGING)!==realpath(B_PUBLIC),'TARGET_PATH_ISOLATION_INVALID');
    bAssert(realpath(__DIR__)===B_ROOT.'/oneqaydev-business-activate-192c8a3f7190'
        && !is_link(__DIR__),'OPERATOR_DIRECTORY_INVALID');
    bAssert(hash_equals(B_HELPER_SHA,hash('sha256',bRead(B_HELPER))),'PREPARED_HELPER_CHANGED');
    require_once B_HELPER;
}
function bRequest(): array {
    $r=bJson(B_REQUEST);
    $expected=[
        'schema_version'=>1,'request_state'=>'PENDING_SEPARATE_BUSINESS_AUTHORIZATION',
        'domain'=>'oneqaydev.n07.my.id','private_root'=>B_ROOT,'public_root'=>B_PUBLIC,
        'release_id'=>B_RELEASE,'source_commit'=>B_SOURCE,'archive_sha256'=>B_ARCHIVE,
        'expected_health_bridge_sha256'=>B_BRIDGE,'merchant_receipt_sha256'=>B_MERCHANT_SHA,
        'rollback_snapshot_directory'=>B_BACKUP,'rollback_target_state'=>'HEALTH_READY_BUSINESS_OFF',
        'expected_upgrade_rollback_bridge_sha256'=>'ff7b3888b72c163641baa95d41b2431dcdaee52a754ca0e17d6db2095e5df125',
        'operator_sha256'=>(string)hash_file('sha256',__FILE__),
        'transactions'=>'CASH_AUTHORIZED_MERCHANT_TEST_DATA_ONLY',
        'migrations_allowed'=>false,'merchant_reprovision_allowed'=>false,
        'staging_allowed'=>false,'updater_allowed'=>false,'external_payments_allowed'=>false,
        'authority_window_seconds'=>900,'attribution'=>'Lab | zefry',
    ];
    bAssert(count($r)===count($expected),'REQUEST_ENVELOPE_INVALID');
    foreach($expected as $k=>$v) bAssert(($r[$k]??null)===$v,'REQUEST_BINDING_INVALID');
    return $r;
}
function bApprovalBody(string $requestSha): string {
    return "ONEQAYDEV PRODUCTION BUSINESS ACTIVATION AUTHORIZATION\n"
        ."REQUEST_SHA256: ".$requestSha."\n"
        ."TARGET: https://oneqaydev.n07.my.id\n"
        ."RELEASE: ".B_RELEASE."\nSOURCE_COMMIT: ".B_SOURCE."\nARTIFACT_SHA256: ".B_ARCHIVE."\n"
        ."BUSINESS_TRAFFIC: GRANTED\nTRANSACTIONS: CASH_AUTHORIZED_MERCHANT_TEST_DATA_ONLY\n"
        ."ROLLBACK: GRANTED\nMIGRATIONS: NOT_ALLOWED\nMERCHANT_REPROVISION: NOT_ALLOWED\n"
        ."STAGING: NOT_ALLOWED\nUPDATER: NOT_ALLOWED\nEXTERNAL_PAYMENTS: NOT_ALLOWED\n"
        ."WINDOW_SECONDS: 900\nAuthor by Lab | zefry";
}
/** Only public GitHub's authenticated owner comment can supply approval. */
function bQualifyApproval(array $c, int $id, string $requestSha, int $now): array {
    bAssert(($c['id']??null)===$id && ($c['user']['login']??null)==='labzefry'
        && ($c['user']['type']??null)==='User' && ($c['author_association']??null)==='OWNER',
        'INDEPENDENT_OWNER_AUTHORITY_INVALID');
    bAssert(($c['issue_url']??null)==='https://api.github.com/repos/labzefry/oneQay/issues/942',
        'AUTHORITY_REPOSITORY_INVALID');
    $body=$c['body']??null; $wanted=bApprovalBody($requestSha);
    bAssert(is_string($body) && ($body===$wanted || str_starts_with($body,$wanted."\n\n")),
        'EXACT_BUSINESS_GRANT_MISSING');
    $created=$c['created_at']??''; $updated=$c['updated_at']??'';
    bAssert(is_string($created) && preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z\z/D',$created)===1
        && $updated===$created,'AUTHORITY_TIMESTAMP_INVALID');
    $start=strtotime($created);
    bAssert(is_int($start) && $now>=$start && $now<=$start+900,'AUTHORITY_WINDOW_EXPIRED');
    return ['approval_comment_id'=>$id,'request_sha256'=>$requestSha,'authorized_at_unix'=>$start,'expires_at_unix'=>$start+900];
}
function bHttps(string $url, string $accept): array {
    bAssert(str_starts_with($url,'https://oneqaydev.n07.my.id/')
        || preg_match('~\Ahttps://api\.github\.com/repos/labzefry/oneQay/issues/comments/[1-9][0-9]*\z~D',$url)===1,
        'HTTPS_TARGET_NOT_ALLOWED');
    $h=curl_init($url); bAssert($h!==false,'HTTPS_INITIALIZE_FAILED'); $headers='';
    curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,
        CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>25,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
        CURLOPT_HTTPHEADER=>['Accept: '.$accept,'Cache-Control: no-cache','User-Agent: oneQayDev-governed-activation'],
        CURLOPT_HEADERFUNCTION=>static function($ch,string $line)use(&$headers):int{$headers.=$line;return strlen($line);},
    ]);
    $body=curl_exec($h);$status=(int)curl_getinfo($h,CURLINFO_HTTP_CODE);curl_close($h);
    bAssert(is_string($body) && strlen($body)<=2000000 && strlen($headers)<65536,'HTTPS_RESPONSE_INVALID');
    return ['status'=>$status,'body'=>$body,'headers'=>$headers];
}
function bAuthority(string $path, ?int $qualificationTime=null): array {
    bRequest();$sha=hash('sha256',bRead(B_REQUEST));
    if(preg_match('/\A[1-9][0-9]{0,17}\z/D',$path)===1) {
        // Public comment ID is a locator, never an authority token. The native
        // owner grant must still bind this complete exact request over HTTPS.
        $locator=['approval_comment_id'=>(int)$path];
    } else {
        bPrivate($path);$locator=bJson($path);
        bAssert(count($locator)===3 && ($locator['schema_version']??null)===1
            && is_int($locator['approval_comment_id']??null) && $locator['approval_comment_id']>0,'AUTHORITY_LOCATOR_INVALID');
        bAssert(($locator['request_sha256']??null)===$sha,'AUTHORITY_REQUEST_MISMATCH');
    }
    $response=bHttps('https://api.github.com/repos/labzefry/oneQay/issues/comments/'.$locator['approval_comment_id'],'application/vnd.github+json');
    bAssert($response['status']===200,'OWNER_AUTHORITY_LOOKUP_FAILED');
    $c=json_decode($response['body'],true,32,JSON_THROW_ON_ERROR);
    bAssert(is_array($c),'OWNER_AUTHORITY_RESPONSE_INVALID');
    return bQualifyApproval($c,$locator['approval_comment_id'],$sha,$qualificationTime??time());
}
function bMerchant(): array {
    bPrivate(B_MERCHANT);bAssert(hash_equals(B_MERCHANT_SHA,(string)hash_file('sha256',B_MERCHANT)),'MERCHANT_RECEIPT_CHANGED');
    $m=bJson(B_MERCHANT);
    bAssert(($m['state']??null)==='APPLIED' && ($m['domain']??null)==='oneqaydev.n07.my.id'
        && ($m['source_commit']??null)==='409ac6b2bdb80d31fbfb9425ddbb413a278b97e6'
        && is_string($m['password']??null) && strlen($m['password'])>=32
        && is_array($m['merchant']??null),'EXISTING_MERCHANT_INVALID');
    foreach(['tenant_id','identity_id','organization_id','outlet_id','device_id','provisioning_id'] as $k)
        bAssert(is_string($m['merchant'][$k]??null) && preg_match('/\A[a-z0-9][a-z0-9-]{8,95}\z/D',$m['merchant'][$k])===1,'MERCHANT_CONTEXT_INVALID');
    return $m;
}
function bIntegrity(): void {
    $list=oqData(dirname(B_HELPER).'/release-files.sha256',1800000);
    bAssert(hash_equals(OQ_FILE_MANIFEST_SHA,hash('sha256',$list)),'FILE_MANIFEST_CHANGED');
    $seen=[];
    foreach(explode("\n",trim($list)) as $line) {
        bAssert(preg_match('~\A([0-9a-f]{64})  (production-192c8a3f7190/[A-Za-z0-9._+@/-]+)\z~D',$line,$m)===1
            && !str_contains('/'.$m[2].'/','/../') && !isset($seen[$m[2]]),'SOURCE_MANIFEST_INVALID');
        $path=B_ROOT.'/releases/'.$m[2];
        bAssert(is_file($path) && !is_link($path) && hash_equals($m[1],(string)hash_file('sha256',$path)),'CERTIFIED_SOURCE_CHANGED');
        $seen[$m[2]]=true;
    }
    bAssert(count($seen)===6252,'SOURCE_FILE_COUNT_INVALID');
    $prefix=B_ROOT.'/releases/';
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(B_ROOT.'/releases/'.B_RELEASE,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST) as $e) {
        $rel=substr($e->getPathname(),strlen($prefix));
        if($rel===B_RELEASE.'/apps/web/.env')continue;
        bAssert(!$e->isLink(),'UNEXPECTED_SOURCE_SYMLINK');
        if($e->isDir())continue;
        if(isset($seen[$rel]))continue;
        $runtime=B_RELEASE.'/apps/web/storage/';
        bAssert($e->isFile() && (str_starts_with($rel,$runtime.'framework/views/')
            || str_starts_with($rel,$runtime.'framework/cache/data/')
            || str_starts_with($rel,$runtime.'logs/')
            || in_array($rel,[B_RELEASE.'/apps/web/bootstrap/cache/packages.php',B_RELEASE.'/apps/web/bootstrap/cache/services.php'],true)),
            'UNEXPECTED_SOURCE_FILE');
    }
    foreach(['config.php','routes-v7.php','routes.php'] as $cache)
        bAssert(!file_exists(B_APP.'/bootstrap/cache/'.$cache) && !is_link(B_APP.'/bootstrap/cache/'.$cache),'CACHED_RUNTIME_NOT_ALLOWED');
}
function bPreconditions(): array {
    bAssert(time()<=(int)strtotime(B_EXPIRES),'OPERATOR_WINDOW_EXPIRED');
    bRequest(); bIntegrity(); $m=bMerchant();bPrivate(B_PREPARED_ENV);
    $report=bJson(dirname(B_HELPER).'/upgrade-result.json');
    foreach(['state'=>'HEALTH_READY_BUSINESS_OFF','release_id'=>B_RELEASE,'source_commit'=>B_SOURCE,
        'domain'=>'oneqaydev.n07.my.id','source_integrity_files'=>6252,'merchant_preserved'=>true,
        'session_configuration_verified'=>true,'session_storage_probe'=>'FILE_WRITE_READ_DELETE_PASS',
        'required_runtime_features_prepared'=>16,'rollback_snapshot_verified'=>true,
        'bridge_sha256'=>B_BRIDGE,'merchant_sha256'=>B_MERCHANT_SHA,'staging'=>'NOT_TOUCHED','migrations'=>'NOT_TOUCHED'] as $k=>$v)
        bAssert(($report[$k]??null)===$v,'PREPARED_HOST_REPORT_MISMATCH');
    bAssert(hash_equals(B_BRIDGE,(string)hash_file('sha256',B_PUBLIC.'/index.php'))
        && !is_link(B_PUBLIC.'/index.php'),'HEALTH_ONLY_BRIDGE_CHANGED');
    bAssert(hash_equals(oqHashTree(B_APP.'/public/build'),oqHashTree(B_PUBLIC.'/build')),'PUBLIC_ASSET_TREE_CHANGED');
    $raw=bRead(B_PREPARED_ENV);$cfg=oqReadEnv($raw);
    bAssert((is_file(B_APP.'/.env')||is_link(B_APP.'/.env')) && hash_equals(hash('sha256',$raw),(string)hash_file('sha256',B_APP.'/.env')),'PREPARED_ENV_BINDING_CHANGED');
    foreach(oqPreparedFields() as $k=>$v)bAssert(($cfg[$k]??null)===$v,'PREPARED_FEATURE_CONFIG_CHANGED');
    foreach(['APP_ENV'=>'production','APP_URL'=>'https://oneqaydev.n07.my.id','ONEQAY_RUNTIME_CLASS'=>'production'] as $k=>$v)
        bAssert(($cfg[$k]??null)===$v,'PRODUCTION_CONFIG_INVALID');
    bAssert(in_array(strtolower($cfg['APP_DEBUG']??''),['false','0'],true)
        && in_array(strtolower($cfg['ONEQAY_PERSISTENCE_ENABLED']??''),['true','1'],true),'PRODUCTION_POLICY_INVALID');
    foreach(['ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED','ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED','ONEQAY_PREVIEW_DB_QUALIFICATION_ENABLED','ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_ENABLED'] as $k)
        bAssert(!filter_var($cfg[$k]??false,FILTER_VALIDATE_BOOL),'FORBIDDEN_RUNTIME_FEATURE_ENABLED');
    bAssert(preg_match('/\A[a-z0-9][a-z0-9-]{2,62}\z/D',$cfg['ONEQAY_PRODUCTION_ENVIRONMENT_ID']??'')===1,'ENVIRONMENT_ID_INVALID');
    bAssert(str_starts_with($cfg['APP_KEY']??'','base64:') && strlen((string)base64_decode(substr($cfg['APP_KEY'],7),true))===32,'APP_KEY_INVALID');
    bAssert(!file_exists(B_ENV) && !is_link(B_ENV) && !file_exists(B_BACKUP) && !is_link(B_BACKUP),'BUSINESS_ACTIVATION_ALREADY_STAGED');
    bAssert(hash_equals(OQ_OLD_BRIDGE_SHA,(string)hash_file('sha256',dirname(B_HELPER).'/rollback/index.php')),'UPGRADE_ROLLBACK_CHANGED');
    oqVerifyHttps('/health/live','ok');oqVerifyHttps('/health/ready','ready');
    return ['merchant'=>$m,'raw_env'=>$raw,'env'=>$cfg,'asset_sha256'=>oqHashTree(B_PUBLIC.'/build')];
}
function bDatabase(array $cfg,array $m): array {
    try {
        $db=$cfg['ONEQAY_DB_DATABASE']??'';$host=$cfg['ONEQAY_DB_HOST']??'';$port=$cfg['ONEQAY_DB_PORT']??'3306';$socket=$cfg['ONEQAY_DB_SOCKET']??'';
        bAssert(preg_match('/\A[a-zA-Z0-9_-]{1,64}\z/D',$db)===1 && $host!=='' && ctype_digit($port),'DATABASE_BINDING_INVALID');
        $dsn=$socket!==''?'mysql:unix_socket='.$socket.';dbname='.$db.';charset=utf8mb4':'mysql:host='.$host.';port='.$port.';dbname='.$db.';charset=utf8mb4';
        $pdo=new PDO($dsn,$cfg['ONEQAY_DB_USERNAME']??'',$cfg['ONEQAY_DB_PASSWORD']??'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_TIMEOUT=>5]);
        bAssert((int)$pdo->query('SELECT 1')->fetchColumn()===1,'DATABASE_SELECT_FAILED');
        $q=$pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=? AND table_type=?');
        $q->execute([$db,'BASE TABLE']);$tables=(int)$q->fetchColumn();
        bAssert($tables>=43 && (int)$pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn()>=27,'DATABASE_SCHEMA_BASELINE_INVALID');
        $g=$m['merchant'];$params=[$g['tenant_id'],$g['identity_id'],$g['organization_id'],$g['outlet_id'],$g['device_id']];
        $q=$pdo->prepare('SELECT COUNT(*) FROM oneqay_identities i JOIN oneqay_identity_organizations g ON g.tenant_id=i.tenant_id AND g.identity_id=i.id JOIN oneqay_outlets o ON o.tenant_id=g.tenant_id AND o.organization_id=g.organization_id JOIN oneqay_devices d ON d.tenant_id=o.tenant_id AND d.organization_id=o.organization_id AND d.outlet_id=o.id WHERE i.tenant_id=? AND i.id=? AND g.organization_id=? AND o.id=? AND d.id=? AND i.first_party_authentication_enabled=1');
        $q->execute($params);bAssert((int)$q->fetchColumn()===1,'MERCHANT_DATABASE_SCOPE_INVALID');
        $q=$pdo->prepare('SELECT password_hash FROM oneqay_identity_password_credentials WHERE tenant_id=? AND identity_id=?');
        $q->execute([$g['tenant_id'],$g['identity_id']]);$rows=$q->fetchAll(PDO::FETCH_COLUMN);
        bAssert(count($rows)===1 && is_string($rows[0]) && password_verify($m['password'],$rows[0]),'MERCHANT_CREDENTIAL_DATABASE_MISMATCH');
        return ['database_connection'=>'READ_ONLY_SELECT_PASS','table_count'=>$tables,'migration_receipts_at_least_27'=>true,'merchant_database_scope_verified'=>true,'merchant_password_hash_matches'=>true];
    } catch(Throwable $e) { if($e instanceof BusinessActivationStop)throw $e;throw new BusinessActivationStop('DATABASE_READ_ONLY_VALIDATION_FAILED'); }
}
function bCandidateEnv(string $raw,array $grant): string {
    $before=oqReadEnv($raw);$change=['ONEQAY_PRODUCTION_TRANSACTION_ACTIVATION_ENABLED'=>'true','ONEQAY_PRODUCTION_BUSINESS_TRAFFIC_AUTHORIZED'=>'true'];
    foreach(['tenant_id','identity_id','organization_id','outlet_id','device_id'] as $k) {
        bAssert(is_string($grant[$k]??null) && preg_match('/\A[a-z0-9][a-z0-9-]{8,95}\z/D',$grant[$k])===1,'MERCHANT_ENTRY_BINDING_INVALID');
        $change['ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_'.strtoupper($k)]=$grant[$k];
    }
    foreach($change as $k=>$v) {
        $pattern='/^'.preg_quote($k,'/').'=.*$/m';
        if(preg_match($pattern,$raw))$raw=(string)preg_replace($pattern,$k.'='.$v,$raw);
        else $raw=rtrim($raw,"\r\n")."\n".$k.'='.$v."\n";
    }
    $after=oqReadEnv($raw);
    foreach($before as $k=>$v)if(!array_key_exists($k,$change))bAssert(($after[$k]??null)===$v,'PRIVATE_BINDING_NOT_PRESERVED');
    return $raw;
}
function bBind(string $env): void {
    $p=B_APP.'/.env.business-stage-'.bin2hex(random_bytes(8));
    try {
        bAssert(@link($env,$p)||@symlink($env,$p),'PRIVATE_ENV_LINK_FAILED');
        bAssert(@rename($p,B_APP.'/.env'),'PRIVATE_ENV_SWITCH_FAILED');
        bAssert(hash_equals((string)hash_file('sha256',$env),(string)hash_file('sha256',B_APP.'/.env')),'PRIVATE_ENV_READBACK_FAILED');
    } finally { if(is_file($p)||is_link($p))@unlink($p); }
}
function bRuntime(array $grant): void {
    require_once B_APP.'/vendor/autoload.php';
    $app=require B_APP.'/bootstrap/app.php';$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    bAssert(config('app.env')==='production' && config('app.debug')===false && config('oneqay.runtime_class')==='production'
        && config('database.oneqay_persistence_enabled')===true,'LARAVEL_RUNTIME_CONFIG_INVALID');
    bAssert(\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows('production'),'NATIVE_PRODUCTION_BUSINESS_GATE_DENIED');
    bAssert(config('session.driver')==='file' && config('session.files')===OQ_SESSIONS
        && config('session.secure')===true && config('session.http_only')===true && config('session.same_site')==='lax'
        && config('session.domain')===null && config('session.cookie')==='oneqaydev-production-session','SECURE_SESSION_POLICY_INVALID');
    foreach($grant as $k=>$v)if($k!=='provisioning_id')bAssert(config('merchant_context_bootstrap.grant.'.$k)===$v,'MERCHANT_ENTRY_CONTEXT_READBACK_FAILED');
    bAssert(config('merchant_context_bootstrap.enabled')===false && config('oneqay.privileged_totp_mfa.enabled')===true
        && config('oneqay.privileged_step_up.enabled')===true && config('oneqay.session_control.enabled')===true
        && config('oneqay.session_control.idle_ttl_seconds')===7200 && config('oneqay.session_control.absolute_ttl_seconds')===43200,'AUTHENTICATION_POLICY_INVALID');
    foreach(['auth.first-party.login','auth.privileged-totp.enrollment.start','auth.privileged-totp.challenge',
        'pos.operations.hub','pos.sales.complete','pos.shifts.close'] as $route)bAssert(\Illuminate\Support\Facades\Route::has($route),'REQUIRED_NATIVE_BUSINESS_ROUTE_MISSING');
    $handler=$app->make('session')->driver()->getHandler();$id=bin2hex(random_bytes(20));$marker=bin2hex(random_bytes(24));
    try { bAssert($handler->write($id,$marker) && $handler->read($id)===$marker,'DURABLE_SESSION_PROBE_FAILED'); }
    finally { bAssert($handler->destroy($id),'SESSION_PROBE_CLEANUP_FAILED'); }
}
function bBridge(): string {
    return str_replace('__APP__',var_export(B_APP,true),<<<'BRIDGE'
<?php
// Author by Lab | zefry — exact-source oneQayDev native Production business bridge.
declare(strict_types=1);
define('LARAVEL_START',microtime(true));
$root=__APP__;
if(realpath($root)!==$root){http_response_code(503);exit;}
if(file_exists($maintenance=$root.'/storage/framework/maintenance.php'))require $maintenance;
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$kernel=$app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
if(!\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows('production')){
    http_response_code(503);header('Cache-Control: no-store');echo 'oneQayDev business gate denied';exit;
}
$request=\Illuminate\Http\Request::capture();
$response=$kernel->handle($request);$response->send();$kernel->terminate($request,$response);
BRIDGE);
}
function bStagingFingerprint(): string {
    $items=[];
    foreach(['index.php','index.html','.htaccess'] as $name) {
        $p=B_STAGING.'/'.$name;
        $items[$name]=is_link($p)?'LINK:'.hash('sha256',(string)readlink($p)):(is_file($p)?hash_file('sha256',$p):'ABSENT');
    }
    return hash('sha256',json_encode($items,JSON_THROW_ON_ERROR));
}
function bSnapshot(array $pre): array {
    bAssert(@mkdir(B_BACKUP,0700) && @chmod(B_BACKUP,0700),'ROLLBACK_DIRECTORY_FAILED');
    $hashes=[];
    foreach(['index.php','.htaccess','index.html'] as $name) {
        $raw=bRead(B_PUBLIC.'/'.$name,2000000);bWrite(B_BACKUP.'/'.$name,$raw);$hashes[$name]=hash('sha256',$raw);
    }
    bWrite(B_BACKUP.'/prepared.env',$pre['raw_env']);$hashes['prepared.env']=hash('sha256',$pre['raw_env']);
    $s=['files'=>$hashes,'public_asset_sha256'=>$pre['asset_sha256'],'merchant_sha256'=>B_MERCHANT_SHA,'staging_fingerprint'=>bStagingFingerprint()];
    bWrite(B_BACKUP.'/snapshot.json',json_encode($s,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
    foreach($hashes as $name=>$hash)bAssert(hash_equals($hash,(string)hash_file('sha256',B_BACKUP.'/'.$name)),'ROLLBACK_SNAPSHOT_READBACK_FAILED');
    return $s;
}
function bPublicVerify(): void {
    oqVerifyHttps('/health/live','ok');oqVerifyHttps('/health/ready','ready');
    $r=bHttps('https://oneqaydev.n07.my.id/','text/html');
    bAssert($r['status']===200 && preg_match('/<meta\s+name="oneqay-merchant-entry"\s+content="enabled"/',$r['body'])===1
        && str_contains($r['body'],'oneqay-merchant-login-context'),'MERCHANT_ENTRY_HTTPS_NOT_READY');
    bAssert(preg_match('/^Set-Cookie:\s*oneqaydev-production-session=[^\r\n]*;\s*secure[^\r\n]*;\s*httponly[^\r\n]*;\s*samesite=lax/im',$r['headers'])===1,'SECURE_SESSION_HTTPS_COOKIE_INVALID');
    $r=bHttps('https://oneqaydev.n07.my.id/pos','application/json');
    bAssert(in_array($r['status'],[401,403],true),'UNAUTHENTICATED_POS_NOT_DENIED');
}
function bRestore(): array {
    bAssert(hash_equals(B_BRIDGE,(string)hash_file('sha256',B_BACKUP.'/index.php')),'KNOWN_HEALTH_BRIDGE_BACKUP_CHANGED');
    bWrite(B_PUBLIC.'/index.php',bRead(B_BACKUP.'/index.php'),0644);
    bPrivate(B_BACKUP.'/snapshot.json');$s=bJson(B_BACKUP.'/snapshot.json');
    foreach(['index.php','.htaccess','index.html','prepared.env'] as $name)
        bAssert(hash_equals((string)($s['files'][$name]??''),(string)hash_file('sha256',B_BACKUP.'/'.$name)),'ROLLBACK_BACKUP_CHANGED');
    bAssert(hash_equals((string)$s['files']['prepared.env'],(string)hash_file('sha256',B_PREPARED_ENV)),'PREPARED_ENV_FOR_ROLLBACK_CHANGED');
    // Public business is already closed; remaining restoration stays health-only.
    bBind(B_PREPARED_ENV);
    foreach(['.htaccess','index.html'] as $name)bWrite(B_PUBLIC.'/'.$name,bRead(B_BACKUP.'/'.$name,2000000),0644);
    foreach(['index.php','.htaccess','index.html'] as $name)bAssert(hash_equals($s['files'][$name],(string)hash_file('sha256',B_PUBLIC.'/'.$name)),'ROLLBACK_PUBLIC_READBACK_FAILED');
    oqBoolsOff(oqReadEnv(bRead(B_PREPARED_ENV)));
    bAssert(hash_equals($s['merchant_sha256'],(string)hash_file('sha256',B_MERCHANT))
        && hash_equals($s['staging_fingerprint'],bStagingFingerprint())
        && hash_equals($s['public_asset_sha256'],oqHashTree(B_PUBLIC.'/build')),'ROLLBACK_PROTECTED_BINDING_CHANGED');
    if(is_file(B_ENV) && !is_link(B_ENV))bAssert(@unlink(B_ENV),'CANDIDATE_ENV_CLEANUP_FAILED');
    $https='NOT_VERIFIED';
    try { oqVerifyHttps('/health/live','ok');oqVerifyHttps('/health/ready','ready');$https='PASS'; } catch(Throwable) {}
    return ['rollback'=>'FILES_AND_BUSINESS_OFF_VERIFIED','rollback_https'=>$https,'database_records'=>'PRESERVED_NOT_REVERTED'];
}
function bFailedRollback(Throwable $e): void {
    $code=$e instanceof BusinessActivationStop || $e instanceof OQStop?$e->getMessage():'INTERNAL_ERROR';
    try {
        $rollback=bRestore();
        bReport(array_merge(['state'=>'ACTIVATION_FAILED_ROLLED_BACK','error_code'=>$code,'business_traffic'=>'OFF',
            'release_id'=>B_RELEASE,'source_commit'=>B_SOURCE,'host_business_acceptance'=>'NOT_VERIFIED'], $rollback));
    } catch(Throwable) {
        try { bReport(['state'=>'ACTIVATION_FAILED_ROLLBACK_NOT_VERIFIED','error_code'=>$code,
            'business_traffic'=>'NOT_VERIFIED','source_commit'=>B_SOURCE,'release_id'=>B_RELEASE,
            'host_business_acceptance'=>'NOT_VERIFIED']); } catch(Throwable) {}
    }
}
function bActivate(string $authorityPath): array {
    $authority=bAuthority($authorityPath);$pre=bPreconditions();$db=bDatabase($pre['env'],$pre['merchant']);
    $authority=bAuthority($authorityPath);$snapshot=bSnapshot($pre);
    try {
        bAssert(!file_exists(B_ENV) && !is_link(B_ENV),'BUSINESS_ENV_ALREADY_EXISTS');
        bWrite(B_ENV,bCandidateEnv($pre['raw_env'],$pre['merchant']['merchant']));
        bBind(B_ENV);bRuntime($pre['merchant']['merchant']);
        bAssert(time()<=$authority['expires_at_unix'],'AUTHORITY_EXPIRED_BEFORE_SWITCH');
        bWrite(B_PUBLIC.'/.htaccess',"DirectoryIndex index.php\n".bRead(B_APP.'/public/.htaccess'),0644);
        bWrite(B_PUBLIC.'/index.php',bBridge(),0644);
        bPublicVerify();
        bAssert(hash_equals(hash('sha256',bBridge()),(string)hash_file('sha256',B_PUBLIC.'/index.php'))
            && hash_equals(B_MERCHANT_SHA,(string)hash_file('sha256',B_MERCHANT))
            && hash_equals($snapshot['staging_fingerprint'],bStagingFingerprint())
            && hash_equals($pre['asset_sha256'],oqHashTree(B_PUBLIC.'/build')),'POST_SWITCH_PROTECTED_BINDING_CHANGED');
        $result=array_merge(['state'=>'BUSINESS_ACTIVATED_ACCEPTANCE_PENDING','domain'=>'oneqaydev.n07.my.id',
            'release_id'=>B_RELEASE,'source_commit'=>B_SOURCE,'source_integrity_files'=>6252,
            'business_traffic'=>'ON_AUTHORIZED','production_runtime_gate'=>'NATIVE_PRODUCTION_PASS',
            'merchant_entry_https'=>'PASS','unauthenticated_pos'=>'DENIED','session_configuration_verified'=>true,
            'session_storage_probe'=>'FILE_WRITE_READ_DELETE_PASS','merchant_preserved'=>true,'staging'=>'NOT_TOUCHED',
            'migrations'=>'NOT_TOUCHED','updater'=>'INACTIVE','external_payments'=>'NOT_PERFORMED',
            'rollback_snapshot_verified'=>true,'host_business_acceptance'=>'NOT_VERIFIED','go_live'=>'NOT_YET_PROVEN',
            'bridge_sha256'=>(string)hash_file('sha256',B_PUBLIC.'/index.php'),
            'runtime_sha256'=>(string)hash_file('sha256',B_ENV)],$db,$authority);
        bReport($result);return $result;
    } catch(Throwable $e) {
        bFailedRollback($e);throw $e;
    }
}
function bReplay(string $authorityPath,array $r): array {
    try {
        $historical=bAuthority($authorityPath,$r['authorized_at_unix']??0);
        foreach($historical as $k=>$v)bAssert(($r[$k]??null)===$v,'REPLAY_AUTHORITY_RECEIPT_CHANGED');
        bAssert(hash_equals($r['bridge_sha256'],(string)hash_file('sha256',B_PUBLIC.'/index.php'))
            && hash_equals($r['runtime_sha256'],(string)hash_file('sha256',B_ENV))
            && hash_equals((string)hash_file('sha256',B_ENV),(string)hash_file('sha256',B_APP.'/.env'))
            && hash_equals(B_MERCHANT_SHA,(string)hash_file('sha256',B_MERCHANT)),'ACTIVE_REPLAY_BINDING_CHANGED');
        bAssert(hash_equals(bJson(B_BACKUP.'/snapshot.json')['staging_fingerprint'],bStagingFingerprint()),'REPLAY_STAGING_CHANGED');
        bIntegrity();bRuntime(bMerchant()['merchant']);bPublicVerify();$r['idempotent_replay']=true;bReport($r);return $r;
    } catch(Throwable $e) {
        bFailedRollback($e);throw $e;
    }
}
if(PHP_SAPI==='cli' && realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__) {
    if(!in_array($argv[1]??'', ['activate','rollback'],true)
        || (($argv[1]==='activate')?$argc!==3:$argc!==2)){fwrite(STDERR,"USAGE: php activate.php activate OWNER_COMMENT_ID_OR_PRIVATE_LOCATOR | rollback\n");exit(64);}
    try {
        bLoad();$lock=@fopen(__DIR__.'/activation.lock','c');
        bAssert($lock!==false && @chmod(__DIR__.'/activation.lock',0600) && flock($lock,LOCK_EX|LOCK_NB),'ACTIVATION_LOCK_UNAVAILABLE');
        if($argv[1]==='rollback') {
            $r=array_merge(['state'=>'BUSINESS_OFF_ROLLBACK_VERIFIED','business_traffic'=>'OFF','release_id'=>B_RELEASE,'source_commit'=>B_SOURCE],bRestore());bReport($r);
        } elseif(is_file(B_REPORT)) {
            $r=bJson(B_REPORT);
            bAssert(($r['state']??'')==='BUSINESS_ACTIVATED_ACCEPTANCE_PENDING','PREVIOUS_ACTIVATION_STOPPED_NO_AUTOMATIC_RETRY');
            $r=bReplay($argv[2],$r);
        } else {
            $r=bActivate($argv[2]);
        }
        fwrite(STDOUT,"ONEQAYDEV|".$r['state']."\n");exit(0);
    } catch(Throwable $e) {
        $code=$e instanceof BusinessActivationStop || $e instanceof OQStop?$e->getMessage():'INTERNAL_ERROR';
        if(!is_file(B_REPORT))try{bReport(['state'=>'STOPPED_FAIL_CLOSED','error_code'=>$code,'source_commit'=>B_SOURCE,'release_id'=>B_RELEASE,'host_business_acceptance'=>'NOT_VERIFIED']);}catch(Throwable){}
        fwrite(STDERR,"ONEQAYDEV|STOPPED|".$code."\n");exit(1);
    }
}

