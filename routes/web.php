<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Banpdm\Http\Controllers\BanpdmController;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route::get('dashboard', function () {
    // return Inertia::render('Dashboard');
   

// })->middleware(['auth', 'verified'])->name('dashboard');
  Route::get('dashboard', [BanpdmController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
