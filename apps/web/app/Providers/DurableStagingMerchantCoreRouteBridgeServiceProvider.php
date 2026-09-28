<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class DurableStagingMerchantCoreRouteBridgeServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $runtimeClass = strtolower(trim((string) config('oneqay.runtime_class', '')));
        if (! DurableStagingMerchantCoreBridge::armedFor($runtimeClass)) {
            return;
        }

        // Successor delivery fix: bind the existing compatibility bridge directly
        // to Laravel's web middleware group for an explicitly armed durable-staging
        // runtime. This avoids relying on late mutation of the HTTP kernel global
        // middleware stack while preserving the bridge's exact request allowlist,
        // fail-closed runtime projection, and post-request restoration semantics.
        $router->prependMiddlewareToGroup(
            'web',
            DurableStagingMerchantCoreRequestBridge::class,
        );
    }
}
