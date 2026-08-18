<?php

namespace App\Providers;

use App\Application\Access\DurableOrganizationalAccessRepository;
use App\Application\Authorization\DurablePolicyAdministrationRepository;
use App\Application\Authorization\DurableRolePermissionRepository;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Authorization\ProtectedControlAdministratorLifecycleRepository;
use App\Application\Identity\FirstPartyIdentityCredentialVerifier;
use App\Application\Identity\InitialPasswordEnrollmentRepository;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\OrganizationalRelationshipVerifier;
use App\Application\Persistence\DurableContextGraphRepository;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Tenancy\TenantContextStore;
use App\Application\Tenancy\TenantMembershipVerifier;
use App\Infrastructure\Access\LaravelDurableOrganizationalAccessRepository;
use App\Infrastructure\Authorization\LaravelDurablePolicyAdministrationRepository;
use App\Infrastructure\Authorization\LaravelDurableRolePermissionRepository;
use App\Infrastructure\Authorization\LaravelInitialTenantAdministratorProvisioningRepository;
use App\Infrastructure\Authorization\LaravelProtectedControlAdministratorLifecycleRepository;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityCredentialVerifier;
use App\Infrastructure\Identity\LaravelInitialPasswordEnrollmentRepository;
use App\Infrastructure\Organization\LaravelOrganizationalRelationshipVerifier;
use App\Infrastructure\Organization\RequestOrganizationalContextStore;
use App\Infrastructure\Persistence\LaravelDurableContextGraphRepository;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use App\Infrastructure\Tenancy\LaravelTenantMembershipVerifier;
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
            OrganizationalContextStore::class,
            static fn (): OrganizationalContextStore => new RequestOrganizationalContextStore(),
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
            TenantMembershipVerifier::class,
            fn ($app): TenantMembershipVerifier => new LaravelTenantMembershipVerifier(
                $app->make(DurableOrganizationalAccessRepository::class),
            ),
        );

        $this->app->scoped(
            OrganizationalRelationshipVerifier::class,
            fn ($app): OrganizationalRelationshipVerifier => new LaravelOrganizationalRelationshipVerifier(
                $app->make(DurableOrganizationalAccessRepository::class),
            ),
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
            InitialTenantAdministratorProvisioningRepository::class,
            function ($app): InitialTenantAdministratorProvisioningRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelInitialTenantAdministratorProvisioningRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                );
            },
        );

        $this->app->scoped(
            ProtectedControlAdministratorLifecycleRepository::class,
            function ($app): ProtectedControlAdministratorLifecycleRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelProtectedControlAdministratorLifecycleRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                );
            },
        );

        $this->app->scoped(
            FirstPartyIdentityCredentialVerifier::class,
            function ($app): FirstPartyIdentityCredentialVerifier {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelFirstPartyIdentityCredentialVerifier(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                );
            },
        );

        $this->app->scoped(
            InitialPasswordEnrollmentRepository::class,
            function ($app): InitialPasswordEnrollmentRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelInitialPasswordEnrollmentRepository(
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
