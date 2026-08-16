<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

// Author by Lab | zefry
final readonly class SystemUpdateSharedRuntimeConfiguration
{
    public const PROFILE = 'oneqay.shared-runtime.v1';
    public const LAYOUT_VERSION = 1;

    public function __construct(
        private string $profile,
        private int $layoutVersion,
        private string $runtimeClass,
        private string $appEnvironment,
        private bool $debugEnabled,
        private string $appUrl,
        private string $logChannel,
        private string $sessionDriver,
        private string $cacheStore,
    ) {
    }

    public function profile(): string
    {
        return trim($this->profile);
    }

    public function layoutVersion(): int
    {
        return $this->layoutVersion;
    }

    public function runtimeClass(): string
    {
        return strtolower(trim($this->runtimeClass));
    }

    public function appEnvironment(): string
    {
        return strtolower(trim($this->appEnvironment));
    }

    public function debugEnabled(): bool
    {
        return $this->debugEnabled;
    }

    public function appUrl(): string
    {
        return trim($this->appUrl);
    }

    public function logChannel(): string
    {
        return strtolower(trim($this->logChannel));
    }

    public function sessionDriver(): string
    {
        return strtolower(trim($this->sessionDriver));
    }

    public function cacheStore(): string
    {
        return strtolower(trim($this->cacheStore));
    }

    /** @return array<string, int|string|bool|null> */
    public function toSafeArray(): array
    {
        $scheme = parse_url($this->appUrl(), PHP_URL_SCHEME);

        return [
            'profile' => $this->profile(),
            'layout_version' => $this->layoutVersion(),
            'runtime_class' => $this->runtimeClass(),
            'app_environment' => $this->appEnvironment(),
            'debug_enabled' => $this->debugEnabled(),
            'url_scheme' => is_string($scheme) ? strtolower($scheme) : null,
            'log_channel' => $this->logChannel(),
            'session_driver' => $this->sessionDriver(),
            'cache_store' => $this->cacheStore(),
        ];
    }
}
