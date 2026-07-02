<?php

use App\Http\Controllers\Api\DetectionController;
use App\Http\Controllers\Api\SignatureController;
use App\Http\Middleware\ParseJsonFields;
use App\Http\Middleware\VerifyHmacSignature;
use Illuminate\Support\Facades\Route;

// Dev helper — no HMAC auth required, but parse JSON form fields
Route::post('v1/signature/generate', [SignatureController::class, 'generate'])
    ->middleware(ParseJsonFields::class)
    ->name('api.signature.generate');

// Protected routes — VerifyHmacSignature runs first (raw body), then ParseJsonFields decodes
Route::prefix('v1')->middleware([VerifyHmacSignature::class, ParseJsonFields::class])->group(function () {
    Route::post('detection/violation', [DetectionController::class, 'store'])
        ->name('api.detection.violation');
});
