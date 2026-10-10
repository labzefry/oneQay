<?php

namespace App\Infrastructure\Configuration;

final class CriticalConfiguration
{
    /**
     * @param array{app_key:mixed,runtime_class:mixed,app_debug:mixed,app_env:mixed} $values
     * @return list<string>
     */
    public static function violations(array $values): array
    {
        $violations = [];

        $appKey = is_string($values['app_key'] ?? null) ? trim($values['app_key']) : '';
        if ($appKey === '' || str_contains($appKey, 'REPLACE_WITH_')) {
            $violations[] = 'app_key';
        }

        $runtimeClass = is_string($values['runtime_class'] ?? null)
            ? strtolower(trim($values['runtime_class']))
            : '';

        if (! in_array($runtimeClass, ['local', 'test', 'ci', 'preview', 'production'], true)) {
            $violations[] = 'runtime_class';
        }

        $debug = filter_var($values['app_debug'] ?? false, FILTER_VALIDATE_BOOL);
        $environment = is_string($values['app_env'] ?? null)
            ? strtolower(trim($values['app_env']))
            : '';

        if ($debug && ! in_array($environment, ['local', 'testing'], true)) {
            $violations[] = 'app_debug';
        }

        // Production readiness is configuration readiness, NOT permission to
        // activate merchant traffic. ProductionBusinessRuntimeGate remains the
        // separate deny-by-default authorization and release-source gate.
        if ($runtimeClass === 'production') {
            $decodedKey = str_starts_with($appKey, 'base64:')
                ? base64_decode(substr($appKey, 7), true)
                : false;
            if ($environment !== 'production'
                || $debug
                || ! is_string($decodedKey)
                || strlen($decodedKey) !== 32) {
                $violations[] = 'runtime_class';
            }
        }

        return array_values(array_unique($violations));
    }

    /**
     * @param array{app_key:mixed,runtime_class:mixed,app_debug:mixed,app_env:mixed} $values
     */
    public static function isReady(array $values): bool
    {
        return self::violations($values) === [];
    }

    private function __construct()
    {
    }
}
