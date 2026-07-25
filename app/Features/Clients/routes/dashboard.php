<?php

use App\Features\Clients\Http\Controllers\Dashboard\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::resource('clients', ClientController::class)
        ->except(['show']);
});
