<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface PrivilegedTotpEngine
{
    public function generateSecret(): string;

    public function provisioningUri(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] string $secret,
    ): string;

    public function matchTimeStep(
        #[\SensitiveParameter] string $secret,
        #[\SensitiveParameter] string $code,
        int $nowUnix,
    ): ?int;
}
