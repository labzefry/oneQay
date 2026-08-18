<?php

declare(strict_types=1);

namespace App\Infrastructure\Authorization;

use App\Application\Authorization\InitialTenantAdministratorProvisioningAuthority;
use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\InitialTenantAdministratorProvisioningViolation;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelInitialTenantAdministratorProvisioningRepository implements InitialTenantAdministratorProvisioningRepository
{
    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
    ) {}

    public function assertTargetEligible(TenantId $tenantId, PlatformIdentityId $identityId): void
    {
        $this->assertRuntimeAllowedForProvisioning();

        try {
            if (! $this->identityExists($tenantId, $identityId)) {
                throw new InitialTenantAdministratorProvisioningViolation(
                    InitialTenantAdministratorProvisioningViolation::TENANT_RELATIONSHIP_DENIED,
                    'Initial administrator identity is not eligible for the requested tenant.',
                );
            }
        } catch (InitialTenantAdministratorProvisioningViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->provisioningStorageFailure();
        }
    }

    public function replayOutcome(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): ?string {
        $this->assertRuntimeAllowedForProvisioning();

        try {
            $row = $this->journalRow($tenantId);
            if ($row === null) {
                return null;
            }

            if (! is_string($row->provisioning_id ?? null) || ! hash_equals($row->provisioning_id, $provisioningId->value())) {
                throw new InitialTenantAdministratorProvisioningViolation(
                    InitialTenantAdministratorProvisioningViolation::ALREADY_INITIALIZED,
                    'Tenant initial administrator provisioning has already completed.',
                );
            }

            $expected = $this->fingerprint($tenantId, $identityId, $provisioningId);
            if (! is_string($row->payload_fingerprint ?? null) || ! hash_equals($row->payload_fingerprint, $expected)) {
                throw new InitialTenantAdministratorProvisioningViolation(
                    InitialTenantAdministratorProvisioningViolation::PROVISIONING_CONFLICT,
                    'Provisioning identifier is already bound to a different payload.',
                );
            }

            if (! is_string($row->outcome ?? null) || ! hash_equals($row->outcome, self::OUTCOME_APPLIED)) {
                $this->provisioningStorageFailure();
            }

            return self::OUTCOME_APPLIED;
        } catch (InitialTenantAdministratorProvisioningViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->provisioningStorageFailure();
        }
    }

    public function assertUninitialized(TenantId $tenantId): void
    {
        $this->assertRuntimeAllowedForProvisioning();

        try {
            if ($this->journalRow($tenantId) !== null || $this->hasAnyControlRole($tenantId)) {
                throw new InitialTenantAdministratorProvisioningViolation(
                    InitialTenantAdministratorProvisioningViolation::ALREADY_INITIALIZED,
                    'Tenant initial administrator provisioning has already completed.',
                );
            }

            if (! $this->controlRoleStateCompatible($tenantId)) {
                throw new InitialTenantAdministratorProvisioningViolation(
                    InitialTenantAdministratorProvisioningViolation::ROLE_STATE_CONFLICT,
                    'Initial administrator control role state is incompatible.',
                );
            }
        } catch (InitialTenantAdministratorProvisioningViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->provisioningStorageFailure();
        }
    }

    public function applyFresh(
        InitialTenantAdministratorProvisioningAuthority $authority,
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        InitialTenantAdministratorProvisioningId $provisioningId,
        int $occurredAtUnix,
    ): string {
        $this->assertRuntimeAllowedForPersistence();
        if ($occurredAtUnix <= 0) {
            $this->relationshipConflict();
        }

        try {
            if (! $authority->authorizes($tenantId, $identityId, $provisioningId)) {
                $this->relationshipConflict();
            }

            if (! $this->identityExists($tenantId, $identityId)) {
                $this->relationshipConflict();
            }

            $existing = $this->journalRow($tenantId);
            if ($existing !== null) {
                $expected = $this->fingerprint($tenantId, $identityId, $provisioningId);
                if (
                    is_string($existing->provisioning_id ?? null)
                    && hash_equals($existing->provisioning_id, $provisioningId->value())
                    && is_string($existing->payload_fingerprint ?? null)
                    && hash_equals($existing->payload_fingerprint, $expected)
                    && is_string($existing->outcome ?? null)
                    && hash_equals($existing->outcome, self::OUTCOME_APPLIED)
                ) {
                    return self::OUTCOME_APPLIED;
                }
                $this->relationshipConflict();
            }

            if ($this->hasAnyControlRole($tenantId) || ! $this->controlRoleStateCompatible($tenantId)) {
                $this->relationshipConflict();
            }

            $tenant = $tenantId->value();
            $identity = $identityId->value();

            $this->connection->table('oneqay_roles')->insertOrIgnore([
                'tenant_id' => $tenant,
                'id' => self::CONTROL_ROLE,
            ]);

            if (! $this->connection->table('oneqay_roles')
                ->where('tenant_id', $tenant)->where('id', self::CONTROL_ROLE)->exists()) {
                $this->relationshipConflict();
            }

            $this->connection->table('oneqay_role_permissions')->insertOrIgnore([
                'tenant_id' => $tenant,
                'role_id' => self::CONTROL_ROLE,
                'permission_id' => self::CONTROL_PERMISSION,
            ]);

            $this->connection->table('oneqay_tenant_role_assignments')->insertOrIgnore([
                'tenant_id' => $tenant,
                'identity_id' => $identity,
                'role_id' => self::CONTROL_ROLE,
            ]);

            $insertedJournal = $this->connection->table('oneqay_initial_tenant_admin_provisionings')->insertOrIgnore([
                'tenant_id' => $tenant,
                'provisioning_id' => $provisioningId->value(),
                'identity_id' => $identity,
                'role_id' => self::CONTROL_ROLE,
                'permission_id' => self::CONTROL_PERMISSION,
                'payload_fingerprint' => $this->fingerprint($tenantId, $identityId, $provisioningId),
                'outcome' => self::OUTCOME_APPLIED,
                'occurred_at_unix' => $occurredAtUnix,
            ]);

            if ($insertedJournal !== 1) {
                $this->relationshipConflict();
            }

            $this->assertDesiredState($tenantId, $identityId);

            return self::OUTCOME_APPLIED;
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->persistenceStorageFailure();
        }
    }

    private function identityExists(TenantId $tenantId, PlatformIdentityId $identityId): bool
    {
        return $this->connection->table('oneqay_identities')
            ->where('tenant_id', $tenantId->value())
            ->where('id', $identityId->value())
            ->exists();
    }

    private function journalRow(TenantId $tenantId): ?object
    {
        return $this->connection->table('oneqay_initial_tenant_admin_provisionings')
            ->where('tenant_id', $tenantId->value())
            ->first();
    }

    private function hasAnyControlRole(TenantId $tenantId): bool
    {
        return $this->connection->table('oneqay_role_permissions')
            ->where('tenant_id', $tenantId->value())
            ->where('permission_id', self::CONTROL_PERMISSION)
            ->exists();
    }

    private function controlRoleStateCompatible(TenantId $tenantId): bool
    {
        $tenant = $tenantId->value();
        $roleExists = $this->connection->table('oneqay_roles')
            ->where('tenant_id', $tenant)
            ->where('id', self::CONTROL_ROLE)
            ->exists();

        if (! $roleExists) {
            return true;
        }

        if ($this->connection->table('oneqay_role_permissions')
            ->where('tenant_id', $tenant)->where('role_id', self::CONTROL_ROLE)->exists()) {
            return false;
        }

        foreach ([
            'oneqay_tenant_role_assignments',
            'oneqay_organization_role_assignments',
            'oneqay_outlet_role_assignments',
            'oneqay_device_role_assignments',
        ] as $table) {
            if ($this->connection->table($table)
                ->where('tenant_id', $tenant)->where('role_id', self::CONTROL_ROLE)->exists()) {
                return false;
            }
        }

        return true;
    }

    private function assertDesiredState(TenantId $tenantId, PlatformIdentityId $identityId): void
    {
        $tenant = $tenantId->value();
        $identity = $identityId->value();

        $permissionRows = $this->connection->table('oneqay_role_permissions')
            ->where('tenant_id', $tenant)->where('role_id', self::CONTROL_ROLE)->get();
        if (
            $permissionRows->count() !== 1
            || ! is_string($permissionRows->first()->permission_id ?? null)
            || ! hash_equals($permissionRows->first()->permission_id, self::CONTROL_PERMISSION)
        ) {
            $this->relationshipConflict();
        }

        $tenantAssignments = $this->connection->table('oneqay_tenant_role_assignments')
            ->where('tenant_id', $tenant)->where('role_id', self::CONTROL_ROLE)->get();
        if (
            $tenantAssignments->count() !== 1
            || ! is_string($tenantAssignments->first()->identity_id ?? null)
            || ! hash_equals($tenantAssignments->first()->identity_id, $identity)
        ) {
            $this->relationshipConflict();
        }

        foreach ([
            'oneqay_organization_role_assignments',
            'oneqay_outlet_role_assignments',
            'oneqay_device_role_assignments',
        ] as $table) {
            if ($this->connection->table($table)
                ->where('tenant_id', $tenant)->where('role_id', self::CONTROL_ROLE)->exists()) {
                $this->relationshipConflict();
            }
        }
    }

    private function fingerprint(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): string {
        return hash('sha256', implode("\n", [
            $tenantId->value(),
            $identityId->value(),
            $provisioningId->value(),
            self::CONTROL_ROLE,
            self::CONTROL_PERMISSION,
            'tenant',
        ]));
    }

    private function assertRuntimeAllowedForProvisioning(): void
    {
        if (! $this->enabled) {
            throw new InitialTenantAdministratorProvisioningViolation(
                InitialTenantAdministratorProvisioningViolation::PERSISTENCE_DISABLED,
                'Initial tenant administrator provisioning persistence is disabled.',
            );
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new InitialTenantAdministratorProvisioningViolation(
                InitialTenantAdministratorProvisioningViolation::RUNTIME_DENIED,
                'Initial tenant administrator provisioning runtime is not authorized.',
            );
        }
    }

    private function assertRuntimeAllowedForPersistence(): void
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
                'Durable persistence runtime is not authorized.',
            );
        }
    }

    private function provisioningStorageFailure(): never
    {
        throw new InitialTenantAdministratorProvisioningViolation(
            InitialTenantAdministratorProvisioningViolation::STORAGE_FAILURE,
            'Initial tenant administrator provisioning storage operation failed.',
        );
    }

    private function relationshipConflict(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::RELATIONSHIP_CONFLICT,
            'Initial tenant administrator provisioning relationship is invalid.',
        );
    }

    private function persistenceStorageFailure(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::STORAGE_FAILURE,
            'Initial tenant administrator provisioning storage operation failed.',
        );
    }
}
