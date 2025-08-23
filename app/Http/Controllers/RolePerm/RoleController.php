<?php

namespace App\Http\Controllers\RolePerm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\AddRequest;
use App\Http\Requests\Role\ListRequest;
use App\Http\Requests\Role\UpdateRequest;
use App\Services\RolePerm\PermissionService;
use App\Services\RolePerm\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $service,
        protected PermissionService $permissionService
    )
    {
    }

    public function list(ListRequest $request)
    {
        $this->permissionService->isAllow('role.list', 1, true);
        $data = $this->service->list($request->get('with_user', false));
        return success($data);
    }

    public function show(int $id)
    {
        $this->permissionService->isAllow('role.list', 1, true);
        $data = $this->service->show($id);
        return success($data);
    }

    public function add(AddRequest $request)
    {
        $this->permissionService->isAllow('role.add', 1, true);
        $this->service->create($request->validated());
        return success();
    }

    public function update(UpdateRequest $request)
    {
        $this->permissionService->isAllow('role.update', 1, true);
        $this->service->update($request->validated());
        return success();
    }
}
