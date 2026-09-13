<?php

declare(strict_types=1);

namespace App\Providers;

use App\Delivery\Http\Middleware\HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Kernel as FoundationHttpKernel;
use Illuminate\Support\ServiceProvider;
use LogicException;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeControlPlaneThrottleResponseHardeningServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $kernel = $this->app->make(HttpKernel::class);

        if (! $kernel instanceof FoundationHttpKernel) {
            throw new LogicException('Final Shift Close runtime control-plane throttle response hardening requires the canonical Laravel HTTP kernel.');
        }

        $kernel->pushMiddleware(HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware::class);
    }
}
