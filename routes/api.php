<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\ServiceController;
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
    Route::post('token-push', [TokenPushController::class, 'store']);
});
