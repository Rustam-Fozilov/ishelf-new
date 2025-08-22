<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RegionController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::get('auth/logout', [AuthController::class, 'logout']);

    Route::get('regions', [RegionController::class, 'list']);
});
