<?php

declare(strict_types=1);

namespace OneQay\Production;

use Throwable;

// Author by Lab | zefry. Never project Production into Local/Test/CI.
final class ProductionBusinessRuntimeGate
{
    public static function allows(string $runtimeClass): bool
    {
        $runtime = strtolower(trim($runtimeClass));
        if (in_array($runtime, ['local', 'test', 'ci'], true)) return true;
        if ($runtime !== 'production' || ! function_exists('config') || ! function_exists('env')
            || ! function_exists('base_path')) return false;

        try {
            $path = dirname(base_path(), 2).DIRECTORY_SEPARATOR.'RELEASE.json';
            if (is_link($path) || ! is_file($path) || ! is_readable($path)) return false;
            $size = filesize($path);
            if (! is_int($size) || $size < 2 || $size > 65536) return false;
            $raw = file_get_contents($path);
            if (! is_string($raw) || strlen($raw) !== $size) return false;
            $release = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
            if (! is_array($release) || array_is_list($release)) return false;

            return self::allowsWithEvidence($runtime, [
                'app_env'=>config('app.env'),
                'app_debug'=>config('app.debug'),
                'configured_runtime'=>config('oneqay.runtime_class'),
                'persistence_enabled'=>config('database.oneqay_persistence_enabled'),
                'session_control_enabled'=>config('oneqay.session_control.enabled'),
                'session_idle_ttl'=>config('oneqay.session_control.idle_ttl_seconds'),
                'session_absolute_ttl'=>config('oneqay.session_control.absolute_ttl_seconds'),
                'activation_enabled'=>filter_var(env('ONEQAY_PRODUCTION_TRANSACTION_ACTIVATION_ENABLED', false), FILTER_VALIDATE_BOOL),
                'traffic_authorized'=>filter_var(env('ONEQAY_PRODUCTION_BUSINESS_TRAFFIC_AUTHORIZED', false), FILTER_VALIDATE_BOOL),
                'environment_id'=>env('ONEQAY_PRODUCTION_ENVIRONMENT_ID', ''),
                'source_commit'=>env('ONEQAY_RUNNING_SOURCE_COMMIT', ''),
                'artifact_sha256'=>env('ONEQAY_RUNNING_ARTIFACT_SHA256', ''),
            ], $release);
        } catch (Throwable) {
            return false;
        }
    }

    /** @param array<string,mixed> $binding @param array<string,mixed> $release */
    public static function allowsWithEvidence(string $runtimeClass, array $binding, array $release): bool
    {
        $runtime = strtolower(trim($runtimeClass));
        if (in_array($runtime, ['local', 'test', 'ci'], true)) return true;
        if ($runtime !== 'production') return false;
        $source = $binding['source_commit'] ?? null;
        $artifact = $binding['artifact_sha256'] ?? null;
        $environment = $binding['environment_id'] ?? null;
        if (! is_string($source) || preg_match('/\A[0-9a-f]{40}\z/', $source) !== 1
            || ! is_string($artifact) || preg_match('/\A[0-9a-f]{64}\z/', $artifact) !== 1
            || ! is_string($environment) || preg_match('/\A[a-z0-9][a-z0-9-]{2,62}\z/', $environment) !== 1) return false;

        return ($binding['app_env'] ?? null) === 'production'
            && ($binding['app_debug'] ?? null) === false
            && ($binding['configured_runtime'] ?? null) === 'production'
            && ($binding['persistence_enabled'] ?? null) === true
            && ($binding['session_control_enabled'] ?? null) === true
            && ($binding['session_idle_ttl'] ?? null) === 7200
            && ($binding['session_absolute_ttl'] ?? null) === 43200
            && ($binding['activation_enabled'] ?? null) === true
            && ($binding['traffic_authorized'] ?? null) === true
            && ($release['environment'] ?? null) === 'PRODUCTION'
            && ($release['required_runtime_class'] ?? null) === 'production'
            && ($release['production'] ?? null) === true
            && ($release['production_data_allowed'] ?? null) === true
            && ($release['business_runtime_activation_ready'] ?? null) === true
            && ($release['source_commit'] ?? null) === $source
            && ($release['release_id'] ?? null) === 'production-'.substr($source, 0, 12);
    }
}
