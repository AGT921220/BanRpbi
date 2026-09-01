<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\DriverHeadersController;
use App\Http\Controllers\Dashboard\DriverController;
use App\Http\Controllers\Dashboard\InvoiceController;
use App\Http\Controllers\Dashboard\ManifestController;
use App\Http\Controllers\Dashboard\ServiceController;
use App\Http\Controllers\Dashboard\TestController;
use Illuminate\Support\Facades\Route;

Route::resource('test', TestController::class)->only(['index']);

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

    Route::get('driver-headers', [DriverHeadersController::class, 'index'])
        ->name('driver-headers.index');

    Route::resource('drivers', DriverController::class)
        ->except(['show']);

    Route::get('services/assign', [ServiceController::class, 'assign'])
        ->name('services.assign');
    Route::post('services/assign', [ServiceController::class, 'storeAssignment'])
        ->name('services.assign.store');
    Route::resource('services', ServiceController::class)
        ->only(['index']);

    Route::get('invoices/create', [InvoiceController::class, 'create'])
        ->name('invoices.create');
    Route::resource('invoices', InvoiceController::class)
        ->only(['index']);
});
