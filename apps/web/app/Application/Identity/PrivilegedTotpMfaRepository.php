<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface PrivilegedTotpMfaRepository
{
    public function protectedControlRequired(TenantId $tenantId, PlatformIdentityId $identityId): bool;

    public function factorState(TenantId $tenantId, PlatformIdentityId $identityId): PrivilegedTotpMfaState;

    /** Called inside PersistenceTransaction. */
    public function ensurePendingSecret(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] ?string $freshSecret,
        int $createdAtUnix,
    ): string;

    public function pendingSecret(TenantId $tenantId, PlatformIdentityId $identityId): string;

    public function confirmedSecret(TenantId $tenantId, PlatformIdentityId $identityId): string;

    /** Called inside PersistenceTransaction after provider verification. */
    public function confirmPendingStep(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $matchedTimeStep,
        int $confirmedAtUnix,
    ): void;

    /** Called inside PersistenceTransaction after provider verification. */
    public function consumeConfirmedStep(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $matchedTimeStep,
    ): void;
}
