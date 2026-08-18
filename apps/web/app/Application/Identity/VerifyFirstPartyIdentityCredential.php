<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
final readonly class VerifyFirstPartyIdentityCredential
{
    private const MAX_PASSWORD_BYTES = 4096;

    public function __construct(private FirstPartyIdentityCredentialVerifier $verifier) {}

    public function verify(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] string $password,
    ): bool {
        if ($password === '' || strlen($password) > self::MAX_PASSWORD_BYTES) {
            return false;
        }

        return $this->verifier->verify($tenantId, $identityId, $password);
    }
}
