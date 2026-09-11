<?php

declare(strict_types=1);

namespace App\Application\Pos;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeControlPlaneTokenPolicy
{
    public const MINIMUM_LENGTH = 32;

    public const MAXIMUM_LENGTH = 512;

    private const ALLOWED_CHARACTER_PATTERN = '/\A[A-Za-z0-9._~+=\/-]+\z/D';

    private const BEARER_PREFIX = 'Bearer ';

    public static function isValidToken(mixed $token): bool
    {
        if (! is_string($token)) {
            return false;
        }

        $length = strlen($token);

        return $length >= self::MINIMUM_LENGTH
            && $length <= self::MAXIMUM_LENGTH
            && preg_match(self::ALLOWED_CHARACTER_PATTERN, $token) === 1;
    }

    public static function parseBearerCredential(mixed $authorization): ?string
    {
        if (! is_string($authorization) || ! str_starts_with($authorization, self::BEARER_PREFIX)) {
            return null;
        }

        $token = substr($authorization, strlen(self::BEARER_PREFIX));

        return self::isValidToken($token) ? $token : null;
    }
}
