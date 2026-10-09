<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Pos\PosOperationalSalesSummaryRepository;
use App\Application\Pos\PosSaleHistoryWorkspaceRepository;
use App\Application\Pos\ViewPosOperationalSalesSummary;
use App\Application\Pos\ViewPosSaleHistoryWorkspace;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosOperationalSalesSummaryController;
use App\Delivery\Http\Pos\PosSaleHistoryWorkspaceController;
use App\Infrastructure\Pos\LaravelPosOperationalSalesSummaryRepository;
use App\Infrastructure\Pos\LaravelPosSaleHistoryWorkspaceRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class PosOperationalReportingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PosOperationalSalesSummaryRepository::class, function ($app): PosOperationalSalesSummaryRepository {
            /** @var Connection $connection */
            $connection = $app->make('db')->connection();

            return new LaravelPosOperationalSalesSummaryRepository(
                $connection,
                (bool) config('database.oneqay_persistence_enabled', false),
                (string) config('oneqay.runtime_class', ''),
                (bool) config('pos_operational_reporting.enabled', false),
            );
        });

        $this->app->scoped(ViewPosOperationalSalesSummary::class, fn ($app): ViewPosOperationalSalesSummary => new ViewPosOperationalSalesSummary(
            $app->make(PosOperationalSalesSummaryRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
        ));

        $this->app->scoped(PosSaleHistoryWorkspaceRepository::class, function ($app): PosSaleHistoryWorkspaceRepository {
            /** @var Connection $connection */
            $connection = $app->make('db')->connection();

            return new LaravelPosSaleHistoryWorkspaceRepository(
                $connection,
                (bool) config('database.oneqay_persistence_enabled', false),
                (string) config('oneqay.runtime_class', ''),
                (bool) config('pos_operational_reporting.enabled', false),
                (bool) config('pos_operational_reporting.sale_history_enabled', false),
            );
        });

        $this->app->scoped(ViewPosSaleHistoryWorkspace::class, fn ($app): ViewPosSaleHistoryWorkspace => new ViewPosSaleHistoryWorkspace(
            $app->make(PosSaleHistoryWorkspaceRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
        ));
    }

    public function boot(): void
    {
        $runtimeClass = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $sessionControlEnabled = (bool) config('oneqay.session_control.enabled', false)
            && (int) config('oneqay.session_control.idle_ttl_seconds', 0) === 7200
            && (int) config('oneqay.session_control.absolute_ttl_seconds', 0) === 43200;

        if (! in_array($runtimeClass, ['local', 'test', 'ci'], true)
            || ! $sessionControlEnabled
            || ! (bool) config('pos_operational_reporting.enabled', false)) {
            return;
        }

        Route::get('/pos/reporting/sales-summary', PosOperationalSalesSummaryController::class)
            ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.reporting.sales-summary');

        if ((bool) config('pos_operational_reporting.sale_history_enabled', false)) {
            Route::get('/pos/reporting/sales-history/{sale_id?}', PosSaleHistoryWorkspaceController::class)
                ->where('sale_id', 'sale-[a-f0-9]{24}')
                ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.reporting.sales-history');
        }
    }
}
