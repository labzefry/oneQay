<?php
declare(strict_types=1);

// Author by Lab | zefry. Real HTTP-kernel regression, isolated from any host DB.
// Release/authority values below are local fixtures, never deployment evidence.
$source=dirname(__DIR__);
if(($argv[1]??'')==='worker') {
    require $source.'/vendor/autoload.php';
    $_SERVER['HTTPS']='on';$_SERVER['SERVER_PORT']='443';
    $app=require $argv[2].'/bootstrap/app.php';
    $kernel=$app->make(\Illuminate\Contracts\Http\Kernel::class);
    $request=\Illuminate\Http\Request::create('https://oneqaydev.n07.my.id'.$argv[4],$argv[3],[],[],[],['HTTP_ACCEPT'=>'application/json']);
    $response=$kernel->handle($request);
    $cookies=array_map(static fn($cookie)=>(string)$cookie,$response->headers->getCookies());
    $router=$app->make('router');$coverage=[];
    foreach($router->getRoutes() as $route) {
        if(!in_array('session.active',$route->gatherMiddleware(),true))continue;
        $resolved=$router->gatherRouteMiddleware($route);
        $start=array_search(\Illuminate\Session\Middleware\StartSession::class,$resolved,true);
        $active=array_search(\App\Delivery\Http\Middleware\EnforceActiveFirstPartySessionAuthorityMiddleware::class,$resolved,true);
        $csrf=array_search(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,$resolved,true);
        $coverage[$route->getName()??$route->uri()]=is_int($start)&&is_int($active)&&is_int($csrf)
            && $start<$active && $csrf<$active
            && count(array_keys($resolved,\Illuminate\Session\Middleware\StartSession::class,true))===1;
    }
    $kernel->terminate($request,$response);
    echo json_encode(['status'=>$response->getStatusCode(),'body'=>$response->getContent(),
        'cookies'=>$cookies,'coverage'=>$coverage,'native_gate'=>\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows('production'),
        'runtime'=>config('oneqay.runtime_class'),'staging'=>config('oneqay.durable_staging_runtime.enabled',false)],JSON_THROW_ON_ERROR);
    exit;
}
$root=sys_get_temp_dir().'/oneqay-native-session-'.bin2hex(random_bytes(8));
$app=$root.'/apps/web';
function cleanFixture(string $path):void {
    if(is_link($path)||is_file($path)){unlink($path);return;}
    if(is_dir($path)){foreach(new FilesystemIterator($path,FilesystemIterator::SKIP_DOTS)as $p)cleanFixture($p->getPathname());rmdir($path);}
}
function assertNative(bool $ok,string $message):void {
    if(!$ok)throw new RuntimeException($message);
}
try {
    foreach(['bootstrap/cache','storage/framework/cache/data','storage/framework/sessions','storage/framework/views','storage/logs']as $dir)
        mkdir($app.'/'.$dir,0700,true);
    foreach(['config','routes','resources','vendor']as $dir)symlink($source.'/'.$dir,$app.'/'.$dir);
    foreach(['app.php','providers.php']as $file)copy($source.'/bootstrap/'.$file,$app.'/bootstrap/'.$file);
    $sourceSha=str_repeat('a',40);$archiveSha=str_repeat('b',64);
    file_put_contents($root.'/RELEASE.json',json_encode(['environment'=>'PRODUCTION','required_runtime_class'=>'production',
        'production'=>true,'production_data_allowed'=>true,'business_runtime_activation_ready'=>true,
        'source_commit'=>$sourceSha,'release_id'=>'production-'.substr($sourceSha,0,12)],JSON_THROW_ON_ERROR));
    $env=[
        'APP_ENV'=>'production','APP_DEBUG'=>'false','APP_URL'=>'https://oneqaydev.n07.my.id',
        'APP_KEY'=>'base64:cXFxcXFxcXFxcXFxcXFxcXFxcXFxcXFxcXFxcXFxcXE=',
        'ONEQAY_RUNTIME_CLASS'=>'production','ONEQAY_PERSISTENCE_ENABLED'=>'true',
        'ONEQAY_PRODUCTION_ENVIRONMENT_ID'=>'oneqaydev-native-session-fixture',
        'ONEQAY_RUNNING_SOURCE_COMMIT'=>$sourceSha,'ONEQAY_RUNNING_ARTIFACT_SHA256'=>$archiveSha,
        'ONEQAY_PRODUCTION_TRANSACTION_ACTIVATION_ENABLED'=>'true','ONEQAY_PRODUCTION_BUSINESS_TRAFFIC_AUTHORIZED'=>'true',
        'SESSION_DRIVER'=>'file','SESSION_FILES'=>$app.'/storage/framework/sessions',
        'SESSION_COOKIE'=>'oneqaydev-production-session','SESSION_SECURE_COOKIE'=>'false','SESSION_HTTP_ONLY'=>'true','SESSION_SAME_SITE'=>'lax',
        'CACHE_STORE'=>'array','LOG_CHANNEL'=>'stderr','ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED'=>'false',
        'ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_ENABLED'=>'false',
    ];
    foreach(['ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED','ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED','ONEQAY_PRIVILEGED_STEP_UP_ENABLED',
        'ONEQAY_POS_SALE_COMPLETION_ENABLED','ONEQAY_POS_CATALOG_PREPARATION_ENABLED','ONEQAY_POS_INVENTORY_BASELINE_ENABLED',
        'ONEQAY_POS_SHIFT_OPENING_ENABLED','ONEQAY_POS_SHIFT_OPENING_CASH_EVIDENCE_ENABLED','ONEQAY_POS_SHIFT_CLOSING_CASH_EVIDENCE_ENABLED',
        'ONEQAY_POS_SHIFT_CLOSE_ENABLED','ONEQAY_POS_OPERATIONS_HUB_ENABLED','ONEQAY_POS_CASHIER_WORKSPACE_ENABLED',
        'ONEQAY_POS_SHIFT_START_WORKSPACE_ENABLED','ONEQAY_POS_CATALOG_INVENTORY_SETUP_ENABLED','ONEQAY_POS_OPERATIONAL_REPORTING_ENABLED','ONEQAY_POS_SALE_HISTORY_WORKSPACE_ENABLED']as $key)$env[$key]='true';
    foreach(['TENANT_ID'=>'merchant-fixture','IDENTITY_ID'=>'owner-fixture','ORGANIZATION_ID'=>'org-fixture','OUTLET_ID'=>'outlet-fixture','DEVICE_ID'=>'device-fixture']as $key=>$value)
        $env['ONEQAY_MERCHANT_CONTEXT_BOOTSTRAP_'.$key]=$value;
    $raw='';foreach($env as $k=>$v)$raw.=$k.'='.$v."\n";
    file_put_contents($app.'/.env',$raw);chmod($app.'/.env',0600);
    // Remove inherited CI settings: this fixture must exercise native Production.
    $childEnv=getenv();foreach($env as $k=>$v)$childEnv[$k]=$v;
    $phpArgs=[PHP_BINARY];
    // Propagate loaded extensions when the parent uses an isolated local PHP -n.
    if(php_ini_loaded_file()===false) {
        $phpArgs[]='-n';
        foreach(['ctype','tokenizer','fileinfo','pdo','mysqlnd','pdo_mysql','phar','mbstring','curl']as $extension) {
            if(extension_loaded($extension)&&is_file(ini_get('extension_dir').'/'.$extension.'.so')) {
                $phpArgs[]='-d';$phpArgs[]='extension='.ini_get('extension_dir').'/'.$extension.'.so';
            }
        }
    }
    $checks=0;
    foreach([['GET','/pos',401],['GET','/pos/catalog-inventory/setup',401],['GET','/pos/shift-start',401],
        ['GET','/pos/cashier',401],['GET','/pos/shifts/close',401],['GET','/auth/sessions',401],
        ['POST','/pos/sales',419],['POST','/auth/login',419]]as [$method,$path,$expected]) {
        $pipes=[];$p=proc_open([...$phpArgs,__FILE__,'worker',$app,$method,$path],[1=>['pipe','w'],2=>['pipe','w']],$pipes,null,$childEnv);
        assertNative(is_resource($p),'Worker could not start');
        $stdout=stream_get_contents($pipes[1]);$stderr=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
        assertNative(proc_close($p)===0,'HTTP worker failed');
        $result=json_decode($stdout,true,32,JSON_THROW_ON_ERROR);
        assertNative($result['native_gate']===true&&$result['runtime']==='production'&&$result['staging']===false,'Native Production fixture did not qualify: '.json_encode($result));
        assertNative($result['status']===$expected,$method.' '.$path.' expected '.$expected.' received '.$result['status']);
        assertNative(count($result['coverage'])>=6&&!in_array(false,$result['coverage'],true),'An authenticated route lacks web/session/CSRF ordering or duplicates StartSession');
        if($method==='GET') {
            assertNative(str_contains($result['body'],'SESSION_AUTHORITY_DENIED'),'Unauthenticated request did not use the guarded denial');
            $secure=false;foreach($result['cookies']as $cookie)if(preg_match('/^oneqaydev-production-session=.*; secure; httponly; samesite=lax$/i',$cookie))$secure=true;
            assertNative($secure,'Secure HttpOnly SameSite=Lax cookie was not emitted');
        }
        ++$checks;
    }
    echo 'PASS: '.$checks." native Production HTTP requests; unauthenticated POS denied, session cookies secured, CSRF enforced, all authenticated routes resolve web before authority once. Local fixtures only; no host or database acceptance.\n";
} finally {cleanFixture($root);}
