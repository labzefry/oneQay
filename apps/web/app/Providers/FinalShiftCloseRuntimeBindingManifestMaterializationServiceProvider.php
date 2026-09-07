<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;
use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware;
use App\Infrastructure\Pos\FilesystemFinalShiftCloseRuntimeBindingManifestWriter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeBindingManifestMaterializationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            FinalShiftCloseRuntimeBindingManifestWriter::class,
            fn (): FinalShiftCloseRuntimeBindingManifestWriter => new FilesystemFinalShiftCloseRuntimeBindingManifestWriter(
                storage_path('app/private/final-shift-close-runtime-binding.json'),
            ),
        );

        $this->app->scoped(
            FinalShiftCloseRuntimeBindingManifestMaterializer::class,
            fn ($app): FinalShiftCloseRuntimeBindingManifestMaterializer => new FinalShiftCloseRuntimeBindingManifestMaterializer(
                $app->make(FinalShiftCloseRuntimeBindingManifestWriter::class),
                base_path('../ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json'),
            ),
        );

        $this->app->when(RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware::class)
            ->needs('$expectedToken')
            ->give(fn (): string => (string) config('oneqay.final_shift_close_runtime_binding_materialization_token', ''));
    }

    public function boot(): void
    {
        if (! $this->deliveryEnabled()) {
            return;
        }

        Route::middleware([
            'throttle:1,1',
            RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware::class,
        ])->group(base_path('routes/final-shift-close-runtime-binding-manifest-materialization.php'));
    }

    private function deliveryEnabled(): bool
    {
        // Sprint121 source-readiness only. A separately qualified successor must
        // register this provider and replace this hard deny with a default-off gate.
        return false;
    }
}
