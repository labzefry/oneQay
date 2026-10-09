<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Pos\PosInventoryAccountabilityWorkspaceRepository;
use App\Application\Pos\ViewPosInventoryAccountabilityWorkspace;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosInventoryAccountabilityWorkspaceController;
use App\Infrastructure\Pos\LaravelPosInventoryAccountabilityWorkspaceRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class PosInventoryAccountabilityWorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            PosInventoryAccountabilityWorkspaceRepository::class,
            function ($app): PosInventoryAccountabilityWorkspaceRepository {
                return new LaravelPosInventoryAccountabilityWorkspaceRepository(
                    $this->connection($app),
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                    (bool) config('pos_inventory_accountability.enabled', false),
                );
            },
        );

        $this->app->scoped(
            ViewPosInventoryAccountabilityWorkspace::class,
            fn ($app): ViewPosInventoryAccountabilityWorkspace => new ViewPosInventoryAccountabilityWorkspace(
                $app->make(PosInventoryAccountabilityWorkspaceRepository::class),
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
            || ! (bool) config('pos_inventory_accountability.enabled', false)) {
            return;
        }

        Route::get('/pos/inventory/accountability', PosInventoryAccountabilityWorkspaceController::class)
            ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.inventory.accountability.workspace');
    }

    private function connection($app): Connection
    {
        /** @var Connection $connection */
        $connection = $app->make('db')->connection();
        return $connection;
    }
}
