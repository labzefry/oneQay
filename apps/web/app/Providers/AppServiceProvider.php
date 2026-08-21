<?php

namespace App\Providers;

use App\Application\Access\DurableOrganizationalAccessRepository;
use App\Application\Authorization\DurablePolicyAdministrationRepository;
use App\Application\Authorization\DurableRolePermissionRepository;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Authorization\ProtectedControlAdministratorLifecycleRepository;
use App\Application\Identity\AuthenticatedPasswordChangeClock;
use App\Application\Identity\AuthenticatedPasswordChangeRepository;
use App\Application\Identity\AuthenticatedPasswordChangeService;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapRepository;
use App\Application\Identity\FirstPartyCredentialEpochRepository;
use App\Application\Identity\FirstPartyIdentityCredentialVerifier;
use App\Application\Identity\InitialPasswordEnrollmentRepository;
use App\Application\Identity\PrivilegedStepUpClock;
use App\Application\Identity\PrivilegedTotpClock;
use App\Application\Identity\PrivilegedTotpEngine;
use App\Application\Identity\PrivilegedTotpMfaRepository;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Application\Identity\RecoveryCodeClock;
use App\Application\Identity\RecoveryCodeRepository;
use App\Application\Identity\RecoveryPasswordResetRepository;
use App\Application\Identity\VerifyFirstPartyCredentialEpoch;
use App\Application\Identity\VerifyFirstPartyIdentityCredential;
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
use App\Infrastructure\Identity\LaravelAuthenticatedPasswordChangeRepository;
use App\Infrastructure\Identity\LaravelFirstControlPrincipalCredentialBootstrapRepository;
use App\Infrastructure\Identity\LaravelFirstPartyCredentialEpochRepository;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityCredentialVerifier;
use App\Infrastructure\Identity\LaravelInitialPasswordEnrollmentRepository;
use App\Infrastructure\Identity\LaravelPrivilegedTotpMfaRepository;
use App\Infrastructure\Identity\LaravelRecoveryCodeRepository;
use App\Infrastructure\Identity\LaravelRecoveryPasswordResetRepository;
use App\Infrastructure\Identity\OtphpPrivilegedTotpEngine;
use App\Infrastructure\Organization\LaravelOrganizationalRelationshipVerifier;
use App\Infrastructure\Organization\RequestOrganizationalContextStore;
use App\Infrastructure\Persistence\LaravelDurableContextGraphRepository;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use App\Infrastructure\Tenancy\LaravelTenantMembershipVerifier;
use App\Infrastructure\Tenancy\RequestTenantContextStore;
use Illuminate\Contracts\Encryption\Encrypter;
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
            FirstPartyCredentialEpochRepository::class,
            function ($app): FirstPartyCredentialEpochRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelFirstPartyCredentialEpochRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                );
            },
        );

        $this->app->scoped(
            AuthenticatedPasswordChangeRepository::class,
            function ($app): AuthenticatedPasswordChangeRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelAuthenticatedPasswordChangeRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                );
            },
        );

        $this->app->scoped(
            AuthenticatedPasswordChangeService::class,
            fn ($app): AuthenticatedPasswordChangeService => new AuthenticatedPasswordChangeService(
                $app->make(AuthenticatedPasswordChangeRepository::class),
                $app->make(VerifyFirstPartyIdentityCredential::class),
                $app->make(VerifyFirstPartyCredentialEpoch::class),
                $app->make(PrivilegedTotpMfaService::class),
                $app->make(PersistenceTransaction::class),
                $app->make(AuthenticatedPasswordChangeClock::class),
                (bool) config('oneqay.privileged_totp_mfa.enabled', false),
            ),
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
            RecoveryCodeRepository::class,
            function ($app): RecoveryCodeRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelRecoveryCodeRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                    (bool) config('oneqay.authentication_recovery.enabled', false),
                );
            },
        );

        $this->app->scoped(
            RecoveryPasswordResetRepository::class,
            function ($app): RecoveryPasswordResetRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelRecoveryPasswordResetRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                    (bool) config('oneqay.authentication_recovery.enabled', false),
                );
            },
        );

        $this->app->scoped(
            FirstControlPrincipalCredentialBootstrapRepository::class,
            function ($app): FirstControlPrincipalCredentialBootstrapRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelFirstControlPrincipalCredentialBootstrapRepository(
                    $connection,
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                    (bool) config('oneqay.first_control_principal_credential_bootstrap.enabled', false),
                );
            },
        );

        $this->app->scoped(
            PrivilegedTotpMfaRepository::class,
            function ($app): PrivilegedTotpMfaRepository {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection();

                return new LaravelPrivilegedTotpMfaRepository(
                    $connection,
                    $app->make(Encrypter::class),
                    (bool) config('database.oneqay_persistence_enabled', false),
                    (string) config('oneqay.runtime_class', ''),
                    (bool) config('oneqay.privileged_totp_mfa.enabled', false),
                );
            },
        );

        $this->app->scoped(
            PrivilegedTotpEngine::class,
            static fn (): PrivilegedTotpEngine => new OtphpPrivilegedTotpEngine(),
        );

        $this->app->scoped(
            PrivilegedTotpClock::class,
            static fn (): PrivilegedTotpClock => new class implements PrivilegedTotpClock {
                public function nowUnix(): int
                {
                    return time();
                }
            },
        );

        $this->app->scoped(
            PrivilegedStepUpClock::class,
            static fn (): PrivilegedStepUpClock => new class implements PrivilegedStepUpClock {
                public function nowUnix(): int
                {
                    return time();
                }
            },
        );

        $this->app->scoped(
            RecoveryCodeClock::class,
            static fn (): RecoveryCodeClock => new class implements RecoveryCodeClock {
                public function nowUnix(): int
                {
                    return time();
                }
            },
        );

        $this->app->scoped(
            AuthenticatedPasswordChangeClock::class,
            static fn (): AuthenticatedPasswordChangeClock => new class implements AuthenticatedPasswordChangeClock {
                public function nowUnix(): int
                {
                    return time();
                }
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
        $technicalPreviewEnabled = filter_var(
            env('ONEQAY_TECHNICAL_PREVIEW_ENABLED', false),
            FILTER_VALIDATE_BOOL,
        );

        if ($technicalPreviewEnabled) {
            return;
        }

        // TechnicalPreviewServiceProvider is registered after this provider and owns the
        // synthetic verifier pair only while Technical Preview is explicitly armed.
        // Restore durable authentication verification for normal Local/Test/CI runtime.
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
    }
}
