<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\SystemUpdate\SystemUpdateFeatureGate;
use App\Application\SystemUpdate\SystemUpdateOperationStateStore;
use App\Application\SystemUpdate\SystemUpdateReleaseAvailabilityProbe;
use App\Infrastructure\SystemUpdate\ConfiguredSystemUpdateFeatureGate;
use App\Infrastructure\SystemUpdate\DisabledSystemUpdateOperationStateStore;
use App\Infrastructure\SystemUpdate\UnavailableSystemUpdateReleaseAvailabilityProbe;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class SystemUpdateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            SystemUpdateFeatureGate::class,
            static fn (): SystemUpdateFeatureGate => new ConfiguredSystemUpdateFeatureGate(
                (bool) config('oneqay.system_update.control_plane_enabled', false),
                (bool) config('oneqay.system_update.install_enabled', false),
            ),
        );

        $this->app->scoped(
            SystemUpdateOperationStateStore::class,
            static fn (): SystemUpdateOperationStateStore => new DisabledSystemUpdateOperationStateStore(),
        );

        $this->app->scoped(
            SystemUpdateReleaseAvailabilityProbe::class,
            static fn (): SystemUpdateReleaseAvailabilityProbe => new UnavailableSystemUpdateReleaseAvailabilityProbe(),
        );
    }
}
