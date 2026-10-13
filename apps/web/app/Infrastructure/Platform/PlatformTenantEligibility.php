<?php
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use App\Application\Identity\FirstPartyIdentityEligibilityVerifier;
use App\Application\Platform\PlatformStore;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
// Author by Lab | zefry. Applied to fresh login, active sessions, and pending MFA.
final readonly class PlatformTenantEligibility implements FirstPartyIdentityEligibilityVerifier
{
    public function __construct(private FirstPartyIdentityEligibilityVerifier $inner,private PlatformStore $store) {}
    public function isEligible(TenantId $tenantId,PlatformIdentityId $identityId):bool
    {
        try{return $this->inner->isEligible($tenantId,$identityId) && $this->store->tenantAllowed($tenantId->value());}
        catch(\Throwable){return false;}
    }
}
