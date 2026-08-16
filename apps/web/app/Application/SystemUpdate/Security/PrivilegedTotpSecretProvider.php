<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Security;

use App\Domain\Identity\PlatformIdentityId;

// Author by Lab | zefry
interface PrivilegedTotpSecretProvider
{
    /**
     * Returns Base32 secret material only to the verifier boundary.
     * Implementations must keep it encrypted at rest and must never log or expose it to delivery code.
     */
    public function base32SecretFor(PlatformIdentityId $identityId): ?string;
}
