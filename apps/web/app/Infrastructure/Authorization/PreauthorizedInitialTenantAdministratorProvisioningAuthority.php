<?php

declare(strict_types=1);

namespace App\Infrastructure\Authorization;

use App\Application\Authorization\InitialTenantAdministratorProvisioningAuthority;
use App\Application\Authorization\InitialTenantAdministratorProvisioningId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
final class PreauthorizedInitialTenantAdministratorProvisioningAuthority implements InitialTenantAdministratorProvisioningAuthority
{
    /** @var array<string, true> */
    private array $authorized = [];

    /**
     * @param list<array{tenant_id:string,identity_id:string,provisioning_id:string}> $grants
     */
    public function __construct(array $grants)
    {
        foreach ($grants as $grant) {
            $tenantId = TenantId::fromString($grant['tenant_id']);
            $identityId = PlatformIdentityId::fromString($grant['identity_id']);
            $provisioningId = InitialTenantAdministratorProvisioningId::fromString($grant['provisioning_id']);
            $this->authorized[$this->key($tenantId, $identityId, $provisioningId)] = true;
        }
    }

    public function authorizes(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): bool {
        return isset($this->authorized[$this->key($tenantId, $identityId, $provisioningId)]);
    }

    private function key(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        InitialTenantAdministratorProvisioningId $provisioningId,
    ): string {
        return $tenantId->value()."\0".$identityId->value()."\0".$provisioningId->value();
    }
}
