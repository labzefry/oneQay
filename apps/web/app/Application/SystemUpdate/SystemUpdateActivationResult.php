<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
final readonly class SystemUpdateActivationResult
{
    public function __construct(
        private SystemUpdateOperationState $terminalState,
        private SystemUpdateReleaseIdentity $activeRelease,
        private string $safeCode,
    ) {
        if (! in_array($terminalState, [SystemUpdateOperationState::SUCCEEDED, SystemUpdateOperationState::ROLLED_BACK], true)) {
            throw new SystemUpdateControlPlaneViolation('invalid_activation_result');
        }

        if (preg_match('/\A[a-z0-9_]{3,64}\z/', $safeCode) !== 1) {
            throw new SystemUpdateControlPlaneViolation('invalid_activation_result');
        }
    }

    public function terminalState(): SystemUpdateOperationState
    {
        return $this->terminalState;
    }

    public function activeRelease(): SystemUpdateReleaseIdentity
    {
        return $this->activeRelease;
    }

    public function safeCode(): string
    {
        return $this->safeCode;
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'state' => $this->terminalState->value,
            'active_release' => $this->activeRelease->toSafeArray(),
            'safe_code' => $this->safeCode,
        ];
    }
}
