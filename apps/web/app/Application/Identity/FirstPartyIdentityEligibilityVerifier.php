<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface FirstPartyIdentityEligibilityVerifier
{
    public function isEligible(TenantId $tenantId, PlatformIdentityId $identityId): bool;
}
