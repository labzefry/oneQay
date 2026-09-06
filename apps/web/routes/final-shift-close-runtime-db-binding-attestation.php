<?php

declare(strict_types=1);

use App\Delivery\Http\Pos\FinalShiftCloseRuntimeDbBindingAttestationController;
use Illuminate\Support\Facades\Route;

// Author by Lab | zefry
Route::get(
    '/internal/final-shift-close/runtime-db-binding-attestation',
    FinalShiftCloseRuntimeDbBindingAttestationController::class,
)->name('internal.final-shift-close.runtime-db-binding-attestation');
