<?php

declare(strict_types=1);

namespace App\Infrastructure\Bootstrap;

use App\Infrastructure\Configuration\CriticalConfiguration;
use RuntimeException;

// Author by Lab | zefry
// CLI-scoped, short-lived, first-merchant-only preactivation. This is not a
// ProductionBusinessRuntimeGate override and never authorizes HTTP business routes.
final class ProductionMerchantProvisioningScope
{
    private static bool $armed = false;
    private static int $expiresAt = 0;

    public static function allows(string $runtimeClass): bool
    {
        return strtolower(trim($runtimeClass)) === 'production'
            && PHP_SAPI === 'cli'
            && self::$armed
            && self::$expiresAt > time();
    }

    /** @param callable():mixed $operation */
    public static function execute(callable $operation): mixed
    {
        if (PHP_SAPI !== 'cli' || self::$armed
            || ! function_exists('config') || ! function_exists('base_path')
            || ! function_exists('env')) {
            throw new RuntimeException('PRODUCTION_MERCHANT_PROVISIONING_DENIED');
        }

        $runtime = strtolower(trim((string) config('oneqay.runtime_class', '')));
        if ($runtime !== 'production'
            || config('app.env') !== 'production'
            || config('app.debug') !== false
            || config('app.url') !== 'https://oneqaydev.n07.my.id'
            || config('database.oneqay_persistence_enabled') !== true
            || ! CriticalConfiguration::isReady([
                'app_key' => config('app.key'),
                'runtime_class' => $runtime,
                'app_debug' => config('app.debug'),
                'app_env' => config('app.env'),
            ])
            || filter_var(env('ONEQAY_PRODUCTION_TRANSACTION_ACTIVATION_ENABLED', false), FILTER_VALIDATE_BOOL)
            || filter_var(env('ONEQAY_PRODUCTION_BUSINESS_TRAFFIC_AUTHORIZED', false), FILTER_VALIDATE_BOOL)) {
            throw new RuntimeException('PRODUCTION_MERCHANT_PROVISIONING_DENIED');
        }

        $releasePath = dirname(base_path(), 2).DIRECTORY_SEPARATOR.'RELEASE.json';
        if (! is_file($releasePath) || is_link($releasePath) || ! is_readable($releasePath)
            || (int) filesize($releasePath) > 65536) {
            throw new RuntimeException('PRODUCTION_MERCHANT_PROVISIONING_DENIED');
        }

        try {
            $release = json_decode((string) file_get_contents($releasePath), true, 32, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            throw new RuntimeException('PRODUCTION_MERCHANT_PROVISIONING_DENIED');
        }

        $source = env('ONEQAY_RUNNING_SOURCE_COMMIT', '');
        $environment = env('ONEQAY_PRODUCTION_ENVIRONMENT_ID', '');
        if (! is_array($release) || array_is_list($release)
            || ($release['environment'] ?? null) !== 'PRODUCTION'
            || ($release['required_runtime_class'] ?? null) !== 'production'
            || ($release['business_runtime_activation_ready'] ?? null) !== true
            || ($release['source_ci_certified'] ?? null) !== true
            || ($release['production_activation'] ?? null) !== 'NOT_AUTHORIZED'
            || ($release['production_traffic_activation'] ?? null) !== 'NOT_AUTHORIZED'
            || ! is_string($source) || preg_match('/\A[0-9a-f]{40}\z/', $source) !== 1
            || ($release['source_commit'] ?? null) !== $source
            || ($release['release_id'] ?? null) !== 'production-'.substr($source, 0, 12)
            || ! is_string($environment)
            || preg_match('/\A[a-z0-9][a-z0-9-]{2,62}\z/', $environment) !== 1) {
            throw new RuntimeException('PRODUCTION_MERCHANT_PROVISIONING_DENIED');
        }

        self::$armed = true;
        self::$expiresAt = time() + 120;
        try {
            return $operation();
        } finally {
            self::$expiresAt = 0;
            self::$armed = false;
        }
    }

    private function __construct() {}
}
