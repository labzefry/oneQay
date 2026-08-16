<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Security;

use App\Application\Identity\VerifiedPlatformIdentity;

// Author by Lab | zefry
interface VerifiedPrivilegedPlatformIdentity extends VerifiedPlatformIdentity
{
    public function isPlatformSuperadmin(): bool;

    /** @return list<string> */
    public function capabilities(): array;

    public function authenticatedAtUnix(): int;

    /**
     * Opaque, non-secret server-derived fingerprint for the current authenticated session.
     * The raw session identifier must never be exposed through this boundary.
     */
    public function sessionBinding(): string;
}
