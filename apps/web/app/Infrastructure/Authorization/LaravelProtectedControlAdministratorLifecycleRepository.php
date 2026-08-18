<?php

declare(strict_types=1);

namespace App\Infrastructure\Authorization;

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\ProtectedControlAdministratorLifecycleRepository;
use App\Application\Authorization\ProtectedControlAdministratorLifecycleViolation;
use App\Application\Authorization\ProtectedControlAdministratorMutation;
use App\Application\Authorization\ProtectedControlAdministratorOperation;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelProtectedControlAdministratorLifecycleRepository implements ProtectedControlAdministratorLifecycleRepository
{
    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
    ) {}

    public function hasTenantControlAuthority(VerifiedOrganizationalContext $actor): bool
    {
        $this->assertRuntimeAllowedForLifecycle();

        try {
            return $this->tenantControlAuthorityExists(
                $actor->tenantId()->value(),
                $actor->identityId()->value(),
            );
        } catch (Throwable) {
            $this->lifecycleStorageFailure();
        }
    }

    public function assertTargetEligible(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
    ): void {
        $this->assertRuntimeAllowedForLifecycle();

        try {
            if (! $this->identityExists($actor->tenantId()->value(), $targetIdentityId->value())) {
                throw new ProtectedControlAdministratorLifecycleViolation(
                    ProtectedControlAdministratorLifecycleViolation::TENANT_RELATIONSHIP_DENIED,
                    'Protected control lifecycle target is not eligible for the requested tenant.',
                );
            }
        } catch (ProtectedControlAdministratorLifecycleViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->lifecycleStorageFailure();
        }
    }

    public function assertProtectedRoleState(VerifiedOrganizationalContext $actor): void
    {
        $this->assertRuntimeAllowedForLifecycle();

        try {
            if (! $this->protectedRoleStateCompatible($actor->tenantId()->value())) {
                throw new ProtectedControlAdministratorLifecycleViolation(
                    ProtectedControlAdministratorLifecycleViolation::PROTECTED_ROLE_STATE_CONFLICT,
                    'Protected control administrator role state is incompatible.',
                );
            }
        } catch (ProtectedControlAdministratorLifecycleViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->lifecycleStorageFailure();
        }
    }

    public function replayOutcome(
        VerifiedOrganizationalContext $actor,
        ProtectedControlAdministratorMutation $mutation,
    ): ?string {
        $this->assertRuntimeAllowedForLifecycle();

        try {
            $row = $this->connection->table('oneqay_protected_control_admin_mutations')
                ->where('tenant_id', $actor->tenantId()->value())
                ->where('mutation_id', $mutation->mutationId()->value())
                ->first();

            if ($row === null) {
                return null;
            }

            $expected = $this->fingerprint($actor, $mutation);
            if (! is_string($row->payload_fingerprint ?? null) || ! hash_equals($row->payload_fingerprint, $expected)) {
                throw new ProtectedControlAdministratorLifecycleViolation(
                    ProtectedControlAdministratorLifecycleViolation::MUTATION_CONFLICT,
                    'Protected control lifecycle mutation identifier is bound to a different payload.',
                );
            }

            $outcome = is_string($row->outcome ?? null) ? $row->outcome : '';
            if (! in_array($outcome, [self::OUTCOME_APPLIED, self::OUTCOME_NO_CHANGE], true)) {
                $this->lifecycleStorageFailure();
            }

            return $outcome;
        } catch (ProtectedControlAdministratorLifecycleViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->lifecycleStorageFailure();
        }
    }

    public function assertOperationAllowed(
        VerifiedOrganizationalContext $actor,
        ProtectedControlAdministratorMutation $mutation,
    ): void {
        $this->assertRuntimeAllowedForLifecycle();

        if (! $mutation->operation()->isRevoke()) {
            return;
        }

        try {
            $tenant = $actor->tenantId()->value();
            $target = $mutation->targetIdentityId()->value();
            if ($this->exactControlAssignmentExists($tenant, $target) && $this->tenantControlPrincipalCount($tenant) <= 1) {
                throw new ProtectedControlAdministratorLifecycleViolation(
                    ProtectedControlAdministratorLifecycleViolation::LAST_CONTROL_PRINCIPAL,
                    'The final tenant-scoped control principal cannot be revoked.',
                );
            }
        } catch (ProtectedControlAdministratorLifecycleViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->lifecycleStorageFailure();
        }
    }

    public function applyFresh(
        VerifiedOrganizationalContext $actor,
        ProtectedControlAdministratorMutation $mutation,
        int $occurredAtUnix,
    ): string {
        $this->assertRuntimeAllowedForPersistence();
        if ($occurredAtUnix <= 0) {
            $this->relationshipConflict();
        }

        try {
            $tenant = $actor->tenantId()->value();
            $actorIdentity = $actor->identityId()->value();
            $target = $mutation->targetIdentityId()->value();

            if (! $this->tenantControlAuthorityExists($tenant, $actorIdentity)) {
                $this->relationshipConflict();
            }
            if (! $this->identityExists($tenant, $target)) {
                $this->relationshipConflict();
            }
            if (! $this->protectedRoleStateCompatible($tenant)) {
                $this->relationshipConflict();
            }

            $existing = $this->connection->table('oneqay_protected_control_admin_mutations')
                ->where('tenant_id', $tenant)
                ->where('mutation_id', $mutation->mutationId()->value())
                ->first();
            if ($existing !== null) {
                $expected = $this->fingerprint($actor, $mutation);
                if (
                    is_string($existing->payload_fingerprint ?? null)
                    && hash_equals($existing->payload_fingerprint, $expected)
                    && is_string($existing->outcome ?? null)
                    && in_array($existing->outcome, [self::OUTCOME_APPLIED, self::OUTCOME_NO_CHANGE], true)
                ) {
                    return $existing->outcome;
                }
                $this->relationshipConflict();
            }

            $outcome = self::OUTCOME_NO_CHANGE;
            if ($mutation->operation()->isDelegate()) {
                $inserted = $this->connection->table('oneqay_tenant_role_assignments')->insertOrIgnore([
                    'tenant_id' => $tenant,
                    'identity_id' => $target,
                    'role_id' => self::CONTROL_ROLE,
                ]);
                $outcome = $inserted === 1 ? self::OUTCOME_APPLIED : self::OUTCOME_NO_CHANGE;

                if (! $this->exactControlAssignmentExists($tenant, $target)) {
                    $this->relationshipConflict();
                }
            } elseif ($mutation->operation()->isRevoke()) {
                if ($this->exactControlAssignmentExists($tenant, $target)) {
                    if ($this->tenantControlPrincipalCount($tenant) <= 1) {
                        $this->relationshipConflict();
                    }

                    $deleted = $this->connection->table('oneqay_tenant_role_assignments')
                        ->where('tenant_id', $tenant)
                        ->where('identity_id', $target)
                        ->where('role_id', self::CONTROL_ROLE)
                        ->delete();
                    if ($deleted !== 1) {
                        $this->relationshipConflict();
                    }
                    $outcome = self::OUTCOME_APPLIED;
                }
            } else {
                $this->relationshipConflict();
            }

            $insertedJournal = $this->connection->table('oneqay_protected_control_admin_mutations')->insertOrIgnore([
                'tenant_id' => $tenant,
                'mutation_id' => $mutation->mutationId()->value(),
                'actor_identity_id' => $actorIdentity,
                'operation' => $mutation->operation()->value(),
                'target_identity_id' => $target,
                'role_id' => self::CONTROL_ROLE,
                'permission_id' => self::CONTROL_PERMISSION,
                'payload_fingerprint' => $this->fingerprint($actor, $mutation),
                'outcome' => $outcome,
                'occurred_at_unix' => $occurredAtUnix,
            ]);
            if ($insertedJournal !== 1) {
                $this->relationshipConflict();
            }

            if (! $this->protectedRoleStateCompatible($tenant)) {
                $this->relationshipConflict();
            }
            if ($mutation->operation()->isDelegate() && ! $this->exactControlAssignmentExists($tenant, $target)) {
                $this->relationshipConflict();
            }
            if ($mutation->operation()->isRevoke() && $outcome === self::OUTCOME_APPLIED && $this->exactControlAssignmentExists($tenant, $target)) {
                $this->relationshipConflict();
            }
            if ($this->tenantControlPrincipalCount($tenant) < 1) {
                $this->relationshipConflict();
            }

            return $outcome;
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->persistenceStorageFailure();
        }
    }

    private function tenantControlAuthorityExists(string $tenantId, string $identityId): bool
    {
        return $this->connection->table('oneqay_tenant_role_assignments as a')
            ->join('oneqay_role_permissions as p', function ($join): void {
                $join->on('p.tenant_id', '=', 'a.tenant_id')->on('p.role_id', '=', 'a.role_id');
            })
            ->where('a.tenant_id', $tenantId)
            ->where('a.identity_id', $identityId)
            ->where('p.permission_id', AdministrationPermission::MANAGE)
            ->exists();
    }

    private function identityExists(string $tenantId, string $identityId): bool
    {
        return $this->connection->table('oneqay_identities')
            ->where('tenant_id', $tenantId)
            ->where('id', $identityId)
            ->exists();
    }

    private function exactControlAssignmentExists(string $tenantId, string $identityId): bool
    {
        return $this->connection->table('oneqay_tenant_role_assignments')
            ->where('tenant_id', $tenantId)
            ->where('identity_id', $identityId)
            ->where('role_id', self::CONTROL_ROLE)
            ->exists();
    }

    private function tenantControlPrincipalCount(string $tenantId): int
    {
        return (int) $this->connection->table('oneqay_tenant_role_assignments as a')
            ->join('oneqay_role_permissions as p', function ($join): void {
                $join->on('p.tenant_id', '=', 'a.tenant_id')->on('p.role_id', '=', 'a.role_id');
            })
            ->where('a.tenant_id', $tenantId)
            ->where('p.permission_id', AdministrationPermission::MANAGE)
            ->distinct()
            ->count('a.identity_id');
    }

    private function protectedRoleStateCompatible(string $tenantId): bool
    {
        if (! $this->connection->table('oneqay_roles')
            ->where('tenant_id', $tenantId)
            ->where('id', self::CONTROL_ROLE)
            ->exists()) {
            return false;
        }

        $permissions = $this->connection->table('oneqay_role_permissions')
            ->where('tenant_id', $tenantId)
            ->where('role_id', self::CONTROL_ROLE)
            ->get();
        if (
            $permissions->count() !== 1
            || ! is_string($permissions->first()->permission_id ?? null)
            || ! hash_equals($permissions->first()->permission_id, self::CONTROL_PERMISSION)
        ) {
            return false;
        }

        return ! $this->connection->table('oneqay_role_permissions')
            ->where('tenant_id', $tenantId)
            ->where('permission_id', self::CONTROL_PERMISSION)
            ->where('role_id', '!=', self::CONTROL_ROLE)
            ->exists();
    }

    private function fingerprint(
        VerifiedOrganizationalContext $actor,
        ProtectedControlAdministratorMutation $mutation,
    ): string {
        return hash('sha256', implode("\n", [
            $actor->tenantId()->value(),
            $actor->identityId()->value(),
            $mutation->operation()->value(),
            $mutation->targetIdentityId()->value(),
            self::CONTROL_ROLE,
            self::CONTROL_PERMISSION,
            'tenant',
        ]));
    }

    private function assertRuntimeAllowedForLifecycle(): void
    {
        if (! $this->enabled) {
            throw new ProtectedControlAdministratorLifecycleViolation(
                ProtectedControlAdministratorLifecycleViolation::PERSISTENCE_DISABLED,
                'Protected control administrator lifecycle persistence is disabled.',
            );
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new ProtectedControlAdministratorLifecycleViolation(
                ProtectedControlAdministratorLifecycleViolation::RUNTIME_DENIED,
                'Protected control administrator lifecycle runtime is not authorized.',
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

    private function lifecycleStorageFailure(): never
    {
        throw new ProtectedControlAdministratorLifecycleViolation(
            ProtectedControlAdministratorLifecycleViolation::STORAGE_FAILURE,
            'Protected control administrator lifecycle storage operation failed.',
        );
    }

    private function relationshipConflict(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::RELATIONSHIP_CONFLICT,
            'Protected control administrator lifecycle relationship is invalid.',
        );
    }

    private function persistenceStorageFailure(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::STORAGE_FAILURE,
            'Protected control administrator lifecycle storage operation failed.',
        );
    }
}
