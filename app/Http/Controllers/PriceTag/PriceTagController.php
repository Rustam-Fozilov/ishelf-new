<?php

namespace App\Http\Controllers\PriceTag;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriceTag\ListRequest;
use App\Http\Requests\PriceTag\PrintRequest;
use App\Http\Resources\Resource;
use App\Services\PriceTag\PriceTagService;
use App\Services\RolePerm\PermissionService;
use Illuminate\Http\Request;

class PriceTagController extends Controller
{
    public function __construct(
        protected PriceTagService $service,
        protected PermissionService $permissionService,
    )
    {
    }

    public function list(ListRequest $request)
    {
        $this->permissionService->hasPermission('priceTag.list');
        $data = $this->service->list($request->validated());
        return new Resource($data);
    }

    public function print(PrintRequest $request)
    {
        $this->service->print($request->validated());
        return success();
    }
}
