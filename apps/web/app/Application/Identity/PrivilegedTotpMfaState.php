<?php

declare(strict_types=1);

namespace App\Application\Identity;

use InvalidArgumentException;

// Author by Lab | zefry
final readonly class PrivilegedTotpMfaState
{
    public const ABSENT = 'absent';
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';

    private const ALLOWED = [
        self::ABSENT,
        self::PENDING,
        self::CONFIRMED,
    ];

    public function __construct(public string $value)
    {
        if (! in_array($value, self::ALLOWED, true)) {
            throw new InvalidArgumentException('Privileged TOTP MFA state is invalid.');
        }
    }

    public function is(string $state): bool
    {
        return hash_equals($this->value, $state);
    }
}
