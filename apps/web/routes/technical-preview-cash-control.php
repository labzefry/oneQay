<?php

declare(strict_types=1);

use App\Delivery\Preview\TechnicalPreviewAdjustmentController;
use App\Delivery\Preview\TechnicalPreviewController;
use Illuminate\Support\Facades\Route;

// Author by Lab | zefry
Route::middleware('web')->prefix('technical-preview')->group(function (): void {
    Route::controller(TechnicalPreviewController::class)->group(function (): void {
        Route::post('/shift/open', 'openShift')->name('preview.shift.open');
        Route::post('/shift/close', 'closeShift')->name('preview.shift.close');
        Route::get('/reconciliation', 'reconciliation')->name('preview.reconciliation');
    });

    Route::controller(TechnicalPreviewAdjustmentController::class)->group(function (): void {
        Route::post('/sale/void', 'voidSale')->name('preview.sale.void');
        Route::post('/sale/refund', 'refundCashSale')->name('preview.sale.refund');
    });
});
