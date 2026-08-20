<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
final readonly class VerifyFirstPartyCredentialEpoch
{
    public function __construct(
        private FirstPartyCredentialEpochRepository $repository,
    ) {}

    public function capture(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
    ): int {
        $epoch = $this->repository->current($tenantId, $identityId);
        if ($epoch < 0) {
            throw new IdentityContextViolation('Credential epoch is invalid.');
        }

        return $epoch;
    }

    public function assertCurrent(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        mixed $sessionEpoch,
    ): void {
        $durableEpoch = $this->capture($tenantId, $identityId);

        if ($sessionEpoch === null) {
            if ($durableEpoch === 0) {
                return;
            }

            throw new IdentityContextViolation('Credential epoch is stale.');
        }

        if (! is_int($sessionEpoch) || $sessionEpoch < 0 || $sessionEpoch !== $durableEpoch) {
            throw new IdentityContextViolation('Credential epoch is stale.');
        }
    }
}
