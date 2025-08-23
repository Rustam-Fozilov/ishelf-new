<?php

namespace App\Http\Controllers\RolePerm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\AddRequest;
use App\Http\Requests\Permission\UpdateRequest;
use App\Services\RolePerm\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $service,
    )
    {
    }

    public function list(Request $request)
    {
        $perm = $this->service::getAllow('permission.list');
        if (!$perm) $this->service::forbidden('permission.list');

        $data = $this->service->list();
        return success($data);
    }

    public function show(int $id)
    {
        $perm = $this->service::getAllow('permission.list');
        if (!$perm) $this->service::forbidden('permission.list');

        $data = $this->service::getById($id);
        return success($data);
    }

    public function add(AddRequest $request)
    {
        $this->service->isAllow('permission.add',1,true);
        $this->service->create($request->validated());
        return success();
    }

    public function update(UpdateRequest $request)
    {
        $this->service->isAllow('permission.update',1,true);
        $this->service->update($request['id'], $request->validated());
        return success();
    }

    public function delete(int $id)
    {
        $this->service->isAllow('permission.delete',1,true);
        $this->service->delete($id);
        return success();
    }
}
