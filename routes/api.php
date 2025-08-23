<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::get('auth/logout', [AuthController::class, 'logout']);

    Route::get('regions', [RegionController::class, 'list']);

    Route::group(['prefix' => 'user'], function () {
        Route::get('pinfl/{pinfl}', [UserController::class, 'getInfoByPinfl']);
        Route::get('list', [UserController::class, 'list']);
        Route::get('get/{id}', [UserController::class, 'getById']);
        Route::post('add', [UserController::class, 'add']);
        Route::patch('change_password', [UserController::class, 'changePassword']);
        Route::patch('change_phone', [UserController::class, 'changePhone']);
        Route::put('update/{id}', [UserController::class, 'update']);
        Route::put('toggle/status/{id}', [UserController::class, 'toggleStatus']);
        Route::get('categories', [UserController::class, 'categories']);
        Route::get('branches', [UserController::class, 'branches']);
        Route::delete('delete/{id}', [UserController::class, 'delete']);
    });
});
