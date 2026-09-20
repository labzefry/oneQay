<?php
declare(strict_types=1);

namespace App\Infrastructure\Runtime;

// Author by Lab | zefry
final class GovernedBusinessRuntimePolicy
{
    private const ALWAYS_ALLOWED = ['local', 'test', 'ci'];
    private const GOVERNED_ALLOWED = ['durable-staging', 'production'];

    public static function allows(mixed $runtimeClass, mixed $governedEnabled): bool
    {
        $runtime = is_string($runtimeClass) ? strtolower(trim($runtimeClass)) : '';

        if (in_array($runtime, self::ALWAYS_ALLOWED, true)) {
            return true;
        }

        return filter_var($governedEnabled, FILTER_VALIDATE_BOOL)
            && in_array($runtime, self::GOVERNED_ALLOWED, true);
    }

    public static function isGovernedRuntime(mixed $runtimeClass): bool
    {
        $runtime = is_string($runtimeClass) ? strtolower(trim($runtimeClass)) : '';
        return in_array($runtime, self::GOVERNED_ALLOWED, true);
    }

    private function __construct() {}
}
