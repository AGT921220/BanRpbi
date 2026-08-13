<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Dashboard\ManifestController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');


    Route::get('approvals', [ApprovalController::class, 'index'])
        ->name('approvals.index');
    Route::post('approvals/{client}/approve', [ApprovalController::class, 'approve'])
        ->name('approvals.approve');
    Route::post('approvals/{client}/reject', [ApprovalController::class, 'reject'])
        ->name('approvals.reject');

        Route::resource('manifests', ManifestController::class)->only(['index']);
});
