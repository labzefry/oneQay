<?php

namespace App\Infrastructure\Access;

use App\Application\Access\DurableOrganizationalAccessGrant;
use App\Application\Access\DurableOrganizationalAccessRepository;
use App\Application\Access\DurableOrganizationalAccessViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelDurableOrganizationalAccessRepository implements DurableOrganizationalAccessRepository
{
    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
    ) {
    }

    public function record(DurableOrganizationalAccessGrant $grant): void
    {
        $this->assertRuntimeAllowed();

        $tenant = $grant->tenantId->value();
        $identity = $grant->identityId->value();
        $organization = $grant->organizationId->value();

        try {
            if (! $this->organizationMembershipExists($tenant, $identity, $organization)) {
                throw new DurableOrganizationalAccessViolation(
                    DurableOrganizationalAccessViolation::MEMBERSHIP_REQUIRED,
                    'Durable organizational access requires an existing verified organization membership.',
                );
            }

            if ($grant->outletId === null) {
                return;
            }

            $outlet = $grant->outletId->value();
            $this->insertAndAssert(
                'oneqay_outlet_access_grants',
                [
                    'tenant_id' => $tenant,
                    'identity_id' => $identity,
                    'organization_id' => $organization,
                    'outlet_id' => $outlet,
                ],
            );

            if ($grant->deviceId === null) {
                return;
            }

            $this->insertAndAssert(
                'oneqay_device_access_grants',
                [
                    'tenant_id' => $tenant,
                    'identity_id' => $identity,
                    'organization_id' => $organization,
                    'outlet_id' => $outlet,
                    'device_id' => $grant->deviceId->value(),
                ],
            );
        } catch (DurableOrganizationalAccessViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new DurableOrganizationalAccessViolation(
                DurableOrganizationalAccessViolation::STORAGE_FAILURE,
                'Durable organizational access storage operation failed.',
            );
        }
    }

    public function hasTenantMembership(TenantId $tenantId, PlatformIdentityId $identityId): bool
    {
        $this->assertRuntimeAllowed();

        try {
            return $this->connection->table('oneqay_identity_organizations')
                ->where('tenant_id', $tenantId->value())
                ->where('identity_id', $identityId->value())
                ->exists();
        } catch (Throwable) {
            throw new DurableOrganizationalAccessViolation(
                DurableOrganizationalAccessViolation::STORAGE_FAILURE,
                'Durable tenant membership verification failed.',
            );
        }
    }

    public function allows(DurableOrganizationalAccessGrant $grant): bool
    {
        $this->assertRuntimeAllowed();

        $tenant = $grant->tenantId->value();
        $identity = $grant->identityId->value();
        $organization = $grant->organizationId->value();

        try {
            if (! $this->organizationMembershipExists($tenant, $identity, $organization)) {
                return false;
            }

            if ($grant->outletId === null) {
                return $grant->deviceId === null;
            }

            $outlet = $grant->outletId->value();
            $outletAllowed = $this->connection->table('oneqay_outlet_access_grants')
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('organization_id', $organization)
                ->where('outlet_id', $outlet)
                ->exists();

            if (! $outletAllowed) {
                return false;
            }

            if ($grant->deviceId === null) {
                return true;
            }

            return $this->connection->table('oneqay_device_access_grants')
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identity)
                ->where('organization_id', $organization)
                ->where('outlet_id', $outlet)
                ->where('device_id', $grant->deviceId->value())
                ->exists();
        } catch (Throwable) {
            throw new DurableOrganizationalAccessViolation(
                DurableOrganizationalAccessViolation::STORAGE_FAILURE,
                'Durable organizational access verification failed.',
            );
        }
    }

    private function organizationMembershipExists(string $tenant, string $identity, string $organization): bool
    {
        return $this->connection->table('oneqay_identity_organizations')
            ->where('tenant_id', $tenant)
            ->where('identity_id', $identity)
            ->where('organization_id', $organization)
            ->exists();
    }

    /** @param array<string, string> $values */
    private function insertAndAssert(string $table, array $values): void
    {
        $this->connection->table($table)->insertOrIgnore($values);

        $query = $this->connection->table($table);
        foreach ($values as $column => $value) {
            $query->where($column, $value);
        }

        $persisted = $query->first();
        if ($persisted === null) {
            throw new DurableOrganizationalAccessViolation(
                DurableOrganizationalAccessViolation::STORAGE_FAILURE,
                'Durable organizational access grant was not stored.',
            );
        }

        foreach ($values as $column => $value) {
            if (! property_exists($persisted, $column) || ! hash_equals($value, (string) $persisted->{$column})) {
                throw new DurableOrganizationalAccessViolation(
                    DurableOrganizationalAccessViolation::RELATIONSHIP_CONFLICT,
                    'Durable organizational access relationship conflict.',
                );
            }
        }
    }

    private function assertRuntimeAllowed(): void
    {
        if (! $this->enabled) {
            throw new DurableOrganizationalAccessViolation(
                DurableOrganizationalAccessViolation::PERSISTENCE_DISABLED,
                'Durable organizational access persistence is disabled.',
            );
        }

        $runtime = strtolower(trim($this->runtimeClass));
        if (! in_array($runtime, ['local', 'test', 'ci'], true)) {
            throw new DurableOrganizationalAccessViolation(
                DurableOrganizationalAccessViolation::RUNTIME_DENIED,
                'Durable organizational access runtime is not authorized.',
            );
        }
    }
}
