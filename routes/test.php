<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('test', function (Request $request) {
        (new \App\Services\Shelf\ShelfTempService(21))->create(123);
    });
});
