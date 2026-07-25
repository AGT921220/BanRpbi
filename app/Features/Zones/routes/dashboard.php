<?php

use App\Features\Zones\Http\Controllers\Dashboard\ZoneController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::patch(
        'zones/{zone}/toggle-status',
        [ZoneController::class, 'toggleStatus']
    )->name('zones.toggle-status');

    Route::resource('zones', ZoneController::class)
        ->except(['show']);
});
