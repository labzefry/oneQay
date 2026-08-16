<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
final readonly class SystemUpdateActiveReleasePointer
{
    public function __construct(
        private SystemUpdateReleaseIdentity $active,
        private ?SystemUpdateReleaseIdentity $previousStable,
        private int $activatedAtUnix,
    ) {
        if ($activatedAtUnix <= 0) {
            throw new SystemUpdateControlPlaneViolation('invalid_pointer_timestamp');
        }

        if ($previousStable !== null && $active->equals($previousStable)) {
            throw new SystemUpdateControlPlaneViolation('invalid_pointer_history');
        }
    }

    public function active(): SystemUpdateReleaseIdentity
    {
        return $this->active;
    }

    public function previousStable(): ?SystemUpdateReleaseIdentity
    {
        return $this->previousStable;
    }

    public function activatedAtUnix(): int
    {
        return $this->activatedAtUnix;
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'pointer_version' => 1,
            'active' => $this->active->toSafeArray(),
            'previous_stable' => $this->previousStable?->toSafeArray(),
            'activated_at_unix' => $this->activatedAtUnix,
            'attribution' => 'Lab | zefry',
        ];
    }
}
