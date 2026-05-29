<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('items')->group(function () {
    Route::get('/',        [ItemController::class, 'index'])->name('items.index');
    Route::post('/',       [ItemController::class, 'store'])->name('items.store');
    Route::put('/{id}',    [ItemController::class, 'update'])->name('items.update');
    Route::delete('/{id}', [ItemController::class, 'destroy'])->name('items.destroy');

    Route::get('/export/{format?}', [ItemController::class, 'export'])->name('items.export');
});
