<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface RecoveryCodeRepository
{
    /**
     * Called only inside PersistenceTransaction.
     *
     * @return list<string> Eight one-time plaintext recovery codes.
     */
    public function rotate(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $occurredAtUnix,
        string $correlationId,
    ): array;

    /**
     * Called only inside PersistenceTransaction.
     *
     * @return array{tenant_id:string,identity_id:string,proved_at_unix:int}
     */
    public function consume(
        #[\SensitiveParameter] string $recoveryCode,
        int $occurredAtUnix,
        string $correlationId,
    ): array;
}
