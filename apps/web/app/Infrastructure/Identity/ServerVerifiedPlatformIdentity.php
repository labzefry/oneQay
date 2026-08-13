<?php

namespace App\Infrastructure\Identity;

use App\Application\Identity\VerifiedPlatformIdentity;
use App\Domain\Identity\PlatformIdentityId;

// Author by Lab | zefry
final readonly class ServerVerifiedPlatformIdentity implements VerifiedPlatformIdentity
{
    public function __construct(private PlatformIdentityId $identityId)
    {
    }

    public function identityId(): string
    {
        return $this->identityId->value();
    }
}
