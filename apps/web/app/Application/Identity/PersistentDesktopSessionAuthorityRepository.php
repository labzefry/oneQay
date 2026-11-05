<?php
declare(strict_types=1);
namespace App\Application\Identity;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
// Author by Lab | zefry. Native-only issuance capability; browser repository contract stays compatible.
interface PersistentDesktopSessionAuthorityRepository extends FirstPartySessionAuthorityRepository
{
    public function issuePersistentDesktop(
        TenantId $tenantId, PlatformIdentityId $identityId, string $organizationId,
        ?string $outletId, ?string $deviceId, int $credentialEpoch, ?int $factorEpoch,
        string $authorityId, string $publicHandle, int $issuedAtUnix, int $expiresAtUnix,
        string $correlationId,
    ): IssuedFirstPartySessionAuthority;
}
