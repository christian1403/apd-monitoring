<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'signed'])->prefix('files')->group(function () {
    Route::get('/{path}', [FileController::class, 'show'])
        ->name('files.show')
        ->where('path', '.*');
});
