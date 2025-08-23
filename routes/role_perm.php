<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\RolePerm\PermissionController;
use App\Http\Controllers\RolePerm\RoleController;
use App\Http\Controllers\RolePerm\RolePermController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'role'], function () {
        Route::get('list', [RoleController::class, 'list']);
        Route::get('get/{role_id}', [RoleController::class, 'show']);
        Route::post('add', [RoleController::class, 'add']);
        Route::put('update', [RoleController::class, 'update']);
    });

    Route::group(['prefix' => 'permission'], function () {
        Route::post('add', [PermissionController::class, 'add']);
        Route::put('update', [PermissionController::class, 'update']);
        Route::get('list', [PermissionController::class, 'list']);
        Route::get('get/{id}', [PermissionController::class, 'show']);
        Route::delete('delete/{id}', [PermissionController::class, 'delete']);
    });

    Route::group(['prefix' => 'role_perms'], function () {
        Route::put('save', [RolePermController::class, 'save']);
        Route::get('get_by_permission/{id}', [RolePermController::class, 'getByPermission']);
        Route::get('get_by_role/{id}/{withChildren?}', [RolePermController::class, 'getByRole']);
        Route::get('user_roles/{id?}', [RolePermController::class, 'getUserRoles']);
    });
});
