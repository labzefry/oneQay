<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate;

use App\Application\SystemUpdate\Security\PrivilegedUpdateAuthorization;
use App\Application\SystemUpdate\Security\PrivilegedUpdateCapability;

// Author by Lab | zefry
final readonly class SystemUpdateControlPlane
{
    public function __construct(
        private SystemUpdateFeatureGate $featureGate,
        private SystemUpdateOperationStateStore $stateStore,
        private SystemUpdateReleaseAvailabilityProbe $releaseProbe,
    ) {
    }

    public function status(): SystemUpdateControlPlaneStatus
    {
        return new SystemUpdateControlPlaneStatus(
            $this->featureGate->controlPlaneEnabled(),
            $this->featureGate->installEnabled(),
            $this->stateStore->currentState(),
            $this->stateStore->activeOperationId(),
            SystemUpdateReleaseAvailability::notChecked(),
        );
    }

    public function checkAvailability(): SystemUpdateControlPlaneStatus
    {
        if (! $this->featureGate->controlPlaneEnabled()) {
            throw new SystemUpdateControlPlaneViolation('control_plane_disabled');
        }

        return new SystemUpdateControlPlaneStatus(
            true,
            $this->featureGate->installEnabled(),
            $this->stateStore->currentState(),
            $this->stateStore->activeOperationId(),
            $this->releaseProbe->probe(),
        );
    }

    public function requestInstall(?PrivilegedUpdateAuthorization $authorization): never
    {
        if (! $this->featureGate->controlPlaneEnabled()) {
            throw new SystemUpdateControlPlaneViolation('control_plane_disabled');
        }

        if (! $this->featureGate->installEnabled()) {
            throw new SystemUpdateControlPlaneViolation('install_disabled');
        }

        if ($authorization === null || $authorization->capability() !== PrivilegedUpdateCapability::INSTALL) {
            throw new SystemUpdateControlPlaneViolation('privileged_authorization_required');
        }

        throw new SystemUpdateControlPlaneViolation('activation_not_implemented');
    }
}
