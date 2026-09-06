<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader;
use App\Application\Pos\FinalShiftCloseRuntimeDbBindingAttestation;
use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingTokenMiddleware;
use App\Infrastructure\Pos\LaravelFinalShiftCloseRuntimeDatabaseIdentityReader;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

// Author by Lab | zefry
final class FinalShiftCloseRuntimeDbBindingAttestationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            FinalShiftCloseRuntimeDatabaseIdentityReader::class,
            function ($app): FinalShiftCloseRuntimeDatabaseIdentityReader {
                /** @var Connection $connection */
                $connection = $app->make('db')->connection('oneqay');

                return new LaravelFinalShiftCloseRuntimeDatabaseIdentityReader($connection);
            },
        );

        $this->app->scoped(
            FinalShiftCloseRuntimeDbBindingAttestation::class,
            fn ($app): FinalShiftCloseRuntimeDbBindingAttestation => new FinalShiftCloseRuntimeDbBindingAttestation(
                $app->make(FinalShiftCloseRuntimeDatabaseIdentityReader::class),
                storage_path('app/private/final-shift-close-runtime-binding.json'),
            ),
        );
    }

    public function boot(): void
    {
        if (! $this->deliveryEnabled()) {
            return;
        }

        Route::middleware([
            'throttle:2,1',
            RequireFinalShiftCloseRuntimeBindingTokenMiddleware::class,
        ])->group(base_path('routes/final-shift-close-runtime-db-binding-attestation.php'));
    }

    private function deliveryEnabled(): bool
    {
        // Sprint119 source-readiness only. A separately qualified successor must
        // register this provider and replace this hard deny with a default-off gate.
        return false;
    }
}
