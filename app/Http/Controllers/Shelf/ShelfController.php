<?php

namespace App\Http\Controllers\Shelf;

use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use App\Services\Shelf\ShelfService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shelf\AddRequest;
use App\Http\Requests\Shelf\ListRequest;
use App\Services\Shelf\PhoneShelfService;
use App\Services\RolePerm\PermissionService;
use App\Http\Requests\Shelf\DeleteSkusRequest;
use App\Http\Requests\Shelf\UpdatePhoneTableRequest;
use App\Http\Requests\Shelf\UploadPhoneImageRequest;

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
        $this->permissionService->hasPermission('shelf.get');
        $data = $this->service->getById($id);
        return success($data);
    }

    public function add(AddRequest $request)
    {
        $this->permissionService->hasPermission('shelf.add');
        $this->service->add($request->validated());
        return success();
    }

    public function update(int $id, AddRequest $request)
    {
        $this->permissionService->hasPermission('shelf.update');
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
        $this->permissionService->hasPermission('shelf.delete');
        $this->service->delete($id);
        return success();
    }

    public function updatePhoneTable(UpdatePhoneTableRequest $request)
    {
        $this->permissionService->hasPermission('shelf.change_phone');
        $this->service->updatePhoneTable($request->validated());
        return success();
    }

    public function saveOrderingProduct(int $shelf_id)
    {
        $this->service->moveToProduct($shelf_id);
        return success();
    }

    public function orderingProductList(int $shelf_id)
    {
        $data = $this->service->orderingProductList($shelf_id);
        return success($data);
    }

    public function uploadImageToPhone(UploadPhoneImageRequest $request)
    {
        $this->permissionService->hasPermission('shelf.upload_image');
        $this->service->uploadImageToPhone($request->validated());
        return success();
    }

    public function addStartPointToPhone(int $table_id, Request $request)
    {
        PhoneShelfService::addStartPoint($table_id, $request['start_point']);
    }

    public function getParameters(Request $request)
    {
        $request->validate(['shelf_id' => 'required|integer|exists:shelves,id']);
        $data = $this->service->getParameters($request->all());
        return success($data);
    }
}
