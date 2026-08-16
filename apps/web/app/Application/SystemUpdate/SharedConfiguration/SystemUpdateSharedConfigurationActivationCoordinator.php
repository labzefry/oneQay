<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\Security\PrivilegedUpdateAuthorization;
use App\Application\SystemUpdate\SystemUpdateActivationCoordinator;
use App\Application\SystemUpdate\SystemUpdateActivationResult;
use App\Application\SystemUpdate\SystemUpdatePreparedRelease;

// Author by Lab | zefry
final readonly class SystemUpdateSharedConfigurationActivationCoordinator
{
    public function __construct(
        private SystemUpdateSharedConfigurationGuard $sharedConfigurationGuard,
        private SystemUpdateActivationCoordinator $activationCoordinator,
    ) {
    }

    public function activate(
        SystemUpdatePreparedRelease $release,
        PrivilegedUpdateAuthorization $authorization,
        int $nowUnix,
    ): SystemUpdateActivationResult {
        $this->sharedConfigurationGuard->assertCompatible($release, $nowUnix);

        return $this->activationCoordinator->activate($release, $authorization, $nowUnix);
    }
}
