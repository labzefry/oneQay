<?php

declare(strict_types=1);

namespace App\Application\Preview;

// Author by Lab | zefry
final class TechnicalPreviewRuntimePolicy
{
    public const DEPLOYED_RUNTIME_CLASS = 'preview';
    public const DEPLOYED_SESSION_DRIVER = 'file';
    public const DEPLOYED_SESSION_COOKIE = 'oneqay-preview-session';
    public const MAX_DEPLOYED_SESSION_LIFETIME_MINUTES = 60;

    private const QUALIFICATION_RUNTIME_CLASSES = ['local', 'test', 'testing', 'ci'];

    public static function permits(
        bool $enabled,
        string $runtimeClass,
        string $sessionDriver,
        int $sessionLifetimeMinutes,
        bool $sessionEncrypted,
        bool $sessionSecure,
        bool $sessionHttpOnly,
        string $sessionSameSite,
        ?string $sessionDomain,
        string $sessionPath,
        string $sessionCookie,
    ): bool {
        if (! $enabled) {
            return false;
        }

        $runtimeClass = strtolower(trim($runtimeClass));
        if (in_array($runtimeClass, self::QUALIFICATION_RUNTIME_CLASSES, true)) {
            return true;
        }

        if ($runtimeClass !== self::DEPLOYED_RUNTIME_CLASS) {
            return false;
        }

        return strtolower(trim($sessionDriver)) === self::DEPLOYED_SESSION_DRIVER
            && $sessionLifetimeMinutes > 0
            && $sessionLifetimeMinutes <= self::MAX_DEPLOYED_SESSION_LIFETIME_MINUTES
            && $sessionEncrypted
            && $sessionSecure
            && $sessionHttpOnly
            && strtolower(trim($sessionSameSite)) === 'lax'
            && $sessionDomain === null
            && $sessionPath === '/'
            && hash_equals(self::DEPLOYED_SESSION_COOKIE, $sessionCookie);
    }
}
