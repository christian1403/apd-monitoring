<?php

use App\Http\Controllers\DetectionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('detection')
    ->name('detection.')
    ->group(function () {
        Route::get('/', [DetectionController::class, 'index'])->name('index');
        Route::post('/', [DetectionController::class, 'store'])->name('store');
        Route::put('/{detection}', [DetectionController::class, 'update'])->name('update');
        Route::delete('/{detection}', [DetectionController::class, 'destroy'])->name('destroy');
    });
