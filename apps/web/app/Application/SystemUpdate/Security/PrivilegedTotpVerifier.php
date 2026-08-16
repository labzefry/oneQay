<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Security;

use App\Domain\Identity\PlatformIdentityId;

// Author by Lab | zefry
interface PrivilegedTotpVerifier
{
    /**
     * The TOTP code is sensitive transient input. Implementations must never persist or log it.
     */
    public function verify(PlatformIdentityId $identityId, string $code, int $unixTime): bool;
}
