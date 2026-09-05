<?php

declare(strict_types=1);

use App\Delivery\Preview\TechnicalPreviewController;
use App\Delivery\Preview\TechnicalPreviewDatabaseQualificationController;
use Illuminate\Support\Facades\Route;

// Author by Lab | zefry
Route::middleware('web')->prefix('technical-preview')->group(function (): void {
    Route::controller(TechnicalPreviewController::class)->group(function (): void {
        Route::get('/', 'index')->name('preview.index');
        Route::post('/sign-in', 'signIn')->name('preview.sign-in');
        Route::get('/context', 'context')->name('preview.context');
        Route::post('/context', 'selectContext')->name('preview.context.select');
        Route::get('/pos', 'pos')->name('preview.pos');
        Route::post('/sale', 'sale')->name('preview.sale');
        Route::get('/receipt', 'receipt')->name('preview.receipt');
        Route::post('/logout', 'logout')->name('preview.logout');
    });

    Route::get('/database-qualification', TechnicalPreviewDatabaseQualificationController::class)
        ->name('preview.database-qualification');
});
