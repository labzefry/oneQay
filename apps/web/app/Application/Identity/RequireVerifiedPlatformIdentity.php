<?php

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use InvalidArgumentException;

// Author by Lab | zefry
final class RequireVerifiedPlatformIdentity
{
    public function require(?VerifiedPlatformIdentity $identity): PlatformIdentityId
    {
        if ($identity === null) {
            throw new IdentityContextViolation('Verified platform identity is required.');
        }

        try {
            return PlatformIdentityId::fromString($identity->identityId());
        } catch (InvalidArgumentException) {
            throw new IdentityContextViolation('Verified platform identity is invalid.');
        }
    }
}
