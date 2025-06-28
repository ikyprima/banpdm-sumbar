<?php

use Illuminate\Support\Facades\Route;
use Modules\Dokumen\Http\Controllers\DokumenController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('dokumen', DokumenController::class)->names('dokumen');
});
