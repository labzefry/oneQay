<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class VerifiedPrivilegedTotpRecoveryProof
{
    public function __construct(
        private TenantId $tenantId,
        private PlatformIdentityId $identityId,
        private string $codeId,
        private int $factorEpoch,
        private int $provedAtUnix,
    ) {
        if (preg_match('/\A[0-9a-f]{32}\z/D', $codeId) !== 1 || $factorEpoch < 0 || $provedAtUnix <= 0) {
            throw new InvalidArgumentException('Privileged TOTP recovery proof is invalid.');
        }
    }

    public function tenantId(): TenantId { return $this->tenantId; }
    public function identityId(): PlatformIdentityId { return $this->identityId; }
    public function codeId(): string { return $this->codeId; }
    public function factorEpoch(): int { return $this->factorEpoch; }
    public function provedAtUnix(): int { return $this->provedAtUnix; }
}
