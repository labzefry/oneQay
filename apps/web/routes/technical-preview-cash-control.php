<?php

declare(strict_types=1);

use App\Delivery\Preview\TechnicalPreviewController;
use Illuminate\Support\Facades\Route;

// Author by Lab | zefry
Route::middleware('web')->prefix('technical-preview')->controller(TechnicalPreviewController::class)->group(function (): void {
    Route::post('/shift/open', 'openShift')->name('preview.shift.open');
    Route::post('/shift/close', 'closeShift')->name('preview.shift.close');
    Route::get('/reconciliation', 'reconciliation')->name('preview.reconciliation');
});
