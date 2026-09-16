<?php

declare(strict_types=1);

namespace App\Infrastructure\Bootstrap;

use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Application\Bootstrap\MerchantContextBootstrapAuthority;
use App\Application\Persistence\DurableContextGraph;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
final class PreauthorizedMerchantContextBootstrapAuthority implements MerchantContextBootstrapAuthority
{
    /** @var array<string, true> */
    private array $bootstrapAuthorized = [];

    /** @var array<string, true> */
    private array $administratorAuthorized = [];

    /**
     * @param list<array{
     *   tenant_id:string,
     *   identity_id:string,
     *   organization_id:string,
     *   outlet_id:string,
     *   device_id:string,
     *   provisioning_id:string
     * }> $grants
     */
    public function __construct(array $grants)
    {
        foreach ($grants as $grant) {
            $graph = new DurableContextGraph(
                TenantId::fromString($grant['tenant_id']),
                PlatformIdentityId::fromString($grant['identity_id']),
                OrganizationId::fromString($grant['organization_id']),
                OutletId::fromString($grant['outlet_id']),
                DeviceId::fromString($grant['device_id']),
            );
            $provisioningId = InitialTenantAdministratorProvisioningId::fromString($grant['provisioning_id']);

            $this->bootstrapAuthorized[$this->bootstrapKey($graph, $provisioningId)] = true;
            $this->administratorAuthorized[$this->administratorKey(
                $graph->tenantId,
                $graph->identityId,
                $provisioningId,
            )] = true;
        }
    }

    public function authorizesBootstrap(
        DurableContextGraph $graph,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): bool {
        return isset($this->bootstrapAuthorized[$this->bootstrapKey($graph, $provisioningId)]);
    }

    public function authorizes(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): bool {
        return isset($this->administratorAuthorized[$this->administratorKey(
            $tenantId,
            $identityId,
            $provisioningId,
        )]);
    }

    private function bootstrapKey(
        DurableContextGraph $graph,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): string {
        return implode("\0", [
            $graph->tenantId->value(),
            $graph->identityId->value(),
            $graph->organizationId->value(),
            $graph->outletId->value(),
            $graph->deviceId->value(),
            $provisioningId->value(),
        ]);
    }

    private function administratorKey(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): string {
        return implode("\0", [
            $tenantId->value(),
            $identityId->value(),
            $provisioningId->value(),
        ]);
    }
}
