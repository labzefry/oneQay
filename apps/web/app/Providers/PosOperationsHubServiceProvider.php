<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Pos\ViewPosCashierWorkspace;
use App\Application\Pos\ViewPosCatalogInventorySetupWorkspace;
use App\Application\Pos\ViewPosMerchantOperationsReadiness;
use App\Application\Pos\ViewPosOperationsHub;
use App\Application\Pos\ViewPosShiftStartWorkspace;
use App\Delivery\Http\Identity\AuthenticatedPasswordChangeController;
use App\Delivery\Http\Identity\FirstPartySessionController;
use App\Delivery\Http\Identity\PrivilegedTotpMfaController;
use App\Delivery\Http\Identity\PrivilegedTotpRecoveryController;
use App\Delivery\Http\Identity\RecoveryCodeController;
use App\Delivery\Http\Identity\RecoveryPasswordResetController;
use App\Delivery\Http\Middleware\RequirePosSessionContextMiddleware;
use App\Delivery\Http\Pos\PosCashierWorkspaceController;
use App\Delivery\Http\Pos\PosCatalogInventorySetupWorkspaceController;
use App\Delivery\Http\Pos\PosCatalogPreparationController;
use App\Delivery\Http\Pos\PosInventoryBaselineController;
use App\Delivery\Http\Pos\PosOperationsHubController;
use App\Delivery\Http\Pos\PosSaleController;
use App\Delivery\Http\Pos\PosShiftOpeningCashController;
use App\Delivery\Http\Pos\PosShiftOpeningController;
use App\Delivery\Http\Pos\PosShiftStartWorkspaceController;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

// Author by Lab | zefry
final class PosOperationsHubServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Sprint198 makes the existing POS operations hub the single guarded
        // delivery aggregate for all already-qualified business workspaces.
        // Every child provider retains its own Local/Test/CI, persistence,
        // session-control, feature-flag, and permission gates.
        $this->app->register(PosCatalogInventorySetupWorkspaceServiceProvider::class);
        $this->app->register(PosShiftStartWorkspaceServiceProvider::class);
        $this->app->register(PosCashierWorkspaceServiceProvider::class);
        $this->app->register(PosSaleCorrectionWorkspaceServiceProvider::class);
        $this->app->register(PosOperationalReportingServiceProvider::class);
        $this->app->register(PosCashVarianceReconciliationWorkspaceServiceProvider::class);

        // Shift history performance is a bounded read-only reporting child capability.
        $this->app->register(PosShiftHistoryPerformanceWorkspaceServiceProvider::class);

        // Active shift performance is a bounded read-only reporting child capability.
        $this->app->register(PosActiveShiftPerformanceWorkspaceServiceProvider::class);

        // Product sales performance is a bounded read-only reporting child capability.
        $this->app->register(PosProductSalesPerformanceWorkspaceServiceProvider::class);

        // Inventory accountability is a bounded read-only POS operations child capability.
        $this->app->register(PosInventoryAccountabilityWorkspaceServiceProvider::class);

        // Inventory replenishment is a bounded POS operations child capability.
        // Its provider owns independent fail-closed delivery and mutation gates.
        $this->app->register(PosInventoryReplenishmentWorkspaceServiceProvider::class);

        $this->app->scoped(ViewPosOperationsHub::class, fn ($app): ViewPosOperationsHub => new ViewPosOperationsHub(
            $app->make(OrganizationalContextStore::class),
            $app->make(DurableScopedAuthorizationPolicy::class),
        ));

        $this->app->scoped(ViewPosMerchantOperationsReadiness::class, fn ($app): ViewPosMerchantOperationsReadiness => new ViewPosMerchantOperationsReadiness(
            $app->make(ViewPosCatalogInventorySetupWorkspace::class),
            $app->make(ViewPosShiftStartWorkspace::class),
            $app->make(ViewPosCashierWorkspace::class),
        ));

        if ($this->app->runningInConsole()) {
            $this->commands([DurableStagingMerchantContextBootstrapBridgeCommand::class]);
        }
    }

    public function boot(): void
    {
        $runtimeClass = strtolower(trim((string) config('oneqay.runtime_class', '')));
        $sessionControlEnabled = $this->sessionControlEnabled();

        if (in_array($runtimeClass, ['local', 'test', 'ci'], true)
            && (bool) config('database.oneqay_persistence_enabled', false)
            && $sessionControlEnabled
            && (bool) config('pos_operations_hub.enabled', false)) {
            Route::get('/pos', PosOperationsHubController::class)
                ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.operations.hub');
        }

        if (! $this->stagingCoreDeliveryEnabled($runtimeClass, $sessionControlEnabled)) {
            return;
        }

        // Sprint203 bridge is globally installed only for an explicitly armed staging
        // process. The middleware itself changes effective runtime only for the exact
        // merchant-core request allowlist and restores it after every request.
        $this->app->make(HttpKernel::class)
            ->prependMiddleware(DurableStagingMerchantCoreRequestBridge::class);

        $this->registerStagingMerchantCoreRoutes();
    }

    private function stagingCoreDeliveryEnabled(string $runtimeClass, bool $sessionControlEnabled): bool
    {
        return DurableStagingMerchantCoreBridge::armedFor($runtimeClass)
            && (bool) config('database.oneqay_persistence_enabled', false)
            && $sessionControlEnabled
            && $this->merchantEntryContextComplete()
            && (bool) config('pos_operations_hub.enabled', false)
            && (bool) config('pos_catalog_inventory_setup.enabled', false)
            && (bool) config('oneqay.pos_catalog_preparation.enabled', false)
            && (bool) config('oneqay.pos_inventory_baseline.enabled', false)
            && (bool) config('pos_shift_start_workspace.enabled', false)
            && (bool) config('oneqay.pos_shift_opening.enabled', false)
            && (bool) config('oneqay.pos_shift_opening_cash_evidence.enabled', false)
            && (bool) config('pos_cashier_workspace.enabled', false)
            && (bool) config('oneqay.pos_sale_completion.enabled', false);
    }

    private function sessionControlEnabled(): bool
    {
        return (bool) config('oneqay.session_control.enabled', false)
            && (int) config('oneqay.session_control.idle_ttl_seconds', 0) === 7200
            && (int) config('oneqay.session_control.absolute_ttl_seconds', 0) === 43200;
    }

    private function merchantEntryContextComplete(): bool
    {
        $grant = config('merchant_context_bootstrap.grant', []);
        if (! is_array($grant)) {
            return false;
        }

        foreach (['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id'] as $key) {
            $value = $grant[$key] ?? null;
            if (! is_string($value) || $value === '' || trim($value) !== $value) {
                return false;
            }
        }

        return true;
    }

    private function registerStagingMerchantCoreRoutes(): void
    {
        Route::middleware('web')->group(function (): void {
            Route::post('/auth/login', [FirstPartySessionController::class, 'login'])
                ->middleware(['throttle:5,1', 'throttle:20,60'])
                ->name('auth.first-party.login');

            Route::post('/auth/logout', [FirstPartySessionController::class, 'logout'])
                ->middleware('session.active')
                ->name('auth.first-party.logout');

            Route::post('/auth/password/change', [AuthenticatedPasswordChangeController::class, 'change'])
                ->middleware(['session.active', 'throttle:5,1', 'throttle:20,60'])
                ->name('auth.password.change');

            if ((bool) config('oneqay.authentication_recovery.enabled', false)
                && (int) config('oneqay.authentication_recovery.restricted_session_ttl_seconds', 0) === 600) {
                Route::post('/auth/recovery/codes/rotate', [RecoveryCodeController::class, 'rotate'])
                    ->middleware(['session.active', 'throttle:5,1', 'throttle:20,60'])
                    ->name('auth.recovery.codes.rotate');

                Route::post('/auth/recovery/proof', [RecoveryCodeController::class, 'proof'])
                    ->middleware(['throttle:5,1', 'throttle:20,60'])
                    ->name('auth.recovery.proof');

                Route::post('/auth/recovery/password-reset', [RecoveryPasswordResetController::class, 'reset'])
                    ->middleware(['throttle:5,1', 'throttle:20,60'])
                    ->name('auth.recovery.password-reset');
            }

            if ((bool) config('oneqay.privileged_totp_mfa.enabled', false)) {
                Route::post('/auth/mfa/totp/enrollment/start', [PrivilegedTotpMfaController::class, 'startEnrollment'])
                    ->middleware(['throttle:5,1', 'throttle:20,60'])
                    ->name('auth.privileged-totp.enrollment.start');

                Route::post('/auth/mfa/totp/enrollment/confirm', [PrivilegedTotpMfaController::class, 'confirmEnrollment'])
                    ->middleware(['throttle:5,1', 'throttle:20,60'])
                    ->name('auth.privileged-totp.enrollment.confirm');

                Route::post('/auth/mfa/totp/challenge', [PrivilegedTotpMfaController::class, 'challenge'])
                    ->middleware(['throttle:5,1', 'throttle:20,60'])
                    ->name('auth.privileged-totp.challenge');

                if ((bool) config('oneqay.authentication_recovery.enabled', false)
                    && (int) config('oneqay.authentication_recovery.restricted_session_ttl_seconds', 0) === 600) {
                    Route::post('/auth/mfa/recovery/codes/rotate', [PrivilegedTotpRecoveryController::class, 'rotate'])
                        ->middleware(['session.active', 'throttle:5,1', 'throttle:20,60'])
                        ->name('auth.privileged-totp-recovery.codes.rotate');

                    Route::post('/auth/mfa/recovery/proof', [PrivilegedTotpRecoveryController::class, 'proof'])
                        ->middleware(['throttle:5,1', 'throttle:20,60'])
                        ->name('auth.privileged-totp-recovery.proof');

                    Route::post('/auth/mfa/recovery/totp/replace/start', [PrivilegedTotpRecoveryController::class, 'startReplacement'])
                        ->middleware(['throttle:5,1', 'throttle:20,60'])
                        ->name('auth.privileged-totp-recovery.replace.start');

                    Route::post('/auth/mfa/recovery/totp/replace/confirm', [PrivilegedTotpRecoveryController::class, 'confirmReplacement'])
                        ->middleware(['throttle:5,1', 'throttle:20,60'])
                        ->name('auth.privileged-totp-recovery.replace.confirm');
                }
            }

            Route::post('/pos/catalog/preparation', PosCatalogPreparationController::class)
                ->middleware(['session.active', 'throttle:20,1', 'throttle:200,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.catalog.prepare');

            Route::post('/pos/inventory/baseline', PosInventoryBaselineController::class)
                ->middleware(['session.active', 'throttle:20,1', 'throttle:200,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.inventory.baseline');

            Route::post('/pos/shifts/open', PosShiftOpeningController::class)
                ->middleware(['session.active', 'throttle:10,1', 'throttle:100,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.shifts.open');

            Route::post('/pos/shifts/opening-cash', PosShiftOpeningCashController::class)
                ->middleware(['session.active', 'throttle:10,1', 'throttle:100,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.shifts.opening-cash');

            Route::post('/pos/sales', PosSaleController::class)
                ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.sales.complete');

            Route::get('/pos/catalog-inventory/setup', PosCatalogInventorySetupWorkspaceController::class)
                ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.catalog-inventory.setup');

            Route::get('/pos/shift-start', PosShiftStartWorkspaceController::class)
                ->middleware(['session.active', 'throttle:10,1', 'throttle:100,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.shift-start.workspace');

            Route::get('/pos/cashier', PosCashierWorkspaceController::class)
                ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.cashier.workspace');

            Route::get('/pos', PosOperationsHubController::class)
                ->middleware(['session.active', 'throttle:30,1', 'throttle:300,60', RequirePosSessionContextMiddleware::class])
                ->name('pos.operations.hub');
        });
    }
}

// Sprint203 keeps the external runtime as "staging" and only projects the
// already-qualified Local/Test/CI compatibility class inside the exact merchant
// core request/command boundary. Production and unknown runtimes never qualify.
final class DurableStagingMerchantCoreBridge
{
    public static function armedFor(string $runtimeClass): bool
    {
        return strtolower(trim($runtimeClass)) === 'staging'
            && filter_var(
                env('ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED', false),
                FILTER_VALIDATE_BOOL,
            );
    }
}

final class DurableStagingMerchantCoreRequestBridge
{
    /** @var list<string> */
    private const ALLOWED_REQUESTS = [
        'GET /',
        'POST /auth/login',
        'POST /auth/logout',
        'POST /auth/password/change',
        'POST /auth/recovery/codes/rotate',
        'POST /auth/recovery/proof',
        'POST /auth/recovery/password-reset',
        'POST /auth/mfa/totp/enrollment/start',
        'POST /auth/mfa/totp/enrollment/confirm',
        'POST /auth/mfa/totp/challenge',
        'POST /auth/mfa/recovery/codes/rotate',
        'POST /auth/mfa/recovery/proof',
        'POST /auth/mfa/recovery/totp/replace/start',
        'POST /auth/mfa/recovery/totp/replace/confirm',
        'GET /pos',
        'GET /pos/catalog-inventory/setup',
        'GET /pos/shift-start',
        'GET /pos/cashier',
        'POST /pos/catalog/preparation',
        'POST /pos/inventory/baseline',
        'POST /pos/shifts/open',
        'POST /pos/shifts/opening-cash',
        'POST /pos/sales',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $runtimeClass = (string) config('oneqay.runtime_class', '');
        if (! DurableStagingMerchantCoreBridge::armedFor($runtimeClass)
            || ! in_array($this->requestKey($request), self::ALLOWED_REQUESTS, true)) {
            return $next($request);
        }

        $originalRuntime = config('oneqay.runtime_class');
        $request->attributes->set('oneqay.external_runtime_class', 'staging');
        $request->attributes->set('oneqay.runtime_compatibility_bridge', 'merchant-core-ci');

        config(['oneqay.runtime_class' => 'ci']);

        try {
            return $next($request);
        } finally {
            config(['oneqay.runtime_class' => $originalRuntime]);
        }
    }

    private function requestKey(Request $request): string
    {
        $path = trim($request->path(), '/');
        $normalizedPath = $path === '' ? '/' : '/'.$path;

        return strtoupper($request->method()).' '.$normalizedPath;
    }
}

final class DurableStagingMerchantContextBootstrapBridgeCommand extends Command
{
    /** @var string */
    protected $signature = 'oneqay:merchant-context:bootstrap-staging';

    /** @var string */
    protected $description = 'Bridge the existing guarded merchant bootstrap into an explicitly armed non-production staging runtime.';

    public function handle(): int
    {
        $runtimeClass = (string) config('oneqay.runtime_class', '');
        if (! DurableStagingMerchantCoreBridge::armedFor($runtimeClass)) {
            return $this->failClosed();
        }

        $originalRuntime = config('oneqay.runtime_class');
        config(['oneqay.runtime_class' => 'ci']);

        try {
            $result = $this->call('oneqay:merchant-context:bootstrap');
            if ($result !== self::SUCCESS) {
                return $this->failClosed();
            }

            $this->line('ONEQAY_DURABLE_STAGING_MERCHANT_CONTEXT_BOOTSTRAP|STATE=applied');

            return self::SUCCESS;
        } catch (Throwable) {
            return $this->failClosed();
        } finally {
            config(['oneqay.runtime_class' => $originalRuntime]);
        }
    }

    private function failClosed(): int
    {
        $this->error('ONEQAY_DURABLE_STAGING_MERCHANT_CONTEXT_BOOTSTRAP_FAILED');

        return self::FAILURE;
    }
}
