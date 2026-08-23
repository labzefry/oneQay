<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
interface FirstPartySessionAuthorityRepository
{
    public function issue(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $organizationId,
        ?string $outletId,
        ?string $deviceId,
        int $credentialEpoch,
        ?int $factorEpoch,
        string $authorityId,
        string $publicHandle,
        int $issuedAtUnix,
        int $expiresAtUnix,
        string $correlationId,
    ): IssuedFirstPartySessionAuthority;

    /** @return array<string,mixed>|null */
    public function ownedByAuthorityId(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $authorityId,
    ): ?array;

    /** @return array<string,mixed>|null */
    public function ownedByPublicHandle(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $publicHandle,
    ): ?array;

    /** @return list<array<string,mixed>> */
    public function activeOwned(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $nowUnix,
    ): array;

    public function touch(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $authorityId,
        int $nowUnix,
        int $expiresAtUnix,
    ): void;

    public function revokeOne(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $actorAuthorityId,
        string $targetAuthorityId,
        int $nowUnix,
        string $correlationId,
    ): bool;

    public function revokeOthers(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $actorAuthorityId,
        int $nowUnix,
        string $correlationId,
    ): int;

    public function revokeAll(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $actorAuthorityId,
        int $nowUnix,
        string $correlationId,
    ): int;

    public function revokeCurrent(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        string $authorityId,
        int $nowUnix,
        string $correlationId,
    ): bool;
}
