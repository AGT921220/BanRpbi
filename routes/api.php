<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\ManifestController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceOrderController;
use App\Http\Controllers\Api\TokenPushController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('auth')->group(function () {
    Route::post('/login', LoginController::class);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', LogoutController::class);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('services', [ServiceController::class, 'index']);
    Route::resource('services/order', ServiceOrderController::class);

    Route::resource('manifests', ManifestController::class)
    ->names([
        'index' => 'api.manifests.index',
        'store' => 'api.manifests.store',
        'show' => 'api.manifests.show',
        'update' => 'api.manifests.update',
        'destroy' => 'api.manifests.destroy',
    ]);


    Route::post('token-push', [TokenPushController::class, 'store']);
});
