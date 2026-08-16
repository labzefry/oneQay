<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\Security;

// Author by Lab | zefry
final class PrivilegedUpdateSecurityPolicy
{
    public const SESSION_MAX_AGE_SECONDS = 900;
    public const STEP_UP_MAX_AGE_SECONDS = 300;
    public const FUTURE_CLOCK_SKEW_SECONDS = 30;
    public const TOTP_PERIOD_SECONDS = 30;
    public const TOTP_DIGITS = 6;
    public const TOTP_WINDOW_STEPS = 1;
    public const RATE_LIMIT_PER_MINUTE = 5;
    public const RATE_LIMIT_PER_HOUR = 20;

    private function __construct()
    {
    }

    public static function timestampIsFresh(int $timestamp, int $now, int $maximumAgeSeconds): bool
    {
        if ($timestamp <= 0 || $now <= 0 || $maximumAgeSeconds < 0) {
            return false;
        }

        if ($timestamp > $now + self::FUTURE_CLOCK_SKEW_SECONDS) {
            return false;
        }

        return ($now - $timestamp) <= $maximumAgeSeconds;
    }
}
