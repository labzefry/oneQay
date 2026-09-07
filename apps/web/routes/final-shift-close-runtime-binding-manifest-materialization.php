<?php

declare(strict_types=1);

use App\Delivery\Http\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationController;
use Illuminate\Support\Facades\Route;

// Author by Lab | zefry
Route::post('/internal/final-shift-close/runtime-binding-manifest/materialize', FinalShiftCloseRuntimeBindingManifestMaterializationController::class)
    ->name('internal.final-shift-close.runtime-binding-manifest.materialize');
