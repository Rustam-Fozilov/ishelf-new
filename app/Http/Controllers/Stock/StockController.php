<?php

namespace App\Http\Controllers\Stock;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\Product\ProductSyncJob;

class StockController extends Controller
{
    public function syncStock(Request $request)
    {
        if (auth()->user()->is_admin == 0) throwError('Permission denied');
        dispatch(new ProductSyncJob());
        return success();
    }
}
