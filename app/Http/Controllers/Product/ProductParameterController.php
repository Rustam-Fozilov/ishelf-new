<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductParameter\ListRequest;
use App\Http\Requests\ProductParameter\UpdateRequest;
use App\Services\Product\ProductParametersService;
use App\Services\RolePerm\PermissionService;
use Illuminate\Http\Request;

class ProductParameterController extends Controller
{
    public function __construct(
        protected ProductParametersService $service,
        protected PermissionService $permissionService,
    )
    {
    }

    public function list(ListRequest $request)
    {
        $this->permissionService->isAllow('characters.list', 1, true);
        $data = $this->service->list($request->validated());
        return success($data);
    }

    public function update(UpdateRequest $request)
    {
        $this->permissionService->isAllow('characters.edit', 1, true);
        $this->service->update($request->validated());
        return success();
    }
}
