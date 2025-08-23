<?php

namespace App\Http\Controllers\RolePerm;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolePerms\SaveRequest;
use App\Services\RolePerm\PermissionService;
use App\Services\RolePerm\RolePermsService;
use Illuminate\Http\Request;

class RolePermController extends Controller
{
    public function __construct(
        protected RolePermsService $service,
    )
    {
    }

    public function save(SaveRequest $request)
    {
        $this->service->save($request->validated());
        return success();
    }

    public function getByPermission(int $id)
    {
        $data = $this->service->getByPermission($id);
        return success($data);
    }

    public function getByRole(int $id, $withChildren = false)
    {
        $data = $this->service->getByRole($id, $withChildren);
        return success($data);
    }

    public function getUserRoles(int|bool $id = false)
    {
        $data = $this->service->getUserRoles($id);
        return success($data);
    }
}
