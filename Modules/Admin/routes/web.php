<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\DatabaseController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('admin', '/admin/dashboards');
    Route::group(['prefix' => 'admin'], function () {
        Route::resource('dashboards', AdminController::class)->names('admin.dashboard');
        Route::resource('database-manager', DatabaseController::class)->names('admin.database-manager');
        Route::get('list-table', [DatabaseController::class, 'listTable'])->name('admin.list-table');
    });
    
});
