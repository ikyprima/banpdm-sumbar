<?php

use Illuminate\Support\Facades\Route;
use Modules\Dokumen\Http\Controllers\DokumenController;
use Modules\Dokumen\Http\Controllers\KategoriController;
use Modules\Dokumen\Http\Controllers\FileController;
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dokumen', [DokumenController::class, 'index'])->name('dokumen.index');
    Route::group(['prefix' => 'dokumen'], function () {
        Route::resource('kategori', KategoriController::class)->names('kategori');
        Route::get('/data-kategori',[KategoriController::class,'dataKategori'])->name('data.kategori');
        Route::resource('file', FileController::class)->names('file');
        Route::get('/data-file',[FileController::class,'dataFile'])->name('data.file');
        Route::get('/data-preview-file',[FileController::class,'previewFile'])->name('data.preview.file');
    });
});
