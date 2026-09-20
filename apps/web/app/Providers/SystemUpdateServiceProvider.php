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
        // Sprint220 keeps development/staging updater configuration out of the historical
        // shared config source so legacy application invariants remain byte-for-byte stable.
        config([
            'oneqay.development_updater.enabled' => filter_var(env('ONEQAY_DEVELOPMENT_UPDATER_ENABLED', false), FILTER_VALIDATE_BOOL),
            'oneqay.development_updater.private_root' => env('ONEQAY_DEVELOPMENT_UPDATER_PRIVATE_ROOT', ''),
            'oneqay.development_updater.operator_token_sha256' => env('ONEQAY_DEVELOPMENT_UPDATER_OPERATOR_TOKEN_SHA256', ''),
            'oneqay.development_updater.totp_secret' => env('ONEQAY_DEVELOPMENT_UPDATER_TOTP_SECRET', ''),
            'oneqay.development_updater.request_hmac_key' => env('ONEQAY_DEVELOPMENT_UPDATER_REQUEST_HMAC_KEY', ''),
            'oneqay.development_updater.github_token' => env('ONEQAY_DEVELOPMENT_UPDATER_GITHUB_TOKEN', ''),
            'oneqay.development_updater.release_root' => env('ONEQAY_DEVELOPMENT_UPDATER_RELEASE_ROOT', ''),
            'oneqay.development_updater.active_release_pointer' => env('ONEQAY_DEVELOPMENT_UPDATER_ACTIVE_RELEASE_POINTER', ''),
            'oneqay.development_updater.runtime_env_path' => env('ONEQAY_DEVELOPMENT_UPDATER_RUNTIME_ENV_PATH', ''),
            'oneqay.development_updater.document_root' => env('ONEQAY_DEVELOPMENT_UPDATER_DOCUMENT_ROOT', ''),
            'oneqay.development_updater.document_root_mode' => env('ONEQAY_DEVELOPMENT_UPDATER_DOCUMENT_ROOT_MODE', 'FIXED_PUBLIC_BRIDGE'),
            'oneqay.development_updater.attestation_url' => env('ONEQAY_DEVELOPMENT_UPDATER_ATTESTATION_URL', ''),
            'oneqay.development_updater.attestation_token' => env('ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN', ''),
            'oneqay.development_updater.environment_id' => env('ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID', ''),
            'oneqay.development_updater.running_source_commit' => env('ONEQAY_RUNNING_SOURCE_COMMIT', ''),
            'oneqay.development_updater.running_artifact_sha256' => env('ONEQAY_RUNNING_ARTIFACT_SHA256', ''),
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
