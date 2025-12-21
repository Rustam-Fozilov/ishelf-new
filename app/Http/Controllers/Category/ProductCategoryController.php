<?php

namespace App\Http\Controllers\Category;

use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Http\Controllers\Controller;
use App\Services\Product\ProductCategoryService;
use App\Http\Requests\ProductCategory\ListRequest;
use App\Http\Requests\ProductCategory\AddTypeRequest;
use App\Http\Requests\ProductCategory\AddPrintTypeRequest;
use App\Http\Requests\ProductCategory\UploadAttributeRequest;

class ProductCategoryController extends Controller
{
    public function __construct(
        protected ProductCategoryService $service,
    )
    {
    }

    public function list(ListRequest $request)
    {
        $data = $this->service->list($request->validated());
        return new Resource($data);
    }

    public function listPrintType(Request $request)
    {
        $data = $this->service->listPrintType();
        return success($data);
    }

    public function listPriceTag(Request $request)
    {
        $data = $this->service->listPriceTag($request->all());
        return success($data);
    }

    public function show(int $id)
    {
        $data = $this->service->show($id);
        return success($data);
    }

    public function addType(AddTypeRequest $request)
    {
        $this->service->addType($request->validated());
        return success();
    }

    public function addPrintType(AddPrintTypeRequest $request)
    {
        $this->service->addPrintType($request->validated());
        return success();
    }

    public function typeList(Request $request, int $type)
    {
        $data = $this->service->typeList($type, $request->get('status'));
        return success($data);
    }

    public function uploadAttributes(UploadAttributeRequest $request)
    {
        $this->service->uploadAttributes($request->validated());
        return success();
    }
}
