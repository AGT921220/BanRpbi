<?php

use App\Features\Contracts\Http\Controllers\Dashboard\ContractController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::resource('contracts', ContractController::class)
        ->except(['show']);
});
