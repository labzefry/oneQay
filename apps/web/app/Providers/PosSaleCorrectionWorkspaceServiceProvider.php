<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Pos\PosSaleCorrectionWorkspaceRepository;
use App\Application\Pos\ViewPosSaleCorrectionWorkspace;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosSaleCorrectionWorkspaceController;
use App\Infrastructure\Pos\LaravelPosSaleCorrectionWorkspaceRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class PosSaleCorrectionWorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PosSaleCorrectionWorkspaceRepository::class, function ($app): PosSaleCorrectionWorkspaceRepository {
            /** @var Connection $connection */
            $connection = $app->make('db')->connection();

            return new LaravelPosSaleCorrectionWorkspaceRepository(
                $connection,
                (bool) config('database.oneqay_persistence_enabled', false),
                (string) config('oneqay.runtime_class', ''),
                (bool) config('pos_sale_correction_workspace.enabled', false),
                (bool) config('oneqay.pos_sale_completion.enabled', false),
                (bool) config('oneqay.pos_sale_void.enabled', false),
                (bool) config('oneqay.pos_sale_cash_refund.enabled', false),
            );
        });

        $this->app->scoped(ViewPosSaleCorrectionWorkspace::class, fn ($app): ViewPosSaleCorrectionWorkspace => new ViewPosSaleCorrectionWorkspace(
            $app->make(PosSaleCorrectionWorkspaceRepository::class),
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
            || ! (bool) config('database.oneqay_persistence_enabled', false)
            || ! $sessionControlEnabled
            || ! (bool) config('oneqay.pos_sale_completion.enabled', false)
            || ! (bool) config('oneqay.pos_sale_void.enabled', false)
            || ! (bool) config('oneqay.pos_sale_cash_refund.enabled', false)
            || ! (bool) config('pos_sale_correction_workspace.enabled', false)) {
            return;
        }

        Route::get('/pos/sales/corrections', PosSaleCorrectionWorkspaceController::class)
            ->middleware(['session.active', 'throttle:10,1', 'throttle:100,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.sales.corrections.workspace');
    }
}
