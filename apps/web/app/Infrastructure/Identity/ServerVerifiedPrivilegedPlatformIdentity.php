<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\SystemUpdate\Security\VerifiedPrivilegedPlatformIdentity;
use App\Domain\Identity\PlatformIdentityId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class ServerVerifiedPrivilegedPlatformIdentity implements VerifiedPrivilegedPlatformIdentity
{
    /** @var list<string> */
    private array $capabilities;

    public function __construct(
        private PlatformIdentityId $identityId,
        private bool $platformSuperadmin,
        array $capabilities,
        private int $authenticatedAtUnix,
        private string $sessionBinding,
    ) {
        if ($authenticatedAtUnix <= 0) {
            throw new InvalidArgumentException('Verified privileged authentication timestamp is invalid.');
        }

        if (preg_match('/\A[a-f0-9]{64}\z/', $sessionBinding) !== 1) {
            throw new InvalidArgumentException('Verified privileged session binding is invalid.');
        }

        $normalized = [];
        foreach ($capabilities as $capability) {
            if (! is_string($capability) || preg_match('/\A[a-z0-9][a-z0-9._-]{2,127}\z/', $capability) !== 1) {
                throw new InvalidArgumentException('Verified privileged capability is invalid.');
            }

            $normalized[$capability] = true;
        }

        $capabilityList = array_keys($normalized);
        sort($capabilityList, SORT_STRING);
        $this->capabilities = array_values($capabilityList);
    }

    public function identityId(): string
    {
        return $this->identityId->value();
    }

    public function isPlatformSuperadmin(): bool
    {
        return $this->platformSuperadmin;
    }

    public function capabilities(): array
    {
        return $this->capabilities;
    }

    public function authenticatedAtUnix(): int
    {
        return $this->authenticatedAtUnix;
    }

    public function sessionBinding(): string
    {
        return $this->sessionBinding;
    }
}
