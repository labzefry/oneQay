<?php

declare(strict_types=1);

namespace App\Infrastructure\Authorization;

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\DurablePolicyAdministrationRepository;
use App\Application\Authorization\DurablePolicyAdministrationViolation;
use App\Application\Authorization\DurablePolicyMutation;
use App\Application\Authorization\PolicyMutationOperation;
use App\Application\Authorization\RoleIdentifier;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurablePersistenceViolation;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelDurablePolicyAdministrationRepository implements DurablePolicyAdministrationRepository
{
    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
    ) {}

    public function replayOutcome(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): ?string
    {
        $this->assertRuntimeAllowedForAdministration();

        try {
            $row = $this->journalQuery($actor, $mutation)->first();
            if ($row === null) { return null; }

            $fingerprint = $mutation->fingerprint($actor);
            if (! is_string($row->payload_fingerprint ?? null) || ! hash_equals($row->payload_fingerprint, $fingerprint)) {
                throw new DurablePolicyAdministrationViolation(
                    DurablePolicyAdministrationViolation::MUTATION_CONFLICT,
                    'Policy mutation identifier is already bound to a different payload.',
                );
            }

            $outcome = $row->outcome ?? null;
            if (! is_string($outcome) || ! in_array($outcome, ['applied', 'no_change'], true)) {
                throw new DurablePolicyAdministrationViolation(
                    DurablePolicyAdministrationViolation::STORAGE_FAILURE,
                    'Policy mutation journal data is invalid.',
                );
            }

            return $outcome;
        } catch (DurablePolicyAdministrationViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new DurablePolicyAdministrationViolation(
                DurablePolicyAdministrationViolation::STORAGE_FAILURE,
                'Policy administration storage operation failed.',
            );
        }
    }

    public function isProtectedControlRole(VerifiedOrganizationalContext $actor, RoleIdentifier $role): bool
    {
        $this->assertRuntimeAllowedForAdministration();

        try {
            return $this->protectedRoleExists($actor->tenantId()->value(), $role->value());
        } catch (Throwable) {
            throw new DurablePolicyAdministrationViolation(
                DurablePolicyAdministrationViolation::STORAGE_FAILURE,
                'Policy administration storage operation failed.',
            );
        }
    }

    public function assertTargetEligible(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): void
    {
        $this->assertRuntimeAllowedForAdministration();
        if (! $mutation->scope()->matchesActor($actor)) {
            throw new DurablePolicyAdministrationViolation(
                DurablePolicyAdministrationViolation::TARGET_SCOPE_INVALID,
                'Policy administration target scope is invalid.',
            );
        }

        try {
            if (! $this->targetEligible($actor, $mutation)) {
                throw new DurablePolicyAdministrationViolation(
                    DurablePolicyAdministrationViolation::TARGET_ACCESS_DENIED,
                    'Policy administration target membership or access is denied.',
                );
            }
        } catch (DurablePolicyAdministrationViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new DurablePolicyAdministrationViolation(
                DurablePolicyAdministrationViolation::STORAGE_FAILURE,
                'Policy administration storage operation failed.',
            );
        }
    }

    public function applyFresh(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation, int $occurredAtUnix): string
    {
        $this->assertRuntimeAllowedForPersistence();
        if ($occurredAtUnix <= 0 || ! $mutation->scope()->matchesActor($actor)) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::RELATIONSHIP_CONFLICT,
                'Policy mutation relationship is invalid.',
            );
        }

        $tenant = $actor->tenantId()->value();
        $role = $mutation->role()->value();
        $permission = $mutation->permission()?->value();

        try {
            if ($permission !== null && hash_equals(AdministrationPermission::MANAGE, $permission)) {
                $this->relationshipConflict();
            }

            if (($mutation->operation()->isPermissionMutation() || $mutation->operation()->isAssignmentMutation())
                && $this->protectedRoleExists($tenant, $role)) {
                $this->relationshipConflict();
            }

            if ($mutation->operation()->isAssignmentMutation() && ! $this->targetEligible($actor, $mutation)) {
                $this->relationshipConflict();
            }

            $fingerprint = $mutation->fingerprint($actor);
            $existingJournal = $this->journalQuery($actor, $mutation)->first();
            if ($existingJournal !== null) {
                if (! is_string($existingJournal->payload_fingerprint ?? null) || ! hash_equals($existingJournal->payload_fingerprint, $fingerprint)) {
                    $this->relationshipConflict();
                }
                $outcome = $existingJournal->outcome ?? null;
                if (! is_string($outcome) || ! in_array($outcome, ['applied', 'no_change'], true)) {
                    $this->storageFailure();
                }
                return $outcome;
            }

            $outcome = $this->plannedOutcome($actor, $mutation);
            $insertedJournal = $this->connection->table('oneqay_policy_mutations')->insertOrIgnore([
                'tenant_id' => $tenant,
                'mutation_id' => $mutation->mutationId()->value(),
                'actor_identity_id' => $actor->identityId()->value(),
                'operation' => $mutation->operation()->value(),
                'scope_type' => $mutation->scope()->type(),
                'organization_id' => $mutation->scope()->organizationId()?->value(),
                'outlet_id' => $mutation->scope()->outletId()?->value(),
                'device_id' => $mutation->scope()->deviceId()?->value(),
                'target_identity_id' => $mutation->targetIdentity()?->value(),
                'role_id' => $role,
                'permission_id' => $permission,
                'payload_fingerprint' => $fingerprint,
                'outcome' => $outcome,
                'occurred_at_unix' => $occurredAtUnix,
            ]);

            if ($insertedJournal !== 1) {
                $raced = $this->journalQuery($actor, $mutation)->first();
                if ($raced === null || ! is_string($raced->payload_fingerprint ?? null) || ! hash_equals($raced->payload_fingerprint, $fingerprint)) {
                    $this->relationshipConflict();
                }
                $racedOutcome = $raced->outcome ?? null;
                if (! is_string($racedOutcome) || ! in_array($racedOutcome, ['applied', 'no_change'], true)) {
                    $this->storageFailure();
                }
                return $racedOutcome;
            }

            if ($outcome === 'no_change') { return $outcome; }

            $this->performMutation($actor, $mutation);
            $this->assertDesiredState($actor, $mutation);

            return $outcome;
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->storageFailure();
        }
    }

    private function plannedOutcome(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): string
    {
        return $this->desiredStateExists($actor, $mutation) === ! $mutation->operation()->isRevocation()
            ? 'no_change'
            : 'applied';
    }

    private function performMutation(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): void
    {
        $tenant = $actor->tenantId()->value();
        $role = $mutation->role()->value();
        $operation = $mutation->operation()->value();

        if ($operation === PolicyMutationOperation::ROLE_CREATE) {
            $this->connection->table('oneqay_roles')->insertOrIgnore(['tenant_id' => $tenant, 'id' => $role]);
            return;
        }

        if ($operation === PolicyMutationOperation::PERMISSION_GRANT) {
            $this->requireRole($tenant, $role);
            $this->connection->table('oneqay_role_permissions')->insertOrIgnore([
                'tenant_id' => $tenant, 'role_id' => $role, 'permission_id' => $mutation->permission()?->value(),
            ]);
            return;
        }

        if ($operation === PolicyMutationOperation::PERMISSION_REVOKE) {
            $this->connection->table('oneqay_role_permissions')
                ->where('tenant_id', $tenant)->where('role_id', $role)
                ->where('permission_id', $mutation->permission()?->value())->delete();
            return;
        }

        $query = $this->assignmentQuery($actor, $mutation);
        if ($mutation->operation()->isRevocation()) {
            $query->delete();
            return;
        }

        $this->requireRole($tenant, $role);
        $this->connection->table($this->assignmentTable($mutation))->insertOrIgnore($this->assignmentValues($actor, $mutation));
    }

    private function desiredStateExists(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): bool
    {
        $tenant = $actor->tenantId()->value();
        $role = $mutation->role()->value();
        $operation = $mutation->operation()->value();

        if ($operation === PolicyMutationOperation::ROLE_CREATE) {
            return $this->connection->table('oneqay_roles')->where('tenant_id', $tenant)->where('id', $role)->exists();
        }
        if ($mutation->operation()->isPermissionMutation()) {
            return $this->connection->table('oneqay_role_permissions')->where('tenant_id', $tenant)->where('role_id', $role)
                ->where('permission_id', $mutation->permission()?->value())->exists();
        }
        return $this->assignmentQuery($actor, $mutation)->exists();
    }

    private function assertDesiredState(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): void
    {
        $exists = $this->desiredStateExists($actor, $mutation);
        $expectedExists = ! $mutation->operation()->isRevocation();
        if ($exists !== $expectedExists) { $this->relationshipConflict(); }
    }

    private function targetEligible(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): bool
    {
        if (! $mutation->operation()->isAssignmentMutation()) { return true; }
        $target = $mutation->targetIdentity()?->value();
        if ($target === null) { return false; }

        $tenant = $actor->tenantId()->value();
        return match ($mutation->scope()->type()) {
            'tenant' => $this->connection->table('oneqay_identities')->where('tenant_id', $tenant)->where('id', $target)->exists(),
            'organization' => $this->connection->table('oneqay_identity_organizations')->where('tenant_id', $tenant)->where('identity_id', $target)
                ->where('organization_id', $mutation->scope()->organizationId()?->value())->exists(),
            'outlet' => $this->connection->table('oneqay_outlet_access_grants')->where('tenant_id', $tenant)->where('identity_id', $target)
                ->where('organization_id', $mutation->scope()->organizationId()?->value())->where('outlet_id', $mutation->scope()->outletId()?->value())->exists(),
            'device' => $this->connection->table('oneqay_device_access_grants')->where('tenant_id', $tenant)->where('identity_id', $target)
                ->where('organization_id', $mutation->scope()->organizationId()?->value())->where('outlet_id', $mutation->scope()->outletId()?->value())
                ->where('device_id', $mutation->scope()->deviceId()?->value())->exists(),
            default => false,
        };
    }

    private function protectedRoleExists(string $tenant, string $role): bool
    {
        return $this->connection->table('oneqay_role_permissions')->where('tenant_id', $tenant)->where('role_id', $role)
            ->where('permission_id', AdministrationPermission::MANAGE)->exists();
    }

    private function requireRole(string $tenant, string $role): void
    {
        if (! $this->connection->table('oneqay_roles')->where('tenant_id', $tenant)->where('id', $role)->exists()) {
            $this->relationshipConflict();
        }
    }

    private function journalQuery(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation)
    {
        return $this->connection->table('oneqay_policy_mutations')
            ->where('tenant_id', $actor->tenantId()->value())
            ->where('mutation_id', $mutation->mutationId()->value());
    }

    private function assignmentTable(DurablePolicyMutation $mutation): string
    {
        return match ($mutation->scope()->type()) {
            'tenant' => 'oneqay_tenant_role_assignments',
            'organization' => 'oneqay_organization_role_assignments',
            'outlet' => 'oneqay_outlet_role_assignments',
            'device' => 'oneqay_device_role_assignments',
            default => throw new DurablePersistenceViolation(DurablePersistenceViolation::RELATIONSHIP_CONFLICT, 'Policy assignment scope is invalid.'),
        };
    }

    private function assignmentValues(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation): array
    {
        $values = [
            'tenant_id' => $actor->tenantId()->value(),
            'identity_id' => $mutation->targetIdentity()?->value(),
            'role_id' => $mutation->role()->value(),
        ];
        if ($mutation->scope()->organizationId() !== null) { $values['organization_id'] = $mutation->scope()->organizationId()->value(); }
        if ($mutation->scope()->outletId() !== null) { $values['outlet_id'] = $mutation->scope()->outletId()->value(); }
        if ($mutation->scope()->deviceId() !== null) { $values['device_id'] = $mutation->scope()->deviceId()->value(); }
        return $values;
    }

    private function assignmentQuery(VerifiedOrganizationalContext $actor, DurablePolicyMutation $mutation)
    {
        $query = $this->connection->table($this->assignmentTable($mutation))
            ->where('tenant_id', $actor->tenantId()->value())
            ->where('identity_id', $mutation->targetIdentity()?->value())
            ->where('role_id', $mutation->role()->value());
        if ($mutation->scope()->organizationId() !== null) { $query->where('organization_id', $mutation->scope()->organizationId()->value()); }
        if ($mutation->scope()->outletId() !== null) { $query->where('outlet_id', $mutation->scope()->outletId()->value()); }
        if ($mutation->scope()->deviceId() !== null) { $query->where('device_id', $mutation->scope()->deviceId()->value()); }
        return $query;
    }

    private function assertRuntimeAllowedForAdministration(): void
    {
        if (! $this->enabled) {
            throw new DurablePolicyAdministrationViolation(DurablePolicyAdministrationViolation::PERSISTENCE_DISABLED, 'Durable policy administration is disabled.');
        }
        $runtime = strtolower(trim($this->runtimeClass));
        if (! in_array($runtime, ['local', 'test', 'ci'], true)) {
            throw new DurablePolicyAdministrationViolation(DurablePolicyAdministrationViolation::RUNTIME_DENIED, 'Durable policy administration runtime is not authorized.');
        }
    }

    private function assertRuntimeAllowedForPersistence(): void
    {
        if (! $this->enabled) {
            throw new DurablePersistenceViolation(DurablePersistenceViolation::PERSISTENCE_DISABLED, 'Durable persistence is disabled.');
        }
        $runtime = strtolower(trim($this->runtimeClass));
        if (! in_array($runtime, ['local', 'test', 'ci'], true)) {
            throw new DurablePersistenceViolation(DurablePersistenceViolation::RUNTIME_DENIED, 'Durable persistence runtime is not authorized.');
        }
    }

    private function relationshipConflict(): never
    {
        throw new DurablePersistenceViolation(DurablePersistenceViolation::RELATIONSHIP_CONFLICT, 'Policy mutation relationship conflict.');
    }

    private function storageFailure(): never
    {
        throw new DurablePersistenceViolation(DurablePersistenceViolation::STORAGE_FAILURE, 'Policy administration storage operation failed.');
    }
}
