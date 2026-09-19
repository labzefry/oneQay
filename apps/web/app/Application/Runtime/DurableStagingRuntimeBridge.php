<?php

declare(strict_types=1);

namespace App\Application\Runtime;

// Author by Lab | zefry
final class DurableStagingRuntimeBridge
{
    /** @var list<string> */
    private const LEGACY_ALLOWED = ['local', 'test', 'ci'];

    public static function deliveryAllowed(string $runtimeClass, bool $stagingArmed): bool
    {
        $runtime = self::normalize($runtimeClass);

        return in_array($runtime, self::LEGACY_ALLOWED, true)
            || ($runtime === 'staging' && $stagingArmed);
    }

    public static function repositoryRuntimeClass(string $runtimeClass, bool $stagingArmed): string
    {
        $runtime = self::normalize($runtimeClass);

        if (in_array($runtime, self::LEGACY_ALLOWED, true)) {
            return $runtime;
        }

        if ($runtime === 'staging' && $stagingArmed) {
            // Legacy repositories use the runtime value only as a non-production
            // allowlist capability token. Preserve their historical source contract
            // while the external runtime remains explicitly "staging".
            return 'ci';
        }

        return 'denied';
    }

    public static function externalRuntime(string $runtimeClass): string
    {
        return self::normalize($runtimeClass);
    }

    private static function normalize(string $runtimeClass): string
    {
        return strtolower(trim($runtimeClass));
    }
}
