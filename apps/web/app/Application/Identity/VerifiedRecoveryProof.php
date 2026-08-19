<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class VerifiedRecoveryProof
{
    public function __construct(
        private TenantId $tenantId,
        private PlatformIdentityId $identityId,
        private int $provedAtUnix,
    ) {
        if ($this->provedAtUnix <= 0) {
            throw new InvalidArgumentException('Verified recovery proof timestamp is invalid.');
        }
    }

    public function tenantId(): TenantId
    {
        return $this->tenantId;
    }

    public function identityId(): PlatformIdentityId
    {
        return $this->identityId;
    }

    public function provedAtUnix(): int
    {
        return $this->provedAtUnix;
    }
}
