<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\Api\ClientHeaderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::resource('users', UserController::class);

    Route::resource('roles', RoleController::class)
        ->except(['show']);

    Route::resource('clients', ClientController::class)
        ->except(['show']);

    Route::get('client-headers', [ClientHeaderController::class, 'index'])
        ->name('client-headers.index');

    Route::patch(
        'zones/{zone}/toggle-status',
        [ZoneController::class, 'toggleStatus']
    )->name('zones.toggle-status');

    Route::resource('zones', ZoneController::class)
        ->except(['show']);

    Route::resource('contracts', ContractController::class)
        ->except(['show']);
});
