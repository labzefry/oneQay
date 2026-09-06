<?php

declare(strict_types=1);

use App\Delivery\Http\Pos\PosShiftCloseController;
use Illuminate\Support\Facades\Route;

// Author by Lab | zefry
Route::post('/pos/shifts/close', PosShiftCloseController::class)
    ->name('pos.shifts.close');
