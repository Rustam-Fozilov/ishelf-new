<?php

namespace App\Http\Controllers\Shelf;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shelf\AddRequest;
use App\Http\Requests\Shelf\DeleteSkusRequest;
use App\Http\Requests\Shelf\ListRequest;
use App\Http\Requests\Shelf\UpdatePhoneTableRequest;
use App\Http\Resources\Resource;
use App\Models\Shelf\Shelf;
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

    public function getById(int $id)
    {
        $this->permissionService->isAllow('shelf.get', 1, true);
        $data = $this->service->getById($id);
        return success($data);
    }

    public function add(AddRequest $request)
    {
        $this->permissionService->isAllow('shelf.add', 1, true);
        $this->service->add($request->validated());
        return success();
    }

    public function update(int $id, AddRequest $request)
    {
        $this->permissionService->isAllow('shelf.update', 1, true);
        $this->service->update($id, $request->validated());
        return success();
    }

    public function deleteSkus(DeleteSkusRequest $request)
    {
        $this->service->deleteSkus($request->validated());
        return success();
    }

    public function delete(int $id)
    {
        $this->permissionService->isAllow('shelf.delete', 1, true);
        $this->service->delete($id);
        return success();
    }

    public function updatePhoneTable(UpdatePhoneTableRequest $request)
    {
        $this->permissionService->isAllow('shelf.change_phone', 1, true);
        $this->service->updatePhoneTable($request->validated());
        return success();
    }

    public function saveOrderingProduct(int $shelf_id)
    {
        $this->service->moveToProduct($shelf_id);
        return success();
    }
}
