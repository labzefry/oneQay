<?php
// Author by Lab | zefry. Real Laravel route matching; no production connections or credentials.
declare(strict_types=1);
require __DIR__.'/../vendor/autoload.php';
foreach(['APP_ENV'=>'testing','APP_KEY'=>'base64:'.base64_encode(str_repeat('r',32)),'ONEQAY_RUNTIME_CLASS'=>'ci','ONEQAY_PERSISTENCE_ENABLED'=>'true','ONEQAY_POS_SALE_COMPLETION_ENABLED'=>'true','ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED'=>'true','SESSION_DRIVER'=>'array','CACHE_STORE'=>'array'] as $key=>$value){putenv($key.'='.$value);$_ENV[$key]=$value;$_SERVER[$key]=$value;}
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app['config']->set('oneqay.runtime_class','ci');$app['config']->set('database.oneqay_persistence_enabled',true);$app['config']->set('oneqay.pos_sale_completion.enabled',true);$app['config']->set('oneqay.session_control.enabled',true);$app['config']->set('oneqay.session_control.idle_ttl_seconds',7200);$app['config']->set('oneqay.session_control.absolute_ttl_seconds',43200);
$router=$app['router'];$count=0;
$assert=static function(bool $ok,string $label)use(&$count):void{if(!$ok)throw new RuntimeException($label);++$count;};
$business=App\Providers\BusinessManagementServiceProvider::class;$desktop=App\Providers\DesktopPosServiceProvider::class;
foreach([[$business,$desktop],[$desktop,$business]] as $providers){
 $router->setRoutes(new Illuminate\Routing\RouteCollection());foreach($providers as $provider)(new $provider($app))->boot();
 $match=static fn(string $method,string $path)=>$router->getRoutes()->match(Illuminate\Http\Request::create($path,$method));
 foreach(['GET /pos/business/desktop'=>'settings','POST /pos/business/desktop'=>'saveSettings','POST /pos/business/desktop/cashiers'=>'cashier','POST /pos/business/desktop/finalize'=>'finalize'] as $request=>$action){
  [$method,$path]=explode(' ',$request);$route=$match($method,$path);$assert($route->getActionName()===App\Delivery\Http\Pos\DesktopEnterpriseController::class.'@'.$action,'dedicated desktop action '.$request);
  $assert(in_array('web',$route->middleware(),true)&&in_array('session.active',$route->middleware(),true)&&in_array(App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware::class,$route->middleware(),true),'desktop session and context middleware retained');
 }
 foreach(['suppliers','purchases','customers','finance','people','audit'] as $module)$assert($match('GET','/pos/business/'.$module)->getActionName()===App\Delivery\Http\Business\BusinessWorkspaceController::class.'@show','generic module preserved '.$module);
 foreach(['products'=>'catalog','settings'=>'settings','reports'=>'reports'] as $module=>$action)$assert($match('GET','/pos/business/'.$module)->getActionName()===App\Delivery\Http\Business\MerchandisingController::class.'@'.$action,'dedicated commerce page preserved '.$module);
 $assert($match('GET','/pos/business/access')->getActionName()===App\Delivery\Http\Business\BusinessAccessController::class.'@show','business roles page preserved');
 try{$match('GET','/pos/business/not-a-module');$assert(false,'unknown module matched');}catch(Symfony\Component\HttpKernel\Exception\NotFoundHttpException){$assert(true,'unknown module denied');}
}
echo 'DESKTOP_SETTINGS_ROUTING_PASS assertions='.$count.PHP_EOL;
