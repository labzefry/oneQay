<?php

use App\Delivery\Http\HealthController;
use App\Delivery\Preview\TechnicalPreviewController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/health/live', [HealthController::class, 'live'])->name('health.live');
Route::get('/health/ready', [HealthController::class, 'ready'])->name('health.ready');

Route::get('/', static fn () => Inertia::render('Foundation', [
    'headline' => 'oneQay application foundation',
]))->name('foundation');

// Author by Lab | zefry
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
