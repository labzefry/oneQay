<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\SystemUpdate\SystemUpdateFeatureGate;
use App\Application\SystemUpdate\SystemUpdateOperationStateStore;
use App\Application\SystemUpdate\SystemUpdateReleaseAvailabilityProbe;
use App\Delivery\Http\SystemUpdate\DevelopmentUpdateRequestController;
use App\Infrastructure\SystemUpdate\ConfiguredSystemUpdateFeatureGate;
use App\Infrastructure\SystemUpdate\DisabledSystemUpdateOperationStateStore;
use App\Infrastructure\SystemUpdate\UnavailableSystemUpdateReleaseAvailabilityProbe;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class SystemUpdateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Sprint220 configuration is loaded through config/ so Laravel config:cache
        // remains authoritative on cPanel deployments.
        config([
            'oneqay.development_updater' => config('oneqay_development_updater', []),
        ]);

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

    public function boot(): void
    {

        Route::post('/system/update/development/request', DevelopmentUpdateRequestController::class)
            ->middleware(['web', 'throttle:5,1', 'throttle:20,60'])
            ->name('system-update.development.request');
    }
}
