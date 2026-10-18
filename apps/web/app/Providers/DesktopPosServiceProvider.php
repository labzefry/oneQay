<?php
// Author by Lab | zefry

declare(strict_types=1);
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Infrastructure\Pos\LaravelDesktopPosService;
use App\Delivery\Http\Pos\DesktopPosController as Controller;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
final class DesktopPosServiceProvider extends ServiceProvider {
 public function register():void {$this->app->scoped(LaravelDesktopPosService::class,fn($a)=>new LaravelDesktopPosService($a->make('db')->connection(),$a->make('encrypter'),$a->make(\App\Application\Organization\OrganizationalContextStore::class),$a->make(\App\Application\Tenancy\TenantContextStore::class),$a->make(\App\Application\Organization\EnterOrganizationalContext::class),$a->make(\App\Application\Identity\FirstPartySessionAuthorityService::class),$a->make(\App\Application\Identity\FirstPartyIdentityEligibilityVerifier::class),$a->make(\App\Application\Authorization\DurableScopedAuthorizationPolicy::class),$a->make(\App\Application\Pos\CompleteSale::class)));}
 public function boot():void {
  $runtime=(string)config('oneqay.runtime_class');
  if(!config('database.oneqay_persistence_enabled')||!config('oneqay.pos_sale_completion.enabled')||(!in_array($runtime,['local','test','ci'],true)&&!\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows($runtime)))return;
  Route::middleware(['web','session.active',RequirePosSessionContextMiddleware::class,'throttle:20,1'])->group(function():void{
   Route::get('/pos/desktop',[Controller::class,'page'])->name('pos.desktop');
   Route::post('/pos/desktop/pair',[Controller::class,'pair'])->name('pos.desktop.pair');
   Route::get('/pos/desktop/review',[Controller::class,'review'])->name('pos.desktop.review');
  });
  // Native requests carry an opaque credential, not browser cookies; never add CSRF exclusions to web routes.
  Route::middleware('throttle:20,1')->post('/api/pos/desktop/exchange',[Controller::class,'exchange'])->name('pos.desktop.exchange');
  Route::middleware('throttle:120,1')->group(function():void{
   Route::get('/api/pos/desktop/snapshot',[Controller::class,'snapshot'])->name('pos.desktop.snapshot');
   Route::post('/api/pos/desktop/sync',[Controller::class,'sync'])->name('pos.desktop.sync');
  });
 }
}
