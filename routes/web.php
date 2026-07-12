<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/items.php';
require __DIR__.'/fileManager.php';
require __DIR__.'/location.php';
require __DIR__.'/camera.php';
require __DIR__.'/detection.php';

// Zone config routes (authenticated via web/session)
Route::middleware(['auth', 'verified'])->prefix('api')->group(function () {
    Route::get('camera/{camera}/zone/config', [\App\Http\Controllers\Api\ZoneConfigController::class, 'show'])->name('api.zone.config.show');
    Route::post('camera/{camera}/zone/config', [\App\Http\Controllers\Api\ZoneConfigController::class, 'store'])->name('api.zone.config.store');
});
