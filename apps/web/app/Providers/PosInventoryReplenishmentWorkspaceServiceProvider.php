<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Pos\InventoryReplenishmentClock;
use App\Application\Pos\InventoryReplenishmentRepository;
use App\Application\Pos\PosInventoryReplenishmentWorkspaceRepository;
use App\Application\Pos\ReplenishInventory;
use App\Application\Pos\ViewPosInventoryReplenishmentWorkspace;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosInventoryReplenishmentController;
use App\Delivery\Http\Pos\PosInventoryReplenishmentWorkspaceController;
use App\Infrastructure\Pos\LaravelInventoryReplenishmentRepository;
use App\Infrastructure\Pos\LaravelPosInventoryReplenishmentWorkspaceRepository;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class PosInventoryReplenishmentWorkspaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(InventoryReplenishmentRepository::class, function ($app): InventoryReplenishmentRepository {
            return new LaravelInventoryReplenishmentRepository(
                $this->connection($app),
                (bool) config('database.oneqay_persistence_enabled', false),
                (string) config('oneqay.runtime_class', ''),
                (bool) config('pos_inventory_replenishment.enabled', false),
            );
        });

        $this->app->scoped(InventoryReplenishmentClock::class, static fn (): InventoryReplenishmentClock => new class implements InventoryReplenishmentClock {
            public function nowUnix(): int { return time(); }
        });

        $this->app->scoped(ReplenishInventory::class, fn ($app): ReplenishInventory => new ReplenishInventory(
            $app->make(InventoryReplenishmentRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
            $app->make(PersistenceTransaction::class),
            $app->make(InventoryReplenishmentClock::class),
        ));

        $this->app->scoped(PosInventoryReplenishmentWorkspaceRepository::class, function ($app): PosInventoryReplenishmentWorkspaceRepository {
            return new LaravelPosInventoryReplenishmentWorkspaceRepository(
                $this->connection($app),
                (bool) config('database.oneqay_persistence_enabled', false),
                (string) config('oneqay.runtime_class', ''),
                (bool) config('pos_inventory_replenishment.enabled', false),
            );
        });

        $this->app->scoped(ViewPosInventoryReplenishmentWorkspace::class, fn ($app): ViewPosInventoryReplenishmentWorkspace => new ViewPosInventoryReplenishmentWorkspace(
            $app->make(PosInventoryReplenishmentWorkspaceRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
        ));
    }

    public function boot(): void
    {
        // Keep Sprint164 schema discovery module-owned and independent from delivery activation.
        $this->loadMigrationsFrom(base_path('database/module-migrations/pos'));

        $runtimeClass = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $sessionControlEnabled = (bool) config('oneqay.session_control.enabled', false)
            && (int) config('oneqay.session_control.idle_ttl_seconds', 0) === 7200
            && (int) config('oneqay.session_control.absolute_ttl_seconds', 0) === 43200;

        if (! in_array($runtimeClass, ['local', 'test', 'ci'], true)
            || ! (bool) config('database.oneqay_persistence_enabled', false)
            || ! $sessionControlEnabled
            || ! (bool) config('oneqay.pos_sale_completion.enabled', false)
            || ! (bool) config('oneqay.pos_inventory_baseline.enabled', false)
            || ! (bool) config('pos_inventory_replenishment.enabled', false)) {
            return;
        }

        Route::get('/pos/inventory/replenishment', PosInventoryReplenishmentWorkspaceController::class)
            ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.inventory.replenishment.workspace');

        Route::post('/pos/inventory/replenishment', PosInventoryReplenishmentController::class)
            ->middleware(['session.active', 'throttle:20,1', 'throttle:200,60', RequirePosSessionContextMiddleware::class])
            ->name('pos.inventory.replenishment.record');
    }

    private function connection($app): Connection
    {
        /** @var Connection $connection */
        $connection = $app->make('db')->connection();
        return $connection;
    }
}
