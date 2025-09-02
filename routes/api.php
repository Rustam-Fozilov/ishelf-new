<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\Shelf\ShelfController;
use App\Http\Controllers\Shelf\ShelfStockPriorityController;
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

    Route::group(['prefix' => 'shelf'], function () {
        Route::get('list', [ShelfController::class, 'list']);
        Route::get('get/{id}', [ShelfController::class, 'getById']);
        Route::post('add', [ShelfController::class, 'add']);
        Route::put('update/{id}', [ShelfController::class, 'update']);
        Route::delete('delete/skus', [ShelfController::class, 'deleteSkus']);
        Route::delete('delete/{id}', [ShelfController::class, 'delete']);
        Route::post('update/phone/table', [ShelfController::class, 'updatePhoneTable']);
    });

    Route::group(['prefix' => 'priority'], function() {
        Route::get('shelf/get/{shelf_id}', [ShelfStockPriorityController::class, 'get']);
        Route::post('shelf/add/{shelf_id}', [ShelfStockPriorityController::class, 'add']);
        Route::delete('shelf/delete/{shelf_id}', [ShelfStockPriorityController::class, 'delete']);
    });
});
