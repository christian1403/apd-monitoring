<?php

use App\Http\Controllers\CameraController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('camera')
    ->name('camera.')
    ->group(function () {
        Route::get('/', [CameraController::class, 'index'])->name('index');
        Route::post('/', [CameraController::class, 'store'])->name('store');
        Route::put('/{camera}', [CameraController::class, 'update'])->name('update');
        Route::delete('/{camera}', [CameraController::class, 'destroy'])->name('destroy');
        Route::get('/export/{format}', [CameraController::class, 'export'])->name('export');
    });
