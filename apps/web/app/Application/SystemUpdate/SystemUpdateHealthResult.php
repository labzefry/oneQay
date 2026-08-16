<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
final readonly class SystemUpdateHealthResult
{
    private function __construct(
        private bool $healthy,
        private ?string $observedReleaseId,
        private string $safeCode,
    ) {
    }

    public static function healthy(string $observedReleaseId): self
    {
        return new self(true, $observedReleaseId, 'ready');
    }

    public static function unhealthy(string $safeCode, ?string $observedReleaseId = null): self
    {
        if (preg_match('/\A[a-z0-9_]{3,64}\z/', $safeCode) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_health_failure_code');
        }

        return new self(false, $observedReleaseId, $safeCode);
    }

    public function healthyFor(SystemUpdateReleaseIdentity $release): bool
    {
        return $this->healthy
            && is_string($this->observedReleaseId)
            && hash_equals($release->releaseId(), $this->observedReleaseId);
    }

    public function safeCode(): string
    {
        return $this->safeCode;
    }

    public function observedReleaseId(): ?string
    {
        return $this->observedReleaseId;
    }
}
