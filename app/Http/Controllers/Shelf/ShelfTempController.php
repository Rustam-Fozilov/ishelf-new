<?php

namespace App\Http\Controllers\Shelf;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductShelfTemp\AddProductRequest;
use App\Services\Shelf\ShelfTempService;
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
}
