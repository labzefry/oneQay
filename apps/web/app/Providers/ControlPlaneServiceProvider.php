<?php
declare(strict_types=1);
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Application\Platform\{ControlPlaneStore,PlatformStore,PlatformOwnerAccess};
use App\Application\Notification\MailStore;
use App\Infrastructure\Platform\{LaravelControlPlaneStore,ControlPlaneFeatures,ControlPlaneTelemetry,ControlPlaneCampaigns,ControlPlanePilot};
use App\Infrastructure\Identity\LaravelPlatformIdentitySecurity;
use App\Application\Authorization\DurableRolePermissionRepository;
use App\Delivery\Http\Middleware\{RequirePosSessionContextMiddleware,RequirePlatformSecurityMiddleware,RequirePolicyAdministrationSessionContextMiddleware};
use App\Delivery\Http\Platform\{ControlPlaneController,MarketingPreferenceController};
// Author by Lab | zefry
final class ControlPlaneServiceProvider extends ServiceProvider {
 public function register():void{
  $this->app->scoped(ControlPlaneTelemetry::class,fn($a)=>new ControlPlaneTelemetry($a->make('db')->connection()));
  $this->app->scoped(ControlPlanePilot::class,fn($a)=>new ControlPlanePilot($a->make('db')->connection()));
  $this->app->scoped(ControlPlaneCampaigns::class,fn($a)=>new ControlPlaneCampaigns($a->make('db')->connection(),$a->make(MailStore::class),(string)config('platform.public_url'),(string)config('app.key')));
  $this->app->scoped(LaravelPlatformIdentitySecurity::class,fn($a)=>new LaravelPlatformIdentitySecurity($a->make('db')->connection(),$a->make('encrypter'),$a->make(PlatformOwnerAccess::class)));
  $this->app->scoped(ControlPlaneStore::class,fn($a)=>new LaravelControlPlaneStore($a->make('db')->connection(),$a->make(PlatformStore::class),$a->make(ControlPlaneTelemetry::class),$a->make(ControlPlaneCampaigns::class),$a->make(LaravelPlatformIdentitySecurity::class),$a->make(ControlPlanePilot::class),$a->make(MailStore::class)));
  $this->app->extend(DurableRolePermissionRepository::class,fn($inner,$a)=>config('platform.enabled')?new ControlPlaneFeatures($inner,$a->make('db')->connection()):$inner);
 }
 public function boot():void{
  $this->commands([\App\Console\Commands\ControlPlaneWorkCommand::class]);
  if(!config('platform.enabled')||!config('database.oneqay_persistence_enabled')||!config('oneqay.session_control.enabled')||!\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows((string)config('oneqay.runtime_class')))return;
  Route::middleware(['web','session.active',RequirePosSessionContextMiddleware::class,RequirePlatformSecurityMiddleware::class,'throttle:60,1'])->group(function():void{
   Route::get('/platform/control/overview',[ControlPlaneController::class,'overview'])->name('platform.control.overview');
   Route::get('/platform/control/businesses/{id}',[ControlPlaneController::class,'business'])->where('id','[a-f0-9]{32}')->name('platform.control.business');
   Route::get('/platform/control/businesses/{id}/reset-preview',[ControlPlaneController::class,'resetPreview'])->where('id','[a-f0-9]{32}')->name('platform.control.reset-preview');
   Route::get('/platform/control/campaigns/{id}/preview',[ControlPlaneController::class,'preview'])->where('id','[a-f0-9]{32}')->name('platform.control.campaign-preview');
   Route::post('/platform/control/commands',[ControlPlaneController::class,'command'])->middleware(RequirePolicyAdministrationSessionContextMiddleware::class)->name('platform.control.command');
  });
  Route::middleware(['web','throttle:10,1'])->group(function():void{Route::get('/marketing/preferences',[MarketingPreferenceController::class,'publicPage']);Route::post('/marketing/unsubscribe',[MarketingPreferenceController::class,'unsubscribe']);});
  Route::post('/pos/marketing/preferences',[MarketingPreferenceController::class,'save'])->middleware(['web','session.active',RequirePosSessionContextMiddleware::class,RequirePolicyAdministrationSessionContextMiddleware::class,'throttle:10,1']);
 }
}
