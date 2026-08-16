<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Security;

use App\Domain\Identity\PlatformIdentityId;

// Author by Lab | zefry
final readonly class PrivilegedUpdateAuthorization
{
    public function __construct(
        private PlatformIdentityId $identityId,
        private string $capability,
        private int $authorizedAtUnix,
    ) {
    }

    public function identityId(): PlatformIdentityId
    {
        return $this->identityId;
    }

    public function capability(): string
    {
        return $this->capability;
    }

    public function authorizedAtUnix(): int
    {
        return $this->authorizedAtUnix;
    }
}
