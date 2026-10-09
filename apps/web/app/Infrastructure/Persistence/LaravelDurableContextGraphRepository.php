<?php

namespace App\Infrastructure\Persistence;

use App\Application\Persistence\DurableContextGraph;
use App\Application\Persistence\DurableContextGraphRepository;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Database\Connection;
use Throwable;

// Author by Lab | zefry
final readonly class LaravelDurableContextGraphRepository implements DurableContextGraphRepository
{
    public function __construct(
        private Connection $connection,
        private bool $enabled,
        private string $runtimeClass,
    ) {
    }

    public function persist(DurableContextGraph $graph): void
    {
        $this->assertRuntimeAllowed();

        $tenantId = $graph->tenantId->value();
        $identityId = $graph->identityId->value();
        $organizationId = $graph->organizationId->value();
        $outletId = $graph->outletId->value();
        $deviceId = $graph->deviceId->value();

        try {
            $this->insertAndAssert(
                'oneqay_tenants',
                ['id' => $tenantId],
                ['id' => $tenantId],
            );
            $this->insertAndAssert(
                'oneqay_identities',
                ['tenant_id' => $tenantId, 'id' => $identityId],
                ['tenant_id' => $tenantId, 'id' => $identityId],
            );
            $this->insertAndAssert(
                'oneqay_organizations',
                ['tenant_id' => $tenantId, 'id' => $organizationId],
                ['tenant_id' => $tenantId, 'id' => $organizationId],
            );
            $this->insertAndAssert(
                'oneqay_identity_organizations',
                [
                    'tenant_id' => $tenantId,
                    'identity_id' => $identityId,
                    'organization_id' => $organizationId,
                ],
                [
                    'tenant_id' => $tenantId,
                    'identity_id' => $identityId,
                    'organization_id' => $organizationId,
                ],
            );
            $this->insertAndAssert(
                'oneqay_outlets',
                [
                    'tenant_id' => $tenantId,
                    'id' => $outletId,
                    'organization_id' => $organizationId,
                ],
                ['tenant_id' => $tenantId, 'id' => $outletId],
            );
            $this->insertAndAssert(
                'oneqay_devices',
                [
                    'tenant_id' => $tenantId,
                    'id' => $deviceId,
                    'organization_id' => $organizationId,
                    'outlet_id' => $outletId,
                ],
                ['tenant_id' => $tenantId, 'id' => $deviceId],
            );
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::STORAGE_FAILURE,
                'Durable persistence storage operation failed.',
            );
        }
    }

    public function findForTenant(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        DeviceId $deviceId,
    ): ?DurableContextGraph {
        $this->assertRuntimeAllowed();

        $tenant = $tenantId->value();

        try {
            $device = $this->connection->table('oneqay_devices')
                ->where('tenant_id', $tenant)
                ->where('id', $deviceId->value())
                ->first();

            if ($device === null) {
                return null;
            }

            $identity = $this->connection->table('oneqay_identities')
                ->where('tenant_id', $tenant)
                ->where('id', $identityId->value())
                ->first();
            $organization = $this->connection->table('oneqay_organizations')
                ->where('tenant_id', $tenant)
                ->where('id', (string) $device->organization_id)
                ->first();
            $outlet = $this->connection->table('oneqay_outlets')
                ->where('tenant_id', $tenant)
                ->where('id', (string) $device->outlet_id)
                ->where('organization_id', (string) $device->organization_id)
                ->first();
            $membership = $this->connection->table('oneqay_identity_organizations')
                ->where('tenant_id', $tenant)
                ->where('identity_id', $identityId->value())
                ->where('organization_id', (string) $device->organization_id)
                ->first();

            if ($identity === null || $organization === null || $outlet === null || $membership === null) {
                throw new DurablePersistenceViolation(
                    DurablePersistenceViolation::STORAGE_FAILURE,
                    'Durable persistence relationship graph is incomplete.',
                );
            }

            return new DurableContextGraph(
                TenantId::fromString($tenant),
                PlatformIdentityId::fromString((string) $identity->id),
                OrganizationId::fromString((string) $organization->id),
                OutletId::fromString((string) $outlet->id),
                DeviceId::fromString((string) $device->id),
            );
        } catch (DurablePersistenceViolation $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::STORAGE_FAILURE,
                'Durable persistence read failed.',
            );
        }
    }

    /**
     * @param array<string, string> $values
     * @param array<string, string> $key
     */
    private function insertAndAssert(string $table, array $values, array $key): void
    {
        $this->connection->table($table)->insertOrIgnore($values);

        $query = $this->connection->table($table);
        foreach ($key as $column => $value) {
            $query->where($column, $value);
        }

        $persisted = $query->first();
        if ($persisted === null) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::STORAGE_FAILURE,
                'Durable persistence row was not stored.',
            );
        }

        foreach ($values as $column => $value) {
            if (! property_exists($persisted, $column) || ! hash_equals($value, (string) $persisted->{$column})) {
                throw new DurablePersistenceViolation(
                    DurablePersistenceViolation::RELATIONSHIP_CONFLICT,
                    'Durable persistence relationship conflict.',
                );
            }
        }
    }

    private function assertRuntimeAllowed(): void
    {
        if (! $this->enabled) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::PERSISTENCE_DISABLED,
                'Durable persistence is disabled.',
            );
        }

        $runtime = strtolower(trim($this->runtimeClass));
        if (! in_array($runtime, ['local', 'test', 'ci'], true)) {
            throw new DurablePersistenceViolation(
                DurablePersistenceViolation::RUNTIME_DENIED,
                'Durable persistence runtime is not authorized.',
            );
        }
    }
}
