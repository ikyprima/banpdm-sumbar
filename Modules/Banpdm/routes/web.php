<?php

use Illuminate\Support\Facades\Route;
use Modules\Banpdm\Http\Controllers\BanpdmController;
use Modules\Banpdm\Http\Controllers\PenugasanController;
use Modules\Banpdm\Http\Controllers\SekolahController;
use Modules\Banpdm\Http\Controllers\AsesorController;
use Modules\Banpdm\Models\Asesor;

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::resource('banpdm', BanpdmController::class)->names('banpdm');

    route::prefix('banpdm')->group(function () {
        Route::get('/', [BanpdmController::class, 'index'])->name('banpdm.index');
        Route::resource('/penugasan', PenugasanController::class)->names('banpdm.penugasan');
    });

    route::prefix('get-data')->group(function () {
        Route::get('/penugasan', [PenugasanController::class, 'dataPenugasan'])->name('get.data.penugasan');
        Route::get('/sekolah', [SekolahController::class, 'getDataSekolah'])->name('get.data.sekolah');
        Route::get('/asesor', action: [AsesorController::class, 'getDataAsesor'])->name('get.data.asesor');
    });
    route::prefix('post-data')->group(function () {
        Route::post('/penugasan', [PenugasanController::class, 'simpanPenugasan'])->name('post.simpan.penugasan');
    
    });


});
