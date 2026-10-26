<?php
// Author by Lab | zefry.
declare(strict_types=1);
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Infrastructure\BusinessExperience\LaravelBusinessDomains;
use App\Delivery\Http\BusinessExperience\BusinessExperienceController as C;
final class BusinessExperienceServiceProvider extends ServiceProvider {
 public function register():void {
  $this->app->scoped(\App\Infrastructure\BusinessExperience\LaravelServicePos::class,fn($a)=>new \App\Infrastructure\BusinessExperience\LaravelServicePos($a->make('db')->connection(),$a->make(\App\Application\Business\BusinessAccess::class),$a->make(\App\Application\Platform\PlatformStore::class),$a->make(LaravelBusinessDomains::class)));
  $this->app->scoped(LaravelBusinessDomains::class,fn($a)=>new LaravelBusinessDomains($a->make('db')->connection(),$a->make(\App\Application\Platform\PlatformStore::class),$a->make(\App\Application\Notification\MailStore::class),$a->make(\App\Application\Platform\PlatformOwnerAccess::class)));
 }
 public function boot():void {
  if(!config('platform.enabled')||!config('database.oneqay_persistence_enabled')||!\App\Infrastructure\Runtime\ProductionBusinessRuntimeGate::allows((string)config('oneqay.runtime_class')))return;
  Route::middleware(['web','throttle:60,1'])->group(function(){Route::get('/register/subdomain',[C::class,'availability']);Route::get('/business-entry',[C::class,'entry']);Route::post('/business-entry',[C::class,'consume'])->middleware('throttle:10,1');});
  $service=\App\Delivery\Http\BusinessExperience\ServicePosController::class;
  Route::middleware(['web','session.active',\App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware::class,'throttle:60,1'])->group(function()use($service){Route::get('/pos/business-handoff',[C::class,'issue']);Route::get('/pos/service',[$service,'page']);Route::get('/pos/service/data',[$service,'read']);Route::post('/pos/service/commands',[$service,'command']);Route::post('/pos/service/reconcile',[$service,'reconcile']);Route::get('/pos/business/security',fn()=>\Inertia\Inertia::render('Business/OwnerSecurity',['security'=>(new \App\Infrastructure\Identity\LaravelBusinessOwnerSecurity())->status()]));Route::post('/pos/business/security',function(\Illuminate\Http\Request $r){try{return response()->json((new \App\Infrastructure\Identity\LaravelBusinessOwnerSecurity())->command($r));}catch(\App\Application\Platform\PlatformViolation $e){return response()->json(['code'=>$e->reason],$e->status);}catch(\App\Application\Identity\PrivilegedTotpMfaViolation){return response()->json(['code'=>'AUTHENTICATOR_VERIFICATION_FAILED'],422);}})->middleware('throttle:5,1');});
  Route::post('/api/pos/desktop/v3/service/reconcile',[$service,'nativeReconcile'])->middleware('throttle:30,1');Route::get('/api/pos/desktop/v3/service',[$service,'nativeRead'])->middleware('throttle:60,1');Route::post('/api/pos/desktop/v3/service',[$service,'nativeCommand'])->middleware('throttle:60,1');
  $branding=\App\Delivery\Http\BusinessExperience\BrandingController::class;Route::middleware(['web','session.active',\App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware::class,\App\Delivery\Http\Middleware\RequirePlatformSecurityMiddleware::class,'throttle:10,1'])->group(function()use($branding){Route::get('/platform/branding',[$branding,'read']);Route::post('/platform/branding',[$branding,'save'])->middleware(\App\Delivery\Http\Middleware\RequirePolicyAdministrationSessionContextMiddleware::class);});
  Route::get('/platform/business-domain',[C::class,'domain'])->middleware(['web','session.active',\App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware::class,\App\Delivery\Http\Middleware\RequirePlatformSecurityMiddleware::class,'throttle:30,1']);
  Route::post('/platform/business-domain',[C::class,'rename'])->middleware(['web','session.active',\App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware::class,\App\Delivery\Http\Middleware\RequirePlatformSecurityMiddleware::class,\App\Delivery\Http\Middleware\RequirePolicyAdministrationSessionContextMiddleware::class,'throttle:5,1']);
 }
}
