<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapRepository;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapViolation;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelFirstControlPrincipalCredentialBootstrapRepository implements FirstControlPrincipalCredentialBootstrapRepository
{
    private const CONTROL_ROLE = InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE;
    private const CONTROL_PERMISSION = AdministrationPermission::MANAGE;
    private const PROVISIONING_TABLE = 'oneqay_initial_tenant_admin_provisionings';
    private const ROLE_TABLE = 'oneqay_roles';
    private const ROLE_PERMISSION_TABLE = 'oneqay_role_permissions';
    private const ASSIGNMENT_TABLE = 'oneqay_tenant_role_assignments';
    private const IDENTITY_TABLE = 'oneqay_identities';
    private const CREDENTIAL_TABLE = 'oneqay_identity_password_credentials';
    private const ENROLLMENT_TABLE = 'oneqay_initial_password_enrollments';

    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
        private bool $bootstrapEnabled,
    ) {}

    public function assertEligible(TenantId $tenantId): void
    {
        $this->assertRuntimeAllowedForBootstrap();

        try {
            $this->resolveEligibleTarget($tenantId->value());
        } catch (FirstControlPrincipalCredentialBootstrapViolation|DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->persistenceStorageFailure();
        }
    }

    public function bootstrapFresh(
        TenantId $tenantId,
        #[\SensitiveParameter] string $password,
    ): string {
        try {
            // Repeat runtime/feature and durable eligibility inside the transaction.
            // A stale/racing eligibility change is intentionally collapsed to a
            // generic relationship conflict by this method.
            $this->assertRuntimeAllowedForBootstrap();
            $tenant = $tenantId->value();
            $target = $this->resolveEligibleTarget($tenant);
            $passwordHash = $this->hashPassword($password);

            $inserted = $this->connection->table(self::CREDENTIAL_TABLE)->insert([
                'tenant_id' => $tenant,
                'identity_id' => $target,
                'password_hash' => $passwordHash,
            ]);

            if ($inserted !== true) {
                $this->relationshipConflict();
            }

            return self::OUTCOME_APPLIED;
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (FirstControlPrincipalCredentialBootstrapViolation) {
            $this->relationshipConflict();
        } catch (Throwable) {
            $this->persistenceStorageFailure();
        }
    }

    private function resolveEligibleTarget(string $tenantId): string
    {
        $target = $this->resolveInitialControlPrincipal($tenantId);

        if (! $this->identityExists($tenantId, $target)
            || ! $this->protectedRoleStateCompatible($tenantId)
            || ! $this->targetStillOwnsProtectedControlAssignment($tenantId, $target)) {
            $this->ineligible();
        }

        if ($this->credentialExists($tenantId, $target)) {
            $this->fail(
                FirstControlPrincipalCredentialBootstrapViolation::CREDENTIAL_ALREADY_EXISTS,
                'First control principal already has a password credential.',
            );
        }

        if ($this->activeInitialPasswordEnrollmentExists($tenantId, $target)) {
            $this->fail(
                FirstControlPrincipalCredentialBootstrapViolation::ACTIVE_ENROLLMENT_EXISTS,
                'First control principal has an active initial password enrollment.',
            );
        }

        return $target;
    }

    private function resolveInitialControlPrincipal(string $tenantId): string
    {
        $row = $this->connection->table(self::PROVISIONING_TABLE)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($row === null
            || ! is_string($row->identity_id ?? null)
            || $row->identity_id === ''
            || ! is_string($row->role_id ?? null)
            || ! hash_equals($row->role_id, self::CONTROL_ROLE)
            || ! is_string($row->permission_id ?? null)
            || ! hash_equals($row->permission_id, self::CONTROL_PERMISSION)
            || ! is_string($row->outcome ?? null)
            || ! hash_equals($row->outcome, self::OUTCOME_APPLIED)) {
            $this->ineligible();
        }

        return $row->identity_id;
    }

    private function protectedRoleStateCompatible(string $tenantId): bool
    {
        if (! $this->connection->table(self::ROLE_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('id', self::CONTROL_ROLE)
            ->exists()) {
            return false;
        }

        $permissions = $this->connection->table(self::ROLE_PERMISSION_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('role_id', self::CONTROL_ROLE)
            ->get();

        if ($permissions->count() !== 1
            || ! is_string($permissions->first()->permission_id ?? null)
            || ! hash_equals($permissions->first()->permission_id, self::CONTROL_PERMISSION)) {
            return false;
        }

        return ! $this->connection->table(self::ROLE_PERMISSION_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('permission_id', self::CONTROL_PERMISSION)
            ->where('role_id', '!=', self::CONTROL_ROLE)
            ->exists();
    }

    private function targetStillOwnsProtectedControlAssignment(string $tenantId, string $identityId): bool
    {
        return $this->connection->table(self::ASSIGNMENT_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('identity_id', $identityId)
            ->where('role_id', self::CONTROL_ROLE)
            ->exists();
    }

    private function identityExists(string $tenantId, string $identityId): bool
    {
        return $this->connection->table(self::IDENTITY_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('id', $identityId)
            ->exists();
    }

    private function credentialExists(string $tenantId, string $identityId): bool
    {
        return $this->connection->table(self::CREDENTIAL_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('identity_id', $identityId)
            ->exists();
    }

    private function activeInitialPasswordEnrollmentExists(string $tenantId, string $identityId): bool
    {
        return $this->connection->table(self::ENROLLMENT_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('target_identity_id', $identityId)
            ->where('active_marker', 1)
            ->whereNull('consumed_at_unix')
            ->exists();
    }

    private function hashPassword(#[\SensitiveParameter] string $password): string
    {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
        } catch (Throwable) {
            $this->fail(
                FirstControlPrincipalCredentialBootstrapViolation::INVALID_PASSWORD,
                'First control principal bootstrap password could not be hashed.',
            );
        }

        if (! is_string($hash) || $hash === '' || strlen($hash) > 255) {
            $this->fail(
                FirstControlPrincipalCredentialBootstrapViolation::INVALID_PASSWORD,
                'First control principal bootstrap password could not be hashed.',
            );
        }

        return $hash;
    }

    private function assertRuntimeAllowedForBootstrap(): void
    {
        if (! $this->enabled) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::PERSISTENCE_DISABLED,
                'Durable persistence is disabled.',
            );
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::RUNTIME_DENIED,
                'First control principal bootstrap runtime is not authorized.',
            );
        }

        if (! $this->bootstrapEnabled) {
            $this->fail(
                FirstControlPrincipalCredentialBootstrapViolation::FEATURE_DISABLED,
                'First control principal credential bootstrap is disabled.',
            );
        }
    }

    private function ineligible(): never
    {
        $this->fail(
            FirstControlPrincipalCredentialBootstrapViolation::BOOTSTRAP_INELIGIBLE,
            'First control principal credential bootstrap is not eligible.',
        );
    }

    private function relationshipConflict(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::RELATIONSHIP_CONFLICT,
            'First control principal credential bootstrap durable relationship conflict.',
        );
    }

    private function persistenceStorageFailure(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::STORAGE_FAILURE,
            'First control principal credential bootstrap storage operation failed.',
        );
    }

    private function fail(string $code, string $message): never
    {
        throw new FirstControlPrincipalCredentialBootstrapViolation($code, $message);
    }
}
