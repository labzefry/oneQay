<?php

use App\Delivery\Http\Pos\FinalShiftCloseRuntimeBindingMaterializationController;
use Illuminate\Support\Facades\Route;

// Author by Lab | zefry
Route::post(
    '/internal/final-shift-close/runtime-binding-materialization',
    FinalShiftCloseRuntimeBindingMaterializationController::class,
)->name('internal.final-shift-close.runtime-binding-materialization');
