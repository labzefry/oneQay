<?php

namespace App\Providers;

use App\Application\Persistence\DurableContextGraphRepository;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Tenancy\TenantContextStore;
use App\Infrastructure\Persistence\LaravelDurableContextGraphRepository;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use App\Infrastructure\Tenancy\RequestTenantContextStore;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            TenantContextStore::class,
            static fn (): TenantContextStore => new RequestTenantContextStore(),
        );

        $this->app->scoped(
            DurableContextGraphRepository::class,
            function ($app): DurableContextGraphRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelDurableContextGraphRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                );
            },
        );

        $this->app->scoped(
            PersistenceTransaction::class,
            function ($app): PersistenceTransaction {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelPersistenceTransaction(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                );
            },
        );
    }

    public function boot(): void
    {
        //
    }
}
