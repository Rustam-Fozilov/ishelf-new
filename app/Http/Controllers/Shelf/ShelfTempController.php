<?php

namespace App\Http\Controllers\Shelf;

use App\Http\Controllers\Controller;
use App\Services\Shelf\ShelfTempService;
use App\Http\Requests\AutoOrdering\SaveAutoOrderingRequest;
use App\Http\Requests\ProductShelfTemp\AddProductRequest;
use App\Http\Requests\ProductShelfTemp\AutoOrderingRequest;
use Illuminate\Http\Request;

class ShelfTempController extends Controller
{
    public function __construct(
        protected ShelfTempService $service,
    )
    {
    }

    public function getTempByShelfId(int $shelf_id)
    {
        $data = $this->service->getTempByShelfId($shelf_id);
        return success($data);
    }

    public function tempAddProduct(AddProductRequest $request)
    {
        $this->service->addProduct($request->validated());
        return success();
    }

    public function deleteTempProduct(int $temp_id)
    {
        $this->service->deleteTempProduct($temp_id);
        return success();
    }

    public function makeAutoOrdering(AutoOrderingRequest $request)
    {
        $data = $this->service->autoOrdering($request->validated());
        return success($data);
    }

    public function saveAutoOrderingProps(SaveAutoOrderingRequest $request)
    {
        $this->service->saveAutoOrderingProps($request->validated());
        return success();
    }

    public function deleteAutoOrderingProps(int $shelf_id)
    {
        $this->service->deleteAutoOrderingProps($shelf_id);
        return success();
    }
}
