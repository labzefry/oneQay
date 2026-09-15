<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Pos\PosProductSalesPerformanceWorkspaceRepository;
use App\Application\Pos\ViewPosProductSalesPerformanceWorkspace;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosProductSalesPerformanceWorkspaceController;
use App\Infrastructure\Pos\LaravelPosProductSalesPerformanceWorkspaceRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class PosProductSalesPerformanceWorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PosProductSalesPerformanceWorkspaceRepository::class, function ($app): PosProductSalesPerformanceWorkspaceRepository {
            /** @var Connection $connection */
            $connection = $app->make('db')->connection();

            return new LaravelPosProductSalesPerformanceWorkspaceRepository(
                $connection,
                (bool) config('database.oneqay_persistence_enabled', false),
                (string) config('oneqay.runtime_class', ''),
                (bool) config('pos_operational_reporting.enabled', false),
                (bool) config('pos_product_sales_performance.enabled', false),
            );
        });

        $this->app->scoped(
            ViewPosProductSalesPerformanceWorkspace::class,
            fn ($app): ViewPosProductSalesPerformanceWorkspace => new ViewPosProductSalesPerformanceWorkspace(
                $app->make(PosProductSalesPerformanceWorkspaceRepository::class),
                $app->make(OrganizationalContextStore::class),
                $app->make(DurableScopedAuthorizationPolicy::class),
            ),
        );
    }

    public function boot(): void
    {
        $runtimeClass = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $sessionControlEnabled = (bool) config('oneqay.session_control.enabled', false)
            && (int) config('oneqay.session_control.idle_ttl_seconds', 0) === 7200
            && (int) config('oneqay.session_control.absolute_ttl_seconds', 0) === 43200;

        if (! in_array($runtimeClass, ['local', 'test', 'ci'], true)
            || ! (bool) config('database.oneqay_persistence_enabled', false)
            || ! $sessionControlEnabled
            || ! (bool) config('pos_operational_reporting.enabled', false)
            || ! (bool) config('pos_product_sales_performance.enabled', false)) {
            return;
        }

        Route::get('/pos/reporting/product-performance', PosProductSalesPerformanceWorkspaceController::class)
            ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.reporting.product-performance');
    }
}
