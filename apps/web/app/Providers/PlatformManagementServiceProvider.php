<?php
declare(strict_types=1);
namespace App\Providers;
use App\Application\Platform\{PlatformOwnerAccess,PlatformStore,BusinessActivation,BusinessAccountProvisioner};
use App\Application\Notification\{MailStore,MailTransport};
use App\Application\Identity\FirstPartyIdentityEligibilityVerifier;
use App\Application\Organization\OrganizationalContextStore;
use App\Infrastructure\Platform\{LaravelPlatformStore,LaravelBusinessAccountProvisioner,PlatformTenantEligibility};
use App\Infrastructure\Identity\LaravelTenantSessionRevoker;
use App\Infrastructure\Notification\{LaravelMailStore,SmtpMailTransport};
use App\Infrastructure\Runtime\ProductionBusinessRuntimeGate;
use App\Delivery\Http\Platform\PlatformController;
use App\Delivery\Http\Middleware\{RequirePosSessionContextMiddleware,RequirePolicyAdministrationSessionContextMiddleware,RequirePlatformSecurityMiddleware};
use App\Console\Commands\PlatformMailWorkCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
// Author by Lab | zefry
final class PlatformManagementServiceProvider extends ServiceProvider
{
    public function register():void
    {
        $this->app->scoped(\App\Application\Website\WebsiteStore::class,fn($app)=>new \App\Infrastructure\Website\LaravelWebsiteStore($app->make('db')->connection(),json_decode(file_get_contents(resource_path('website/defaults.json')),true,64,JSON_THROW_ON_ERROR)));
        $this->app->scoped(\App\Application\Website\WebsiteManagement::class,fn($app)=>new \App\Application\Website\WebsiteManagement($app->make(PlatformOwnerAccess::class),$app->make(PlatformStore::class),$app->make(\App\Application\Website\WebsiteStore::class)));

        $this->app->scoped(PlatformOwnerAccess::class,fn($app)=>new PlatformOwnerAccess($app->make(OrganizationalContextStore::class),(string)config('platform.owner_tenant',''),(string)config('platform.owner_identity','')));
        $this->app->scoped(PlatformStore::class,fn($app)=>new LaravelPlatformStore($app->make('db')->connection()));
        $this->app->scoped(LaravelMailStore::class,fn($app)=>new LaravelMailStore($app->make('db')->connection(),$app->make('encrypter')));
        $this->app->alias(LaravelMailStore::class,MailStore::class);
        $this->app->scoped(MailTransport::class,fn($app)=>new SmtpMailTransport($app->make(LaravelMailStore::class)));
        $this->app->scoped(BusinessAccountProvisioner::class,fn($app)=>new LaravelBusinessAccountProvisioner($app,new LaravelTenantSessionRevoker($app->make('db')->connection())));
        $this->app->scoped(BusinessActivation::class,fn($app)=>new BusinessActivation($app->make(PlatformOwnerAccess::class),$app->make(PlatformStore::class),$app->make(BusinessAccountProvisioner::class),$app->make(MailStore::class),(string)config('platform.public_url','')));
        $this->app->extend(FirstPartyIdentityEligibilityVerifier::class,fn($inner,$app)=>config('platform.enabled',false)?new PlatformTenantEligibility($inner,$app->make(PlatformStore::class)):$inner);
    }
    public function boot():void
    {
        $this->loadMigrationsFrom(base_path('database/module-migrations/platform'));
        $this->commands([PlatformMailWorkCommand::class,\App\Console\Commands\PlatformMailBounceCommand::class]);
        if(!config('platform.enabled',false)||!config('database.oneqay_persistence_enabled')||!config('oneqay.session_control.enabled')||!ProductionBusinessRuntimeGate::allows((string)config('oneqay.runtime_class')))return;
        Route::middleware(['web','session.active',RequirePosSessionContextMiddleware::class,RequirePlatformSecurityMiddleware::class,'throttle:30,1'])->group(function():void {
            Route::get('/platform',[PlatformController::class,'show'])->name('platform.control');
            $site=\App\Delivery\Http\Website\WebsiteController::class;
            Route::get('/platform/site',[$site,'editor']);
            Route::get('/platform/site/media/{id}',[$site,'media'])->where('id','[a-f0-9]{32}');
            Route::post('/platform/site/media',[$site,'upload'])->middleware(RequirePolicyAdministrationSessionContextMiddleware::class);
            Route::get('/platform/site/{slug}/preview',[$site,'preview'])->where('slug','[a-z][a-z0-9-]{0,63}');
            Route::get('/platform/site/{slug}',[$site,'editor'])->where('slug','[a-z][a-z0-9-]{0,63}');
            Route::post('/platform/site/{slug}',[$site,'command'])->where('slug','[a-z][a-z0-9-]{0,63}')->middleware(RequirePolicyAdministrationSessionContextMiddleware::class);

            Route::post('/platform/businesses',[PlatformController::class,'command'])->middleware(RequirePolicyAdministrationSessionContextMiddleware::class);
            Route::post('/platform/mail/settings',[PlatformController::class,'configure'])->middleware(RequirePolicyAdministrationSessionContextMiddleware::class);
            Route::post('/platform/mail/retry',[PlatformController::class,'retry'])->middleware(RequirePolicyAdministrationSessionContextMiddleware::class);
            Route::get('/platform/mail/inspect',[PlatformController::class,'inspect'])->middleware('throttle:3,1');
        });
        Route::middleware(['web','throttle:10,1'])->group(function():void {
            Route::get('/business-login',[\App\Delivery\Http\Platform\BusinessLoginController::class,'show']);
            Route::post('/business-login',[\App\Delivery\Http\Platform\BusinessLoginController::class,'login'])->middleware(['throttle:5,1','throttle:20,60']);
            Route::get('/activate-business',fn()=>Inertia::render('Platform/Activate'))->name('platform.activate');
            Route::post('/activate-business',[PlatformController::class,'activate']);
        });
        Route::middleware(['web'])->group(function():void {
            $site=\App\Delivery\Http\Website\WebsiteController::class;
            Route::get('/owner-login',fn()=>Inertia::render('Foundation',['headline'=>'oneQay platform owner']))->name('platform.owner-login');
            Route::get('/pages/{slug}',[$site,'page'])->where('slug','[a-z][a-z0-9-]{0,63}');
            Route::get('/register',[$site,'registration']);
            Route::post('/register',[$site,'register'])->middleware(['throttle:3,1','throttle:20,1440']);
            Route::get('/site/media/{id}',[$site,'media'])->where('id','[a-f0-9]{32}');
        });
        // External provider adapter uses HMAC authentication, deliberately outside cookie/CSRF middleware.
        Route::post('/platform/mail/feedback',[PlatformController::class,'feedback'])->middleware('throttle:60,1');
    }
}
