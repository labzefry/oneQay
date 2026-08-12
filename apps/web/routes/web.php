<?php

use App\Delivery\Http\HealthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/health/live', [HealthController::class, 'live'])->name('health.live');
Route::get('/health/ready', [HealthController::class, 'ready'])->name('health.ready');

Route::get('/', static fn () => Inertia::render('Foundation', [
    'headline' => 'oneQay application foundation',
]))->name('foundation');
