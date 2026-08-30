<?php

// Sprint47 JRN-004 JRN-006 compatibility preservation anchor.

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
use App\Application\Identity\FirstPartyIdentityDisablementSessionTerminationRepository;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationRepository;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationService;
use App\Application\Identity\FirstPartyIdentityEligibilityVerifier;
use App\Application\Identity\FirstPartySessionAuthorityClock;
use App\Application\Identity\FirstPartySessionAuthorityRepository;
use App\Application\Identity\FirstPartySessionAuthorityService;
use App\Application\Identity\InitialPasswordEnrollmentRepository;
use App\Application\Identity\PrivilegedStepUpClock;
use App\Application\Identity\PrivilegedTotpClock;
use App\Application\Identity\PrivilegedTotpEngine;
use App\Application\Identity\PrivilegedTotpFactorEpochRepository;
use App\Application\Identity\PrivilegedTotpMfaRepository;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Application\Identity\PrivilegedTotpRecoveryClock;
use App\Application\Identity\PrivilegedTotpRecoveryRepository;
use App\Application\Identity\RecoveryCodeClock;
use App\Application\Identity\RecoveryCodeRepository;
use App\Application\Identity\RecoveryPasswordResetRepository;
use App\Application\Identity\VerifyFirstPartyCredentialEpoch;
use App\Application\Identity\VerifyFirstPartyIdentityCredential;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\OrganizationalRelationshipVerifier;
use App\Application\Persistence\DurableContextGraphRepository;
use App\Application\Persistence\PersistenceTransaction;
use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Pos\CompleteSale;
use App\Application\Pos\DurablePosSaleRepository;
use App\Application\Pos\PosSaleClock;
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
use App\Infrastructure\Identity\LaravelFirstPartyIdentityDisablementSessionTerminationRepository;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityAdministrationRepository;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityVerifier;
use App\Infrastructure\Identity\LaravelFirstPartySessionAuthorityRepository;
use App\Infrastructure\Identity\LaravelInitialPasswordEnrollmentRepository;
use App\Infrastructure\Identity\LaravelPrivilegedTotpFactorEpochRepository;
use App\Infrastructure\Identity\LaravelPrivilegedTotpMfaRepository;
use App\Infrastructure\Identity\LaravelPrivilegedTotpRecoveryRepository;
use App\Infrastructure\Identity\LaravelRecoveryCodeRepository;
use App\Infrastructure\Identity\LaravelRecoveryPasswordResetRepository;
use App\Infrastructure\Identity\OtphpPrivilegedTotpEngine;
use App\Infrastructure\Organization\LaravelOrganizationalRelationshipVerifier;
use App\Infrastructure\Organization\RequestOrganizationalContextStore;
use App\Infrastructure\Pos\LaravelDurablePosSaleRepository;
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
        $this->app->scoped(TenantContextStore::class, static fn (): TenantContextStore => new RequestTenantContextStore());
        $this->app->scoped(OrganizationalContextStore::class, static fn (): OrganizationalContextStore => new RequestOrganizationalContextStore());

        $this->app->scoped(DurableContextGraphRepository::class, function ($app): DurableContextGraphRepository {
            return new LaravelDurableContextGraphRepository($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });
        $this->app->scoped(DurableOrganizationalAccessRepository::class, function ($app): DurableOrganizationalAccessRepository {
            return new LaravelDurableOrganizationalAccessRepository($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });
        $this->app->scoped(TenantMembershipVerifier::class, fn ($app): TenantMembershipVerifier => new LaravelTenantMembershipVerifier($app->make(DurableOrganizationalAccessRepository::class)));
        $this->app->scoped(OrganizationalRelationshipVerifier::class, fn ($app): OrganizationalRelationshipVerifier => new LaravelOrganizationalRelationshipVerifier($app->make(DurableOrganizationalAccessRepository::class)));

        $this->app->scoped(DurableRolePermissionRepository::class, function ($app): DurableRolePermissionRepository {
            return new LaravelDurableRolePermissionRepository($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });
        $this->app->scoped(DurablePolicyAdministrationRepository::class, function ($app): DurablePolicyAdministrationRepository {
            return new LaravelDurablePolicyAdministrationRepository($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });
        $this->app->scoped(InitialTenantAdministratorProvisioningRepository::class, function ($app): InitialTenantAdministratorProvisioningRepository {
            return new LaravelInitialTenantAdministratorProvisioningRepository($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });
        $this->app->scoped(ProtectedControlAdministratorLifecycleRepository::class, function ($app): ProtectedControlAdministratorLifecycleRepository {
            return new LaravelProtectedControlAdministratorLifecycleRepository($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });

        $this->app->scoped(FirstPartyIdentityCredentialVerifier::class, function ($app): FirstPartyIdentityCredentialVerifier {
            return new LaravelFirstPartyIdentityCredentialVerifier($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });
        $this->app->scoped(FirstPartyCredentialEpochRepository::class, function ($app): FirstPartyCredentialEpochRepository {
            return new LaravelFirstPartyCredentialEpochRepository($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });
        $this->app->scoped(AuthenticatedPasswordChangeRepository::class, function ($app): AuthenticatedPasswordChangeRepository {
            return new LaravelAuthenticatedPasswordChangeRepository($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });
        $this->app->scoped(AuthenticatedPasswordChangeService::class, fn ($app): AuthenticatedPasswordChangeService => new AuthenticatedPasswordChangeService(
            $app->make(AuthenticatedPasswordChangeRepository::class),
            $app->make(VerifyFirstPartyIdentityCredential::class),
            $app->make(VerifyFirstPartyCredentialEpoch::class),
            $app->make(PrivilegedTotpMfaService::class),
            $app->make(PersistenceTransaction::class),
            $app->make(AuthenticatedPasswordChangeClock::class),
            (bool) config('oneqay.privileged_totp_mfa.enabled', false),
        ));
        $this->app->scoped(InitialPasswordEnrollmentRepository::class, function ($app): InitialPasswordEnrollmentRepository {
            return new LaravelInitialPasswordEnrollmentRepository($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });

        $this->app->scoped(RecoveryCodeRepository::class, function ($app): RecoveryCodeRepository {
            return new LaravelRecoveryCodeRepository(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                (bool) config('oneqay.authentication_recovery.enabled', false),
            );
        });
        $this->app->scoped(RecoveryPasswordResetRepository::class, function ($app): RecoveryPasswordResetRepository {
            return new LaravelRecoveryPasswordResetRepository(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                (bool) config('oneqay.authentication_recovery.enabled', false),
            );
        });
        $this->app->scoped(FirstControlPrincipalCredentialBootstrapRepository::class, function ($app): FirstControlPrincipalCredentialBootstrapRepository {
            return new LaravelFirstControlPrincipalCredentialBootstrapRepository(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                (bool) config('oneqay.first_control_principal_credential_bootstrap.enabled', false),
            );
        });

        $this->app->scoped(PrivilegedTotpMfaRepository::class, function ($app): PrivilegedTotpMfaRepository {
            return new LaravelPrivilegedTotpMfaRepository(
                $this->connection($app),
                $app->make(Encrypter::class),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                $this->mfaOperationalEnabled(),
            );
        });
        $this->app->scoped(PrivilegedTotpFactorEpochRepository::class, function ($app): PrivilegedTotpFactorEpochRepository {
            return new LaravelPrivilegedTotpFactorEpochRepository(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                $this->totpRecoveryEnabled() || $this->sessionControlEnabled(),
            );
        });
        $this->app->scoped(PrivilegedTotpRecoveryRepository::class, function ($app): PrivilegedTotpRecoveryRepository {
            return new LaravelPrivilegedTotpRecoveryRepository(
                $this->connection($app),
                $app->make(Encrypter::class),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                $this->totpRecoveryEnabled(),
            );
        });

        $this->app->scoped(FirstPartyIdentityEligibilityVerifier::class, function ($app): FirstPartyIdentityEligibilityVerifier {
            return new LaravelFirstPartyIdentityEligibilityVerifier(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                $this->sessionControlEnabled(),
            );
        });
        $this->app->scoped(FirstPartyIdentityEligibilityAdministrationRepository::class, function ($app): FirstPartyIdentityEligibilityAdministrationRepository {
            return new LaravelFirstPartyIdentityEligibilityAdministrationRepository(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
            );
        });
        $this->app->scoped(FirstPartyIdentityDisablementSessionTerminationRepository::class, function ($app): FirstPartyIdentityDisablementSessionTerminationRepository {
            return new LaravelFirstPartyIdentityDisablementSessionTerminationRepository(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                $this->sessionControlEnabled(),
            );
        });
        $this->app->scoped(FirstPartyIdentityEligibilityAdministrationService::class, fn ($app): FirstPartyIdentityEligibilityAdministrationService => new FirstPartyIdentityEligibilityAdministrationService(
            $app->make(FirstPartyIdentityEligibilityAdministrationRepository::class),
            $app->make(FirstPartyIdentityDisablementSessionTerminationRepository::class),
            $app->make(PersistenceTransaction::class),
            $app->make(PolicyAdministrationClock::class),
        ));
        $this->app->scoped(FirstPartySessionAuthorityRepository::class, function ($app): FirstPartySessionAuthorityRepository {
            return new LaravelFirstPartySessionAuthorityRepository(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                $this->sessionControlEnabled(),
            );
        });
        $this->app->scoped(FirstPartySessionAuthorityService::class, fn ($app): FirstPartySessionAuthorityService => new FirstPartySessionAuthorityService(
            $app->make(FirstPartySessionAuthorityRepository::class),
            $app->make(FirstPartySessionAuthorityClock::class),
            $app->make(FirstPartyCredentialEpochRepository::class),
            $app->make(PrivilegedTotpFactorEpochRepository::class),
            $app->make(PrivilegedTotpMfaService::class),
            $this->mfaOperationalEnabled(),
            (int) config('oneqay.session_control.idle_ttl_seconds', 0),
            (int) config('oneqay.session_control.absolute_ttl_seconds', 0),
        ));

        $this->app->scoped(DurablePosSaleRepository::class, function ($app): DurablePosSaleRepository {
            return new LaravelDurablePosSaleRepository(
                $this->connection($app),
                $this->persistenceEnabled(),
                $this->runtimeClass(),
                (bool) config('oneqay.pos_sale_completion.enabled', false),
            );
        });
        $this->app->scoped(CompleteSale::class, fn ($app): CompleteSale => new CompleteSale(
            $app->make(DurablePosSaleRepository::class),
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
            $app->make(PersistenceTransaction::class),
            $app->make(PosSaleClock::class),
        ));

        $this->app->scoped(PrivilegedTotpEngine::class, static fn (): PrivilegedTotpEngine => new OtphpPrivilegedTotpEngine());
        $this->app->scoped(PrivilegedTotpClock::class, static fn (): PrivilegedTotpClock => new class implements PrivilegedTotpClock { public function nowUnix(): int { return time(); } });
        $this->app->scoped(PrivilegedTotpRecoveryClock::class, static fn (): PrivilegedTotpRecoveryClock => new class implements PrivilegedTotpRecoveryClock { public function nowUnix(): int { return time(); } });
        $this->app->scoped(PrivilegedStepUpClock::class, static fn (): PrivilegedStepUpClock => new class implements PrivilegedStepUpClock { public function nowUnix(): int { return time(); } });
        $this->app->scoped(RecoveryCodeClock::class, static fn (): RecoveryCodeClock => new class implements RecoveryCodeClock { public function nowUnix(): int { return time(); } });
        $this->app->scoped(AuthenticatedPasswordChangeClock::class, static fn (): AuthenticatedPasswordChangeClock => new class implements AuthenticatedPasswordChangeClock { public function nowUnix(): int { return time(); } });
        $this->app->scoped(PolicyAdministrationClock::class, static fn (): PolicyAdministrationClock => new class implements PolicyAdministrationClock { public function nowUnix(): int { return time(); } });
        $this->app->scoped(FirstPartySessionAuthorityClock::class, static fn (): FirstPartySessionAuthorityClock => new class implements FirstPartySessionAuthorityClock { public function nowUnix(): int { return time(); } });
        $this->app->scoped(PosSaleClock::class, static fn (): PosSaleClock => new class implements PosSaleClock { public function nowUnix(): int { return time(); } });

        $this->app->scoped(PersistenceTransaction::class, function ($app): PersistenceTransaction {
            return new LaravelPersistenceTransaction($this->connection($app), $this->persistenceEnabled(), $this->runtimeClass());
        });
    }

    public function boot(): void
    {
        $technicalPreviewEnabled = filter_var(env('ONEQAY_TECHNICAL_PREVIEW_ENABLED', false), FILTER_VALIDATE_BOOL);
        if ($technicalPreviewEnabled) {
            return;
        }

        // TechnicalPreviewServiceProvider is registered after this provider and owns the
        // synthetic verifier pair only while Technical Preview is explicitly armed.
        // Restore durable authentication verification for normal Local/Test/CI runtime.
        $this->app->scoped(TenantMembershipVerifier::class, fn ($app): TenantMembershipVerifier => new LaravelTenantMembershipVerifier($app->make(DurableOrganizationalAccessRepository::class)));
        $this->app->scoped(OrganizationalRelationshipVerifier::class, fn ($app): OrganizationalRelationshipVerifier => new LaravelOrganizationalRelationshipVerifier($app->make(DurableOrganizationalAccessRepository::class)));
    }

    private function connection($app): Connection
    {
        /** @var Connection $connection */
        $connection = $app->make('db')->connection();
        return $connection;
    }

    private function persistenceEnabled(): bool
    {
        return (bool) config('database.oneqay_persistence_enabled', false);
    }

    private function runtimeClass(): string
    {
        return (string) config('oneqay.runtime_class', '');
    }

    private function totpRecoveryEnabled(): bool
    {
        return (bool) config('oneqay.authentication_recovery.enabled', false)
            && (bool) config('oneqay.privileged_totp_mfa.enabled', false);
    }

    private function sessionControlEnabled(): bool
    {
        return (bool) config('oneqay.session_control.enabled', false)
            && (int) config('oneqay.session_control.idle_ttl_seconds', 0) === 7200
            && (int) config('oneqay.session_control.absolute_ttl_seconds', 0) === 43200;
    }

    private function mfaOperationalEnabled(): bool
    {
        return (bool) config('oneqay.privileged_totp_mfa.enabled', false)
            || $this->sessionControlEnabled();
    }
}
