<?php

declare(strict_types=1);

namespace App\Providers;

use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeBindingMaterializationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Sprint121 source-only control-channel contract.
        // No FinalShiftCloseRuntimeBindingMaterializer implementation is bound here.
    }

    public function boot(): void
    {
        if (! $this->deliveryEnabled()) {
            return;
        }

        Route::middleware([
            'throttle:1,1',
            RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware::class,
        ])->group(base_path('routes/final-shift-close-runtime-binding-materialization.php'));
    }

    private function deliveryEnabled(): bool
    {
        // A separately qualified successor must provide anti-stale materialization
        // semantics, bind the materializer, register this provider, and replace
        // this hard deny with an explicit default-off gate.
        return false;
    }
}
