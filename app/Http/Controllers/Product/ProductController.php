<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ListRequest;
use App\Http\Requests\Product\MakeFamilyRequest;
use App\Http\Requests\Product\ToggleStatusRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\Resource;
use App\Jobs\PriceTag\PriceTagSyncJob;
use App\Jobs\Product\ProductPriceMonthsJob;
use App\Jobs\Product\ProductSyncJob;
use App\Models\PriceTag\PriceTagLog;
use App\Models\Product\ProductPriceLog;
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
        $work = !$last || now()->gte(Carbon::parse($last->created_at)->addMinutes(30));

        if ($work) {
            $this->logService->create($request->all());
            dispatch(new ProductSyncJob());
        }

        $this->logService->deleteExcessLogs();
        return success();
    }

    public function createPriceTag(Request $request)
    {
        $this->logService->createPriceTag($request->all());
        dispatch(new PriceTagSyncJob());

        $this->logService->deleteExcessPriceTagLogs();
        return success();
    }

    public function createPriceMonths(Request $request)
    {
        $this->logService->createProductPrice($request->all());
        dispatch(new ProductPriceMonthsJob());

        $this->logService->deleteExcessProductPriceLogs();
        return success();
    }

    public function list(ListRequest $request)
    {
        $data = $this->service->list($request->validated());
        return new Resource($data);
    }

    public function show(int $id)
    {
        $data = $this->service->show($id, ['attribute', 'category']);
        return success($data);
    }

    public function update(int $id, UpdateRequest $request)
    {
        $this->service->updateAttribute($id, $request->validated());
        return success();
    }

    public function toggleStatus(ToggleStatusRequest $request)
    {
        $this->service->toggleStatus($request->validated());
        return success();
    }

    public function family(MakeFamilyRequest $request)
    {
        $this->service->family($request->validated());
        return success();
    }
}
