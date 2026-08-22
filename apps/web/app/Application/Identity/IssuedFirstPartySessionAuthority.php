<?php

declare(strict_types=1);

namespace App\Application\Identity;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class IssuedFirstPartySessionAuthority
{
    public function __construct(
        private string $authorityId,
        private string $publicHandle,
    ) {
        if (preg_match('/\A[0-9a-f]{32}\z/D', $authorityId) !== 1
            || preg_match('/\A[A-Za-z0-9_-]{43}\z/D', $publicHandle) !== 1) {
            throw new InvalidArgumentException('Issued session authority material is invalid.');
        }
    }

    public function authorityId(): string
    {
        return $this->authorityId;
    }

    public function publicHandle(): string
    {
        return $this->publicHandle;
    }
}
