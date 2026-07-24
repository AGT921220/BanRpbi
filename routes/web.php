<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');

    Route::resource('users', UserController::class)
        ->except(['show']);
});
