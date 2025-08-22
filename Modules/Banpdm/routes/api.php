<?php

use Illuminate\Support\Facades\Route;
use Modules\Banpdm\Http\Controllers\BanpdmController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('banpdms', BanpdmController::class)->names('banpdm');
});
