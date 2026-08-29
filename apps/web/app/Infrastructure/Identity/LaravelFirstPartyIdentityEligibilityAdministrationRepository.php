<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Authorization\AdministrationPermission;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationRepository;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationViolation;
use App\Application\Identity\IdentityAuthenticationEligibilityMutationId;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelFirstPartyIdentityEligibilityAdministrationRepository implements FirstPartyIdentityEligibilityAdministrationRepository
{
    private const IDENTITY_TABLE = 'oneqay_identities';
    private const JOURNAL_TABLE = 'oneqay_identity_authentication_eligibility_mutations';

    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
    ) {}

    public function hasTenantControlAuthority(VerifiedOrganizationalContext $actor): bool
    {
        $this->assertRuntimeAllowedForAdministration();

        try {
            return $this->tenantControlAuthorityExists(
                $actor->tenantId()->value(),
                $actor->identityId()->value(),
            );
        } catch (Throwable) {
            $this->administrationStorageFailure();
        }
    }

    public function assertTargetEligible(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
    ): void {
        $this->assertRuntimeAllowedForAdministration();

        try {
            $tenant = $actor->tenantId()->value();
            $actorIdentity = $actor->identityId()->value();
            $target = $targetIdentityId->value();

            if (hash_equals($actorIdentity, $target)) {
                throw new FirstPartyIdentityEligibilityAdministrationViolation(
                    FirstPartyIdentityEligibilityAdministrationViolation::TARGET_IDENTITY_DENIED,
                    'Identity authentication eligibility administration self-target is denied.',
                );
            }

            if (! $this->identityExists($tenant, $target)) {
                throw new FirstPartyIdentityEligibilityAdministrationViolation(
                    FirstPartyIdentityEligibilityAdministrationViolation::TARGET_IDENTITY_DENIED,
                    'Identity authentication eligibility administration target is not eligible for this tenant.',
                );
            }

            if ($this->tenantControlAuthorityExists($tenant, $target)) {
                throw new FirstPartyIdentityEligibilityAdministrationViolation(
                    FirstPartyIdentityEligibilityAdministrationViolation::PROTECTED_TARGET,
                    'Protected control identity eligibility is outside this administration concern.',
                );
            }
        } catch (FirstPartyIdentityEligibilityAdministrationViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->administrationStorageFailure();
        }
    }

    public function replayOutcome(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        IdentityAuthenticationEligibilityMutationId $mutationId,
    ): ?string {
        $this->assertRuntimeAllowedForAdministration();

        try {
            $row = $this->connection->table(self::JOURNAL_TABLE)
                ->where('tenant_id', $actor->tenantId()->value())
                ->where('mutation_id', $mutationId->value())
                ->first();

            if ($row === null) {
                return null;
            }

            $expected = $this->fingerprint($actor, $targetIdentityId);
            if (
                ! is_string($row->payload_fingerprint ?? null)
                || ! hash_equals($row->payload_fingerprint, $expected)
                || ! is_string($row->operation ?? null)
                || ! hash_equals($row->operation, self::OPERATION_DISABLE)
            ) {
                throw new FirstPartyIdentityEligibilityAdministrationViolation(
                    FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT,
                    'Identity authentication eligibility mutation identifier is bound to a different payload.',
                );
            }

            $outcome = is_string($row->outcome ?? null) ? $row->outcome : '';
            if (! in_array($outcome, [self::OUTCOME_APPLIED, self::OUTCOME_NO_CHANGE], true)) {
                $this->administrationStorageFailure();
            }

            return $outcome;
        } catch (FirstPartyIdentityEligibilityAdministrationViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->administrationStorageFailure();
        }
    }

    public function replayReactivationOutcome(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        IdentityAuthenticationEligibilityMutationId $mutationId,
    ): ?string {
        $this->assertRuntimeAllowedForAdministration();

        try {
            $row = $this->connection->table(self::JOURNAL_TABLE)
                ->where('tenant_id', $actor->tenantId()->value())
                ->where('mutation_id', $mutationId->value())
                ->first();

            if ($row === null) {
                return null;
            }

            $expected = $this->reactivationFingerprint($actor, $targetIdentityId);
            $actorIdentity = $actor->identityId()->value();
            $target = $targetIdentityId->value();

            if (
                ! is_string($row->payload_fingerprint ?? null)
                || ! hash_equals($row->payload_fingerprint, $expected)
                || ! is_string($row->operation ?? null)
                || ! hash_equals($row->operation, self::OPERATION_REACTIVATE)
                || ! is_string($row->actor_identity_id ?? null)
                || ! hash_equals($row->actor_identity_id, $actorIdentity)
                || ! is_string($row->target_identity_id ?? null)
                || ! hash_equals($row->target_identity_id, $target)
            ) {
                throw new FirstPartyIdentityEligibilityAdministrationViolation(
                    FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT,
                    'Identity authentication eligibility mutation identifier is bound to a different payload.',
                );
            }

            $outcome = is_string($row->outcome ?? null) ? $row->outcome : '';
            if (! in_array($outcome, [self::OUTCOME_APPLIED, self::OUTCOME_NO_CHANGE], true)) {
                $this->administrationStorageFailure();
            }

            return $outcome;
        } catch (FirstPartyIdentityEligibilityAdministrationViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->administrationStorageFailure();
        }
    }

    public function applyFresh(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        IdentityAuthenticationEligibilityMutationId $mutationId,
        int $occurredAtUnix,
    ): string {
        $this->assertRuntimeAllowedForPersistence();

        if ($occurredAtUnix <= 0) {
            $this->relationshipConflict();
        }

        try {
            $tenant = $actor->tenantId()->value();
            $actorIdentity = $actor->identityId()->value();
            $target = $targetIdentityId->value();

            if (hash_equals($actorIdentity, $target)) {
                $this->relationshipConflict();
            }
            if (! $this->tenantControlAuthorityExists($tenant, $actorIdentity)) {
                $this->relationshipConflict();
            }
            if (! $this->identityExists($tenant, $target)) {
                $this->relationshipConflict();
            }
            if ($this->tenantControlAuthorityExists($tenant, $target)) {
                $this->relationshipConflict();
            }

            $existing = $this->connection->table(self::JOURNAL_TABLE)
                ->where('tenant_id', $tenant)
                ->where('mutation_id', $mutationId->value())
                ->first();

            if ($existing !== null) {
                if (
                    is_string($existing->payload_fingerprint ?? null)
                    && hash_equals($existing->payload_fingerprint, $this->fingerprint($actor, $targetIdentityId))
                    && is_string($existing->operation ?? null)
                    && hash_equals($existing->operation, self::OPERATION_DISABLE)
                    && is_string($existing->outcome ?? null)
                    && in_array($existing->outcome, [self::OUTCOME_APPLIED, self::OUTCOME_NO_CHANGE], true)
                ) {
                    return $existing->outcome;
                }

                $this->relationshipConflict();
            }

            $state = $this->readEligibilityState($tenant, $target);
            $outcome = self::OUTCOME_NO_CHANGE;

            if ($state === true) {
                $updated = $this->connection->table(self::IDENTITY_TABLE)
                    ->where('tenant_id', $tenant)
                    ->where('id', $target)
                    ->where('first_party_authentication_enabled', 1)
                    ->update(['first_party_authentication_enabled' => false]);

                if ($updated === 1) {
                    $outcome = self::OUTCOME_APPLIED;
                } elseif ($updated === 0 && $this->readEligibilityState($tenant, $target) === false) {
                    $outcome = self::OUTCOME_NO_CHANGE;
                } else {
                    $this->relationshipConflict();
                }
            }

            if ($this->readEligibilityState($tenant, $target) !== false) {
                $this->relationshipConflict();
            }

            $inserted = $this->connection->table(self::JOURNAL_TABLE)->insertOrIgnore([
                'tenant_id' => $tenant,
                'mutation_id' => $mutationId->value(),
                'actor_identity_id' => $actorIdentity,
                'target_identity_id' => $target,
                'operation' => self::OPERATION_DISABLE,
                'payload_fingerprint' => $this->fingerprint($actor, $targetIdentityId),
                'outcome' => $outcome,
                'occurred_at_unix' => $occurredAtUnix,
            ]);

            if ($inserted !== 1) {
                $this->relationshipConflict();
            }

            if (! $this->tenantControlAuthorityExists($tenant, $actorIdentity)) {
                $this->relationshipConflict();
            }
            if ($this->tenantControlAuthorityExists($tenant, $target)) {
                $this->relationshipConflict();
            }
            if ($this->readEligibilityState($tenant, $target) !== false) {
                $this->relationshipConflict();
            }

            return $outcome;
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            $this->persistenceStorageFailure();
        }
    }

    public function applyFreshReactivation(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
        IdentityAuthenticationEligibilityMutationId $mutationId,
        int $occurredAtUnix,
    ): string {
        $this->assertRuntimeAllowedForPersistence();

        if ($occurredAtUnix <= 0) {
            $this->relationshipConflict();
        }

        try {
            $tenant = $actor->tenantId()->value();
            $actorIdentity = $actor->identityId()->value();
            $target = $targetIdentityId->value();
            $fingerprint = $this->reactivationFingerprint($actor, $targetIdentityId);

            if (hash_equals($actorIdentity, $target)) {
                $this->relationshipConflict();
            }
            if (! $this->tenantControlAuthorityExists($tenant, $actorIdentity)) {
                $this->relationshipConflict();
            }
            if (! $this->identityExists($tenant, $target)) {
                $this->relationshipConflict();
            }
            if ($this->tenantControlAuthorityExists($tenant, $target)) {
                $this->relationshipConflict();
            }

            $existing = $this->connection->table(self::JOURNAL_TABLE)
                ->where('tenant_id', $tenant)
                ->where('mutation_id', $mutationId->value())
                ->first();

            if ($existing !== null) {
                if (
                    is_string($existing->payload_fingerprint ?? null)
                    && hash_equals($existing->payload_fingerprint, $fingerprint)
                    && is_string($existing->operation ?? null)
                    && hash_equals($existing->operation, self::OPERATION_REACTIVATE)
                    && is_string($existing->actor_identity_id ?? null)
                    && hash_equals($existing->actor_identity_id, $actorIdentity)
                    && is_string($existing->target_identity_id ?? null)
                    && hash_equals($existing->target_identity_id, $target)
                    && is_string($existing->outcome ?? null)
                    && in_array($existing->outcome, [self::OUTCOME_APPLIED, self::OUTCOME_NO_CHANGE], true)
                ) {
                    return $existing->outcome;
                }

                $this->relationshipConflict();
            }

            $state = $this->readEligibilityState($tenant, $target);
            $outcome = self::OUTCOME_NO_CHANGE;

            if ($state === false) {
                $updated = $this->connection->table(self::IDENTITY_TABLE)
                    ->where('tenant_id', $tenant)
                    ->where('id', $target)
                    ->where('first_party_authentication_enabled', 0)
                    ->update(['first_party_authentication_enabled' => true]);

                if ($updated === 1) {
                    $outcome = self::OUTCOME_APPLIED;
                } elseif ($updated === 0 && $this->readEligibilityState($tenant, $target) === true) {
                    $outcome = self::OUTCOME_NO_CHANGE;
                } else {
                    $this->relationshipConflict();
                }
            }

            if ($this->readEligibilityState($tenant, $target) !== true) {
                $this->relationshipConflict();
            }

            $inserted = $this->connection->table(self::JOURNAL_TABLE)->insertOrIgnore([
                'tenant_id' => $tenant,
                'mutation_id' => $mutationId->value(),
                'actor_identity_id' => $actorIdentity,
                'target_identity_id' => $target,
                'operation' => self::OPERATION_REACTIVATE,
                'payload_fingerprint' => $fingerprint,
                'outcome' => $outcome,
                'occurred_at_unix' => $occurredAtUnix,
            ]);

            if ($inserted !== 1) {
                $this->relationshipConflict();
            }

            if (! $this->tenantControlAuthorityExists($tenant, $actorIdentity)) {
                $this->relationshipConflict();
            }
            if ($this->tenantControlAuthorityExists($tenant, $target)) {
                $this->relationshipConflict();
            }
            if ($this->readEligibilityState($tenant, $target) !== true) {
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
        return $this->connection->table(self::IDENTITY_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('id', $identityId)
            ->exists();
    }

    private function readEligibilityState(string $tenantId, string $identityId): bool
    {
        $rows = $this->connection->table(self::IDENTITY_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('id', $identityId)
            ->limit(2)
            ->get(['first_party_authentication_enabled']);

        if ($rows->count() !== 1) {
            $this->relationshipConflict();
        }

        $value = $rows->first()->first_party_authentication_enabled ?? null;
        if ($value === true || $value === 1 || $value === '1') {
            return true;
        }
        if ($value === false || $value === 0 || $value === '0') {
            return false;
        }

        $this->relationshipConflict();
    }

    private function fingerprint(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
    ): string {
        return hash('sha256', implode("\n", [
            $actor->tenantId()->value(),
            $actor->identityId()->value(),
            $targetIdentityId->value(),
            self::OPERATION_DISABLE,
            AdministrationPermission::MANAGE,
            'tenant',
        ]));
    }

    private function reactivationFingerprint(
        VerifiedOrganizationalContext $actor,
        PlatformIdentityId $targetIdentityId,
    ): string {
        return hash('sha256', implode("\n", [
            $actor->tenantId()->value(),
            $actor->identityId()->value(),
            $targetIdentityId->value(),
            self::OPERATION_REACTIVATE,
            AdministrationPermission::MANAGE,
            'tenant',
        ]));
    }

    private function assertRuntimeAllowedForAdministration(): void
    {
        if (! $this->enabled) {
            throw new FirstPartyIdentityEligibilityAdministrationViolation(
                FirstPartyIdentityEligibilityAdministrationViolation::PERSISTENCE_DISABLED,
                'Identity authentication eligibility administration persistence is disabled.',
            );
        }

        if (! in_array(strtolower(trim($this->runtimeClass)), ['local', 'test', 'ci'], true)) {
            throw new FirstPartyIdentityEligibilityAdministrationViolation(
                FirstPartyIdentityEligibilityAdministrationViolation::RUNTIME_DENIED,
                'Identity authentication eligibility administration runtime is not authorized.',
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

    private function administrationStorageFailure(): never
    {
        throw new FirstPartyIdentityEligibilityAdministrationViolation(
            FirstPartyIdentityEligibilityAdministrationViolation::STORAGE_FAILURE,
            'Identity authentication eligibility administration storage operation failed.',
        );
    }

    private function relationshipConflict(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::RELATIONSHIP_CONFLICT,
            'Identity authentication eligibility administration relationship is invalid.',
        );
    }

    private function persistenceStorageFailure(): never
    {
        throw new DurablePersistenceViolation(
            DurablePersistenceViolation::STORAGE_FAILURE,
            'Identity authentication eligibility administration storage operation failed.',
        );
    }
}
