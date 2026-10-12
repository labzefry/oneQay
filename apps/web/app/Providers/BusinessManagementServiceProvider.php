<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Business\BusinessRepository;
use App\Application\Pos\InventoryReplenishmentRepository;
use App\Delivery\Http\Business\BusinessWorkspaceController;
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
            Route::get('/pos/business/options/{resource}', [BusinessWorkspaceController::class, 'options'])->name('business.options');
            Route::get('/pos/business/{module}/export', [BusinessWorkspaceController::class, 'export'])->name('business.export');
            Route::get('/pos/business/{module}', [BusinessWorkspaceController::class, 'show'])->name('business.workspace');
            Route::post('/pos/business/commands/{action}', [BusinessWorkspaceController::class, 'command'])->middleware('throttle:20,1')->name('business.command');
        });
    }
}
