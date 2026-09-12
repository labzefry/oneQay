<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;
use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
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
            ->give(fn (): string => (string) config(
                'final_shift_close_runtime_binding_materialization.token',
                '',
            ));
    }

    public function boot(): void
    {
        if (! $this->deliveryEnabled()) {
            return;
        }

        Route::middleware([
            RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware::class,
            'throttle:1,1',
        ])->group(base_path('routes/final-shift-close-runtime-binding-manifest-materialization.php'));
    }

    private function deliveryEnabled(): bool
    {
        if ((bool) config('final_shift_close_runtime_binding_materialization.enabled', false) !== true) {
            return false;
        }

        return FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken(
            config('final_shift_close_runtime_binding_materialization.token', ''),
        );
    }
}
