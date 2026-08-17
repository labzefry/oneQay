<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\Security\PrivilegedUpdateAuthorization;
use App\Application\SystemUpdate\SystemUpdateActivationResult;
use App\Application\SystemUpdate\SystemUpdatePreparedRelease;

// Author by Lab | zefry
final readonly class SystemUpdateSharedRuntimeEnvironmentActivationCoordinator
{
    public function __construct(
        private SystemUpdateSharedRuntimeEnvironmentGuard $sharedEnvironmentGuard,
        private SystemUpdateSharedConfigurationActivationCoordinator $activationCoordinator,
    ) {
    }

    public function activate(
        SystemUpdatePreparedRelease $release,
        PrivilegedUpdateAuthorization $authorization,
        int $nowUnix,
    ): SystemUpdateActivationResult {
        $this->sharedEnvironmentGuard->assertReady($nowUnix);

        return $this->activationCoordinator->activate($release, $authorization, $nowUnix);
    }
}
