<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Jobs\Product\ProductSyncJob;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function syncStock(Request $request)
    {
        if (auth()->user()->is_admin == 0) throwError('Permission denied');
        dispatch(new ProductSyncJob());
        return success();
    }
}
