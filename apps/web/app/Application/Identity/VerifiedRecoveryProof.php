<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class VerifiedRecoveryProof
{
    private const CODE_ID_PATTERN = '/\A[0-9a-f]{32}\z/D';

    public function __construct(
        private TenantId $tenantId,
        private PlatformIdentityId $identityId,
        private string $codeId,
        private int $provedAtUnix,
    ) {
        if (preg_match(self::CODE_ID_PATTERN, $this->codeId) !== 1
            || $this->provedAtUnix <= 0) {
            throw new InvalidArgumentException('Verified recovery proof is invalid.');
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

    public function codeId(): string
    {
        return $this->codeId;
    }

    public function provedAtUnix(): int
    {
        return $this->provedAtUnix;
    }
}
