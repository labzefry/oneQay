<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Pos\PosActiveShiftPerformanceWorkspaceRepository;
use App\Application\Pos\ViewPosActiveShiftPerformanceWorkspace;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosActiveShiftPerformanceWorkspaceController;
use App\Infrastructure\Pos\LaravelPosActiveShiftPerformanceWorkspaceRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class PosActiveShiftPerformanceWorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PosActiveShiftPerformanceWorkspaceRepository::class, function ($app): PosActiveShiftPerformanceWorkspaceRepository {
            /** @var Connection $connection */
            $connection = $app->make('db')->connection();

            return new LaravelPosActiveShiftPerformanceWorkspaceRepository(
                $connection,
                (bool) config('database.oneqay_persistence_enabled', false),
                (string) config('oneqay.runtime_class', ''),
                (bool) config('pos_operational_reporting.enabled', false),
                (bool) config('pos_active_shift_performance.enabled', false),
            );
        });

        $this->app->scoped(
            ViewPosActiveShiftPerformanceWorkspace::class,
            fn ($app): ViewPosActiveShiftPerformanceWorkspace => new ViewPosActiveShiftPerformanceWorkspace(
                $app->make(PosActiveShiftPerformanceWorkspaceRepository::class),
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
            || ! (bool) config('pos_active_shift_performance.enabled', false)) {
            return;
        }

        Route::get('/pos/reporting/active-shift-performance', PosActiveShiftPerformanceWorkspaceController::class)
            ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.reporting.active-shift-performance');
    }
}
