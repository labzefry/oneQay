<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface RecoveryPasswordResetRepository
{
    /** Called only inside PersistenceTransaction. */
    public function complete(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $codeId,
        #[\SensitiveParameter] string $password,
        int $occurredAtUnix,
        string $correlationId,
    ): void;
}
