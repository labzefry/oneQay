<?php

declare(strict_types=1);

namespace App\Infrastructure\Installation;

/**
 * Deterministic, read-only installation readiness assessment.
 *
 * Author by Lab | zefry
 */
final class SecureInstallationReadiness
{
    /** @var list<string> */
    private const REQUIRED_EXTENSIONS = ['ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring', 'openssl', 'pdo', 'session', 'tokenizer', 'xml'];

    /** @var list<string> */
    private const REQUIRED_ENVIRONMENT_KEYS = ['APP_ENV', 'APP_KEY', 'APP_URL', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];

    /**
     * @param array<string, mixed> $environment
     * @param list<string>|null $loadedExtensions
     * @return array{ready: bool, checks: array<string, array{ready: bool, reason: string}>}
     */
    public function assess(array $environment, ?array $loadedExtensions = null, ?string $phpVersion = null): array
    {
        $extensions = array_map('strtolower', $loadedExtensions ?? get_loaded_extensions());
        $version = $phpVersion ?? PHP_VERSION;
        $checks = [];

        $checks['php_version'] = $this->check(
            version_compare($version, '8.2.0', '>='),
            'PHP runtime satisfies the minimum supported version.',
            'PHP runtime is below the minimum supported version.'
        );

        $missingExtensions = array_values(array_filter(
            self::REQUIRED_EXTENSIONS,
            static fn (string $extension): bool => ! in_array($extension, $extensions, true)
        ));
        $checks['php_extensions'] = $this->check(
            $missingExtensions === [],
            'Required PHP extensions are available.',
            'Required PHP extensions are missing: '.implode(', ', $missingExtensions).'.'
        );

        $missingEnvironment = array_values(array_filter(
            self::REQUIRED_ENVIRONMENT_KEYS,
            static fn (string $key): bool => ! isset($environment[$key]) || trim((string) $environment[$key]) === ''
        ));
        $checks['environment'] = $this->check(
            $missingEnvironment === [],
            'Required installation configuration is present.',
            'Required installation configuration is incomplete: '.implode(', ', $missingEnvironment).'.'
        );

        $appKey = trim((string) ($environment['APP_KEY'] ?? ''));
        $checks['application_key'] = $this->check(
            $appKey !== '' && ! in_array(strtolower($appKey), ['changeme', 'base64:changeme'], true),
            'Application key is configured.',
            'Application key is absent or uses a prohibited placeholder.'
        );

        $appEnvironment = strtolower(trim((string) ($environment['APP_ENV'] ?? '')));
        $appDebug = filter_var($environment['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
        $checks['production_debug'] = $this->check(
            $appEnvironment !== 'production' || $appDebug === false,
            'Debug posture is acceptable for the selected environment.',
            'Production installation cannot proceed while debug mode is enabled.'
        );

        $url = trim((string) ($environment['APP_URL'] ?? ''));
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $checks['production_https'] = $this->check(
            $appEnvironment !== 'production' || $scheme === 'https',
            'Application URL transport posture is acceptable.',
            'Production installation requires an HTTPS application URL.'
        );

        return [
            'ready' => ! in_array(false, array_column($checks, 'ready'), true),
            'checks' => $checks,
        ];
    }

    /** @return array{ready: bool, reason: string} */
    private function check(bool $ready, string $success, string $failure): array
    {
        return ['ready' => $ready, 'reason' => $ready ? $success : $failure];
    }
}
