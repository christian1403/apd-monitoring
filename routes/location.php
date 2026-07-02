<?php

use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('location')->group(function () {
    Route::get('/', [LocationController::class, 'index'])->name('location.index');
    Route::post('/', [LocationController::class, 'store'])->name('location.store');
    Route::put('/{id}', [LocationController::class, 'update'])->name('location.update');
    Route::delete('/{id}', [LocationController::class, 'destroy'])->name('location.destroy');

    Route::get('/export/{format?}', [LocationController::class, 'export'])->name('location.export');
});
