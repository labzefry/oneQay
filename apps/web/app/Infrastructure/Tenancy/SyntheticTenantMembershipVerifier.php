<?php

namespace App\Infrastructure\Tenancy;

use App\Application\Tenancy\TenantMembershipVerifier;
use App\Application\Tenancy\VerifiedTenantContext;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

final class SyntheticTenantMembershipVerifier implements TenantMembershipVerifier
{
    /** @var array<string, array<string, true>> */
    private array $grants = [];

    /**
     * @param array<string, list<string>> $grants
     */
    public function __construct(array $grants)
    {
        foreach ($grants as $principalId => $tenantIds) {
            if (! str_starts_with($principalId, 'synthetic-principal-')) {
                throw new InvalidArgumentException('Synthetic verifier accepts synthetic principals only.');
            }

            foreach ($tenantIds as $tenantId) {
                $canonical = TenantId::fromString($tenantId)->value();
                $this->grants[$principalId][$canonical] = true;
            }
        }
    }

    public function verify(string $principalId, string $tenantHint): ?VerifiedTenantContext
    {
        try {
            $tenantId = TenantId::fromString($tenantHint);
        } catch (InvalidArgumentException) {
            return null;
        }

        if (($this->grants[$principalId][$tenantId->value()] ?? false) !== true) {
            return null;
        }

        return new ServerVerifiedTenantContext($tenantId);
    }
}
