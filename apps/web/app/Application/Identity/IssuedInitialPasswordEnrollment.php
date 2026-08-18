<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\PlatformIdentityId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class IssuedInitialPasswordEnrollment
{
    public function __construct(
        private InitialPasswordEnrollmentId $enrollmentId,
        private PlatformIdentityId $targetIdentityId,
        #[\SensitiveParameter] private string $enrollmentToken,
        private int $expiresAtUnix,
    ) {
        if ($this->enrollmentToken === '' || strlen($this->enrollmentToken) > 128) {
            throw new InvalidArgumentException('Issued enrollment token is invalid.');
        }

        if ($this->expiresAtUnix <= 0) {
            throw new InvalidArgumentException('Issued enrollment expiration is invalid.');
        }
    }

    public function enrollmentId(): InitialPasswordEnrollmentId
    {
        return $this->enrollmentId;
    }

    public function targetIdentityId(): PlatformIdentityId
    {
        return $this->targetIdentityId;
    }

    public function enrollmentToken(): string
    {
        return $this->enrollmentToken;
    }

    public function expiresAtUnix(): int
    {
        return $this->expiresAtUnix;
    }
}
