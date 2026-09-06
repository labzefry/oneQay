<?php

declare(strict_types=1);

use App\Delivery\Http\Pos\PosShiftCloseController;
use App\Delivery\Http\Pos\PosShiftClosePageController;
use Illuminate\Support\Facades\Route;

// Author by Lab | zefry
Route::get('/pos/shifts/close', PosShiftClosePageController::class)
    ->name('pos.shifts.close.page');

Route::post('/pos/shifts/close', PosShiftCloseController::class)
    ->name('pos.shifts.close');
