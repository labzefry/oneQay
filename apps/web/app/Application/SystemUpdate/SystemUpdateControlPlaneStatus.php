<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

// Author by Lab | zefry
final readonly class SystemUpdateControlPlaneStatus
{
    public function __construct(
        private bool $controlPlaneEnabled,
        private bool $installEnabled,
        private SystemUpdateOperationState $state,
        private ?string $operationId,
        private SystemUpdateReleaseAvailability $releaseAvailability,
    ) {
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'control_plane' => $this->controlPlaneEnabled ? 'ENABLED' : 'DISABLED',
            'install' => $this->installEnabled ? 'ENABLED' : 'DISABLED',
            'state' => $this->state->value,
            'active_operation' => $this->operationId !== null,
            'operation_id' => $this->operationId,
            'release_check' => $this->releaseAvailability->toSafeArray(),
            'schema_change_supported' => false,
            'activation_supported' => false,
            'deployment_authorized' => false,
            'attribution' => 'Lab | zefry',
        ];
    }
}
