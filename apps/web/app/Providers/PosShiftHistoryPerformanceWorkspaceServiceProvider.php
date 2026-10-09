<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Pos\PosShiftHistoryPerformanceWorkspaceRepository;
use App\Application\Pos\ViewPosShiftHistoryPerformanceWorkspace;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosShiftHistoryPerformanceWorkspaceController;
use App\Infrastructure\Pos\LaravelPosShiftHistoryPerformanceWorkspaceRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class PosShiftHistoryPerformanceWorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PosShiftHistoryPerformanceWorkspaceRepository::class, function ($app): PosShiftHistoryPerformanceWorkspaceRepository {
            /** @var Connection $connection */
            $connection = $app->make('db')->connection();

            return new LaravelPosShiftHistoryPerformanceWorkspaceRepository(
                $connection,
                (bool) config('database.oneqay_persistence_enabled', false),
                (string) config('oneqay.runtime_class', ''),
                (bool) config('pos_operational_reporting.enabled', false),
                (bool) config('pos_shift_history_performance.enabled', false),
                filter_var(env('ONEQAY_POS_SHIFT_CLOSE_ENABLED', false), FILTER_VALIDATE_BOOL),
            );
        });

        $this->app->scoped(
            ViewPosShiftHistoryPerformanceWorkspace::class,
            fn ($app): ViewPosShiftHistoryPerformanceWorkspace => new ViewPosShiftHistoryPerformanceWorkspace(
                $app->make(PosShiftHistoryPerformanceWorkspaceRepository::class),
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
        $shiftCloseEnabled = filter_var(env('ONEQAY_POS_SHIFT_CLOSE_ENABLED', false), FILTER_VALIDATE_BOOL);

        if (! in_array($runtimeClass, ['local', 'test', 'ci'], true)
            || ! (bool) config('database.oneqay_persistence_enabled', false)
            || ! $sessionControlEnabled
            || ! (bool) config('pos_operational_reporting.enabled', false)
            || ! (bool) config('pos_shift_history_performance.enabled', false)
            || ! $shiftCloseEnabled) {
            return;
        }

        Route::get('/pos/reporting/shift-history/{shiftId?}', PosShiftHistoryPerformanceWorkspaceController::class)
            ->where('shiftId', '[a-f0-9]{32}')
            ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.reporting.shift-history');
    }
}
