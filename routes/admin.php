<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\ManifestHeadersController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\Api\ClientHeaderController;
use App\Http\Controllers\Dashboard\ManifestController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {

Route::group(['prefix' => 'admin'], function (): void {
Route::get('manifest-headers', [ManifestHeadersController::class, 'index'])
        ->name('manifest-headers.index');
        
Route::get('manifests/{id}', [ManifestController::class, 'show'])
        ->name('manifests.show');
    
});


    Route::resource('users', UserController::class);

    Route::resource('roles', RoleController::class)
        ->except(['show']);

    Route::resource('clients', ClientController::class)
        ->except(['show']);

    Route::get('clients/{client}/configuration', [ClientController::class, 'showConfiguration'])
        ->name('clients.configuration.show');
    Route::put('clients/{client}/configuration', [ClientController::class, 'saveConfiguration'])
        ->name('clients.configuration.save');
    Route::post('clients/{client}/configuration/submit', [ClientController::class, 'submitConfiguration'])
        ->name('clients.configuration.submit');

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
