<?php

use App\Delivery\Http\Authorization\PolicyAdministrationController;
use App\Delivery\Http\HealthController;
use App\Delivery\Http\Identity\AuthenticatedPasswordChangeController;
use App\Delivery\Http\Identity\FirstPartySessionController;
use App\Delivery\Http\Identity\InitialPasswordEnrollmentController;
use App\Delivery\Http\Identity\PrivilegedReauthenticationController;
use App\Delivery\Http\Identity\PrivilegedTotpMfaController;
use App\Delivery\Http\Identity\PrivilegedTotpRecoveryController;
use App\Delivery\Http\Identity\RecoveryCodeController;
use App\Delivery\Http\Identity\RecoveryPasswordResetController;
use App\Delivery\Http\Middleware\RequirePolicyAdministrationSessionContextMiddleware;
use App\Delivery\Http\SystemUpdate\SystemUpdateControlPlaneController;
use App\Delivery\Http\SystemUpdate\SystemUpdatePageController;
use App\Delivery\Preview\TechnicalPreviewController;
use App\Delivery\Preview\TechnicalPreviewDatabaseQualificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/health/live', [HealthController::class, 'live'])->name('health.live');
Route::get('/health/ready', [HealthController::class, 'ready'])->name('health.ready');

Route::get('/', static fn () => Inertia::render('Foundation', [
    'headline' => 'oneQay application foundation',
]))->name('foundation');

// Author by Lab | zefry
$firstPartyAuthRuntime = strtolower(trim((string) config('oneqay.runtime_class', '')));
if (in_array($firstPartyAuthRuntime, ['local', 'test', 'ci'], true)) {
    Route::post('/auth/login', [FirstPartySessionController::class, 'login'])
        ->middleware(['throttle:5,1', 'throttle:20,60'])
        ->name('auth.first-party.login');

    Route::post('/auth/logout', [FirstPartySessionController::class, 'logout'])
        ->name('auth.first-party.logout');

    Route::post('/auth/password/change', [AuthenticatedPasswordChangeController::class, 'change'])
        ->middleware(['throttle:5,1', 'throttle:20,60'])
        ->name('auth.password.change');

    Route::post('/auth/password-enrollment', [InitialPasswordEnrollmentController::class, 'redeem'])
        ->middleware(['throttle:5,1', 'throttle:20,60'])
        ->name('auth.initial-password-enrollment.redeem');

    Route::post('/administration/identity/password-enrollments', [InitialPasswordEnrollmentController::class, 'issue'])
        ->middleware(['throttle:5,1', RequirePolicyAdministrationSessionContextMiddleware::class])
        ->name('identity.initial-password-enrollment.issue');

    if ((bool) config('oneqay.authentication_recovery.enabled', false)
        && (int) config('oneqay.authentication_recovery.restricted_session_ttl_seconds', 0) === 600) {
        Route::post('/auth/recovery/codes/rotate', [RecoveryCodeController::class, 'rotate'])
            ->middleware(['throttle:5,1', 'throttle:20,60'])
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
                ->middleware(['throttle:5,1', 'throttle:20,60'])
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

        if ((bool) config('oneqay.privileged_step_up.enabled', false)
            && (int) config('oneqay.privileged_step_up.freshness_seconds', 0) === 300) {
            Route::post('/auth/reauthenticate/privileged', [PrivilegedReauthenticationController::class, 'reauthenticate'])
                ->middleware(['throttle:5,1', 'throttle:20,60'])
                ->name('auth.privileged-step-up.reauthenticate');
        }
    }
}

Route::post('/administration/policy/mutations', PolicyAdministrationController::class)
    ->middleware(RequirePolicyAdministrationSessionContextMiddleware::class)
    ->name('policy-administration.mutate');

Route::get('/system/update', SystemUpdatePageController::class)->name('system-update.page');

Route::prefix('system/update')->controller(SystemUpdateControlPlaneController::class)->group(function (): void {
    Route::get('/status', 'status')->name('system-update.status');
    Route::middleware(['throttle:5,1', 'throttle:20,60'])->group(function (): void {
        Route::post('/check', 'check')->name('system-update.check');
        Route::post('/install', 'install')->name('system-update.install');
    });
});

Route::prefix('technical-preview')->controller(TechnicalPreviewController::class)->group(function (): void {
    Route::get('/', 'index')->name('preview.index');
    Route::post('/sign-in', 'signIn')->name('preview.sign-in');
    Route::get('/context', 'context')->name('preview.context');
    Route::post('/context', 'selectContext')->name('preview.context.select');
    Route::get('/pos', 'pos')->name('preview.pos');
    Route::post('/sale', 'sale')->name('preview.sale');
    Route::get('/receipt', 'receipt')->name('preview.receipt');
    Route::post('/logout', 'logout')->name('preview.logout');
});

Route::get('/technical-preview/database-qualification', TechnicalPreviewDatabaseQualificationController::class)
    ->name('preview.database-qualification');
