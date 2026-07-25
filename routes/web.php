<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');

    Route::resource('users', UserController::class);

    Route::resource('roles', RoleController::class)
        ->except(['show']);
});
