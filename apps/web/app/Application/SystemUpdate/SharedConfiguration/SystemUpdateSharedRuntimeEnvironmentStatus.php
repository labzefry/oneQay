<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;

// Author by Lab | zefry
final readonly class SystemUpdateSharedRuntimeEnvironmentStatus
{
    public const PROFILE = 'PRIVATE_SHARED_DOTENV_V1';

    public function __construct(
        private bool $ready,
        private string $safeCode,
        private bool $appKeyPresent,
        private int $checkedAtUnix,
    ) {
        if ($checkedAtUnix < 1 || preg_match('/\A[a-z0-9_]{3,96}\z/', $safeCode) !== 1) {
            throw new SystemUpdateControlPlaneViolation('shared_runtime_environment_status_invalid');
        }
    }

    public static function ready(int $checkedAtUnix): self
    {
        return new self(true, 'shared_runtime_environment_ready', true, $checkedAtUnix);
    }

    public static function blocked(string $safeCode, bool $appKeyPresent, int $checkedAtUnix): self
    {
        return new self(false, $safeCode, $appKeyPresent, $checkedAtUnix);
    }

    public function isReady(): bool
    {
        return $this->ready;
    }

    public function safeCode(): string
    {
        return $this->safeCode;
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'profile' => self::PROFILE,
            'ready' => $this->ready,
            'code' => $this->safeCode,
            'required_secrets' => [
                'APP_KEY' => $this->appKeyPresent ? 'PRESENT' : 'MISSING',
            ],
            'checked_at_unix' => $this->checkedAtUnix,
        ];
    }
}
