<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Pos\PosCatalogInventorySetupWorkspaceRepository;
use App\Application\Pos\ViewPosCatalogInventorySetupWorkspace;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosCatalogInventorySetupWorkspaceController;
use App\Infrastructure\Pos\LaravelPosCatalogInventorySetupWorkspaceRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class PosCatalogInventorySetupWorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(PosCatalogInventorySetupWorkspaceRepository::class, function ($app): PosCatalogInventorySetupWorkspaceRepository {
            /** @var Connection $connection */
            $connection = $app->make('db')->connection();

            return new LaravelPosCatalogInventorySetupWorkspaceRepository(
                $connection,
                (bool) config('database.oneqay_persistence_enabled', false),
                (string) config('oneqay.runtime_class', ''),
                (bool) config('pos_catalog_inventory_setup.enabled', false),
                (bool) config('oneqay.pos_catalog_preparation.enabled', false),
                (bool) config('oneqay.pos_inventory_baseline.enabled', false),
            );
        });

        $this->app->scoped(ViewPosCatalogInventorySetupWorkspace::class, fn ($app): ViewPosCatalogInventorySetupWorkspace => new ViewPosCatalogInventorySetupWorkspace(
            $app->make(PosCatalogInventorySetupWorkspaceRepository::class),
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
            || ! (bool) config('oneqay.pos_catalog_preparation.enabled', false)
            || ! (bool) config('oneqay.pos_inventory_baseline.enabled', false)
            || ! (bool) config('pos_catalog_inventory_setup.enabled', false)) {
            return;
        }

        Route::get('/pos/catalog-inventory/setup', PosCatalogInventorySetupWorkspaceController::class)
            ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.catalog-inventory.setup');
    }
}
