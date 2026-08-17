<?php

namespace App\Providers;

use App\Application\Access\DurableOrganizationalAccessRepository;
use App\Application\Authorization\DurablePolicyAdministrationRepository;
use App\Application\Authorization\DurableRolePermissionRepository;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Persistence\DurableContextGraphRepository;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Tenancy\TenantContextStore;
use App\Infrastructure\Access\LaravelDurableOrganizationalAccessRepository;
use App\Infrastructure\Authorization\LaravelDurablePolicyAdministrationRepository;
use App\Infrastructure\Authorization\LaravelDurableRolePermissionRepository;
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
            DurableOrganizationalAccessRepository::class,
            function ($app): DurableOrganizationalAccessRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelDurableOrganizationalAccessRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                );
            },
        );

        $this->app->scoped(
            DurableRolePermissionRepository::class,
            function ($app): DurableRolePermissionRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelDurableRolePermissionRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                );
            },
        );

        $this->app->scoped(
            DurablePolicyAdministrationRepository::class,
            function ($app): DurablePolicyAdministrationRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelDurablePolicyAdministrationRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                );
            },
        );

        $this->app->scoped(
            PolicyAdministrationClock::class,
            static fn (): PolicyAdministrationClock => new class implements PolicyAdministrationClock {
                public function nowUnix(): int
                {
                    return time();
                }
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
