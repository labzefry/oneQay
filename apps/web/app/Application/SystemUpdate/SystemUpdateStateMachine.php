<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
final class SystemUpdateStateMachine
{
    /** @var array<string, list<string>> */
    private const ALLOWED = [
        'IDLE' => ['CHECKING'],
        'CHECKING' => ['AVAILABLE', 'FAILED', 'IDLE'],
        'AVAILABLE' => ['DOWNLOADING', 'IDLE'],
        'DOWNLOADING' => ['VERIFYING', 'FAILED'],
        'VERIFYING' => ['STAGED', 'FAILED'],
        'STAGED' => ['PREFLIGHTING', 'FAILED'],
        'PREFLIGHTING' => ['READY_TO_APPLY', 'FAILED'],
        'READY_TO_APPLY' => ['APPLYING_SHARED_CONFIGURATION', 'FAILED'],
        'APPLYING_SHARED_CONFIGURATION' => ['PREPARING_PUBLIC_SURFACE', 'FAILED'],
        'PREPARING_PUBLIC_SURFACE' => ['SWITCHING', 'FAILED'],
        'SWITCHING' => ['VERIFYING_HEALTH', 'ROLLING_BACK', 'FAILED'],
        'VERIFYING_HEALTH' => ['SUCCEEDED', 'ROLLING_BACK', 'FAILED'],
        'SUCCEEDED' => ['IDLE'],
        'ROLLING_BACK' => ['ROLLED_BACK', 'FAILED'],
        'ROLLED_BACK' => ['IDLE'],
        'FAILED' => ['IDLE'],
    ];

    public function allows(SystemUpdateOperationState $from, SystemUpdateOperationState $to): bool
    {
        return in_array($to->value, self::ALLOWED[$from->value] ?? [], true);
    }

    public function assertAllowed(SystemUpdateOperationState $from, SystemUpdateOperationState $to): void
    {
        if (! $this->allows($from, $to)) {
            throw new SystemUpdateControlPlaneViolation('invalid_state_transition');
        }
    }
}
