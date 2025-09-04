<?php

use App\Http\Controllers\Product\ProductCategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Shelf\ShelfController;
use App\Http\Controllers\Shelf\ShelfStockPriorityController;

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

    Route::group(['prefix' => 'admin'], function () {
        Route::get('branch_sync', [AdminController::class, 'branchSync']);

        Route::group(['prefix' => 'sync_attributes'], function () {
            Route::get('all', [AdminController::class, 'syncAllParams']);
            Route::get('phone', [AdminController::class, 'phone']);
            Route::get('water_heater', [AdminController::class, 'waterHeater']);
            Route::get('refrigerator', [AdminController::class, 'refrigerator']);
            Route::get('air_conditioner', [AdminController::class, 'airConditioner']);
            Route::get('laptop', [AdminController::class, 'laptop']);
            Route::get('tablet', [AdminController::class, 'tablet']);
            Route::get('mono_block', [AdminController::class, 'monoBlock']);
            Route::get('printer', [AdminController::class, 'printer']);
            Route::get('gas_cooker', [AdminController::class, 'gasCooker']);
            Route::get('washing_machine', [AdminController::class, 'washingMachineSync']);
            Route::get('vacuum_cleaner', [AdminController::class, 'vacuumCleaner']);
            Route::get('tv', [AdminController::class, 'tv']);
            Route::get('hood', [AdminController::class, 'hood']);
            Route::get('microwave_oven', [AdminController::class, 'microwaveOven']);
            Route::get('mini_oven', [AdminController::class, 'miniOven']);
            Route::get('freezer', [AdminController::class, 'freezer']);
            Route::get('oven', [AdminController::class, 'oven']);
            Route::get('heater', [AdminController::class, 'heater']);
        });
    });

    Route::group(['prefix' => 'branch'], function () {
        Route::get('list', [BranchController::class, 'list']);
        Route::post('change/status', [BranchController::class, 'changeStatus']);
    });

    Route::group(['prefix' => 'category'], function () {
        Route::get('list', [ProductCategoryController::class, 'list']);
        Route::get('list/print_type', [ProductCategoryController::class, 'listPrintType']);
        Route::get('list/price_tag', [ProductCategoryController::class, 'listPriceTag']);
        Route::get('show/{id}', [ProductCategoryController::class, 'show']);
        Route::post('add/type',[ProductCategoryController::class, 'addType']);
        Route::post('add/print_type', [ProductCategoryController::class, 'addPrintType']);
        Route::get('type/list/{type}', [ProductCategoryController::class, 'typeList']);
        Route::post('upload/attributes', [ProductCategoryController::class, 'uploadAttributes']);
    });
});
