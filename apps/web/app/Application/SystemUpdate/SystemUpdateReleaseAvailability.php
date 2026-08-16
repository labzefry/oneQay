<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
final readonly class SystemUpdateReleaseAvailability
{
    public function __construct(
        private string $status,
        private ?string $releaseId = null,
        private ?string $version = null,
        private ?string $channel = null,
        private ?string $sourceCommit = null,
    ) {
        if (! in_array($status, ['NOT_CHECKED', 'UNAVAILABLE', 'AVAILABLE'], true)) {
            throw new SystemUpdateControlPlaneViolation('invalid_release_availability');
        }
    }

    public static function notChecked(): self
    {
        return new self('NOT_CHECKED');
    }

    public static function unavailable(): self
    {
        return new self('UNAVAILABLE');
    }

    /** @return array<string, string|null> */
    public function toSafeArray(): array
    {
        return [
            'status' => $this->status,
            'release_id' => $this->releaseId,
            'version' => $this->version,
            'channel' => $this->channel,
            'source_commit' => $this->sourceCommit,
        ];
    }
}
