<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\AddRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\ChangePhoneRequest;
use App\Http\Requests\User\ListRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\Resource;
use App\Services\RolePerm\PermissionService;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service,
        protected PermissionService $permissionService
    )
    {
    }

    public function list(ListRequest $request)
    {
        $this->permissionService->hasPermission('user.list');
        $data = $this->service->list($request->validated());
        return new Resource($data);
    }

    public function getById(int $id)
    {
        $this->permissionService->hasPermission('user.get');
        $data = $this->service->getById($id, ['role','branches','categories']);
        return success($data);
    }

    public function getInfoByPinfl(int $pinfl)
    {
        $info = $this->service->getInfoByPinfl($pinfl);
        return success($info);
    }

    public function add(AddRequest $request)
    {
        $this->permissionService->hasPermission('user.add');
        $this->service->add($request->validated());
        return success();
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->service->changePassword($request->validated());
        return success();
    }

    public function changePhone(ChangePhoneRequest $request)
    {
        $this->permissionService->hasPermission('user.change_phone');
        $this->service->changePhone($request->get('phone'));
        return success();
    }

    public function update(int $id, UpdateRequest $request)
    {
        $this->permissionService->hasPermission('user.update');
        $this->service->update($id, $request->validated());
        return success();
    }

    public function toggleStatus(int $id, Request $request)
    {
        $request->validate(['status' => 'required|boolean']);
        $this->permissionService->hasPermission('user.update');
        $this->service->toggleStatus($id, $request->get('status'));
        return success();
    }

    public function categories(Request $request)
    {
        $this->permissionService->hasPermission('user.categories_list');
        $data = $this->service->categories($request->all());
        return new Resource($data);
    }

    public function branches(Request $request)
    {
        $this->permissionService->hasPermission('user.branches_list');
        $data = $this->service->branches($request->all());
        return new Resource($data);
    }

    public function delete(int $id)
    {
        $this->permissionService->hasPermission('user.delete');
        $this->service->delete($id);
        return success();
    }
}
