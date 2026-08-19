<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

// Author by Lab | zefry
final readonly class PrivilegedStepUpService
{
    public function __construct(
        private VerifyFirstPartyIdentityCredential $credentials,
        private PrivilegedTotpMfaService $mfa,
    ) {}

    public function verify(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] string $password,
        #[\SensitiveParameter] string $code,
    ): int {
        if (! $this->credentials->verify($tenantId, $identityId, $password)) {
            $this->verificationFailed();
        }

        try {
            $verifiedAt = $this->mfa->challenge($tenantId, $identityId, $code);
        } catch (PrivilegedTotpMfaViolation) {
            $this->verificationFailed();
        }

        if (! isset($verifiedAt) || $verifiedAt <= 0) {
            $this->verificationFailed();
        }

        return $verifiedAt;
    }

    private function verificationFailed(): never
    {
        throw new PrivilegedStepUpViolation();
    }
}
