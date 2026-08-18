<?php

use App\Delivery\Http\Authorization\PolicyAdministrationController;
use App\Delivery\Http\HealthController;
use App\Delivery\Http\Identity\FirstPartySessionController;
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

Route::get(
    '/technical-preview/database-qualification',
    TechnicalPreviewDatabaseQualificationController::class,
)->name('preview.database-qualification');
