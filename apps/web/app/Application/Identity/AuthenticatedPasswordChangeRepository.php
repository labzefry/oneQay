<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface AuthenticatedPasswordChangeRepository
{
    /** Called only inside PersistenceTransaction. */
    public function change(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $expectedCredentialEpoch,
        #[\SensitiveParameter] string $currentPassword,
        #[\SensitiveParameter] string $newPassword,
        int $occurredAtUnix,
    ): void;
}
