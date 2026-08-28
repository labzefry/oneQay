<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface FirstPartyIdentityDisablementSessionTerminationRepository
{
    public function revokeActiveForIdentityDisablement(
        TenantId $tenantId,
        PlatformIdentityId $targetIdentityId,
        int $revokedAtUnix,
    ): int;
}
