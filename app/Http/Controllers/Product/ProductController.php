<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ListRequest;
use App\Http\Resources\Resource;
use App\Jobs\Product\ProductSyncJob;
use App\Services\Product\ProductLogService;
use App\Services\Product\ProductService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $service,
        protected ProductLogService $logService
    )
    {
    }

    public function productLog(Request $request)
    {
        $last = $this->logService->last();
        $work = !$last || Carbon::now()->gte(Carbon::parse($last->created_at)->addMinutes(30));

        if ($work && !empty($request->all())) {
            $this->logService->create($request->all());
            dispatch(new ProductSyncJob());
        }

        $this->logService->deleteExcessLogs();
        return success();
    }

    public function list(ListRequest $request)
    {
        $data = $this->service->list($request->all());
        return new Resource($data);
    }

    public function show(int $id)
    {

    }
}
