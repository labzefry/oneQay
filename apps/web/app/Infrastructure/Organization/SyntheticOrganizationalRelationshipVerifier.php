<?php

namespace App\Infrastructure\Organization;

use App\Application\Organization\OrganizationalRelationshipVerifier;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final class SyntheticOrganizationalRelationshipVerifier implements OrganizationalRelationshipVerifier
{
    /** @var array<string, true> */
    private array $grants = [];

    /**
     * @param array<string, list<array{
     *     tenant: string,
     *     organization: string,
     *     outlet?: ?string,
     *     device?: ?string
     * }>> $grants
     */
    public function __construct(array $grants)
    {
        foreach ($grants as $identity => $relationships) {
            if (! str_starts_with($identity, 'synthetic-principal-')) {
                throw new InvalidArgumentException('Synthetic organizational verifier accepts synthetic principals only.');
            }

            $identityId = PlatformIdentityId::fromString($identity);

            foreach ($relationships as $relationship) {
                $tenantId = TenantId::fromString($relationship['tenant']);
                $organizationId = OrganizationId::fromString($relationship['organization']);
                $outletId = array_key_exists('outlet', $relationship) && $relationship['outlet'] !== null
                    ? OutletId::fromString($relationship['outlet'])
                    : null;
                $deviceId = array_key_exists('device', $relationship) && $relationship['device'] !== null
                    ? DeviceId::fromString($relationship['device'])
                    : null;

                if ($deviceId !== null && $outletId === null) {
                    throw new InvalidArgumentException('Synthetic device relationship requires an outlet.');
                }

                $this->grants[$this->key(
                    $identityId,
                    $tenantId,
                    $organizationId,
                    $outletId,
                    $deviceId,
                )] = true;
            }
        }
    }

    public function verify(
        PlatformIdentityId $identityId,
        TenantId $tenantId,
        OrganizationId $organizationId,
        ?OutletId $outletId,
        ?DeviceId $deviceId,
    ): bool {
        return ($this->grants[$this->key(
            $identityId,
            $tenantId,
            $organizationId,
            $outletId,
            $deviceId,
        )] ?? false) === true;
    }

    private function key(
        PlatformIdentityId $identityId,
        TenantId $tenantId,
        OrganizationId $organizationId,
        ?OutletId $outletId,
        ?DeviceId $deviceId,
    ): string {
        return implode('|', [
            $identityId->value(),
            $tenantId->value(),
            $organizationId->value(),
            $outletId?->value() ?? '-',
            $deviceId?->value() ?? '-',
        ]);
    }
}
