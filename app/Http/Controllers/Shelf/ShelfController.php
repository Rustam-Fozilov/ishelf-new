<?php

namespace App\Http\Controllers\Shelf;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shelf\AddRequest;
use App\Http\Requests\Shelf\ListRequest;
use App\Http\Resources\Resource;
use App\Services\RolePerm\PermissionService;
use App\Services\Shelf\ShelfService;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
    public function __construct(
        protected ShelfService $service,
        protected PermissionService $permissionService,
    )
    {
    }

    public function list(ListRequest $request)
    {
        $this->permissionService->hasPermission('shelf.list');
        $data = $this->service->list($request->validated());
        return new Resource($data);
    }

    public function add(AddRequest $request)
    {
        $this->permissionService->isAllow('shelf.add', 1, true);
        $this->service->add($request->validated());
        return success();
    }
}
