<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface PrivilegedTotpRecoveryRepository
{
    /** @return list<string> */
    public function rotate(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $factorEpoch,
        int $occurredAtUnix,
        string $correlationId,
    ): array;

    /** @return array{tenant_id:string,identity_id:string,code_id:string,factor_epoch:int,proved_at_unix:int} */
    public function consume(
        #[\SensitiveParameter] string $recoveryCode,
        int $occurredAtUnix,
        string $correlationId,
    ): array;

    public function assertProofCurrent(VerifiedPrivilegedTotpRecoveryProof $proof): void;

    public function sealReplacementSecret(
        VerifiedPrivilegedTotpRecoveryProof $proof,
        #[\SensitiveParameter] string $secret,
        int $issuedAtUnix,
    ): string;

    public function openReplacementSecret(
        VerifiedPrivilegedTotpRecoveryProof $proof,
        #[\SensitiveParameter] string $sealedReplacement,
    ): string;

    public function replaceFactor(
        VerifiedPrivilegedTotpRecoveryProof $proof,
        #[\SensitiveParameter] string $newSecret,
        int $matchedTimeStep,
        int $occurredAtUnix,
        string $correlationId,
    ): int;
}
