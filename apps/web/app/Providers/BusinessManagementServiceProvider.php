<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Business\BusinessRepository;
use App\Application\Pos\InventoryReplenishmentRepository;
use App\Delivery\Http\Business\BusinessWorkspaceController;
use App\Delivery\Http\Business\BusinessAccessController;
use App\Delivery\Http\Middleware\RequirePolicyAdministrationSessionContextMiddleware;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Infrastructure\Business\LaravelBusinessRepository;
use App\Infrastructure\Runtime\ProductionBusinessRuntimeGate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class BusinessManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(\App\Application\Business\WorkforceAccounts::class,fn($a)=>new \App\Infrastructure\Business\LaravelWorkforceAccounts($a->make('db')->connection(),$a));
        $this->app->scoped(\App\Application\Business\WorkforceManagement::class,fn($a)=>new \App\Application\Business\WorkforceManagement($a->make(BusinessRepository::class),$a->make(\App\Application\Business\WorkforceAccounts::class)));
        $this->app->scoped(\App\Application\Pos\ShiftClosureWorkspaceRepository::class, fn ($app) => new \App\Infrastructure\Pos\LaravelShiftClosureWorkspaceRepository($app->make('db')->connection()));
        $this->app->bind(\App\Application\Business\BusinessDocuments::class, \App\Infrastructure\Business\BusinessDocumentCodec::class);
        $this->app->scoped(\App\Application\Business\MerchandisingRepository::class, fn ($app) => new \App\Infrastructure\Business\LaravelMerchandisingRepository($app->make('db')->connection()));
        $this->app->scoped(\App\Application\Business\CommerceReportingRepository::class, fn ($app) => new \App\Infrastructure\Business\LaravelCommerceReportingRepository($app->make('db')->connection()));
        $this->app->scoped(BusinessRepository::class, fn ($app): BusinessRepository => new LaravelBusinessRepository($app->make('db')->connection(), $app->make(InventoryReplenishmentRepository::class)));
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(base_path('database/module-migrations/business'));
        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        if (!(in_array($runtime, ['local', 'test', 'ci'], true) || ProductionBusinessRuntimeGate::allows($runtime))
            || !(bool) config('database.oneqay_persistence_enabled', false)
            || !(bool) config('oneqay.session_control.enabled', false)
            || (int) config('oneqay.session_control.idle_ttl_seconds', 0) !== 7200
            || (int) config('oneqay.session_control.absolute_ttl_seconds', 0) !== 43200) return;

        Route::middleware(['web', 'session.active', 'throttle:60,1', RequirePosSessionContextMiddleware::class])->group(static function (): void {
            $catalog=\App\Delivery\Http\Business\MerchandisingController::class;
            Route::get('/pos/business/products',[$catalog,'catalog'])->name('business.products');
            Route::post('/pos/business/products/categories',[$catalog,'createCategory'])->middleware('throttle:20,1');
            Route::post('/pos/business/products/save',[$catalog,'saveProduct'])->middleware('throttle:20,1');
            Route::post('/pos/business/products/import/preview',[$catalog,'preview'])->middleware('throttle:10,1');
            Route::post('/pos/business/products/import/commit',[$catalog,'commit'])->middleware('throttle:5,1');
            Route::get('/pos/business/products/import/template',[$catalog,'template']);
            Route::get('/pos/business/settings',[$catalog,'settings'])->name('business.settings');
            Route::post('/pos/business/settings',[$catalog,'saveSettings'])->middleware('throttle:10,1');
            Route::get('/pos/business/reports',[$catalog,'reports'])->name('business.reports');
            Route::get('/pos/business/reports/sales-trend',[$catalog,'trend']);
            Route::get('/pos/business/reports/{report}/download',[$catalog,'download'])->middleware('throttle:10,1');
            Route::get('/pos/business/access', [BusinessAccessController::class, 'show'])->name('business.access');
            Route::post('/pos/business/access/roles', [BusinessAccessController::class, 'apply'])->middleware(RequirePolicyAdministrationSessionContextMiddleware::class)->name('business.access.roles');
            Route::get('/pos/business/options/{resource}', [BusinessWorkspaceController::class, 'options'])->name('business.options');
            Route::get('/pos/business/{module}/export', [BusinessWorkspaceController::class, 'export'])->name('business.export');
            // Dedicated pages (especially desktop settings registered by another provider) must never match the generic table route.
            Route::get('/pos/business/{module}', [BusinessWorkspaceController::class, 'show'])->whereIn('module', ['suppliers', 'purchases', 'customers', 'finance', 'people', 'audit'])->name('business.workspace');
            Route::post('/pos/business/commands/{action}', [BusinessWorkspaceController::class, 'command'])->middleware('throttle:20,1')->name('business.command');
        });
    }
}
