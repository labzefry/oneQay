<?php

declare(strict_types=1);

namespace App\Application\Identity;

// Author by Lab | zefry
final readonly class IssuedPrivilegedTotpEnrollment
{
    public function __construct(
        #[\SensitiveParameter] private string $secret,
        #[\SensitiveParameter] private string $provisioningUri,
    ) {
        if ($secret === '' || $provisioningUri === '') {
            throw new PrivilegedTotpMfaViolation(
                PrivilegedTotpMfaViolation::STORAGE_FAILURE,
                'Privileged TOTP enrollment material is invalid.',
            );
        }
    }

    public function secret(): string
    {
        return $this->secret;
    }

    public function provisioningUri(): string
    {
        return $this->provisioningUri;
    }
}
