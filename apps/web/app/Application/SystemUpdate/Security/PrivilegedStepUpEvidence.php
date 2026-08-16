<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Security;

use App\Domain\Identity\PlatformIdentityId;
use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PrivilegedStepUpEvidence
{
    private function __construct(
        private PlatformIdentityId $identityId,
        private string $sessionBinding,
        private int $reauthenticatedAtUnix,
        private int $totpVerifiedAtUnix,
    ) {
    }

    public static function issue(
        string $identityId,
        string $sessionBinding,
        int $reauthenticatedAtUnix,
        int $totpVerifiedAtUnix,
    ): self {
        $binding = strtolower(trim($sessionBinding));

        if (preg_match('/\A[a-f0-9]{64}\z/', $binding) !== 1) {
            throw new InvalidArgumentException('Privileged session binding is invalid.');
        }

        if ($reauthenticatedAtUnix <= 0 || $totpVerifiedAtUnix <= 0) {
            throw new InvalidArgumentException('Privileged step-up timestamps are invalid.');
        }

        return new self(
            PlatformIdentityId::fromString($identityId),
            $binding,
            $reauthenticatedAtUnix,
            $totpVerifiedAtUnix,
        );
    }

    public function identityId(): PlatformIdentityId
    {
        return $this->identityId;
    }

    public function sessionBinding(): string
    {
        return $this->sessionBinding;
    }

    public function reauthenticatedAtUnix(): int
    {
        return $this->reauthenticatedAtUnix;
    }

    public function totpVerifiedAtUnix(): int
    {
        return $this->totpVerifiedAtUnix;
    }
}
