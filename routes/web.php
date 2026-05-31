<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

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