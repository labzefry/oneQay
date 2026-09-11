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

        $this->app->when(RequireFinalShiftCloseRuntimeBindingTokenMiddleware::class)
            ->needs('$expectedToken')
            ->give(fn (): string => (string) config(
                'final_shift_close_runtime_db_binding_attestation.token',
                '',
            ));
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
        if ((bool) config('final_shift_close_runtime_db_binding_attestation.enabled', false) !== true) {
            return false;
        }

        $token = config('final_shift_close_runtime_db_binding_attestation.token', '');

        return is_string($token)
            && strlen($token) >= 32
            && strlen($token) <= 512
            && preg_match('/\A[A-Za-z0-9._~+=\/-]{32,512}\z/D', $token) === 1;
    }
}
