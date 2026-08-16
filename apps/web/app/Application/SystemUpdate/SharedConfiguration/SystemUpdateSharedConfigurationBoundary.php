<?php

declare(strict_types=1);

namespace App\Application\SystemUpdate\SharedConfiguration;

use App\Application\SystemUpdate\SystemUpdateControlPlaneViolation;
use App\Application\SystemUpdate\SystemUpdatePreparedRelease;

// Author by Lab | zefry
final readonly class SystemUpdateSharedConfigurationBoundary implements SystemUpdateSharedConfigurationGuard
{
    public function __construct(
        private SystemUpdateSharedConfigurationSource $source,
        private SystemUpdateSecretPresenceProbe $secretProbe,
        private SystemUpdateSharedConfigurationPolicy $policy,
        private SystemUpdateSharedConfigurationMetadataStore $metadataStore,
    ) {
    }

    public function assertCompatible(
        SystemUpdatePreparedRelease $release,
        int $nowUnix,
    ): SystemUpdateSharedConfigurationCompatibility {
        $compatibility = $this->policy->evaluate(
            $this->source->snapshot(),
            $this->secretProbe,
            $nowUnix,
        );

        $this->metadataStore->record($release, $compatibility);

        if (! $compatibility->compatible()) {
            throw new SystemUpdateControlPlaneViolation($compatibility->safeCode());
        }

        return $compatibility;
    }
}
